<?php

namespace Tests\Unit;

use App\Exports\OfferAcceptanceRateExport;
use App\Models\LetterNumber;
use App\Models\RecruitmentOffering;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Tests\TestCase;

class OfferAcceptanceRateExportTest extends TestCase
{
    public function test_timing_counts_calendar_days_between_offer_and_response(): void
    {
        $letter = new LetterNumber;
        $letter->letter_date = '2026-05-01';

        $offering = new RecruitmentOffering;
        $offering->setRelation('letterNumber', $letter);
        $offering->created_at = '2026-11-05 22:30:00';
        $offering->reviewed_at = '2026-05-04 01:15:00';

        $timing = $offering->acceptanceTiming();

        $this->assertSame('2026-05-01', $timing['offering_date']->toDateString());
        $this->assertSame('2026-05-04', $timing['response_date']->toDateString());
        $this->assertSame(3, $timing['response_days']);

        $withoutLetter = new RecruitmentOffering;
        $withoutLetter->setRelation('letterNumber', null);
        $withoutLetter->reviewed_at = '2026-05-04 01:15:00';
        $missing = $withoutLetter->acceptanceTiming();

        $this->assertNull($missing['offering_date']);
        $this->assertSame('2026-05-04', $missing['response_date']->toDateString());
        $this->assertNull($missing['response_days']);
        $this->assertSame('3 Day(s)', RecruitmentOffering::responseTimeLabel(3));
        $this->assertSame('0 Day(s)', RecruitmentOffering::responseTimeLabel(0));
        $this->assertSame('-', RecruitmentOffering::responseTimeLabel(null));
    }

    public function test_excel_keeps_dates_and_day_unit_as_text(): void
    {
        $export = new OfferAcceptanceRateExport([[
            'request_no' => 'FPTK-1',
            'department' => 'HR',
            'position' => 'Staff',
            'project' => 'HO',
            'candidate_name' => 'Ada',
            'offering_date' => '24/11/2026',
            'response_date' => '27/11/2026',
            'response_time' => 3,
            'response' => 'Accepted',
            'offering_letter_no' => 'A0001/ARKA-HCS/XI/2026',
            'notes' => '-',
        ]]);

        $path = tempnam(sys_get_temp_dir(), 'oar').'.xlsx';
        file_put_contents($path, Excel::raw($export, ExcelFormat::XLSX));

        $sheet = IOFactory::load($path)->getActiveSheet();

        $this->assertSame('24/11/2026', $sheet->getCell('F2')->getValue());
        $this->assertSame('27/11/2026', $sheet->getCell('G2')->getValue());
        $this->assertSame('3 Day(s)', $sheet->getCell('H2')->getValue());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('F2')->getDataType());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('G2')->getDataType());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('H2')->getDataType());
        $this->assertSame(NumberFormat::FORMAT_TEXT, $sheet->getStyle('F2')->getNumberFormat()->getFormatCode());
        $this->assertSame(NumberFormat::FORMAT_TEXT, $sheet->getStyle('H2')->getNumberFormat()->getFormatCode());

        @unlink($path);
    }
}
