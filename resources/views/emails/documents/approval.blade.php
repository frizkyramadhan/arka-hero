<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no,date=no,address=no,email=no,url=no">
    <title>{{ $headline }} - {{ $appName }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        html, body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        table { border-collapse: collapse !important; }
        a[x-apple-data-detectors], #MessageViewBody a { color: inherit !important; text-decoration: none !important; }
        @media only screen and (max-width: 620px) {
            .email-shell { width: 100% !important; }
            .email-padding { padding-left: 16px !important; padding-right: 16px !important; }
            .detail-label { width: 36% !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; width: 100%; background-color: #f3efe9; font-family: Arial, Helvetica, sans-serif;">
@php
    $emailUi = require resource_path('views/emails/documents/partials/ui_tokens.php');
@endphp
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" bgcolor="#f3efe9" style="width: 100%; background-color: #f3efe9;">
    <tr>
        <td align="center" valign="top" style="padding: 16px 8px;">
            <!--[if mso]>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600"><tr><td>
            <![endif]-->
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" bgcolor="#ffffff"
                   class="email-shell" style="width: 100%; max-width: 600px; background-color: #ffffff; border: 1px solid #e7e1d8;">
                <tr>
                    <td align="center" class="email-padding" style="padding: 16px 22px 12px; border-bottom: 2px solid #d9d0c4; background-color: #faf8f5;">
                        @if (!empty($logoUrl))
                            <img src="{{ $logoUrl }}" alt="ARKA logo" width="160" height="40"
                                 style="display: block; margin: 0 auto 8px auto; border: 0; outline: none; text-decoration: none; max-width: 160px; height: auto;">
                        @endif
                        <div style="margin: 0; color: #3f4a55; font-size: 18px; line-height: 22px; font-weight: bold;">{{ $appName }}</div>
                        <div style="margin-top: 4px; color: #7a746c; font-size: 13px; line-height: 18px;">{{ $headline }} — {{ $documentLabel }} {{ $reference }}</div>
                    </td>
                </tr>                <tr>
                    <td class="email-padding" style="padding: 16px 22px 18px;">
                        <p style="margin: 0 0 6px 0; color: #2f2a26; font-size: 14px; line-height: 1.45;">
                            Dear {{ $recipientName }},
                        </p>
                        <p style="margin: 0 0 14px 0; color: #5c564e; font-size: 13px; line-height: 1.45;">
                            {{ $intro }}
                        </p>

                        @if (($documentType ?? null) === 'officialtravel' && isset($notifiableDocument))
                            @include('emails.documents.partials.officialtravel', [
                                'officialtravel' => $notifiableDocument,
                                'reference' => $reference,
                                'approvalOrder' => $approvalOrder ?? null,
                                'remarks' => $remarks ?? null,
                                'emailUi' => $emailUi,
                            ])
                        @elseif (($documentType ?? null) === 'flight_request' && isset($notifiableDocument))
                            @include('emails.documents.partials.flight_request', [
                                'flightRequest' => $notifiableDocument,
                                'reference' => $reference,
                                'approvalOrder' => $approvalOrder ?? null,
                                'remarks' => $remarks ?? null,
                                'emailUi' => $emailUi,
                            ])
                        @elseif (($documentType ?? null) === 'flight_request_issuance' && isset($notifiableDocument))
                            @include('emails.documents.partials.flight_request_issuance', [
                                'issuance' => $notifiableDocument,
                                'reference' => $reference,
                                'approvalOrder' => $approvalOrder ?? null,
                                'remarks' => $remarks ?? null,
                                'emailUi' => $emailUi,
                            ])
                        @elseif (($documentType ?? null) === 'overtime_request' && isset($notifiableDocument))
                            @include('emails.documents.partials.overtime_request', [
                                'overtimeRequest' => $notifiableDocument,
                                'reference' => $reference,
                                'approvalOrder' => $approvalOrder ?? null,
                                'remarks' => $remarks ?? null,
                                'emailUi' => $emailUi,
                            ])
                        @elseif (($documentType ?? null) === 'leave_request' && isset($notifiableDocument))
                            @include('emails.documents.partials.leave_request', [
                                'leaveRequest' => $notifiableDocument,
                                'reference' => $reference,
                                'approvalOrder' => $approvalOrder ?? null,
                                'remarks' => $remarks ?? null,
                                'emailUi' => $emailUi,
                            ])
                        @elseif (($documentType ?? null) === 'recruitment_request' && isset($notifiableDocument))
                            @include('emails.documents.partials.recruitment_request', [
                                'recruitmentRequest' => $notifiableDocument,
                                'reference' => $reference,
                                'approvalOrder' => $approvalOrder ?? null,
                                'remarks' => $remarks ?? null,
                                'emailUi' => $emailUi,
                            ])
                        @elseif (($documentType ?? null) === 'room_consumption_request' && isset($notifiableDocument))
                            @include('emails.documents.partials.room_consumption_request', [
                                'roomConsumptionRequest' => $notifiableDocument,
                                'reference' => $reference,
                                'approvalOrder' => $approvalOrder ?? null,
                                'remarks' => $remarks ?? null,
                                'emailUi' => $emailUi,
                            ])
                        @else
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
                               style="{{ $emailUi['section'] }}">
                            <tr>
                                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Document</td>
                                <td style="{{ $emailUi['value'] }}">{{ $documentLabel }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Reference</td>
                                <td style="{{ $emailUi['value'] }}">{{ $reference }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">{{ $titleLabel ?? 'Title' }}</td>
                                <td style="{{ $emailUi['value'] }}">{{ $title }}</td>
                            </tr>
                            @foreach($summary as $key => $value)
                                <tr>
                                    <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">{{ $key }}</td>
                                    <td style="{{ $emailUi['value'] }}">{{ $value ?? '—' }}</td>
                                </tr>
                            @endforeach
                            @if(!empty($approvalOrder))
                                <tr>
                                    <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ !empty($remarks) ? $emailUi['label'] : $emailUi['labelLast'] }}">Approval Step</td>
                                    <td style="{{ !empty($remarks) ? $emailUi['value'] : $emailUi['valueLast'] }}">#{{ $approvalOrder }}</td>
                                </tr>
                            @endif
                            @if(!empty($remarks))
                                <tr>
                                    <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Remarks</td>
                                    <td style="{{ $emailUi['valueLast'] }}">{{ $remarks }}</td>
                                </tr>
                            @endif
                        </table>
                        @endif

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                            <tr>
                                <td align="center" style="padding: 4px 0 12px 0;">
                                    <!--[if mso]>
                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"
                                        href="{{ $actionUrl }}" style="height:38px;v-text-anchor:middle;width:180px;" arcsize="12%"
                                        strokecolor="#3f4a55" fillcolor="#3f4a55">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;">
                                            {{ $cta }}
                                        </center>
                                    </v:roundrect>
                                    <![endif]-->
                                    <!--[if !mso]><!-->
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td align="center" bgcolor="#3f4a55" style="background-color: #3f4a55; border-radius: 6px;">
                                                <a href="{{ $actionUrl }}" target="_blank"
                                                   style="display: inline-block; min-width: 120px; padding: 10px 22px; color: #ffffff; text-decoration: none; font-size: 13px; line-height: 16px; font-weight: bold;">
                                                    {{ $cta }}
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    <!--<![endif]-->
                                </td>
                            </tr>
                        </table>

                        <p style="margin: 0; color: #8a847c; font-size: 11px; line-height: 1.4;">
                            If the button does not work, copy and paste this URL into your browser:<br>
                            <a href="{{ $actionUrl }}" style="color: #3f4a55; text-decoration: underline; word-break: break-all; overflow-wrap: anywhere;">{{ $actionUrl }}</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" class="email-padding" style="padding: 12px 22px 14px; border-top: 1px solid #eee8e0; background-color: #faf8f5;">
                        <p style="margin: 0; color: #8a847c; font-size: 11px; line-height: 16px;">
                            This is an automated email from {{ $appName }}. Please do not reply.
                        </p>
                    </td>
                </tr>
            </table>
            <!--[if mso]>
            </td></tr></table>
            <![endif]-->
        </td>
    </tr>
</table>
</body>
</html>
