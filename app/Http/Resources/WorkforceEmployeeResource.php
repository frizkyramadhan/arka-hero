<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Subset karyawan untuk API workforce (profil ringkas).
 */
class WorkforceEmployeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'emp_pob' => $this->emp_pob,
            'emp_dob' => $this->formatDate($this->emp_dob),
            'gender' => $this->gender,
            'address' => $this->address,
            'phone' => $this->phone,
        ];
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return (string) $value;
    }
}
