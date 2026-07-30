{{ $appName }} — {{ $headline }}

Dear {{ $recipientName }},

{{ $intro }}

Document: {{ $documentLabel }}
Reference: {{ $reference }}
{{ $titleLabel ?? 'Title' }}: {{ $title }}
@if (!empty($approvalOrder))
Approval Step: #{{ $approvalOrder }}
@endif
@if (!empty($remarks))
Remarks: {{ $remarks }}
@endif
@if (!empty($summary) && is_array($summary))
@foreach ($summary as $key => $value)
{{ $key }}: {{ is_scalar($value) || $value === null ? ($value ?? '—') : json_encode($value) }}
@endforeach
@endif

Open in ARKA HERO ({{ $cta }}):
{{ $actionUrl }}

—
This message was sent automatically by {{ $appName }} for a document approval workflow.
If you received this by mistake, you can ignore it or contact your system administrator.
Do not reply to this email.
