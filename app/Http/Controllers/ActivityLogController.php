<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:activity-logs.show')->only(['index', 'data', 'show']);
    }

    public function index()
    {
        $title = 'Activity Logs';
        $subtitle = 'Document approval & email audit trail';
        $logNames = [
            'document_approval' => 'Document Approval',
            'document_email' => 'Document Email',
        ];
        // Static list — avoid querying activity_log on page load (table/package may be missing after deploy)
        $events = collect([
            'submitted',
            'step_approved',
            'step_rejected',
            'document_approved',
            'document_rejected',
            'email_queued',
            'email_sent',
            'email_failed',
            'email_skipped',
        ]);
        $documentTypes = config('document_notifications.labels', []);
        $tableReady = $this->activityLogTableReady();
        $emailMetrics = $this->emailDeliveryMetrics($tableReady);

        return view('activity-logs.index', compact(
            'title',
            'subtitle',
            'logNames',
            'events',
            'documentTypes',
            'tableReady',
            'emailMetrics'
        ));
    }

    public function data(Request $request)
    {
        if (! $this->activityLogTableReady()) {
            return response()->json([
                'draw' => (int) $request->get('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'activity_log table is not ready. Run migrations for spatie/laravel-activitylog.',
            ]);
        }

        try {
            $query = Activity::query()
                ->with(['causer'])
                ->whereIn('log_name', ['document_approval', 'document_email'])
                ->latest();

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->filled('log_name')) {
                $query->where('log_name', $request->log_name);
            }

            if ($request->filled('event')) {
                $query->where('event', $request->event);
            }

            if ($request->filled('causer_id')) {
                $query->where('causer_id', $request->causer_id);
            }

            if ($request->filled('causer')) {
                $causer = $request->causer;
                $query->whereHasMorph('causer', [\App\Models\User::class], function ($q) use ($causer) {
                    $q->where('name', 'like', "%{$causer}%")
                        ->orWhere('email', 'like', "%{$causer}%");
                });
            }

            if ($request->filled('document_type')) {
                $documentType = $request->document_type;
                $query->where(function ($q) use ($documentType) {
                    $q->where('properties->document_type', $documentType);
                });
            }

            if ($request->filled('keyword')) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('description', 'like', "%{$keyword}%")
                        ->orWhere('properties->reference', 'like', "%{$keyword}%")
                        ->orWhere('properties->recipient_email', 'like', "%{$keyword}%")
                        ->orWhere('properties->subject', 'like', "%{$keyword}%");
                });
            }

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('created_at_formatted', function (Activity $activity) {
                    return optional($activity->created_at)->format('d M Y H:i:s');
                })
                ->addColumn('log_name_label', function (Activity $activity) {
                    return match ($activity->log_name) {
                        'document_approval' => '<span class="badge badge-primary">Approval</span>',
                        'document_email' => '<span class="badge badge-info">Email</span>',
                        default => e((string) $activity->log_name),
                    };
                })
                ->addColumn('event_label', function (Activity $activity) {
                    return display_text($activity->event ?: null);
                })
                ->addColumn('document_type_label', function (Activity $activity) {
                    $type = data_get($activity->properties, 'document_type');
                    $labels = config('document_notifications.labels', []);

                    return e($labels[$type] ?? ($type ?: '—'));
                })
                ->addColumn('reference', function (Activity $activity) {
                    return e(data_get($activity->properties, 'reference') ?: '—');
                })
                ->addColumn('causer_name', function (Activity $activity) {
                    return display_text($activity->causer->name ?? 'System');
                })
                ->addColumn('description_short', function (Activity $activity) {
                    return e(\Illuminate\Support\Str::limit($activity->description, 80));
                })
                ->addColumn('action', function (Activity $activity) {
                    $url = route('activity-logs.show', $activity->id);

                    return '<a href="'.$url.'" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>';
                })
                ->rawColumns(['log_name_label', 'action'])
                ->toJson();
        } catch (\Throwable $e) {
            Log::error('ActivityLogController::data failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'draw' => (int) $request->get('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        if (! $this->activityLogTableReady()) {
            return redirect()->route('activity-logs.index')
                ->with('toast_error', 'activity_log table is not ready. Run migrations first.');
        }

        $activity = Activity::with(['causer', 'subject'])->findOrFail($id);
        $title = 'Activity Log Detail';
        $subtitle = 'Log #'.$activity->id;
        $labels = config('document_notifications.labels', []);

        return view('activity-logs.show', compact('activity', 'title', 'subtitle', 'labels'));
    }

    protected function emailDeliveryMetrics(bool $tableReady): array
    {
        $defaults = [
            'days' => 7,
            'queue_pending' => 0,
            'email_queued' => 0,
            'email_sent' => 0,
            'email_failed' => 0,
            'email_skipped' => 0,
            'by_type' => [],
        ];

        if (! $tableReady) {
            return $defaults;
        }

        try {
            if (Schema::hasTable('jobs')) {
                $defaults['queue_pending'] = DB::table('jobs')
                    ->where('payload', 'like', '%DocumentApprovalNotification%')
                    ->count();
            }

            $since = now()->subDays(7)->startOfDay();
            $rows = Activity::query()
                ->where('log_name', 'document_email')
                ->whereIn('event', ['email_queued', 'email_sent', 'email_failed', 'email_skipped'])
                ->where('created_at', '>=', $since)
                ->get(['event', 'properties']);

            foreach ($rows as $row) {
                $event = (string) $row->event;
                if (isset($defaults[$event])) {
                    $defaults[$event]++;
                }

                $type = data_get($row->properties, 'document_type');
                if (! is_string($type) || $type === '') {
                    continue;
                }
                if (! isset($defaults['by_type'][$type])) {
                    $defaults['by_type'][$type] = [
                        'email_queued' => 0,
                        'email_sent' => 0,
                        'email_failed' => 0,
                        'email_skipped' => 0,
                    ];
                }
                if (isset($defaults['by_type'][$type][$event])) {
                    $defaults['by_type'][$type][$event]++;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('emailDeliveryMetrics failed', ['error' => $e->getMessage()]);
        }

        return $defaults;
    }

    protected function activityLogTableReady(): bool
    {
        try {
            if (! class_exists(Activity::class)) {
                return false;
            }

            return Schema::hasTable(config('activitylog.table_name', 'activity_log'));
        } catch (\Throwable $e) {
            Log::warning('activity_log readiness check failed', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
