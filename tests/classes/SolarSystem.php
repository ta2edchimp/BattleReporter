<?php

namespace tests\units;

require_once 'config.php';
require_once 'classes/Db.php';

// Global database access wrapper object
\Db::setCredentials(DB_NAME, DB_USER, DB_PASS, DB_HOST);
$db = \Db::getInstance();

require_once 'classes/SolarSystem.php';

use atoum;

class SolarSystem extends atoum {

	public function testGetByName () {
		$this
			->array(\SolarSystem::getByName('Jita'))
			->isNotEmpty()
			->hasKeys(array('name', 'id'))
			->containsValues(array('Jita', 30000142));
	}

}
