<?php

namespace Database\Seeders;

use App\Models\SupplyItemCategory;
use App\Models\SupplyStockIn;
use App\Models\SupplyStockOut;
use App\Services\SupplyWorkbookImporter;
use Illuminate\Database\Seeder;

class SupplyWorkbookSeeder extends Seeder
{
    /**
     * One-time cutover import from GA Excel workbooks (ADR-0012).
     *
     * Usage:
     *   php artisan db:seed --class=SupplyWorkbookSeeder
     *   php artisan db:seed --class=SupplyWorkbookSeeder --force
     */
    public function run(): void
    {
        $force = (bool) $this->command?->option('force');

        if (SupplyWorkbookImporter::isAlreadyImported()) {
            if (! $force) {
                $this->command?->error('Supply workbook cutover already imported. Re-run with --force to replace cutover documents only.');

                return;
            }

            $this->command?->warn('Removing previous workbook cutover stock documents…');
            SupplyWorkbookImporter::wipeImport();
        }

        $context = SupplyWorkbookImporter::resolveContext();
        $project = $context['project'];
        $createdBy = $context['created_by'];

        $this->guardNoConflictingStock((int) $project->id, $force);

        $workbooks = [
            [
                'label' => 'ATK STOCK 2026 (GAA)',
                'path' => database_path('seeders/data/supplies/ATK_STOCK_2026.xlsx'),
                'prefix' => SupplyItemCategory::PREFIX_OFFICE_SUPPLY,
            ],
            [
                'label' => 'CONSUMABLE STOCK HO 2026 (GAC)',
                'path' => database_path('seeders/data/supplies/CONSUMABLE_STOCK_HO_2026.xlsx'),
                'prefix' => 'GAC',
            ],
        ];

        foreach ($workbooks as $workbook) {
            if (! is_readable($workbook['path'])) {
                $this->command?->error("Workbook not found: {$workbook['path']}");

                continue;
            }

            $category = SupplyItemCategory::query()
                ->where('prefix', $workbook['prefix'])
                ->firstOrFail();

            $this->command?->info("Importing {$workbook['label']}…");

            $importer = new SupplyWorkbookImporter(
                (int) $project->id,
                (string) $project->project_code,
                $createdBy,
                $category,
            );

            $stats = $importer->import($workbook['path'], $workbook['label']);

            $this->command?->table(
                ['Metric', 'Count'],
                [
                    ['Catalog items', $stats['catalog']],
                    ['Opening lines (Awal)', $stats['opening_lines']],
                    ['Stock In documents', $stats['stock_in_docs']],
                    ['Stock In lines', $stats['stock_in_lines']],
                    ['Stock Out documents', $stats['stock_out_docs']],
                    ['Stock Out lines', $stats['stock_out_lines']],
                    ['Reconciliation mismatches', $stats['reconciliation_mismatches']],
                ],
            );

            foreach ($stats['warnings'] as $warning) {
                $this->command?->warn($warning);
            }
        }

        $this->command?->info('Supply workbook cutover import finished for project '.$project->project_code.'.');
    }

    private function guardNoConflictingStock(int $projectId, bool $force): void
    {
        $hasOtherStock = SupplyStockIn::query()
                ->where('project_id', $projectId)
                ->where(fn ($q) => $q->whereNull('notes')->orWhere('notes', 'not like', '%'.SupplyWorkbookImporter::CUTOVER_MARKER.'%'))
                ->exists()
            || SupplyStockOut::query()
                ->where('project_id', $projectId)
                ->where(fn ($q) => $q->whereNull('notes')->orWhere('notes', 'not like', '%'.SupplyWorkbookImporter::CUTOVER_MARKER.'%'))
                ->exists();

        if ($hasOtherStock && ! $force) {
            $this->command?->error(
                'Project 000H already has non-workbook stock movements (e.g. test Supply Orders). '
                .'Remove them manually or re-run with --force after confirming balances.'
            );

            throw new \RuntimeException('Conflicting stock data on project 000H.');
        }

        if ($hasOtherStock && $force) {
            $this->command?->warn('Non-workbook stock exists on 000H — reconciliation may show mismatches for overlapping item codes.');
        }
    }
}
