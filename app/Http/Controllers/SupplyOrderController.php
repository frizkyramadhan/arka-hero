<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApprovalPlanController;
use App\Models\Department;
use App\Models\SupplyItem;
use App\Models\SupplyItemCategory;
use App\Models\SupplyOrder;
use App\Models\SupplyOrderItem;
use App\Support\UserProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class SupplyOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:supplies.orders.show')->only(['index', 'data', 'show', 'print']);
        $this->middleware('permission:supplies.orders.create')->only(['create', 'store']);
        $this->middleware('permission:supplies.orders.edit')->only(['edit', 'update', 'submitForApproval']);
        $this->middleware('permission:supplies.orders.delete')->only(['destroy', 'cancel']);
        $this->middleware('permission:supplies.orders.close')->only(['close']);
        $this->middleware('permission:personal.supplies.orders.view-own')->only(['myOrders', 'myOrdersData', 'myOrderShow', 'myOrderPrint']);
        $this->middleware('permission:personal.supplies.orders.create-own')->only(['myOrdersCreate', 'myOrdersStore']);
        $this->middleware('permission:personal.supplies.orders.edit-own')->only(['myOrdersEdit', 'myOrdersUpdate', 'myOrdersSubmitForApproval']);
        $this->middleware('permission:personal.supplies.orders.cancel-own')->only(['myOrdersCancel']);
    }

    public function index()
    {
        $title = 'Supply Orders';
        $subtitle = 'All supply orders';
        $projects = UserProject::projectsForSelect();

        return view('supplies.orders.index', compact('title', 'subtitle', 'projects'));
    }

    public function data(Request $request)
    {
        $query = SupplyOrder::query()
            ->with(['project', 'requestedBy', 'department'])
            ->orderByDesc('created_at');

        UserProject::scopeToAssignedProjects($query, 'project_id');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $this->datatables($query, false);
    }

    public function myOrders()
    {
        $title = 'My Office Supply Orders';
        $subtitle = 'Office supply orders you created';

        return view('supplies.orders.my-index', compact('title', 'subtitle'));
    }

    public function myOrdersData()
    {
        $query = SupplyOrder::query()
            ->with(['project', 'requestedBy', 'department'])
            ->where('requested_by', Auth::id())
            ->orderByDesc('created_at');

        return $this->datatables($query, true);
    }

    public function create()
    {
        return $this->form(null, false);
    }

    public function myOrdersCreate()
    {
        return $this->form(null, true);
    }

    public function store(Request $request)
    {
        return $this->persist($request, null, false);
    }

    public function myOrdersStore(Request $request)
    {
        return $this->persist($request, null, true);
    }

    public function show(SupplyOrder $supplyOrder)
    {
        abort_unless($supplyOrder->canBeViewedBy(Auth::user()), 403);
        $supplyOrder->load(['project', 'requestedBy', 'department', 'items.item', 'stockIns', 'approvalPlans.approver']);

        return view('supplies.orders.show', [
            'title' => 'Supply Order',
            'subtitle' => $supplyOrder->order_number,
            'order' => $supplyOrder,
            'isPersonal' => false,
        ]);
    }

    public function myOrderShow(SupplyOrder $supplyOrder)
    {
        abort_unless($supplyOrder->canBeViewedBy(Auth::user()), 403);
        $supplyOrder->load(['project', 'requestedBy', 'department', 'items.item', 'stockIns', 'approvalPlans.approver']);

        return view('supplies.orders.show', [
            'title' => 'Office Supply Order',
            'subtitle' => $supplyOrder->order_number,
            'order' => $supplyOrder,
            'isPersonal' => true,
        ]);
    }

    public function print(SupplyOrder $supplyOrder)
    {
        abort_unless($supplyOrder->canBeViewedBy(Auth::user()), 403);

        return $this->printView($supplyOrder, false);
    }

    public function myOrderPrint(SupplyOrder $supplyOrder)
    {
        abort_unless($supplyOrder->canBeViewedBy(Auth::user()), 403);

        return $this->printView($supplyOrder, true);
    }

    private function printView(SupplyOrder $supplyOrder, bool $personal)
    {
        $supplyOrder->load([
            'project',
            'department',
            'requestedBy',
            'items.item',
            'approvalPlans.approver',
        ]);

        return view('supplies.orders.print', [
            'order' => $supplyOrder,
            'isPersonal' => $personal,
        ]);
    }

    public function edit(SupplyOrder $supplyOrder)
    {
        abort_unless($supplyOrder->canBeEditedBy(Auth::user()), 403);

        return $this->form($supplyOrder, false);
    }

    public function myOrdersEdit(SupplyOrder $supplyOrder)
    {
        abort_unless($supplyOrder->canBeEditedBy(Auth::user()), 403);

        return $this->form($supplyOrder, true);
    }

    public function update(Request $request, SupplyOrder $supplyOrder)
    {
        abort_unless($supplyOrder->canBeEditedBy(Auth::user()), 403);

        return $this->persist($request, $supplyOrder, false);
    }

    public function myOrdersUpdate(Request $request, SupplyOrder $supplyOrder)
    {
        abort_unless($supplyOrder->canBeEditedBy(Auth::user()), 403);

        return $this->persist($request, $supplyOrder, true);
    }

    public function submitForApproval(Request $request, SupplyOrder $supplyOrder)
    {
        abort_unless($supplyOrder->canBeEditedBy(Auth::user()), 403);

        return $this->submit($request, $supplyOrder, false);
    }

    public function myOrdersSubmitForApproval(Request $request, SupplyOrder $supplyOrder)
    {
        abort_unless($supplyOrder->canBeEditedBy(Auth::user()), 403);

        return $this->submit($request, $supplyOrder, true);
    }

    public function destroy(SupplyOrder $supplyOrder)
    {
        abort_unless($supplyOrder->isEditable() && Auth::user()?->can('supplies.orders.delete'), 403);
        $supplyOrder->delete();

        return redirect()->route('supplies.orders.index')->with('toast_success', 'Supply Order deleted.');
    }

    public function myOrdersCancel(SupplyOrder $supplyOrder)
    {
        abort_unless(
            (int) $supplyOrder->requested_by === (int) Auth::id()
            && $supplyOrder->canCancel()
            && Auth::user()?->can('personal.supplies.orders.cancel-own'),
            403
        );

        $this->applyCancel($supplyOrder);

        return redirect()->route('supplies.orders.my-orders.show', $supplyOrder)
            ->with('toast_success', 'Supply Order cancelled.');
    }

    public function cancel(SupplyOrder $supplyOrder)
    {
        abort_unless($supplyOrder->canCancel(), 403);

        $this->applyCancel($supplyOrder);

        return redirect()->route('supplies.orders.show', $supplyOrder)
            ->with('toast_success', 'Supply Order cancelled.');
    }

    public function close(SupplyOrder $supplyOrder)
    {
        abort_unless($supplyOrder->canClose(), 403);

        $supplyOrder->update([
            'status' => SupplyOrder::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        return redirect()->route('supplies.orders.show', $supplyOrder)
            ->with('toast_success', 'Supply Order closed.');
    }

    private function form(?SupplyOrder $order, bool $personal)
    {
        $user = Auth::user();
        $user?->loadMissing('employee.activeAdministration.project');
        $employee = $user?->employee;
        $administration = $employee?->activeAdministration;
        if ($administration) {
            $administration->loadMissing('project');
        }

        if (! $order && ! $administration) {
            $route = $personal ? 'supplies.orders.my-orders' : 'supplies.orders.index';

            return redirect()->route($route)
                ->with('toast_error', 'You need an active administration to create a Supply Order.');
        }

        $itemQuery = SupplyItem::query()->active()->orderBy('code');
        if ($personal) {
            $itemQuery->officeSupply();
        }
        $items = $itemQuery->get(['id', 'code', 'name', 'description', 'stock_unit']);
        $order?->load(['items.item', 'administration.project', 'department']);
        $departments = Department::query()->where('department_status', 1)->orderBy('department_name')->get();

        $previewOrderNumber = null;
        if (! $order && $administration?->project) {
            $previewOrderNumber = SupplyOrder::previewNumber(
                (int) $administration->project_id,
                $administration->project->project_code
            );
        }

        return view('supplies.orders.form', [
            'title' => $order
                ? ($personal ? 'Edit Office Supply Order' : 'Edit Supply Order')
                : ($personal ? 'Create Office Supply Order' : 'Create Supply Order'),
            'subtitle' => $order?->order_number ?? ($previewOrderNumber ?? 'New order'),
            'order' => $order,
            'administration' => $order?->administration ?? $administration,
            'items' => $items,
            'departments' => $departments,
            'isPersonal' => $personal,
            'previewOrderNumber' => $previewOrderNumber,
        ]);
    }

    private function persist(Request $request, ?SupplyOrder $order, bool $personal)
    {
        $isSubmit = $request->input('submit_action') === 'submit';

        $rules = [
            'order_date' => 'required|date',
            'department_id' => 'required|exists:departments,id',
            'items' => 'required|array|min:1',
            'items.*.supply_item_id' => $personal
                ? [
                    'required',
                    Rule::exists('supply_items', 'id')->where(function ($query) {
                        $query->where('status', 'active')
                            ->whereIn('supply_item_category_id', function ($sub) {
                                $sub->select('id')
                                    ->from('supply_item_categories')
                                    ->where('prefix', SupplyItemCategory::PREFIX_OFFICE_SUPPLY);
                            });
                    }),
                ]
                : ['required', 'exists:supply_items,id'],
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.remarks' => 'nullable|string',
            'manual_approvers' => $isSubmit ? 'required|array|min:1' : 'nullable|array',
            'manual_approvers.*' => 'exists:users,id',
        ];

        $data = $request->validate($rules);

        $user = Auth::user();
        $user?->loadMissing('employee.activeAdministration.project');
        $administration = $order
            ? $order->administration()->with('project')->first()
            : $user?->employee?->activeAdministration;

        if ($administration) {
            $administration->loadMissing('project');
        }

        if (! $administration || ! $administration->project) {
            return back()->withInput()->with('toast_error', 'Active administration with a project is required.');
        }

        $approvers = array_values(array_unique(array_filter(array_map('intval', $data['manual_approvers'] ?? []))));

        try {
            DB::beginTransaction();

            if (! $order) {
                $number = SupplyOrder::allocateNumber((int) $administration->project_id, $administration->project->project_code);
                $order = SupplyOrder::create([
                    'order_number' => $number['order_number'],
                    'order_sequence' => $number['order_sequence'],
                    'project_id' => $administration->project_id,
                    'administration_id' => $administration->id,
                    'department_id' => $data['department_id'],
                    'requested_by' => $user->id,
                    'order_date' => $data['order_date'],
                    'manual_approvers' => $approvers ?: null,
                    'status' => SupplyOrder::STATUS_DRAFT,
                ]);
            } else {
                $order->update([
                    'department_id' => $data['department_id'],
                    'order_date' => $data['order_date'],
                    'manual_approvers' => $approvers ?: null,
                ]);
                $order->items()->delete();
            }

            foreach ($data['items'] as $line) {
                SupplyOrderItem::create([
                    'supply_order_id' => $order->id,
                    'supply_item_id' => $line['supply_item_id'],
                    'quantity_ordered' => $line['quantity_ordered'],
                    'remarks' => $line['remarks'] ?? null,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to save Supply Order: '.$e->getMessage());
        }

        if ($isSubmit) {
            return $this->submit($request, $order->fresh(), $personal);
        }

        $show = $personal ? 'supplies.orders.my-orders.show' : 'supplies.orders.show';

        return redirect()->route($show, $order)->with('toast_success', 'Supply Order saved.');
    }

    private function submit(Request $request, SupplyOrder $order, bool $personal)
    {
        $approvers = array_values(array_unique(array_filter(array_map('intval', $request->input('manual_approvers', $order->manual_approvers ?? [])))));
        if ($approvers === []) {
            return back()->withInput()->with('toast_error', 'Select at least one approver.');
        }
        if (! $order->items()->exists()) {
            return back()->with('toast_error', 'Add at least one item.');
        }

        $order->update([
            'manual_approvers' => $approvers,
            'status' => SupplyOrder::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $created = app(ApprovalPlanController::class)->create_manual_approval_plan('supply_order', $order->id);
        if (! $created) {
            $order->update(['status' => SupplyOrder::STATUS_DRAFT, 'submitted_at' => null]);

            return back()->with('toast_error', 'Failed to create approval plan.');
        }

        $show = $personal ? 'supplies.orders.my-orders.show' : 'supplies.orders.show';

        return redirect()->route($show, $order)->with('toast_success', 'Supply Order submitted for approval.');
    }

    private function datatables($query, bool $personal)
    {
        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('project_label', fn ($row) => display_text(trim(($row->project->project_code ?? '').' - '.($row->project->project_name ?? ''), ' -')))
            ->addColumn('department_label', fn ($row) => display_text($row->department->department_name ?? null))
            ->editColumn('order_date', fn ($row) => $row->order_date?->format('d/m/Y'))
            ->addColumn('requester_name', fn ($row) => display_text($row->requestedBy->name ?? null))
            ->addColumn('status_badge', function ($row) {
                return '<span class="badge badge-'.$row->statusBadgeClass().'">'.e(display_text($row->statusLabel(), '')).'</span>';
            })
            ->addColumn('action', function ($model) use ($personal) {
                return view('supplies.orders.action', compact('model', 'personal'))->render();
            })
            ->rawColumns(['status_badge', 'action'])
            ->toJson();
    }

    private function applyCancel(SupplyOrder $order): void
    {
        $order->update([
            'status' => SupplyOrder::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        app(ApprovalPlanController::class)->closeOpenApprovalPlans('supply_order', $order->id);
    }
}
