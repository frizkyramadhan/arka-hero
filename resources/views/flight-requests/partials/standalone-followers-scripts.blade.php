<script>
    (function() {
        let standaloneFollowerIndex = $('#standaloneFollowersTableBody tr.standalone-follower-row').length;

        function toggleStandaloneFollowersCard() {
            const requestType = $('#request_type').val();
            if (requestType === 'standalone') {
                $('#standalone_followers_card').show();
            } else {
                $('#standalone_followers_card').hide();
            }
        }

        function initStandaloneFollowerSelect($select) {
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2({
                theme: 'bootstrap4',
                placeholder: 'Select Employee',
                width: '100%'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field')?.focus();
            });
        }

        function updateFollowerRowFromSelect($row) {
            const $option = $row.find('.select2-standalone-follower option:selected');
            $row.find('.follower-employee-name').text($option.data('fullname') || '');
            $row.find('.follower-employee-nik').text($option.data('nik') || '');
            $row.find('.follower-employee-phone').text($option.data('phone') || '');
        }

        function setFollowerRowManualMode($row, isManual) {
            $row.toggleClass('follower-row--manual', isManual);
            $row.toggleClass('follower-row--employee', !isManual);

            const $manualNik = $row.find('.follower-manual-nik');

            if (isManual) {
                $row.find('.follower-employee-select-wrap').hide();
                $row.find('.follower-employee-name, .follower-employee-nik, .follower-employee-phone').hide();
                $row.find('.follower-manual-name, .follower-manual-nik, .follower-manual-phone').show();
                $manualNik.attr('placeholder', 'KTP');
            } else {
                $row.find('.follower-employee-select-wrap').show();
                $row.find('.follower-manual-name, .follower-manual-nik, .follower-manual-phone').hide();
                $row.find('.follower-employee-name, .follower-employee-nik, .follower-employee-phone').show();
                $manualNik.attr('placeholder', 'NIK');
                updateFollowerRowFromSelect($row);
            }
        }

        window.toggleStandaloneFollowersCard = toggleStandaloneFollowersCard;

        $('#addStandaloneFollowerRow').on('click', function() {
            const template = $('#standaloneFollowerRowTemplate').html();
            if (!template) {
                return;
            }
            const html = template.replace(/__INDEX__/g, standaloneFollowerIndex);
            const $row = $(html);
            $('#standaloneFollowersTableBody').append($row);
            initStandaloneFollowerSelect($row.find('.select2-standalone-follower'));
            setFollowerRowManualMode($row, false);
            standaloneFollowerIndex++;
        });

        $(document).on('change', '.follower-manual-toggle', function() {
            const $row = $(this).closest('tr');
            setFollowerRowManualMode($row, $(this).is(':checked'));
        });

        $(document).on('change', '.select2-standalone-follower', function() {
            const $row = $(this).closest('tr');
            if (!$row.hasClass('follower-row--manual')) {
                updateFollowerRowFromSelect($row);
            }
        });

        $(document).on('click', '.remove-standalone-follower', function() {
            $(this).closest('tr').fadeOut(200, function() {
                $(this).remove();
            });
        });

        $('#standaloneFollowersTableBody tr.standalone-follower-row').each(function() {
            const $row = $(this);
            initStandaloneFollowerSelect($row.find('.select2-standalone-follower'));
            setFollowerRowManualMode($row, $row.find('.follower-manual-toggle').is(':checked'));
        });

        $(document).on('change', '#request_type', function() {
            toggleStandaloneFollowersCard();
            if ($(this).val() !== 'standalone') {
                $('#standaloneFollowersTableBody').empty();
            }
        });

        const origFillEmployee = window.fillEmployeeInfo;
        if (typeof origFillEmployee === 'function') {
            window.fillEmployeeInfo = function(data) {
                origFillEmployee(data);
                toggleStandaloneFollowersCard();
            };
        }

        const origClearEmployee = window.clearEmployeeInfo;
        if (typeof origClearEmployee === 'function') {
            window.clearEmployeeInfo = function() {
                origClearEmployee();
                toggleStandaloneFollowersCard();
            };
        }

        toggleStandaloneFollowersCard();
    })();
</script>
<style>
    #standalone_followers_card .follower-row--employee .follower-manual-name,
    #standalone_followers_card .follower-row--employee .follower-manual-nik,
    #standalone_followers_card .follower-row--employee .follower-manual-phone {
        display: none !important;
    }

    #standalone_followers_card .follower-row--manual .follower-employee-select-wrap,
    #standalone_followers_card .follower-row--manual .follower-employee-name,
    #standalone_followers_card .follower-row--manual .follower-employee-nik,
    #standalone_followers_card .follower-row--manual .follower-employee-phone {
        display: none !important;
    }

    #standalone_followers_card .follower-employee-name,
    #standalone_followers_card .follower-employee-nik,
    #standalone_followers_card .follower-employee-phone {
        display: block;
        min-height: 31px;
        padding: 0.25rem 0;
    }

    #standalone_followers_card .follower-source-row {
        width: 100%;
        min-height: 38px;
    }

    #standalone_followers_card .follower-manual-toggle-wrap {
        padding-top: 0.35rem;
    }

    #standalone_followers_card .follower-manual-toggle-wrap .custom-control {
        min-height: 1.25rem;
        padding-left: 1.5rem;
    }

    #standalone_followers_card .follower-employee-select-wrap .select2-container {
        width: 100% !important;
    }

    #standalone_followers_card .follower-row--manual .follower-source-row {
        justify-content: flex-start;
    }
</style>
