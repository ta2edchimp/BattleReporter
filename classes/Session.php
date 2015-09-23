<?php

class Session implements SessionHandlerInterface {
	
	private $cache;
	
	private $ttl = 7200;
	
	public function __construct() {
		$this->cache = phpFastCache();
	}
	
	public function open($savePath, $sessionName) {
		return true;
	}
	
	public function close() {
		return true;
	}
	
	public function read($id) {
		$data = $this->cache->get($id);
		if (is_array($data))
			return "";
		return $this->cache->get($id);
	}
	
	public function write($id, $data) {
		$this->cache->set($id, $data, $this->ttl);
		return true;
	}
	
	public function destroy($id) {
		$this->cache->delete($id);
		return true;
	}
	
	public function gc($maxlifetime) {
		return true;
	}
	
}
