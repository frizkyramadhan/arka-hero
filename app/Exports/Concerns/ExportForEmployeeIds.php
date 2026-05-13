<?php

namespace App\Exports\Concerns;

use Illuminate\Database\Query\Builder;

trait ExportForEmployeeIds
{
    public function __construct(
        protected Builder $employeeExportIdsQuery
    ) {}

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    protected function applyEmployeeIdFilter($query, string $column): void
    {
        $query->whereIn($column, $this->employeeExportIdsQuery->clone());
    }
}
