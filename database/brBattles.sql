DROP TABLE IF EXISTS `brBattles`;
CREATE TABLE `brBattles` (
    `battleReportID` int(11) NOT NULL AUTO_INCREMENT,
    `brTitle` text,
    `brStartTime` int(11) DEFAULT 0 NOT NULL,
    `brEndTime` int(11) DEFAULT 0 NOT NULL,
    `solarSystemID` int(11) DEFAULT 0 NOT NULL,
    `brPublished` tinyint(1) DEFAULT 0 NOT NULL,
	`brCreatorUserID` int(11) DEFAULT 0 NOT NULL,
	`brCreateTime` int(11) DEFAULT 0 NOT NULL,
	`brDeleteUserID` int(11) DEFAULT 0 NOT NULL,
	`brDeleteTime` int(11) DEFAULT NULL,
    PRIMARY KEY (`battleReportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;