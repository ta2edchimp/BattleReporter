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
    
    public $died = false;
    public $killID = "";
    public $killTime = 0;
    public $priceTag = 0.0;
    
    
    private $requiredProps = array("characterID", "characterName", "corporationID", "corporationName", "allianceID", "allianceName", "shipTypeID");
    private $availableProps = array("brCombatantID", "brHidden", "brDeleted", "brTeam", "brBattlePartyID", "brManuallyAdded", "characterID", "characterName", "corporationID", "corporationName", "allianceID", "allianceName", "shipTypeID", "shipTypeName", "died", "killID", "killTime", "priceTag");
    
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
            $corpID = self::getEntityIDByName($this->corporationName);
            if ($corpID >= 0)
                $this->corporationID = $corpID;
            else
                $this->corporationName = "Unknown";
        }
        
        if ($this->allianceID == -1 && !empty($this->allianceName)) {
            $alliID = self::getEntityIDByName($this->allianceName);
            if ($alliID >= 0)
                $this->allianceID = $alliID;
            else
                $this->allianceName = "";
        }
        
        // Detect ship name from its id, if not already delivered
        if (empty($this->shipTypeName))
            $this->shipTypeName = Item::getNameByID($this->shipTypeID);
        else {
            if (empty($this->shipTypeID) || $this->shipTypeID <= 0)
                $this->shipTypeID = Item::getIDByName($this->shipTypeName);
        }
            
        
        if (!empty($killID)) {
            $this->died = true;
            $this->killID = $killID;
        }
    }
    
    
    public function save($partyID = 0) {
        
        if ($partyID <= 0)
            throw new Exception("Houston, we got a problem: The database has absolutely no idea, where to put the pilot " . $this->characterName . " (" . $this->characterID . ").");
        
        // Not yet saved combatants that have been deleted
        // are simply not saved ...
        if ($this->brCombatantID <= 0 && $this->brDeleted == true)
            return;
        
        global $db;
        
        $params = array(
            "characterID" => $this->characterID,
            "characterName" => $this->characterName,
            "corporationID" => $this->corporationID,
            "corporationName" => $this->corporationName,
            "allianceID" => $this->allianceID,
            "allianceName" => $this->allianceName,
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
                "(characterID, characterName, corporationID, corporationName, allianceID, allianceName, brHidden, brBattlePartyID, shipTypeID, died, killID, killTime, priceTag, brManuallyAdded, brDeleted) " .
                "values " .
                "(:characterID, :characterName, :corporationID, :corporationName, :allianceID, :allianceName, :brHidden, :brBattlePartyID, :shipTypeID, :died, :killID, :killTime, :priceTag, :brManuallyAdded, :brDeleted)",
                $params
            );
            if ($result != NULL)
                $this->brCombatantID = $db->lastInsertId();
        } else {
            $params["brCombatantID"] = $this->brCombatantID;
            $result = $db->query(
                "update brCombatants " .
                "set characterID = :characterID, characterName = :characterName, corporationID = :corporationID, corporationName = :corporationName, allianceID = :allianceID, allianceName = :allianceName, brHidden = :brHidden, brBattlePartyID = :brBattlePartyID, shipTypeID = :shipTypeID, died = :died, killID = :killID, killTime = :killTime, priceTag = :priceTag, brManuallyAdded = :brManuallyAdded, brDeleted = :brDeleted " .
                "where brCombatantID = :brCombatantID",
                $params
            );
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
    
    
    public static function sorter($a, $b) {
        if ($a->characterID == $b->characterID) {
            // If its the same char, sort by dead or alive
            if ($a->died == $b->died) {
                // If he didn't die in between, sort by ship
                if ($a->shipTypeID == $b->shipTypeID) {
                    // If ships are the same, sort by kill id
                    strcmp($a->killID, $b->killID);
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
    private static function getEntityIDByName($name = "") {
        
        if (empty($name))
            return -1;
        
        if (isset(self::$fetchedEntityNameIds["name#" . $name]))
            return self::$fetchedEntityNameIds["name#" . $name];

        $pheal = new \Pheal\Pheal();
        $response = $pheal->eveScope->CharacterID(array("names" => $name));
        
        if ($response != null && $response->characters != null) {
            foreach ($response->characters as $row) {
                if (strtolower($row->name) == strtolower($name)) {
                    $result = intVal($row->characterID);
                    $result = ($result > 0 ? $result : -1);
                    self::$fetchedEntityNameIds["name#" . $name] = $result;
                    return $result;
                }
            }
        }
        
        return -1;
        
    }
    
}