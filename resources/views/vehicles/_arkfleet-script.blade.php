<script>
    function mapFuelType(bahanBakar) {
        var s = (bahanBakar || '').toLowerCase();
        if (s.indexOf('diesel') !== -1 || s.indexOf('solar') !== -1) return 'diesel';
        if (s.indexOf('electric') !== -1) return 'electric';
        if (s.indexOf('hybrid') !== -1) return 'hybrid';
        if (s.indexOf('gasoline') !== -1 || s.indexOf('bensin') !== -1 || s.indexOf('pertamax') !== -1) return 'gasoline';
        return 'other';
    }

    function applyArkFleetOption($opt) {
        if (!$opt || !$opt.length || !$opt.val()) return;

        $('#arkfleet_equipment_id').val($opt.val());
        $('#kode').val($opt.data('unit_no') || '');
        $('#license_plate').val($opt.data('nomor_polisi') || '');
        $('#description').val($opt.data('description') || '');
        $('#brand').val($opt.data('manufacture') || '');
        $('#model').val($opt.data('model') || '');
        $('#color').val($opt.data('warna') || '');
        $('#vin').val($opt.data('serial_no') || '');
        $('#engine_number').val($opt.data('machine_no') || '');
        $('#capacity').val($opt.data('capacity') || '');
        $('#arkfleet_status').val($opt.data('unitstatus') || '');

        var projectCode = $opt.data('project_code') || '';
        $('#project_code').val(projectCode);
        $('#lokasi').val(projectCode);

        var fuel = mapFuelType($opt.data('bahan_bakar'));
        if (fuel) {
            $('#fuel_type').val(fuel).trigger('change');
        }
    }

    function buildEquipmentOption(eq) {
        var id = eq.id != null ? String(eq.id) : '';
        var unitNo = eq.unit_no || '-';
        var plate = eq.nomor_polisi || '-';
        var desc = eq.description || '-';
        var label = unitNo + ' — ' + plate + ' (' + desc + ')';

        return $('<option></option>')
            .val(id)
            .text(label)
            .attr('data-unit_no', eq.unit_no || '')
            .attr('data-nomor_polisi', eq.nomor_polisi || '')
            .attr('data-description', eq.description || '')
            .attr('data-manufacture', eq.manufacture || '')
            .attr('data-model', eq.model || '')
            .attr('data-bahan_bakar', eq.bahan_bakar || '')
            .attr('data-warna', eq.warna || '')
            .attr('data-capacity', eq.capacity || '')
            .attr('data-unitstatus', eq.unitstatus || '')
            .attr('data-project_code', eq.project_code || '')
            .attr('data-serial_no', eq.serial_no || '')
            .attr('data-machine_no', eq.machine_no || '');
    }

    function setArkFleetBadge(state, text) {
        var $badge = $('#arkfleet-status-badge');
        if (!$badge.length) return;

        $badge.removeClass('badge-secondary badge-success badge-warning badge-danger');
        if (state === 'ok') {
            $badge.addClass('badge-success').html('<i class="fas fa-check"></i> ' + (text || 'ArkFleet ready'));
        } else if (state === 'warn') {
            $badge.addClass('badge-warning').html('<i class="fas fa-exclamation-triangle"></i> ' + (text || 'ArkFleet unavailable'));
        } else {
            $badge.addClass('badge-secondary').html('<i class="fas fa-spinner fa-spin"></i> ' + (text || 'Loading ArkFleet'));
        }
    }

    function showArkFleetWarning(message) {
        $('#arkfleet-warning-text').text(message || 'Failed to load ArkFleet data.');
        $('#arkfleet-warning').removeClass('d-none');
        $('#kode').prop('readonly', false);
        setArkFleetBadge('warn', 'ArkFleet unavailable');
    }

    function finishArkFleetLoading(ok) {
        $('#arkfleet_select').prop('disabled', false);
        $('#arkfleet-select-wrap').removeClass('is-loading');
        $('#arkfleet-loading-hint').addClass('d-none');
        if (ok !== false) {
            $('#arkfleet-ready-hint').removeClass('d-none');
            setArkFleetBadge('ok');
        }
    }

    function loadArkFleetEquipments() {
        var $select = $('#arkfleet_select');
        var url = $select.data('equipments-url');
        var selectedId = String($select.data('selected-id') || '');
        var selectedKode = String($select.data('selected-kode') || '');

        if (!url) {
            finishArkFleetLoading(false);
            showArkFleetWarning('ArkFleet equipments URL is missing.');
            return;
        }

        $('#arkfleet-select-wrap').addClass('is-loading');
        setArkFleetBadge('loading');

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json'
        }).done(function(res) {
            $select.empty().append($('<option></option>').val('').text('— Select from ArkFleet —'));

            var items = (res && res.data) ? res.data : [];
            if (!res || !res.success) {
                finishArkFleetLoading(false);
                $select.trigger('change.select2');
                showArkFleetWarning((res && res.message) ? res.message : 'Failed to load ArkFleet data.');
                return;
            }

            items.forEach(function(eq) {
                $select.append(buildEquipmentOption(eq));
            });

            var $match = null;
            if (selectedId) {
                $match = $select.find('option[value="' + selectedId.replace(/"/g, '\\"') + '"]');
            }
            if ((!$match || !$match.length) && selectedKode) {
                $match = $select.find('option').filter(function() {
                    return String($(this).attr('data-unit_no') || '') === selectedKode;
                }).first();
            }

            if ($match && $match.length) {
                $select.val($match.val());
                // Do not overwrite existing edit fields on initial restore; only sync hidden arkfleet id.
                $('#arkfleet_equipment_id').val($match.val());
            }

            finishArkFleetLoading(true);
            setArkFleetBadge('ok', items.length + ' units ready');
            $select.trigger('change.select2');
        }).fail(function(xhr) {
            $select.empty().append($('<option></option>').val('').text('— Select from ArkFleet —'));
            finishArkFleetLoading(false);
            $select.trigger('change.select2');
            var msg = 'Failed to load ArkFleet data.';
            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            } else if (xhr && xhr.status) {
                msg = 'ArkFleet request failed (HTTP ' + xhr.status + ').';
            }
            showArkFleetWarning(msg);
        });
    }

    function initVehicleSelect2($el) {
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        $el.select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    $(function() {
        $('.select2bs4').each(function() {
            initVehicleSelect2($(this));
        });

        $('#arkfleet_select').on('change', function() {
            applyArkFleetOption($(this).find(':selected'));
        });

        loadArkFleetEquipments();
    });
</script>
