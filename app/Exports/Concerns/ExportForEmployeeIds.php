<?php

namespace App\Exports\Concerns;

trait ExportForEmployeeIds
{
    /**
     * @param  list<string>  $employeeIds
     */
    public function __construct(
        protected array $employeeIds = []
    ) {}

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    protected function applyEmployeeIdFilter($query, string $column): void
    {
        if ($this->employeeIds === []) {
            $query->whereRaw('0 = 1');

            return;
        }

        $query->whereIn($column, $this->employeeIds);
    }
}
