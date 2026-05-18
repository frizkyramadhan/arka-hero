<?php

namespace App\Exports\Concerns;

trait ExportForEmployeeIds
{
    /**
     * @param  int[]|string[]  $employeeIds
     */
    public function __construct(
        protected array $employeeIds
    ) {}

    /**
     * Apply a WHERE employee-id filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    protected function applyEmployeeIdFilter($query, string $column): void
    {
        if (empty($this->employeeIds)) {
            $query->whereRaw('0 = 1');

            return;
        }

        $query->whereIn($column, $this->employeeIds);
    }
}
