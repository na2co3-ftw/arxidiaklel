<?php

date_default_timezone_set('Asia/Tokyo');

function h($str) {
	return htmlspecialchars($str, ENT_NOQUOTES);
}

function hq($str) {
	return htmlspecialchars($str, ENT_COMPAT);
}

function hbr($str) {
	return str_replace("\n", '<br>', h($str));
}
