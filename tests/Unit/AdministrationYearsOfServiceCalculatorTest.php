<?php

namespace Tests\Unit;

use App\Services\AdministrationYearsOfServiceCalculator;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class AdministrationYearsOfServiceCalculatorTest extends TestCase
{
    private AdministrationYearsOfServiceCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new AdministrationYearsOfServiceCalculator;
    }

    public function test_lsl_period_for_staff_is_five_years_from_service_start_doh(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-20'));

        $administration = (object) [
            'id' => 1,
            'doh' => '2016-07-18',
            'termination_date' => null,
            'termination_reason' => null,
        ];

        $period = $this->calculator->calculateLSLPeriodDates(
            $administration,
            new Collection([$administration]),
            5
        );

        $this->assertNotNull($period);
        $this->assertSame('2021-07-18', $period['start']->toDateString());
        $this->assertSame('2026-07-17', $period['end']->toDateString());

        Carbon::setTestNow();
    }

    public function test_lsl_period_for_non_staff_is_six_years_from_service_start_doh(): void
    {
        Carbon::setTestNow(Carbon::parse('2027-01-01'));

        $administration = (object) [
            'id' => 1,
            'doh' => '2016-07-18',
            'termination_date' => null,
            'termination_reason' => null,
        ];

        $period = $this->calculator->calculateLSLPeriodDates(
            $administration,
            new Collection([$administration]),
            6
        );

        $this->assertNotNull($period);
        $this->assertSame('2022-07-18', $period['start']->toDateString());
        $this->assertSame('2028-07-17', $period['end']->toDateString());

        Carbon::setTestNow();
    }

    public function test_lsl_period_returns_null_before_eligibility(): void
    {
        Carbon::setTestNow(Carbon::parse('2020-01-01'));

        $administration = (object) [
            'id' => 1,
            'doh' => '2016-07-18',
            'termination_date' => null,
            'termination_reason' => null,
        ];

        $period = $this->calculator->calculateLSLPeriodDates(
            $administration,
            new Collection([$administration]),
            5
        );

        $this->assertNull($period);

        Carbon::setTestNow();
    }

    public function test_service_start_resets_after_non_end_of_contract_termination(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-20'));

        $firstAdmin = (object) [
            'id' => 1,
            'doh' => '2010-01-01',
            'termination_date' => '2015-06-30',
            'termination_reason' => 'Resign',
        ];

        $secondAdmin = (object) [
            'id' => 2,
            'doh' => '2016-07-18',
            'termination_date' => null,
            'termination_reason' => null,
        ];

        $startDoh = $this->calculator->getServiceStartDoh(
            $secondAdmin,
            new Collection([$firstAdmin, $secondAdmin])
        );

        $this->assertSame('2016-07-18', $startDoh->toDateString());

        Carbon::setTestNow();
    }
}
