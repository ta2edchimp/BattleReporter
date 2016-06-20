DROP TABLE IF EXISTS `brUsersSessions`;
CREATE TABLE `brUsersSessions` (
    `userID` int(11) NOT NULL,
    `sessionHash` varchar(192) NOT NULL,
    `createdTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `validUntil` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01',
    `userAgent` text NOT NULL,
    `ip` varchar(16) NOT NULL,
    UNIQUE KEY `sessionHash` (`sessionHash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
