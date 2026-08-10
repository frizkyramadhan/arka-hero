<?php

namespace App\Http\Controllers;

use App\Models\FuelBotSubscriber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FuelBotSubscriberController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:fuel-bot-subscribers.show')->only(['index', 'data']);
        $this->middleware('permission:fuel-bot-subscribers.create')->only(['store']);
        $this->middleware('permission:fuel-bot-subscribers.edit')->only(['update']);
        $this->middleware('permission:fuel-bot-subscribers.delete')->only(['destroy']);
    }

    public function index()
    {
        $title = 'Fuel Bot Whitelist';
        $subtitle = 'Telegram subscribers allowed to submit fuel logs';
        $users = $this->usersForSelect();

        return view('fuel-bot-subscribers.index', compact('title', 'subtitle', 'users'));
    }

    public function data(Request $request)
    {
        $query = FuelBotSubscriber::query()
            ->with('user:id,name,email,username')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($w) use ($q) {
                $w->where('telegram_user_id', 'like', "%{$q}%")
                    ->orWhere('telegram_username', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('username', 'like', "%{$q}%");
                    });
            });
        }

        $users = $this->usersForSelect();

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('user_label', function (FuelBotSubscriber $s) {
                $user = $s->user;
                if (! $user) {
                    return '—';
                }

                $label = $user->name ?: ($user->username ?: 'User #'.$user->id);
                if ($user->email) {
                    $label .= ' ('.$user->email.')';
                }

                return e($label);
            })
            ->addColumn('username_label', fn (FuelBotSubscriber $s) => $s->telegram_username
                ? '@'.e(ltrim($s->telegram_username, '@'))
                : '—')
            ->addColumn('notes_short', function (FuelBotSubscriber $s) {
                if (! $s->notes) {
                    return '—';
                }

                return e(Str::limit($s->notes, 40));
            })
            ->addColumn('status_badge', function (FuelBotSubscriber $s) {
                return $s->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-secondary">Inactive</span>';
            })
            ->addColumn('action', function (FuelBotSubscriber $model) use ($users) {
                return view('fuel-bot-subscribers.action', compact('model', 'users'))->render();
            })
            ->rawColumns(['user_label', 'username_label', 'status_badge', 'action'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = Auth::id();
        $data['is_active'] = $request->boolean('is_active', true);

        try {
            DB::beginTransaction();
            FuelBotSubscriber::create($data);
            DB::commit();

            return redirect()->route('fuel-bot-subscribers.index')
                ->with('toast_success', 'Subscriber added successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to add subscriber: '.$e->getMessage());
        }
    }

    public function update(Request $request, FuelBotSubscriber $fuelBotSubscriber)
    {
        $data = $this->validated($request, $fuelBotSubscriber);
        $data['is_active'] = $request->boolean('is_active');

        try {
            DB::beginTransaction();
            $fuelBotSubscriber->update($data);
            DB::commit();

            return redirect()->route('fuel-bot-subscribers.index')
                ->with('toast_success', 'Subscriber updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to update subscriber: '.$e->getMessage());
        }
    }

    public function destroy(FuelBotSubscriber $fuelBotSubscriber)
    {
        try {
            $fuelBotSubscriber->delete();

            return redirect()->route('fuel-bot-subscribers.index')
                ->with('toast_success', 'Subscriber removed successfully.');
        } catch (\Throwable $e) {
            return back()->with('toast_error', 'Failed to remove subscriber: '.$e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    protected function usersForSelect()
    {
        return User::query()
            ->where('user_status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'username']);
    }

    protected function validated(Request $request, ?FuelBotSubscriber $subscriber = null): array
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'telegram_user_id' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('fuel_bot_subscribers', 'telegram_user_id')->ignore($subscriber?->id),
            ],
            'telegram_username' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($data['telegram_username'])) {
            $data['telegram_username'] = ltrim(trim($data['telegram_username']), '@');
        } else {
            $data['telegram_username'] = null;
        }

        return $data;
    }
}
