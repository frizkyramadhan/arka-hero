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
      <div class="col-lg-8">
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h3 class="card-title"><strong>{{ $subtitle }}</strong></h3>
            <div class="card-tools">
              <span class="badge badge-warning">Administrator Only</span>
            </div>
          </div>
          <div class="card-body">
            <div class="alert alert-info">
              <i class="fas fa-info-circle"></i>
              Uses the <strong>latest real document</strong> of each type (by <code>created_at</code>).
              <strong>Preview</strong> opens the actual email template in a new browser tab without sending it.
            </div>

            <form method="POST" action="{{ route('debug.email-notifications.litmus') }}" class="mb-3">
              @csrf
              <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-vial mr-1"></i> Run litmus checks
              </button>
              <small class="text-muted ml-2">Renders all type × event combos; no SMTP.</small>
            </form>

            @if (!empty($litmusResults))
              <div class="card mb-3">
                <div class="card-header py-2">
                  <strong>Litmus results</strong>
                </div>
                <div class="card-body p-0 table-responsive">
                  <table class="table table-sm mb-0">
                    <thead>
                      <tr>
                        <th>Type</th>
                        <th>Event</th>
                        <th>Result</th>
                        <th>Checks</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($litmusResults as $row)
                        <tr>
                          <td><code>{{ $row['document_type'] }}</code></td>
                          <td><code>{{ $row['event'] }}</code></td>
                          <td>
                            @if ($row['pass'])
                              <span class="badge badge-success">PASS</span>
                            @else
                              <span class="badge badge-danger">FAIL</span>
                            @endif
                          </td>
                          <td>
                            <small>
                              @foreach ($row['checks'] as $name => $status)
                                <div>{{ $name }}: {{ $status }}</div>
                              @endforeach
                            </small>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form method="POST" action="{{ route('debug.email-notifications.send') }}" id="debug-email-form">
              @csrf

              <div class="form-group">
                <label for="emails">Recipient email(s)</label>
                <textarea name="emails" id="emails" rows="3" class="form-control @error('emails') is-invalid @enderror"
                          placeholder="you@arka.co.id, colleague@arka.co.id"
                          required>{{ old('emails') }}</textarea>
                <small class="form-text text-muted">Separate with comma, semicolon, space, or new line.</small>
              </div>

              <div class="form-group">
                <label for="event">Notification event</label>
                <select name="event" id="event" class="form-control @error('event') is-invalid @enderror" required>
                  @foreach ($events as $value => $label)
                    <option value="{{ $value }}" @selected(old('event', 'approval_requested') === $value)>{{ $label }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label for="remarks">Remarks (optional)</label>
                <input type="text" name="remarks" id="remarks" class="form-control"
                       value="{{ old('remarks') }}"
                       maxlength="500"
                       placeholder="Leave empty to omit remarks">
              </div>

              <input type="hidden" name="document_type" id="document_type" value="">

              <hr>
              <p class="mb-2"><strong>Preview or send using latest document</strong></p>
              <div class="row">
                @foreach ($documentTypes as $type => $label)
                  @php $latest = $latestDocuments[$type] ?? null; @endphp
                  <div class="col-md-6 mb-2">
                    <div class="card h-100 mb-0 {{ $latest ? '' : 'bg-light' }}">
                      <div class="card-body p-3">
                        <strong>{{ $label }}</strong><br>
                        @if ($latest)
                          <small class="text-muted">
                            {{ $latest['reference'] }}
                            @if ($latest['status']) · {{ $latest['status'] }} @endif
                            @if ($latest['created_at']) · {{ $latest['created_at'] }} @endif
                          </small>
                          <div class="btn-group btn-group-sm d-flex mt-3">
                            <button type="submit"
                                    class="btn btn-outline-primary doc-action-btn preview-doc-btn"
                                    data-document-type="{{ $type }}"
                                    data-action="preview"
                                    formaction="{{ route('debug.email-notifications.preview') }}"
                                    formmethod="POST"
                                    formtarget="_blank"
                                    formnovalidate>
                              <i class="fas fa-eye mr-1"></i> Preview
                            </button>
                            <button type="submit"
                                    class="btn btn-primary doc-action-btn send-doc-btn"
                                    data-document-type="{{ $type }}"
                                    data-action="send">
                              <i class="fas fa-paper-plane mr-1"></i> Send
                            </button>
                          </div>
                        @else
                          <small class="text-muted">No document in database</small>
                          <button type="button" class="btn btn-sm btn-secondary btn-block mt-3" disabled>
                            Preview unavailable
                          </button>
                        @endif
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><strong>Mail config</strong></h3>
          </div>
          <div class="card-body p-0">
            <table class="table table-sm mb-0">
              <tr>
                <th style="width:40%">Notifications</th>
                <td>
                  @if ($notificationsEnabled)
                    <span class="badge badge-success">enabled</span>
                  @else
                    <span class="badge badge-secondary">disabled</span>
                    <br><small class="text-muted">Production emails skipped. Debug Preview/Send still work.</small>
                  @endif
                  <br><small class="text-muted"><code>DOCUMENT_NOTIFICATIONS_ENABLED</code> in <code>.env</code></small>
                </td>
              </tr>
              <tr>
                <th>From address</th>
                <td><code>{{ $mailFrom['address'] ?? '—' }}</code></td>
              </tr>
              <tr>
                <th>From name</th>
                <td>{{ $mailFrom['name'] ?? '—' }}</td>
              </tr>
              <tr>
                <th>Mailer</th>
                <td><code>{{ config('mail.default') }}</code></td>
              </tr>
              <tr>
                <th>Queue</th>
                <td><code>{{ config('queue.default') }}</code></td>
              </tr>
              <tr>
                <th>CTA base</th>
                <td><code>{{ config('document_notifications.base_url') }}</code></td>
              </tr>
            </table>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><strong>Tips</strong></h3>
          </div>
          <div class="card-body">
            <ul class="mb-0 pl-3">
              <li>Each button uses the <strong>latest</strong> row of that document type.</li>
              <li>Preview never sends email and opens in a new browser tab.</li>
              <li>Disabled button = no document in DB yet.</li>
              <li>Try each <em>event</em> to compare subjects &amp; copy.</li>
              <li>Check spam if nothing arrives.</li>
              <li>Failures are logged in <code>storage/logs</code>.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@section('scripts')
<script>
  $(function () {
    $('.doc-action-btn').on('click', function () {
      $('#document_type').val($(this).data('document-type'));
      $('#debug-email-form').data('action', $(this).data('action'));
    });

    $('#debug-email-form').on('submit', function () {
      if (!$('#document_type').val()) {
        alert('Click one of the document type buttons to send.');
        return false;
      }

      if ($(this).data('action') === 'preview') {
        return true;
      }

      var btn = $(this).find('.send-doc-btn:focus');
      $('.send-doc-btn').prop('disabled', true);
      if (btn.length) {
        btn.html('<i class="fas fa-spinner fa-spin"></i> Sending...');
      }
    });
  });
</script>
@endsection
