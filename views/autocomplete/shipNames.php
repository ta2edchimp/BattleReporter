<?php

$params = $app->request->post();

if (isset($params["query"])) {
	
	$ships = Item::getAllShipsByPartialName($params["query"]);
	
	$app->status(200);
	$app->contentType('application/json');
	
	echo '{"ships":' . json_encode($ships) . '}';
	
}
