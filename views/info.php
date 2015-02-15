<?php

global $basePath, $theme;

$output = array();

$infoFilePath = "$basePath/public/themes/$theme/information/$page.md";
$infoFileExists = false;

if (empty($page) || !($infoFileExists = file_exists($infoFilePath)) || ($infoFileContents = file_get_contents($infoFilePath)) === FALSE) {
	$output["infoPageError"] = $infoFileExists ? "Could not read the contents of info file \"$page\"." : "Could not find info file \"$page\".";
} else {
	$pd = new Parsedown();
	$output["infoPageContents"] = $pd->text($infoFileContents);
}

$app->render("info.html", $output);
