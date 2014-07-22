SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE TABLE IF NOT EXISTS `person` (
  `index` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `arbeitgeber` varchar(20) NOT NULL,
  PRIMARY KEY (`index`),
  KEY `arbeitgeber` (`arbeitgeber`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;
INSERT INTO `person` (`index`, `name`, `arbeitgeber`) VALUES
(1, 'Dieter', ''),
(2, 'Werner', ''),
(3, 'Peter', 'person 1'),
(4, 'Otto', '');
