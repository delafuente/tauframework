<?php
/**
 * Calculate distances between places
 * Needs postal codes table with lat/long coordinates
 * @abstract Calculate distances between places
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 26-nov-2013
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
require_once('../config.php');
require_once('../DataManager.php');

class DistanceCalculation {

    protected $db;

    public function __construct() {

        $this->db = DataManager::getInstance('postalcodes');
    }
    /**
     * Get km/miles distance between two coordinates.
     * @param String $orISO Country ISO of two letters, like es,fr,de
     * @param String $orCity Origin city name
     * @param String $orPC Postal code of origin
     * @param String $destISO Country ISO of two letters, like es,fr,de
     * @param String $destCity Destination city name
     * @param String $destPC Postal code of destination
     * @param boolean $miles If true, will return distance in miles, or in Km otherwise
     * @param int $precission The rounding precission we'll need, the number of decimal positions
     * @param boolean $getRoadDistance If false, will get straight ( parabolic ) line distance, if true, some approximation of road distance.
     * @return float The distance in miles or Km, or -1 if some data don't fit ( Postal code, city name, etc ).
     */
    public function getDistance($orISO,$orCity,$orPC,$destISO,$destCity,$destPC,$miles=false,$precission=1,$getRoadDistance=true){
        
        
        $orISO = strtolower($orISO);
        $destISO = strtolower($destISO);
        
        $res_o = $this->db->getRow("select Lat,Lng from pc_$orISO where codpostal='$orPC' and poblacion ='$orCity' limit 1");
        $res_d = $this->db->getRow("select Lat,Lng from pc_$destISO where codpostal='$destPC' and poblacion ='$destCity' limit 1");
        
        if(!$res_o || !$res_d){ return -1; }
        
        $lat1 = $res_o['Lat'];
	$lon1 = $res_o['Lng'];
        $lat2 = $res_d['Lat'];
	$lon2 = $res_d['Lng'];
        
        $d = round($this->distance($lat1,$lon1,$lat2,$lon2),$precission);
        
        $roadDistance = round($d * 1.19,$precission);
        
        if($getRoadDistance){
            return $roadDistance;
        }else{
            return $d;
        }
        
    }
    
    protected function distance($lat1, $lng1, $lat2, $lng2, $miles = false) {
        $pi80 = M_PI / 180;
        $lat1 *= $pi80;
        $lng1 *= $pi80;
        $lat2 *= $pi80;
        $lng2 *= $pi80;

        $r = 6372.797; // mean radius of Earth in km
        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;
        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $km = $r * $c;

        return ($miles ? ($km * 0.621371192) : $km);
    }

}

?>
