<?php

class Combatant {
	
	public $brCombatantID = 0;
	public $brHidden = false;
	public $brDeleted = false;
	public $brTeam = "";
	public $brBattlePartyID = 0;
	public $brManuallyAdded = false;
	
	public $brCyno = false;
	
	public $characterID;
	public $characterName;
	
	public $corporationID;
	public $corporationName;
	
	public $allianceID;
	public $allianceName;
	
	public $shipTypeID = 0;
	public $shipTypeName = "";
	public $shipTypeMass = 0;
	
	public $shipGroup = "DPS";
	public $shipGroupOrderKey = 0;
	public $shipIsPod = null;
	
	public $died = false;
	public $killID = "";
	public $killTime = 0;
	public $priceTag = 0.0;

	public $damageTaken = 0.0;
	public $damageDealt = 0.0;
	public $damageComposition = null;
	
	public $assignedFootage = 0;
	
	private $hasBeenRemoved = false;
	
	private $requiredProps = array("characterID", "characterName", "corporationID", "corporationName", "allianceID", "allianceName", "shipTypeID");
	private $availableProps = array("brCombatantID", "brHidden", "brDeleted", "brTeam", "brBattlePartyID", "brManuallyAdded", "characterID", "characterName", "corporationID", "corporationName", "allianceID", "allianceName", "shipTypeID", "shipTypeName", "shipTypeMass", "shipGroup", "shipGroupOrderKey", "shipIsPod", "brCyno", "died", "killID", "killTime", "priceTag", "assignedFootage", "damageTaken", "damageDealt", "damageComposition");
	
	public function __construct($props, $killID = "") {
		
		$props = Utils::arrayToObject($props);
		
		foreach ($this->requiredProps as $key) {
			if (!isset($props->$key))
				throw new Exception("Given properties do not meat a combatant's requirements (\"$key\" is missing)!");
		}
		
		foreach ($this->availableProps as $key) {
			if (isset($props->$key))
				$this->$key = $props->$key;
		}
		
		// If it's not from the db, it might have no internal combatant id
		if ($this->brCombatantID == 0)
			$this->brCombatantID = self::getNextCombatantID();
		
		if ($this->corporationID == -1 && !empty($this->corporationName) && $this->corporationName != "Unknown") {
			$corp = self::getCorpInfoByName($this->corporationName);
			if ($corp !== null) {
				$this->corporationID = $corp["corporationID"];
				$this->corporationName = $corp["corporationName"];
				if (isset($corp["allianceID"]) && isset($corp["allianceName"]) && !empty($corp["allianceID"]) && !empty($corp["allianceName"])) {
					$this->allianceID = $corp["allianceID"];
					$this->allianceName = $corp["allianceName"];
				}
			} else {
				$this->corporationName = "Unknown";
			}
		}
		
		if ($this->allianceID == -1 && !empty($this->allianceName)) {
			$alli = self::getEntityByName($this->allianceName);
			if ($alli !== null) {
				$this->allianceID = $alli["entityID"];
				$this->allianceName = $alli["entityName"];
			} else {
				$this->allianceName = "";
			}
		}
		
		// Detect ship name from its id, if not already delivered
		if (empty($this->shipTypeName))
			$this->shipTypeName = Item::getNameByID($this->shipTypeID);
		else {
			if (empty($this->shipTypeID) || $this->shipTypeID < 0)
				$this->shipTypeID = Item::getIDByName($this->shipTypeName);
		}
		if ($this->shipTypeID == 0)
			$this->shipTypeName = "Unknown";
		if ($this->shipIsPod === null)
			$this->shipIsPod = Item::isCapsule($this->shipTypeID);

		
		if (!empty($killID)) {
			$this->died = true;
			$this->killID = $killID;
		}
		
		// Ensure boolean type for certain values
		if (!is_bool($this->brHidden))
			$this->brHidden = ($this->brHidden == 1);
		if (!is_bool($this->brDeleted))
			$this->brDeleted = ($this->brDeleted == 1);
		if (!is_bool($this->brManuallyAdded))
			$this->brManuallyAdded = ($this->brManuallyAdded == 1);
		if (!is_bool($this->brCyno))
			$this->brCyno = ($this->brCyno == 1);
		if (!is_bool($this->died))
			$this->died = ($this->died == 1);
		
	}
	
	
	public function save($partyID = "") {
		
		if (empty($partyID))
			$partyID = $this->brBattlePartyID;
		
		if ($partyID <= 0)
			throw new Exception("Houston, we got a problem: The database has absolutely no idea, where to put the pilot " . $this->characterName . " (" . $this->characterID . ").");
		
		// Not yet saved combatants that have been deleted
		// are simply not saved ...
		if ($this->brCombatantID <= 0 && $this->brDeleted === true)
			return;
		
		$db = Db::getInstance();
		
		$params = array(
			"characterID" => $this->characterID,
			"characterName" => $this->characterName,
			"corporationID" => $this->corporationID,
			"allianceID" => $this->allianceID,
			"brHidden" => $this->brHidden ? 1 : 0,
			"brBattlePartyID" => $partyID,
			"shipTypeID" => $this->shipTypeID,
			"died" => $this->died ? 1 : 0,
			"killID" => $this->killID,
			"killTime" => $this->killTime,
			"priceTag" => $this->priceTag,
			"brManuallyAdded" => $this->brManuallyAdded ? 1 : 0,
			"brDeleted" => $this->brDeleted ? 1 : 0,
			"brCyno" => $this->brCyno ? 1 : 0,
			"damageTaken" => $this->damageTaken
		);
		if ($this->brCombatantID <= 0) {
			
			// This case should not happen: Combatant has not been saved, but was removed due to a reimported battle.
			if ($this->hasBeenRemoved === true) {
				\Slim\Slim::getInstance()->log->warn("Combatant::save() - A Combatant who has not been saved yet, but has been removed due to a reimported Battle.");
				return;
			}
			
			$result = $db->query(
				"insert into brCombatants ".
				"(characterID, characterName, corporationID, allianceID, brHidden, brBattlePartyID, shipTypeID, died, killID, killTime, priceTag, brManuallyAdded, brDeleted, brCyno, damageTaken) " .
				"values " .
				"(:characterID, :characterName, :corporationID, :allianceID, :brHidden, :brBattlePartyID, :shipTypeID, :died, :killID, :killTime, :priceTag, :brManuallyAdded, :brDeleted, :brCyno, :damageTaken)",
				$params,
				true	// Return last inserted row's ID instead of affected rows' count
			);
			
			if ($result > 0)
				$this->brCombatantID = $result;
			// Else fail silently ...
		} else {
			
			// In case this combatant has been removed, due to a reimported battle, delete it from the database
			if ($this->hasBeenRemoved === true) {
				$result = $db->query(
					"delete from brCombatants where brCombatantID = :brCombatantID",
					array(
						"brCombatantID" => $this->brCombatantID
					)
				);
				return;
			}
			
			$params["brCombatantID"] = $this->brCombatantID;
			$result = $db->query(
				"update brCombatants " .
				"set characterID = :characterID, characterName = :characterName, corporationID = :corporationID, allianceID = :allianceID, brHidden = :brHidden, brBattlePartyID = :brBattlePartyID, shipTypeID = :shipTypeID, died = :died, killID = :killID, killTime = :killTime, priceTag = :priceTag, brManuallyAdded = :brManuallyAdded, brDeleted = :brDeleted, brCyno = :brCyno, damageTaken = :damageTaken " .
				"where brCombatantID = :brCombatantID",
				$params
			);
			
		}
		
		if ($this->corporationID > 0) {
			if($db->row(
				"select * " .
				"from brCorporations " .
				"where corporationID = :corporationID and corporationName = :corporationName",
				array(
					"corporationID" => $this->corporationID,
					"corporationName" => $this->corporationName
				)) === FALSE) {
				$db->query(
					"insert into brCorporations " .
					"(corporationID, corporationName, allianceID) " .
					"values " .
					"(:corporationID, :corporationName, :allianceID)",
					array(
						"corporationID" => $this->corporationID,
						"corporationName" => $this->corporationName,
						"allianceID" => $this->allianceID > 0 ? $this->allianceID : 0
					)
				);
			}
		}
		
		if ($this->allianceID > 0) {
			if ($db->row(
				"select * " .
				"from brAlliances " .
				"where allianceID = :allianceID and allianceName = :allianceName",
				array(
					"allianceID" => $this->allianceID,
					"allianceName" => $this->allianceName
				)) === FALSE) {
				$db->query(
					"insert into brAlliances " .
					"(allianceID, allianceName) " .
					"values " .
					"(:allianceID, :allianceName)",
					array(
						"allianceID" => $this->allianceID,
						"allianceName" => $this->allianceName
					)
				);
			}
		}
		
	}

	public function saveAdditionalData() {

		if ($this->damageComposition === null || count($this->damageComposition) === 0)
			return;

		$db = \Db::getInstance();

		$db->query(
			"delete from brDamageComposition where brDealingCombatantID = :brCombatantID",
			array(
				"brCombatantID" => $this->brCombatantID
			)
		);

		foreach ($this->damageComposition as $dmgPart) {
			if ($dmgPart === null || empty($dmgPart["receiver"]) || empty($dmgPart["amount"]))
				continue;
			$db->query(
				"insert into brDamageComposition " .
				"(brReceivingCombatantID, brDealingCombatantID, brDamageDealt) " .
				"values " .
				"(:brReceivingCombatantID, :brDealingCombatantID, :brDamageDealt)", 
				array(
					"brReceivingCombatantID" => $dmgPart["receiver"]->brCombatantID,
					"brDealingCombatantID" => $this->brCombatantID,
					"brDamageDealt" => $dmgPart["amount"]
				)
			);
		}

	}

	public function update($props = null) {

		if ($props === null)
			return;

		$props = \Utils::arrayToObject($props);

		if (!empty($props->damageComposition)) {
			if ($this->damageComposition === null)
				$this->damageComposition = array();
			if (count($this->damageComposition) === 0) {
				$this->damageComposition = $props->damageComposition;
			} else {
				$this->damageComposition = array_merge($this->damageComposition, $props->damageComposition);
			}
		}

	}
	
	public function removeFromDatabase() {
		
		$this->hasBeenRemoved = true;
		
	}
	
	public function toArray() {
		
		$props = array();
		
		foreach ($this->availableProps as $key) {
			if (isset($this->$key))
				$props[$key] = $this->$key;
		}
		
		$props["type"] = "combatant";
		
		return $props;
		
	}
		
	public function toJSON() {
		
		return json_encode($this->toArray());
		
	}
	
	
	public static function sorter(Combatant $a, Combatant $b) {
		
		// First, sort by ship group (per default: Capital, Logi, Ewar, default is DPS) ...
		if ($a->shipGroupOrderKey == $b->shipGroupOrderKey) {
			
			// ... then, sort by mass as hint to the ship's hull class (bs, bc, cruiser, frig) ...
			if ($a->shipTypeMass == $b->shipTypeMass) {
				
				// ... and within that group,
				// sort by ship name (Vexor before Vexor Navy Issue) ...
				if ($a->shipTypeName == $b->shipTypeName) {
					
					// ... by character's name
					if ($a->characterName == $b->characterName) {
						
						// ... and losses first
						if ($a->died == $b->died)
							return $a->killID < $b->killID ? 1 : -1;
						
						return ($a->died && !$b->died) ? -1 : 1;
					}
					
					return strcasecmp($a->characterName, $b->characterName);
				}
				
				return strcasecmp($a->shipTypeName, $b->shipTypeName);
			}
			
			return $a->shipTypeMass < $b->shipTypeMass ? 1 : -1;
		}
		
		return $a->shipGroupOrderKey < $b->shipGroupOrderKey ? 1 : -1;
		
	}
	
	private static $lastCombatantID = 0;
	private static function getNextCombatantID() {
		self::$lastCombatantID--;
		return self::$lastCombatantID;
	}
	
	
	private static $fetchedEntityNameIds = array();
	private static function getEntityByName($name = "") {
		
		if (empty($name))
			return null;
		
		if (isset(self::$fetchedEntityNameIds["name#" . strtolower($name)]))
			return self::$fetchedEntityNameIds["name#" . strtolower($name)];
		
		try {
			$pheal = new \Pheal\Pheal();
			$response = $pheal->eveScope->CharacterID(array("names" => $name));
		} catch (\Pheal\Exceptions\PhealException $pex) {
			\Slim\Slim::getInstance()->log->warn("Combatant::getEntityByName() - EVE API Fetch for \"" . json_encode($name) . "\" raised an Exception:\n" . $pex);
			return null;
		}
		
		if ($response !== null && $response->characters !== null) {
			foreach ($response->characters as $row) {
				if (strtolower($row->name) == strtolower($name)) {
					$result = intVal($row->characterID);
					if ($result <= 0)
						return null;
					self::$fetchedEntityNameIds["name#" . strtolower($name)] = array(
						"entityName" => $row->name,
						"entityID" => $result
					);
					return self::$fetchedEntityNameIds["name#" . strtolower($name)];
				}
			}
		}
		
		return null;
		
	}
	
	private static function getCorpInfoByName($name = "") {
		
		if (empty($name))
			return null;
		
		// first, try to get this information from the database
		$db = Db::getInstance();
		
		$result = $db->query(
			"select c.corporationID, c.corporationName, ifnull(a.allianceID, 0) as allianceID, ifnull(a.allianceName, '') as allianceName " .
			"from brCorporations as c left outer join brAlliances as a " .
				"on c.allianceID = a.allianceID " .
			"where c.corporationName like :corporationName " .
			"limit 1",
			array(
				"corporationName" => $name
			)
		);
		
		if ($result !== NULL && count($result) > 0)
			return $result[0];
		
		$corp = self::getEntityByName($name);
		if ($corp === null)
			return null;
		
		$result = array(
			"corporationID" => $corp["entityID"],
			"corporationName" => $corp["entityName"]
		);
		
		
		try {
			$pheal = new \Pheal\Pheal();
			$response = $pheal->corpScope->CorporationSheet(array("corporationID" => $corp["entityID"]));
		} catch (\Pheal\Exceptions\PhealException $pex) {
			\Slim\Slim::getInstance()->log->warn("Combatant::getCorpInfoByName() - EVE API Fetch for \"" . json_encode($name) . "\" raised an Exception:\n" . $pex);
			return $result;
		}
		
		if ($response !== null && $response->allianceID !== null && $response->allianceName !== null) {
			$result["allianceID"] = $response->allianceID;
			$result["allianceName"] = $response->allianceName;
		}
		
		return $result;
		
	}
	
}
