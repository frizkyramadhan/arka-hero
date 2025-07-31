@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $subtitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $title }}</li>
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
                            <h3 class="card-title">Database Debug Tools</h3>
                            <div class="card-tools">
                                <span class="badge badge-warning">Administrator Only</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Warning:</strong> These actions will permanently delete all data from the specified
                                tables. This action cannot be undone.
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Individual Table Truncate</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td>Employees</td>
                                                    <td>
                                                        <form action="{{ route('debug.truncate.employees') }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to truncate employees table?')">
                                                                <i class="fas fa-trash"></i> Truncate
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Administrations</td>
                                                    <td>
                                                        <form action="{{ route('debug.truncate.administrations') }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to truncate administrations table?')">
                                                                <i class="fas fa-trash"></i> Truncate
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Employee Banks</td>
                                                    <td>
                                                        <form action="{{ route('debug.truncate.employeebanks') }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to truncate employee banks table?')">
                                                                <i class="fas fa-trash"></i> Truncate
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Tax Identifications</td>
                                                    <td>
                                                        <form action="{{ route('debug.truncate.taxidentifications') }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to truncate tax identifications table?')">
                                                                <i class="fas fa-trash"></i> Truncate
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Insurances</td>
                                                    <td>
                                                        <form action="{{ route('debug.truncate.insurances') }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to truncate insurances table?')">
                                                                <i class="fas fa-trash"></i> Truncate
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Licenses</td>
                                                    <td>
                                                        <form action="{{ route('debug.truncate.licenses') }}" method="POST"
                                                            style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to truncate licenses table?')">
                                                                <i class="fas fa-trash"></i> Truncate
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Additional Tables</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td>Families</td>
                                                    <td>
                                                        <form action="{{ route('debug.truncate.families') }}" method="POST"
                                                            style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to truncate families table?')">
                                                                <i class="fas fa-trash"></i> Truncate
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Educations</td>
                                                    <td>
                                                        <form action="{{ route('debug.truncate.educations') }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to truncate educations table?')">
                                                                <i class="fas fa-trash"></i> Truncate
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Courses</td>
                                                    <td>
                                                        <form action="{{ route('debug.truncate.courses') }}" method="POST"
                                                            style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to truncate courses table?')">
                                                                <i class="fas fa-trash"></i> Truncate
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Job Experiences</td>
                                                    <td>
                                                        <form action="{{ route('debug.truncate.jobexperiences') }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to truncate job experiences table?')">
                                                                <i class="fas fa-trash"></i> Truncate
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Operable Units</td>
                                                    <td>
                                                        <form action="{{ route('debug.truncate.operableunits') }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to truncate operable units table?')">
                                                                <i class="fas fa-trash"></i> Truncate
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Emergency Calls</td>
                                                    <td>
                                                        <form action="{{ route('debug.truncate.emrgcalls') }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to truncate emergency calls table?')">
                                                                <i class="fas fa-trash"></i> Truncate
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Additional Datas</td>
                                                    <td>
                                                        <form action="{{ route('debug.truncate.additionaldatas') }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to truncate additional datas table?')">
                                                                <i class="fas fa-trash"></i> Truncate
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-danger">
                                        <h5><i class="fas fa-exclamation-triangle"></i> Bulk Operation</h5>
                                        <p>Truncate all tables at once:</p>
                                        <form action="{{ route('debug.truncate.all') }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-lg"
                                                onclick="return confirm('WARNING: This will truncate ALL specified tables. Are you absolutely sure?')">
                                                <i class="fas fa-trash-alt"></i> Truncate All Tables
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
