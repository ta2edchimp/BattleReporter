<?php

class BattleParty {
    
    public $members = array();
    
    public $length = 0;
    
    public $uniquePilots = 0;
    public $totalLost = 0.0;
    public $efficiency = 0.0;
    
    
    public function __construct() {
        
    }
    
    
    public function add($combatant = "") {
        if (empty($combatant))
            return;
        
        // Test, if combatant has not yet been added
        // That is having reshipped counts as being another combatant
        if (count($this->members) > 0) {
            foreach ($this->members as &$member) {
                if ($member->characterID == $combatant->characterID) {
                    if ($member->shipTypeID == $combatant->shipTypeID) {
                        // If this char is already on the list in the same ship,
                        // but this time is the victim, replace him.
                        if ($member->died == false && $combatant->died == true) {
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
        
        foreach ($this->members as $member) {
            if (!in_array($member->characterID, $pilots))
                $pilots[] = $member->characterID;
            $this->totalLost += $member->priceTag;
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
    
    private function getUniquePilotsCount() {
        $pilots = array();
        foreach ($this->members as $member) {
            if (!in_array($member->characterID, $pilots))
                $pilots[] = $member->characterID;
        }
        return count($pilots);
    }
    
    
    public function sort() {
        usort($this->members, 'Combatant::sorter');
    }
    
    
    public function toJSON() {
        
        $members = array();
        foreach ($this->members as $combatant)
            $members[] = $combatant->toJSON();
        
        return '{' .
            '"type":"party",' .
            '"length":' . $this->length . ',' .
            '"members":[' . implode(",", $members) . ']' . //,' .
        '}';
    }
    
}