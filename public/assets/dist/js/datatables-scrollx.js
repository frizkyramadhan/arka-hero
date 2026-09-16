/**
 * Force DataTables lists to keep every column visible and scroll horizontally.
 * DataTables Responsive hides columns on small screens; PWA/mobile needs pan instead.
 * Survives pages that re-include jquery.dataTables after this file.
 */
(function (window, $) {
    if (!$ || !$.fn) {
        return;
    }

    function applyHorizontalScroll(opts) {
        if (!opts || typeof opts !== 'object' || Array.isArray(opts)) {
            return opts;
        }
        opts.responsive = false;
        opts.scrollX = true;
        return opts;
    }

    function wrapDataTable(fn) {
        if (typeof fn !== 'function' || fn._arkaScrollX) {
            return fn;
        }

        function wrapped(opts) {
            applyHorizontalScroll(opts);
            return fn.apply(this, arguments);
        }

        wrapped._arkaScrollX = true;
        for (var key in fn) {
            if (Object.prototype.hasOwnProperty.call(fn, key)) {
                wrapped[key] = fn[key];
            }
        }
        if (fn.defaults) {
            fn.defaults.responsive = false;
            fn.defaults.scrollX = true;
        }
        return wrapped;
    }

    var current = $.fn.dataTable;
    Object.defineProperty($.fn, 'dataTable', {
        configurable: true,
        enumerable: true,
        get: function () {
            return current;
        },
        set: function (value) {
            current = wrapDataTable(value);
        }
    });
    if (current) {
        current = wrapDataTable(current);
    }

    function adjustVisibleTables() {
        if (!$.fn.dataTable || typeof $.fn.dataTable.tables !== 'function') {
            return;
        }
        try {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        } catch (e) {
            /* table not ready */
        }
    }

    $(function () {
        $(document).on('init.dt', function () {
            window.setTimeout(adjustVisibleTables, 0);
        });
        $(window).on('resize.dt-scrollx', function () {
            adjustVisibleTables();
        });
        $(document).on(
            'shown.bs.tab shown.bs.modal shown.bs.collapse collapsed.lte.pushmenu shown.lte.pushmenu',
            function () {
                window.setTimeout(adjustVisibleTables, 350);
            }
        );
    });
})(window, window.jQuery);
