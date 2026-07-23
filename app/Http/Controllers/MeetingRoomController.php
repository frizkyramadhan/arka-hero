<?php

namespace App\Http\Controllers;

use App\Models\MeetingRoom;
use App\Support\UserProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MeetingRoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:meeting-rooms.show')->only(['index', 'data']);
        $this->middleware('permission:meeting-rooms.create')->only(['store']);
        $this->middleware('permission:meeting-rooms.edit')->only(['update']);
        $this->middleware('permission:meeting-rooms.delete')->only(['destroy']);
        // byProject: any authenticated user creating RCR (admin or personal)
        $this->middleware('auth')->only(['byProject']);
    }

    public function index()
    {
        $title = 'Meeting Rooms';
        $subtitle = 'List of Meeting Rooms';
        $projects = UserProject::projectsForSelect();

        return view('meeting-rooms.index', compact('title', 'subtitle', 'projects'));
    }

    public function data(Request $request)
    {
        $query = MeetingRoom::query()
            ->with('project')
            ->orderBy('room_name');

        UserProject::scopeToAssignedProjects($query, 'project_id');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('room_name', 'like', "%{$q}%")
                    ->orWhere('facilities', 'like', "%{$q}%");
            });
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('project_label', function ($row) {
                $p = $row->project;

                return $p ? e($p->project_code.' - '.$p->project_name) : '—';
            })
            ->addColumn('status_badge', function ($row) {
                $map = [
                    'active' => 'success',
                    'inactive' => 'secondary',
                    'maintenance' => 'warning',
                ];
                $class = $map[$row->status] ?? 'secondary';

                return '<span class="badge badge-'.$class.'">'.e(ucfirst($row->status)).'</span>';
            })
            ->addColumn('action', function ($model) {
                $projects = UserProject::projectsForSelect();

                return view('meeting-rooms.action', compact('model', 'projects'))->render();
            })
            ->rawColumns(['status_badge', 'action', 'project_label'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($r = UserProject::guardProjectInAssignmentScope((int) $data['project_id'])) {
            return $r;
        }

        try {
            DB::beginTransaction();
            MeetingRoom::create($data);
            DB::commit();

            return redirect()->route('meeting-rooms.index')
                ->with('toast_success', 'Meeting room added successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to add meeting room: '.$e->getMessage());
        }
    }

    public function update(Request $request, MeetingRoom $meetingRoom)
    {
        $data = $this->validated($request, $meetingRoom);

        if ($r = UserProject::guardProjectInAssignmentScope((int) $data['project_id'])) {
            return $r;
        }

        try {
            DB::beginTransaction();
            $meetingRoom->update($data);
            DB::commit();

            return redirect()->route('meeting-rooms.index')
                ->with('toast_success', 'Meeting room updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to update meeting room: '.$e->getMessage());
        }
    }

    public function destroy(MeetingRoom $meetingRoom)
    {
        try {
            if ($meetingRoom->requests()->exists()) {
                return back()->with('toast_error', 'Cannot delete room that has existing requests.');
            }

            $meetingRoom->delete();

            return redirect()->route('meeting-rooms.index')
                ->with('toast_success', 'Meeting room deleted successfully.');
        } catch (\Throwable $e) {
            return back()->with('toast_error', 'Failed to delete meeting room: '.$e->getMessage());
        }
    }

    /**
     * JSON list of active rooms for a project (request form).
     */
    public function byProject(Request $request)
    {
        $projectId = (int) $request->get('project_id');
        if (! $projectId || ! UserProject::canAccessProjectId($projectId)) {
            return response()->json([]);
        }

        $rooms = MeetingRoom::active()
            ->where('project_id', $projectId)
            ->with('project:id,project_code,project_name')
            ->orderBy('room_name')
            ->get()
            ->map(fn (MeetingRoom $room) => [
                'id' => $room->id,
                'room_name' => $room->room_name,
                'capacity' => $room->capacity,
                'facilities' => $room->facilities,
                'project_code' => $room->project->project_code ?? '',
                'project_name' => $room->project->project_name ?? '',
            ]);

        return response()->json($rooms);
    }

    private function validated(Request $request, ?MeetingRoom $room = null): array
    {
        return $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'room_name' => ['required', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'facilities' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive', 'maintenance'])],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
