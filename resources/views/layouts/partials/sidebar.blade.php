<!-- Main Sidebar Container -->
<aside class="main-sidebar main-sidebar-custom sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ url('/') }}" class="brand-link">
        <img src="{{ asset('assets/dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Arka <b>HERO</b></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('assets/dist/img/avatar6.png') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="{{ route('profile.change-password') }}" class="d-block">{{ auth()->user()->name }}</a>
                <small class="text-muted">{{ auth()->user()->email }}</small>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                    aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->

                {{-- PERSONAL SECTION FOR USER ROLE --}}
                @hasrole('user')
                    <li class="nav-item">
                        <a href="{{ route('dashboard.personal') }}"
                            class="nav-link {{ Request::is('dashboard/personal') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user"></i>
                            <p>My Dashboard</p>
                        </a>
                    </li>

                    {{-- My Leave --}}
                    @canany(['personal.leave.view-own', 'personal.leave.create-own'])
                        <li
                            class="nav-item {{ Request::is('leave/requests/my-requests*') || Request::is('leave/requests/my-entitlements*') ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ Request::is('leave/requests/my-requests*') || Request::is('leave/requests/my-entitlements*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>
                                    My Leave
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @can('personal.leave.view-own')
                                    <li class="nav-item">
                                        <a href="{{ route('leave.requests.my-requests') }}"
                                            class="nav-link {{ Request::is('leave/requests/my-requests*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>My Requests</p>
                                        </a>
                                    </li>
                                @endcan
                                @can('personal.leave.view-entitlements')
                                    <li class="nav-item">
                                        <a href="{{ route('leave.requests.my-entitlements') }}"
                                            class="nav-link {{ Request::is('leave/requests/my-entitlements*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>My Entitlements</p>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    {{-- My Travels --}}
                    @canany(['personal.official-travel.view-own', 'personal.official-travel.create-own'])
                        <li class="nav-item">
                            <a href="{{ route('officialtravels.my-travels') }}"
                                class="nav-link {{ Request::is('officialtravels/my-travels*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-plane"></i>
                                <p>My Travels</p>
                            </a>
                        </li>
                    @endcanany

                    {{-- My Recruitment Requests --}}
                    @canany(['personal.recruitment.view-own', 'personal.recruitment.create-own'])
                        <li class="nav-item">
                            <a href="{{ route('recruitment.requests.my-requests') }}"
                                class="nav-link {{ Request::is('recruitment/requests/my-requests*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>My Recruitment Requests</p>
                            </a>
                        </li>
                    @endcanany

                    {{-- My Approvals --}}
                    @can('personal.approval.view-pending')
                        <li class="nav-item">
                            <a href="{{ route('approval.requests.index') }}"
                                class="nav-link {{ Request::is('approval/requests*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-check-circle"></i>
                                <p>
                                    My Approvals
                                    @php
                                        $pendingApprovals = cache()->remember(
                                            'pending_approvals_' . auth()->id(),
                                            60,
                                            function () {
                                                return \App\Models\ApprovalPlan::where('approver_id', auth()->id())
                                                    ->where('is_open', true)
                                                    ->where('status', 0)
                                                    ->count();
                                            },
                                        );
                                    @endphp
                                    @if ($pendingApprovals > 0)
                                        <span class="badge badge-warning ml-1 approval-badge">{{ $pendingApprovals }}</span>
                                    @endif
                                </p>
                            </a>
                        </li>
                    @endcan
                @endhasrole

                {{-- HERO SECTION --}}
                <li class="nav-header">HERO SECTION</li>

                {{-- DASHBOARD --}}
                <li
                    class="nav-item {{ Request::is('dashboard/employees') || Request::is('dashboard/official-travel') || Request::is('dashboard/recruitment') || Request::is('dashboard/letter-administration') || Request::is('dashboard/leave-management') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ Request::is('dashboard/employees') || Request::is('dashboard/official-travel') || Request::is('dashboard/recruitment') || Request::is('dashboard/letter-administration') || Request::is('dashboard/leave-management') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('dashboard.employees') }}"
                                class="nav-link {{ Request::is('dashboard/employees') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Employee</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard.recruitment') }}"
                                class="nav-link {{ Request::is('dashboard/recruitment') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Recruitment</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard.officialtravel') }}"
                                class="nav-link {{ Request::is('dashboard/official-travel') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Official Travel</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard.leave-management') }}"
                                class="nav-link {{ Request::is('dashboard/leave-management') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Leave Management</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard.letter-administration') }}"
                                class="nav-link {{ Request::is('dashboard/letter-administration') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Letter Administration</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- EMPLOYEE MANAGEMENT --}}
                <li
                    class="nav-item {{ Request::is('employees*') || Request::is('terminations*') || Request::is('employee-bonds*') || Request::is('bond-violations*') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ Request::is('employees*') || Request::is('terminations*') || Request::is('employee-bonds*') || Request::is('bond-violations*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Employee Management
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('employees') }}"
                                class="nav-link {{ Request::is('employees*') || Request::is('terminations*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Employees</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('employee-bonds.index') }}"
                                class="nav-link {{ Request::is('employee-bonds*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Employee Bonds</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('bond-violations.index') }}"
                                class="nav-link {{ Request::is('bond-violations*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Bond Violations</p>
                            </a>
                        </li>
                    </ul>
                </li>
                {{-- SUMMARY EMPLOYEE --}}
                {{-- <li
                    class="nav-item {{ Request::is('personals*') || Request::is('licenses*') || Request::is('insurances*') || Request::is('families*') || Request::is('educations*') || Request::is('courses*') || Request::is('emrgcalls*') || Request::is('additionaldatas*') || Request::is('employeebanks*') || Request::is('administrations*') || Request::is('jobexperiences*') || Request::is('operableunits*') || Request::is('taxidentifications*') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ Request::is('personals*') || Request::is('licenses*') || Request::is('insurances*') || Request::is('families*') || Request::is('educations*') || Request::is('courses*') || Request::is('emrgcalls*') || Request::is('additionaldatas*') || Request::is('employeebanks*') || Request::is('administrations*') || Request::is('jobexperiences*') || Request::is('operableunits*') || Request::is('taxidentifications*') ? 'active' : '' }}">
                        <i class="nav-icon fa fa-table"></i>
                        <p>
                            Summary Employee
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('personals') }}"
                                class="nav-link {{ Request::is('personals*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-house-user"></i>
                                <p>
                                    Personal Details
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('administrations') }}"
                                class="nav-link {{ Request::is('administrations*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-folder"></i>
                                <p>
                                    Administrations
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('employeebanks') }}"
                                class="nav-link {{ Request::is('employeebanks*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-credit-card"></i>
                                <p>
                                    Bank Accounts
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('taxidentifications') }}"
                                class="nav-link {{ Request::is('taxidentifications*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-user-md"></i>
                                <p>
                                    Tax Identification
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('insurances') }}"
                                class="nav-link {{ Request::is('insurances*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-medkit"></i>
                                <p>
                                    Insurances
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('licenses') }}"
                                class="nav-link {{ Request::is('licenses*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-car"></i>
                                <p>
                                    Driver Licenses
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ url('families') }}"
                                class="nav-link {{ Request::is('families*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-address-card"></i>
                                <p>
                                    Employee Families
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('educations') }}"
                                class="nav-link {{ Request::is('educations*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-university"></i>
                                <p>
                                    Educations
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('courses') }}"
                                class="nav-link {{ Request::is('courses*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-graduation-cap"></i>
                                <p>
                                    Courses
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('jobexperiences') }}"
                                class="nav-link {{ Request::is('jobexperiences*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-building"></i>
                                <p>
                                    Job Experiences
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('operableunits') }}"
                                class="nav-link {{ Request::is('operableunits*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-truck"></i>
                                <p>
                                    Operable Units
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('emrgcalls') }}"
                                class="nav-link {{ Request::is('emrgcalls*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-ambulance"></i>
                                <p>
                                    Emergency Calls
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('additionaldatas') }}"
                                class="nav-link {{ Request::is('additionaldatas*') ? 'active' : '' }}">
                                <i class="nav-icon fa fa-list"></i>
                                <p>
                                    Additional Data
                                </p>
                            </a>
                        </li>
                    </ul>
                </li> --}}


                {{-- @can('employees.create')
                    <li class="nav-item {{ Request::is('employee-registrations*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ Request::is('employee-registrations*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-plus"></i>
                            <p>
                                Employee Registration
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('employee.registration.admin.index') }}"
                                    class="nav-link {{ Request::is('employee-registrations') || Request::is('employee-registrations/pending*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Manage Registrations</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('employee.registration.admin.invite') }}"
                                    class="nav-link {{ Request::is('employee-registrations/invite*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Send Invitations</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan --}}



                @canany(['recruitment-requests.show', 'recruitment-candidates.show', 'recruitment-sessions.show'])
                    <li class="nav-item {{ Request::is('recruitment*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ Request::is('recruitment*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tie"></i>
                            <p>
                                Recruitment
                            </p>
                            <i class="fas fa-angle-left right"></i>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('recruitment-requests.show')
                                <li class="nav-item">
                                    <a href="{{ route('recruitment.requests.index') }}"
                                        class="nav-link {{ Request::is('recruitment/requests*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Requests (FPTK)</p>
                                    </a>
                                </li>
                            @endcan
                            @can('mpp.show')
                                <li class="nav-item">
                                    <a href="{{ route('recruitment.mpp.index') }}"
                                        class="nav-link {{ Request::is('recruitment/mpp*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Requests (MPP)</p>
                                    </a>
                                </li>
                            @endcan
                            @can('recruitment-candidates.show')
                                <li class="nav-item">
                                    <a href="{{ route('recruitment.candidates.index') }}"
                                        class="nav-link {{ Request::is('recruitment/candidates*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Candidates (CV)</p>
                                    </a>
                                </li>
                            @endcan
                            @can('recruitment-sessions.show')
                                <li class="nav-item">
                                    <a href="{{ route('recruitment.sessions.index') }}"
                                        class="nav-link {{ Request::is('recruitment/sessions*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Sessions</p>
                                    </a>
                                </li>
                            @endcan
                            <li class="nav-item">
                                <a href="{{ route('recruitment.reports.index') }}"
                                    class="nav-link {{ Request::is('recruitment/reports*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Reports</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcanany

                @can('official-travels.show')
                    <li class="nav-item">
                        <a href="{{ url('officialtravels') }}"
                            class="nav-link {{ Request::is('officialtravels*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-route"></i>
                            <p>
                                Official Travel (LOT)
                                <br>
                                <small
                                    style="text-align: left; display: block; margin-left: 0; padding-left: 0;">Perjalanan
                                    Dinas</small>
                            </p>
                        </a>
                    </li>
                @endcan

                {{-- RECRUITMENT MANAGEMENT --}}
                @canany(['recruitment-requests.show', 'recruitment-candidates.show', 'recruitment-sessions.show'])
                    <li class="nav-item {{ Request::is('recruitment*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ Request::is('recruitment*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tie"></i>
                            <p>
                                Recruitment
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('recruitment-requests.show')
                                <li class="nav-item">
                                    <a href="{{ route('recruitment.requests.index') }}"
                                        class="nav-link {{ Request::is('recruitment/requests*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Requests (FPTK)</p>
                                    </a>
                                </li>
                            @endcan
                            @can('mpp.show')
                                <li class="nav-item">
                                    <a href="{{ route('recruitment.mpp.index') }}"
                                        class="nav-link {{ Request::is('recruitment/mpp*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Requests (MPP)</p>
                                    </a>
                                </li>
                            @endcan
                            @can('recruitment-candidates.show')
                                <li class="nav-item">
                                    <a href="{{ route('recruitment.candidates.index') }}"
                                        class="nav-link {{ Request::is('recruitment/candidates*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Candidates (CV)</p>
                                    </a>
                                </li>
                            @endcan
                            @can('recruitment-sessions.show')
                                <li class="nav-item">
                                    <a href="{{ route('recruitment.sessions.index') }}"
                                        class="nav-link {{ Request::is('recruitment/sessions*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Sessions</p>
                                    </a>
                                </li>
                            @endcan
                            <li class="nav-item">
                                <a href="{{ route('recruitment.reports.index') }}"
                                    class="nav-link {{ Request::is('recruitment/reports*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Reports</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcanany

                {{-- OFFICIAL TRAVEL MANAGEMENT --}}
                @can('official-travels.show')
                    <li class="nav-item">
                        <a href="{{ url('officialtravels') }}"
                            class="nav-link {{ Request::is('officialtravels*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-route"></i>
                            <p>
                                Official Travel (LOT)
                                <br>
                                <small
                                    style="text-align: left; display: block; margin-left: 0; padding-left: 0;">Perjalanan
                                    Dinas</small>
                            </p>
                        </a>
                    </li>
                @endcan

                {{-- LEAVE MANAGEMENT --}}
                @canany(['leave-requests.show', 'bulk-leave-requests.show', 'leave-entitlements.show',
                    'leave-reports.show'])
                    <li
                        class="nav-item {{ Request::is('leave/requests*') || Request::is('leave/bulk-requests*') || Request::is('leave/entitlements*') || Request::is('leave/reports*') ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ Request::is('leave/requests*') || Request::is('leave/bulk-requests*') || Request::is('leave/entitlements*') || Request::is('leave/reports*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>
                                Leave Management
                                <i class="fas fa-angle-left right"></i>
                                <br>
                                <small style="text-align: left; display: block; margin-left: 0; padding-left: 0;">Manajemen
                                    Cuti</small>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('leave-requests.show')
                                <li class="nav-item">
                                    <a href="{{ route('leave.requests.index') }}"
                                        class="nav-link {{ Request::is('leave/requests*') && !Request::is('leave/bulk-requests*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Requests</p>
                                    </a>
                                </li>
                            @endcan
                            @can('leave-requests.show')
                                <li class="nav-item">
                                    <a href="{{ route('leave.bulk-requests.index') }}"
                                        class="nav-link {{ Request::is('leave/bulk-requests*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Periodic Leave Requests</p>
                                    </a>
                                </li>
                            @endcan
                            @can('leave-entitlements.show')
                                <li class="nav-item">
                                    <a href="{{ route('leave.entitlements.index') }}"
                                        class="nav-link {{ Request::is('leave/entitlements*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Entitlements</p>
                                    </a>
                                </li>
                            @endcan
                            @can('leave-reports.show')
                                <li class="nav-item">
                                    <a href="{{ route('leave.reports.index') }}"
                                        class="nav-link {{ Request::is('leave/reports*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Reports</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                {{-- ROSTER MANAGEMENT --}}
                <li class="nav-item">
                    <a href="{{ route('rosters.index') }}"
                        class="nav-link {{ Request::is('rosters*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-week"></i>
                        <p>
                            Roster Management
                            <br>
                            <small style="text-align: left; display: block; margin-left: 0; padding-left: 0;">Manajemen
                                Jadwal Kerja</small>
                        </p>
                    </a>
                </li>

                {{-- LETTER ADMINISTRATION --}}
                <li class="nav-item">
                    <a href="{{ route('letter-numbers.index') }}"
                        class="nav-link {{ Request::is('letter-numbers*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>Letter Administration</p>
                    </a>
                </li>

                {{-- Roster Management --}}
                <li class="nav-item">
                    <a href="{{ route('rosters.index') }}"
                        class="nav-link {{ Request::is('rosters*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-week"></i>
                        <p>
                            Roster Management
                            <br>
                            <small style="text-align: left; display: block; margin-left: 0; padding-left: 0;">Manajemen
                                Jadwal Kerja</small>
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('letter-numbers.index') }}"
                        class="nav-link {{ Request::is('letter-numbers*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>
                            Letter Administration
                        </p>
                    </a>
                </li>


                {{-- MASTER DATA --}}
                @canany(['master-data.show'])
                    <li
                        class="nav-item {{ Request::is('positions*') || Request::is('departments*') || Request::is('projects*') || Request::is('transportations*') || Request::is('leave/types*') || Request::is('letter-categories*') ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ Request::is('positions*') || Request::is('departments*') || Request::is('projects*') || Request::is('transportations*') || Request::is('leave/types*') || Request::is('letter-categories*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-database"></i>
                            <p>
                                Master Data
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ url('positions') }}"
                                    class="nav-link {{ Request::is('positions*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Positions</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('departments') }}"
                                    class="nav-link {{ Request::is('departments*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Departments</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('projects') }}"
                                    class="nav-link {{ Request::is('projects*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Projects</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('transportations') }}"
                                    class="nav-link {{ Request::is('transportations*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Transportations</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('leave.types.index') }}"
                                    class="nav-link {{ Request::is('leave/types*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Leave Types</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('letter-categories') }}"
                                    class="nav-link {{ Request::is('letter-categories*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Letter Categories</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcanany

                {{-- ADMINISTRATOR --}}
                @canany(['users.show', 'roles.show', 'permissions.show'])
                    <li class="nav-header">ADMINISTRATOR</li>
                    <li class="nav-item">
                        <a href="{{ url('users') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>Users</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('roles') }}" class="nav-link {{ Request::is('roles*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tag"></i>
                            <p>Roles</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('permissions') }}"
                            class="nav-link {{ Request::is('permissions*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-lock"></i>
                            <p>Permissions</p>
                        </a>
                    </li>
                    {{-- <li class="nav-item">
                        <a href="{{ route('approval.stages.index') }}"
                            class="nav-link {{ Request::is('approval/stages*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-layer-group"></i>
                            <p>Approval Stages</p>
                        </a>
                    </li> --}}
                    {{-- <li class="nav-item">
                        <a href="{{ route('debug.index') }}"
                            class="nav-link {{ Request::is('debug*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-bug"></i>
                            <p>Debug Tools</p>
                        </a>
                    </li> --}}
                @endcanany
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <div class="sidebar-custom">
        <form action="{{ url('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-block btn-danger">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>
    </div>
    <!-- /.sidebar -->
</aside>
