@php
    $loadingOverlay = '<div class="overlay-wrapper">
        <div class="overlay">
            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
            <div class="text-bold pt-2">Loading...</div>
        </div>
    </div>';
@endphp

<div id="personal-detail-pane" class="content" role="tabpanel" aria-labelledby="personal-detail-pane-trigger">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h5 class="mb-0">Personal Detail</h5>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modal-employee-{{ $employee->id }}">
            <i class="fas fa-pen-square mr-1"></i> Edit Personal
        </button>
    </div>
    <div class="row">
        <div class="col-md-3 text-center">
            @if ($profile)
                <img class="img-fluid img-thumbnail" style="max-height: 250px;"
                    src="{{ asset('images/' . $profile->employee_id . '/' . $profile->filename) }}"
                    alt="User profile picture">
            @else
                <img class="img-fluid img-thumbnail" style="max-height: 250px;"
                    src="{{ asset('assets/dist/img/avatar6.png') }}" alt="Default profile picture">
            @endif
        </div>
        <div class="col-md-9">
            <dl class="row">
                <dt class="col-sm-4">Full Name</dt>
                <dd class="col-sm-8">{{ $employee->fullname ?? '-' }}</dd>
                <dt class="col-sm-4">ID Card No.</dt>
                <dd class="col-sm-8">{{ $employee->identity_card ?? '-' }}</dd>
                <dt class="col-sm-4">Place/Date of Birth</dt>
                <dd class="col-sm-8">{{ $employee->emp_pob ?? '-' }},
                    {{ $employee->emp_dob ? date('d M Y', strtotime($employee->emp_dob)) : '-' }}
                </dd>
                <dt class="col-sm-4">Blood Type</dt>
                <dd class="col-sm-8">{{ $employee->blood_type ?? '-' }}</dd>
                <dt class="col-sm-4">Religion</dt>
                <dd class="col-sm-8">{{ $employee->religion->religion_name ?? '-' }}</dd>
                <dt class="col-sm-4">Nationality</dt>
                <dd class="col-sm-8">{{ $employee->nationality ?? '-' }}</dd>
                <dt class="col-sm-4">Gender</dt>
                <dd class="col-sm-8">
                    {{ $employee->gender == 'male' ? 'Male' : ($employee->gender == 'female' ? 'Female' : '-') }}
                </dd>
                <dt class="col-sm-4">Marital</dt>
                <dd class="col-sm-8">{{ $employee->marital ?? '-' }}</dd>
            </dl>
            <h6 class="mt-4 mb-3 text-muted border-top pt-3">Address & Contact</h6>
            <dl class="row">
                <dt class="col-sm-4">Address</dt>
                <dd class="col-sm-8">{{ $employee->address ?? '-' }}</dd>
                <dt class="col-sm-4">Village</dt>
                <dd class="col-sm-8">{{ $employee->village ?? '-' }}</dd>
                <dt class="col-sm-4">Ward</dt>
                <dd class="col-sm-8">{{ $employee->ward ?? '-' }}</dd>
                <dt class="col-sm-4">District</dt>
                <dd class="col-sm-8">{{ $employee->district ?? '-' }}</dd>
                <dt class="col-sm-4">City</dt>
                <dd class="col-sm-8">{{ $employee->city ?? '-' }}</dd>
                <dt class="col-sm-4">Phone</dt>
                <dd class="col-sm-8">{{ $employee->phone ?? '-' }}</dd>
                <dt class="col-sm-4">Email</dt>
                <dd class="col-sm-8">{{ $employee->email ?? '-' }}</dd>
            </dl>
        </div>
    </div>
</div>

<style>
    .overlay-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
    }

    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
        padding-top: 2rem;
    }

    .overlay i {
        color: #007bff;
    }

    .content {
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease-in-out;
        position: relative;
    }

    .content.loaded {
        opacity: 1;
        visibility: visible;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const personalPane = document.getElementById('personal-detail-pane');
        const personalTrigger = document.getElementById('personal-detail-pane-trigger');

        if (personalTrigger) {
            personalTrigger.addEventListener('click', function() {
                // Show loading overlay
                const overlay = document.createElement('div');
                overlay.className = 'overlay-wrapper';
                overlay.innerHTML = `{!! $loadingOverlay !!}`;
                personalPane.appendChild(overlay);

                // Simulate loading delay (remove this in production)
                setTimeout(() => {
                    // Remove loading overlay
                    personalPane.removeChild(overlay);
                    // Show content
                    personalPane.classList.add('loaded');
                }, 500);
            });
        }
    });
</script>
