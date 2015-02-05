<?php

class Combatant {
    
    public $brCombatantID = 0;
    public $brHidden = false;
    public $brDelete = false;
    public $brTeam = "";
    
    public $characterID;
    public $characterName;
    
    public $corporationID;
    public $corporationName;
    
    public $allianceID;
    public $allianceName;
    
    public $shipTypeID;
    public $shipTypeName = "";
    
    public $died = false;
    public $killID = "";
    public $priceTag = 0.0;
    
    
    private $requiredProps = array("characterID", "characterName", "corporationID", "corporationName", "allianceID", "allianceName", "shipTypeID");
    private $availableProps = array("brCombatantID", "brHidden", "brDelete", "brTeam", "characterID", "characterName", "corporationID", "corporationName", "allianceID", "allianceName", "shipTypeID", "shipTypeName", "died", "killID", "priceTag");
    
    public function __construct($props, $killID = "") {
        foreach ($this->requiredProps as $key) {
            if (!isset($props->$key))
                throw new Exception("Given properties do not meat a combatant's requirements!");
        }
        
        foreach ($this->availableProps as $key) {
            if (isset($props->$key))
                $this->$key = $props->$key;
        }
        
        // If it's not from the db, it might have no internal combatant id
        if ($this->brCombatantID == 0)
            $this->brCombatantID = self::getNextCombatantID();
        
        // Detect ship name from its id, if not already delivered
        if (empty($this->shiptTypeName))
            $this->shipTypeName = Item::getNameByID($this->shipTypeID);
        
        if (!empty($killID)) {
            $this->died = true;
            $this->killID = $killID;
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
                return strcmp($a->shipTypeName, $b->shipTypeName);
            }
            return ($a->died && !$b->died) ? -1 : 1;
        }
        // If one of them is an NPC
        if ($a->characterID == 0)
            return 1;
        if ($b->characterID == 0)
            return -1;
        // By default, sort alphabetically
        return strcmp($a->characterName, $b->characterName);
    }
    
    private static $lastCombatantID = 0;
    private static function getNextCombatantID() {
        self::$lastCombatantID--;
        return self::$lastCombatantID;
    }
    
}