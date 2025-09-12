@extends('layouts.main')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $subtitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a href="{{ route('recruitment.reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter"></i> Filter Options
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('recruitment.reports.interview-assessment-analytics') }}"
                        class="row" id="filterForm">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date1">Date From</label>
                                <input type="date" name="date1" id="date1" class="form-control"
                                    value="{{ $date1 }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date2">Date To</label>
                                <input type="date" name="date2" id="date2" class="form-control"
                                    value="{{ $date2 }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select name="department" id="department" class="form-control">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ $department == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="position">Position</label>
                                <select name="position" id="position" class="form-control">
                                    <option value="">All Positions</option>
                                    @foreach ($positions as $pos)
                                        <option value="{{ $pos->id }}" {{ $position == $pos->id ? 'selected' : '' }}>
                                            {{ $pos->position_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="project">Project</label>
                                <select name="project" id="project" class="form-control">
                                    <option value="">All Projects</option>
                                    @foreach ($projects as $proj)
                                        <option value="{{ $proj->id }}" {{ $project == $proj->id ? 'selected' : '' }}>
                                            {{ $proj->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                    <div class="row mt-2">
                        <div class="col-12">
                            <button type="submit" form="filterForm" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('recruitment.reports.interview-assessment-analytics') }}"
                                class="btn btn-warning mr-2">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                            <button type="button" id="exportExcelBtn" class="btn btn-success mr-2">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-info" data-toggle="modal"
                                data-target="#scoringIndexModal">
                                <i class="fas fa-calculator"></i> Scoring Index
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="interviewAssessmentTable" class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="align-middle">Request No</th>
                                    <th class="align-middle">Department</th>
                                    <th class="align-middle">Position</th>
                                    <th class="align-middle">Project</th>
                                    <th class="align-middle">Candidate Name</th>
                                    <th class="align-middle">Psikotes Result</th>
                                    <th class="align-middle">Tes Teori Result</th>
                                    <th class="align-middle">Interview Result</th>
                                    <th class="align-middle">Overall Assessment</th>
                                    <th class="align-middle">Notes</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scoring Index Modal -->
    <div class="modal fade" id="scoringIndexModal" tabindex="-1" role="dialog" aria-labelledby="scoringIndexModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scoringIndexModalLabel">
                        <i class="fas fa-calculator"></i> Scoring Index - Overall Assessment Rules
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="text-primary">Scoring System</h6>
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="border rounded p-3 bg-success text-white">
                                        <h5>2 Points</h5>
                                        <p class="mb-0">Pass / Recommended</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 bg-warning text-dark">
                                        <h5>1 Point</h5>
                                        <p class="mb-0">Pending / Average</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 bg-danger text-white">
                                        <h5>0 Points</h5>
                                        <p class="mb-0">Fail / Not Recommended</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 bg-secondary text-white">
                                        <h5>-</h5>
                                        <p class="mb-0">No Data / Not Applicable</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-success">Assessment Rules</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center">Psikotes</th>
                                            <th class="text-center">Tes Teori</th>
                                            <th class="text-center">Interview HR</th>
                                            <th class="text-center">Interview User</th>
                                            <th class="text-center">Interview Trainer</th>
                                            <th class="text-center">Total Score</th>
                                            <th class="text-center">Overall Result</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Excellent -->
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><strong>10</strong></td>
                                            <td class="text-center" style="background-color:#c6efce;font-weight:bold;">
                                                Excellent</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><strong>9</strong></td>
                                            <td class="text-center" style="background-color:#c6efce;font-weight:bold;">
                                                Excellent</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><strong>9</strong></td>
                                            <td class="text-center" style="background-color:#c6efce;font-weight:bold;">
                                                Excellent</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><strong>9</strong></td>
                                            <td class="text-center" style="background-color:#c6efce;font-weight:bold;">
                                                Excellent</td>
                                        </tr>

                                        <!-- Very Good -->
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><strong>8</strong></td>
                                            <td class="text-center" style="background-color:#e2efda;font-weight:bold;">
                                                Very Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><strong>8</strong></td>
                                            <td class="text-center" style="background-color:#e2efda;font-weight:bold;">
                                                Very Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><strong>8</strong></td>
                                            <td class="text-center" style="background-color:#e2efda;font-weight:bold;">
                                                Very Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><strong>8</strong></td>
                                            <td class="text-center" style="background-color:#e2efda;font-weight:bold;">
                                                Very Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><strong>8</strong></td>
                                            <td class="text-center" style="background-color:#e2efda;font-weight:bold;">
                                                Very Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><strong>8</strong></td>
                                            <td class="text-center" style="background-color:#e2efda;font-weight:bold;">
                                                Very Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><strong>7</strong></td>
                                            <td class="text-center" style="background-color:#e2efda;font-weight:bold;">
                                                Very Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><strong>7</strong></td>
                                            <td class="text-center" style="background-color:#e2efda;font-weight:bold;">
                                                Very Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><strong>7</strong></td>
                                            <td class="text-center" style="background-color:#e2efda;font-weight:bold;">
                                                Very Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><strong>7</strong></td>
                                            <td class="text-center" style="background-color:#e2efda;font-weight:bold;">
                                                Very Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><strong>7</strong></td>
                                            <td class="text-center" style="background-color:#e2efda;font-weight:bold;">
                                                Very Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><strong>7</strong></td>
                                            <td class="text-center" style="background-color:#e2efda;font-weight:bold;">
                                                Very Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><strong>7</strong></td>
                                            <td class="text-center" style="background-color:#e2efda;font-weight:bold;">
                                                Very Good</td>
                                        </tr>

                                        <!-- Good -->
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><strong>6</strong></td>
                                            <td class="text-center" style="background-color:#ddebf7;font-weight:bold;">
                                                Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><strong>6</strong></td>
                                            <td class="text-center" style="background-color:#ddebf7;font-weight:bold;">
                                                Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><strong>6</strong></td>
                                            <td class="text-center" style="background-color:#ddebf7;font-weight:bold;">
                                                Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><strong>6</strong></td>
                                            <td class="text-center" style="background-color:#ddebf7;font-weight:bold;">
                                                Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><strong>6</strong></td>
                                            <td class="text-center" style="background-color:#ddebf7;font-weight:bold;">
                                                Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><strong>5</strong></td>
                                            <td class="text-center" style="background-color:#ddebf7;font-weight:bold;">
                                                Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><strong>5</strong></td>
                                            <td class="text-center" style="background-color:#ddebf7;font-weight:bold;">
                                                Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><strong>5</strong></td>
                                            <td class="text-center" style="background-color:#ddebf7;font-weight:bold;">
                                                Good</td>
                                        </tr>

                                        <!-- Average -->
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><strong>4</strong></td>
                                            <td class="text-center" style="background-color:#fff2cc;font-weight:bold;">
                                                Average</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><strong>4</strong></td>
                                            <td class="text-center" style="background-color:#fff2cc;font-weight:bold;">
                                                Average</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><strong>4</strong></td>
                                            <td class="text-center" style="background-color:#fff2cc;font-weight:bold;">
                                                Average</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><strong>3</strong></td>
                                            <td class="text-center" style="background-color:#fff2cc;font-weight:bold;">
                                                Average</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ffc000;font-weight:bold;">1</span>
                                            </td>
                                            <td class="text-center"><span style="color:#000000;font-weight:bold;">-</span>
                                            </td>
                                            <td class="text-center"><strong>3</strong></td>
                                            <td class="text-center" style="background-color:#fff2cc;font-weight:bold;">
                                                Average</td>
                                        </tr>

                                        <!-- Poor -->
                                        <tr>
                                            <td class="text-center"><span style="color:#00b050;font-weight:bold;">2</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><strong>2</strong></td>
                                            <td class="text-center" style="background-color:#f8cbad;font-weight:bold;">
                                                Poor</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><span style="color:#ff0000;font-weight:bold;">0</span>
                                            </td>
                                            <td class="text-center"><strong>0</strong></td>
                                            <td class="text-center" style="background-color:#f8cbad;font-weight:bold;">
                                                Poor</td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-info">Key Rules</h6>
                            <div class="alert alert-info">
                                <ul class="mb-0">
                                    <li><strong>Psikotes = 0 (gagal):</strong> Tes Teori = NA, semua interview = 0 &rarr;
                                        proses berhenti</li>
                                    <li><strong>Psikotes = 1 (pending):</strong> Tes Teori = 1, semua interview = 1 &rarr;
                                        semua pending (belum dikerjakan)</li>
                                    <li><strong>Psikotes = 2 (lulus):</strong></li>
                                    <ul>
                                        <li><strong>Tes Teori = NA:</strong> lanjut 2 interview wajib (HR &amp; User),
                                            trainer = NA &rarr; kombinasi semua kemungkinan (2/1/0)</li>
                                        <li><strong>Tes Teori = 0 (gagal):</strong> semua interview = 0 &rarr; proses
                                            berhenti</li>
                                        <li><strong>Tes Teori = 1 (pending):</strong> semua interview = 1 &rarr; pending
                                            semua</li>
                                        <li><strong>Tes Teori = 2 (lulus):</strong> semua interview (HR, User, Trainer) bisa
                                            2/1/0 (independen, paralel)</li>
                                    </ul>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@push('scripts')
    <!-- DataTables -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var table = $('#interviewAssessmentTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route('recruitment.reports.interview-assessment-analytics.data') }}',
                    type: 'GET',
                    data: function(d) {
                        d.date1 = $('input[name="date1"]').val();
                        d.date2 = $('input[name="date2"]').val();
                        d.department = $('select[name="department"]').val();
                        d.position = $('select[name="position"]').val();
                        d.project = $('select[name="project"]').val();
                    }
                },
                columns: [{
                        data: 'request_no'
                    },
                    {
                        data: 'department'
                    },
                    {
                        data: 'position'
                    },
                    {
                        data: 'project'
                    },
                    {
                        data: 'candidate_name',
                        render: function(data, type, row) {
                            return '<a href="{{ route('recruitment.sessions.candidate', '') }}/' +
                                row.session_id +
                                '" target="_blank" title="View Request Details">' + data +
                                '</a>';
                        }
                    },
                    {
                        data: 'psikotes_result',
                        render: function(data, type, row) {
                            // Extract result and score from combined data
                            var result = data;
                            var score = '';
                            if (data.includes('(')) {
                                var parts = data.split('(');
                                result = parts[0].trim();
                                score = '(' + parts[1];
                            }

                            var colorClass = result.toLowerCase() === 'pass' ? 'badge-success' :
                                (result.toLowerCase() === 'fail' ? 'badge-danger' :
                                    'badge-warning');

                            var html = '<span class="badge ' + colorClass + '">' + result +
                                '</span>';
                            if (score) {
                                html += '<br><small class="text-muted">' + score + '</small>';
                            }
                            return html;
                        }
                    },
                    {
                        data: 'tes_teori_result',
                        render: function(data, type, row) {
                            // Extract result and score from combined data
                            var result = data;
                            var score = '';
                            if (data.includes('(')) {
                                var parts = data.split('(');
                                result = parts[0].trim();
                                score = '(' + parts[1];
                            }

                            var colorClass = result.toLowerCase() === 'pass' ? 'badge-success' :
                                (result.toLowerCase() === 'fail' ? 'badge-danger' :
                                    'badge-warning');

                            var html = '<span class="badge ' + colorClass + '">' + result +
                                '</span>';
                            if (score) {
                                html += '<br><small class="text-muted">' + score + '</small>';
                            }
                            return html;
                        }
                    },
                    {
                        data: 'interview_result',
                        render: function(data, type, row) {
                            return window.renderInterviewResult(data);
                        }
                    },
                    {
                        data: 'overall_assessment',
                        render: function(data, type, row) {
                            var colorClass = data === 'Excellent' ? 'badge-success' :
                                (data === 'Very Good' ? 'badge-success' :
                                    (data === 'Good' ? 'badge-info' :
                                        (data === 'Average' ? 'badge-warning' : 'badge-danger')));
                            return '<span class="badge ' + colorClass + '">' + data + '</span>';
                        }
                    },
                    {
                        data: 'notes'
                    }
                ],
                responsive: true,
                pageLength: 25,
                order: [
                    [0, 'asc']
                ],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
                }
            });

            // Helper function to render interview results
            window.renderInterviewResult = function(data) {
                if (!data || data === '-') {
                    return '-';
                }

                // Split by | to get individual interview results
                var interviewItems = data.split(' | ');
                var html = '';

                for (var i = 0; i < interviewItems.length; i++) {
                    var item = interviewItems[i].trim();
                    if (item) {
                        var parts = item.split(': ');
                        var type = parts[0];
                        var result = parts[1];

                        var colorClass = '';
                        if (result.toLowerCase().includes('not_recommended') || result.toLowerCase().includes(
                                'fail')) {
                            colorClass = 'badge-danger'; // Merah untuk not recommend dan fail
                        } else if (result.toLowerCase().includes('pass') || result.toLowerCase().includes(
                                'recommended')) {
                            colorClass = 'badge-success'; // Hijau untuk recommend dan pass
                        } else {
                            colorClass = 'badge-warning'; // Kuning untuk pending
                        }

                        if (html) {
                            html += '<br>';
                        }
                        html += '<span class="badge ' + colorClass + '">' + type + ': ' + result + '</span>';
                    }
                }

                return html || '-';
            };

            // Refresh table when form is submitted
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            // Export Excel with current filters
            $('#exportExcelBtn').on('click', function() {
                var params = $.param({
                    date1: $('input[name="date1"]').val(),
                    date2: $('input[name="date2"]').val(),
                    department: $('select[name="department"]').val(),
                    position: $('select[name="position"]').val(),
                    project: $('select[name="project"]').val()
                });
                window.open('{{ route('recruitment.reports.interview-assessment-analytics.export') }}?' +
                    params, '_blank');
            });
        });
    </script>
@endpush
