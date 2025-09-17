 CREATE TABLE `temperature` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Date` date NOT NULL,
  `Min` int NOT NULL,
  `MinTime` time NOT NULL,
  `Max` int NOT NULL,
  `MaxTime` time NOT NULL,
  PRIMARY KEY (`Id`)
) ;

 CREATE TABLE `wind_speed` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Date` date NOT NULL,
  `Min` int NOT NULL,
  `MinTime` time NOT NULL,
  `Max` int NOT NULL,
  `MaxTime` time NOT NULL,
  `Average` int NOT NULL,
  `MaxAvg` int NOT NULL,
  PRIMARY KEY (`Id`)
) ;

 CREATE TABLE `rainfall` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Date` date NOT NULL,
  `Total` int NOT NULL,
  PRIMARY KEY (`Id`)
) ;