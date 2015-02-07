CREATE TABLE `brUsers` (
    `userID` int(11) NOT NULL AUTO_INCREMENT,
    `userName` varchar(128) NOT NULL,
    `password` varchar(64) NOT NULL,
    `email` varchar(128) NOT NULL,
    `createdTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `characterID` int(16) DEFAULT NULL,
    `corporationID` int(16) DEFAULT NULL,
    `allianceID` int(16) DEFAULT NULL,
    `isAdmin` tinyint(1) DEFAULT 0 NOT NULL,
    PRIMARY KEY (`userID`),
    UNIQUE KEY `userName` (`userName`),
    KEY `brUsers_IX_login` (`userName`, `password`),
    KEY `brUsers_IX_characterID` (`characterID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;