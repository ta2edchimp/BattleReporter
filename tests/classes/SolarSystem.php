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

	/**
	 * @covers SolarSystem::getByName
	 */
	public function testGetByName () {
		$this
			->array(\SolarSystem::getByName('Jita'))
			->isNotEmpty()
			->hasKeys(array('name', 'id'))
			->containsValues(array('Jita', 30000142));
	}

	/**
	 * @covers SolarSystem::getByID
	 */
	public function testGetByID () {
		$this
			->string(\SolarSystem::getByID(30000142))
			->isEqualTo('Jita');
	}

	/**
	 * @covers SolarSystem::getAllByPartialName
	 */
	public function testAllGetByPartialNameStartingWith () {
		$this
			->array(\SolarSystem::getAllByPartialName('Jit'))
			->isNotEmpty()
			->contains(array('name' => 'Jita', 'id' => 30000142));
	}

	/**
	 * @covers SolarSystem::getAllByPartialNameContaining
	 */
	public function testAllGetByPartialNameContaining () {
		$this
			->array(\SolarSystem::getAllByPartialName('it'))
			->isNotEmpty()
			->contains(array('name' => 'Jita', 'id' => 30000142))
			->contains(array('name' => 'Arittant', 'id' => 30003595))
			->contains(array('name' => 'Gonditsa', 'id' => 30005268));
	}

}
