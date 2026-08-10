<?php

namespace App\Http\Controllers;

use App\Models\FuelBotSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FuelBotLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:fuel-bot-logs.show');
    }

    public function index()
    {
        $title = 'Fuel Bot Activity';
        $subtitle = 'Telegram submissions pipeline log';

        return view('fuel-bot-logs.index', [
            'title' => $title,
            'subtitle' => $subtitle,
            'statuses' => FuelBotSubmission::statusLabels(),
            'stats' => $this->stats(),
        ]);
    }

    public function data(Request $request)
    {
        $query = FuelBotSubmission::query()
            ->with(['user:id,name,email', 'fuelRecord:id,status,fuel_date,receipt_number'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($w) use ($q) {
                $w->where('telegram_user_id', 'like', "%{$q}%")
                    ->orWhere('client_uuid', 'like', "%{$q}%")
                    ->orWhere('caption', 'like', "%{$q}%")
                    ->orWhere('error_message', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    });
            });
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('created_fmt', fn (FuelBotSubmission $s) => optional($s->created_at)->format('Y-m-d H:i'))
            ->addColumn('user_label', function (FuelBotSubmission $s) {
                $name = $s->user?->name;

                return '<div>'.e($name ?: '—').'</div>'
                    .'<small class="text-muted">TG '.e((string) $s->telegram_user_id).'</small>';
            })
            ->addColumn('vehicle_label', function (FuelBotSubmission $s) {
                $parsed = $s->parsed_json ?? [];
                $code = $parsed['vehicle_code'] ?? null;
                $matched = ! empty($parsed['vehicle_id']);

                if (! $code) {
                    return '—';
                }

                return e($code).' '.($matched
                    ? '<i class="fas fa-check-circle text-success" title="Matched"></i>'
                    : '<i class="fas fa-exclamation-triangle text-warning" title="Not matched"></i>');
            })
            ->addColumn('amount_label', function (FuelBotSubmission $s) {
                $parsed = $s->parsed_json ?? [];
                if (! isset($parsed['quantity']) && ! isset($parsed['total_cost'])) {
                    return '—';
                }

                $qty = isset($parsed['quantity']) ? number_format((float) $parsed['quantity'], 2).' L' : '—';
                $total = isset($parsed['total_cost']) && $parsed['total_cost'] !== null
                    ? 'Rp '.number_format((float) $parsed['total_cost'], 0, ',', '.')
                    : '—';

                return '<div>'.e($qty).'</div><small class="text-muted">'.e($total).'</small>';
            })
            ->addColumn('status_badge', function (FuelBotSubmission $s) {
                $badge = '<span class="badge badge-'.$s->statusColor().'">'.e($s->statusLabel()).'</span>';
                if ($s->error_message) {
                    $badge .= '<br><small class="text-danger">'.e(Str::limit($s->error_message, 40)).'</small>';
                }

                return $badge;
            })
            ->addColumn('record_link', function (FuelBotSubmission $s) {
                if (! $s->fuel_record_id) {
                    return '—';
                }

                if (! auth()->user()?->can('fuel-records.show')) {
                    return '<i class="fas fa-check text-success" title="Fuel record created"></i>';
                }

                return '<a href="'.route('fuel-records.show', $s->fuel_record_id).'" class="btn btn-outline-success btn-sm" title="Open fuel record"><i class="fas fa-gas-pump"></i></a>';
            })
            ->addColumn('action', function (FuelBotSubmission $s) {
                return '<a href="'.route('fuel-bot-logs.show', $s).'" class="btn btn-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>';
            })
            ->rawColumns(['user_label', 'vehicle_label', 'amount_label', 'status_badge', 'record_link', 'action'])
            ->toJson();
    }

    public function show(FuelBotSubmission $fuelBotLog)
    {
        $fuelBotLog->load(['user', 'fuelRecord.vehicle']);

        return view('fuel-bot-logs.show', [
            'title' => 'Fuel Bot Activity',
            'subtitle' => 'Submission detail',
            'submission' => $fuelBotLog,
        ]);
    }

    /** Serve the raw Telegram inbox photo from the private disk. */
    public function receipt(FuelBotSubmission $fuelBotLog)
    {
        if (! $fuelBotLog->receipt_path || ! Storage::disk('private')->exists($fuelBotLog->receipt_path)) {
            abort(404);
        }

        return Storage::disk('private')->response($fuelBotLog->receipt_path);
    }

    /**
     * @return array<string, int>
     */
    protected function stats(): array
    {
        $today = now()->startOfDay();

        return [
            'today' => FuelBotSubmission::where('created_at', '>=', $today)->count(),
            'synced_today' => FuelBotSubmission::where('created_at', '>=', $today)
                ->where('status', FuelBotSubmission::STATUS_SYNCED)->count(),
            'awaiting' => FuelBotSubmission::where('status', FuelBotSubmission::STATUS_AWAITING_CONFIRM)->count(),
            'failed_7d' => FuelBotSubmission::where('created_at', '>=', now()->subDays(7))
                ->where('status', FuelBotSubmission::STATUS_FAILED)->count(),
        ];
    }
}
