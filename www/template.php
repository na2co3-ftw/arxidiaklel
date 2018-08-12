<?php

function render($dic, $breadcrumb, $form, $body) {
	?>
	<!doctype html>
	<html lang="ja">
	<head>
		<meta charset="UTF-8">
		<title>アルシディア辞典 (仮)</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body class="<?php echo $dic; ?>">
	<h1>アルシディア辞典 (仮)</h1>
	<h2><?php echo $breadcrumb; ?></h2>
	<?php echo $form; ?>
	<hr>
	<?php echo $body; ?>
	</body>
	</html>
	<?php
}
