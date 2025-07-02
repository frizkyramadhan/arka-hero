# Contoh Implementasi Fitur Baru: Employee Contract

## 1. Migration - Employee Contracts Table

```php
<?php
// database/migrations/2025_xx_xx_create_employee_contracts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Letter numbering integration - WAJIB
            $table->foreignId('letter_number_id')->nullable()->constrained('letter_numbers');
            $table->string('letter_number', 50)->nullable();

            // Contract specific fields
            $table->string('contract_number')->nullable(); // Internal numbering jika ada
            $table->date('contract_date');
            $table->foreignId('administration_id')->constrained('administrations');
            $table->string('position');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('salary', 15, 2);
            $table->text('terms_conditions')->nullable();
            $table->enum('contract_type', ['PKWT', 'PKWTT']);
            $table->enum('status', ['draft', 'active', 'expired', 'terminated'])->default('draft');
            $table->text('remarks')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_contracts');
    }
};
```

## 2. Model - EmployeeContract

```php
<?php
// app/Models/EmployeeContract.php

namespace App\Models;

use App\Traits\Uuids;
use App\Traits\HasLetterNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeContract extends Model
{
    use HasFactory;
    use Uuids;
    use HasLetterNumber; // Trait untuk letter number integration

    protected $guarded = [];

    protected $dates = ['contract_date', 'start_date', 'end_date'];

    protected $casts = [
        'contract_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'salary' => 'decimal:2',
    ];

    // Relationships
    public function administration()
    {
        return $this->belongsTo(Administration::class);
    }

    public function employee()
    {
        return $this->hasOneThrough(Employee::class, Administration::class, 'id', 'id', 'administration_id', 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Implementation dari HasLetterNumber trait
    protected function getDocumentType(): string
    {
        return 'employee_contract';
    }

    // Accessors & Mutators
    public function getEmployeeNameAttribute()
    {
        return $this->administration && $this->administration->employee ?
            $this->administration->employee->fullname : null;
    }

    public function getDurationAttribute()
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInMonths($this->end_date) . ' months';
        }
        return null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByContractType($query, $type)
    {
        return $query->where('contract_type', $type);
    }
}
```

## 3. Controller - EmployeeContractController

```php
<?php
// app/Http/Controllers/EmployeeContractController.php

namespace App\Http\Controllers;

use App\Models\EmployeeContract;
use App\Models\Administration;
use App\Http\Controllers\BaseDocumentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeContractController extends BaseDocumentController
{
    // Abstract method implementations
    protected function getDocumentType(): string
    {
        return 'employee_contract';
    }

    protected function getDefaultCategory(): string
    {
        return 'PKWT'; // Employment contract category
    }

    protected function getModelClass(): string
    {
        return EmployeeContract::class;
    }

    protected function getAdministrationId(Request $request)
    {
        return $request->administration_id;
    }

    protected function getPurpose(Request $request)
    {
        return "Employment Contract - {$request->position} - {$request->contract_type}";
    }

    protected function getLetterDate(Request $request)
    {
        return $request->contract_date;
    }

    protected function getStartDate(Request $request)
    {
        return $request->start_date;
    }

    protected function getEndDate(Request $request)
    {
        return $request->end_date;
    }

    protected function getRemarks(Request $request)
    {
        return "Employee Contract for {$request->position}";
    }

    public function index()
    {
        $title = 'Employee Contracts';
        $subtitle = 'Manage Employee Contracts';

        return view('employee-contracts.index', compact('title', 'subtitle'));
    }

    public function create()
    {
        $title = 'Create Employee Contract';
        $subtitle = 'Add New Employment Contract';

        // Load required data
        $employees = Administration::with(['employee', 'project', 'position'])
            ->where('is_active', 1)
            ->get()
            ->map(function ($admin) {
                return [
                    'id' => $admin->id,
                    'nik' => $admin->nik,
                    'fullname' => $admin->employee->fullname ?? 'Unknown',
                    'project' => $admin->project->project_name ?? 'Unknown',
                    'position' => $admin->position->position_name ?? 'Unknown',
                ];
            });

        // Load available letter numbers untuk PKWT category
        $availableLetterNumbers = $this->loadAvailableLetterNumbers('PKWT');

        return view('employee-contracts.create', compact(
            'title',
            'subtitle',
            'employees',
            'availableLetterNumbers'
        ));
    }

    public function store(Request $request)
    {
        // Merge letter number validation rules dengan document specific rules
        $rules = array_merge([
            'contract_date' => 'required|date',
            'administration_id' => 'required|exists:administrations,id',
            'position' => 'required|string|max:200',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'salary' => 'required|numeric|min:0',
            'contract_type' => 'required|in:PKWT,PKWTT',
            'terms_conditions' => 'nullable|string',
            'remarks' => 'nullable|string',
        ], $this->getLetterNumberValidationRules());

        $request->validate($rules);

        DB::beginTransaction();
        try {
            // Create contract
            $contract = EmployeeContract::create([
                'contract_date' => $request->contract_date,
                'administration_id' => $request->administration_id,
                'position' => $request->position,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'salary' => $request->salary,
                'contract_type' => $request->contract_type,
                'terms_conditions' => $request->terms_conditions,
                'remarks' => $request->remarks,
                'created_by' => auth()->id(),
            ]);

            // Handle letter number integration
            $this->handleLetterNumberIntegration($request, $contract);

            DB::commit();

            return $this->successResponse(
                'Employee contract created successfully',
                'employee-contracts.index'
            );

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to create contract: ' . $e->getMessage());
        }
    }

    public function edit(EmployeeContract $employeeContract)
    {
        $title = 'Edit Employee Contract';
        $subtitle = 'Update Employment Contract';

        $employees = Administration::with(['employee', 'project', 'position'])
            ->where('is_active', 1)
            ->get()
            ->map(function ($admin) {
                return [
                    'id' => $admin->id,
                    'nik' => $admin->nik,
                    'fullname' => $admin->employee->fullname ?? 'Unknown',
                    'project' => $admin->project->project_name ?? 'Unknown',
                    'position' => $admin->position->position_name ?? 'Unknown',
                ];
            });

        $availableLetterNumbers = $this->loadAvailableLetterNumbers('PKWT');

        return view('employee-contracts.edit', compact(
            'title',
            'subtitle',
            'employeeContract',
            'employees',
            'availableLetterNumbers'
        ));
    }

    public function update(Request $request, EmployeeContract $employeeContract)
    {
        $rules = array_merge([
            'contract_date' => 'required|date',
            'administration_id' => 'required|exists:administrations,id',
            'position' => 'required|string|max:200',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'salary' => 'required|numeric|min:0',
            'contract_type' => 'required|in:PKWT,PKWTT',
            'terms_conditions' => 'nullable|string',
            'remarks' => 'nullable|string',
        ], $this->getLetterNumberValidationRules());

        $request->validate($rules);

        DB::beginTransaction();
        try {
            // Update contract
            $employeeContract->update([
                'contract_date' => $request->contract_date,
                'administration_id' => $request->administration_id,
                'position' => $request->position,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'salary' => $request->salary,
                'contract_type' => $request->contract_type,
                'terms_conditions' => $request->terms_conditions,
                'remarks' => $request->remarks,
            ]);

            // Handle letter number integration untuk update
            if ($request->has('number_option')) {
                // Release existing letter number if changing
                if ($employeeContract->hasLetterNumber() &&
                    $request->letter_number_id != $employeeContract->letter_number_id) {
                    $employeeContract->releaseLetterNumber();
                }

                $this->handleLetterNumberIntegration($request, $employeeContract);
            }

            DB::commit();

            return $this->successResponse(
                'Employee contract updated successfully',
                'employee-contracts.index'
            );

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to update contract: ' . $e->getMessage());
        }
    }

    public function destroy(EmployeeContract $employeeContract)
    {
        try {
            // Letter number akan otomatis di-release karena boot method di trait
            $employeeContract->delete();

            return response()->json([
                'success' => true,
                'message' => 'Employee contract deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete contract: ' . $e->getMessage()
            ], 500);
        }
    }
}
```

## 4. View - Create Form

```blade
{{-- resources/views/employee-contracts/create.blade.php --}}
@extends('layouts.main')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $title }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('employee-contracts.index') }}">Employee Contracts</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form action="{{ route('employee-contracts.store') }}" method="POST" id="contractForm">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <!-- Contract Information Card -->
                    <div class="card card-primary card-outline elevation-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-contract mr-2"></i>
                                <strong>Contract Information</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contract_date">Contract Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('contract_date') is-invalid @enderror"
                                               name="contract_date" value="{{ old('contract_date', now()->format('Y-m-d')) }}" required>
                                        @error('contract_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contract_type">Contract Type <span class="text-danger">*</span></label>
                                        <select class="form-control @error('contract_type') is-invalid @enderror"
                                                name="contract_type" required>
                                            <option value="">Select Type</option>
                                            <option value="PKWT" {{ old('contract_type') == 'PKWT' ? 'selected' : '' }}>PKWT (Fixed Term)</option>
                                            <option value="PKWTT" {{ old('contract_type') == 'PKWTT' ? 'selected' : '' }}>PKWTT (Permanent)</option>
                                        </select>
                                        @error('contract_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="administration_id">Employee <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('administration_id') is-invalid @enderror"
                                        name="administration_id" style="width: 100%;" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee['id'] }}"
                                                {{ old('administration_id') == $employee['id'] ? 'selected' : '' }}>
                                            {{ $employee['nik'] }} - {{ $employee['fullname'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('administration_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="position">Position <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('position') is-invalid @enderror"
                                               name="position" value="{{ old('position') }}" required>
                                        @error('position')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="salary">Salary <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('salary') is-invalid @enderror"
                                               name="salary" value="{{ old('salary') }}" min="0" step="0.01" required>
                                        @error('salary')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                               name="start_date" value="{{ old('start_date') }}" required>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_date">End Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                               name="end_date" value="{{ old('end_date') }}" required>
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="terms_conditions">Terms & Conditions</label>
                                <textarea class="form-control @error('terms_conditions') is-invalid @enderror"
                                          name="terms_conditions" rows="4">{{ old('terms_conditions') }}</textarea>
                                @error('terms_conditions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="remarks">Remarks</label>
                                <textarea class="form-control @error('remarks') is-invalid @enderror"
                                          name="remarks" rows="3">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Letter Number Integration -->
                    @include('components.letter-number-selector', [
                        'category' => 'PKWT',
                        'availableNumbers' => $availableLetterNumbers ?? [],
                        'defaultSubject' => 'Employment Contract'
                    ])

                    <!-- Action Buttons -->
                    <div class="card elevation-3">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-2"></i> Create Contract
                            </button>
                            <a href="{{ route('employee-contracts.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-times-circle mr-2"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Select an option'
    });

    // Form validation
    $('#contractForm').on('submit', function(e) {
        let isValid = true;

        // Check required fields
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Check date logic
        const startDate = new Date($('input[name="start_date"]').val());
        const endDate = new Date($('input[name="end_date"]').val());

        if (startDate && endDate && endDate <= startDate) {
            isValid = false;
            $('input[name="end_date"]').addClass('is-invalid');
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date',
                text: 'End date must be after start date'
            });
        }

        if (!isValid) {
            e.preventDefault();
            if (!$(this).find('.is-invalid').length) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields marked with *'
                });
            }
        }
    });
});
</script>
@endsection
```

## 5. Routes

```php
// routes/web.php - tambahkan route untuk employee contracts

Route::middleware(['auth'])->group(function () {
    // ... existing routes ...

    // Employee Contract Routes
    Route::resource('employee-contracts', EmployeeContractController::class)->names([
        'index' => 'employee-contracts.index',
        'create' => 'employee-contracts.create',
        'store' => 'employee-contracts.store',
        'show' => 'employee-contracts.show',
        'edit' => 'employee-contracts.edit',
        'update' => 'employee-contracts.update',
        'destroy' => 'employee-contracts.destroy',
    ]);
});
```

## Summary

Dengan framework ini, membuat fitur baru yang terintegrasi dengan letter numbering system menjadi sangat mudah:

1. **Migration**: Tambahkan field `letter_number_id` dan `letter_number`
2. **Model**: Gunakan trait `HasLetterNumber` dan implementasi `getDocumentType()`
3. **Controller**: Extend `BaseDocumentController` dan implementasi abstract methods
4. **View**: Gunakan component `letter-number-selector`
5. **Routes**: Tambahkan routes standar Laravel resource

Semua logic integration sudah dihandle oleh framework, developer hanya fokus pada business logic specific dokumen tersebut.
