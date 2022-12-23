<!-- Main Sidebar Container -->
<aside class="main-sidebar main-sidebar-custom sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="{{ url('/') }}" class="brand-link">
    <img src="{{ asset('assets/dist/img/logo.png') }}" alt="Logo" class="brand-image mt-1 ml-4" style="width:50%">
    <span class="brand-text font-weight-light"><b>HCS</b>SIS</span>
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
        <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-sidebar">
            <i class="fas fa-search fa-fw"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
        <li class="nav-item">
          <a href="{{ url('/') }}" class="nav-link {{ Request::is('/') || Request::is('dashboard*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              Dashboard
            </p>
          </a>
        </li>
        @cannot('user')
        <li class="nav-item {{ Request::is('employees*') || Request::is('banks*') || Request::is('religions*') || Request::is('positions*') || Request::is('departments*') || Request::is('projects*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ Request::is('employees*') || Request::is('banks*') || Request::is('religions*') || Request::is('positions*') || Request::is('departments*') || Request::is('projects*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-database"></i>
            <p>
              Employee Master Data
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
           
            
            <li class="nav-item">
              <a href="{{ url('departments') }}" class="nav-link {{ Request::is('departments*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-building"></i>
                <p>Departments</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('projects') }}" class="nav-link {{ Request::is('projects*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-globe"></i>
                <p>Projects</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('religions') }}" class="nav-link {{ Request::is('religions*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-heart"></i>
                <p>Religions</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('banks') }}" class="nav-link {{ Request::is('banks*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-money-check-alt"></i>
                <p>Banks</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('positions') }}" class="nav-link {{ Request::is('positions*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-sitemap"></i>
                <p>Positions</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('employees') }}" class="nav-link {{ Request::is('employees*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-users"></i>
                <p>
                  Employees
                </p>
              </a>
            </li>
          </ul>
        </li>
        @endcannot
        @cannot('user')
        <li class="nav-item {{ Request::is('licenses*') || Request::is('insurances*') || Request::is('families*') || Request::is('educations*') || Request::is('courses*') || Request::is('emrgcalls*') || Request::is('additionaldatas*') || Request::is('employeebanks*') || Request::is('administrations*') || Request::is('jobexperiences*') || Request::is('operableunits*') || Request::is('taxidentifications*')  ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ Request::is('licenses*') || Request::is('insurances*') || Request::is('families*') || Request::is('educations*') || Request::is('courses*') || Request::is('emrgcalls*') || Request::is('additionaldatas*') || Request::is('employeebanks*') || Request::is('administrations*') || Request::is('jobexperiences*') || Request::is('operableunits*') || Request::is('taxidentifications*')  ? 'active' : '' }}">
            <i class="nav-icon fa fa-table"></i>
            <p>
              Summary Employee
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ url('licenses') }}" class="nav-link {{ Request::is('licenses*') ? 'active' : '' }}">
                <i class="nav-icon fa fa-car"></i>
                <p>
                  Driver Licensee
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('insurances') }}" class="nav-link {{ Request::is('insurances*') ? 'active' : '' }}">
                <i class="nav-icon fa fa-medkit"></i>
                <p>
                  Insurances
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('families') }}" class="nav-link {{ Request::is('families*') ? 'active' : '' }}">
                <i class="nav-icon fa fa-address-card"></i>
                <p>
                  Employee Family
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('educations') }}" class="nav-link {{ Request::is('educations*') ? 'active' : '' }}">
                <i class="nav-icon fa fa-university"></i>
                <p>
                  Education
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('courses') }}" class="nav-link {{ Request::is('courses*') ? 'active' : '' }}">
                <i class="nav-icon fa fa-graduation-cap"></i>
                <p>
                  Courses
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('emrgcalls') }}" class="nav-link {{ Request::is('emrgcalls*') ? 'active' : '' }}">
                <i class="nav-icon fa fa-ambulance"></i>
                <p>
                  Emergency Call
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('additionaldatas') }}" class="nav-link {{ Request::is('additionaldatas*') ? 'active' : '' }}">
                <i class="nav-icon fa fa-list"></i>
                <p>
                  Additional Data
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('employeebanks') }}" class="nav-link {{ Request::is('employeebanks*') ? 'active' : '' }}">
                <i class="nav-icon fa fa-credit-card"></i>
                <p>
                  Employee Banks
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('administrations') }}" class="nav-link {{ Request::is('administrations*') ? 'active' : '' }}">
                <i class="nav-icon fa fa-folder"></i>
                <p>
                 Administration
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('jobexperiences') }}" class="nav-link {{ Request::is('jobexperiences*') ? 'active' : '' }}">
                <i class="nav-icon fa fa-building"></i>
                <p>
                  Job Experience
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('operableunits') }}" class="nav-link {{ Request::is('operableunits*') ? 'active' : '' }}">
                <i class="nav-icon fa fa-truck"></i>
                <p>
                  Operable Units
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ url('taxidentifications') }}" class="nav-link {{ Request::is('taxidentifications*') ? 'active' : '' }}">
                <i class="nav-icon fa fa-user-md"></i>
                <p>
                  Tax Identification
                </p>
              </a>
            </li>
          </ul>
        </li>
        @endcannot
        @can('superadmin')
        <li class="nav-header">ADMINISTRATOR</li>
        <li class="nav-item">
          <a href="{{ url('users') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-key"></i>
            <p>Users</p>
          </a>
        </li>
        @endcan
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
