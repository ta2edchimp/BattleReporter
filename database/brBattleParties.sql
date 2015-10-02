DROP TABLE IF EXISTS `brBattleParties`;
CREATE TABLE `brBattleParties` (
  `brBattlePartyID` int(11) NOT NULL AUTO_INCREMENT,
  `battleReportID` int(11) DEFAULT 0 NOT NULL,
  `brTeamName` text,
  `brDamageDealt` int(11) NOT NULL DEFAULT '0',
  `brDamageReceived` int(11) NOT NULL DEFAULT '0',
  `brIskDestroyed` decimal(13,2) NOT NULL DEFAULT '0.00',
  `brIskLost` decimal(13,2) NOT NULL DEFAULT '0.00',
  `brEfficiency` decimal(5,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`brBattlePartyID`),
  KEY `brBattleParties_IX_battleReport` (`battleReportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
