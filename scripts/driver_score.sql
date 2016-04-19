USE `torque`;

DROP TABLE IF EXISTS `speed`;
CREATE TABLE `speed` (
  `session` varchar(15) NOT NULL,
  `id` varchar(32) NOT NULL,
  `score` float NOT NULL DEFAULT '0',
    `time` varchar(15) NOT NULL DEFAULT '0',
    `OSt` varchar(10) NOT NULL DEFAULT '0',
    `OSp` varchar(10) NOT NULL DEFAULT '0',
    `OSa` varchar(10) NOT NULL DEFAULT '0',


  KEY `session` (`session`,`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `accel`;
CREATE TABLE `accel` (
  `session` varchar(15) NOT NULL,
  `id` varchar(32) NOT NULL,
  `score` float NOT NULL DEFAULT '0',
    `time` varchar(15) NOT NULL DEFAULT '0',
    `aggEvent` varchar(10) NOT NULL DEFAULT '0',
    `modEvent` varchar(10) NOT NULL DEFAULT '0',
        `GAp` varchar(10) NOT NULL DEFAULT '0',




  KEY `session` (`session`,`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `break`;
CREATE TABLE `break` (
  `session` varchar(15) NOT NULL,
  `id` varchar(32) NOT NULL,
  `score` float NOT NULL DEFAULT '0',
    `time` varchar(15) NOT NULL DEFAULT '0',
       `aggEvent` varchar(10) NOT NULL DEFAULT '0',
    `modEvent` varchar(10) NOT NULL DEFAULT '0',
        `GAn` varchar(10) NOT NULL DEFAULT '0',


  KEY `session` (`session`,`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `angle`;
CREATE TABLE `angle` (
  `session` varchar(15) NOT NULL,
  `id` varchar(32) NOT NULL,
  `score` float NOT NULL DEFAULT '0',
    `time` varchar(15) NOT NULL DEFAULT '0',
       `aggEvent` varchar(10) NOT NULL DEFAULT '0',
    `modEvent` varchar(10) NOT NULL DEFAULT '0',
    `BRp` varchar(10) NOT NULL DEFAULT '0',



  KEY `session` (`session`,`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `overall`;
CREATE TABLE `overall` (
  `session` varchar(15) NOT NULL,
  `id` varchar(32) NOT NULL,
  `total_score` float NOT NULL DEFAULT '0',
    `speed` float NOT NULL DEFAULT '0',
    `accel` float NOT NULL DEFAULT '0',
    `break` float NOT NULL DEFAULT '0',
    `angle` float NOT NULL DEFAULT '0',
  KEY `session` (`session`,`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;