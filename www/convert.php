<?php
ini_set('memory_limit', '256M');

$dictionary = [];
$fp = fopen("data/log/arka.ldjson", "r");
$threadId = 0;
while (($line = fgets($fp)) !== false) {
	$entry = json_decode(substr($line, 0, -1), true);

	$content = [];
	$fmt = '';
	for ($i = count($entry) - 1; $i >= 0; $i--) {
		$record = $entry[$i];
		if (isset($record['content'])) {
			$content = $content + $record['content'];
		}
		if (isset($record['fmt'])) {
			$fmt = $record['fmt'];
			break;
		}
	}
	$tags = [];
	for ($i = count($entry) - 1; $i >= 0; $i--) {
		if (isset($entry[$i]['tags'])) {
			$tags = $entry[$i]['tags'];
			break;
		}
	}
	if (in_array("removed", $tags) || in_array("request", $tags)) {
		$threadId++;
		continue;
	}

	if ($fmt != '') {
		$content["id"] = $threadId;
		$dictionary[] = ["ja" => $content];
	}

	$threadId++;
}

file_put_contents("data/dictionary/arka.json", json_encode($dictionary, JSON_UNESCAPED_UNICODE));

echo "converted data/log/arka.ldjson > data/dictionary/arka.json ";
