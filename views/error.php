<?php

$code		= $e->getCode();
$message	= $e->getMessage();
$file		= $e->getFile();
$line		= $e->getLine();
$trace		= $e->getTraceAsString();

$codeHash = md5($trace);

$errorDetails = array();

if ($code)
	$errorDetails[] = array("label" => "Code", "value" => $code);
if ($message)
	$errorDetails[] = array("label" => "Message", "value" => $message);
if ($file)
	$errorDetails[] = array("label" => "File", "value" => $file);
if ($line)
	$errorDetails[] = array("label" => "Line", "value" => $line);

$errorDetails[] = array("label" => "Date", "value" => date("Y-m-d H:i:s"));

$app->render("error.html", array("codeHash" => $codeHash, "message" => $message, "errorDetails" => $errorDetails, "trace" => nl2br($trace)));
