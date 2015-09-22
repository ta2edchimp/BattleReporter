<?php

namespace tests\units;

require_once 'classes/KBFetch.php';

use atoum;

class KBFetch extends atoum {

	/**
	 * @covers KBFetch::testTimespanPattern
	 */
	public function testTestTimespanPattern () {

		$this
			->boolean(\KBFetch::testTimespanPattern('2015.09.22 21:00-22:00'))
				->isFalse()
			->boolean(\KBFetch::testTimespanPattern('2015-09-22 21:00-22:00'))
				->isFalse()
			->boolean(\KBFetch::testTimespanPattern('2015-09-22 21:00 - 22:00'))
				->isTrue();

	}

	/**
	 * @covers KBFetch::getDateTime
	 */
	public function testGetDateTime () {

		$this
			->datetime(\KBFetch::getDateTime('2015-09-21 21:00 - 22:00'))
				->hasDate(2015, 9, 21, 21, 0)
			->datetime(\KBFetch::getDateTime('2015-09-21 23:50 - 23:55', true))
				->hasDate(2015, 9, 21, 23, 55)
			->datetime(\KBFetch::getDateTime('2015-09-21 23:55 - 00:05', true))
				->hasDate(2015, 9, 22, 0, 5);

	}

	/**
	 * @covers KBFetch::getZKBStartTime
	 */
	public function testGetZKBStartTime () {

		$this
			->string(\KBFetch::getZKBStartTime('2015-09-21 23:55 - 00:05'))
				->isEqualTo('201509212355');

	}

	/**
	 * @covers KBFetch::getZKBEndTime
	 */
	public function testGetZKBEndTime () {

		$this
			->string(\KBFetch::getZKBEndTime('2015-09-21 23:55 - 00:05'))
				->isEqualTo('201509220005');

	}

}
