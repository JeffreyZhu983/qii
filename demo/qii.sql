CREATE DATABASE IF NOT EXISTS qii DEFAULT CHARSET utf8 COLLATE utf8_general_ci;

use qii;

CREATE TABLE IF NOT EXISTS `database_form_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `form_uniqid` char(41) NOT NULL,
  `form_value` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `add_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `database_form_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `uniqid` char(41) NOT NULL,
  `mask` char(32) NOT NULL,
  `form` text,
  `form_serialize` text,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `add_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqid_UNIQUE` (`uniqid`),
  UNIQUE KEY `mask_UNIQUE` (`mask`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;