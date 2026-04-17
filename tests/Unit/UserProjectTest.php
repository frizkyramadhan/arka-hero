<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\UserProject;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Tests\TestCase;

class UserProjectTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_assignment_scope_follows_user_project_pivot(): void
    {
        $user = Mockery::mock(User::class)->makePartial();

        $rel = Mockery::mock(Builder::class);
        $rel->shouldReceive('pluck')->with('projects.id')->andReturn(collect([100, 200]));

        $user->shouldReceive('projects')->andReturn($rel);

        $this->assertSame([100, 200], UserProject::assignmentScope($user));
    }

    public function test_restricted_user_scope_matches_projects_pluck(): void
    {
        $user = Mockery::mock(User::class)->makePartial();

        $rel = Mockery::mock(Builder::class);
        $rel->shouldReceive('pluck')->with('projects.id')->andReturn(collect([5, 7]));

        $user->shouldReceive('projects')->andReturn($rel);

        $this->assertSame([5, 7], UserProject::assignmentScope($user));
        $this->assertTrue(UserProject::canAccessProjectId(5, $user));
        $this->assertFalse(UserProject::canAccessProjectId(99, $user));
    }

    public function test_empty_assignment_denies_access(): void
    {
        $user = Mockery::mock(User::class)->makePartial();

        $rel = Mockery::mock(Builder::class);
        $rel->shouldReceive('pluck')->with('projects.id')->andReturn(collect());

        $user->shouldReceive('projects')->andReturn($rel);

        $this->assertSame([], UserProject::assignmentScope($user));
        $this->assertFalse(UserProject::canAccessProjectId(1, $user));
    }
}
