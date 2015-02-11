DROP TABLE IF EXISTS `brCombatants`;
CREATE TABLE `brCombatants` (
    `brCombatantID` int(11) NOT NULL AUTO_INCREMENT,
    `characterID` int(11) DEFAULT 0 NOT NULL,
    `characterName` text,
    `corporationID` int(11) DEFAULT 0 NOT NULL,
    `corporationName` text,
    `allianceID` int(11) DEFAULT 0 NOT NULL,
    `allianceName` text,
    `shipTypeID` int(11) DEFAULT 0 NOT NULL,
    `died` tinyint(1) DEFAULT 0 NOT NULL,
    `killID` text,
    `killTime` int(11) DEFAULT 0 NOT NULL,
    `priceTag` decimal(13,2) unsigned DEFAULT 0 NOT NULL,
    `brBattlePartyID` int(11) DEFAULT 0 NOT NULL,
    `brHidden` tinyint(1) DEFAULT 0 NOT NULL,
	`brManuallyAdded` tinyint(1) DEFAULT 0 NOT NULL,
	`brDeleted` tinyint(1) DEFAULT 0 NOT NULL,
    PRIMARY KEY (`brCombatantID`),
    KEY `brCombatants_IX_characterID` (`characterID`),
    KEY `brCombatants_IX_corporationID` (`corporationID`),
    KEY `brCombatants_IX_allianceID` (`allianceID`),
    KEY `brCombatants_IX_shipTypeID` (`shipTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;