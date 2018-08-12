<?php

require_once 'common.php';

function format_entry($entry, string $dic, $forms = []): string {
	$str = "<div class=\"word $dic\">";
	$str .= '<h3>';
	if (isset($entry['id'])) {
		$str .= '<a href="./log.php?dic=' . hq('arka') . '&id=' . $entry['id'] . '">' . h($entry['word']) . '</a>';
	} else {
		$str .= h($entry['word']);
	}
	foreach ($forms as $form) {
		if ($form == '') continue;
		$str .= ' -' . $form;
	}

	$str .= '</h3>';

	if (isset($entry['pron'])) {
		$str .= '<span class="tag">発音</span>';
		$str .= h($entry['pron']);
		$str .= '<hr>';
	}

	foreach ($entry['defs'] as $def) {
		foreach ($def['tags'] as $tag) {
			$str .= '<span class="tag">'. h($tag) . '</span>';
		}
		foreach ($def['trans'] as $i => $trans) {
			if ($i != 0) $str .= '<span class="punc">、</span>';
			$str .= h($trans);
		}
		if (isset($def['desc'])) {
			$str .= ' (';
			$str .= h($def['desc']);
			$str .= ')';
		}
		$str .= '<br>';
	}

	if (count($entry['rel']) !== 0) {
		foreach ($entry['rel'] as $rel) {
			foreach ($rel['tags'] as $tag) {
				$str .= '<span class="tag">'. h($tag) . '</span>';
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
		$str .= '<span class="tag">'. h($tag) . '</span>';
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
	$str .= "</div>\n";

	return $str;
}
