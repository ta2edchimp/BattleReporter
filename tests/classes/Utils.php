<?php

namespace tests\units;

require_once 'classes/Fetcher/FetcherInterface.php';
require_once 'classes/Fetcher/FetcherBase.php';
require_once 'classes/Fetcher/Curl.php';
require_once 'classes/Fetcher/File.php';
require_once 'classes/Utils.php';

use atoum;

class Utils extends atoum {

	/**
	 * @covers Utils::arrayToObject
	 * @covers Utils::objectToArray
	 */
	public function testArraysAndObjects () {

		$this
			->if($inputArray = array(
					'foo' => 'bar'
				))
			->and($outputObject = \Utils::arrayToObject($inputArray))
			->then
				->object($outputObject)
					->string($outputObject->foo)
						->isEqualTo('bar')

			->if($outputArray = \Utils::objectToArray($outputObject))
			->then
				->array($outputArray)
					->hasKey('foo')
						->contains('bar')
					->notHasKey('bar')
						->notContains('foo');

	}

	/**
	 * @covers Utils::setFetcher
	 * @covers Utils::getFetcher
	 */
	public function testSetAndGetFetcher () {

		$this
			->if($curlFetcher = new \Utils\Fetcher\Curl())
			->and(\Utils::setFetcher($curlFetcher))
			->then
				->object(\Utils::getFetcher())
					->isIdenticalTo($curlFetcher)

			->if($fileFetcher = new \Utils\Fetcher\File())
			->and(\Utils::setFetcher($fileFetcher))
			->then
				->object(\Utils::getFetcher())
					->isIdenticalTo($fileFetcher);

	}

}
