<?php

namespace App\Exports;

use App\Models\LetterNumber;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LetterAdministrationExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query()
    {
        return LetterNumber::query()->with([
            'category',
            'subject',
            'administration.employee',
            'administration.project',
            'reservedBy',
            'usedBy'
        ])->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'id',
            'year',
            'project_code',
            'letter_number',
            'sequence_number',
            'category_code',
            'category_name',
            'letter_date',
            'status',
            'destination',
            'subject_master',
            'subject_custom',
            'remarks',
            'nik',
            'employee_name',
            'duration',
            'start_date',
            'end_date',
            'classification',
            'pkwt_type',
            'par_type',
            'ticket_classification',
            'reserved_by',
            'used_by',
            'used_at',
        ];
    }

    public function map($letterNumber): array
    {
        // Determine project code - prioritize direct project_code, then project relationship, then administration project
        $projectCode = $letterNumber->project_code ??
            $letterNumber->project?->project_code ??
            $letterNumber->administration?->project?->project_code;

        return [
            $letterNumber->id,
            $letterNumber->year ?? (($letterNumber->letter_date ? date('Y', strtotime($letterNumber->letter_date)) : date('Y'))),
            $projectCode,
            $letterNumber->letter_number,
            $letterNumber->sequence_number,
            $letterNumber->category?->category_code,
            $letterNumber->category_name,
            $letterNumber->letter_date ? date('d F Y', strtotime($letterNumber->letter_date)) : '',
            $letterNumber->status,
            $letterNumber->destination,
            $letterNumber->subject?->subject_name,
            $letterNumber->custom_subject,
            $letterNumber->remarks,
            $letterNumber->administration?->nik,
            $letterNumber->administration?->employee?->fullname,
            $letterNumber->duration,
            $letterNumber->start_date ? date('d F Y', strtotime($letterNumber->start_date)) : '',
            $letterNumber->end_date ? date('d F Y', strtotime($letterNumber->end_date)) : '',
            $letterNumber->classification,
            $letterNumber->pkwt_type,
            $letterNumber->par_type,
            $letterNumber->ticket_classification,
            $letterNumber->reservedBy?->name,
            $letterNumber->usedBy?->name,
            $letterNumber->used_at,
        ];
    }
}
