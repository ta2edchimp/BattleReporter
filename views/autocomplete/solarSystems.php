<?php

$params = $app->request->post();

if (isset($params["query"])) {
    
    $systems = SolarSystem::getAllByPartialName($params["query"]);
    
    $app->status(200);
    $app->contentType('application/json');
    
    echo '{"solarSystems":' . json_encode($systems) . '}';
    
}
