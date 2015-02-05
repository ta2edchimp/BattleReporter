CREATE TABLE `brBattles` (
    `battleReportID` int(11) NOT NULL AUTO_INCREMENT,
    `brTitle` text,
    `brStartTime` int(11) DEFAULT 0 NOT NULL,
    `brEndTime` int(11) DEFAULT 0 NOT NULL,
    `solarSystemID` int(11) DEFAULT 0 NOT NULL,
    `brPublished` tinyint(1) DEFAULT 0 NOT NULL,
    PRIMARY KEY (`battleReportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;