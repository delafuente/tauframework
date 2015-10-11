-- lucas@2015-10-08 18:28:37 

drop table tau_localization;

-- &|6.28@tau&

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- &|6.28@tau&

INSERT INTO `tau_localization` (`id`, `name`, `currency`, `currency_symbol`, `weight`, `weight_si`, `length`, `length_si`, `date_format`, `date_first_day`) VALUES
(4, 'EU', 'EUR', '&euro;', 'Kg', 1, 'm', 1, 'yy/mm/dd', 0),
(5, 'GB', 'POUND', '&pound;', 'lb', 0.453, 'ft', 0.3048, 'yy/mm/dd', 0),
(6, 'ES', 'EUR', '&euro;', 'Kg', 1, 'm', 1, 'dd/mm/yy', 1),
(7, 'EN', 'DOLLAR', '$', 'lb', 0.453, 'ft', 0.3048, 'yy/mm/dd', 0);