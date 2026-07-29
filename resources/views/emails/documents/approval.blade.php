<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $headline }} - {{ $appName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f4f4;">
    <tr>
        <td align="center" style="padding: 20px 0;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="650"
                   style="max-width: 650px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <tr>
                    <td align="center" style="padding: 36px 40px 24px 40px; border-bottom: 3px solid #2c3e50;">
                        <h1 style="margin: 0; color: #2c3e50; font-size: 28px; font-weight: 300;">{{ $appName }}</h1>
                        <p style="margin: 8px 0 0 0; color: #666666; font-size: 16px;">{{ $headline }}</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 36px 40px;">
                        <p style="margin: 0 0 16px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                            Dear {{ $recipientName }},
                        </p>
                        <p style="margin: 0 0 24px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                            {{ $intro }}
                        </p>

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
                               style="margin: 0 0 24px 0; border: 1px solid #e5e5e5; border-radius: 6px;">
                            <tr>
                                <td style="padding: 12px 16px; background: #f8f9fa; font-weight: bold; width: 40%; color: #555;">Document</td>
                                <td style="padding: 12px 16px; color: #333;">{{ $documentLabel }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 16px; background: #f8f9fa; font-weight: bold; color: #555;">Reference</td>
                                <td style="padding: 12px 16px; color: #333;">{{ $reference }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 16px; background: #f8f9fa; font-weight: bold; color: #555;">Title</td>
                                <td style="padding: 12px 16px; color: #333;">{{ $title }}</td>
                            </tr>
                            @foreach($summary as $key => $value)
                                <tr>
                                    <td style="padding: 12px 16px; background: #f8f9fa; font-weight: bold; color: #555;">{{ $key }}</td>
                                    <td style="padding: 12px 16px; color: #333;">{{ $value ?? '—' }}</td>
                                </tr>
                            @endforeach
                            @if(!empty($approvalOrder))
                                <tr>
                                    <td style="padding: 12px 16px; background: #f8f9fa; font-weight: bold; color: #555;">Approval Step</td>
                                    <td style="padding: 12px 16px; color: #333;">#{{ $approvalOrder }}</td>
                                </tr>
                            @endif
                            @if(!empty($remarks))
                                <tr>
                                    <td style="padding: 12px 16px; background: #f8f9fa; font-weight: bold; color: #555;">Remarks</td>
                                    <td style="padding: 12px 16px; color: #333;">{{ $remarks }}</td>
                                </tr>
                            @endif
                        </table>

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                            <tr>
                                <td align="center" style="padding: 0 0 24px 0;">
                                    <a href="{{ $actionUrl }}" target="_blank"
                                       style="display: inline-block; background-color: #2c3e50; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-size: 16px; font-weight: bold;">
                                        {{ $cta }}
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin: 0; color: #888888; font-size: 13px; line-height: 1.5;">
                            If the button does not work, copy and paste this URL into your browser:<br>
                            <a href="{{ $actionUrl }}" style="color: #2c3e50; word-break: break-all;">{{ $actionUrl }}</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding: 20px 40px 30px 40px; border-top: 1px solid #eeeeee;">
                        <p style="margin: 0; color: #999999; font-size: 12px;">
                            This is an automated email from {{ $appName }}. Please do not reply.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
