<!-- Main Sidebar Container -->
<aside class="main-sidebar main-sidebar-custom sidebar-dark-primary elevation-4">
	<!-- Brand Logo -->
	<a href="{{ url('/') }}" class="brand-link">
		<img src="{{ asset('assets/dist/img/AdminLTELogo.png') }}" alt="Logo" class="brand-image img-circle elevation-3"
			style="opacity: .8">
		<span class="brand-text font-weight-light">ARAIM v.2</span>
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
					<a href="{{ url('/') }}"
						class="nav-link {{ Request::is('/') || Request::is('dashboard*') ? 'active' : '' }}">
						<i class="nav-icon fas fa-tachometer-alt"></i>
						<p>
							Dashboard
						</p>
					</a>
				</li>
				<li class="nav-item">
					<a href="{{ url('inventories') }}" class="nav-link {{ Request::is('inventories*') ? 'active' : '' }}">
						<i class="nav-icon fas fa-qrcode"></i>
						<p>
							Inventories
						</p>
					</a>
				</li>
				<li class="nav-item">
					<a href="{{ url('trackings') }}" class="nav-link {{ Request::is('trackings*') ? 'active' : '' }}">
						<i class="nav-icon fas fa-search"></i>
						<p>
							Tracking
						</p>
					</a>
				</li>
				@can('admin')
					<li class="nav-item {{ Request::is('basts*') || Request::is('bapbs*') ? 'menu-open' : '' }}">
						<a href="#" class="nav-link {{ Request::is('basts*') || Request::is('bapbs*') ? 'active' : '' }}">
							<i class="nav-icon fas fa-file-contract"></i>
							<p>
								Berita Acara
								<i class="right fas fa-angle-left"></i>
							</p>
						</a>
						<ul class="nav nav-treeview">
							<li class="nav-item">
								<a href="{{ url('basts') }}" class="nav-link {{ Request::is('basts*') ? 'active' : '' }}">
									<i class="far fa-circle nav-icon"></i>
									<p>Serah Terima</p>
								</a>
							</li>
							<li class="nav-item">
								<a href="{{ url('bapbs') }}" class="nav-link {{ Request::is('bapbs*') ? 'active' : '' }}">
									<i class="far fa-circle nav-icon"></i>
									<p>Peminjaman</p>
								</a>
							</li>
						</ul>
					</li>
				@endcan
				@cannot('user')
					<li class="nav-header">MASTER DATA</li>
					<li
						class="nav-item {{ Request::is('assets*') || Request::is('categories*') || Request::is('components*') ? 'menu-open' : '' }}">
						<a href="#"
							class="nav-link {{ Request::is('assets*') || Request::is('categories*') || Request::is('components*') ? 'active' : '' }}">
							<i class="nav-icon fas fa-boxes"></i>
							<p>
								Item Master Data
								<i class="fas fa-angle-left right"></i>
							</p>
						</a>
						<ul class="nav nav-treeview">
							<li class="nav-item">
								<a href="{{ url('assets') }}" class="nav-link {{ Request::is('assets*') ? 'active' : '' }}">
									<i class="far fa-circle nav-icon"></i>
									<p>Assets</p>
								</a>
							</li>
							<li class="nav-item">
								<a href="{{ url('categories') }}" class="nav-link {{ Request::is('categories*') ? 'active' : '' }}">
									<i class="far fa-circle nav-icon"></i>
									<p>Categories</p>
								</a>
							</li>
							@can('admin')
								<li class="nav-item">
									<a href="{{ url('components') }}" class="nav-link {{ Request::is('components*') ? 'active' : '' }}">
										<i class="far fa-circle nav-icon"></i>
										<p>Components</p>
									</a>
								</li>
							@endcan
						</ul>
					</li>
					<li
						class="nav-item {{ Request::is('employees*') || Request::is('positions*') || Request::is('departments*') || Request::is('projects*') ? 'menu-open' : '' }}">
						<a href="#"
							class="nav-link {{ Request::is('employees*') || Request::is('positions*') || Request::is('departments*') || Request::is('projects*') ? 'active' : '' }}">
							<i class="nav-icon fas fa-users"></i>
							<p>
								Human Resources
								<i class="fas fa-angle-left right"></i>
							</p>
						</a>
						<ul class="nav nav-treeview">
							<li class="nav-item">
								<a href="{{ url('employees') }}" class="nav-link {{ Request::is('employees*') ? 'active' : '' }}">
									<i class="far fa-circle nav-icon"></i>
									<p>Employees</p>
								</a>
							</li>
							<li class="nav-item">
								<a href="{{ url('positions') }}" class="nav-link {{ Request::is('positions*') ? 'active' : '' }}">
									<i class="far fa-circle nav-icon"></i>
									<p>Positions</p>
								</a>
							</li>
							<li class="nav-item">
								<a href="{{ url('departments') }}" class="nav-link {{ Request::is('departments*') ? 'active' : '' }}">
									<i class="far fa-circle nav-icon"></i>
									<p>Departments</p>
								</a>
							</li>
							<li class="nav-item">
								<a href="{{ url('projects') }}" class="nav-link {{ Request::is('projects*') ? 'active' : '' }}">
									<i class="far fa-circle nav-icon"></i>
									<p>Projects</p>
								</a>
							</li>
						</ul>
					</li>
				@endcannot
				@can('admin')
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
