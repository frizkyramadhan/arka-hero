<?php

namespace App\Http\Resources;

/**
 * LOT untuk workforce: objek employee di bawah traveler/follower memakai subset field saja.
 */
class WorkforceOfficialtravelResource extends OfficialtravelResource
{
    protected function useWorkforceEmployeePayload(): bool
    {
        return true;
    }
}
