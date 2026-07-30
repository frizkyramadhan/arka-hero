@extends('layouts.main')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">{{ $title }}</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">{{ $title }}</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <section class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><strong>{{ $subtitle }}</strong></h3>
          </div>
          <div class="card-body">
            @if (!empty($emailMetrics))
              <div class="row mb-3">
                <div class="col-md-3">
                  <div class="small-box bg-info mb-0">
                    <div class="inner p-3">
                      <h4 class="mb-0">{{ $emailMetrics['email_queued'] ?? 0 }}</h4>
                      <p class="mb-0">Queued ({{ $emailMetrics['days'] ?? 7 }}d)</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="small-box bg-success mb-0">
                    <div class="inner p-3">
                      <h4 class="mb-0">{{ $emailMetrics['email_sent'] ?? 0 }}</h4>
                      <p class="mb-0">Sent</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="small-box bg-danger mb-0">
                    <div class="inner p-3">
                      <h4 class="mb-0">{{ $emailMetrics['email_failed'] ?? 0 }}</h4>
                      <p class="mb-0">Failed</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="small-box bg-secondary mb-0">
                    <div class="inner p-3">
                      <h4 class="mb-0">{{ $emailMetrics['email_skipped'] ?? 0 }}</h4>
                      <p class="mb-0">Skipped</p>
                    </div>
                  </div>
                </div>
              </div>
              @if (!empty($emailMetrics['by_type']))
                <div class="table-responsive mb-3">
                  <table class="table table-sm table-bordered mb-0">
                    <thead>
                      <tr>
                        <th>Document type (7d)</th>
                        <th>Queued</th>
                        <th>Sent</th>
                        <th>Failed</th>
                        <th>Skipped</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($emailMetrics['by_type'] as $type => $counts)
                        <tr>
                          <td>{{ $documentTypes[$type] ?? $type }}</td>
                          <td>{{ $counts['email_queued'] ?? 0 }}</td>
                          <td>{{ $counts['email_sent'] ?? 0 }}</td>
                          <td>{{ $counts['email_failed'] ?? 0 }}</td>
                          <td>{{ $counts['email_skipped'] ?? 0 }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            @endif
            <form id="activity-log-filters" class="mb-3">
              <div class="row">
                <div class="col-md-2">
                  <label>Date From</label>
                  <input type="date" name="date_from" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                  <label>Date To</label>
                  <input type="date" name="date_to" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                  <label>Log Name</label>
                  <select name="log_name" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach($logNames as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2">
                  <label>Event</label>
                  <select name="event" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach($events as $event)
                      <option value="{{ $event }}">{{ $event }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2">
                  <label>Document Type</label>
                  <select name="document_type" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach($documentTypes as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2">
                  <label>Causer</label>
                  <input type="text" name="causer" class="form-control form-control-sm"
                         placeholder="Name or email">
                </div>
              </div>
              @if(isset($tableReady) && ! $tableReady)
                <div class="alert alert-warning mt-3 mb-0">
                  <strong>activity_log</strong> is not ready on this environment.
                  Run <code>composer install</code> and
                  <code>php artisan migrate</code> (Spatie activitylog migrations),
                  then <code>php artisan db:seed --class=ActivityLogPermissionSeeder</code>.
                </div>
              @endif
              <div class="row mt-2">
                <div class="col-md-6">
                  <label>Keyword</label>
                  <input type="text" name="keyword" class="form-control form-control-sm"
                         placeholder="Description, reference, recipient email...">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                  <button type="button" id="btn-filter" class="btn btn-primary btn-sm mr-2">
                    <i class="fas fa-filter"></i> Filter
                  </button>
                  <button type="button" id="btn-reset" class="btn btn-secondary btn-sm">
                    <i class="fas fa-undo"></i> Reset
                  </button>
                </div>
              </div>
            </form>

            <div class="table-responsive">
              <table id="activity-logs-table" width="100%" class="table table-bordered table-striped table-sm">
                <thead>
                  <tr>
                    <th class="text-center">No</th>
                    <th>Date</th>
                    <th>Log</th>
                    <th>Event</th>
                    <th>Document</th>
                    <th>Reference</th>
                    <th>Causer</th>
                    <th>Description</th>
                    <th class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</section>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
  $(function () {
    var table = $('#activity-logs-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('activity-logs.data') }}",
        data: function (d) {
          var form = $('#activity-log-filters').serializeArray();
          form.forEach(function (item) {
            d[item.name] = item.value;
          });
        }
      },
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
        { data: 'created_at_formatted', name: 'created_at' },
        { data: 'log_name_label', name: 'log_name', orderable: false },
        { data: 'event_label', name: 'event' },
        { data: 'document_type_label', name: 'document_type', orderable: false, searchable: false },
        { data: 'reference', name: 'reference', orderable: false, searchable: false },
        { data: 'causer_name', name: 'causer_name', orderable: false, searchable: false },
        { data: 'description_short', name: 'description' },
        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
      ],
      order: [[1, 'desc']]
    });

    $('#btn-filter').on('click', function () {
      table.ajax.reload();
    });

    $('#btn-reset').on('click', function () {
      $('#activity-log-filters')[0].reset();
      table.ajax.reload();
    });
  });
</script>
@endsection
