<?php

require_once 'common.php';

function getLogEntry(string $dic, string $id) {
	if ($dic == 'arka') {
		$fp = fopen("data/log/$dic.ldjson", "r");
		$threadId = 0;
		while (($line = fgets($fp)) !== false) {
			if ($threadId != $id) {
				$threadId++;
				continue;
			}
			return json_decode(substr($line, 0, -1), true);
		}
	}
	return null;
}

function format_record_content($fmt, $entry, $old, $dic) {
	$str = "<div class=\"word $dic\">";
	if (isset($entry['word'])) {
		$str .= '<h3>' . h($entry['word']) . '</h3>';
	} else if (isset($old['word'])) {
		$str .= '<h3 class="unchanged">' . h($old['word']) . '</h3>';
	}

	switch ($fmt) {
		case 'ism':
			if (isset($entry['axn'])) {
				$str .= '<span class="tag">正幻</span><br>';
				$str .= hbr($entry['axn']);
			} else if (isset($old['axn'])) {
				$str .= '<span class="unchanged"><span class="tag">正幻</span><br>';
				$str .= hbr($old['axn']) . '</span>';
			}
			if (isset($entry['ism'])) {
				$str .= '<span class="tag">俗幻</span><br>';
				$str .= hbr($entry['ism']);
			} else if (isset($entry['ism'])) {
				$str .= '<span class="unchanged"><span class="tag">俗幻</span><br>';
				$str .= hbr($entry['ism']) . '</span>';
			}
			break;
		case 'arx':
			$str .= format_entry_arx($entry, $old);
			break;
		case 'text':
			if (isset($entry['text'])) {
				$str .= hbr($entry['text']);
			} else if (isset($entry['text'])) {
				$str .= '<span class="unchanged">' . hbr($entry['text']) . '</span>';
			}
			break;
	}

	$str .= "</div>";
	return $str;
}

function format_entry_arx($entry, $old) {
	$str = '';

	if (isset($entry['pron'])) {
		$str .= '<span class="tag">発音</span>';
		$str .= h($entry['pron']);
		$str .= '<hr>';
	}

	foreach ($entry['defs'] as $def) {
		foreach ($def['tags'] as $tag) {
			$str .= '<span class="tag">' . h($tag) . '</span>';
		}
		foreach ($def['trans'] as $i => $trans) {
			if ($i != 0) $str .= '<span class="punc">、</span>';
			$str .= h($trans);
		}
		if (isset($def['desc'])) {
			$str .= ' (' . h($def['desc']) . ')';
		}
		$str .= '<br>';
	}

	if (count($entry['rel']) !== 0) {
		foreach ($entry['rel'] as $rel) {
			foreach ($rel['tags'] as $tag) {
				$str .= '<span class="tag">' . h($tag) . '</span>';
			}
			foreach ($rel['words'] as $i => $word) {
				if ($i != 0) $str .= '<span class="punc">, </span>';
				$str .= h($word);
			}
			$str .= '<br>';
		}
	}

	$str .= '<hr>';

	if (isset($entry['ety_atolas'])) {
		$str .= '<span class="tag">語源(アトラス)</span>';
		$str .= h($entry['ety_atolas']);
		$str .= '<br>';
	}

	if (isset($entry['ety'])) {
		$str .= '<span class="tag">語源</span>';
		$str .= h($entry['ety']);
		$str .= '<br>';
	}

	if (isset($entry['otherlang'])) {
		$str .= '<span class="tag">他言語</span>';
		$str .= h($entry['otherlang']);
		$str .= '<br>';
	}

	if (isset($entry['level'])) {
		$str .= '<span class="tag">レベル</span>';
		$str .= h($entry['level']);
		$str .= '<br>';
	}

	foreach ($entry['tags'] as $tag) {
		$str .= '<span class="tag">' . h($tag) . '</span>';
	}
	if (count($entry['tags'])) {
		$str .= '<br>';
	}

	foreach ($entry['details'] as $detail) {
		$str .= '<span class="tag">' . h($detail['tag']) . '</span><br>';
		$str .= str_replace("\n", '<br>', h($detail['text']));
		$str .= '<br>';
	}

	foreach ($entry['example'] as $example) {
		$str .= '<span class="tag">' . h($example['tag']) . '</span><br>';
		foreach ($example['texts'] as $text) {
			$str .= h($text);
			$str .= '<br>';
		}
	}

	if (isset($entry['image'])) {
		$str .= '<span class="tag">画像</span>';
		$str .= h($entry['image']);
		$str .= '<br>';
	}

	return $str;
}
