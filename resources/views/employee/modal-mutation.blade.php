<div class="modal fade text-left" id="modal-mutation">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Mutation</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('employee-mutations/' . $employee->id) }}" method="POST">
                @csrf
                <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="mutated_at_add">Mutation Date</label>
                        <input type="date" class="form-control @error('mutated_at') is-invalid @enderror"
                            id="mutated_at_add" name="mutated_at" value="{{ old('mutated_at') }}" required>
                        @error('mutated_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="mutation_project_id_add">Destination Project</label>
                        <select name="project_id" id="mutation_project_id_add"
                            class="form-control @error('project_id') is-invalid @enderror" required>
                            <option value="">-Select Project-</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}"
                                    {{ (string) old('project_id') === (string) $project->id ? 'selected' : '' }}>
                                    {{ $project->project_code }} - {{ $project->project_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="mutation_status_add">Status</label>
                        <select name="status" id="mutation_status_add"
                            class="form-control @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="mutation_remarks_add">Remarks</label>
                        <textarea class="form-control @error('remarks') is-invalid @enderror" id="mutation_remarks_add"
                            name="remarks" rows="2">{{ old('remarks') }}</textarea>
                        @error('remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <p class="text-muted mb-0">
                        Annual leave uses the latest mutation date. Leave settings follow this destination project.
                    </p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach ($mutations as $mutation)
    <div class="modal fade text-left" id="modal-mutation-{{ $mutation->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Mutation</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('employee-mutations/' . $mutation->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="employee_id"
                        value="{{ old('employee_id', $mutation->employee_id) }}">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="mutated_at_edit_{{ $mutation->id }}">Mutation Date</label>
                            <input type="date" class="form-control @error('mutated_at') is-invalid @enderror"
                                id="mutated_at_edit_{{ $mutation->id }}" name="mutated_at"
                                value="{{ old('mutated_at', optional($mutation->mutated_at)->format('Y-m-d')) }}"
                                required>
                            @error('mutated_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="mutation_project_id_edit_{{ $mutation->id }}">Destination Project</label>
                            <select name="project_id" id="mutation_project_id_edit_{{ $mutation->id }}"
                                class="form-control @error('project_id') is-invalid @enderror" required>
                                <option value="">-Select Project-</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}"
                                        {{ (string) old('project_id', $mutation->project_id) === (string) $project->id ? 'selected' : '' }}>
                                        {{ $project->project_code }} - {{ $project->project_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="mutation_status_edit_{{ $mutation->id }}">Status</label>
                            <select name="status" id="mutation_status_edit_{{ $mutation->id }}"
                                class="form-control @error('status') is-invalid @enderror" required>
                                <option value="active"
                                    {{ old('status', $mutation->status) === 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive"
                                    {{ old('status', $mutation->status) === 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="mutation_remarks_edit_{{ $mutation->id }}">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror"
                                id="mutation_remarks_edit_{{ $mutation->id }}" name="remarks"
                                rows="2">{{ old('remarks', $mutation->remarks) }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
