@extends('layouts.main')

@section('title', $title)

@php
$parsed = $submission->parsed_json ?? [];
@endphp

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
                    <li class="breadcrumb-item"><a href="{{ route('fuel-bot-logs.index') }}">{{ $title }}</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $subtitle }}</h3>
                        <div class="card-tools">
                            <span class="badge badge-{{ $submission->statusColor() }}">
                                {{ $submission->statusLabel() }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr>
                                <th width="35%">Reference</th>
                                <td><code>{{ $submission->client_uuid }}</code></td>
                            </tr>
                            <tr>
                                <th>Driver</th>
                                <td>
                                    {{ $submission->user?->name ?: '—' }}
                                    @if ($submission->user?->email)
                                    <small class="text-muted">({{ $submission->user->email }})</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Telegram user ID</th>
                                <td>{{ $submission->telegram_user_id }}</td>
                            </tr>
                            <tr>
                                <th>Chat ID</th>
                                <td>{{ $submission->chat_id ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Caption</th>
                                <td>{{ $submission->caption ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>AI model</th>
                                <td>{{ $submission->ai_model ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Received at</th>
                                <td>{{ optional($submission->created_at)->format('d M Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Confirmed at</th>
                                <td>{{ $submission->confirmed_at?->format('d M Y H:i:s') ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Synced at</th>
                                <td>{{ $submission->synced_at?->format('d M Y H:i:s') ?: '—' }}</td>
                            </tr>
                            @if ($submission->error_message)
                            <tr class="text-danger">
                                <th>Error</th>
                                <td>{{ $submission->error_message }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('fuel-bot-logs.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        @if ($submission->fuel_record_id)
                        @can('fuel-records.show')
                        <a href="{{ route('fuel-records.show', $submission->fuel_record_id) }}"
                            class="btn btn-success btn-sm">
                            <i class="fas fa-gas-pump"></i> Open fuel record
                        </a>
                        @endcan
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Parsed data</h3>
                    </div>
                    <div class="card-body p-0">
                        @if (empty($parsed))
                        <p class="p-3 mb-0 text-muted">Belum ada hasil parsing.</p>
                        @else
                        <table class="table table-sm mb-0">
                            <tr>
                                <th width="35%">Vehicle</th>
                                <td>
                                    {{ $parsed['vehicle_code'] ?? '—' }}
                                    @if (!empty($parsed['vehicle_id']))
                                    <i class="fas fa-check-circle text-success" title="Matched to vehicle master"></i>
                                    @elseif (!empty($parsed['vehicle_code']))
                                    <span class="badge badge-warning">unmatched</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{{ $parsed['fuel_date'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Odometer</th>
                                <td>{{ isset($parsed['odometer']) ? number_format((int) $parsed['odometer']) . ' km' : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Fuel type</th>
                                <td>{{ $parsed['fuel_type'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Qty</th>
                                <td>{{ isset($parsed['quantity']) ? number_format((float) $parsed['quantity'], 2) . ' L' : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Price / L</th>
                                <td>{{ isset($parsed['price_per_liter']) ? number_format((float) $parsed['price_per_liter'], 0, ',', '.') : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Total</th>
                                <td>{{ isset($parsed['total_cost']) ? number_format((float) $parsed['total_cost'], 0, ',', '.') : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Station</th>
                                <td>{{ $parsed['fuel_station'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>No. Trans / Receipt No.</th>
                                <td>{{ $parsed['receipt_number'] ?? '—' }}</td>
                            </tr>
                        </table>
                        @endif
                    </div>
                    @if (!empty($parsed))
                    <div class="card-footer p-0">
                        <a class="btn btn-link btn-sm" data-toggle="collapse" href="#rawJson">
                            <i class="fas fa-code"></i> Raw JSON
                        </a>
                        <div id="rawJson" class="collapse">
                            <pre class="mb-0 p-3 bg-light"
                                style="max-height:320px;overflow:auto">{{ json_encode($parsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Receipt photo</h3>
                    </div>
                    <div class="card-body text-center">
                        @if ($submission->receipt_path)
                        <a href="{{ route('fuel-bot-logs.receipt', $submission) }}" target="_blank" rel="noopener">
                            <img src="{{ route('fuel-bot-logs.receipt', $submission) }}" alt="Receipt"
                                class="img-fluid rounded" style="max-height:520px">
                        </a>
                        @else
                        <p class="text-muted mb-0">Tidak ada foto tersimpan.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
