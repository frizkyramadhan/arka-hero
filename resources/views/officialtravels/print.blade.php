<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>LETTER OF OFFICIAL TRAVEL</title>
        {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet" href="{{ asset('assets/dist/css/font.css') }}">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
        <!-- SweetAlert2 -->
        <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 11pt;
            }

            .header {
                display: flex;
                align-items: center;
                gap: 20px;
                margin-bottom: 15px;
            }

            .logo {
                width: 150px;
            }

            .title {
                font-size: 16pt;
                font-weight: bold;
            }

            .number {
                text-align: center;
                margin-bottom: 20px;
                font-weight: bold;
                font-size: 14pt;
            }

            .info-row {
                display: flex;
                margin-bottom: 2px;
                line-height: 1;
            }

            .label {
                width: 170px;
                font-weight: bold;
                margin-bottom: 0;
                padding: 0;
            }

            .colon {
                width: 20px;
                text-align: center;
                margin-bottom: 0;
                padding: 0;
            }

            .value {
                flex: 1;
                margin-bottom: 0;
                padding: 0;
                line-height: 1;
            }

            .signature-section {
                margin-top: 30px;
            }

            .signature-date {
                margin-bottom: 10px;
            }

            .signature-boxes {
                display: flex;
                justify-content: space-between;
                gap: 100px;
            }

            .signature-box {
                text-align: center;
                flex: 1;
            }

            .signature-line {
                margin: 40px 0 10px 0;
                border-bottom: 1px solid #000;
                width: 200px;
                display: inline-block;
            }

            .dotted-line {
                border-bottom: 1px dotted black;
                display: inline-block;
                min-width: 200px;
            }

            .footer {
                display: flex;
                justify-content: space-between;
                margin-top: 30px;
                font-size: 10pt;
            }

            table td {
                padding: 3px 8px !important;
                line-height: 1.2 !important;
            }

            table th {
                padding: 5px 8px !important;
                line-height: 1.2 !important;
            }

            .arrival-departure td {
                padding: 8px !important;
            }

            .arrival-departure div {
                margin-bottom: 5px;
            }

            @media print {
                body {
                    padding: 20px;
                }
            }
        </style>
    </head>

    <body>
        <div class="container-fluid">
            <div class="header">
                <img src="{{ asset('assets/dist/img/logo.png') }}" alt="ARKA" class="logo">
                <div class="title">LETTER OF OFFICIAL TRAVEL</div>
            </div>

            <div class="number">
                No. {{ $officialtravel->official_travel_number }}
            </div>

            <div class="info-row">
                <div class="label">Name / NIK</div>
                <div class="colon">:</div>
                <div class="value">{{ $officialtravel->traveler->employee->fullname ?? 'N/A' }} /
                    {{ $officialtravel->traveler->nik ?? 'N/A' }}</div>
            </div>

            <div class="info-row">
                <div class="label">Title</div>
                <div class="colon">:</div>
                <div class="value">{{ $officialtravel->traveler->position->position_name ?? 'N/A' }}</div>
            </div>

            <div class="info-row">
                <div class="label">Business Unit</div>
                <div class="colon">:</div>
                <div class="value">{{ $officialtravel->traveler->project->project_name ?? 'N/A' }}</div>
            </div>

            <div class="info-row">
                <div class="label">Division / Department</div>
                <div class="colon">:</div>
                <div class="value">{{ $officialtravel->traveler->position->department->department_name ?? 'N/A' }}
                </div>
            </div>

            <br>

            <div class="info-row">
                <div class="label">Purpose of Travel</div>
                <div class="colon">:</div>
                <div class="value">{{ $officialtravel->purpose }}</div>
            </div>

            <div class="info-row">
                <div class="label">Destination</div>
                <div class="colon">:</div>
                <div class="value">{{ $officialtravel->destination }}</div>
            </div>

            <div class="info-row">
                <div class="label">Duration of Travel</div>
                <div class="colon">:</div>
                <div class="value">{{ $officialtravel->duration }}</div>
            </div>

            <div class="info-row">
                <div class="label">Departure from</div>
                <div class="colon">:</div>
                <div class="value">{{ date('d F Y', strtotime($officialtravel->departure_from)) ?? 'N/A' }}</div>
            </div>

            <div class="mt-3 mb-2">
                <div class="mb-1">
                    <strong>Arrivals and Departures : </strong><i>(filled by officer on destination with Stamp and
                        Sign)</i>
                </div>
                @if ($officialtravel->stops->count() > 0)
                    @foreach ($officialtravel->stops as $index => $stop)
                        <div class="mb-3">
                            <h6><strong>Stop #{{ $index + 1 }}</strong></h6>
                            <table class="table table-sm table-bordered arrival-departure">
                                <tr>
                                    <td width="50%">
                                        <strong>Arrival at Destination</strong>
                                    </td>
                                    <td width="50%">
                                        <strong>Departure from Destination</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%" height="120px">
                                        <div>
                                            <strong>Checked by:</strong><br>
                                            {{ $stop->arrivalChecker->name ?? '.........................' }}
                                        </div>
                                        <div>
                                            <strong>Remarks:</strong><br>
                                            {{ $stop->arrival_remark ?? '.........................' }}
                                        </div>
                                        <div>
                                            <strong>Date:</strong><br>
                                            {{ $stop->arrival_at_destination ? date('d/m/Y', strtotime($stop->arrival_at_destination)) : '.........................' }}
                                        </div>
                                    </td>
                                    <td width="50%" height="120px">
                                        <div>
                                            <strong>Checked by:</strong><br>
                                            {{ $stop->departureChecker->name ?? '.........................' }}
                                        </div>
                                        <div>
                                            <strong>Remarks:</strong><br>
                                            {{ $stop->departure_remark ?? '.........................' }}
                                        </div>
                                        <div>
                                            <strong>Date:</strong><br>
                                            {{ $stop->departure_from_destination ? date('d/m/Y', strtotime($stop->departure_from_destination)) : '.........................' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endforeach
                @else
                    <div class="mb-3">
                        <table class="table table-sm table-bordered arrival-departure">
                            <tr>
                                <td width="50%">
                                    <strong>Arrival at Destination</strong>
                                </td>
                                <td width="50%">
                                    <strong>Departure from Destination</strong>
                                </td>
                            </tr>
                            <tr>
                                <td width="50%" height="120px">
                                    <div>
                                        <strong>Checked by:</strong><br>
                                        .........................
                                    </div>
                                    <div>
                                        <strong>Remarks:</strong><br>
                                        .........................
                                    </div>
                                    <div>
                                        <strong>Date:</strong><br>
                                        .........................
                                    </div>
                                </td>
                                <td width="50%" height="120px">
                                    <div>
                                        <strong>Checked by:</strong><br>
                                        .........................
                                    </div>
                                    <div>
                                        <strong>Remarks:</strong><br>
                                        .........................
                                    </div>
                                    <div>
                                        <strong>Date:</strong><br>
                                        .........................
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif
            </div>

            <div class="info-row">
                <div class="label">Follower/s</div>
                <div class="colon">:</div>
            </div>
            <table class="table table-sm table-bordered" width="100%">
                <thead>
                    <tr>
                        <th class="text-center" width="5%">No.</th>
                        <th class="text-center">Name / NIK</th>
                        <th class="text-center">Title</th>
                        <th class="text-center">Business Unit</th>
                        <th class="text-center">Department</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($officialtravel->details->count() > 0)
                        @foreach ($officialtravel->details as $key => $detail)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>{{ $detail->follower->employee->fullname ?? 'N/A' }} /
                                    {{ $detail->follower->nik ?? 'N/A' }}</td>
                                <td>{{ $detail->follower->position->position_name ?? 'N/A' }}</td>
                                <td>{{ $detail->follower->project->project_name ?? 'N/A' }}</td>
                                <td>{{ $detail->follower->position->department->department_name ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center">No Follower Available</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div class="mt-4">
                <div class="info-row">
                    <div class="label">Note</div>
                    <div class="colon">:</div>
                </div>
                <div class="info-row">
                    <div class="label">Transportation</div>
                    <div class="colon">:</div>
                    <div class="value">
                        {{ $officialtravel->transportation->transportation_name }}</div>
                </div>
                <div class="info-row">
                    <div class="label">Acommodation</div>
                    <div class="colon">:</div>
                    <div class="value">
                        {{ $officialtravel->accommodation->accommodation_name }}
                    </div>
                </div>
            </div>

            <div class="signature-section">
                <div class="signature-date">
                    Balikpapan, {{ date('d F Y', strtotime($officialtravel->official_travel_date)) }}
                </div>
                <div class="signature-boxes">
                    <div class="signature-box">
                        <div>Approved by,</div>
                        <div style="margin-top: 70px">( Position )</div>
                    </div>
                    <div class="signature-box">
                        <div>Recommended by,</div>
                        <div style="margin-top: 70px">( Position )</div>
                    </div>
                </div>
            </div>

            <div class="footer">
                <div>ARKA/HCS/IV/04.01</div>
                <div>Rev.2</div>
                <div>Page 1/ 1</div>
            </div>
        </div>
    </body>

</html>
