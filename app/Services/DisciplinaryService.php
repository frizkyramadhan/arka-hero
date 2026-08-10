<?php

namespace App\Services;

use App\Models\Administration;
use App\Models\DisciplinaryCriterion;
use App\Models\Employee;
use App\Models\EmployeeDisciplinary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DisciplinaryService
{
    public const SP_RANKS = [
        EmployeeDisciplinary::TYPE_SP1 => 1,
        EmployeeDisciplinary::TYPE_SP2 => 2,
        EmployeeDisciplinary::TYPE_SP3 => 3,
    ];

    public const VALIDITY_MONTHS = [
        EmployeeDisciplinary::TYPE_COACHING => 3,
        EmployeeDisciplinary::TYPE_COUNSELING => 3,
        EmployeeDisciplinary::TYPE_SP1 => 6,
        EmployeeDisciplinary::TYPE_SP2 => 6,
        EmployeeDisciplinary::TYPE_SP3 => 6,
    ];

    public function expireDueForEmployee(?string $employeeId = null): int
    {
        $query = EmployeeDisciplinary::query()
            ->where('status', EmployeeDisciplinary::STATUS_ACTIVE)
            ->whereDate('end_date', '<', Carbon::today());

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->update(['status' => EmployeeDisciplinary::STATUS_EXPIRED]);
    }

    public function expireDue(): int
    {
        return $this->expireDueForEmployee(null);
    }

    public function getActiveRecords(Employee|string $employee): \Illuminate\Support\Collection
    {
        $employeeId = $employee instanceof Employee ? $employee->id : $employee;
        $this->expireDueForEmployee($employeeId);

        return EmployeeDisciplinary::query()
            ->with('criteria')
            ->where('employee_id', $employeeId)
            ->where('status', EmployeeDisciplinary::STATUS_ACTIVE)
            ->whereDate('end_date', '>=', Carbon::today())
            ->orderBy('effective_date', 'desc')
            ->get();
    }

    public function getActiveSpFloor(Employee|string $employee, ?int $excludeId = null): ?string
    {
        $employeeId = $employee instanceof Employee ? $employee->id : $employee;
        $this->expireDueForEmployee($employeeId);

        $query = EmployeeDisciplinary::query()
            ->where('employee_id', $employeeId)
            ->where('status', EmployeeDisciplinary::STATUS_ACTIVE)
            ->whereIn('type', EmployeeDisciplinary::SP_TYPES)
            ->whereDate('end_date', '>=', Carbon::today());

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $activeSp = $query->get();
        if ($activeSp->isEmpty()) {
            return null;
        }

        return $activeSp
            ->sortByDesc(fn (EmployeeDisciplinary $row) => self::SP_RANKS[$row->type] ?? 0)
            ->first()
            ->type;
    }

    /**
     * Types allowed when issuing a NEW disciplinary for another violation.
     * Active SP floor forces escalation: same or lower SP is blocked; pembinaan is blocked.
     * Coaching/Counseling never set an SP floor (independent of each other and of the SP ladder).
     */
    public function allowedTypes(Employee|string $employee, ?int $excludeId = null): array
    {
        $floor = $this->getActiveSpFloor($employee, $excludeId);

        if ($floor === null) {
            return [
                EmployeeDisciplinary::TYPE_COACHING,
                EmployeeDisciplinary::TYPE_COUNSELING,
                EmployeeDisciplinary::TYPE_SP1,
                EmployeeDisciplinary::TYPE_SP2,
                EmployeeDisciplinary::TYPE_SP3,
            ];
        }

        if ($floor === EmployeeDisciplinary::TYPE_SP3) {
            return [];
        }

        $floorRank = self::SP_RANKS[$floor] ?? 0;

        // Strict escalation: next violation must be higher than the active SP (jump-up allowed).
        return collect(self::SP_RANKS)
            ->filter(fn (int $rank) => $rank > $floorRank)
            ->keys()
            ->values()
            ->all();
    }

    /**
     * Types allowed on edit form/save. Always keeps the record's current type;
     * changing type cannot go below the active SP floor (including this record if it is an active SP).
     */
    public function allowedTypesForEdit(EmployeeDisciplinary $record): array
    {
        $fromOthers = $this->allowedTypes($record->employee_id, $record->id);
        $allowed = collect($fromOthers);

        // Keep current type selectable so the form can round-trip without forcing a change.
        if (! $allowed->contains($record->type)) {
            $allowed->prepend($record->type);
        }

        if (
            $record->status === EmployeeDisciplinary::STATUS_ACTIVE
            && $record->isSp()
            && $record->end_date
            && $record->end_date->gte(Carbon::today())
        ) {
            $currentRank = self::SP_RANKS[$record->type] ?? 0;
            $allowed = $allowed->filter(function (string $type) use ($record, $currentRank) {
                if ($type === $record->type) {
                    return true;
                }
                $rank = self::SP_RANKS[$type] ?? null;

                // No return to pembinaan / lower SP while this active SP still applies.
                return $rank !== null && $rank > $currentRank;
            });
        }

        // Respect a higher floor from other active SP records (exclude self already applied).
        $otherFloor = $this->getActiveSpFloor($record->employee_id, $record->id);
        if ($otherFloor !== null) {
            if ($otherFloor === EmployeeDisciplinary::TYPE_SP3) {
                $allowed = collect([$record->type]);
            } else {
                $otherRank = self::SP_RANKS[$otherFloor] ?? 0;
                $allowed = $allowed->filter(function (string $type) use ($record, $otherRank) {
                    if ($type === $record->type) {
                        return true;
                    }
                    $rank = self::SP_RANKS[$type] ?? null;

                    return $rank !== null && $rank > $otherRank;
                });
            }
        }

        return $allowed->unique()->values()->all();
    }

    public function suggestNext(Employee|string $employee, ?int $excludeId = null): ?string
    {
        $floor = $this->getActiveSpFloor($employee, $excludeId);

        if ($floor === null) {
            return null;
        }

        $rank = self::SP_RANKS[$floor] ?? 0;
        $next = collect(self::SP_RANKS)->search(fn (int $r) => $r === $rank + 1);

        return $next !== false ? $next : null;
    }

    public function typeNotAllowedMessage(Employee|string $employee, ?int $excludeId = null): string
    {
        $floor = $this->getActiveSpFloor($employee, $excludeId);

        if ($floor === EmployeeDisciplinary::TYPE_SP3) {
            return 'Employee has an active First & Final Warning. Termination is required.';
        }

        if ($floor !== null) {
            $floorLabel = EmployeeDisciplinary::TYPE_LABELS[$floor] ?? $floor;
            $next = $this->suggestNext($employee, $excludeId);
            $nextLabel = $next
                ? (EmployeeDisciplinary::TYPE_LABELS[$next] ?? $next)
                : 'a higher sanction';

            return "A new violation while {$floorLabel} is still valid must escalate. "
                ."Re-issuing the same or a lower type is not allowed. Next step: {$nextLabel} (or higher), or termination after SP3.";
        }

        return 'This disciplinary type is not allowed based on the employee active status.';
    }

    public function requiresTermination(Employee|string $employee, ?int $excludeId = null): bool
    {
        return $this->getActiveSpFloor($employee, $excludeId) === EmployeeDisciplinary::TYPE_SP3;
    }

    public function calculateEndDate(string $type, Carbon|string $effectiveDate): Carbon
    {
        $date = $effectiveDate instanceof Carbon ? $effectiveDate->copy() : Carbon::parse($effectiveDate);
        $months = self::VALIDITY_MONTHS[$type] ?? 6;

        return $date->copy()->addMonths($months);
    }

    public function create(array $data, array $criterionIds = [], $document = null): EmployeeDisciplinary
    {
        $employee = Employee::findOrFail($data['employee_id']);

        if ($this->requiresTermination($employee)) {
            throw ValidationException::withMessages([
                'type' => $this->typeNotAllowedMessage($employee),
            ]);
        }

        $type = $data['type'];
        $allowed = $this->allowedTypes($employee);
        if (! in_array($type, $allowed, true)) {
            throw ValidationException::withMessages([
                'type' => $this->typeNotAllowedMessage($employee),
            ]);
        }

        $this->validateCriteriaSelection($type, $criterionIds, $data['pp_notes'] ?? null);

        return DB::transaction(function () use ($data, $criterionIds, $document, $employee, $type) {
            $effectiveDate = Carbon::parse($data['effective_date']);
            $endDate = $this->calculateEndDate($type, $effectiveDate);

            $this->supersedePrevious($employee->id, $type);

            $record = EmployeeDisciplinary::create([
                'employee_id' => $employee->id,
                'type' => $type,
                'effective_date' => $effectiveDate,
                'end_date' => $endDate,
                'reason' => $data['reason'],
                'pp_notes' => $data['pp_notes'] ?? null,
                'status' => EmployeeDisciplinary::STATUS_ACTIVE,
                'created_by' => $data['created_by'] ?? auth()->id(),
                'imported_at' => $data['imported_at'] ?? null,
            ]);

            if ($document) {
                $record->document_path = $document->store('disciplinary_documents', 'public');
                $record->save();
            }

            if (! empty($criterionIds)) {
                $record->criteria()->sync($criterionIds);
            }

            // Historical imports may land with an end_date already past.
            if ($endDate->lt(Carbon::today())) {
                $record->update(['status' => EmployeeDisciplinary::STATUS_EXPIRED]);
            }

            return $record->load(['employee.administrations', 'criteria', 'creator']);
        });
    }

    public function attachDocument(EmployeeDisciplinary $record, $document): EmployeeDisciplinary
    {
        if (! $record->allowsDeferredDocument()) {
            throw ValidationException::withMessages([
                'document' => 'Deferred document upload is only allowed for imported records that still have no document.',
            ]);
        }

        if (! $document) {
            throw ValidationException::withMessages([
                'document' => 'Supporting document is required.',
            ]);
        }

        $record->document_path = $document->store('disciplinary_documents', 'public');
        $record->save();

        return $record->fresh(['employee.administrations', 'criteria', 'creator']);
    }

    public function update(EmployeeDisciplinary $record, array $data, array $criterionIds = [], $document = null, bool $removeDocument = false): EmployeeDisciplinary
    {
        $type = $data['type'] ?? $record->type;

        $allowed = $this->allowedTypesForEdit($record);
        if (! in_array($type, $allowed, true)) {
            throw ValidationException::withMessages([
                'type' => 'Cannot change to a lower disciplinary type while an active warning still applies. '
                    .'Escalate to a higher level, or keep the current type.',
            ]);
        }

        $typesWithCriteria = array_merge(
            [EmployeeDisciplinary::TYPE_COUNSELING],
            EmployeeDisciplinary::SP_TYPES
        );
        if (in_array($type, $typesWithCriteria, true) || ! empty($criterionIds)) {
            $this->validateCriteriaSelection($type, $criterionIds, $data['pp_notes'] ?? $record->pp_notes);
        }

        return DB::transaction(function () use ($record, $data, $criterionIds, $document, $removeDocument, $type) {
            $effectiveDate = Carbon::parse($data['effective_date'] ?? $record->effective_date);
            $endDate = $this->calculateEndDate($type, $effectiveDate);

            if ($type !== $record->type && in_array($type, EmployeeDisciplinary::SP_TYPES, true)) {
                $this->supersedePrevious($record->employee_id, $type, $record->id);
            }

            $record->fill([
                'type' => $type,
                'effective_date' => $effectiveDate,
                'end_date' => $endDate,
                'reason' => $data['reason'] ?? $record->reason,
                'pp_notes' => array_key_exists('pp_notes', $data) ? $data['pp_notes'] : $record->pp_notes,
            ]);

            if ($removeDocument && $record->document_path) {
                Storage::disk('public')->delete($record->document_path);
                $record->document_path = null;
            }

            if ($document) {
                if ($record->document_path) {
                    Storage::disk('public')->delete($record->document_path);
                }
                $record->document_path = $document->store('disciplinary_documents', 'public');
            }

            $record->save();
            $record->criteria()->sync($criterionIds);

            return $record->load(['employee.administrations', 'criteria', 'creator']);
        });
    }

    public function handleRepeatAfterSp3(
        Employee $employee,
        ?Carbon $terminationDate = null,
        ?string $reason = null
    ): Administration {
        if (! $this->requiresTermination($employee)) {
            throw ValidationException::withMessages([
                'employee_id' => 'Employee does not have an active First & Final Warning.',
            ]);
        }

        $administration = Administration::query()
            ->where('employee_id', $employee->id)
            ->where('is_active', 1)
            ->first();

        if (! $administration) {
            throw ValidationException::withMessages([
                'employee_id' => 'No active administration found for this employee.',
            ]);
        }

        return DB::transaction(function () use ($employee, $administration, $terminationDate, $reason) {
            $administration->termination_date = ($terminationDate ?? Carbon::today())->toDateString();
            $administration->termination_reason = $reason
                ?: 'Repeated violation after First & Final Warning';
            $administration->is_active = 0;
            $administration->user_id = auth()->id();
            $administration->save();

            EmployeeDisciplinary::query()
                ->where('employee_id', $employee->id)
                ->where('status', EmployeeDisciplinary::STATUS_ACTIVE)
                ->where('type', EmployeeDisciplinary::TYPE_SP3)
                ->update(['status' => EmployeeDisciplinary::STATUS_TERMINATED]);

            EmployeeDisciplinary::query()
                ->where('employee_id', $employee->id)
                ->where('status', EmployeeDisciplinary::STATUS_ACTIVE)
                ->whereIn('type', EmployeeDisciplinary::PEMBINAAN_TYPES)
                ->update(['status' => EmployeeDisciplinary::STATUS_SUPERSEDED]);

            return $administration;
        });
    }

    public function validateCriteriaSelection(string $type, array $criterionIds, ?string $ppNotes): void
    {
        $typesWithCriteria = array_merge(
            [
                EmployeeDisciplinary::TYPE_COACHING,
                EmployeeDisciplinary::TYPE_COUNSELING,
            ],
            EmployeeDisciplinary::SP_TYPES
        );

        if (! in_array($type, $typesWithCriteria, true)) {
            return;
        }

        $sanctionTypes = match ($type) {
            EmployeeDisciplinary::TYPE_COACHING,
            EmployeeDisciplinary::TYPE_COUNSELING => ['counseling'],
            default => [$type],
        };

        $activeCriteria = DisciplinaryCriterion::query()
            ->active()
            ->whereIn('sanction_type', $sanctionTypes)
            ->pluck('id')
            ->all();

        $criterionIds = array_values(array_unique(array_map('intval', $criterionIds)));

        if (! empty($activeCriteria)) {
            if (empty($criterionIds)) {
                // Coaching historically had no required PP picker; keep optional unless criteria were supplied.
                if ($type === EmployeeDisciplinary::TYPE_COACHING) {
                    return;
                }

                throw ValidationException::withMessages([
                    'criterion_ids' => 'Select at least one PP criterion for this sanction type.',
                ]);
            }

            $invalid = array_diff($criterionIds, $activeCriteria);
            if (! empty($invalid)) {
                throw ValidationException::withMessages([
                    'criterion_ids' => 'One or more selected criteria are invalid for the chosen sanction type.',
                ]);
            }

            return;
        }

        if (empty($criterionIds) && blank($ppNotes)) {
            throw ValidationException::withMessages([
                'pp_notes' => 'PP criteria master is empty. Please fill PP notes / justification.',
            ]);
        }
    }

    protected function supersedePrevious(string $employeeId, string $newType, ?int $excludeId = null): void
    {
        if (! in_array($newType, EmployeeDisciplinary::SP_TYPES, true)) {
            return;
        }

        $newRank = self::SP_RANKS[$newType] ?? 0;

        $query = EmployeeDisciplinary::query()
            ->where('employee_id', $employeeId)
            ->where('status', EmployeeDisciplinary::STATUS_ACTIVE);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $actives = $query->get();

        foreach ($actives as $active) {
            if ($active->isPembinaan()) {
                $active->update(['status' => EmployeeDisciplinary::STATUS_SUPERSEDED]);
                continue;
            }

            if ($active->isSp()) {
                $rank = self::SP_RANKS[$active->type] ?? 0;
                if ($rank < $newRank) {
                    $active->update(['status' => EmployeeDisciplinary::STATUS_SUPERSEDED]);
                }
            }
        }
    }

    public function statusSummary(Employee|string $employee, ?EmployeeDisciplinary $editing = null): array
    {
        $excludeId = $editing?->id;
        $actives = $this->getActiveRecords($employee);
        $floor = $this->getActiveSpFloor($employee, $excludeId);
        $suggestNext = $this->suggestNext($employee, $excludeId);

        $allowed = $editing
            ? $this->allowedTypesForEdit($editing)
            : $this->allowedTypes($employee);

        return [
            'active_records' => $actives->map(fn (EmployeeDisciplinary $row) => [
                'id' => $row->id,
                'type' => $row->type,
                'type_label' => $row->type_label,
                'effective_date' => $row->effective_date->format('Y-m-d'),
                'end_date' => $row->end_date->format('Y-m-d'),
                'remaining_days' => $row->remaining_days,
            ])->values(),
            'sp_floor' => $floor,
            'sp_floor_label' => $floor ? (EmployeeDisciplinary::TYPE_LABELS[$floor] ?? $floor) : null,
            'allowed_types' => $allowed,
            'suggest_next' => $suggestNext,
            'suggest_next_label' => $suggestNext
                ? (EmployeeDisciplinary::TYPE_LABELS[$suggestNext] ?? $suggestNext)
                : null,
            'requires_termination' => $this->requiresTermination($employee, $excludeId),
            'is_edit' => $editing !== null,
        ];
    }
}
