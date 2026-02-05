/**
 * Amount format helper - Indonesia (ribuan . desimal ,)
 * Usage: AmountFormat.parse(str), AmountFormat.format(num), AmountFormat.formatTyping(str)
 */
(function (global) {
    'use strict';

    var SEP = '.';

    function thousands(x) {
        return String(x).replace(/\B(?=(\d{3})+(?!\d))/g, SEP);
    }

    function parse(str) {
        if (!String(str || '').trim()) return 0;
        var n = parseFloat(String(str).replace(/\s/g, '').replace(/\./g, '').replace(',', '.'));
        return isNaN(n) ? 0 : n;
    }

    function format(num) {
        var n = Number(num);
        if (isNaN(n)) return '';
        var neg = n < 0;
        var p = Math.abs(n).toFixed(2).split('.');
        return (neg ? '-' : '') + thousands(p[0]) + ',' + p[1];
    }

    function formatTyping(str) {
        str = String(str || '').trim().replace(/\s/g, '');
        if (!str) return '';
        var neg = str.charAt(0) === '-';
        if (neg) str = str.slice(1);
        var commaAt = str.lastIndexOf(',');
        var intPart = (commaAt < 0 ? str : str.slice(0, commaAt)).replace(/\./g, '').replace(/\D/g, '') || '0';
        var decPart = commaAt < 0 ? '' : str.slice(commaAt + 1).replace(/\D/g, '').slice(0, 2);
        var out = (neg ? '-' : '') + thousands(intPart);
        if (commaAt >= 0) out += ',' + decPart;
        return out;
    }

    global.AmountFormat = {
        parse: parse,
        format: format,
        formatTyping: formatTyping,
        thousands: thousands
    };
})(typeof window !== 'undefined' ? window : this);
