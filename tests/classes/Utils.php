<?php

namespace tests\units;

require_once 'vendor/autoload.php';

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
				->string(\Utils::fetch(
					"https://api.github.com/repos/ta2edchimp/BattleReporter",
					null,
					array(
						"headers" => array("User-Agent: ta2edchimp/BattleReporter Unit Test"),
						"queryParams" => false,
						"caching" => false
					)
				))
					->isNotEmpty()
					->matches("/\"name\":\s*\"BattleReporter\"/")
					->matches("/\"full_name\":\s*\"ta2edchimp\/BattleReporter\"/")
					->matches("/\"fork\":\s*false/")

			->if($fileFetcher = new \Utils\Fetcher\File())
			->and(\Utils::setFetcher($fileFetcher))
			->then
				->object(\Utils::getFetcher())
					->isIdenticalTo($fileFetcher);

	}

	/**
	 * @covers Utils::parseVersion
	 */
	public function testParseVersion () {

		$this
			->given($parsedVersion = \Utils::parseVersion("any string."))
				->array($parsedVersion)
					->containsValues(
						array(
							"valid" => false
						)
					)

			->given($parsedVersion = \Utils::parseVersion("1.2.3"))
				->array($parsedVersion)
					->containsValues(
						array(
							"major" => 1,
							"minor" => 2,
							"patch" => 3,
							"valid" => true
						)
					)

			->given($parsedVersion = \Utils::parseVersion("v4.5.67"))
				->array($parsedVersion)
					->containsValues(
						array(
							"major" => 4,
							"minor" => 5,
							"patch" => 67,
							"valid" => true
						)
					);

	}

}
