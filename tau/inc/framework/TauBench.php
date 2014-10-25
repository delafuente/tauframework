<?php

/**
 * 
 * @abstract Class to benchmark page execution by stages
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 21-mar-2011
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

class TauBench {
    
    private $benchMode;
    private $initTime; //Float
    private $stages;
    private $firstCall;
    private $lastTime;
    /**
     * Creates new TimeBench object
     * @param boolean $overWriteBenchMode If true, don't use BENCH_MODE but newBenchMode param
     * @param boolean $newBenchMode If overWriteBenchMode is true, then this value is used
 
     */
    function  __construct($overWriteBenchMode=false,$newBenchMode=false,$timeInMilliseconds=false) {
        
        if($overWriteBenchMode){
            $this->benchMode = $newBenchMode;
        }else{
            $this->benchMode = BENCH_MODE;
        }
        $this->stages = array();
        $this->firstCall=true;
        $this->initTime = $this->microtime_float();
        
        
    }
    /**
     * Creates a stage to show later with time elapsed from last stage
     * @param String $stageTitle The text for this stage, a title or comment
     * @param boolean $returnAsArray If true, return $data, else, $stageTitle and time
     * @return mixed see $returnAsArray
     */
    public function testTime($stageTitle,$returnAsArray=true){
        if(!$this->benchMode){
            return false;
        }
        
        if($this->firstCall){
            $lastTime = $this->initTime;
            $this->firstCall = false;
            $this->lastTime = $lastTime;
            $index = 0;
        }else{
            
            $lastTime = $this->lastTime;
            
            $index = count($this->stages);
        }
        
        $currentTime =  $this->microtime_float();
        
        $data = array();
        $data['text'] = $stageTitle;
        $data['elapsed_time'] = (float)$currentTime - $lastTime;
        $this->stages[$index] = $data;
        
        $this->lastTime = $currentTime;
        if($returnAsArray){
            return $data;
        }else{
            return $stageTitle . " : " . $data['elapsed_time'] . " secs.";
        }
        
        
    }
    /**
     * Print a list of all benches
     * @param string $lineSeparator The string/char line separator
     * @param boolean $timeInMilliseconds If true, times are shown in ms., else in seconds.
     * @return string A list of all benches
     */
    public function toString($lineSeparator="<br/>",$timeInMilliseconds=false){
        if(!$this->benchMode){
            return false;
        }
        foreach($this->stages as $stage){
            $str .= $lineSeparator . $stage['text'];

            if($timeInMilliseconds){
                $str .=" : " . ($stage['elapsed_time']/1000) . " ms. ";
            }else{
                $str .=" : " . $stage['elapsed_time'] . " secs. ";
            }
            
            
        }
        return $str;
    }
    /**
     * Get time in seconds from init to last testTime call
     * @return float Time elapsed from initTime to last testTime call
     */
    public function getTotalTime(){
        if(!$this->benchMode){
            return false;
        }
        return $this->lastTime - $this->initTime;
    }

    public function  __toString() {
        return $this->toString();
    }

    protected function microtime_float(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}
