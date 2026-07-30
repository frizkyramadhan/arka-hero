<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNotificationSend extends Model
{
    protected $fillable = [
        'document_type',
        'document_id',
        'event',
        'recipient_user_id',
        'approval_plan_id',
        'dedupe_day',
    ];
}
