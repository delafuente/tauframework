<?php

/**
 * 
 * @abstract Logs data into CallerFile.log files within LOG_PATH
 * @use example: Log::put(__FILE__, 'entering here', FILE_APPEND);
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 17-sep-2015
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
class Log {

    protected static $paths;
    
    /**
     * If you want a different name than CallerFile.log, specify here
     * @param string $path Should be __FILE__ constant
     * @param string $filename The filename we want, with extension
     */
    public static function setFileNameForPath($path, $filename){
        self::$paths[$path] = $filename;
    }
    /**
     * Add a new line to the caller log file, or overwrite if specified in flag
     * @param string $calling Should be __FILE__ constant
     * @param string $data The data to write
     * @param mixed $flags False to overwrite the file, or file_put_contents flags
     * @return int bytes written, or false if failed or DEBUG_MODE = false
     */
    public static function put($calling, $data, $flags = false){

        if(DEBUG_MODE === false){ return false; }
        
        $info = pathinfo($calling);
        $ext = (isset($info['extension']))?$info['extension']:'';
        $callingFileNoExt =  basename($calling,'.'.$ext);
        
        if(isset(self::$paths[$calling])){
            $file_name = self::$paths[$calling];
        }else{
            $file_name = $callingFileNoExt.'.log';
        }
        
        if($flags){
            return file_put_contents(LOG_PATH . $file_name , date('Y-m-d H:i:s') . " - [$callingFileNoExt] - " . $data . "\n", $flags);
        }else{
            return file_put_contents(LOG_PATH . $file_name , date('Y-m-d H:i:s') . " - [$callingFileNoExt] - " . $data . "\n");
        }
    }
    /**
     * Add a new line to the caller log file, or overwrite if specified in flag
     * @param string $calling Should be __FILE__ constant
     * @param string $data The data to write
     * @param mixed $flags False to overwrite the file, or file_put_contents flags
     * @return int bytes written, or false if failed or VERBOSE_MODE = false
     */
    public static function putv($calling, $data, $flags = false){
        if(VERBOSE_MODE){
            return self::put($calling, $data, $flags);
        }else{
            return false;
        }
    }

}