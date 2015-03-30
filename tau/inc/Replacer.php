<?php
/**
 * 
 * @abstract Main replacer of the page
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 10-feb-2011
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
require_once (dirname(__FILE__) . "/DataManager.php");
require_once (dirname(__FILE__) . "/LanguageLoader.php");

class Replacer {
    //put your code here

    protected $replacements;
    protected $isEmpty;
    protected $db;
    
    function __construct(){
        $this->replacements=array();
        $this->isEmpty=true;
        $this->db = DataManager::getInstance();
    }
    /**
     * Add a string_to_replace(title) and string for replacing it(value)
     * @param string $title The string to be replaced in file
     * @param string $value The string to replace the 'title' string
     */
    public function addFilter($title,$value){
        $this->replacements[$title]=$value;
        $this->isEmpty=false;
    }

    /**
     * This function adds filters for any given group of replacements.
     * Also returns the list of key-value replacements for further use, if
     * needed, but normally you don't need to use the returned value
     * @param string $group The group identifier
     * @param string $base_url The base url, APPLICATION_BASE_URL just in case must be replaced inside this function
     * @param string $language The two letter code of language, like es,en,de,it etc
     * @return array Key/value pairs of the translation
     */
    public function addLanguageFile($group, $base_url, $language) {
        
        $lines = LanguageLoader::getInstance()->getTranslations($group, $base_url, $language, false);
        
        $tanslations = array();

        foreach ($lines as $item => $text) {

            if (TauSession::get('vanilla')) {
                $this->addFilter(trim("{{".$item."}}"), "%%" . trim($item) . "%%");
            } else {
                $this->addFilter(trim("{{".$item."}}"), trim($text));
            }

            $tanslations[$item] = $text;
        }

        return $tanslations;
    }

    public function filter($text){
        $keys = array_keys($this->replacements);
        $values = array_values($this->replacements);
        if(!$this->isEmpty){
            return str_replace($keys,$values,$text);
        }else{
            return $text;
        }
        
    }
    /**
     * Get the filters array
     * @return array The array with filters, in the form key(to_be_replaced) value(replace_with)
     */
    public function getFilters(){
        return $this->replacements;
    }
    
    public function getFilter($replacementKey){
        return $this->replacements[$replacementKey];
    }
}


?>
