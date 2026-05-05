<?php

namespace App\Http\Resources;

/**
 * LOT untuk workforce: resource yang sama; nested `employee` hanya `fullname`.
 */
class WorkforceOfficialtravelResource extends OfficialtravelResource
{
    protected function useWorkforceEmployeePayload(): bool
    {
        return true;
    }
}
