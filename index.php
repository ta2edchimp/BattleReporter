<?php
require_once 'vendor/autoload.php';

//import namespace
use Pheal\Pheal;

// create pheal object with default values
// so far this will not use caching, and since no key is supplied
// only allow access to the public parts of the EVE API
$pheal = new Pheal();

// requests /server/ServerStatus.xml.aspx
$response = $pheal->serverScope->ServerStatus();

echo sprintf(
  "Hello Visitor! The EVE Online Server is: %s!, current amount of online players: %s",
  $response->serverOpen ? "open" : "closed",
  $response->onlinePlayers
);

?>