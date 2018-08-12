<!doctype html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<title>アルシディア辞典 - 凪霧複合語作成</title>
	<link rel="stylesheet" href="../style.css">
</head>
<body>
	<h1>凪霧複合語作成</h1>
<?php
$words = $_GET['words'] ?? '';

?>
	<form action="./compound.php" method="get">
		京極:<br>
		<textarea name="words" rows="20" cols="20"><?php echo h($words); ?></textarea><br>
		<input type="submit" value="複合語作成">
	</form>
<?php

$dictionary = json_decode(file_get_contents('../data/dictionary/nagili.json'), true);

foreach (preg_split('/\r?\n/u', $words) as $word) {
	if (empty($word)) {
		continue;
	}
	echo h($word) . ' : ';

	preg_match_all('/[^가-힣][루투]?|[가-힣]+/u', $word, $matches);
	$roots = $matches[0];

	$hacms = [];
	$bases = [];
	foreach ($roots as $root) {
		$entries = [];
		foreach ($dictionary as $entry) {
			$entry = $entry['ja'];
			if (!isset($entry['defs'])) {
				continue;
			}
			if ($entry['word'] == $root) {
				$entries[] = $entry;
			}
		}

		if (count($entries) != 1) {
			if (count($entries) == 0) {
				echo h($root) . 'が見つかりません。 ';
			} else {
				echo h($root) . 'が複数項目見つかりました。 ';
			}
			continue;
		}
		$entry = $entries[0];

		$hacm = $entry['pron'];
		$base = presumeBase($entry);
		if ($base === null) {
			echo h($root) . 'の基層形が不明です。 ';
			continue;
		}

		$hacms[] = $hacm;
		$bases[] = $base;
	}

	echo h(implode('/', $bases)) . ' > ';

	for ($i = 1; $i < count($hacms); $i++) {
		$prevIsV = preg_match('/[aioeu]$|yu$/u', $hacms[$i - 1]);
		$nextIsV = preg_match('/^[aioeu]|^yu/u', $hacms[$i]);
		if ($prevIsV) {
			if ($nextIsV) { // V/V
				$hacms[$i - 1] = preg_replace('/[aioeu]$|yu$/u', '', $hacms[$i - 1]);
				if (substr($hacms[$i - 1], -1) == 'y' && substr($hacms[$i], 0, 2) == 'yu') {
					$hacms[$i] = 'o' . substr($hacms[$i], 2);
				}
			} else { // V/C
			}
		} else {
			if ($nextIsV) { // C/V
				preg_match('/((?:[pbvfmtdzsjxnlkghw]|t[sx]|sw)(?:y(?!u))?|(?:y(?!u))?)(?:[aioeu]|yu)$/u', $bases[$i - 1], $matches);
				$hacms[$i - 1] = substr($hacms[$i - 1], 0, -1) . $matches[1];
			} else { // C/C
				if (preg_match('/^[pftsxkh]/u', $hacms[$i])) {
					$hacms[$i - 1] = substr($hacms[$i - 1], 0, -1) . 'k';
				} else {
					$hacms[$i - 1] = substr($hacms[$i - 1], 0, -1) . 'n';
				}
			}
		}
	}

	$hacm = implode('', $hacms);

	$hacm = preg_replace([
			'/^v|(?<=n)v/u',
			'/(?<!n)b/u',
			'/([xj])y([aeo])/u',
			'/([xj])y?u/u',
			'/([sz])yu/u'
		], [
			'b',
			'v',
			'$1$2',
			'$1i',
			'$1i'
		], $hacm);

	echo '<strong>' . h($hacm) . '</strong>';
	echo '<br>';
}

function presumeBase($entry) {
	global $dictionary;
	foreach ($entry['details'] as $detail) {
		if ($detail['tag'] == '基層') {
			if (substr($detail['text'], 0, 1) !== '*') {
				return $detail['text'];
			}
			break;
		}
	}
	if (in_array("機能語", $entry['tags'])) {
		return $entry['pron'];
	}
	if (preg_match('/-(.*)$/u', $entry['ety'], $matches)) {
		return $matches[1];
	}
	if (preg_match('/\{(.*)\}/', $entry['ety'], $matches)) {
		$entries = [];
		foreach ($dictionary as $entry) {
			$entry = $entry['ja'];
			if (!isset($entry['defs'])) {
				continue;
			}
			if ($entry['word'] == $matches[1]) {
				$entries[] = $entry;
			}
		}
		if (count($entries) == 1) {
			return presumeBase($entries[0]) . 'lu';
		}
	}
	return null;
}

function h($str) {
	return htmlspecialchars($str, ENT_NOQUOTES);
}

function hq($str) {
	return htmlspecialchars($str, ENT_COMPAT);
}

?>
</body>
</html>
