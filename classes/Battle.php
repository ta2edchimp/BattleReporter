<?php

class Battle {
    
    public $battleReportID;
    
    public $title;
    
    public $killsTotal = 0;
    
    public $solarSystemID = 0;
    public $solarSystemName = "";
    
    public $startTime = 0;
    public $endTime = 0;
    public $timeSpan = "";
    
    public $teamA;
    public $teamB;
    public $teamC;
    
    
    public function __construct($id = 0) {
        
        $this->teamA = new BattleParty("teamA");
        $this->teamB = new BattleParty("teamB");
        $this->teamC = new BattleParty("teamC");
        
        if ($id > 0) {
            // Load from db
        }
        
        $this->battleReportID = $id;
    }
    
    
    public function updateDetails() {
        if ($this->solarSystemID > 0)
            $this->solarSystemName = SolarSystem::getByID($this->solarSystemID);
        else
            $this->solarSystemName = "";
        
        $this->teamA->updateDetails(array($this->teamB, $this->teamC));
        $this->teamB->updateDetails(array($this->teamA, $this->teamC));
        $this->teamC->updateDetails(array($this->teamA, $this->teamB));
        
        $this->killsTotal = $this->teamA->losses + $this->teamB->losses + $this->teamC->losses;
        
        if ($this->startTime > 0 && $this->endTime > 0)
            $this->timeSpan = date("Y.m.d H:i", $this->startTime) . " - " . date("H:i", $this->endTime);
        else
            $this->timeSpan = "";
    }
    
    
    public function import($importedKills) {
        
        if (count($importedKills) <= 0)
            return;
        
        $earliestKillTime = 0;
        $latestKillTime = 0;
        
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
                
                if (isset($kill->killTime)) {
                    $killTime = $kill->killTime;
                    if ($earliestKillTime == 0 || $killTime < $earliestKillTime)
                        $earliestKillTime = $killTime;
                    if ($killTime > $latestKillTime)
                        $latestKillTime = $killTime;
                }
                
                if (isset($kill->solarSystemID))
                    $this->solarSystemID = $kill->solarSystemID;
                
            }
        }
        
        $this->startTime = $earliestKillTime;
        $this->endTime = $latestKillTime;
        
        $this->teamA->sort();
        $this->teamB->sort();
        $this->teamC->sort();
        
        $this->updateDetails();

    }
    
    
    public function toJSON() {
        return '{' .
            '"type":"battle",' .
            '"battleReportID":' . $this->battleReportID . ',' .
            '"title":"' . $this->title . '",' .
            '"killsTotal":' . $this->killsTotal . ',' .
            '"startTime":' . $this->startTime . ',' .
            '"endTime":' . $this->endTime . ',' .
            '"timeSpan":"' . $this->timeSpan . '",' .
            '"solarSystemName":"' . $this->solarSystemName . '",' .
            '"teamA":' . $this->teamA->toJSON() . ',' .
            '"teamB":' . $this->teamB->toJSON() . ',' .
            '"teamC":' . $this->teamC->toJSON() .
        '}';
    }
    
}