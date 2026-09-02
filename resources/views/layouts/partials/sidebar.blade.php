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

                {{-- PERSONAL NAVIGATION - USER ROLE --}}
                @hasrole('user')
                {{-- Personal Dashboard --}}
                <li class="nav-item">
                    <a href="{{ route('dashboard.personal') }}"
                        class="nav-link {{ Request::is('dashboard/personal') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user"></i>
                        <p>My Dashboard</p>
                    </a>
                </li>

                {{-- My Features Dropdown --}}
                <li
                    class="nav-item {{ Request::is('leave/my-*') || Request::is('officialtravels/my-*') || Request::is('recruitment/my-*') || Request::is('profile/my-profile*') || Request::is('flight/my-*') || Request::is('overtime/my-requests*') || Request::is('room-consumption-requests/my-requests*') || Request::is('supplies/orders/my-orders*') || Request::is('employee-disciplinaries/my-records*') || Request::is('vehicle-assignments/my-trips*') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ Request::is('leave/my-*') || Request::is('officialtravels/my-*') || Request::is('recruitment/my-*') || Request::is('profile/my-profile*') || Request::is('flight/my-*') || Request::is('overtime/my-requests*') || Request::is('room-consumption-requests/my-requests*') || Request::is('supplies/orders/my-orders*') || Request::is('employee-disciplinaries/my-records*') || Request::is('vehicle-assignments/my-trips*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-folder-open"></i>
                        <p>
                            My Features
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        {{-- My Profile --}}
                        @can('personal.profile.view-own')
                        <li class="nav-item">
                            <a href="{{ route('profile.my-profile') }}"
                                class="nav-link {{ Request::is('profile/my-profile*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>My Profile</p>
                            </a>
                        </li>
                        @endcan

                        {{-- My Leave Request --}}
                        @canany(['personal.leave.view-own', 'personal.leave.create-own',
                        'personal.leave.view-entitlements'])
                        <li class="nav-item">
                            <a href="{{ route('leave.my-requests') }}"
                                class="nav-link {{ Request::is('leave/my-requests*') || Request::is('leave/my-entitlements*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>My Leave Request</p>
                            </a>
                        </li>
                        @endcanany

                        {{-- My Travels --}}
                        @canany(['personal.official-travel.view-own', 'personal.official-travel.create-own'])
                        <li class="nav-item">
                            <a href="{{ route('officialtravels.my-travels') }}"
                                class="nav-link {{ Request::is('officialtravels/my-requests*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p><span style="font-size: 93%;">My Official Travel Request</span></p>
                            </a>
                        </li>
                        @endcanany



                        {{-- My Flight Requests --}}
                        @canany(['personal.flight.view-own', 'personal.flight.create-own'])
                        <li class="nav-item">
                            <a href="{{ route('flight-requests.my-requests') }}"
                                class="nav-link {{ Request::is('flight/my-requests*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>My Flight Request</p>
                            </a>
                        </li>
                        @endcanany

                        {{-- My Overtime Request --}}
                        @canany(['personal.overtime.view-own', 'personal.overtime.create-own'])
                        <li class="nav-item">
                            <a href="{{ route('overtime.my-requests') }}"
                                class="nav-link {{ Request::is('overtime/my-requests*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>My Overtime Request</p>
                            </a>
                        </li>
                        @endcanany

                        {{-- My Room & Consumption Request --}}
                        @canany(['personal.room-consumption.view-own', 'personal.room-consumption.create-own'])
                        <li class="nav-item">
                            <a href="{{ route('room-consumption-requests.my-requests') }}"
                                class="nav-link {{ Request::is('room-consumption-requests/my-requests*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>My Room & Consumption</p>
                            </a>
                        </li>
                        @endcanany

                        {{-- My Office Supply Orders --}}
                        @canany(['personal.supplies.orders.view-own', 'personal.supplies.orders.create-own'])
                        <li class="nav-item">
                            <a href="{{ route('supplies.orders.my-orders') }}"
                                class="nav-link {{ Request::is('supplies/orders/my-orders*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>My Office Supply Orders</p>
                            </a>
                        </li>
                        @endcanany

                        {{-- My Recruitment Requests --}}
                        @canany(['personal.recruitment.view-own', 'personal.recruitment.create-own'])
                        <li class="nav-item">
                            <a href="{{ route('recruitment.my-requests') }}"
                                class="nav-link {{ Request::is('recruitment/my-requests*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>My Recruitment Request</p>
                            </a>
                        </li>
                        @endcanany

                        {{-- My Disciplinary Record --}}
                        @can('personal.disciplinary.view-own')
                        <li class="nav-item">
                            <a href="{{ route('employee-disciplinaries.my-records') }}"
                                class="nav-link {{ Request::is('employee-disciplinaries/my-records*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>My Disciplinary Record</p>
                            </a>
                        </li>
                        @endcan

                        {{-- My Form of Assignment (driver) --}}
                        @can('personal.vehicle-assignments.view-own')
                        <li class="nav-item">
                            <a href="{{ route('vehicle-assignments.my-trips') }}"
                                class="nav-link {{ Request::is('vehicle-assignments/my-trips*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>My Form of Assignment</p>
                            </a>
                        </li>
                        @endcan


                    </ul>
                </li>
                @endhasrole

                {{-- My Approvals --}}
                @can('personal.approval.view-pending')
                <li class="nav-item">
                    <a href="{{ route('approval.requests.index') }}"
                        class="nav-link {{ Request::is('approval/requests*') ? 'active' : '' }}">
                        <i class="fas fa-check-circle nav-icon"></i>
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
                            <span class="badge badge-warning ml-1 approval-badge"
                                style="float: right; margin-top: 4px;">{{ $pendingApprovals }}</span>
                            @endif
                        </p>
                    </a>
                </li>
                @endcan

                {{-- Fuel Log (top-level, peer to My Dashboard / My Features / My Approvals) --}}
                @canany(['personal.fuel.view-own', 'personal.fuel.create-own'])
                <li class="nav-item">
                    <a href="{{ route('fuel-records.my-requests') }}"
                        class="nav-link {{ Request::is('fuel-records/my-requests*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-gas-pump"></i>
                        <p>Fuel Log</p>
                    </a>
                </li>
                @endcanany

                @canany(['employees.show', 'employee-disciplinaries.show',
                'recruitment-requests.show', 'recruitment-candidates.show',
                'recruitment-sessions.show', 'official-travels.show', 'leave-requests.show',
                'periodic-leave-requests.show', 'leave-entitlements.show', 'leave-reports.show', 'roster.show',
                'overtime-requests.show'])
                {{-- HERO SECTION --}}
                <li class="nav-header">HERO SECTION</li>
                @endcanany

                {{-- Employee Management --}}
                @canany(['employees.show', 'employee-disciplinaries.show'])
                <li
                    class="nav-item {{ Request::is('employees*') || Request::is('terminations*') || Request::is('employee-bonds*') || Request::is('bond-violations*') || Request::is('employee-disciplinaries*') || Request::is('dashboard/employees') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ Request::is('employees*') || Request::is('terminations*') || Request::is('employee-bonds*') || Request::is('bond-violations*') || Request::is('employee-disciplinaries*') || Request::is('dashboard/employees') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Employee Management
                        </p>
                        <i class="fas fa-angle-left right"></i>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('employees.show')
                        <li class="nav-item">
                            <a href="{{ route('dashboard.employees') }}"
                                class="nav-link {{ Request::is('dashboard/employees') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
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
                        @endcan
                        @can('employee-disciplinaries.show')
                        <li class="nav-item">
                            <a href="{{ route('employee-disciplinaries.index') }}"
                                class="nav-link {{ Request::is('employee-disciplinaries*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Disciplinary</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Recruitment Management --}}
                @canany(['recruitment-requests.show', 'recruitment-candidates.show', 'recruitment-sessions.show'])
                @php
                // Check if current route is my-requests - if so, menu should NOT be open or active
                $currentPath = Request::path();
                $isMyRequests = strpos($currentPath, 'recruitment/my-requests') === 0;

                // Only check for other recruitment routes if NOT my-requests
                if ($isMyRequests) {
                $isRecruitment = false;
                $shouldMenuOpen = false;
                $shouldActive = false;
                } else {
                // Check for exact 'recruitment' or routes that start with 'recruitment/' but not 'recruitment/my-requests'
                $isRecruitment =
                $currentPath === 'recruitment' ||
                (strpos($currentPath, 'recruitment/') === 0 && !$isMyRequests);
                $isRecruitmentDashboard = Request::is('dashboard/recruitment');
                $shouldMenuOpen = $isRecruitment || $isRecruitmentDashboard;
                $shouldActive = $isRecruitment || $isRecruitmentDashboard;
                }
                @endphp
                <li class="nav-item {{ $shouldMenuOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $shouldActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-tie"></i>
                        <p>
                            <span style="font-size: 93%;">Recruitment Management</span>
                        </p>
                        <i class="fas fa-angle-left right"></i>
                    </a>
                    <ul class="nav nav-treeview">
                        @canany(['recruitment-requests.show', 'recruitment-candidates.show',
                        'recruitment-sessions.show'])
                        <li class="nav-item">
                            <a href="{{ route('dashboard.recruitment') }}"
                                class="nav-link {{ Request::is('dashboard/recruitment') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        @endcanany
                        @can('recruitment-requests.show')
                        <li class="nav-item">
                            <a href="{{ route('recruitment.requests.index') }}"
                                class="nav-link {{ Request::is('recruitment/requests*') && !$isMyRequests ? 'active' : '' }}">
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

                {{-- Official Travel Management --}}
                @can('official-travels.show')
                @php
                // Check if current route is my-travels - if so, menu should NOT be open or active
                $currentPath = Request::path();
                $isMyTravels = strpos($currentPath, 'officialtravels/my-requests') === 0;

                // Only check for other officialtravels routes if NOT my-travels
                if ($isMyTravels) {
                $isOfficialTravels = false;
                $shouldMenuOpen = false;
                $shouldActive = false;
                } else {
                // Check for exact 'officialtravels' or routes that start with 'officialtravels/' but not 'officialtravels/my-travels'
                $isOfficialTravels =
                $currentPath === 'officialtravels' ||
                (strpos($currentPath, 'officialtravels/') === 0 && !$isMyTravels);
                $isOfficialTravelDashboard = Request::is('dashboard/official-travel');
                $shouldMenuOpen = $isOfficialTravels || $isOfficialTravelDashboard;
                $shouldActive = $isOfficialTravels || $isOfficialTravelDashboard;
                }
                @endphp
                <li class="nav-item {{ $shouldMenuOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $shouldActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-route"></i>
                        <p>
                            <span style="font-size: 90%;">Official Travel Management</span>
                        </p>
                        <i class="fas fa-angle-left right"></i>
                        {{-- <br>
                            <small
                                style="text-align: left; display: block; margin-left: 0; padding-left: 0;">Perjalanan
                                Dinas</small> --}}
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('dashboard.officialtravel') }}"
                                class="nav-link {{ Request::is('dashboard/official-travel') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        @can('official-travels.show')
                        <li class="nav-item">
                            <a href="{{ url('officialtravels') }}"
                                class="nav-link {{ $isOfficialTravels && !Request::is('officialtravels/reports*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Requests</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('officialtravels.reports.index') }}"
                                class="nav-link {{ Request::is('officialtravels/reports*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Reports</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                {{-- Overtime Management (HR) --}}
                @can('overtime-requests.show')
                @php
                $isMyOvertimeNav = Request::is('overtime/my-requests*');
                $isOvertimeReports = Request::is('overtime/reports*');
                $isOvertimeDashboard = Request::is('dashboard/overtime-management');
                $isOvertimeHr =
                (Request::is('overtime/requests') || Request::is('overtime/requests/*')) &&
                !$isMyOvertimeNav;
                $isOvertimeMenuOpen =
                $isOvertimeHr ||
                Request::is('overtime/requests/create') ||
                $isOvertimeDashboard ||
                $isOvertimeReports;
                @endphp
                <li class="nav-item {{ $isOvertimeMenuOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $isOvertimeMenuOpen ? 'active' : '' }}">
                        <i class="nav-icon fas fa-business-time"></i>
                        <p>
                            Overtime Management
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('dashboard.overtime-management') }}"
                                class="nav-link {{ $isOvertimeDashboard ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('overtime.requests.index') }}"
                                class="nav-link {{ $isOvertimeHr && !Request::is('overtime/requests/create') && !$isOvertimeDashboard && !$isOvertimeReports ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Requests</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('overtime.reports.index') }}"
                                class="nav-link {{ $isOvertimeReports ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Reports</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- Leave Management --}}
                @canany(['leave-requests.show', 'periodic-leave-requests.show', 'leave-entitlements.show',
                'leave-reports.show'])
                <li
                    class="nav-item {{ Request::is('leave/requests*') || Request::is('leave/entitlements*') || Request::is('leave/reports*') || Request::is('dashboard/leave-management') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ Request::is('leave/requests*') || Request::is('leave/entitlements*') || Request::is('leave/reports*') || Request::is('dashboard/leave-management') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>
                            Leave Management
                            <i class="fas fa-angle-left right"></i>
                            {{-- <br>
                                <small style="text-align: left; display: block; margin-left: 0; padding-left: 0;">Manajemen
                                    Cuti</small> --}}
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @canany(['leave-requests.show', 'periodic-leave-requests.show', 'leave-entitlements.show',
                        'leave-reports.show'])
                        <li class="nav-item">
                            <a href="{{ route('dashboard.leave-management') }}"
                                class="nav-link {{ Request::is('dashboard/leave-management') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        @endcanany
                        @can('leave-entitlements.show')
                        <li class="nav-item">
                            <a href="{{ route('leave.entitlements.index') }}"
                                class="nav-link {{ Request::is('leave/entitlements*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Entitlements</p>
                            </a>
                        </li>
                        @endcan
                        @can('leave-requests.show')
                        <li class="nav-item">
                            <a href="{{ route('leave.requests.index') }}"
                                class="nav-link {{ Request::is('leave/requests*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Requests</p>
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

                {{-- Roster Management --}}
                @canany(['rosters.show', 'leave-requests.show'])
                <li
                    class="nav-item {{ Request::is('rosters*') || Request::is('leave/periodic-requests*') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ Request::is('rosters*') || Request::is('leave/periodic-requests*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-week"></i>
                        <p>
                            Roster Management
                            <i class="fas fa-angle-left right"></i>
                            {{-- <br>
                                <small style="text-align: left; display: block; margin-left: 0; padding-left: 0;">Manajemen
                                    Jadwal Kerja</small> --}}
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @canany(['rosters.show', 'leave-requests.show'])
                        <li class="nav-item">
                            <a href="{{ route('rosters.dashboard') }}"
                                class="nav-link {{ Request::is('rosters/dashboard*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        @endcanany
                        @can('rosters.show')
                        <li class="nav-item">
                            <a href="{{ route('rosters.index') }}"
                                class="nav-link {{ Request::is('rosters*') && !Request::is('rosters/dashboard*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Rosters</p>
                            </a>
                        </li>
                        @endcan
                        @can('leave-requests.show')
                        <li class="nav-item">
                            <a href="{{ route('leave.periodic-requests.index') }}"
                                class="nav-link {{ Request::is('leave/periodic-requests*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Periodic Leave Requests</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- GAMMA SECTION - Flight + Room & Consumption + Vehicles + Supplies --}}
                @canany(['flight-requests.show', 'flight-issuances.view', 'flight-issuances.create', 'room-consumption-requests.show', 'vehicles.show', 'vehicle-assignments.show', 'fuel-records.verify', 'fuel-claims.show', 'fuel-records.show', 'supplies.catalog.show', 'supplies.stock-in.show', 'supplies.stock-out.show', 'supplies.orders.show'])
                <li class="nav-header">GAMMA SECTION</li>
                @canany(['flight-requests.show', 'flight-issuances.view', 'flight-issuances.create'])
                <li
                    class="nav-item {{ Request::is('flight/requests*') || Request::is('flight/issuances*') || Request::is('flight/reports*') || Request::is('dashboard/flight-management') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ Request::is('flight/requests*') || Request::is('flight/issuances*') || Request::is('flight/reports*') || Request::is('dashboard/flight-management') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-plane"></i>
                        <p>
                            Flight Management
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('dashboard.flight-management') }}"
                                class="nav-link {{ Request::is('dashboard/flight-management') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        @can('flight-requests.show')
                        <li class="nav-item">
                            <a href="{{ route('flight-requests.index') }}"
                                class="nav-link {{ Request::is('flight/requests*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Requests</p>
                            </a>
                        </li>
                        @endcan
                        @can('flight-issuances.show')
                        <li class="nav-item">
                            <a href="{{ route('flight-issuances.index') }}"
                                class="nav-link {{ Request::is('flight/issuances*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Issuances</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('flight.reports.index') }}"
                                class="nav-link {{ Request::is('flight/reports*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Reports</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                @can('room-consumption-requests.show')
                @php
                $isRcrMy = Request::is('room-consumption-requests/my-requests*');
                $isRcrReports = Request::is('room-consumption-requests/reports*');
                $isRcrDashboard = Request::is('dashboard/room-consumption');
                $isRcrAdmin =
                Request::is('room-consumption-requests*') && !$isRcrMy && !$isRcrReports;
                $isRcrMenuOpen = $isRcrAdmin || $isRcrReports || $isRcrDashboard;
                @endphp
                <li class="nav-item {{ $isRcrMenuOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $isRcrMenuOpen ? 'active' : '' }}">
                        <i class="nav-icon fas fa-door-open"></i>
                        <p>
                            Room & Consumption
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('dashboard.room-consumption') }}"
                                class="nav-link {{ $isRcrDashboard ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('room-consumption-requests.index') }}"
                                class="nav-link {{ $isRcrAdmin ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Requests</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('room-consumption-requests.reports.index') }}"
                                class="nav-link {{ $isRcrReports ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Reports</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                @canany(['supplies.catalog.show', 'supplies.stock-in.show', 'supplies.stock-out.show', 'supplies.orders.show', 'supplies.dashboard.show', 'supplies.reports.show'])
                @php
                $isSuppliesMy = Request::is('supplies/orders/my-orders*');
                $isSuppliesCatalog = Request::is('supplies/catalog*');
                $isSuppliesStockIn = Request::is('supplies/stock-ins*');
                $isSuppliesStockOut = Request::is('supplies/stock-outs*');
                $isSuppliesOrders = Request::is('supplies/orders*') && !$isSuppliesMy;
                $isSuppliesReports = Request::is('supplies/reports*');
                $isSuppliesDashboard = Request::is('dashboard/supplies-management');
                $isSuppliesMenuOpen = $isSuppliesCatalog || $isSuppliesStockIn || $isSuppliesStockOut || $isSuppliesOrders || $isSuppliesReports || $isSuppliesDashboard;
                @endphp
                <li class="nav-item {{ $isSuppliesMenuOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $isSuppliesMenuOpen ? 'active' : '' }}">
                        <i class="nav-icon fas fa-boxes"></i>
                        <p>
                            Supplies
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('supplies.dashboard.show')
                        <li class="nav-item">
                            <a href="{{ route('dashboard.supplies-management') }}"
                                class="nav-link {{ $isSuppliesDashboard ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        @endcan
                        @can('supplies.catalog.show')
                        <li class="nav-item">
                            <a href="{{ route('supplies.catalog.index') }}"
                                class="nav-link {{ $isSuppliesCatalog ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Catalog</p>
                            </a>
                        </li>
                        @endcan
                        @can('supplies.stock-in.show')
                        <li class="nav-item">
                            <a href="{{ route('supplies.stock-ins.index') }}"
                                class="nav-link {{ $isSuppliesStockIn ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Stock In</p>
                            </a>
                        </li>
                        @endcan
                        @can('supplies.stock-out.show')
                        <li class="nav-item">
                            <a href="{{ route('supplies.stock-outs.index') }}"
                                class="nav-link {{ $isSuppliesStockOut ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Stock Out</p>
                            </a>
                        </li>
                        @endcan
                        @can('supplies.orders.show')
                        <li class="nav-item">
                            <a href="{{ route('supplies.orders.index') }}"
                                class="nav-link {{ $isSuppliesOrders ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Orders</p>
                            </a>
                        </li>
                        @endcan
                        @can('supplies.reports.show')
                        <li class="nav-item">
                            <a href="{{ route('supplies.reports.index') }}"
                                class="nav-link {{ $isSuppliesReports ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Reports</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                @canany(['vehicles.show', 'vehicle-assignments.show', 'fuel-records.verify', 'fuel-claims.show'])
                @php
                $isVehicleDashboard = Request::is('dashboard/vehicles');
                $isVehicleList = Request::is('vehicles*') && !Request::is('fuel-*') && !Request::is('vehicle-assignments*');
                $isFoaList = Request::is('vehicle-assignments*') && !Request::is('vehicle-assignments/my-trips*');
                $isFuelRecords = Request::is('fuel-records*') && !Request::is('fuel-records/my-requests*') && !Request::is('fuel-records/pending*');
                $isFuelClaims = Request::is('fuel-claims*');
                $isVehicleMenuOpen = $isVehicleDashboard || $isVehicleList || $isFoaList || $isFuelRecords || $isFuelClaims;
                @endphp
                <li class="nav-item {{ $isVehicleMenuOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $isVehicleMenuOpen ? 'active' : '' }}">
                        <i class="nav-icon fas fa-car"></i>
                        <p>
                            Vehicle Management
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('vehicles.show')
                        <li class="nav-item">
                            <a href="{{ route('dashboard.vehicles') }}"
                                class="nav-link {{ $isVehicleDashboard ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('vehicles.index') }}"
                                class="nav-link {{ $isVehicleList ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Vehicle List</p>
                            </a>
                        </li>
                        @endcan
                        @can('vehicle-assignments.show')
                        <li class="nav-item">
                            <a href="{{ route('vehicle-assignments.index') }}"
                                class="nav-link {{ $isFoaList ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Form of Assignment</p>
                            </a>
                        </li>
                        @endcan
                        @can('fuel-records.show')
                        <li class="nav-item">
                            <a href="{{ route('fuel-records.index') }}"
                                class="nav-link {{ $isFuelRecords ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Fuel Records</p>
                            </a>
                        </li>
                        @endcan
                        @can('fuel-claims.show')
                        <li class="nav-item">
                            <a href="{{ route('fuel-claims.index') }}"
                                class="nav-link {{ $isFuelClaims ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Fuel Claims</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @endcanany

                {{-- GENERAL SECTION --}}
                @canany(['letter-numbers.show', 'master-data.show', 'business-partners.show', 'meeting-rooms.show'])
                <li class="nav-header">GENERAL SECTION</li>
                @endcanany
                {{-- Letter Administration --}}
                @can('letter-numbers.show')
                <li
                    class="nav-item {{ Request::is('letter-numbers*') || Request::is('dashboard/letter-administration') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ Request::is('letter-numbers*') || Request::is('dashboard/letter-administration') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>
                            Letter Administration
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('dashboard.letter-administration') }}"
                                class="nav-link {{ Request::is('dashboard/letter-administration') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('letter-numbers.index') }}"
                                class="nav-link {{ Request::is('letter-numbers*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Letter Numbers</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- MASTER DATA --}}
                @canany(['master-data.show', 'business-partners.show', 'national-holidays.show', 'meeting-rooms.show', 'disciplinary-criteria.show', 'supplies.item-categories.show'])
                {{-- Master Data Dropdown --}}
                <li
                    class="nav-item {{ Request::is('banks*') || Request::is('religions*') || Request::is('positions*') || Request::is('departments*') || Request::is('projects*') || Request::is('grades*') || Request::is('levels*') || Request::is('transportations*') || Request::is('accommodations*') || Request::is('meeting-rooms*') || Request::is('letter-categories*') || Request::is('leave/types*') || Request::is('leave/national-holidays*') || Request::is('business-partners*') || Request::is('disciplinary-criteria*') || Request::is('supplies/item-categories*') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ Request::is('banks*') || Request::is('religions*') || Request::is('positions*') || Request::is('departments*') || Request::is('projects*') || Request::is('grades*') || Request::is('levels*') || Request::is('transportations*') || Request::is('accommodations*') || Request::is('meeting-rooms*') || Request::is('letter-categories*') || Request::is('leave/types*') || Request::is('leave/national-holidays*') || Request::is('business-partners*') || Request::is('disciplinary-criteria*') || Request::is('supplies/item-categories*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-database"></i>
                        <p>
                            Master Data
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        {{-- Employee Data Group --}}
                        <li class="nav-header"
                            style="font-size: 0.75rem; color: #6c757d; padding: 0.5rem 1rem 0.25rem;">
                            Employee Data
                        </li>
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
                            <a href="{{ url('grades') }}"
                                class="nav-link {{ Request::is('grades*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Grades</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('levels') }}"
                                class="nav-link {{ Request::is('levels*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Levels</p>
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
                            <a href="{{ url('religions') }}"
                                class="nav-link {{ Request::is('religions*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Religions</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('banks') }}"
                                class="nav-link {{ Request::is('banks*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Banks</p>
                            </a>
                        </li>
                        @can('disciplinary-criteria.show')
                        <li class="nav-item">
                            <a href="{{ route('disciplinary-criteria.index') }}"
                                class="nav-link {{ Request::is('disciplinary-criteria*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>PP Criteria</p>
                            </a>
                        </li>
                        @endcan

                        {{-- Official Travel Data Group --}}
                        <li class="nav-header"
                            style="font-size: 0.75rem; color: #6c757d; padding: 0.5rem 1rem 0.25rem;">
                            Official Travel Data
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('transportations') }}"
                                class="nav-link {{ Request::is('transportations*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Transportations</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('accommodations') }}"
                                class="nav-link {{ Request::is('accommodations*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Accommodations</p>
                            </a>
                        </li>

                        {{-- Room & Consumption Data --}}
                        @can('meeting-rooms.show')
                        <li class="nav-header"
                            style="font-size: 0.75rem; color: #6c757d; padding: 0.5rem 1rem 0.25rem;">
                            Room & Consumption Data
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('meeting-rooms.index') }}"
                                class="nav-link {{ Request::is('meeting-rooms*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Meeting Rooms</p>
                            </a>
                        </li>
                        @endcan

                        {{-- Supplies Data --}}
                        @can('supplies.item-categories.show')
                        <li class="nav-header"
                            style="font-size: 0.75rem; color: #6c757d; padding: 0.5rem 1rem 0.25rem;">
                            Supplies Data
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('supplies.item-categories.index') }}"
                                class="nav-link {{ Request::is('supplies/item-categories*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Item Categories</p>
                            </a>
                        </li>
                        @endcan

                        {{-- Letter Management Data Group --}}
                        <li class="nav-header"
                            style="font-size: 0.75rem; color: #6c757d; padding: 0.5rem 1rem 0.25rem;">
                            Letter Management Data
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('letter-categories') }}"
                                class="nav-link {{ Request::is('letter-categories*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Letter Categories</p>
                            </a>
                        </li>

                        {{-- Leave Management Data Group --}}
                        <li class="nav-header"
                            style="font-size: 0.75rem; color: #6c757d; padding: 0.5rem 1rem 0.25rem;">
                            Leave Management Data
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('leave.types.index') }}"
                                class="nav-link {{ Request::is('leave/types*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Leave Types</p>
                            </a>
                        </li>
                        @can('national-holidays.show')
                        <li class="nav-item">
                            <a href="{{ route('leave.national-holidays.index') }}"
                                class="nav-link {{ Request::is('leave/national-holidays*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>National Holidays</p>
                            </a>
                        </li>
                        @endcan

                        {{-- Flight Management Data Group --}}
                        <li class="nav-header"
                            style="font-size: 0.75rem; color: #6c757d; padding: 0.5rem 1rem 0.25rem;">
                            Flight Management Data
                        </li>
                        @can('business-partners.show')
                        <li class="nav-item">
                            <a href="{{ route('business-partners.index') }}"
                                class="nav-link {{ Request::is('business-partners*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Business Partners</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- ADMINISTRATOR --}}
                @canany(['users.show', 'roles.show', 'permissions.show', 'activity-logs.show',
                'fuel-bot-subscribers.show', 'fuel-bot-logs.show'])
                <li class="nav-header">SYSTEMS</li>
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
                @can('activity-logs.show')
                <li class="nav-item">
                    <a href="{{ route('activity-logs.index') }}"
                        class="nav-link {{ Request::is('activity-logs*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Activity Logs</p>
                    </a>
                </li>
                @endcan
                @canany(['fuel-bot-subscribers.show', 'fuel-bot-logs.show'])
                <li
                    class="nav-item {{ Request::is('fuel-bot-subscribers*') || Request::is('fuel-bot-logs*') ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ Request::is('fuel-bot-subscribers*') || Request::is('fuel-bot-logs*') ? 'active' : '' }}">
                        <i class="nav-icon fab fa-telegram-plane"></i>
                        <p>
                            Telegram Fuel Bot
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('fuel-bot-subscribers.show')
                        <li class="nav-item">
                            <a href="{{ route('fuel-bot-subscribers.index') }}"
                                class="nav-link {{ Request::is('fuel-bot-subscribers*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Whitelist</p>
                            </a>
                        </li>
                        @endcan
                        @can('fuel-bot-logs.show')
                        <li class="nav-item">
                            <a href="{{ route('fuel-bot-logs.index') }}"
                                class="nav-link {{ Request::is('fuel-bot-logs*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Activity Log</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @role('administrator')
                <li class="nav-item">
                    <a href="{{ route('debug.email-notifications.index') }}"
                        class="nav-link {{ Request::is('debug/email-notifications*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-envelope-open-text"></i>
                        <p>Debug Email Notify</p>
                    </a>
                </li>
                @endrole
                {{-- Approval Stages - Commented as requested --}}
                {{-- <li class="nav-item">
                            <a href="{{ route('approval.stages.index') }}"
                class="nav-link {{ Request::is('approval/stages*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-layer-group"></i>
                <p>Approval Stages</p>
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