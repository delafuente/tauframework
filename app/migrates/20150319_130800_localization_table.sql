-- lucas-nuevo@2015-03-19 13:09:25

CREATE TABLE IF NOT EXISTS `tau_localization` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `currency` varchar(25) NOT NULL,
  `currency_symbol` varchar(25) NOT NULL,
  `weight` varchar(25) NOT NULL,
  `weight_si` float NOT NULL COMMENT 'kg multiplier',
  `length` varchar(25) NOT NULL,
  `length_si` float NOT NULL COMMENT 'meters multiplier',
  `date_format` varchar(25) NOT NULL,
  `date_first_day` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- &|6.28@tau&

INSERT INTO `tau_localization` (`id`, `name`, `currency`, `currency_symbol`, `weight`, `weight_si`, `length`, `length_si`, `date_format`, `date_first_day`) VALUES
(NULL, 'EU', 'EUR', '&euro;', 'Kg', 1, 'm', 1, 'yy/mm/dd', 0),
(NULL, 'GB', 'POUND', '&pound;', 'lb', 0.453, 'ft', 0.3048, 'yy/mm/dd', 0),
(NULL, 'ES', 'EUR', '&euro;', 'Kg', 1, 'm', 1, 'dd/mm/yy', 1),
(NULL, 'EN', 'DOLLAR', '$', 'lb', '0.453', 'ft', '0.3048', 'yy/mm/dd', 0);
