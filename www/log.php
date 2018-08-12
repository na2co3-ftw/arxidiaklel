<?php
ini_set('memory_limit', '256M');
require_once 'common.php';
require_once 'dictionary.php';
require_once 'logfile.php';
require_once 'template.php';

$start = microtime(true);

$param_dic = isset($_GET['dic']) ? $_GET['dic'] : '';
$param_id = isset($_GET['id']) ? $_GET['id'] : '';

$entry = getLogEntry($param_dic, $param_id);
$records_html = [];
$word = '';
if ($entry !== null) {
	$content = [];
	$fmt = '';
	foreach ($entry as $recordId => $record) {
		$str = '<div class="record">';
		$str .= $recordId + 1 . '. ';
		if (isset($record['date'])) {
			$str .= '<span class="date">' . h(date("Y/m/d H:i:s", $record['date'])) . '</span> ';
		}
		$str .= '<span class="editor">' . h($record['editor']) . '</span><br>';
		if (isset($record['fmt'])) {
			$fmt = $record['fmt'];
			$content = [];
		}
		if (isset($record['content'])) {
			$str .= "投稿: ";
			$str .= format_record_content($fmt, $record['content'], $content, $param_dic);
			$content = $record['content'] + $content;

			if (isset($record['content']['word'])) {
				$word = $record['content']['word'];
			}
		}
		if (isset($record['coment'])) {
			$str .= "コメント: ";
			$str .= '<div class="coment">' . hbr($record['coment']) . '</div>';
		}
		if (isset($record['tags'])) {
			foreach ($record['tags'] as $tag) {
				$str .= '<span class="tag">' . h($tag) . '</span>';
			}
			$str .= '<br>';
		}
		$str .= '</div>';

		$records_html[] = $str;
	}
} else {
	$content = null;
}

$breadcrumb = '';
if ($param_dic != '') {
	$breadcrumb .= $param_dic == 'arka' ? 'アルカ' : '凪霧';
}
if ($param_id != '') {
	$breadcrumb .= $word . '(' . $param_id . ')';
}

$html = '';
//$html .= $_SERVER['PATH_INFO']."<br>";
//$html .= $_SERVER['REQUEST_URI']."<br>";
//$html .= $_SERVER['SCRIPT_NAME']."<br>";

if ($content !== null) {
	$html .= format_entry($content, $param_dic);
}

$html .= '<h3>履歴</h3>';
foreach (array_reverse($records_html) as $record_html) {
	$html .= $record_html;
	$html .= "<hr>\n";
}

$end = microtime(true);
$html .= "<br>";
$html .= ($end - $start) * 1000 . "ms<br>";
$html .= memory_get_usage() / 1024 / 1024 . "MB<br>";

render($param_dic, $breadcrumb, '', $html);
