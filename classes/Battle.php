<?php

class Battle {
    
    public $killsTotal = 0;
    
    public $teamA;
    public $teamB;
    public $teamC;
    
    public $unassigned = array();
    
    
    public function __construct($id = 0) {
        
        $this->teamA = new BattleParty();
        $this->teamB = new BattleParty();
        $this->teamC = new BattleParty();
        
        if ($id > 0) {
            // Load from db
        }
    }
    
    
    public function import($importedKills) {
        
        if (count($importedKills) <= 0)
            return;
        
        foreach ($importedKills as $impKill) {
            $kill = Kill::fromImport($impKill);

            if ($kill != null) {

                // Per default, the victim is member of teamB
                $tgt = "teamB";
                
                // Oh no, its a loss for the owner corp :(
                if ($kill->victim->corporationID == BR_OWNERCORP_ID)
                    $tgt = "teamA";
                
                $this->$tgt->add($kill->victim);
                
                foreach ($kill->attackers as $attacker) {
                    $tgt = "teamB";
                    if ($attacker->corporationID == BR_OWNERCORP_ID)
                        $tgt = "teamA";
                    
                    $this->$tgt->add($attacker);
                }
            }
        }
        
        $this->teamA->sort();
        $this->teamB->sort();
        $this->teamC->sort();
        
        $this->teamA->updateDetails(array($this->teamB, $this->teamC));
        $this->teamB->updateDetails(array($this->teamA, $this->teamC));
        $this->teamC->updateDetails(array($this->teamA, $this->teamB));
        
        $this->killsTotal = count($this->teamA) + count($this->teamB) + count($this->teamC);
        
    }
    
    
    public function toJSON() {
        return '{' .
            '"type":"battle",' .
            '"killsTotal":' . $this->killsTotal . ',' .
            '"teamA":' . $this->teamA->toJSON() . ',' .
            '"teamB":' . $this->teamB->toJSON() . ',' .
            '"teamC":' . $this->teamC->toJSON() . //',' .
        '}';
    }
    
}