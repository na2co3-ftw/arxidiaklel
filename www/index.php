<?php
ini_set('memory_limit', '256M');
require_once 'common.php';
require_once 'form.php';
require_once 'dictionary.php';
require_once 'template.php';

$start = microtime(true);

$form = new Form();
$form->radio('key', [
	'word' => '見出し語',
	'def' => '訳語',
	'deftag' => '訳語タグ',
	'exp' => '例文',
	'all' => '全文'
], 'word');
$form->br();
$form->radio('type', ['just' => '完全一致', 'include' => '部分一致'], 'just');
$form->br();
$form->radio('dic', ['' => '横断', 'arka' => 'アルカ', 'nagili' => '凪霧'], '');
$form->br();
$form->textEdit('query', '');
$form->submit('検索');

$param = $form->getParam($_GET);

$breadcrumb = '';
if ($param['dic'] != '') {
	$breadcrumb .= $form->getComponent('dic')->getLabel($param['dic']);
}

const ARKA_ADJ_SUFFIX = [
	'An', 'Ol', 'el', 'ati', 'en'
];

const ARKA_VERB_SUFFIX = [
	'at', 'or', 'ik', 'es', 'and', 'ast', 'ont', 'An', 'Ol', 'el', 'Anel', 'ator', 'atik', 'ates', 'atand'
];

$html = '';
//$html .= $_SERVER['PATH_INFO']."<br>";
//$html .= $_SERVER['REQUEST_URI']."<br>";
//$html .= $_SERVER['SCRIPT_NAME']."<br>";

if ($param['query'] != '') {
	$hitcount = 0;
	if ($param['dic'] == 'arka' || $param['dic'] == '') {
		$dictionary = json_decode(file_get_contents('./data/dictionary/arka.json'), true);
		$html .= search($dictionary, $param['query'], $param['key'], $param['type'], 'arka');
	}
	if ($param['dic'] == 'nagili' || $param['dic'] == '') {
		$dictionary = json_decode(file_get_contents('./data/dictionary/nagili.json'), true);
		$html .= search($dictionary, $param['query'], $param['key'], $param['type'], 'nagili');
	}
	if ($hitcount == 0) {
		$html .= "項目が見つかりませんでした。\n";
	}
	$breadcrumb .= '「' . h($param['query']) . '」で' . $form->getComponent('key')->getLabel($param['key']) . '検索';
	$breadcrumb .= ' (' . $form->getComponent('type')->getLabel($param['type']) . '): ' . $hitcount . '件';
} else {
	if ($param['dic'] == '') {
		$html .= <<<HTML
<p>架空の世界カルディアで使われる人工言語の統合辞書です。</p>
<ul>
<li><a href="./?dic=arka">アルカ</a></li>
<li><a href="./?dic=nagili">凪霧</a></li>
</ul>
HTML;
	} else {
		$html .= <<<HTML
<p>人工言語{$form->getComponent('dic')->getLabel($param['dic'])}の辞書です。</p>
HTML;
	}
}

$end = microtime(true);

$html .= "<br>";
$html .= ($end - $start) * 1000 . "ms<br>";
$html .= memory_get_usage() / 1024 / 1024 . "MB<br>";

function search($dictionary, $query, $key, $type, $dic) {
	global $hitcount;
	$str = '';
	foreach ($dictionary as $entry) {
		$entry = $entry['ja'];
		if (!isset($entry['defs'])) {
			continue;
		}
		switch ($key) {
			case 'word':
				if ($dic == 'arka' && $type == 'just') {
					foreach ($entry['defs'] as $def) {
						if (in_array('動詞', $def['tags'])) {
							$forms = test_inflection($entry['word'], $query, ARKA_VERB_SUFFIX);
							if (count($forms) != 0) {
								$str .= format_entry($entry, $dic, $forms);
								$hitcount++;
								break 2;
							}
						}
						if (in_array('形容詞', $def['tags'])) {
							$forms = test_inflection($entry['word'], $query, ARKA_ADJ_SUFFIX);
							if (count($forms) != 0) {
								$str .= format_entry($entry, $dic, $forms);
								$hitcount++;
								break 2;
							}
						}
					}
				}
				if (test_value($entry['word'], $query, $type)) {
					$str .= format_entry($entry, $dic);
					$hitcount++;
					break;
				}
				if (isset($entry['pron']) && test_value($entry['pron'], $query, $type)) {
					$str .= format_entry($entry, $dic);
					$hitcount++;
				}
				break;
			case 'def':
				foreach ($entry['defs'] as $def) {
					if (test_array($def['trans'], $query, $type)) {
						$str .= format_entry($entry, $dic);
						$hitcount++;
						break;
					}
				}
				break;
			case 'deftag':
				foreach ($entry['defs'] as $def) {
					if (test_array($def['tags'], $query, $type)) {
						$str .= format_entry($entry, $dic);
						$hitcount++;
						break;
					}
				}
				break;
			case 'exp':
				foreach ($entry['example'] as $example) {
					if (test_array($example['texts'], $query, $type)) {
						$str .= format_entry($entry, $dic);
						$hitcount++;
						break;
					}
				}
				break;
			case 'all':
				if (test_value($entry['word'], $query, 'include')) {
					$str .= format_entry($entry, $dic);
					$hitcount++;
					break;
				}
				if (isset($entry['pron']) && test_value($entry['pron'], $query, 'include')) {
					$str .= format_entry($entry, $dic);
					$hitcount++;
					break;
				}
				foreach ($entry['defs'] as $def) {
					if (test_array($def['trans'], $query, 'include')) {
						$str .= format_entry($entry, $dic);
						$hitcount++;
						break 2;
					}
					if (test_array($def['tags'], $query, 'include')) {
						$str .= format_entry($entry, $dic);
						$hitcount++;
						break 2;
					}
					if (isset($def['desc']) && test_value($def['desc'], $query, 'include')) {
						$str .= format_entry($entry, $dic);
						$hitcount++;
						break 2;
					}
				}
				foreach ($entry['details'] as $detail) {
					if (test_value($detail['text'], $query, 'include')) {
						$str .= format_entry($entry, $dic);
						$hitcount++;
						break 2;
					}
				}
				foreach ($entry['example'] as $example) {
					if (test_array($example['texts'], $query, 'include')) {
						$str .= format_entry($entry, $dic);
						$hitcount++;
						break 2;
					}
				}
		}
	}
	return $str;
}

function test_value($value, $query, $type) {
	if ($type == 'just') {
		return $value == $query;
	} else if ($type == 'include') {
		return strpos($value, $query) !== false;
	}
	return false;
}

function test_inflection($value , $query, $table) {
	$matches = [];
	if (substr($query, 0, strlen($value)) != $value) {
		return $matches;
	}
	if ($value == $query) {
		$matches[] = '';
	}
	foreach ($table as $suffix) {
		$cand = $value;
		if (preg_match('/[aioeu]/', substr($suffix, 0, 1))) {
			if (preg_match('/[aioeu]/', substr($cand, -1))) {
				$cand .= substr($suffix, 1);
			} else {
				$cand .= $suffix;
			}
		} else if (preg_match('/[A-Z]/', substr($suffix, 0, 1))) {
			if (substr($cand, -1) == strtolower(substr($suffix, 0, 1))) {
				$cand .= substr($suffix, 1);
			} else {
				$cand .= strtolower($suffix);
			}
		} else {
			$cand .= $suffix;
		}
		if ($cand == $query) {
			$matches[] = strtolower($suffix);
		}
	}
	return $matches;
}

function test_array($array, $query, $type) {
	if ($type == 'just') {
		return in_array($query, $array);
	} else if ($type == 'include') {
		foreach ($array as $value) {
			if (strpos($value, $query) !== false) {
				return true;
			}
		}
		return false;
	}
	return false;
}

render($param['dic'], $breadcrumb, $form->render($param), $html);
