<style>
.sidebarTitle {
	margin-left:30px;
	margin-top:10px !important;	
	margin-top:0;
	font-weight:bold;
}
</style>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <li class="nav-item nav-profile">
              <div class="nav-link"><center>
                <div class="logo">
				<a class="navbar-brand brand-logo" href="index.html"><img src="{{ asset('images/logo-color.png') }}" width="130px" alt="logo"/></a>
                </div></center>
                <!--<div class="profile-name">
                  <p class="name">
                    Marina Michel
                  </p>
                  <p class="designation">
                    Super Admin
                  </p>
                </div>-->
              </div>
            </li>
		        @can ('dashboard')
            <li class="nav-item"> 
              <a class="nav-link" href="{{ route('home') }}">
                <i class="icon-menu menu-icon"></i>
                <span class="menu-title">Dashboard</span>
              </a>
            </li>
            @endcan
            @if(Auth::user()->role == "employee")
            <li class="nav-item"> 
              <a class="nav-link" href="{{ route('attendance') }}">
                <i class="fa fa-user menu-icon" aria-hidden="true"></i>
                <span class="menu-title">Own Attendance</span>
              </a>
            </li>
            @endif
            <li class="nav-item"> 
              <a class="nav-link" href="{{ route('daily_scan') }}">
                <i class="fa fa-calendar-check-o menu-icon" aria-hidden="true"></i>
                <span class="menu-title">Daily Scan</span>
              </a>
            </li>
		        @can ('notification-list')
           <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#noti" aria-expanded="false" aria-controls="noti">
                <i class="icon-bell menu-icon"></i>
                <span class="menu-title">Notification</span>
                <span class="badge badge-own"><i class="fa fa-angle-right" style="color:white"></i><i class="fa fa-angle-right" style="color:white"></i></span>
              </a>
              <div class="collapse" id="noti">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('notification.index') }}"> Notification Management </a></li>
		              @can ('notification-create')
                  <li class="nav-item"> <a class="nav-link" href="{{ route('notification.create') }}"> Add Notification </a></li>
                  @endcan
                </ul>
              </div>
            </li>
            @endcan
			<h6 class="sidebarTitle">MANAGEMENT<h6>
		        @can ('employee-list')
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
                <i class="icon-user menu-icon"></i>
                <span class="menu-title">Employee</span>
                <span class="badge badge-own"><i class="fa fa-angle-right" style="color:white"></i><i class="fa fa-angle-right" style="color:white"></i></span>
              </a>
              <div class="collapse" id="auth">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('employee.index') }}"> Employee Management </a></li>
		              @can ('employee-create')
                  <li class="nav-item"> <a class="nav-link" href="{{ route('employee.create') }}"> Add Employee </a></li>
                  @endcan
                </ul>
              </div>
            </li>
            @endcan
		        @can ('tenant-list')
            <!--<li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#auth2" aria-expanded="false" aria-controls="auth2">
                <i class="icon-user menu-icon"></i>
                <span class="menu-title">Tenant</span>
                <span class="badge badge-own"><i class="fa fa-angle-right" style="color:white"></i><i class="fa fa-angle-right" style="color:white"></i></span>
              </a>
              <div class="collapse" id="auth2">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('tenant.index') }}"> Tenant Management </a></li>
		              @can ('tenant-create')
                  <li class="nav-item"> <a class="nav-link" href="{{ route('tenant.create') }}"> Add Tenant </a></li>
                  @endcan
                </ul>
              </div>
            </li>
            @endcan
		        @can ('visitor-list')
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#auth3" aria-expanded="false" aria-controls="auth3">
                <i class="icon-user menu-icon"></i>
                <span class="menu-title">Visitor</span>
                <span class="badge badge-own"><i class="fa fa-angle-right" style="color:white"></i><i class="fa fa-angle-right" style="color:white"></i></span>
              </a>
              <div class="collapse" id="auth3">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('visitor.index') }}"> Visitor Management </a></li>
		              @can ('visitor-create')
                  <li class="nav-item"> <a class="nav-link" href="{{ route('visitor.create') }}"> Add Visitor </a></li>
                  @endcan
                </ul>
              </div>
            </li>-->
            @endcan
			<h6 class="sidebarTitle">REPORT<h6>
		        @can ('report-building_access_index')
            <li class="nav-item">
              <a class="nav-link" href="{{ route('report.employee_index') }}">
                <i class="icon-handbag menu-icon"></i>
                <span class="menu-title">Employee Attendance</span>
              </a>
            </li>
            @endcan
		        @can ('report-employee_index')
            <li class="nav-item">
              <a class="nav-link" href="{{ route('report.building_access_index') }}">
                <i class="icon-handbag menu-icon"></i>
                <span class="menu-title">Daily Scan</span>
              </a>
            </li>
            @endcan
			<h6 class="sidebarTitle">Setting<h6>
		        @can ('access-list')
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#access" aria-expanded="false" aria-controls="access">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">Access</span>
                <span class="badge badge-own"><i class="fa fa-angle-right" style="color:white"></i><i class="fa fa-angle-right" style="color:white"></i></span>
              </a>
              <div class="collapse" id="access">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('access.index') }}"> Access Management </a></li>
                  <!--<li class="nav-item"> <a class="nav-link" href="{{ route('access.create') }}"> Add Access </a></li>-->
                </ul>
              </div>
            </li>
            @endcan
		        @can ('department-list')
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#department" aria-expanded="false" aria-controls="department">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">Department</span>
                <span class="badge badge-own"><i class="fa fa-angle-right" style="color:white"></i><i class="fa fa-angle-right" style="color:white"></i></span>
              </a>
              <div class="collapse" id="department">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('department.index') }}"> Department Management </a></li>
		              @can ('department-create')
                  <li class="nav-item"> <a class="nav-link" href="{{ route('department.create') }}"> Add Department </a></li>
                  @endcan
                </ul>
              </div>
            </li>
            @endcan
		        @can ('company-list')
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#company" aria-expanded="false" aria-controls="company">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">Company</span>
                <span class="badge badge-own"><i class="fa fa-angle-right" style="color:white"></i><i class="fa fa-angle-right" style="color:white"></i></span>
              </a>
              <div class="collapse" id="company">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('company.index') }}">Company Management </a></li>
		              @can ('company-create')
                  <li class="nav-item"> <a class="nav-link" href="{{ route('company.create') }}"> Add Company </a></li>
                  @endcan
                </ul>
              </div>
            </li>
            @endcan
		        @can ('working_hour-list')
            <li class="nav-item">
              <a class="nav-link" href="{{ route('working_hour.index') }}">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">Working Hour</span>
              </a>
            </li>
            @endcan
		        @can ('holiday-list')
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#holiday" aria-expanded="false" aria-controls="holiday">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">Holiday</span>
                <span class="badge badge-own"><i class="fa fa-angle-right" style="color:white"></i><i class="fa fa-angle-right" style="color:white"></i></span>
              </a>
              <div class="collapse" id="holiday">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('holiday.index') }}"> Holiday Management </a></li>
		              @can ('holiday-create')
                  <li class="nav-item"> <a class="nav-link" href="{{ route('holiday.create') }}"> Add Holiday </a></li>
                  @endcan
                </ul>
              </div>
            </li>
            @endcan
		        @can ('role-list')
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#user_role" aria-expanded="false" aria-controls="user_role">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">User Role</span>
                <span class="badge badge-own"><i class="fa fa-angle-right" style="color:white"></i><i class="fa fa-angle-right" style="color:white"></i></span>
              </a>
              <div class="collapse" id="user_role">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('user.role_index') }}">User Role Management</a></li>
                </ul>
              </div>
            </li>
            @endcan
		        @can ('user-list')
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#user" aria-expanded="false" aria-controls="user">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">User</span>
                <span class="badge badge-own"><i class="fa fa-angle-right" style="color:white"></i><i class="fa fa-angle-right" style="color:white"></i></span>
              </a>
              <div class="collapse" id="user">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('user.index') }}"> User Management </a></li>
		              @can ('user-create')
                  <li class="nav-item"> <a class="nav-link" href="{{ route('user.create') }}"> Add User </a></li>
                  @endcan
                </ul>
              </div>
            </li>
            @endcan
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#leave" aria-expanded="false" aria-controls="leave">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">Leave</span>
                <span class="badge badge-own"><i class="fa fa-angle-right" style="color:white"></i><i class="fa fa-angle-right" style="color:white"></i></span>
              </a>
              <div class="collapse" id="leave">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('leave_type.index') }}"> Leave Type </a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('leave_reviewer.index') }}"> Leave Reviewer </a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('leave_approver.index') }}"> Leave Approver </a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link">
                <span class="menu-title"></span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link">
                <span class="menu-title"></span>
              </a>
            </li>
          </ul>
        </nav>