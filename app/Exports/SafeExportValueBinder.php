<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

/**
 * A value binder that prevents any database string starting with '=' from being
 * mis-interpreted as a spreadsheet formula by PhpSpreadsheet.
 *
 * Standard Excel formula prefixes that PhpSpreadsheet would otherwise evaluate:
 *   =   (formula)   +   -   @   (legacy Lotus / DDE triggers)
 *
 * Storing such data as explicit TYPE_STRING avoids "Unable to access External Workbook"
 * and similar Calculation exceptions when the value contains formula-like patterns.
 */
class SafeExportValueBinder extends DefaultValueBinder
{
    /** Characters that make PhpSpreadsheet treat a string as a formula / trigger. */
    private const FORMULA_STARTERS = ['=', '+', '-', '@'];

    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value) && $value !== '' && in_array($value[0], self::FORMULA_STARTERS, true)) {
            // Force plain text so the calculation engine is never invoked.
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
