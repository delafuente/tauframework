-- lucas@2014-05-02 15:05:03


CREATE TABLE IF NOT EXISTS `migrates` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `author` varchar(50) NOT NULL,
 `created` datetime NOT NULL,
 `applied` datetime NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='all applied migrates';

-- &|6.28@tau&

INSERT INTO `migrates` (`id`, `name`, `author`, `created`, `applied`) VALUES
(NULL, '20150308_214800_create_table_migrates', 'lucas-nuevo', '2015-03-10 00:16:14', '0000-00-00 00:00:00');