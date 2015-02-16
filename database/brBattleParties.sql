DROP TABLE IF EXISTS `brBattleParties`;
CREATE TABLE `brBattleParties` (
    `brBattlePartyID` int(11) NOT NULL AUTO_INCREMENT,
    `battleReportID` int(11) DEFAULT 0 NOT NULL,
    `brTeamName` text,
    PRIMARY KEY (`brBattlePartyID`),
    KEY `brBattleParties_IX_battleReport`(`battleReportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
