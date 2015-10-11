-- lucas@2015-10-08 18:28:37 

-- Update the table structure and base data

  alter table tau_localization change `name` `country` varchar(25);
-- &|6.28@tau&
  alter table tau_localization change `currency_symbol` `cur_symbol` varchar(25);
-- &|6.28@tau&
  alter table tau_localization add column cur_html varchar(25) not null default 'none' after `currency`;
-- &|6.28@tau&
  alter table tau_localization add column lang char(2) not null default 'en' after `country`;
-- &|6.28@tau&
  delete from tau_localization;
-- &|6.28@tau&
  -- Insert base config for all countries, modify after this

  insert into tau_localization(`id`,`country`,`lang`,`currency`,`cur_html`,`cur_symbol`,`weight`,`weight_si`,`length`,`length_si`,`date_format`,`date_first_day`) values
  (NULL,'AD','es','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'AE','en','AED','AED;','AED','Kg','1', 'm', '1', 'dd/mm/yy', 0), 
  (NULL,'AF','en','AFN','AFN;','AFN','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'AG','en','XCD','EC$','EC$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'AI','en','GBP','&pound;','£','lb','0.453', 'ft', '0.3048', 'yy/mm/dd', 0), 
  (NULL,'AL','en','ALL','ALL','ALL','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'AM','en','AMD','AMD','AMD','Kg','1', 'm', '1', 'dd/mm/yy', 0), 
  (NULL,'AN','en','ANG','NAƒ','NAƒ','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'AO','en','AOA','AOA','AOA','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'AR','es','ARS','$;','$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'AS','en','USD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'AT','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd.mm.yy', 1),
  (NULL,'AU','en','AUD','$','$','lb','0.453', 'ft', '0.3048', 'dd/mm/yy', 1), 
  (NULL,'AW','en','AWG','AWG','AWG','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'AX','en','EUR','&euro;','€','Kg','1', 'm', '1', 'yy/mm/dd', 0),
  (NULL,'AZ','en','AZN','ман','ман','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'BA','en','BAM','KM','KM','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'BB','en','BBD','$','$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'BD','en','BDT','Taka','Taka','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'BE','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd-mm-yy', 1),
  (NULL,'BF','en','XOF','FCFA','FCFA','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'BG','en','BGN','Лв','Лв','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'BH','en','BHD','.د.ب','.د.ب','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'BI','en','BIF','FBu','FBu','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'BJ','en','XOF','FCFA','FCFA','Kg','1', 'm', '1', 'yy/mm/dd', 0),
  (NULL,'BL','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'BM','en','BMD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'BN','en','BND','B$','B$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'BO','es','BOB','Bs','Bs','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'BR','en','BRS','R$','R$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'BS','en','BSD','B$','B$','lb','0.453', 'ft', '0.3048', 'mm/dd/yy', 1), 
  (NULL,'BT','en','BTN','Nu','Nu','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'BW','en','BWP','P','P','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'BY','en','BYR','Br','Br','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'BZ','en','BZD','$','$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'CA','en','CAD','C$','C$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'CD','en','CDF','F','F','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'CF','en','XAF','FCFA','FCFA','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'CG','en','XAF','FCFA','FCFA','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'CH','en','CHF','SFr','SFr','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'CI','en','XOF','FCFA','FCFA','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'CK','en','NZD','NZ$','NZ$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'CL','es','CLP','$','$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'CM','en','XAF','FCFA','FCFA','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'CN','en','CNY','&yen;','¥','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'CO','es','COP','$','$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'CR','es','CRC','₡','₡','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'CU','es','CUC','$','$','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'CV','en','CVE','Esc','Esc','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'CY','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'CZ','en','CZK','Kč','Kč','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'DE','en','EUR','&euro;','€','Kg','1', 'm', '1', 'yy.mm.dd', 1), 
  (NULL,'DJ','en','DJF','₣','₣','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'DK','en','DKK','kr','kr','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'DM','en','XCD','EC$','EC$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'DO','es','DOP','RD$','RD$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'DZ','en','DZD','دج','دج','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'EC','es','USD','$','$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'EE','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'EG','en','EGP','LE','LE','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'EH','en','DZD','DA','DA','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'EI','en','EUR','&euro;','€','Kg','1', 'm', '1', 'yy/mm/dd', 0),
  (NULL,'ER','en','ERN','Nfk','Nfk','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'ES','es','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'ET','en','ETB','Br','Br','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'FI','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'FJ','en','FJD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'FK','en','FKP','FK£','FK£','lb','0.453', 'ft', '0.3048', 'yy/mm/dd', 0),  
  (NULL,'FM','en','USD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'FO','en','DKK','kr','kr','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'FR','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'GA','en','XAF','FCFA','FCFA','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'GB','en','GBP','&pound;','£','lb','0.453', 'ft', '0.3048', 'yy/mm/dd', 0), 
  (NULL,'GD','en','XCD','EC$','EC$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'GE','en','GEL','GEL','GEL','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'GF','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'GH','en','GHS','GH₵','GH₵','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'GI','en','GIP','&pound;','£','lb','0.453', 'ft', '0.3048', 'yy/mm/dd', 0),  
  (NULL,'GL','en','DKK','kr','kr','Kg','1', 'm', '1', 'dd/mm/yy', 1),  
  (NULL,'GM','en','GMD','D','D','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'GN','en','GNF','FG','FG','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'GP','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'GQ','es','XAF','FCFA','FCFA','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'GR','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'GT','es','GTQ','Q','Q','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'GU','en','USD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'GW','en','XOF','FCFA','FCFA','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'GY','en','GYD','$','$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'HK','en','HKD','HK$','HK$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'HN','es','HNL','L','L','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'HR','en','HRK','kn','kn','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'HT','en','HTG','G','G','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'HU','en','HUF','Ft','Ft','Kg','1', 'm', '1', 'yy/mm/dd', 1), 
  (NULL,'ID','en','IDR','Rp','Rp','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'IE','en','EUR','&euro;','€','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'IL','en','ILS','&#x20aa;','₪','Kg','1', 'm', '1', 'dd/mm/yy', 0), 
  (NULL,'IN','en','INR','&#x20B9;','&#x20B9;','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'IQ','en','IQD','ع.د','ع.د','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'IR','en','IRR','IRR','IRR','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'IS','en','ISK','Íkr','Íkr','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'IT','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'JM','en','JMD','$','$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'JO','en','JOD','JD','JD','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'JP','en','JPY','&yen;','¥','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'KE','en','KES','KSh','KSh','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'KG','en','KGS','COM','COM','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'KH','en','KHR','KHR','KHR','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'KI','en','AUD','$','$','lb','0.453', 'ft', '0.3048', 'dd/mm/yy', 1),  
  (NULL,'KM','en','KMF','&#x20a3;','&#x20a3;','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'KN','en','XCD','EC$','EC$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'KP','en','KPW','&#x20a9;','&#x20a9;','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'KR','en','KRW','&#x20a9;','&#x20a9;','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'KW','en','KWD','د.ك','د.ك','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'KY','en','KYD','CI$','CI$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'KZ','en','KZT','T','T','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'LA','en','LAK','₭N','₭N','Kg','1', 'm', '1', 'dd/mm/yy', 0), 
  (NULL,'LB','en','LBP','ل.ل','ل.ل','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'LC','en','XCD','EC$','EC$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'LI','en','CHF','SFr','SFr','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'LK','en','LKR','Rs','Rs','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'LR','en','LRD','L$','L$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'LS','en','LSL','L','L','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'LT','en','EUR','&euro;','€','Kg','1', 'm', '1', 'yy/mm/dd', 1),
  (NULL,'LU','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'LV','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'LY','en','LYD','LD','LD','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'MA','en','MAD','درهم','درهم','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'MC','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'MD','en','MDL','MDL','MDL','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'ME','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'MF','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'MG','en','MGA','Fr','Fr','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'MH','en','USD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'MK','en','MKD','Ден','Ден','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'ML','en','XOF','FCFA','FCFA','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'MM','en','MMK','K','K','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'MN','en','MNT','&#x20ae;','&#x20ae;','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'MO','en','MOP','MOP$','MOP$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'MP','en','USD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'MQ','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'MR','en','MRO','UM','UM','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'MS','en','XCD','EC$','EC$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'MT','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'MU','en','MUR','Rp','Rp','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'MV','en','MVR','Rf','Rf','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'MW','en','MWK','MK','MK','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'MX','es','MXN','$','$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'MY','en','MYR','RM','RM','Kg','1', 'm', '1', 'dd.mm.yy', 1), 
  (NULL,'MZ','en','MZN','MTn','MTn','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'NA','en','NAD','N$','N$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'NC','en','XPF','F','F','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'NE','en','XOF','FCFA','FCFA','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'NG','en','NGN','₦','₦','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'NI','es','NIO','C$','C$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'NL','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'NO','en','NOK','kr','kr','Kg','1', 'm', '1', 'dd.mm.yy', 0), 
  (NULL,'NP','en','NPR','Rs','Rs','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'NR','en','AUD','$','$','lb','0.453', 'ft', '0.3048', 'dd/mm/yy', 1), 
  (NULL,'NZ','en','NZD','NZ$','NZ$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'OM','en','OMR','ر.ع.','ر.ع.','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'PA','es','USD','$','$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'PE','es','PEN','S/.','S/.','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'PF','en','XPF','F','F','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'PG','en','PGK','PGK','PGK','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'PH','en','PHP','&#x20b1;','₱','Kg','1', 'm', '1', 'mm/dd/yy', 0), 
  (NULL,'PK','en','PKR','Rs','Rs','Kg','1', 'm', '1', 'dd/mm/yy', 0), 
  (NULL,'PL','en','PLN','zł','zł','Kg','1', 'm', '1', 'dd.mm.yy', 1), 
  (NULL,'PM','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'PR','es','USD','$','$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'PS','en','ILS','&#x20aa;','&#x20aa;','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'PT','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'PW','en','USD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'PY','es','PYG','&#x20b2;','₲','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'QA','en','QAR','QR',' ر.ق','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'RE','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'RO','en','RON','RON','RON','Kg','1', 'm', '1', 'dd.mm.yy', 1), 
  (NULL,'RS','en','RSD','дин.','дин.','Kg','1', 'm', '1', 'dd.mm.yy', 1), 
  (NULL,'RU','en','RUB','руб','руб','Kg','1', 'm', '1', 'dd.mm.yy', 1), 
  (NULL,'RW','en','RWF','RF','RF','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'SA','en','SAR','SR','SR','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'SB','en','SBD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'SC','en','SCR','SRe','SRe','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'SD','en','SDG','SD£','SD£','Kg','1', 'm', '1', 'yy/mm/dd', 0),
  (NULL,'SE','en','SEK','kr','kr','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'SG','en','SGD','S$','S$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'SI','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'SK','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'SL','en','SLL','Le','Le','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'SM','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'SN','en','XOF','FCFA','FCFA','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'SO','en','SOS','Sh','Sh','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'SR','en','SRD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'SS','en','SSD','SSD','SSD','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'ST','en','STD','Db','Db','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'SV','es','USD','$','$','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'SY','en','SYP','S£','S£','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'SZ','en','SZL','SZL','SZL','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'TC','en','USD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'TD','en','XAF','FCFA','FCFA','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'TG','en','XOF','FCFA','FCFA','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'TH','en','THB','฿','฿','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'TJ','en','TJS','TJS','TJS','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'TL','en','USD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'TM','en','TMM','ман.','ман.','Kg','1', 'm', '1', 'dd.mm.yy', 1), 
  (NULL,'TN','en','TND','DT','DT','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'TO','en','TOP','TOP','TOP','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'TR','en','TRY','TL','TL','Kg','1', 'm', '1', 'dd.mm.yy', 1), 
  (NULL,'TT','en','TTD','TT$','TT$','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'TV','en','AUD','$','$','lb','0.453', 'ft', '0.3048', 'dd/mm/yy', 1),  
  (NULL,'TW','en','TWD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'TZ','en','TZS','TZS','nones','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'UA','en','UAH','грн','грн','Kg','1', 'm', '1', 'dd.mm.yy', 1), 
  (NULL,'UG','en','UGX','USh','USh','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'US','en','USD','$','$','lb','0.453', 'ft', '0.3048', 'yy/mm/dd', 0), 
  (NULL,'UY','es','UYU','$u','$u','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'UZ','en','UZS','UZS','UZS','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'VA','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'VC','en','XCD','EC$','EC$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'VE','es','VEF','Bs.','Bs.','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'VG','en','USD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'VI','en','USD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'VN','en','VND','₫','₫','Kg','1', 'm', '1', 'dd/mm/yy', 1), 
  (NULL,'VU','en','VUV','Vt','Vt','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'WF','en','XPF','F','F','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'WS','en','WST','WS$','WS$','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'YE','en','YER','YER','YER','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'YT','en','EUR','&euro;','€','Kg','1', 'm', '1', 'dd/mm/yy', 1),
  (NULL,'ZA','en','ZAR','R','R','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'ZM','en','ZMW','ZMW','ZMW','Kg','1', 'm', '1', 'yy/mm/dd', 0), 
  (NULL,'ZW','en','USD','$','$','Kg','1', 'm', '1', 'yy/mm/dd', 0);

-- &|6.28@tau&

alter table tau_localization drop column date_format, drop column date_first_day;