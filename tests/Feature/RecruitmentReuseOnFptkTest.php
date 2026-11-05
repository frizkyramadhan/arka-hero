<?php

namespace Tests\Feature;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentCvReview;
use App\Models\RecruitmentRequest;
use App\Models\RecruitmentSession;
use App\Services\RecruitmentSessionService;
use App\Support\UserProject;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RecruitmentReuseOnFptkTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        URL::forceRootUrl('http://localhost');
    }

    public function test_fulfilled_close_cancels_sibling_in_process_sessions(): void
    {
        $this->actingAs(\App\Models\User::findOrFail(1));
        $scope = UserProject::assignmentScope();

        $fptk = RecruitmentRequest::where('status', 'approved')
            ->when($scope !== null, fn ($q) => $q->whereIn('project_id', $scope))
            ->whereHas('activeSessions')
            ->first();

        if (! $fptk) {
            $this->markTestSkipped('No approved FPTK with in-process sessions');
        }

        $before = $fptk->activeSessions()->count();
        $this->assertGreaterThan(0, $before);

        $fptk->closeWith('fulfilled', null, 1);
        $fptk->refresh();

        $this->assertSame('closed', $fptk->status);
        $this->assertSame(0, $fptk->activeSessions()->count());
        $this->assertSame('fulfilled', $fptk->activeClosure->close_reason);
    }

    public function test_reuse_fresh_creates_session_and_closes_source(): void
    {
        $this->actingAs(\App\Models\User::findOrFail(1));
        $scope = UserProject::assignmentScope();

        $source = RecruitmentSession::with(['candidate', 'fptk'])
            ->whereIn('status', ['cancelled', 'rejected'])
            ->whereNotNull('fptk_id')
            ->whereHas('candidate', fn ($q) => $q->whereNotIn('global_status', ['hired', 'blacklisted']))
            ->first();

        if (! $source) {
            $this->markTestSkipped('No reusable source session');
        }

        $candidate = $source->candidate;
        $target = RecruitmentRequest::where('status', 'approved')
            ->when($scope !== null, fn ($q) => $q->whereIn('project_id', $scope))
            ->where('id', '!=', $source->fptk_id)
            ->whereNotIn('id', function ($q) use ($candidate) {
                $q->select('fptk_id')->from('recruitment_sessions')
                    ->where('candidate_id', $candidate->id)
                    ->whereNotNull('fptk_id');
            })
            ->first();

        if (! $target) {
            $this->markTestSkipped('No available target FPTK for reuse');
        }

        /** @var RecruitmentSessionService $service */
        $service = app(RecruitmentSessionService::class);
        $result = $service->reuseOnFptk($candidate, $target, $source, 'fresh');
        $newSession = $result['session'];

        $this->assertSame($target->id, $newSession->fptk_id);
        $this->assertSame($source->id, $newSession->source_session_id);
        $this->assertSame('in_process', $newSession->status);
        $this->assertTrue((float) $newSession->overall_progress < 20);

        $source->refresh();
        $this->assertSame('forwarded', $source->status);
        $this->assertStringContainsString('Forwarded to FPTK', (string) $source->final_decision_notes);

        $candidate->refresh();
        $this->assertSame('in_process', $candidate->global_status);
    }

    public function test_reuse_copy_recalculates_progress_for_target_flow(): void
    {
        $this->actingAs(\App\Models\User::findOrFail(1));
        $scope = UserProject::assignmentScope();

        $source = RecruitmentSession::with(['candidate', 'fptk', 'cvReview'])
            ->whereIn('status', ['cancelled', 'rejected'])
            ->whereNotNull('fptk_id')
            ->whereHas('cvReview', fn ($q) => $q->where('decision', 'recommended'))
            ->whereHas('candidate', fn ($q) => $q->whereNotIn('global_status', ['hired', 'blacklisted']))
            ->first();

        if (! $source) {
            // Create a minimal cancelled session with CV recommended on an existing FPTK/candidate pair
            $fptkA = RecruitmentRequest::where('status', 'closed')
                ->when($scope !== null, fn ($q) => $q->whereIn('project_id', $scope))
                ->first()
                ?? RecruitmentRequest::when($scope !== null, fn ($q) => $q->whereIn('project_id', $scope))->first();
            $candidate = RecruitmentCandidate::whereNotIn('global_status', ['hired', 'blacklisted'])->first();
            if (! $fptkA || ! $candidate) {
                $this->markTestSkipped('Cannot seed copy reuse scenario');
            }

            $existing = RecruitmentSession::where('fptk_id', $fptkA->id)->where('candidate_id', $candidate->id)->first();
            if ($existing) {
                $source = $existing;
            } else {
                $source = RecruitmentSession::create([
                    'session_number' => RecruitmentSession::generateSessionNumber(),
                    'fptk_id' => $fptkA->id,
                    'candidate_id' => $candidate->id,
                    'applied_date' => now()->toDateString(),
                    'source' => 'website',
                    'current_stage' => 'psikotes',
                    'stage_status' => 'pending',
                    'stage_started_at' => now(),
                    'overall_progress' => 14.3,
                    'status' => 'cancelled',
                    'final_decision_date' => now(),
                    'final_decision_notes' => 'FPTK fulfilled — another candidate hired',
                ]);
            }

            if (! $source->cvReview) {
                RecruitmentCvReview::create([
                    'session_id' => $source->id,
                    'decision' => 'recommended',
                    'notes' => 'test',
                    'reviewed_by' => 1,
                    'reviewed_at' => now(),
                ]);
            } elseif ($source->cvReview->decision !== 'recommended') {
                $source->cvReview->update(['decision' => 'recommended']);
            }

            if ($source->status === 'hired') {
                $this->markTestSkipped('Source session is hired');
            }
            if ($source->status === 'in_process') {
                $source->update(['status' => 'cancelled', 'final_decision_notes' => 'test cancel']);
            }

            $source->load(['candidate', 'fptk', 'cvReview']);
        }

        $candidate = $source->candidate;
        $target = RecruitmentRequest::where('status', 'approved')
            ->when($scope !== null, fn ($q) => $q->whereIn('project_id', $scope))
            ->where('id', '!=', $source->fptk_id)
            ->whereNotIn('employment_type', ['magang', 'harian'])
            ->whereNotIn('id', function ($q) use ($candidate) {
                $q->select('fptk_id')->from('recruitment_sessions')
                    ->where('candidate_id', $candidate->id)
                    ->whereNotNull('fptk_id');
            })
            ->first();

        if (! $target) {
            $this->markTestSkipped('No full-flow target FPTK for copy reuse');
        }

        /** @var RecruitmentSessionService $service */
        $service = app(RecruitmentSessionService::class);
        $result = $service->reuseOnFptk($candidate->fresh(), $target, $source->fresh(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring', 'documents', 'fptk']), 'copy');
        $newSession = $result['session']->fresh(['cvReview', 'fptk']);

        $this->assertNotNull($newSession->cvReview);
        $this->assertSame('recommended', $newSession->cvReview->decision);
        $this->assertSame($source->id, $newSession->source_session_id);

        $expected = $newSession->calculateActualProgress();
        $this->assertEqualsWithDelta($expected, (float) $newSession->overall_progress, 0.2);
        $this->assertGreaterThanOrEqual(14.0, (float) $newSession->overall_progress);
    }

    public function test_blacklisted_candidate_cannot_reuse(): void
    {
        $this->actingAs(\App\Models\User::findOrFail(1));

        $blacklisted = RecruitmentCandidate::where('global_status', 'blacklisted')->first();
        $target = RecruitmentRequest::where('status', 'approved')->first();
        $source = $blacklisted
            ? RecruitmentSession::where('candidate_id', $blacklisted->id)->first()
            : null;

        if (! $blacklisted || ! $target || ! $source) {
            $this->markTestSkipped('No blacklisted candidate scenario');
        }

        $this->expectException(\Exception::class);
        app(RecruitmentSessionService::class)->reuseOnFptk($blacklisted, $target, $source, 'fresh');
    }

    public function test_hired_magang_copy_to_full_flow_keeps_source_hired(): void
    {
        $this->actingAs(\App\Models\User::findOrFail(1));
        $scope = UserProject::assignmentScope();

        $source = RecruitmentSession::with(['candidate', 'fptk', 'mcu', 'hiring'])
            ->where('status', 'hired')
            ->whereNotNull('fptk_id')
            ->whereHas('fptk', fn ($q) => $q->whereIn('employment_type', ['magang', 'harian']))
            ->whereHas('mcu')
            ->whereHas('candidate', fn ($q) => $q->where('global_status', '!=', 'blacklisted'))
            ->first();

        if (! $source) {
            $magangFptk = RecruitmentRequest::when($scope !== null, fn ($q) => $q->whereIn('project_id', $scope))
                ->whereIn('employment_type', ['magang', 'harian'])
                ->first();
            $candidate = RecruitmentCandidate::where('global_status', '!=', 'blacklisted')->first();
            if (! $magangFptk || ! $candidate) {
                $this->markTestSkipped('Cannot seed hired magang reuse scenario');
            }

            $existing = RecruitmentSession::where('fptk_id', $magangFptk->id)
                ->where('candidate_id', $candidate->id)
                ->first();
            if ($existing) {
                $source = $existing;
            } else {
                $source = RecruitmentSession::create([
                    'session_number' => RecruitmentSession::generateSessionNumber(),
                    'fptk_id' => $magangFptk->id,
                    'candidate_id' => $candidate->id,
                    'applied_date' => now()->toDateString(),
                    'source' => 'website',
                    'current_stage' => 'hire',
                    'stage_status' => 'completed',
                    'stage_started_at' => now(),
                    'overall_progress' => 100,
                    'status' => 'hired',
                    'final_decision_date' => now(),
                ]);
            }

            $source->update([
                'status' => 'hired',
                'current_stage' => 'hire',
                'overall_progress' => 100,
                'final_decision_date' => $source->final_decision_date ?: now(),
            ]);

            if (! $source->mcu) {
                \App\Models\RecruitmentMcu::create([
                    'session_id' => $source->id,
                    'result' => 'fit',
                    'notes' => 'test mcu',
                    'reviewed_by' => 1,
                    'reviewed_at' => now(),
                ]);
            }

            if (! $source->hiring) {
                \App\Models\RecruitmentHiring::create([
                    'session_id' => $source->id,
                    'agreement_type' => 'magang',
                    'letter_number' => 'TEST-MAGANG-1',
                    'notes' => 'test hire',
                    'reviewed_by' => 1,
                    'reviewed_at' => now(),
                    'employee_id' => null,
                ]);
            }

            $source->load(['candidate', 'fptk', 'mcu', 'hiring']);
        }

        $candidate = $source->candidate;
        $target = RecruitmentRequest::where('status', 'approved')
            ->when($scope !== null, fn ($q) => $q->whereIn('project_id', $scope))
            ->where('id', '!=', $source->fptk_id)
            ->whereNotIn('employment_type', ['magang', 'harian'])
            ->whereNotIn('id', function ($q) use ($candidate) {
                $q->select('fptk_id')->from('recruitment_sessions')
                    ->where('candidate_id', $candidate->id)
                    ->whereNotNull('fptk_id');
            })
            ->first();

        if (! $target) {
            $this->markTestSkipped('No full-flow target FPTK for hired magang reuse');
        }

        $this->assertTrue($source->canBeReusedOnNewFptk());

        /** @var RecruitmentSessionService $service */
        $service = app(RecruitmentSessionService::class);
        $result = $service->reuseOnFptk(
            $candidate->fresh(),
            $target,
            $source->fresh(['fptk', 'mcu', 'hiring', 'documents']),
            'copy'
        );
        $newSession = $result['session']->fresh(['mcu', 'hiring', 'cvReview', 'fptk']);

        $source->refresh();
        $this->assertSame('hired', $source->status);
        $this->assertSame($source->id, $newSession->source_session_id);
        $this->assertSame('in_process', $newSession->status);
        $this->assertNotNull($newSession->mcu);
        $this->assertSame('fit', $newSession->mcu->result);
        $this->assertNotNull($newSession->hiring);
        $this->assertNull($newSession->hiring->employee_id);
        $this->assertNull($newSession->cvReview);
        $this->assertContains($newSession->current_stage, ['mcu', 'hire']);
        $this->assertStringContainsString('Transition Stage', (string) ($result['warning'] ?? ''));

        $candidate->refresh();
        $this->assertSame('in_process', $candidate->global_status);
    }
}
