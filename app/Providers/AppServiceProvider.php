<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Administration;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Observers\AdministrationObserver;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();
        Carbon::setLocale('id');

        /*
         * PhpSpreadsheet throws on formulas that reference external workbooks/files
         * (common in spreadsheets edited with links to other files). Docker/Linux
         * cannot resolve Windows paths or missing linked files → 500 during import/export.
         * Suppressing formula errors uses cached values / #REF-style fallbacks instead of aborting.
         */
        $suppressFormulaErrorsOnSpreadsheet = static function (?Spreadsheet $spreadsheet): void {
            if ($spreadsheet !== null) {
                $spreadsheet->getCalculationEngine()->setSuppressFormulaErrors(true);
            }
        };

        Event::listen(BeforeImport::class, function (BeforeImport $event) use ($suppressFormulaErrorsOnSpreadsheet): void {
            $suppressFormulaErrorsOnSpreadsheet($event->reader->getDelegate());
        });

        Event::listen(BeforeExport::class, function (BeforeExport $event) use ($suppressFormulaErrorsOnSpreadsheet): void {
            $suppressFormulaErrorsOnSpreadsheet($event->writer->getDelegate());
        });

        Event::listen(BeforeWriting::class, function (BeforeWriting $event) use ($suppressFormulaErrorsOnSpreadsheet): void {
            $suppressFormulaErrorsOnSpreadsheet($event->writer->getDelegate());
        });

        // Register observers
        Administration::observe(AdministrationObserver::class);

        // Force URL scheme for subfolder/port setup
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));

            $scheme = parse_url(config('app.url'), PHP_URL_SCHEME);
            if ($scheme) {
                URL::forceScheme($scheme);
            }
        }
    }
}
