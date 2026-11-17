@php
    $araimConfigured = $araimConfigured ?? false;
@endphp

<div id="inventories-pane" class="content" role="tabpanel" aria-labelledby="inventories-pane-trigger">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h5 class="mb-0">Inventories</h5>
        <small class="text-muted">Live data from ARAIM (currently held items only)</small>
    </div>

    @if (! $araimConfigured)
        <div class="alert alert-secondary mb-0" id="inventories-unconfigured">
            ARAIM integration is not configured. Set <code>ARAIM_API_URL</code> and <code>ARAIM_API_KEY</code> on the server.
        </div>
    @else
        <div class="alert alert-warning d-none" id="inventories-error"></div>
        <div class="table-responsive">
            <table id="employee-inventories-table" class="table table-modern table-bordered table-striped w-100"
                data-ajax-url="{{ route('employees.inventories.data', $employee) }}">
                <thead>
                    <tr>
                        <th style="width: 2.5rem;"></th>
                        <th>Inventory No</th>
                        <th>Asset</th>
                        <th>Serial No</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Project</th>
                        <th>Date</th>
                        <th class="d-none">detail</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endif
</div>
