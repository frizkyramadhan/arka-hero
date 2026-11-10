@php
    $assignmentRows = collect($rows ?? [])->values();
    if ($assignmentRows->isEmpty()) {
        $assignmentRows = collect([['project_id' => '', 'position_id' => '', 'is_active' => 1]]);
    }
@endphp

<div class="assignment-block mt-3">
    <h5 class="mb-1 border-bottom pb-2">Acting Position</h5>
    <p class="text-muted small mb-2">Project and position above stay the primary posting on this administration. Add another project here only when the employee also acts in a different project.</p>
    @error('assignments')
        <div class="text-danger small mb-2">{{ $message }}</div>
    @enderror
    <div class="assignment-rows">
        @foreach ($assignmentRows as $index => $row)
            @php
                $projectId = (int) (is_array($row) ? ($row['project_id'] ?? 0) : $row->project_id);
                $positionId = (int) (is_array($row) ? ($row['position_id'] ?? 0) : $row->position_id);
                $isActive = (int) (is_array($row) ? ($row['is_active'] ?? 1) : $row->is_active) === 1;
            @endphp
            <div class="form-row assignment-row align-items-end mb-2">
                <div class="form-group col-md-5 mb-2">
                    <label class="form-label">Project</label>
                    <select name="assignments[{{ $index }}][project_id]" class="form-control">
                        <option value="">-Select Project-</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" {{ $projectId === (int) $project->id ? 'selected' : '' }}>
                                {{ $project->project_code }} - {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-5 mb-2">
                    <label class="form-label">Position</label>
                    <select name="assignments[{{ $index }}][position_id]" class="form-control">
                        <option value="">-Select Position-</option>
                        @foreach ($positions as $position)
                            <option value="{{ $position->id }}" {{ $positionId === (int) $position->id ? 'selected' : '' }}>
                                {{ $position->position_name }}@if ($position->department) ({{ $position->department->department_name }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2 mb-2">
                    <input type="hidden" name="assignments[{{ $index }}][is_active]" value="0">
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input"
                            id="assignment_active_{{ $formKey }}_{{ $index }}"
                            name="assignments[{{ $index }}][is_active]" value="1"
                            {{ $isActive ? 'checked' : '' }}>
                        <label class="custom-control-label" for="assignment_active_{{ $formKey }}_{{ $index }}">Active</label>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-assignment">Remove</button>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm add-assignment">Add acting position</button>
</div>
