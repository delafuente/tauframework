<?php
/**
 * 
 * @abstract Main render of the page
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 10-feb-2011
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
require_once("config.php");
require_once("Replacer.php");
require_once("../Tau.php");

class PageRender {

    protected $pageSlots; //array (num_key,text_of_page)
    protected $last_id;
    protected $description;
    protected $lang; // like es
    protected $lang_code; // like es_ES
    //Widgets
    protected $headlineWidget;
    protected $breakingNewsWidget;
    protected $friendsWidget;

    /**
     * Creates a new PageRender, to process template additions.
     * @param string $lang Language, like es
     * @param string $lang_code Language code, like es_ES
     */

    public function __construct($lang, $lang_code) {

        if ($lang == "" || !isset($lang_code)) {
            echo "NOT LANG DEFINED, please contact with us in " . WEBMASTER_MAIL . " about this error.";
            error_log("NOT LANG DEFINED, please contact with us in " . WEBMASTER_MAIL . " about this error.");
            die();
        }
        $this->pageSlots = array();
        $this->last_id = -1;
        $this->description = APPLICATION_NAME;
        $this->lang = $lang;
        $this->lang_code = $lang_code;
        
        $this->headlineWidget = false;
        $this->breakingNewsWidget = false;
        $this->friendsWidget = false;
    }

    public function getTranslationGroup($full_file_path){
    
        $sha1 = substr(sha1($full_file_path), 0, 7);
        
        return "tau_" . $sha1;
    }
    
    /**
     * Load text from a file, and parses it with a Replacer
     * @param string $filename The path to filename.
     * @param Replacer $replacer A Replacer object to replace text.
     * @param int $id Optional id of the array, be aware to not unconscious overwrite
     * other slot's id
     */
    public function loadFile($filename, Replacer $replacer, $id = false) {

        try {
            $text = file_get_contents($filename);
            $replacer->addLanguageFile($this->getTranslationGroup($filename), APPLICATION_BASE_URL, $this->lang);
            $this->addContent($text, $replacer, $id);
            if ($text = "") {
                throw new Exception("Cannot open filename " . $filename);
            }
        } catch (Exception $ex) {
            if (DEBUG_MODE) {
                error_log("PageRender.php - loadFile() - Exception trying to open " . $filename);
                return $ex->getMessage();
            } else {
                sendSimpleMail(ERROR_MAIL, ERROR_RECIPIENT_MAIL, "FATAL ERROR in " . APPLICATION_NAME, "PageRender.php - getStringFromFile() - Exception trying to open " . $filename);
                return "Error in server : Perhaps we are into maintenance, if the problem persists please contact " .
                        WEBMASTER_MAIL . " and explain where do you find this error.";
            }
        }
    }

    /**
     * Get the contents of file without adding it to this object, for
     * parsing or formatting. Intended to use it later with addContent()
     * @param String $filename The path to filename
     * @param Replacer $replacer An optional replacer to parse the text before return
     * @return String The contents of the file.
     */
    public function getStringFromFile($filename, Replacer $replacer = null) {
        try {
            $text = file_get_contents($filename);
            if ($text == "") {
                throw new Exception("Cannot open filename " . $filename);
            }
        } catch (Exception $ex) {
            $message = "PageRender.php - getStringFromFile() - Exception trying to open " . $filename;

            if (DEBUG_MODE) {
                error_log($message);
                return $ex->getMessage();
            } else {
                sendSimpleMail(ERROR_MAIL, ERROR_RECIPIENT_MAIL, "FATAL ERROR in " .APPLICATION_NAME , "PageRender.php - getStringFromFile() - Exception trying to open " . $filename);
                return "Error in server : Perhaps we are into maintenance, if the problem persists please contact " .
                        WEBMASTER_MAIL . " and explain where do you find this error.";
            }
        }
        if ($replacer != null) {
            $replacer->addLanguageFile($this->getTranslationGroup($filename), APPLICATION_BASE_URL, $this->lang);
            return $replacer->filter($text);
        } else {
            return $text;
        }
    }

    /**
     * Get the output of a php file in a string
     * @param string Url to file
     * @param Replacer $replacer An optional replacer to parse the text before return
     * @return string The php file executed contents
     */
    function loadPhpFile($file, Replacer $replacer = null) {
        if (!is_file($file) || !file_exists($file) || !is_readable($file))
            return false;
        ob_start();
        include($file);
        $contents = ob_get_contents();
        ob_end_clean();
        
        if ($replacer != null) {
            $replacer->addLanguageFile($this->getTranslationGroup($filename), APPLICATION_BASE_URL, $this->lang);
            return $replacer->filter($contents);
        } else {
            return $contents;
        }
    }

    /**
     * Load text and parses it with a Replacer object.
     * @param string $text the text or html to be inserted in the page
     * @param Replacer $replacer A replacer to replace text.
     * @param int $id Optional id of the array, be aware to not unconscious overwrite
     * other slot's id
     */
    public function addContent($text, Replacer $replacer, $id = false) {

        if (!$id) {
            $this->last_id++;
            $id = $this->last_id;
        } else {
            if ($this->last_id < $id) {
                $this->last_id = $id;
            }
        }
        $this->pageSlots[$id] = $replacer->filter($text);
    }

    public function setDescription($description) {
        $this->description = $description;
    }
    
    public function addHeadlineWidget(){       
        $this->headlineWidget = true;
    }
    public function addBreakingNewsWidget(){
        $this->breakingNewsWidget = true;
    }
    public function addFriendsWidget(){
        $this->friendsWidget = true;
    }
    protected function getHeadlineWidget(){
        return $this->loadPhpFile(APPLICATION_PATH . "/controllers/".APP_SLUG."/headlineWidget.php");
    }
    protected function getBreakingNewsWidget(){
        return $this->loadPhpFile(APPLICATION_PATH . "/controllers/".APP_SLUG."/breakingNewsWidget.php");
    }
    protected function getFriendsWidget(){
        return $this->loadPhpFile(APPLICATION_PATH . "/controllers/".APP_SLUG."/friendsWidget.php");
    }
    /**
     * Get the slot contents, yet parsed. This method is not commonly used,
     * you can get it to parse outside and then use addContent with the same id
     * to overwrite previous text.
     * @param int $id the id of the slot in the array.
     * @return string the content of the slot, yet parsed by a Replacer.
     */
    public function getSlot($id) {
        return $this->pageSlots[$id];
    }

    /**
     * Return the full content of the PageRender in the order it was inserted,
     * or the specified order when inserting. And last filtering all page
     * replacements ( like form hash )
     * @return string The full content of the PageRender in the order
     * it was inserted, or the specified when inserting
     */
    public function toString() {
        $endPage = "";
        Tau::getInstance()->hookBeforeRender();
        
        $totSlots = count($this->pageSlots);

        for ($i = 0; $i < $totSlots; $i++) {

            if (!empty($this->pageSlots[$i])) {
                $endPage .= $this->pageSlots[$i];
            }
        }

        $endPage = str_replace("replace_form_hash", uniqid(), $endPage);
        $endPage = str_replace("replace_urlbase", APPLICATION_BASE_URL, $endPage);
        $endPage = str_replace("replace_base_url", APPLICATION_BASE_URL, $endPage);
        //Prevent this values not being overriden before
        $endPage = str_replace("replace_more_css", "", $endPage);
        $endPage = str_replace("replace_more_js", "", $endPage);
        
        $endPage = str_replace("replace_description", $this->description, $endPage);
          
        $endPage = str_replace("{replace_app}",APP,$endPage);
        $endPage = str_replace("{replace_lang}",$this->lang,$endPage);
        
        if($this->headlineWidget === true){
            
            $endPage = str_replace("{replace_headline}",$this->getHeadlineWidget(),$endPage);
        }else{
            
            $endPage = str_replace("{replace_headline}","",$endPage);
        }
        
        if($this->breakingNewsWidget === true){
            
            $endPage = str_replace("{replace_breaking_news}",$this->getBreakingNewsWidget(),$endPage);
        }else{
            
            $endPage = str_replace("{replace_breaking_news}","",$endPage);
        }
        
        if($this->friendsWidget === true){
            
            $endPage = str_replace("{replace_friends_widget}",$this->getFriendsWidget(),$endPage);
        }else{
            
            $endPage = str_replace("{replace_friends_widget}","",$endPage);
        }
        
        
        //Validation texts
        $validation_text = $this->getStringFromFile(APPLICATION_PATH . "/js/lang/" . $this->lang_code . "/lang_validation.js");
        //Constants
        $constants = "const APP_BASE_URL='" . APPLICATION_BASE_URL . "';";
        $constants .= "const LANG='" . $this->lang . "';";
        $endPage = str_replace("<head>","<head>\n\n<script language='javascript'>\n\n" . $validation_text . "\n" .$constants. "\n\n</script>\n\n",$endPage);
        
        Tau::getInstance()->hookAfterRender();
        
        return $endPage;
    }

    /**
     * Magic method, called from functions which try to print this object.
     * @return string See toString()
     */
    public function __toString() {
        return $this->toString();
    }

}

?>
