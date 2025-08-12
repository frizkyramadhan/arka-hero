<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title }} - ARKA Human Experience & Resource Optimization</title>

        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet" href="{{ asset('assets/dist/css/font.css') }}">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
        <!-- SweetAlert2 -->
        <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
        <!-- Select2 styles (global) -->
        <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
        <!-- pace-progress -->
        {{-- <link rel="stylesheet" href="{{ asset('assets/plugins/pace-progress/themes/black/pace-theme-flat-top.css') }}"> --}}

        @yield('styles')

        @stack('styles')

        <style>
            /* Fix approval badge color when sidebar item is active */
            .nav-link.active .approval-badge.badge-warning {
                background-color: #ffc107 !important;
                color: #212529 !important;
                border-color: #ffc107 !important;
                font-size: 0.75em !important;
                font-weight: 700 !important;
                line-height: 1 !important;
                text-align: center !important;
                white-space: nowrap !important;
                vertical-align: baseline !important;
                border-radius: 0.25rem !important;
                padding: 0.25em 0.4em !important;
            }
        </style>

    </head>
