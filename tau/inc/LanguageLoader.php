<?php

/**
 * 
 * @abstract Loads translations from DB
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 02-feb-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
require_once (dirname(__FILE__) . "/DataManager.php");

class LanguageLoader {

    protected $db;
    protected $cache;
    private static $uniqueInstance = null;
    
    protected function __construct() {
        
        $this->db =  DataManager::getInstance();
        $this->cache = array();
        
    }
    private final function __clone() {}
    private final function __wakeup() {}
    
     /**
     * Get the main singleton instance
     * @return LanguageLoader Singleton instance
     */
    public static function getInstance(){
        
        if(self::$uniqueInstance === null){
            self::$uniqueInstance = new LanguageLoader();
        }
        return self::$uniqueInstance;
    }
    
    public static function reset() {
        self::$uniqueInstance = null;
    }
    /**
     * Get translations from database, and parse some filters
     * @param string $group The group of labels
     * @param string $base_url The base url of the page
     * @param string $language The language code of two low-case letters
     * @param boolean $parseVanilla If true, will print the replacers instead of replace with text
     * @return array A list of key - value array with all the translations of the group
     */
    public function getTranslations($group,$base_url,$language, $parseVanilla = true){

        if(isset($this->cache[$group][$language]) && is_array($this->cache[$group][$language])){
            $lines = $this->cache[$group][$language];
        }else{
            $lines = $this->db->getResults("select item,content from tau_translations where lang='".$language."' and t_group='".$group."';" );
            $this->cache[$group][$language] = $lines;
        }
        
        $translations = array();
        
        foreach($lines as $line){

            $item = $line['item'];
            $text = $line['content'];
            
            if(strpos($text,"replace_ff_url") !== false  || strpos($text,"replace_ff_lang") !== false){
                
                $text = str_replace("replace_ff_url",$base_url,$text);
                $text = str_replace("replace_ff_lang",$language,$text);

            }
                if($_SESSION['vanilla'] && $parseVanilla){
                    $translations[$item] = "%%" . $item . "%%";
                }else{
                    $translations[$item] = $text;
                }
        }
             
            return $translations;

    }
    
}

?>
