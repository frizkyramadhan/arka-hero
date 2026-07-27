<?php

/**
 * Full Zoom room catalog per account (Admin reference).
 */
function zoom_room_catalog()
{
	return array(
		'131' => array(
			array(
				'name' => 'ARKANANTA131 INTERVIEW ROOM',
				'url' => 'https://us02web.zoom.us/j/85943904701?pwd=WE1vZ3FMSjloZDlYNjJtSFgvaWhmZz09',
				'meeting_id' => '859 4390 4701',
				'passcode' => '564448',
			),
			array(
				'name' => 'ARKNANTA 131 Center Room Meeting',
				'url' => 'https://us02web.zoom.us/j/89045533638?pwd=OVdHTEdBeUdrMHJkZkcwTXE5NXNFQT09',
				'meeting_id' => '890 4553 3638',
				'passcode' => '36987',
			),
			array(
				'name' => 'ARKNANTA Asesmen Room 131',
				'url' => 'https://us02web.zoom.us/j/89600459262?pwd=YjRxSStRNjYyQjMvdzJqZEl4cnA0Zz09',
				'meeting_id' => '896 0045 9262',
				'passcode' => '78963',
			),
		),
		'132' => array(
			array(
				'name' => 'ARKA General Meeting Room',
				'url' => 'https://us02web.zoom.us/j/84572368909?pwd=V1hPbnFZdHViRklCdGIyN1B4VCttQT09',
				'meeting_id' => '845 7236 8909',
				'passcode' => '#98741',
			),
			array(
				'name' => 'ARKANANTA INTERVIEW ROOM 132',
				'url' => 'https://us02web.zoom.us/j/891290030?pwd=SFVmL01RY044c0J5eWI1R1N5Skx1QT09',
				'meeting_id' => '891 290 030',
				'passcode' => '132#74123',
			),
			array(
				'name' => 'Assessment Room 132',
				'url' => 'https://us02web.zoom.us/j/81939630645?pwd=L3BTRnFiMnBsSk1LK1RScSt2YjF6Zz09',
				'meeting_id' => '819 3963 0645',
				'passcode' => '#456789',
			),
		),
		'134' => array(
			array(
				'name' => 'ARKANANTA INTERVIEW ROOM 134',
				'url' => 'https://us02web.zoom.us/j/89151875525?pwd=dzN5RmZIUWFCbDhOVlFiT2VUSTFxdz09',
				'meeting_id' => '891 5187 5525',
				'passcode' => '#191758',
			),
			array(
				'name' => 'General Meeting Room 134',
				'url' => 'https://us02web.zoom.us/j/87575421315?pwd=bGEyZFg5b2JQNm1Dcncva3dJMUNGdz09',
				'meeting_id' => '875 7542 1315',
				'passcode' => '#555683',
			),
		),
	);
}

/**
 * Known Zoom room accounts and Meeting ID mapping (derived from catalog).
 */
function zoom_known_accounts()
{
	$accounts = array();
	foreach (zoom_room_catalog() as $code => $rooms) {
		$meeting_ids = array();
		foreach ($rooms as $room) {
			$meeting_ids[] = zoom_normalize_meeting_id($room['meeting_id']);
		}
		$accounts[$code] = array(
			'name' => 'Account ' . $code,
			'meeting_ids' => $meeting_ids,
			'rooms' => $rooms,
		);
	}
	return $accounts;
}

/**
 * Normalize Meeting ID digits only.
 */
function zoom_normalize_meeting_id($meeting_id)
{
	return preg_replace('/\D+/', '', (string) $meeting_id);
}

/**
 * Format Meeting ID as groups of digits (e.g. 890 4553 3638).
 */
function zoom_format_meeting_id($meeting_id)
{
	$digits = zoom_normalize_meeting_id($meeting_id);
	if ($digits === '') {
		return '';
	}
	if (strlen($digits) === 11) {
		return substr($digits, 0, 3) . ' ' . substr($digits, 3, 4) . ' ' . substr($digits, 7, 4);
	}
	if (strlen($digits) === 10) {
		return substr($digits, 0, 3) . ' ' . substr($digits, 3, 3) . ' ' . substr($digits, 6, 4);
	}
	if (strlen($digits) === 9) {
		return substr($digits, 0, 3) . ' ' . substr($digits, 3, 3) . ' ' . substr($digits, 6, 3);
	}
	return $digits;
}

/**
 * Look up room catalog entry by Meeting ID.
 */
function zoom_find_room_by_meeting_id($meeting_id)
{
	$digits = zoom_normalize_meeting_id($meeting_id);
	foreach (zoom_room_catalog() as $code => $rooms) {
		foreach ($rooms as $room) {
			if (zoom_normalize_meeting_id($room['meeting_id']) === $digits) {
				$room['account'] = $code;
				return $room;
			}
		}
	}
	return null;
}

/**
 * Resolve account code from Meeting ID digits or topic text.
 */
function zoom_resolve_account($meeting_id, $topic = '')
{
	$digits = zoom_normalize_meeting_id($meeting_id);
	foreach (zoom_known_accounts() as $code => $meta) {
		if (in_array($digits, $meta['meeting_ids'], true)) {
			return $code;
		}
	}

	if (preg_match('/\b(131|132|134)\b/', $topic, $m)) {
		return $m[1];
	}

	return '';
}

/**
 * Parse activity.detail into one or more Zoom assignments.
 *
 * @return array[] each: account, meeting_id, passcode, url, topic
 */
function zoom_parse_activity_detail($detail)
{
	$detail = trim((string) $detail);
	if ($detail === '') {
		return array();
	}

	$results = array();
	$global_url = '';
	if (preg_match('#URL\s*:\s*(\S+)#i', $detail, $um)) {
		$global_url = rtrim($um[1], '.,;)');
	} elseif (preg_match('#(https?://[^\s]+zoom\.us[^\s]*)#i', $detail, $um)) {
		$global_url = rtrim($um[1], '.,;)');
	}

	if (!preg_match_all('/Meeting\s*ID\s*:\s*([0-9][0-9\s]{8,})/i', $detail, $id_matches, PREG_OFFSET_CAPTURE)) {
		return array();
	}

	$count = count($id_matches[0]);
	for ($i = 0; $i < $count; $i++) {
		$meeting_id = zoom_format_meeting_id($id_matches[1][$i][0]);
		$pos = $id_matches[0][$i][1];
		$next_pos = ($i + 1 < $count) ? $id_matches[0][$i + 1][1] : strlen($detail);

		$before = substr($detail, 0, $pos);
		// Topic = text after previous Meeting ID block (or start), strip leading Topic:
		$prev_end = 0;
		if ($i > 0) {
			$prev_end = $id_matches[0][$i - 1][1];
			// skip previous "Meeting ID: xxx Passcode: yyy"
			$prev_chunk = substr($detail, $prev_end, $pos - $prev_end);
			if (preg_match('/^Meeting\s*ID\s*:\s*[0-9][0-9\s]+(?:\s*Passcode\s*:\s*[^\s&]+)?/i', $prev_chunk, $skip)) {
				$prev_end += strlen($skip[0]);
			}
		}
		$topic = substr($detail, $prev_end, $pos - $prev_end);
		$topic = preg_replace('/^\s*Topic\s*:\s*/i', '', $topic);
		$topic = trim(preg_replace('/\s+/', ' ', $topic));
		$topic = rtrim($topic, ',; ');

		$after = substr($detail, $pos, $next_pos - $pos);
		$passcode = '';
		if (preg_match('/Passcode\s*:\s*([^\s&]+)/i', $after, $pm)) {
			$passcode = trim($pm[1]);
		}

		$url = $global_url;
		if (preg_match('/URL\s*:\s*(\S+)/i', $after, $um2)) {
			$url = rtrim($um2[1], '.,;)');
		}

		$account = zoom_resolve_account($meeting_id, $topic);
		$catalog_room = zoom_find_room_by_meeting_id($meeting_id);
		if ($catalog_room) {
			if ($url === '') {
				$url = $catalog_room['url'];
			}
			if ($passcode === '') {
				$passcode = $catalog_room['passcode'];
			}
			if ($account === '') {
				$account = $catalog_room['account'];
			}
			if ($topic === '') {
				$topic = $catalog_room['name'];
			}
		}

		$results[] = array(
			'account' => $account,
			'meeting_id' => $meeting_id,
			'passcode' => $passcode,
			'url' => $url,
			'topic' => $topic,
		);
	}

	// Deduplicate by meeting_id + account
	$unique = array();
	$seen = array();
	foreach ($results as $row) {
		$key = $row['account'] . '|' . zoom_normalize_meeting_id($row['meeting_id']);
		if (isset($seen[$key])) {
			continue;
		}
		$seen[$key] = true;
		$unique[] = $row;
	}

	return $unique;
}

/**
 * Month name map (ID + EN) to month number.
 */
function zoom_month_map()
{
	return array(
		'januari' => 1, 'january' => 1, 'jan' => 1,
		'februari' => 2, 'february' => 2, 'feb' => 2,
		'maret' => 3, 'march' => 3, 'mar' => 3,
		'april' => 4, 'apr' => 4,
		'mei' => 5, 'may' => 5,
		'juni' => 6, 'june' => 6, 'jun' => 6,
		'juli' => 7, 'july' => 7, 'jul' => 7,
		'agustus' => 8, 'august' => 8, 'aug' => 8,
		'september' => 9, 'sep' => 9, 'sept' => 9,
		'oktober' => 10, 'october' => 10, 'oct' => 10, 'okt' => 10,
		'november' => 11, 'nov' => 11,
		'desember' => 12, 'december' => 12, 'dec' => 12, 'des' => 12,
	);
}

/**
 * Normalize hour/minute to H:i, or null if invalid.
 */
function zoom_normalize_clock($hour, $minute = 0)
{
	$h = (int) $hour;
	$min = (int) $minute;
	if ($h < 0 || $h > 23 || $min < 0 || $min > 59) {
		return null;
	}
	return sprintf('%02d:%02d', $h, $min);
}

/**
 * Display string for a session time.
 */
function zoom_format_meeting_time_display($session)
{
	if (!empty($session['is_all_day'])) {
		return 'All Day';
	}
	$start = !empty($session['time']) ? $session['time'] : null;
	$end = !empty($session['time_end']) ? $session['time_end'] : null;
	if ($start && $end) {
		return $start . '-' . $end;
	}
	if ($start) {
		return $start;
	}
	return '-';
}

/**
 * Parse time string to H:i, ALL_DAY, or null.
 */
function zoom_parse_time_token($text)
{
	$text = trim((string) $text);
	if ($text === '') {
		return null;
	}
	if (preg_match('/\bAll\s*Day\b/i', $text) || preg_match('/\bsehari\s*penuh\b/i', $text)) {
		return 'ALL_DAY';
	}
	if (preg_match('/(?:pukul|jam|pkl\.?|at)?\s*(\d{1,2})[.:](\d{2})/i', $text, $m)) {
		return zoom_normalize_clock($m[1], $m[2]);
	}
	if (preg_match('/(?:pukul|jam|pkl\.?)\s*(\d{1,2})\b/i', $text, $m)) {
		return zoom_normalize_clock($m[1], 0);
	}
	return null;
}

/**
 * Mask character ranges in text (replace with spaces) so later passes skip them.
 * Spans use byte offsets (PCRE PREG_OFFSET_CAPTURE).
 */
function zoom_mask_spans($text, $spans)
{
	$text = (string) $text;
	$len = strlen($text);
	foreach ($spans as $span) {
		$from = max(0, (int) $span[0]);
		$to = min($len, (int) $span[1]);
		if ($to <= $from) {
			continue;
		}
		$text = substr($text, 0, $from) . str_repeat(' ', $to - $from) . substr($text, $to);
	}
	return $text;
}

/**
 * Extract sessions from a text chunk belonging to one meeting date.
 *
 * @return array[] each: date, time, time_end, is_all_day, date_source
 */
function zoom_parse_sessions_from_chunk($chunk, $date, $date_source = 'issue')
{
	$chunk = (string) $chunk;
	$sessions = array();
	$spans = array();

	if (preg_match('/\bAll\s*Day\b/i', $chunk) || preg_match('/\bsehari\s*penuh\b/i', $chunk)) {
		return array(array(
			'date' => $date,
			'time' => null,
			'time_end' => null,
			'is_all_day' => true,
			'date_source' => $date_source,
		));
	}

	// 1) Time ranges: 09:00-14:00, 08.30 -12.00, jam 9 - jam 14, s/d, sampai, hingga, to
	$range_pattern = '/(?:pukul|jam|pkl\.?)?\s*(\d{1,2})(?:[.:](\d{2}))?\s*(?:[-–—]|s\/d|sd\.?|sampai|hingga|to)\s*(?:pukul|jam|pkl\.?)?\s*(\d{1,2})(?:[.:](\d{2}))?/iu';
	if (preg_match_all($range_pattern, $chunk, $rm, PREG_OFFSET_CAPTURE)) {
		foreach ($rm[0] as $idx => $full) {
			$start = zoom_normalize_clock($rm[1][$idx][0], $rm[2][$idx][0] !== '' ? $rm[2][$idx][0] : 0);
			$end = zoom_normalize_clock($rm[3][$idx][0], $rm[4][$idx][0] !== '' ? $rm[4][$idx][0] : 0);
			if ($start === null || $end === null) {
				continue;
			}
			$sessions[] = array(
				'date' => $date,
				'time' => $start,
				'time_end' => $end,
				'is_all_day' => false,
				'date_source' => $date_source,
			);
			$spans[] = array($full[1], $full[1] + strlen($full[0]));
		}
	}

	$masked = zoom_mask_spans($chunk, $spans);

	// 2) Explicit multi-session lines: Sesi 1 : 09.00 / Sesi 2 & 3 : 14.00
	if (preg_match_all('/Sesi\s*\d+(?:\s*&\s*\d+)*\s*:\s*(\d{1,2})[.:](\d{2})/iu', $masked, $sm, PREG_OFFSET_CAPTURE)) {
		foreach ($sm[0] as $idx => $full) {
			$t = zoom_normalize_clock($sm[1][$idx][0], $sm[2][$idx][0]);
			if ($t === null) {
				continue;
			}
			$sessions[] = array(
				'date' => $date,
				'time' => $t,
				'time_end' => null,
				'is_all_day' => false,
				'date_source' => $date_source,
			);
			$spans[] = array($full[1], $full[1] + strlen($full[0]));
		}
	}

	$masked = zoom_mask_spans($chunk, $spans);

	// 3) WIB / WITA dual timezone = one meeting; prefer WITA
	if (preg_match_all('/(\d{1,2})[.:](\d{2})\s*WIB\s*[\/,]\s*(\d{1,2})[.:](\d{2})\s*WITA/iu', $masked, $tz, PREG_OFFSET_CAPTURE)) {
		foreach ($tz[0] as $idx => $full) {
			$t = zoom_normalize_clock($tz[3][$idx][0], $tz[4][$idx][0]); // WITA
			if ($t === null) {
				$t = zoom_normalize_clock($tz[1][$idx][0], $tz[2][$idx][0]);
			}
			if ($t === null) {
				continue;
			}
			$sessions[] = array(
				'date' => $date,
				'time' => $t,
				'time_end' => null,
				'is_all_day' => false,
				'date_source' => $date_source,
			);
			$spans[] = array($full[1], $full[1] + strlen($full[0]));
		}
	}
	if (preg_match_all('/(\d{1,2})[.:](\d{2})\s*WITA\s*[\/,]\s*(\d{1,2})[.:](\d{2})\s*WIB/iu', $masked, $tz2, PREG_OFFSET_CAPTURE)) {
		foreach ($tz2[0] as $idx => $full) {
			$t = zoom_normalize_clock($tz2[1][$idx][0], $tz2[2][$idx][0]); // WITA first
			if ($t === null) {
				continue;
			}
			$sessions[] = array(
				'date' => $date,
				'time' => $t,
				'time_end' => null,
				'is_all_day' => false,
				'date_source' => $date_source,
			);
			$spans[] = array($full[1], $full[1] + strlen($full[0]));
		}
	}

	$masked = zoom_mask_spans($chunk, $spans);

	// 4) Remaining standalone times (pukul/jam HH:MM or bare HH:MM / HH.MM)
	if (preg_match_all('/(?:(?:pukul|jam|pkl\.?|at)\s*)?(\d{1,2})[.:](\d{2})/iu', $masked, $tm, PREG_OFFSET_CAPTURE)) {
		foreach ($tm[0] as $idx => $full) {
			// Skip if this match sits entirely inside already-masked spaces
			$slice = substr($masked, $full[1], strlen($full[0]));
			if (trim($slice) === '') {
				continue;
			}
			$t = zoom_normalize_clock($tm[1][$idx][0], $tm[2][$idx][0]);
			if ($t === null) {
				continue;
			}
			$sessions[] = array(
				'date' => $date,
				'time' => $t,
				'time_end' => null,
				'is_all_day' => false,
				'date_source' => $date_source,
			);
			$spans[] = array($full[1], $full[1] + strlen($full[0]));
		}
	}

	$masked = zoom_mask_spans($chunk, $spans);

	// 5) Hour-only leftovers: jam 10 / pukul 10
	if (preg_match_all('/(?:pukul|jam|pkl\.?)\s*(\d{1,2})\b(?!\s*[.:]\d)/iu', $masked, $hm, PREG_OFFSET_CAPTURE)) {
		foreach ($hm[0] as $idx => $full) {
			$slice = substr($masked, $full[1], strlen($full[0]));
			if (trim($slice) === '') {
				continue;
			}
			$t = zoom_normalize_clock($hm[1][$idx][0], 0);
			if ($t === null) {
				continue;
			}
			$sessions[] = array(
				'date' => $date,
				'time' => $t,
				'time_end' => null,
				'is_all_day' => false,
				'date_source' => $date_source,
			);
		}
	}

	if (empty($sessions)) {
		$sessions[] = array(
			'date' => $date,
			'time' => null,
			'time_end' => null,
			'is_all_day' => false,
			'date_source' => $date_source,
		);
	}

	return $sessions;
}

/**
 * Parse meeting sessions (date + time) from wo.issue free text.
 *
 * @return array[] each: date (Y-m-d), time (H:i|null), time_end (H:i|null), is_all_day (bool), date_source (issue|wo)
 */
function zoom_parse_issue_sessions($issue, $fallback_date = null)
{
	$issue = (string) $issue;
	$months = zoom_month_map();
	$month_alt = implode('|', array_keys($months));
	$sessions = array();

	// Match "DD Month YYYY" / "DD Bulan YYYY"
	$pattern = '/(\d{1,2})\s+(' . $month_alt . ')\s+(\d{4})/iu';
	if (preg_match_all($pattern, $issue, $matches, PREG_OFFSET_CAPTURE)) {
		$count = count($matches[0]);
		for ($i = 0; $i < $count; $i++) {
			$day = (int) $matches[1][$i][0];
			$month_key = strtolower($matches[2][$i][0]);
			$year = (int) $matches[3][$i][0];
			$month = isset($months[$month_key]) ? $months[$month_key] : 0;
			if ($month < 1 || !checkdate($month, $day, $year)) {
				continue;
			}
			$date = sprintf('%04d-%02d-%02d', $year, $month, $day);

			$start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
			$end = ($i + 1 < $count) ? $matches[0][$i + 1][1] : strlen($issue);
			$chunk = substr($issue, $start, $end - $start);
			// Include a bit of the date line itself for "06 Juli 2026 : All day"
			$around = $matches[0][$i][0] . $chunk;

			$parsed = zoom_parse_sessions_from_chunk($around, $date, 'issue');
			foreach ($parsed as $s) {
				$sessions[] = $s;
			}
		}
	}

	// "hari ini" / "today" relative to WO date
	if (preg_match('/\bhari\s*ini\b|\btoday\b/i', $issue) && $fallback_date) {
		$fb = substr($fallback_date, 0, 10);
		$parsed = zoom_parse_sessions_from_chunk($issue, $fb, 'issue');
		foreach ($parsed as $s) {
			$sessions[] = $s;
		}
	}

	// Deduplicate identical date+time+time_end+all_day
	$unique = array();
	$seen = array();
	foreach ($sessions as $s) {
		$key = $s['date'] . '|'
			. (!empty($s['is_all_day']) ? 'all' : ((string) $s['time'] . '-' . (string) (isset($s['time_end']) ? $s['time_end'] : '')));
		if (isset($seen[$key])) {
			continue;
		}
		$seen[$key] = true;
		if (!isset($s['time_end'])) {
			$s['time_end'] = null;
		}
		$unique[] = $s;
	}

	if (empty($unique) && $fallback_date) {
		$fb = substr($fallback_date, 0, 10);
		$parsed = zoom_parse_sessions_from_chunk($issue, $fb, 'wo');
		foreach ($parsed as $s) {
			$unique[] = $s;
		}
		if (empty($unique)) {
			$unique[] = array(
				'date' => $fb,
				'time' => null,
				'time_end' => null,
				'is_all_day' => false,
				'date_source' => 'wo',
			);
		}
	}

	return $unique;
}

/**
 * Map sessions x accounts into virtual recap rows (conservative for multi×multi).
 *
 * @param object $wo WO row with id_wo, no_wo, name, status, issue, date
 * @param array $accounts from zoom_parse_activity_detail
 * @param array $sessions from zoom_parse_issue_sessions
 * @return array[]
 */
function zoom_build_rows_for_wo($wo, $accounts, $sessions)
{
	$rows = array();
	$status_label = ($wo->status === 'Finished') ? 'Done' : 'Waiting';

	if (empty($sessions)) {
		return $rows;
	}

	if (empty($accounts)) {
		foreach ($sessions as $session) {
			$rows[] = array(
				'id_wo' => $wo->id_wo,
				'no_wo' => $wo->no_wo,
				'name' => $wo->name,
				'account' => '',
				'room_name' => '',
				'meeting_id' => '',
				'passcode' => '',
				'url' => '',
				'meeting_date' => $session['date'],
				'meeting_time' => zoom_format_meeting_time_display($session),
				'is_all_day' => !empty($session['is_all_day']),
				'status' => $status_label,
				'wo_status' => $wo->status,
				'date_source' => $session['date_source'],
			);
		}
		return $rows;
	}

	// Conservative: every account × every session
	foreach ($sessions as $session) {
		foreach ($accounts as $acc) {
			$room_name = !empty($acc['topic']) ? $acc['topic'] : '';
			if ($room_name === '' && !empty($acc['meeting_id'])) {
				$found = zoom_find_room_by_meeting_id($acc['meeting_id']);
				if ($found) {
					$room_name = $found['name'];
				}
			}
			$rows[] = array(
				'id_wo' => $wo->id_wo,
				'no_wo' => $wo->no_wo,
				'name' => $wo->name,
				'account' => $acc['account'],
				'room_name' => $room_name,
				'meeting_id' => $acc['meeting_id'],
				'passcode' => $acc['passcode'],
				'url' => $acc['url'],
				'meeting_date' => $session['date'],
				'meeting_time' => zoom_format_meeting_time_display($session),
				'is_all_day' => !empty($session['is_all_day']),
				'status' => $status_label,
				'wo_status' => $wo->status,
				'date_source' => $session['date_source'],
			);
		}
	}

	return $rows;
}

/**
 * Default display names for an account from catalog (all rooms).
 */
function zoom_account_room_names($code)
{
	$catalog = zoom_room_catalog();
	if (!isset($catalog[$code])) {
		return array();
	}
	$names = array();
	foreach ($catalog[$code] as $room) {
		$names[] = $room['name'];
	}
	return $names;
}

/**
 * Build availability summary for accounts 131/132/134 on a given date.
 *
 * @param array $rows virtual recap rows (already filtered or full)
 * @param string $date Y-m-d
 * @return array keyed by account code
 */
function zoom_build_availability($rows, $date)
{
	$accounts = array('131', '132', '134');
	$result = array();
	foreach ($accounts as $code) {
		$catalog_names = zoom_account_room_names($code);
		$result[$code] = array(
			'account' => $code,
			'name' => implode(', ', $catalog_names),
			'room_names' => $catalog_names,
			'status' => 'available', // available | booked | all_day
			'slots' => array(),
			'bookings' => array(),
		);
	}

	foreach ($rows as $row) {
		if ($row['meeting_date'] !== $date) {
			continue;
		}
		$code = $row['account'];
		if ($code === '' || !isset($result[$code])) {
			continue;
		}
		$result[$code]['bookings'][] = $row;
		if (!empty($row['is_all_day']) || $row['meeting_time'] === 'All Day') {
			$result[$code]['status'] = 'all_day';
			$result[$code]['slots'][] = 'All Day';
		} else {
			if ($result[$code]['status'] !== 'all_day') {
				$result[$code]['status'] = 'booked';
			}
			if ($row['meeting_time'] && $row['meeting_time'] !== '-') {
				$result[$code]['slots'][] = $row['meeting_time'];
			}
		}
	}

	foreach ($result as $code => &$info) {
		$info['slots'] = array_values(array_unique($info['slots']));
		sort($info['slots']);

		// When booked, prefer room names from activity
		if ($info['status'] !== 'available' && !empty($info['bookings'])) {
			$from_activity = array();
			foreach ($info['bookings'] as $b) {
				if (!empty($b['room_name'])) {
					$from_activity[] = $b['room_name'];
				} elseif (!empty($b['meeting_id'])) {
					$found = zoom_find_room_by_meeting_id($b['meeting_id']);
					if ($found) {
						$from_activity[] = $found['name'];
					}
				}
			}
			$from_activity = array_values(array_unique($from_activity));
			if (!empty($from_activity)) {
				$info['room_names'] = $from_activity;
				$info['name'] = implode(', ', $from_activity);
			}
		}
	}
	unset($info);

	return $result;
}
