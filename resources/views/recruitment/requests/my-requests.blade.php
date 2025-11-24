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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.personal') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $subtitle }}</h3>
                            <div class="card-tools">
                                @can('personal.recruitment.create-own')
                                    <a href="{{ route('recruitment.requests.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> New Recruitment Request
                                    </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Position</th>
                                        <th>Department</th>
                                        <th>Project</th>
                                        <th>Required</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recruitmentRequests as $request)
                                        <tr>
                                            <td>{{ $request->position->position_name ?? 'N/A' }}</td>
                                            <td>{{ $request->department->department_name ?? 'N/A' }}</td>
                                            <td>{{ $request->project->project_code ?? 'N/A' }}</td>
                                            <td>{{ $request->required_quantity }}</td>
                                            <td>
                                                @switch($request->status)
                                                    @case('draft')
                                                        <span class="badge badge-secondary">Draft</span>
                                                    @break

                                                    @case('acknowledged')
                                                        <span class="badge badge-info">Acknowledged</span>
                                                    @break
