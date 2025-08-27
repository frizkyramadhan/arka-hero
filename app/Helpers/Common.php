<?php
function showDateTime($carbon, $format = "d M Y @ H:i")
{
    if (!$carbon) {
        return '-';
    }
    return $carbon->translatedFormat($format);
}
