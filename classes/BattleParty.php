<?php

class BattleParty {
    
    public $brBattlePartyID = 0;
    public $name = "";
    
    public $members = array();
    
    public $uniquePilots = 0;
    public $losses = 0;
    public $totalLost = 0.0;
    public $efficiency = 0.0;
    
    
    public function __construct($name = "") {
        if (!empty($name))
            $this->name = $name;
    }
    
    
	public function getMember(Combatant $combatant) {
		
		if (count($this->members) > 0 && $combatant->characterID >= 0) {
			foreach ($this->members as &$member) {
				if ($member->characterID == $combatant->characterID) {
					if ($member->shipTypeID == $combatant->shipTypeID) {
						// If this char is already on the list in the same ship, but this time
						// is the victim, he basically counts as another combatant ...
						if ($member->died === false && $combatant->died === true)
							continue;
						
						// If he is on the list, in the same ship, died this time
						// and the latter, but the killIDs differ, well, he died again
						if ($member->died && $combatant->died && $member->killID != $combatant->killID)
							continue;
						
						// Either way, he's already on the list
						return $member;
					}
				}
			}
		}
		
		// Combatant is definitely not in yet
		return null;
		
	}
	
    public function add(Combatant $combatant) {
        
        // Test, if combatant has not yet been added
        // That is having reshipped counts as being another combatant
        // Does not include "Unknown" (manually added) pilots
        if (count($this->members) > 0 && $combatant->characterID >= 0) {
            foreach ($this->members as &$member) {
                if ($member->characterID == $combatant->characterID) {
                    if ($member->shipTypeID == $combatant->shipTypeID) {
                        // If this char is already on the list in the same ship,
                        // but this time is the victim, replace him.
                        if ($member->died === false && $combatant->died === true) {
                            // Replace the existing member
                            $member = $combatant;
                            return;
                        }
                        // If he is on the list, in the same ship, died this time
                        // and the latter, but the killIDs differ, well, he died again
                        if ($member->died && $combatant->died && $member->killID != $combatant->killID) {
                            $this->members[] = $combatant;
                            $this->updateDetails();
                        }
                        // Either way, he's already on the list
                        return;
                    }
                }
            }
        }
        
        $this->members[] = $combatant;
        $this->updateDetails();
    }
    
    public function updateDetails($otherParties = array()) {
        $this->length = count($this->members);
        
        $pilots = array();
        $this->totalLost = 0.0;
        $this->losses = 0;
        
        foreach ($this->members as $member) {
            if (!$member->brHidden && (!in_array($member->characterID, $pilots) || $member->characterID <= 0))
                $pilots[] = $member->characterID;
            $this->totalLost += $member->priceTag;
            if ($member->died)
                $this->losses++;
        }
        $this->uniquePilots = count($pilots);
        
        if (count($otherParties) == 0)
            return;
        
        $totalLost = $this->totalLost;
        foreach ($otherParties as $otherParty)
            $totalLost += $otherParty->totalLost;
        
        if ($totalLost > 0.0 && $this->uniquePilots > 0)
            $this->efficiency = 1.0 - $this->totalLost / $totalLost;
        else
            $this->efficiency = 0.0;
    }
    
    
    public function sort() {
        usort($this->members, 'Combatant::sorter');
    }
    
    public function load($brID = 0, $toBeEdited = false) {
        
        if ($brID <= 0)
            throw new Exception("Cannot load a battle party from a non existent battle report!");
        
        if (empty($this->name))
            return false;
        
        $db = Db::getInstance();
        
        // Fetch corresponding records from database
        $result = $db->row(
            "select * from brBattleParties " .
            "where battleReportID = :battleReportID and brTeamName = :brTeamName",
            array(
                "battleReportID" => $brID,
                "brTeamName" => $this->name
            )
        );
        if ($result == NULL)
            return false;
        
        // Assign battle party id
        $this->brBattlePartyID = $result["brBattlePartyID"];
        
        // Fetch team members
        $team = $db->query(
			"select distinct c.*, ifnull(cc.corporationName, 'Unknown') as corporationName, " .
				"ifnull(a.allianceName, '') as allianceName, t.typeName as shipTypeName, t.mass as shipTypeMass, " .
				"bpg.battlePartyGroupName as shipGroup, bpg.battlePartyGroupOrderKey as shipGroupOrderKey, " .
				"(select videoID from brVideos where videoPoVCombatantID = c.brCombatantID order by videoID limit 1) as assignedFootage " .
			"from brBattlePartyGroups as bpg right outer join brBattlePartyGroupShipTypes as bpgst " .
				"on bpg.battlePartyGroupID = bpgst.brBattlePartyGroupID " .
			"right outer join invTypes as t " .
				"on bpgst.shipTypeID = t.typeID " .
			"inner join invGroups as g " .
				"on g.groupID = t.groupID " .
			"right outer join (" .
					"select * from brCombatants " .
					"where brBattlePartyID = :brBattlePartyID and (brManuallyAdded = 0 or brDeleted = 0)" .
				") as c " .
				"on t.typeID = c.shipTypeID " .
			"left outer join brCorporations as cc " .
				"on c.corporationID = cc.corporationID " .
			"left outer join brAlliances as a " .
				"on c.allianceID = a.allianceID " .
            "where (g.groupName <> 'Capsule' or c.died = 1) " .
				($toBeEdited ? "" : "and brHidden = 0 ") .
			"order by bpg.battlePartyGroupOrderKey desc, t.mass desc, t.typeName desc, c.characterName asc, c.died desc",
            array(
                "brBattlePartyID" => $this->brBattlePartyID
            )
        );
        
        foreach ($team as $memberData) {
            $combatant = new Combatant($memberData);
            if ($combatant !== null)
                $this->add($combatant);
        }
        
        return true;
        
    }
    
    public function save($brID = 0) {
        
        if ($brID <= 0)
            throw new Exception("Cannot save a battle party to a non existent battle report!");
        
        $db = Db::getInstance();
        
		// Save basic battle report properties
		if ($this->brBattlePartyID <= 0) {
			$result = $db->query(
				"insert into brBattleParties ".
				"(battleReportID, brTeamName) " .
				"values " .
				"(:battleReportID, :brTeamName)",
				array(
					"battleReportID" => $brID,
					"brTeamName" => $this->name
				),
				true	// Return last inserted row's ID instead of affected rows' count
			);
			if ($result > 0)
				$this->brBattlePartyID = $result;
        } else {
            $result = $db->query(
                "update brBattleParties " .
                "set battleReportID = :battleReportID, brTeamName = :brTeamName " .
                "where brBattlePartyID = :brBattlePartyID",
                array(
                    "battleReportID" => $brID,
                    "brTeamName" => $this->name,
                    "brBattlePartyID" => $this->brBattlePartyID
                )
            );
        }
        
        // Save the combatants properly assigned to this battle party
        foreach ($this->members as $combatant)
            $combatant->save($this->brBattlePartyID);
        
    }
	
	public function toArray() {
		
		$members = array();
		foreach ($this->members as $combatant)
			$members[] = $combatant->toArray();
		
		return array(
			"type" => "party",
			"name" => $this->name,
			"members" => $members
		);
		
	}
	
	public function toJSON() {
		
		return json_encode($this->toArray());
	}
	
}
