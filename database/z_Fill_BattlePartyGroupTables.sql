# Delete existing groups
DELETE FROM `brBattlePartyGroups` WHERE 1;

# Insert default groups
INSERT INTO `brBattlePartyGroups`
(`battlePartyGroupName`, `battlePartyGroupOrderKey`)
VALUES
('Capital', 200), ('Logistics', 100), ('Ewar', 50);

# Delete existing associations
DELETE FROM `brBattlePartyGroupShipTypes` WHERE 1;

# Insert Capitals
INSERT INTO `brBattlePartyGroupShipTypes` (`shipTypeID`, `brBattlePartyGroupID`)
SELECT `typeID`AS `shipTypeID`,
	(SELECT `battlePartyGroupID` FROM `brBattlePartyGroups` WHERE `battlePartyGroupName` = 'Capital' LIMIT 1) AS `brBattlePartyGroupID`
FROM `invTypes`
WHERE `groupID` IN (SELECT `groupID` FROM `invGroups` WHERE `groupName` = 'Carrier' OR `groupName` = 'Supercarrier' OR `groupName` = 'Capital Industrial Ship' OR `groupName` = 'Titan' OR `groupName` = 'Dreadnought' OR `groupName` = 'Jump Freighter')
ORDER BY `mass` DESC, `typeName` ASC;

# Insert T1 Logi Frigs, T1 Logi Cruisers and T2 Logi Cruisers into their respective group
INSERT INTO `brBattlePartyGroupShipTypes` (`shipTypeID`, `brBattlePartyGroupID`)
SELECT `typeID` AS `shipTypeID`,
	(SELECT `battlePartyGroupID` FROM `brBattlePartyGroups` WHERE `battlePartyGroupName` = 'Logistics' LIMIT 1) AS `brBattlePartyGroupID`
FROM `invTypes`
WHERE `typeName` IN ('Inquisitor', 'Bantam', 'Navitas', 'Burst', 'Deacon', 'Kirin', 'Thalia', 'Scalpel', 'Augoror', 'Osprey', 'Exequror', 'Scythe', 'Guardian', 'Basilisk', 'Oneiros', 'Scimitar')
ORDER BY `mass` DESC, `typeName` ASC;

# Insert ships into Ewar group
INSERT INTO `brBattlePartyGroupShipTypes` (`shipTypeID`, `brBattlePartyGroupID`)
SELECT `typeID` AS `shipTypeID`,
	(SELECT `battlePartyGroupID` FROM `brBattlePartyGroups` WHERE `battlePartyGroupName` = 'Ewar' LIMIT 1) AS `brBattlePartyGroupID`
FROM `invTypes`
WHERE `typeName` IN ('Widow', 'Falcon', 'Rook', 'Kitsune', 'Scorpion', 'Blackbird', 'Griffin', 'Pilgrim', 'Curse', 'Sentinel', 'Arbitrator', 'Crucifier', 'Arazu', 'Lachesis', 'Keres', 'Celestis', 'Maulus', 'Golem', 'Rapier', 'Huginn', 'Hyena', 'Bellicose', 'Vigil', 'Crucifier Navy Issue', 'Vigil Fleet Issue', 'Griffin Navy Issue', 'Maulus Navy Issue')
ORDER BY `mass` DESC, `typeName` ASC;
