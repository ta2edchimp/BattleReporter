DROP TABLE IF EXISTS `brCombatants`;
CREATE TABLE `brCombatants` (
  `brCombatantID` int(11) NOT NULL AUTO_INCREMENT,
  `characterID` int(11) NOT NULL DEFAULT '0',
  `characterName` text,
  `corporationID` int(11) NOT NULL DEFAULT '0',
  `allianceID` int(11) NOT NULL DEFAULT '0',
  `brHidden` tinyint(1) NOT NULL DEFAULT '0',
  `brBattlePartyID` int(11) NOT NULL DEFAULT '0',
  `shipTypeID` int(11) NOT NULL DEFAULT '0',
  `died` tinyint(1) NOT NULL DEFAULT '0',
  `killID` text,
  `killTime` int(11) NOT NULL DEFAULT '0',
  `priceTag` decimal(13,2) unsigned NOT NULL DEFAULT '0.00',
  `brManuallyAdded` tinyint(1) DEFAULT '0',
  `brDeleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`brCombatantID`),
  KEY `brCombatants_IX_characterID` (`characterID`),
  KEY `brCombatants_IX_corporationID` (`corporationID`),
  KEY `brCombatants_IX_allianceID` (`allianceID`),
  KEY `brCombatants_IX_shipTypeID` (`shipTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
