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
                <a href="#" class="d-block">{{ auth()->user()->name }}</a>
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
                <li class="nav-item">
                    <a href="{{ url('/') }}"
                        class="nav-link {{ Request::is('/') || Request::is('dashboard*') || Request::is('summary*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>

                {{-- APPS --}}
                <li class="nav-header">APPS</li>
                <li class="nav-item">
                    <a href="{{ url('employees') }}"
                        class="nav-link {{ Request::is('employees*') || Request::is('terminations*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-id-badge"></i>
                        <p>
                            Employees
                        </p>
                    </a>
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
                <li class="nav-item">
                    <a href="{{ route('letter-numbers.index') }}"
                        class="nav-link {{ Request::is('letter-numbers*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>
                            Letter Administration
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('officialtravels') }}"
                        class="nav-link {{ Request::is('officialtravels*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-route"></i>
                        <p>
                            Official Travels (LOT)
                        </p>
                    </a>
                </li>

                @canany(['recruitment-requests.show', 'recruitment-candidates.show', 'recruitment-sessions.show'])
                    <li class="nav-item {{ Request::is('recruitment*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ Request::is('recruitment*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tie"></i>
                            <p>
                                Recruitment
                                <small class="badge badge-warning ml-1">BETA</small>
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
                            @can('recruitment-sessions.dashboard')
                                <li class="nav-item">
                                    <a href="{{ route('recruitment.sessions.dashboard') }}"
                                        class="nav-link {{ Request::is('recruitment/sessions/dashboard*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Dashboard</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                <li class="nav-item">
                    <a href="{{ route('approval.requests.index') }}"
                        class="nav-link {{ Request::is('approval/requests*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-check-circle"></i>
                        <p>
                            Approval Requests
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

                @canany(['master-data.show'])
                    {{-- MASTER DATA --}}
                    <li class="nav-header">MASTER DATA</li>
                    <li
                        class="nav-item {{ Request::is('banks*') || Request::is('religions*') || Request::is('positions*') || Request::is('departments*') || Request::is('projects*') || Request::is('grades*') || Request::is('levels*') ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ Request::is('banks*') || Request::is('religions*') || Request::is('positions*') || Request::is('departments*') || Request::is('projects*') || Request::is('grades*') || Request::is('levels*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-database"></i>
                            <p>
                                Employee
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ url('positions') }}"
                                    class="nav-link {{ Request::is('positions*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sitemap"></i>
                                    <p>Positions</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('departments') }}"
                                    class="nav-link {{ Request::is('departments*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-building"></i>
                                    <p>Departments</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('grades') }}"
                                    class="nav-link {{ Request::is('grades*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-medal"></i>
                                    <p>Grades</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('levels') }}"
                                    class="nav-link {{ Request::is('levels*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-layer-group"></i>
                                    <p>Levels</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('projects') }}"
                                    class="nav-link {{ Request::is('projects*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-project-diagram"></i>
                                    <p>Projects</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('religions') }}"
                                    class="nav-link {{ Request::is('religions*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-praying-hands"></i>
                                    <p>Religions</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('banks') }}"
                                    class="nav-link {{ Request::is('banks*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-money-check-alt"></i>
                                    <p>Banks</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li
                        class="nav-item {{ Request::is('transportations*') || Request::is('accommodations*') ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ Request::is('transportations*') || Request::is('accommodations*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-database"></i>
                            <p>
                                Official Travel
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ url('transportations') }}"
                                    class="nav-link {{ Request::is('transportations*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-shuttle-van"></i>
                                    <p>Transportations</p>
                                </a>
                            </li>
                        </ul>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ url('accommodations') }}"
                                    class="nav-link {{ Request::is('accommodations*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-hotel"></i>
                                    <p>Accommodations</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item {{ Request::is('letter-categories*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ Request::is('letter-categories*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-database"></i>
                            <p>
                                Letter Management
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ url('letter-categories') }}"
                                    class="nav-link {{ Request::is('letter-categories*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-tags"></i>
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
                    <li class="nav-item">
                        <a href="{{ route('approval.stages.index') }}"
                            class="nav-link {{ Request::is('approval/stages*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-layer-group"></i>
                            <p>Approval Stages</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('debug.index') }}"
                            class="nav-link {{ Request::is('debug*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-bug"></i>
                            <p>Debug Tools</p>
                        </a>
                    </li>
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
