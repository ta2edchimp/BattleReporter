<?php

class Kill {
    
    public $victim;
    
    public $attackers = array();
    
    
    public function __construct($victim = null, $attackers = null) {
        if ($victim != null)
            $this->victim = $victim;
        if ($attackers != null) {
            foreach ($attackers as $attacker)
                $this->addAttacker($attacker);
        }
        // ...
    }
    
    
    public function setCombatant($combatant) {
        $this->victim = $combatant;
    }
    
    public function addAttacker($attacker) {
        if ($this->getAttacker($attacker) == null)
            $this->attackers[] = $attacker;
    }
    
    public function getAttacker($attacker) {
        $id = $attacker->characterID;
        foreach ($this->attackers as $atk) {
            if ($atk->characterID == $id)
                return $atk;
        }
        return null;
    }
    
    
    public function isCorpLoss($corpID) {
        return ($this->victim->corporationID == $corpID);
    }
    
    public function isCorpKill($corpID) {
        foreach ($this->attackers as $attacker) {
            if ($attacker->corporationID == $corpID)
                return true;
        }
        return false;
    }
    
    
    public static function fromImport($kill = "") {
        if (empty($kill))
            return null;
        
        if (!isset($kill->victim) || !isset($kill->attackers) || !isset($kill->killID))
            return null;
        
        $victim = new Combatant($kill->victim, $kill->killID);
        
        if (isset($kill->zkb) && isset($kill->zkb->totalValue)) {
            $victim->priceTag = floatVal($kill->zkb->totalValue);
        }
        
        $attackers = array();
        foreach ($kill->attackers as $atk) {
            $attacker = new Combatant($atk);
            if ($atk != null)
                $attackers[] = $attacker;
        }
        
        if ($victim != null && count($attackers) > 0)
            return new Kill($victim, $attackers);
        
        return null;
    }
    
    
}