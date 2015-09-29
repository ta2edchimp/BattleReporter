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
			$result["message"] = "$refetchCount kills completed by adding the missing loss values. You should redo all the corresponding calculations!";
		} else {
			$result = array(
				"success" => false,
				"message" => "No kill mail refetched in order to add missing loss values."
			);
		}
		
		return $result;
		
	}

	public static function refetchKillMailsForMissingDamageValues() {
		
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
			"where c.died = 1 and c.brDamageReceived <= 0.0"
		);
		if ($combatants === NULL || $combatants === FALSE) {
			return array(
				"success" => false,
				"message" => "An error occurred when trying to collect the kills missing their damage values from database."
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
				
				// Set the received damage in db ...
				$combatant->brDamageReceived = intval($kill->victim->damageTaken);
				$combatant->save();

				// ... and dispatch the dealt damage values
				foreach ($kill->attackers as $attacker) {
					// Get the attacker's brCombatantID
					$attackerID = $db->single(
						'select brCombatantID ' .
						'from brCombatants ' .
						'where characterID = :characterID ' .
							'and shipTypeID = :shipTypeID ' .
							'and brBattlePartyID in (' .
								'select brBattlePartyID from brBattleParties where battleReportID = (' .
									'select battleReportID from brBattleParties where brBattlePartyID = :victimBattlePartyID limit 1' .
								')' .
							')',
						array(
							'characterID' => $attacker->characterID,
							'shipTypeID' => $attacker->shipTypeID,
							'victimBattlePartyID' => $combatant->brBattlePartyID
						)
					);
					// We screwed up here :/
					if ($attackerID === FALSE)
						continue;

					// Delete entries in table brDamageComposition of this attacker and victim
					$db->query(
						'delete from brDamageComposition ' .
						'where brReceivingCombatantID = :victimID and brDealingCombatantID = :attackerID',
						array(
							'victimID' => $combatant->brCombatantID,
							'attackerID' => $attackerID,
						)
					);

					// And insert it as a new entry
					$db->query(
						'insert into brDamageComposition ' .
						'(brReceivingCombatantID, brDealingCombatantID, brDamageDealt) ' .
						'values ' .
						'(:victimID, :attackerID, :dealtDamage)',
						array(
							'victimID' => $combatant->brCombatantID,
							'attackerID' => $attackerID,
							'dealtDamage' => $attacker->damageDone
						)
					);
				}
				
				$refetchCount++;
				
			}
		
		}
		
		if ($missingCount <= 0)
			return null;
		
		if ($refetchCount > 0) {
			$result["message"] = "$refetchCount kills completed by adding the missing damage values. You should redo all the corresponding calculations!";
		} else {
			$result = array(
				"success" => false,
				"message" => "No kill mail refetched in order to add missing damage values."
			);
		}
		
		return $result;
		
	}

	public static function repopulateStatistics() {

		$result = array(
			"error" => "Something went totally wrong. Sorry, but there's no further information available."
		);

		$resultPartialFailures = false;
		$resultStatisticsRepopulated = false;

		$db = \Db::getInstance();

		// Fetch all battles from database
		$battles = $db->query(
			'select * from brBattles'
		);

		if ($battles === FALSE) {
			return array(
				"error" => "Could not find any battles."
			);
		}

		// Loop through each battle ...
		foreach ($battles as $battle) {
			$partialResult = \Battle::updateStats($battle["battleReportID"]);
			if ($partialResult === true) {
				$resultStatisticsRepopulated = true;
			} else {
				$resultPartialFailures = true;
			}
		}

		if ($resultStatisticsRepopulated === true) {
			$result = array(
				"success" => "Statistic fields have been repopulated."
			);
		}

		if ($resultPartialFailures === true) {
			$results["error"] = "Some statistics could not be repopulated.";
		}

		return $result;

	}
	
}
