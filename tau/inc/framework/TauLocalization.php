<?php

/**
 * 
 * @abstract Localization routines
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 02-dic-2015
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
class TauLocalization {

    /**
     * Get localized decimal separator 
     * @param string $countryIsoCode ISO-3166 ISO code A2 ( two characters )
     * @param string $langCode ISO language code
     * @return string Comma or dot symbol, as decimal separator
     */
    public static function getDecimalSeparator($countryIsoCode, $langCode =''){
        $country = mb_strtolower($countryIsoCode);
        $lang = mb_strtolower($langCode);
        
        $dotDecimalCountries = array(
            'au','bd','bw','io','bn','ca','cn','do','eg','gh',
            'gt','hn','hk','in','ie','il','jp','jo','ke','kr',
            'lb','lu','mo','my','mt','mx','mn','np','nz','ni',
            'pk','ps','pa','ph','sg','lk','ch','tw','tz','th',
            'ug','gb','us','zw','kp'
        );
        if($country == 'ca'){
            if($lang == 'en'){
                return '.';
            }else{
                return ',';
            }
        }
        if(in_array($country, $dotDecimalCountries)){
            return '.';
        }else{
            return ',';
        }
    }

}
