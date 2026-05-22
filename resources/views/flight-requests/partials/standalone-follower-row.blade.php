@php
    $rowIndex = $rowIndex ?? 0;
    $row = $row ?? [];
    $isManual = !empty($row['is_manual']);
    $adminId = $row['administration_id'] ?? '';
    $selectedAdmin = $followerEmployeeOptions->firstWhere('id', (int) $adminId);
    $title = $row['title'] ?? '';
    $titleValue = match (true) {
        in_array($title, ['Mr', 'Mr.'], true) => 'Mr.',
        in_array($title, ['Mrs', 'Mrs.'], true) => 'Mrs.',
        in_array($title, ['Inf', 'Inf.'], true) => 'Inf.',
        default => '',
    };
    $displayName = $isManual ? $row['follower_name'] ?? '' : $selectedAdmin['fullname'] ?? '';
    $displayNik = $isManual ? $row['nik'] ?? '' : $selectedAdmin['nik'] ?? '';
    $displayPhone = $isManual ? $row['phone_number'] ?? '' : $selectedAdmin['phone_number'] ?? '';
@endphp
<tr class="standalone-follower-row {{ $isManual ? 'follower-row--manual' : 'follower-row--employee' }}"
    data-index="{{ $rowIndex }}">
    <td class="follower-source-cell">
        <div class="follower-source-row d-flex align-items-center">
            <div class="follower-manual-toggle-wrap flex-shrink-0">
                <div class="custom-control custom-checkbox mb-0">
                    <input type="checkbox" class="custom-control-input follower-manual-toggle"
                        id="follower_manual_{{ $rowIndex }}" name="followers[{{ $rowIndex }}][is_manual]"
                        value="1" title="Fill follower information manually"
                        aria-label="Fill follower information manually" {{ $isManual ? 'checked' : '' }}>
                    <label class="custom-control-label" for="follower_manual_{{ $rowIndex }}"></label>
                </div>
            </div>
            <div class="follower-employee-select-wrap flex-grow-1 min-w-0 pl-2"
                style="{{ $isManual ? 'display:none !important;' : '' }}">
                <select class="form-control select2-standalone-follower"
                    name="followers[{{ $rowIndex }}][administration_id]" style="width: 100%;">
                    <option value="">Select Employee</option>
                    @foreach ($followerEmployeeOptions as $employee)
                        <option value="{{ $employee['id'] }}" data-fullname="{{ $employee['fullname'] }}"
                            data-nik="{{ $employee['nik'] }}" data-phone="{{ $employee['phone_number'] }}"
                            {{ (string) $adminId === (string) $employee['id'] ? 'selected' : '' }}>
                            {{ $employee['nik'] }} - {{ $employee['fullname'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </td>
    <td>
        <select class="form-control form-control-sm follower-title-select"
            name="followers[{{ $rowIndex }}][title]">
            <option value="" {{ $titleValue === '' ? 'selected' : '' }}>—</option>
            <option value="Mr." {{ $titleValue === 'Mr.' ? 'selected' : '' }}>Mr.</option>
            <option value="Mrs." {{ $titleValue === 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
            <option value="Inf." {{ $titleValue === 'Inf.' ? 'selected' : '' }}>Inf.</option>
        </select>
    </td>
    <td class="follower-name-cell">
        <span class="follower-employee-name text-break">{{ $isManual ? '' : $displayName }}</span>
        <input type="text" class="form-control form-control-sm follower-manual-name"
            name="followers[{{ $rowIndex }}][follower_name]" value="{{ $isManual ? $displayName : '' }}"
            placeholder="Name" style="{{ $isManual ? '' : 'display:none;' }}">
    </td>
    <td class="follower-id-cell">
        <span class="follower-employee-nik text-break">{{ $isManual ? '' : $displayNik }}</span>
        <input type="text" class="form-control form-control-sm follower-manual-nik"
            name="followers[{{ $rowIndex }}][nik]" value="{{ $isManual ? $displayNik : '' }}"
            placeholder="{{ $isManual ? 'KTP' : 'NIK' }}" style="{{ $isManual ? '' : 'display:none;' }}">
    </td>
    <td class="follower-phone-cell">
        <span class="follower-employee-phone text-break">{{ $isManual ? '' : $displayPhone }}</span>
        <input type="text" class="form-control form-control-sm follower-manual-phone"
            name="followers[{{ $rowIndex }}][phone_number]" value="{{ $isManual ? $displayPhone : '' }}"
            placeholder="Phone number" style="{{ $isManual ? '' : 'display:none;' }}">
    </td>
    <td class="text-center align-middle">
        <a href="javascript:void(0)" class="remove-standalone-follower" title="Remove">
            <i class="fas fa-times-circle text-danger"></i>
        </a>
    </td>
</tr>
