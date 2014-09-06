SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Structure of table `tau_change_password`
--

CREATE TABLE IF NOT EXISTS `tau_change_password` (
  `ui_id_change_password` int(10) unsigned NOT NULL auto_increment,
  `ui_id_user` int(10) unsigned NOT NULL,
  `bo_active` tinyint(1) NOT NULL,
  `vc_key` char(40) collate utf8_general_ci NOT NULL,
  `dt_created` datetime NOT NULL,
  `ts_lastmod` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ui_id_change_password`),
  UNIQUE KEY `vc_key` (`vc_key`),
  KEY `ui_id_user` (`ui_id_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='key for password lost confirmation' ;

-- --------------------------------------------------------

--
-- Structure of table `tau_friendship`
--

CREATE TABLE IF NOT EXISTS `tau_friendship` (
  `id_rel` int(10) unsigned NOT NULL auto_increment,
  `id_a` int(10) unsigned NOT NULL,
  `id_b` int(10) unsigned NOT NULL,
  `relation` enum('a_to_b','b_to_a','friends','exfriends_a','exfriends_b') collate utf8_general_ci NOT NULL,
  `dt_created` datetime NOT NULL,
  `ts_lastmod` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_rel`),
  KEY `id_a` (`id_a`,`id_b`,`relation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure of table `tau_log_session`
--

CREATE TABLE IF NOT EXISTS `tau_log_session` (
  `ui_id_log_session` int(10) unsigned NOT NULL auto_increment,
  `vc_action` varchar(255) collate utf8_general_ci NOT NULL,
  `vc_username` varchar(255) collate utf8_general_ci NOT NULL,
  `ui_id_author` int(11) NOT NULL,
  `cts_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `vc_ip` varchar(255) collate utf8_general_ci default NULL,
  `vc_os` varchar(255) collate utf8_general_ci default NULL,
  `vc_browser` varchar(255) collate utf8_general_ci NOT NULL,
  `vc_data` varchar(5000) collate utf8_general_ci NOT NULL,
  PRIMARY KEY  (`ui_id_log_session`),
  KEY `vc_action` (`vc_action`,`vc_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Table for log user actions';

-- --------------------------------------------------------

--
-- Structure of table `tau_user`
--

CREATE TABLE IF NOT EXISTS `tau_user` (
  `ui_id_user` int(10) unsigned NOT NULL auto_increment,
  `vc_username` char(50) collate utf8_general_ci NOT NULL,
  `pa_passwd` char(40) collate utf8_general_ci NOT NULL,
  `vc_role` varchar(45) collate utf8_general_ci NOT NULL,
  `vc_name` varchar(255) collate utf8_general_ci NOT NULL,
  `vc_surname` varchar(255) collate utf8_general_ci NOT NULL,
  `bo_active` tinyint(1) NOT NULL,
  `vc_email` varchar(255) collate utf8_general_ci NOT NULL,
  `dt_date_register` datetime NOT NULL,
  `ts_lastmod` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `image` varchar(255) collate utf8_general_ci default NULL,
  `dt_lastaccess` datetime NOT NULL,
  PRIMARY KEY  (`ui_id_user`),
  UNIQUE KEY `vc_email` (`vc_email`),
  KEY `vc_username` (`vc_username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure of table `tau_pre_register`
--

CREATE TABLE IF NOT EXISTS `tau_pre_register` (
  `ui_id_pre_register` int(10) unsigned NOT NULL auto_increment,
  `vc_username` varchar(255) collate utf8_general_ci NOT NULL,
  `pa_passwd` varchar(255) collate utf8_general_ci NOT NULL,
  `vc_name` varchar(255) collate utf8_general_ci NOT NULL,
  `vc_surname` varchar(255) collate utf8_general_ci NOT NULL,
  `vc_email` varchar(255) collate utf8_general_ci NOT NULL,
  `cts_date_ins` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `vc_confirm_code` varchar(255) collate utf8_general_ci NOT NULL,
  `bo_registered_yet` tinyint(1) NOT NULL,
  PRIMARY KEY  (`ui_id_pre_register`),
  KEY `vc_confirm_code` (`vc_confirm_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

-- --------------------------------------------------------

--
-- Structure of table `tau_status`
--

CREATE TABLE IF NOT EXISTS `tau_status` (
  `type_status` char(7) character set utf8 collate utf8_general_ci NOT NULL,
  `tot_users` int(10) unsigned NOT NULL,
  `tot_users_active` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`type_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

-- --------------------------------------------------------

--
-- Structure of table `tau_gallery`
--

CREATE TABLE IF NOT EXISTS `tau_gallery` (
`id_gallery` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`ui_id_user` INT UNSIGNED NOT NULL ,
`name` VARCHAR( 100 ) NOT NULL ,
`is_private` BOOLEAN NOT NULL ,
`passwd` VARCHAR( 40 ) NOT NULL ,
`num_photos` SMALLINT NOT NULL ,
`ts_lastmod` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- --------------------------------------------------------

ALTER TABLE  `tau_gallery` ADD FOREIGN KEY (  `ui_id_user` ) REFERENCES  
`tau_user` (`ui_id_user`) 
ON DELETE CASCADE ON UPDATE CASCADE ;

-- --------------------------------------------------------

--
-- Structure of table `tau_photos`
--

CREATE TABLE IF NOT EXISTS `tau_photos` (
`id_photo` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`id_gallery` INT UNSIGNED NOT NULL ,
`ui_id_user` INT UNSIGNED NOT NULL ,
`path` VARCHAR( 255 ) NOT NULL ,
`ts_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
INDEX (  `id_gallery` ,  `ui_id_user` )
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- --------------------------------------------------------

ALTER TABLE  `tau_photos` ADD FOREIGN KEY (  `id_gallery` ) REFERENCES `tau_gallery` (
`id_gallery`
) ON DELETE CASCADE ON UPDATE CASCADE ;

-- --------------------------------------------------------
--
-- Structure of table `tau`
--

CREATE TABLE IF NOT EXISTS `tau` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `tau_key` varchar(255) NOT NULL,
 `tau_value` varchar(255) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `tau_key` (`tau_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Main tau data';

-- --------------------------------------------------------
--
-- Structure of table `tau_translations`
--

CREATE TABLE IF NOT EXISTS `tau_translations` (
  `id_trans` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lang` char(5) NOT NULL,
  `t_group` varchar(100) NOT NULL,
  `item` varchar(100) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id_trans`),
  UNIQUE KEY `lang` (`lang`,`t_group`,`item`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

