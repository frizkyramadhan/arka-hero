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
          <li class="breadcrumb-item"><a href="{{ route('activity-logs.index') }}">Activity Logs</a></li>
          <li class="breadcrumb-item active">#{{ $activity->id }}</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><strong>{{ $subtitle }}</strong></h3>
            <div class="card-tools">
              <a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <table class="table table-bordered">
              <tr>
                <th style="width: 30%">Date</th>
                <td>{{ optional($activity->created_at)->format('d M Y H:i:s') }}</td>
              </tr>
              <tr>
                <th>Log Name</th>
                <td>{{ $activity->log_name }}</td>
              </tr>
              <tr>
                <th>Event</th>
                <td>{{ $activity->event ?: '—' }}</td>
              </tr>
              <tr>
                <th>Description</th>
                <td>{{ $activity->description }}</td>
              </tr>
              <tr>
                <th>Causer</th>
                <td>
                  @if($activity->causer)
                    {{ $activity->causer->name }} ({{ $activity->causer->email }})
                  @else
                    System
                  @endif
                </td>
              </tr>
              <tr>
                <th>Subject</th>
                <td>
                  @php
                    $type = data_get($activity->properties, 'document_type');
                    $typeLabel = $labels[$type] ?? ($type ?: class_basename($activity->subject_type));
                  @endphp
                  {{ $typeLabel }}
                  @if($activity->subject_id)
                    — ID: {{ $activity->subject_id }}
                  @endif
                  @if(data_get($activity->properties, 'reference'))
                    <br><small>Ref: {{ data_get($activity->properties, 'reference') }}</small>
                  @endif
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><strong>Properties</strong></h3>
          </div>
          <div class="card-body p-0">
            <pre class="mb-0 p-3" style="max-height: 480px; overflow: auto; font-size: 12px;">{{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
