<?php
/**
 * 
 * @abstract Class to control writting to log files
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 13-feb-2011
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
require_once("config.php");

//error_reporting(E_ALL);

class LogFile {

    private $log_mode = false;
    private $filepath = "error.php";
    private $overwrite_mode;
    /**
     * Creates new LobFile object
     * @param boolean $debugmode Constant of state, to log or not
     * @param string $filename The file name to log on, please use a php file, to control web log access. Uses LOG_PATH as path.
     * @param string $overwrite_last_file If true, will overwrite last file, if not, will append to it
     */
    function __construct($debugmode, $filename='error.php', $overwrite_last_file=true) {
        $this->log_mode = $debugmode;
        $this->filepath = LOG_PATH . $filename;
        
        if(!file_exists($this->filepath)){
            file_put_contents($this->filepath, "<?php if(\$_SERVER['SERVER_NAME']=='". CANONICAL_APP_NAME."'){ die('nothing to see here'); }else{ echo 'file " . $this->filepath . " created on " .
                    date("Y-m-d H:i:s",time()) . " <br/><br/>'; } ?>\n\n", FILE_APPEND);
        }
        if ($overwrite_last_file) {
            $this->overwrite_mode = FILE_APPEND;
            shell_exec("rm " . $this->filepath);
            file_put_contents($this->filepath, "<?php if(\$_SERVER['SERVER_NAME']=='". CANONICAL_APP_NAME."'){ die('nothing to see here'); }else{ echo 'file " . $this->filepath . " created on " .
                    date("Y-m-d H:i:s",time()) . " <br/><br/>'; } ?>\n\n", FILE_APPEND);
        } else {
            $this->overwrite_mode = FILE_APPEND;
        }
    }

    public function put($data) {
        if ($this->log_mode) {
            file_put_contents($this->filepath, $data . " <br/>\n",$this->overwrite_mode);
            //$res = $this->writeToFile($this->filepath, $data . "\n", $this->overwrite_mode);

        }
    }

    function setLogMode($mode) {
        $this->log_mode = $mode;
    }

    public function writeToFile($filePath, $data, $mode) {
        if($mode == 'a'){
            $mod = FILE_APPEND;
        }else{
            $mod = 0;
        }
        

        return file_put_contents($filePath, $data,$mod);
    }

}

?>
