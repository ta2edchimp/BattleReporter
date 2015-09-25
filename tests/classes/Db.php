<?php

namespace tests\units;

require_once 'config.php';
require_once 'classes/Db.php';

use atoum;

class Db extends atoum {

	/**
	 * @covers Db::setCredentials
	 * @covers Db::getInstance
	 */
	public function testDbAccess () {
		$this
			->if(\Db::setCredentials(DB_NAME, DB_USER, DB_PASS, DB_HOST))
			->and($db = \Db::getInstance())
			->then
				->object($db)
				->isInstanceOf('\Db');
	}

	/**
	 * @covers Db::single
	 */
	public function testSingle () {
		$this
			->if($db = new \Db(DB_NAME, DB_USER, DB_PASS, DB_HOST))
			->then
				->string($db->single("SELECT 'foo';"))
					->isEqualTo('foo');
	}

	/**
	 * @covers Db::row
	 */
	public function testRow () {
		$this
			->if($db = new \Db(DB_NAME, DB_USER, DB_PASS, DB_HOST))
			->then
				->array($db->row("SELECT 'bar' AS `foo`;"))
					->isEqualTo(array('foo' => 'bar'));
	}

	/**
	 * @covers Db::column
	 */
	public function testColumn () {
		$this
			->if($db = new \Db(DB_NAME, DB_USER, DB_PASS, DB_HOST))
			->then
				->array($db->column("SELECT 'bar' AS `foo`, 'notcontained' AS `expectation` UNION SELECT 'baz' AS `foo`, 'notcontained2' AS `expectation`;"))
					->containsValues(array('bar', 'baz'))
					->notContainsValues(array('notcontained', 'notcontained2'));
	}

}
