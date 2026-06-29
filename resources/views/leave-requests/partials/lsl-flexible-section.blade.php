@php
    $lslLeaveRequest = $leaveRequest ?? null;
    $lslTakenDays = (int) old('lsl_taken_days', $lslLeaveRequest->lsl_taken_days ?? 0);
    $lslCashoutDays = (int) old('lsl_cashout_days', $lslLeaveRequest->lsl_cashout_days ?? 0);
    $lslTotalDays = (int) old('lsl_total_days', $lslTakenDays + $lslCashoutDays);
    $lslUsageMode = old('lsl_usage_mode');
    if (! $lslUsageMode) {
        $lslUsageMode = ($lslTakenDays === 0 && $lslCashoutDays > 0)
            ? 'cashout_only'
            : ($lslCashoutDays > 0 ? 'combined' : 'leave_only');
    }
@endphp

<div class="row" id="lsl_flexible_section" style="display: none;">
    <div class="col-md-12">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-coins mr-2"></i>
                    <strong>Long Service Leave</strong>
                    <small class="text-muted">(Leave, cash out, or both)</small>
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3" id="lsl_mode_info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Total Days</strong> (leave + cash out) will be deducted from your LSL balance.
                </div>

                <div class="form-group">
                    <label class="d-block mb-2">
                        <i class="fas fa-sliders-h mr-1"></i>
                        LSL Usage Mode
                    </label>
                    <div class="btn-group btn-group-toggle d-flex w-100 lsl-usage-mode-group" data-toggle="buttons">
                        <label class="btn btn-outline-primary flex-fill {{ $lslUsageMode === 'leave_only' ? 'active' : '' }}">
                            <input type="radio" name="lsl_usage_mode" id="lsl_mode_leave_only" value="leave_only"
                                autocomplete="off" {{ $lslUsageMode === 'leave_only' ? 'checked' : '' }}>
                            <i class="fas fa-calendar-check mr-1"></i> Take Leave Only
                        </label>
                        <label class="btn btn-outline-primary flex-fill {{ $lslUsageMode === 'cashout_only' ? 'active' : '' }}">
                            <input type="radio" name="lsl_usage_mode" id="lsl_mode_cashout_only" value="cashout_only"
                                autocomplete="off" {{ $lslUsageMode === 'cashout_only' ? 'checked' : '' }}>
                            <i class="fas fa-money-bill-wave mr-1"></i> Cash Out Only
                        </label>
                        <label class="btn btn-outline-primary flex-fill {{ $lslUsageMode === 'combined' ? 'active' : '' }}">
                            <input type="radio" name="lsl_usage_mode" id="lsl_mode_combined" value="combined"
                                autocomplete="off" {{ $lslUsageMode === 'combined' ? 'checked' : '' }}>
                            <i class="fas fa-layer-group mr-1"></i> Combined
                        </label>
                    </div>
                    <small class="form-text text-muted" id="lsl_mode_help">
                        Select how you want to use your Long Service Leave entitlement.
                    </small>
                    @error('lsl_usage_mode')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-lg-4" id="lsl_taken_days_col">
                        <div class="form-group">
                            <label for="lsl_taken_days">
                                <i class="fas fa-calendar-check mr-1"></i>
                                Leave Days
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="lsl_taken_days" name="lsl_taken_days"
                                    min="0" max="365" value="{{ $lslTakenDays }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                            <small class="form-text text-muted" id="lsl_taken_days_help">
                                Calculated from date range by default. Can be edited manually.
                            </small>
                            @error('lsl_taken_days')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4" id="lsl_cashout_field_col">
                        <div class="form-group">
                            <label for="lsl_cashout_days">
                                <i class="fas fa-money-bill-wave mr-1"></i>
                                Cash Out
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="lsl_cashout_days" name="lsl_cashout_days"
                                    min="0" max="365" value="{{ $lslCashoutDays }}"
                                    data-initial-value="{{ $lslCashoutDays }}"
                                    {{ $lslUsageMode === 'leave_only' ? 'readonly' : '' }}>
                                <div class="input-group-append">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                            <small class="form-text text-muted" id="lsl_cashout_help">
                                Enter days to cash out
                            </small>
                            @error('lsl_cashout_days')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="lsl_total_days">
                                <i class="fas fa-calculator mr-1"></i>
                                Total Days
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="lsl_total_days" name="lsl_total_days"
                                    min="0" value="{{ $lslTotalDays }}" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Sum of Leave Days + Cash Out Days (deducted from balance)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .lsl-usage-mode-group {
        display: flex !important;
    }

    .lsl-usage-mode-group > label.btn {
        flex: 1 1 0;
        min-width: 0;
        white-space: normal;
    }
</style>
