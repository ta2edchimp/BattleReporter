<?php

$code		= $e->getCode();
$message	= $e->getMessage();
$file		= $e->getFile();
$line		= $e->getLine();
$trace		= $e->getTraceAsString();

$codeHash = md5($trace);

$errorDetails = array();

if ($code)
	$errorDetails["Code"] = $code;
if ($message)
	$errorDetails["Message"] = $message;
if ($file)
	$errorDetails["File"] = $file;
if ($line)
	$errorDetails["Line"] = $line;

$errorDetails["Date"] = date("Y-m-d H:i:s");

$app->render("error.html", array("codeHash" => $codeHash, "message" => $message, "errorDetails" => $errorDetails, "trace" => nl2br($trace)));