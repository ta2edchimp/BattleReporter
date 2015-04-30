<?php

namespace Utils\Fetcher;

interface FetcherBase {
	
	public function fetch ($url = "", $parameters = array(), $options = array());
	
}
