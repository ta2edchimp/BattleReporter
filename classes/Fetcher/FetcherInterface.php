<?php

namespace Utils\Fetcher;

interface FetcherInterface {
	
	public function fetch ($url = "", $parameters = array(), $options = array());
	
}
