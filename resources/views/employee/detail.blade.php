@extends('layouts.main')
@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $subtitle }}</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('employees') }}">Employees</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card mr-1"></i>
                        <strong>{{ $employee->fullname }}</strong>
                    </h3>
                    <div class="card-tools">
                        <a href="{{ url('employees/print/' . $employee->id) }}" class="btn btn-success" target="blank">
                            <i class="fas fa-print mr-1"></i> Print
                        </a>
                        @role('administrator')
                            <form action="{{ url('employees/' . $employee->id) }}" method="post"
                                onsubmit="return confirm('This employee and all associated data will be deleted. Are you sure?')"
                                class="d-inline ml-1">
                                @method('delete')
                                @csrf
                                <button class="btn btn-danger"><i class="fas fa-trash mr-1"></i> Delete Employee</button>
                            </form>
                        @endrole
                        <a href="{{ url('employees') }}" class="btn btn-warning ml-1">
                            <i class="fas fa-undo mr-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="bs-stepper">
                        <div class="bs-stepper-header" role="tablist">
                            <div class="step" data-target="#personal-detail-pane">
                                <button type="button" class="step-trigger" role="tab"
                                    aria-controls="personal-detail-pane" id="personal-detail-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-id-card"></i></span>
                                    <span class="bs-stepper-label">Personal</span>
                                </button>
                            </div>
                            <div class="step" data-target="#administration-pane">
                                <button type="button" class="step-trigger" role="tab"
                                    aria-controls="administration-pane" id="administration-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-briefcase"></i></span>
                                    <span class="bs-stepper-label">Employment</span>
                                </button>
                            </div>
                            <div class="step{{ $bank == null ? ' step-empty' : '' }}" data-target="#bank-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="bank-pane"
                                    id="bank-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-money-check-alt"></i></span>
                                    <span class="bs-stepper-label">&nbsp;&nbsp;Bank&nbsp;&nbsp;</span>
                                </button>
                            </div>
                            <div class="step{{ $tax == null ? ' step-empty' : '' }}" data-target="#tax-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="tax-pane"
                                    id="tax-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-file-invoice-dollar"></i></span>
                                    <span class="bs-stepper-label">&nbsp;&nbsp;Tax&nbsp;&nbsp;</span>
                                </button>
                            </div>
                            <div class="step{{ $insurances->isEmpty() ? ' step-empty' : '' }}"
                                data-target="#insurance-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="insurance-pane"
                                    id="insurance-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-heartbeat"></i></span>
                                    <span class="bs-stepper-label">Insurances</span>
                                </button>
                            </div>
                            <div class="step{{ $licenses->isEmpty() ? ' step-empty' : '' }}" data-target="#license-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="license-pane"
                                    id="license-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-id-badge"></i></span>
                                    <span class="bs-stepper-label">Licenses</span>
                                </button>
                            </div>
                            <div class="step{{ $families->isEmpty() ? ' step-empty' : '' }}" data-target="#family-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="family-pane"
                                    id="family-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-users"></i></span>
                                    <span class="bs-stepper-label">Families</span>
                                </button>
                            </div>
                            <div class="step{{ $educations->isEmpty() ? ' step-empty' : '' }}"
                                data-target="#education-pane">
                                <button type="button" class="step-trigger" role="tab"
                                    aria-controls="education-pane" id="education-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-graduation-cap"></i></span>
                                    <span class="bs-stepper-label">Educations</span>
                                </button>
                            </div>
                            <div class="step{{ $courses->isEmpty() ? ' step-empty' : '' }}" data-target="#course-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="course-pane"
                                    id="course-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-certificate"></i></span>
                                    <span class="bs-stepper-label">Courses</span>
                                </button>
                            </div>
                            <div class="step{{ $jobs->isEmpty() ? ' step-empty' : '' }}" data-target="#jobexp-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="jobexp-pane"
                                    id="jobexp-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-history"></i></span>
                                    <span class="bs-stepper-label">Experiences</span>
                                </button>
                            </div>
                            <div class="step{{ $units->isEmpty() ? ' step-empty' : '' }}" data-target="#unit-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="unit-pane"
                                    id="unit-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-truck"></i></span>
                                    <span class="bs-stepper-label">Units</span>
                                </button>
                            </div>
                            <div class="step{{ $emergencies->isEmpty() ? ' step-empty' : '' }}"
                                data-target="#emergency-pane">
                                <button type="button" class="step-trigger" role="tab"
                                    aria-controls="emergency-pane" id="emergency-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-phone-alt"></i></span>
                                    <span class="bs-stepper-label">Emergencies</span>
                                </button>
                            </div>
                            <div class="step{{ $additional == null ? ' step-empty' : '' }}"
                                data-target="#additional-pane">
                                <button type="button" class="step-trigger" role="tab"
                                    aria-controls="additional-pane" id="additional-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-info-circle"></i></span>
                                    <span class="bs-stepper-label">Additional</span>
                                </button>
                            </div>
                            <div class="step{{ $images->isEmpty() ? ' step-empty' : '' }}" data-target="#image-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="image-pane"
                                    id="image-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-images"></i></span>
                                    <span class="bs-stepper-label">Images</span>
                                </button>
                            </div>
                        </div>

                        <div class="bs-stepper-content p-3">
                            @include('employee.components.personal-detail-pane')
                            @include('employee.components.administration-pane')
                            @include('employee.components.bank-pane')
                            @include('employee.components.tax-pane')
                            @include('employee.components.insurance-pane')
                            @include('employee.components.license-pane')
                            @include('employee.components.family-pane')
                            @include('employee.components.education-pane')
                            @include('employee.components.course-pane')
                            @include('employee.components.jobexp-pane')
                            @include('employee.components.unit-pane')
                            @include('employee.components.emergency-pane')
                            @include('employee.components.additional-pane')
                            @include('employee.components.image-pane')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button"
            aria-label="Scroll to top">
            <i class="fas fa-chevron-up"></i>
        </a>

    </section>

    @include('employee.modal-employee')
    @include('employee.modal-administration')
    @include('employee.modal-termination-employment')
    @include('employee.modal-bank')
    @include('employee.modal-tax')
    @include('employee.modal-insurance')
    @include('employee.modal-family')
    @include('employee.modal-education')
    @include('employee.modal-course')
    @include('employee.modal-job')
    @include('employee.modal-unit')
    @include('employee.modal-license')
    @include('employee.modal-emergency')
    @include('employee.modal-additional')
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bs-stepper/css/bs-stepper.min.css') }}">
    <style>
        /* Critical CSS - Load First */
        .content {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out;
        }

        .content.loaded {
            opacity: 1;
            visibility: visible;
        }

        .card-body h5,
        .card-body h6 {
            color: #0056b3;
        }

        /* Table Styles */
        .table-modern {
            width: 100%;
            margin-bottom: 1rem;
            background-color: transparent;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-modern thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            padding: 1rem;
            border-bottom: 2px solid #dee2e6;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .table-modern tbody tr {
            transition: all 0.2s ease;
        }

        .table-modern tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .table-modern tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
            color: #212529;
        }

        .table-modern .action-buttons {
            white-space: nowrap;
            text-align: center;
        }

        .table-modern .action-buttons .btn {
            padding: 0.375rem 0.75rem;
            margin: 0 0.25rem;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
        }

        .table-modern .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table-modern .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
            border-radius: 0.25rem;
        }

        .table-modern .text-center {
            text-align: center;
        }

        .table-modern .text-muted {
            color: #6c757d !important;
        }

        .table-modern .empty-state {
            padding: 2rem;
            text-align: center;
            color: #6c757d;
        }

        .table-modern .empty-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #adb5bd;
        }

        /* Responsive Table */
        .table-responsive {
            position: relative;
            width: 100%;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
            box-shadow: 0 0 1px rgba(0, 0, 0, 0.1);
        }

        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        /* Status Badges */
        .badge-status {
            padding: 0.5em 0.75em;
            font-weight: 500;
            border-radius: 0.25rem;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .badge-status.active {
            background-color: #28a745;
            color: white;
        }

        .badge-status.inactive {
            background-color: #dc3545;
            color: white;
        }

        .badge-status.pending {
            background-color: #ffc107;
            color: #212529;
        }

        /* Action Buttons */
        .btn-action {
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
            margin: 0 0.25rem;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-action i {
            margin-right: 0.25rem;
        }

        /* Empty State */
        .empty-state {
            padding: 2rem;
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            margin: 1rem 0;
        }

        .empty-state i {
            font-size: 2.5rem;
            color: #adb5bd;
            margin-bottom: 1rem;
        }

        .empty-state h6 {
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #adb5bd;
            margin-bottom: 0;
        }

        /* Loading Indicator */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .page-loader.hidden {
            display: none;
        }

        /* Optimized Stepper Styles */
        .bs-stepper {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .bs-stepper.initialized {
            opacity: 1;
        }

        .bs-stepper .step-trigger {
            padding: 8px 5px;
            color: #6c757d;
            background-color: transparent;
            transition: background-color 0.2s ease;
            user-select: none;
            -webkit-user-select: none;
        }

        .bs-stepper .step-trigger:hover {
            background-color: #f8f9fa;
            color: #0056b3;
        }

        .bs-stepper .bs-stepper-circle {
            background-color: #adb5bd;
            width: 35px;
            height: 35px;
            line-height: 32px;
            font-size: 1rem;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
            will-change: transform;
        }

        .bs-stepper .step-trigger .bs-stepper-label {
            color: #495057;
            font-weight: 500;
            margin-left: 8px;
            font-size: small;
            transition: color 0.2s ease;
        }

        .bs-stepper .step.active .step-trigger .bs-stepper-circle {
            background-color: #007bff;
            box-shadow: 0 2px 5px rgba(0, 123, 255, 0.4);
        }

        .bs-stepper .step.active .step-trigger .bs-stepper-label {
            color: #007bff;
            font-weight: 600;
        }

        /* Optimized Header Styles */
        .bs-stepper-header {
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
            border-bottom: 1px solid #dee2e6;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .bs-stepper-header::-webkit-scrollbar {
            height: 6px;
        }

        .bs-stepper-header::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .bs-stepper-header::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        /* Optimized Content Animation */
        .bs-stepper-content .content {
            opacity: 0;
            transform: translateZ(0);
            transition: opacity 0.3s ease-out;
            will-change: opacity;
        }

        .bs-stepper-content .content.active {
            opacity: 1;
        }

        /* Optimized Table Styles */
        .table-sm th,
        .table-sm td {
            padding: 0.4rem;
        }

        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            display: none;
            z-index: 1030;
            transform: translateZ(0);
            will-change: transform, opacity;
            transition: opacity 0.2s ease;
        }

        /* Empty Step Styles */
        .bs-stepper-header .step.step-empty .step-trigger {
            opacity: 0.7;
            cursor: pointer;
        }

        .bs-stepper-header .step.step-empty .step-trigger:hover {
            opacity: 1;
            background-color: #f8f9fa;
        }

        .bs-stepper-header .step.step-empty .step-trigger .bs-stepper-label {
            color: #adb5bd;
            font-style: italic;
        }

        .bs-stepper-header .step.step-empty .step-trigger .bs-stepper-circle {
            background-color: #e9ecef;
            border: 1px dashed #adb5bd;
        }

        .bs-stepper-header .step.step-empty.active .step-trigger {
            opacity: 1;
        }

        .bs-stepper-header .step.step-empty.active .step-trigger .bs-stepper-label {
            color: #6c757d;
            font-weight: 500;
        }

        .bs-stepper-header .step.step-empty.active .step-trigger .bs-stepper-circle {
            background-color: #ced4da;
            border: 1px dashed #6c757d;
        }

        /* Responsive Optimizations */
        @media (max-width: 768px) {
            .bs-stepper .step-trigger {
                padding: 10px;
                text-align: center;
            }

            .bs-stepper .step-trigger .bs-stepper-label {
                display: block;
                margin: 5px 0 0;
                white-space: normal;
            }
        }

        /* Print Optimizations */
        @media print {
            .bs-stepper-header {
                overflow: visible;
            }

            .back-to-top {
                display: none !important;
            }
        }

        /* Loading state styles */
        .select2-container.loading .select2-selection {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath fill='%23007bff' d='M12,4V2A10,10 0 0,0 2,12H4A8,8 0 0,1 12,4Z'%3E%3CanimateTransform attributeName='transform' type='rotate' from='0 12 12' to='360 12 12' dur='1s' repeatCount='indefinite'/%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right center;
            background-size: 20px;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.min.js') }}" defer></script>
    <script src="{{ asset('assets/plugins/bs-stepper/js/bs-stepper.min.js') }}" defer></script>
    <script src="{{ asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}" defer></script>

    <script>
        // App initialization
        (function() {
            // Add loading indicator to body
            document.body.insertAdjacentHTML('afterbegin',
                '<div class="page-loader"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>'
            );

            // Global variables
            let stepper;
            const stepMap = {
                '#personal': 1,
                '#administration': 2,
                '#bank': 3,
                '#tax': 4,
                '#insurance': 5,
                '#license': 6,
                '#family': 7,
                '#education': 8,
                '#course': 9,
                '#jobexp': 10,
                '#unit': 11,
                '#emergency': 12,
                '#additional': 13,
                '#image': 14
            };

            // Main initialization when DOM is ready
            document.addEventListener('DOMContentLoaded', function() {
                hideLoader();
                initComponents();
                attachEventListeners();
            });

            /**
             * Remove loader and show content
             */
            function hideLoader() {
                document.querySelector('.page-loader').classList.add('hidden');
                document.querySelector('.content').classList.add('loaded');
            }

            /**
             * Initialize all page components
             */
            function initComponents() {
                initStepper();
                initBackToTop();
                initLightbox();
                initCustomFileInput();
                initDepartmentHandlers();
                handleInitialHash();
            }

            /**
             * Attach global event listeners
             */
            function attachEventListeners() {
                // Handle hash changes
                window.addEventListener('hashchange', handleHashChange);

                // Optimize back to top click
                const backToTop = document.getElementById('back-to-top');
                backToTop.addEventListener('click', scrollToTop);

                // Add scroll listener for back-to-top button visibility
                let ticking = false;
                window.addEventListener('scroll', function() {
                    if (!ticking) {
                        window.requestAnimationFrame(function() {
                            backToTop.style.display = window.pageYOffset > 100 ? 'block' : 'none';
                            ticking = false;
                        });
                        ticking = true;
                    }
                });

                // Attach step trigger click listeners
                document.querySelectorAll('.step-trigger').forEach(trigger => {
                    trigger.addEventListener('click', function() {
                        const paneId = this.getAttribute('aria-controls');
                        if (paneId) {
                            const hash = paneId.replace('-pane', '');
                            window.location.hash = hash;
                            scrollToTop();
                        }
                    });
                });
            }

            /**
             * Initialize the stepper component
             */
            function initStepper() {
                stepper = new Stepper(document.querySelector('.bs-stepper'), {
                    linear: false,
                    animation: true,
                    selectors: {
                        steps: '.step',
                        trigger: '.step-trigger',
                        stepper: '.bs-stepper'
                    }
                });

                // Ensure all panes can be activated when steps are clicked
                document.querySelectorAll('.step').forEach(function(step) {
                    step.addEventListener('click', function() {
                        let targetPane = this.getAttribute('data-target');
                        if (targetPane) {
                            document.querySelectorAll('.content').forEach(pane => pane.classList.remove(
                                'active'));
                            document.querySelector(targetPane).classList.add('active');
                        }
                    });
                });

                document.querySelector('.bs-stepper').classList.add('initialized');
            }

            /**
             * Initialize back to top button
             */
            function initBackToTop() {
                document.getElementById('back-to-top').style.display = 'none';
            }

            /**
             * Handle smooth scrolling to top
             */
            function scrollToTop(e) {
                if (e) e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }

            /**
             * Process URL hash and navigate to correct tab
             */
            function handleInitialHash() {
                const hash = window.location.hash.toLowerCase();
                if (hash && stepMap.hasOwnProperty(hash)) {
                    stepper.to(stepMap[hash]);
                    scrollToTop();
                }
            }

            /**
             * Handle hash change events
             */
            function handleHashChange() {
                const hash = window.location.hash.toLowerCase();
                if (hash && stepMap.hasOwnProperty(hash)) {
                    stepper.to(stepMap[hash]);
                    scrollToTop();
                }
            }

            /**
             * Initialize lightbox for images
             */
            function initLightbox() {
                document.querySelectorAll('[data-toggle="lightbox"]').forEach(function(el) {
                    el.addEventListener('click', function(e) {
                        e.preventDefault();
                        $(this).ekkoLightbox({
                            alwaysShowClose: true,
                            loadingMessage: 'Loading...'
                        });
                    });
                });
            }

            /**
             * Initialize custom file input
             */
            function initCustomFileInput() {
                bsCustomFileInput.init();
            }

            /**
             * Fetch department based on position ID
             */
            function fetchDepartment(positionId, departmentElement) {
                if (!positionId) {
                    $(departmentElement).val('');
                    return;
                }

                $.ajax({
                    url: "{{ route('employees.getDepartment') }}",
                    type: "GET",
                    data: {
                        position_id: positionId
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data && data.department_name) {
                            $(departmentElement).val(data.department_name);
                        } else {
                            $(departmentElement).val('');
                        }
                    },
                    error: function() {
                        $(departmentElement).val('');
                    }
                });
            }

            /**
             * Initialize department handlers
             */
            function initDepartmentHandlers() {
                // Handle position change for add modal
                $('#position_id_add').on('change', function() {
                    fetchDepartment($(this).val(), $('#department_add'));
                });

                // Handle position change for edit modals
                @foreach ($administrations as $administration)
                    $('#position_id_edit_{{ $administration->id }}').on('change', function() {
                        fetchDepartment($(this).val(), $('#department_edit_{{ $administration->id }}'));
                    });
                @endforeach

                // Initialize departments when modals are shown
                $('[id^="modal-administration"]').on('shown.bs.modal', function() {
                    const modalId = $(this).attr('id');
                    if (modalId === 'modal-administration') {
                        // Add modal
                        if ($('#position_id_add').val()) {
                            fetchDepartment($('#position_id_add').val(), $('#department_add'));
                        }
                    } else {
                        // Edit modal
                        const id = modalId.replace('modal-administration-', '');
                        if ($(`#position_id_edit_${id}`).val()) {
                            fetchDepartment($(`#position_id_edit_${id}`).val(), $(`#department_edit_${id}`));
                        }
                    }
                });
            }
        })();
    </script>
@endsection
