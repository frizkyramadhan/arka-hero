<?php

namespace Tests\Feature;

use App\Models\Administration;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeLookupTest extends TestCase
{
    use DatabaseTransactions;

    public function test_lookup_returns_employee_id_name_and_active_nik()
    {
        URL::forceRootUrl('http://localhost');
        Administration::flushEventListeners();
        config(['services.api.key' => 'lookup-test-key']);
        $this->withHeader('X-API-Key', 'lookup-test-key');

        $user = User::create([
            'name' => 'Lookup Tester',
            'email' => 'lookup-'.Str::uuid().'@example.test',
            'password' => 'secret',
        ]);

        $employee = Employee::create([
            'fullname' => 'Lookup Contract Person Zz7863',
            'user_id' => $user->id,
        ]);

        Administration::create([
            'employee_id' => $employee->id,
            'nik' => 'ZX7863OLD',
            'is_active' => 0,
            'user_id' => $user->id,
        ]);

        $current = Administration::create([
            'employee_id' => $employee->id,
            'nik' => 'ZX7863NOW',
            'is_active' => 1,
            'user_id' => $user->id,
        ]);

        $byName = $this->getJson('/api/employees/lookup?q=Lookup%20Contract%20Person%20Zz7863');
        $byName->assertOk();
        $byName->assertJsonPath('data.0.id', $employee->id);
        $byName->assertJsonPath('data.0.name', 'Lookup Contract Person Zz7863');
        $byName->assertJsonPath('data.0.nik', 'ZX7863NOW');
        $this->assertNotSame((string) $current->id, $byName->json('data.0.id'));
        $this->assertSame(['id', 'name', 'nik'], array_keys($byName->json('data.0')));

        $byNik = $this->getJson('/api/employees/lookup?q=ZX7863NOW');
        $byNik->assertOk();
        $byNik->assertJsonPath('data.0.id', $employee->id);
        $byNik->assertJsonPath('data.0.nik', 'ZX7863NOW');

        $byOldNik = $this->getJson('/api/employees/lookup?q=ZX7863OLD');
        $byOldNik->assertOk();
        $this->assertSame([], $byOldNik->json('data'));

        $byId = $this->getJson('/api/employees/lookup?id='.$employee->id);
        $byId->assertOk();
        $byId->assertJsonPath('data.0.id', $employee->id);
        $byId->assertJsonPath('data.0.nik', 'ZX7863NOW');

        $this->getJson('/api/employees/lookup?id='.$current->id)->assertNotFound();
    }
}
