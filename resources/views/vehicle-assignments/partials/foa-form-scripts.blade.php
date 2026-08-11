<script>
(function() {
    function stripSelect2DomArtifacts($select) {
        if (!$select.length) return;
        var $wrap = $select.closest('.stop-project-wrap, #origin_project_wrap');
        if ($wrap.length) {
            $wrap.find('span.select2-container, span.select2').remove();
        }
        $select.next('span.select2-container, span.select2').remove();
        $select.removeClass('select2-hidden-accessible');
        $select.removeAttr('data-select2-id aria-hidden tabindex');
        $select.find('option').removeAttr('data-select2-id');
    }

    function destroySelect2($select) {
        if (!$select.length) return;
        try {
            if ($select.hasClass('select2-hidden-accessible') && $select.data('select2')) {
                $select.select2('destroy');
            }
        } catch (e) {}
        stripSelect2DomArtifacts($select);
    }

    function initSelect2($select, $parent) {
        destroySelect2($select);
        $select.select2({
            theme: 'bootstrap4',
            placeholder: 'Select project',
            width: '100%',
            dropdownParent: $parent && $parent.length ? $parent : $(document.body)
        });
    }

    function syncOrigin($form) {
        var manual = $form.find('#origin_manual_cb').is(':checked');
        $form.find('#origin_manual_flag').val(manual ? '1' : '0');
        var v = manual
            ? ($form.find('#origin_manual_input').val() || '').trim()
            : ($form.find('#origin_project_select').val() || '').trim();
        $form.find('#origin_dest_hidden').val(v);
    }

    function toggleOrigin($form) {
        var manual = $form.find('#origin_manual_cb').is(':checked');
        var $sel = $form.find('#origin_project_select');
        var $in = $form.find('#origin_manual_input');
        if (manual) {
            destroySelect2($sel);
            $form.find('#origin_project_wrap').addClass('d-none');
            $in.removeClass('d-none').prop('disabled', false);
            $sel.prop('disabled', true);
        } else {
            $in.addClass('d-none').prop('disabled', true);
            $form.find('#origin_project_wrap').removeClass('d-none');
            $sel.prop('disabled', false);
            initSelect2($sel, $form);
        }
        syncOrigin($form);
    }

    function syncRow($row) {
        var manual = $row.find('.stop-manual-cb').is(':checked');
        $row.find('.stop-manual-flag').val(manual ? '1' : '0');
        var v = manual
            ? ($row.find('.stop-manual-input').val() || '').trim()
            : ($row.find('.stop-project-select').val() || '').trim();
        $row.find('.stop-dest-hidden').val(v);
    }

    function toggleRow($row, $form) {
        var manual = $row.find('.stop-manual-cb').is(':checked');
        var $sel = $row.find('.stop-project-select');
        var $in = $row.find('.stop-manual-input');
        var $wrap = $row.find('.stop-project-wrap');
        if (manual) {
            destroySelect2($sel);
            $wrap.addClass('d-none');
            $in.removeClass('d-none').prop('disabled', false);
            $sel.prop('disabled', true);
        } else {
            $in.addClass('d-none').prop('disabled', true);
            $wrap.removeClass('d-none');
            $sel.prop('disabled', false);
            initSelect2($sel, $form);
        }
        syncRow($row);
    }

    function renumberStops($form) {
        $form.find('#destination-stops-container .stop-destination-row').each(function(i) {
            $(this).find('.stop-row-label').text('Destination ' + (i + 1));
        });
        var count = $form.find('#destination-stops-container .stop-destination-row').length;
        $form.find('.remove-stop-destination').prop('disabled', count <= 1);
    }

    function buildFreshStopRow(optionsHtml) {
        // Build via HTML string — avoid $('<input>', { autocomplete: 'off' }) which
        // jQuery UI treats as widget method call before initialization.
        var $row = $(
            '<div class="stop-destination-row border rounded p-2 mb-2" data-stop-row>' +
            '<div class="d-flex justify-content-between align-items-center mb-1">' +
            '<small class="text-muted font-weight-bold stop-row-label">Destination</small>' +
            '<button type="button" class="btn btn-link btn-sm text-danger p-0 remove-stop-destination">&times; Remove</button>' +
            '</div>' +
            '<input type="hidden" name="stop_destinations[]" class="stop-dest-hidden" value="">' +
            '<input type="hidden" name="stop_destinations_manual[]" class="stop-manual-flag" value="0">' +
            '<div class="input-group">' +
            '<div class="input-group-prepend">' +
            '<span class="input-group-text p-1" title="External / manual destination">' +
            '<input type="checkbox" class="stop-manual-cb mx-1" aria-label="Manual destination">' +
            '</span></div>' +
            '<div class="stop-project-wrap flex-grow-1" style="min-width:0">' +
            '<select class="form-control stop-project-select" style="width:100%"></select>' +
            '</div>' +
            '<input type="text" class="form-control stop-manual-input d-none" style="min-width:0" ' +
            'placeholder="Manual destination label" autocomplete="off" disabled>' +
            '</div></div>'
        );
        var $sel = $row.find('.stop-project-select');
        $sel.html(optionsHtml || '<option value="">Select project</option>');
        $sel.val('');
        return $row;
    }

    $(function() {
        var $form = $('#foaForm');
        if (!$form.length) return;

        $form.find('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

        toggleOrigin($form);
        $form.find('#destination-stops-container .stop-destination-row').each(function() {
            // Strip any stale select2 leftovers before init
            destroySelect2($(this).find('.stop-project-select'));
            toggleRow($(this), $form);
        });
        renumberStops($form);

        $form.on('change', '#origin_manual_cb', function() { toggleOrigin($form); });
        $form.on('change', '#origin_project_select', function() { syncOrigin($form); });
        $form.on('input', '#origin_manual_input', function() { syncOrigin($form); });

        $form.on('change', '.stop-manual-cb', function() {
            toggleRow($(this).closest('.stop-destination-row'), $form);
        });
        $form.on('change', '.stop-project-select', function() {
            syncRow($(this).closest('.stop-destination-row'));
        });
        $form.on('input', '.stop-manual-input', function() {
            syncRow($(this).closest('.stop-destination-row'));
        });

        var optionsHtml = $form.find('#destination-stops-container .stop-project-select').first().html();

        $form.on('click', '#add-stop-destination', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $container = $form.find('#destination-stops-container');
            var $row = buildFreshStopRow(optionsHtml);
            $container.append($row);
            renumberStops($form);
            toggleRow($row, $form);
        });

        $form.on('click', '.remove-stop-destination', function(e) {
            e.preventDefault();
            var $rows = $form.find('#destination-stops-container .stop-destination-row');
            if ($rows.length <= 1) return;
            $(this).closest('.stop-destination-row').remove();
            renumberStops($form);
        });

        $form.on('submit', function() {
            syncOrigin($form);
            $form.find('.stop-destination-row').each(function() { syncRow($(this)); });
        });

        $form.on('change', '#vehicle_id', function() {
            var opt = $(this).find('option:selected');
            var projectLabel = opt.data('project-label') || '';
            var lokasi = opt.data('lokasi') || '';
            if ($form.find('#origin_dest_hidden').val()) return;
            if (projectLabel) {
                $form.find('#origin_manual_cb').prop('checked', false);
                toggleOrigin($form);
                $form.find('#origin_project_select').val(projectLabel).trigger('change');
            } else if (lokasi) {
                $form.find('#origin_manual_cb').prop('checked', true);
                toggleOrigin($form);
                $form.find('#origin_manual_input').val(lokasi);
                syncOrigin($form);
            }
        });

        // Passengers (right column)
        var pIndex = $form.find('#passengers-container .passenger-row').length;
        var passengerOptions = $form.find('#passengers-container .passenger-employee').first().html();

        $form.on('change', '.passenger-employee', function() {
            var $row = $(this).closest('.passenger-row');
            var name = $(this).find('option:selected').data('name') || '';
            var $nameInput = $row.find('input[name*="[passenger_name]"]');
            if (name && (!$nameInput.val() || $nameInput.data('autofilled'))) {
                $nameInput.val(name).data('autofilled', true);
            }
        });

        $form.on('click', '#add-passenger', function(e) {
            e.preventDefault();
            var $row = $('<div>', { 'class': 'passenger-row border rounded p-2 mb-2' });
            $row.append(
                '<div class="form-group mb-2"><label class="small mb-1">Employee (optional)</label>' +
                '<select name="passengers[' + pIndex + '][employee_id]" class="form-control select2bs4 passenger-employee" style="width:100%">' +
                (passengerOptions || '<option value="">— Optional —</option>') +
                '</select></div>'
            );
            $row.append(
                '<div class="form-group mb-2"><label class="small mb-1">Name</label>' +
                '<input type="text" name="passengers[' + pIndex + '][passenger_name]" class="form-control form-control-sm" placeholder="Passenger name (free text ok)"></div>'
            );
            $row.append(
                '<div class="text-right"><button type="button" class="btn btn-xs btn-outline-danger remove-passenger">' +
                '<i class="fas fa-times"></i> Remove</button></div>'
            );
            $row.find('select').val('');
            $form.find('#passengers-container').append($row);
            $row.find('.passenger-employee').select2({ theme: 'bootstrap4', width: '100%' });
            pIndex++;
        });

        $form.on('click', '.remove-passenger', function(e) {
            e.preventDefault();
            var $rows = $form.find('#passengers-container .passenger-row');
            if ($rows.length <= 1) {
                var $only = $(this).closest('.passenger-row');
                destroySelect2($only.find('.passenger-employee'));
                $only.find('input').val('');
                $only.find('select').val('').trigger('change');
                $only.find('.passenger-employee').select2({ theme: 'bootstrap4', width: '100%' });
                return;
            }
            $(this).closest('.passenger-row').remove();
        });

        // FOA No preview from letter number (FOA0001)
        var existingFormNo = @json(old('form_number', $assignment->form_number ?? ''));

        function pad4(n) {
            return String(n).padStart(4, '0');
        }

        function getLetterDataFromOption($option) {
            if (!$option.length || !$option.val()) {
                return null;
            }
            var letterNumber = $option.attr('data-letter-number') || $option.data('letterNumber');
            if (!letterNumber) {
                return null;
            }
            return {
                letter_number: String(letterNumber)
            };
        }

        function updateFoaPreview() {
            var $preview = $('#form_number_preview');
            if (!$preview.length) {
                return;
            }
            var $option = $('select[name="letter_number_id"] option:selected');
            var letterData = getLetterDataFromOption($option);

            if (!$option.val()) {
                if (existingFormNo) {
                    $preview.val(existingFormNo).removeClass('alert-warning').addClass('alert-success');
                } else {
                    $preview.val('').removeClass('alert-success').addClass('alert-warning');
                }
                return;
            }
            if (!letterData) {
                return;
            }

            var numeric = letterData.letter_number;
            if (/^FOA/i.test(numeric)) {
                numeric = numeric.replace(/^FOA/i, '');
            }
            numeric = String(numeric).replace(/\D+/g, '');
            var parsed = parseInt(numeric, 10);
            if (isNaN(parsed)) {
                return;
            }

            var foaNo = 'FOA' + pad4(parsed);
            $preview.val(foaNo).removeClass('alert-warning').addClass('alert-success');
        }

        $(document).on('change', 'select[name="letter_number_id"]', updateFoaPreview);
        $(document).on('letter-number-options:updated', updateFoaPreview);
        // Refresh style after letter options load (create + edit)
        updateFoaPreview();
    });
})();
</script>
