<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequestDetail extends Model
{
    protected $table = 'overtime_request_details';

    protected $fillable = [
        'overtime_request_id',
        'administration_id',
        'time_in',
        'time_out',
        'work_description',
        'sort_order',
    ];

    public function overtimeRequest(): BelongsTo
    {
        return $this->belongsTo(OvertimeRequest::class, 'overtime_request_id');
    }

    public function administration(): BelongsTo
    {
        return $this->belongsTo(Administration::class);
    }
}
