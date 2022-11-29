@include('layouts.partials.header')

@include('layouts.partials.navbar')

@include('layouts.partials.sidebar')


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  @yield('content')
</div>

@include('layouts.partials.footer')
@include('layouts.partials.scripts')
