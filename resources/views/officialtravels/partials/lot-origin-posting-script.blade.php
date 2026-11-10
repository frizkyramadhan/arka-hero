function lotOriginPosting($option, originId) {
    const primary = {
        position: $option.attr('data-position') || '-',
        project: $option.attr('data-project') || '-',
        department: $option.attr('data-department') || '-',
        departmentId: $option.attr('data-department-id') || ''
    };
    if (!$option.length) {
        return primary;
    }
    let acting = [];
    try {
        acting = JSON.parse($option.attr('data-acting') || '[]') || [];
    } catch (e) {
        acting = [];
    }
    if (!acting.length || !originId) {
        return primary;
    }
    if (String($option.attr('data-project-id') || '') === String(originId)) {
        return primary;
    }
    const match = acting.find(function(row) {
        return String(row.project_id) === String(originId);
    });
    if (!match) {
        return primary;
    }
    return {
        position: match.position_name || '-',
        project: match.project_name || '-',
        department: match.department_name || '-',
        departmentId: match.department_id || ''
    };
}

function applyLotOriginPosting($option, $position, $project, $department) {
    let acting = [];
    try {
        acting = JSON.parse($option.attr('data-acting') || '[]') || [];
    } catch (e) {
        acting = [];
    }
    if (!acting.length) {
        return false;
    }
    const posting = lotOriginPosting($option, $('#official_travel_origin').val());
    $position.text(posting.position || '-');
    $project.text(posting.project || '-');
    $department.text(posting.department || '-');
    return true;
}

function refreshLotOriginPostings() {
    const $traveler = $('#traveler_id option:selected');
    if ($traveler.length && $traveler.val()) {
        applyLotOriginPosting($traveler, $('.main-traveler-position'), $('.main-traveler-project'), $('.main-traveler-department'));
    }
    const $fixed = $('#lot-primary-posting');
    if ($fixed.length) {
        applyLotOriginPosting($fixed, $('.main-traveler-position'), $('.main-traveler-project'), $('.main-traveler-department'));
    }
    $('.select2-follower').each(function() {
        const $option = $(this).find('option:selected');
        if (!$option.val()) {
            return;
        }
        const $row = $(this).closest('tr');
        applyLotOriginPosting($option, $row.find('.employee-position'), $row.find('.employee-project'), $row.find('.employee-department'));
    });
}
