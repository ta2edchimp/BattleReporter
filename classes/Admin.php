<?php

class Admin {
	
	public static function refetchKillMailsForMissingLossValues() {
		
		if (!User::isAdmin()) {
			return array(
				"success" => false,
				"message" => "Administrator privileges are required to perform this action!"
			);
		}
		
		$db = Db::getInstance();
		
		$result = array(
			"success" => true
		);
		$missingCount = -1;
		$refetchCount = 0;
		
		$combatants = $db->query(
			"select c.*, ifnull(cc.corporationName, 'Unknown') as corporationName, ifnull(a.allianceName, '') as allianceName " .
			"from invGroups as g right outer join invTypes as t " .
				"on g.groupID = t.groupID " .
			"right outer join brCombatants as c " .
				"on t.typeID = c.shipTypeID " .
			"left outer join brCorporations as cc " .
				"on c.corporationID = cc.corporationID " .
			"left outer join brAlliances as a " .
				"on c.allianceID = a.allianceID " .         
			"where c.died = 1 and c.priceTag <= 0"
		);
		if ($combatants === NULL || $combatants === FALSE) {
			return array(
				"success" => false,
				"message" => "An error occurred when trying to collect the kills missing their loss values from database."
			);
		} else {
			$missingCount = count($combatants);
		}
		
		foreach ($combatants as $combatant) {
			
			$combatant = new Combatant($combatant);
			// Maybe track the count of omitted combatants?
			if ($combatant === null)
				continue;
			
			$killArray = KBFetch::fetchKill($combatant->killID);
			foreach ($killArray as $kill) {
				
				if ($kill->killID != $combatant->killID ||
					!isset($kill->zkb) || !isset($kill->zkb->totalValue))
					continue;
				
				$combatant->priceTag = floatVal($kill->zkb->totalValue);
				$combatant->save();
				
				$refetchCount++;
				
			}
		
		}
		
		if ($missingCount <= 0)
			return null;
		
		if ($refetchCount > 0) {
			$result["message"] = "$refetchCount kills completed by adding the missing loss values.";
		} else {
			$result = array(
				"success" => false,
				"message" => "No kill mail refetched in order to add missing loss values."
			);
		}
		
		return $result;
		
	}
	
}
