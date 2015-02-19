<?php

class Combatant {
    
    public $brCombatantID = 0;
    public $brHidden = false;
    public $brDeleted = false;
    public $brTeam = "";
    public $brBattlePartyID = 0;
	public $brManuallyAdded = false;
    
    public $characterID;
    public $characterName;
    
    public $corporationID;
    public $corporationName;
    
    public $allianceID;
    public $allianceName;
    
    public $shipTypeID = 0;
    public $shipTypeName = "";
	public $shipIsPod = null;
    
    public $died = false;
    public $killID = "";
    public $killTime = 0;
    public $priceTag = 0.0;
    
    
    private $requiredProps = array("characterID", "characterName", "corporationID", "corporationName", "allianceID", "allianceName", "shipTypeID");
    private $availableProps = array("brCombatantID", "brHidden", "brDeleted", "brTeam", "brBattlePartyID", "brManuallyAdded", "characterID", "characterName", "corporationID", "corporationName", "allianceID", "allianceName", "shipTypeID", "shipTypeName", "shipIsPod", "died", "killID", "killTime", "priceTag");
    
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
            if (empty($this->shipTypeID) || $this->shipTypeID <= 0)
                $this->shipTypeID = Item::getIDByName($this->shipTypeName);
        }
		if ($this->shipIsPod === null)
			$this->shipIsPod = Item::isCapsule($this->shipTypeID);
            
        
        if (!empty($killID)) {
            $this->died = true;
            $this->killID = $killID;
        }
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
			"brDeleted" => $this->brDeleted ? 1 : 0
        );
        if ($this->brCombatantID <= 0) {
            $result = $db->query(
                "insert into brCombatants ".
                "(characterID, characterName, corporationID, allianceID, brHidden, brBattlePartyID, shipTypeID, died, killID, killTime, priceTag, brManuallyAdded, brDeleted) " .
                "values " .
                "(:characterID, :characterName, :corporationID, :allianceID, :brHidden, :brBattlePartyID, :shipTypeID, :died, :killID, :killTime, :priceTag, :brManuallyAdded, :brDeleted)",
                $params
            );
            if ($result !== NULL && $result !== FALSE && $result == 1)
                $this->brCombatantID = $db->lastInsertId();
			// Else fail silently ...
        } else {
            $params["brCombatantID"] = $this->brCombatantID;
            $result = $db->query(
                "update brCombatants " .
                "set characterID = :characterID, characterName = :characterName, corporationID = :corporationID, allianceID = :allianceID, brHidden = :brHidden, brBattlePartyID = :brBattlePartyID, shipTypeID = :shipTypeID, died = :died, killID = :killID, killTime = :killTime, priceTag = :priceTag, brManuallyAdded = :brManuallyAdded, brDeleted = :brDeleted " .
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
    
    
    public function toJSON() {
        $props = array();
        
        foreach ($this->availableProps as $key) {
            if (isset($this->$key))
                $props[$key] = $this->$key;
        }
        
        $props["type"] = "combatant";
        
        return json_encode($props);
    }
    
    
    public static function sorter(Combatant $a, Combatant $b) {
        if ($a->characterID == $b->characterID) {
            // If its the same char, sort by dead or alive
            if ($a->died == $b->died) {
                // If he didn't die in between, sort by ship
                if ($a->shipTypeID == $b->shipTypeID) {
                    // If ships are the same, sort by kill id
                    return strcmp($a->killID, $b->killID);
                }
				// If one of the ships is a pod,
				// display the non-capsule first
				if ($a->shipIsPod || $b->shipIsPod) {
					return $a->shipIsPod ? 1 : -1;
				}
                return strcasecmp($a->shipTypeName, $b->shipTypeName);
            }
            return ($a->died && !$b->died) ? -1 : 1;
        }
        // If one of them is an NPC
        if ($a->characterID == 0)
            return 1;
        if ($b->characterID == 0)
            return -1;
        // By default, sort alphabetically
        return strcasecmp($a->characterName, $b->characterName);
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

        $pheal = new \Pheal\Pheal();
        $response = $pheal->eveScope->CharacterID(array("names" => $name));
        
        if ($response !== null && $response->characters !== null) {
            foreach ($response->characters as $row) {
                if (strtolower($row->name) == strtolower($name)) {
                    $result = intVal($row->characterID);
                    $result = ($result > 0 ? $result : -1);
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
		
		
		$pheal = new \Pheal\Pheal();
		$response = $pheal->corpScope->CorporationSheet(array("corporationID" => $corp["entityID"]));
		
		if ($response !== null && $response->allianceID !== null && $response->allianceName !== null) {
			$result["allianceID"] = $response->allianceID;
			$result["allianceName"] = $response->allianceName;
		}
		
		return $result;
		
	}
    
}
