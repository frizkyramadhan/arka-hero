<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalNotification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_approval_id',
        'recipient_id',
        'notification_type',
        'sent_at',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    /**
     * Get the document approval for this notification.
     */
    public function documentApproval(): BelongsTo
    {
        return $this->belongsTo(DocumentApproval::class);
    }

    /**
     * Get the user who should receive this notification.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Check if this notification is for pending approval.
     */
    public function isPending(): bool
    {
        return $this->notification_type === 'pending';
    }

    /**
     * Check if this notification is for approved document.
     */
    public function isApproved(): bool
    {
        return $this->notification_type === 'approved';
    }

    /**
     * Check if this notification is for rejected document.
     */
    public function isRejected(): bool
    {
        return $this->notification_type === 'rejected';
    }

    /**
     * Check if this notification is for escalation.
     */
    public function isEscalation(): bool
    {
        return $this->notification_type === 'escalation';
    }

    /**
     * Check if this notification has been sent.
     */
    public function isSent(): bool
    {
        return !is_null($this->sent_at);
    }

    /**
     * Check if this notification has been read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if this notification is unread.
     */
    public function isUnread(): bool
    {
        return $this->isSent() && !$this->isRead();
    }

    /**
     * Mark this notification as sent.
     */
    public function markAsSent(): bool
    {
        return $this->update(['sent_at' => now()]);
    }

    /**
     * Mark this notification as read.
     */
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }

    /**
     * Get the notification description for display.
     */
    public function getNotificationDescription(): string
    {
        $descriptions = [
            'pending' => 'Pending Approval',
            'approved' => 'Document Approved',
            'rejected' => 'Document Rejected',
            'escalation' => 'Approval Escalation',
        ];

        return $descriptions[$this->notification_type] ?? ucfirst($this->notification_type);
    }

    /**
     * Get the notification message based on type.
     */
    public function getNotificationMessage(): string
    {
        $document = $this->documentApproval->getDocument();
        $documentTitle = $document ? $document->title ?? $document->id : 'Document';

        switch ($this->notification_type) {
            case 'pending':
                return "You have a pending approval for: {$documentTitle}";
            case 'approved':
                return "Your document has been approved: {$documentTitle}";
            case 'rejected':
                return "Your document has been rejected: {$documentTitle}";
            case 'escalation':
                return "Approval escalation required for: {$documentTitle}";
            default:
                return "Notification for: {$documentTitle}";
        }
    }

    /**
     * Scope a query to only include pending notifications.
     */
    public function scopePending($query)
    {
        return $query->where('notification_type', 'pending');
    }

    /**
     * Scope a query to only include approved notifications.
     */
    public function scopeApproved($query)
    {
        return $query->where('notification_type', 'approved');
    }

    /**
     * Scope a query to only include rejected notifications.
     */
    public function scopeRejected($query)
    {
        return $query->where('notification_type', 'rejected');
    }

    /**
     * Scope a query to only include escalation notifications.
     */
    public function scopeEscalation($query)
    {
        return $query->where('notification_type', 'escalation');
    }

    /**
     * Scope a query to only include sent notifications.
     */
    public function scopeSent($query)
    {
        return $query->whereNotNull('sent_at');
    }

    /**
     * Scope a query to only include unsent notifications.
     */
    public function scopeUnsent($query)
    {
        return $query->whereNull('sent_at');
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNotNull('sent_at')->whereNull('read_at');
    }
}
