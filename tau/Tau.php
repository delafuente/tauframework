<?php

/**
 * 
 * @abstract Main Tau Framework class
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 05-jul-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
require_once( "inc/framework/TauURI.php" );
require_once( "inc/framework/TauResponse.php" );
require_once( "inc/framework/TauMessages.php" );

class Tau {

    protected $environment;
    protected $lang;
    protected $country;
    protected $fake = null;
    private static $allDbInstances;
    private static $uniqueInstance = null;
    private static $loadedTemplates;
    

    protected function __construct(array $fake = null) {

        if($fake){ $this->fake = $fake; }
        
        $current_env = self::getEnv('APPLICATION_ENVIRONMENT');
        self::$loadedTemplates = array();
        
        switch ($current_env) {
            case 'local':
            case 'pre':
            case 'pro':
                $this->environment = $current_env;
                break;
            default:
                $this->environment = 'pro';
        }
        
        if($fake && isset($fake['environment'])){ 
            $this->environment = $fake['environment']; 
        }
        
        $this->determineCountry();
        $this->determineLang();
        
        TauResponse::setCookie('lang', $this->lang, time() + SECONDS_ONE_YEAR, "/");
        TauSession::put('lang', $this->lang);
        
    }
    public function getFake( $key ){
        if(isset($this->fake) && isset($this->fake[$key])){
            return $this->fake[$key];
        }else{
            return false;
        }
    }
    public function getCountry(){
        return $this->country;
    }
    
    protected function determineLang(){
        
        if( $fakeLang = $this->getFake('lang') ){
            $this->lang = $fakeLang;
            TauMessages::addNotice("LANG taken from FAKE lang: $this->lang", "Tau::__construct()");
            TauResponse::setCookie('lang', $this->lang, time() + SECONDS_ONE_YEAR, "/");
        } else if ( TauURI::$langOnURI ) {
            $this->lang = TauURI::$langOnURI;
            TauMessages::addNotice("LANG taken from URI: $this->lang", "Tau::__construct()");
        } else if ( TauSession::get('lang') ) {
            $this->lang = TauSession::get('lang');
            TauMessages::addNotice("LANG taken from SESSION: $this->lang", "Tau::__construct()");
        }  else if ( TauResponse::getCookie('lang') ) {
            $this->lang = TauResponse::getCookie('lang');
            TauMessages::addNotice("LANG taken from COOKIE: $this->lang", "Tau::__construct()");
        } else if( TauSession::getSub('localization', 'lang') ) {
            $this->lang = mb_strtolower( TauSession::getSub('localization', 'lang') );
            TauMessages::addNotice("LANG taken from DEFAULT for Country $this->country: $this->lang", "Tau::__construct()");
        } else {
            $this->lang = DEFAULT_LANG_ABBR;
            TauMessages::addNotice("LANG taken from DEFAULT_LANG_ABBR: ". DEFAULT_LANG_ABBR. ", Tau::__construct()");
        }
    }
    
    protected function determineCountry(){
        
        if($fakeCountry = $this->getFake('country')){
            $this->country = $fakeCountry;
            $this->addCountryToSession($fakeCountry);
            return;
        }
        if($currentLoc = TauSession::get('localization')){
            TauResponse::setCookie('country', 
            mb_strtolower($currentLoc['country']), 
                    time() + SECONDS_ONE_YEAR, "/");
            return;
        }
        
        if( $currentCountry = TauRequest::getCountryByIP() ){
            $this->country = $currentCountry;
            $this->addCountryToSession($this->country);
        }else{
            $accept = filter_input(
                    INPUT_SERVER,'HTTP_ACCEPT_LANGUAGE',FILTER_SANITIZE_STRING);
            $locale = Locale::acceptFromHttp($accept);
            if(strpos($locale, '_') !== false){
                $splitted = explode('_', $locale);
                $this->country = $splitted[1];
            }else{
                $this->country = DEFAULT_COUNTRY;
            }
            $this->addCountryToSession($this->country);
        }
    }
    
    protected function addCountryToSession($country){
        global $lang_local;
        $country = mb_strtolower($country);
        $db = DataManager::getInstance();
        $countryData = $db->getRow("select * from tau_localization ".
                "where country='$country' limit 1");
        TauSession::put('localization', $countryData);
        TauSession::put('country', $country);
        TauSession::addToKey('localization', 
                'date_format', $lang_local[$country]['date_format']);
        TauSession::addToKey('localization', 
                'date_first_day', $lang_local[$country]['date_first_day']);
        TauResponse::setCookie('country', $country, time() + SECONDS_ONE_YEAR, "/");
    }

    private final function __clone() {
        
    }

    private final function __wakeup() {
        
    }
    /**
     * Add a template to the list
     * @param string $template path to the template
     */
    public static function addTemplate( $template ){
        self::$loadedTemplates[] = $template;
    }
    /**
     * Get the list of loaded templates
     * @return array The list of templates
     */
    public static function getLoadedTemplates(){
        return self::$loadedTemplates;
    }
    
    /**
     * Get the main singleton instance
     * @param array $fake Used to fake country, lang, etc
     * @return Tau Singleton instance
     */
    public static function getInstance(array $fake = null) {

        if (self::$uniqueInstance === null) {
            self::$uniqueInstance = new Tau($fake);
        }
        return self::$uniqueInstance;
    }

    public static function reset() {
        self::$uniqueInstance = null;
    }

    public function getEnvironment() {
        return $this->environment;
    }

    /**
     * Get app current language
     * @return string Lang code of two lowercase letters
     */
    public function getLang() {
        return $this->lang;
    }

    public function hookBeforeRender() {
        
    }

    public function hookAfterRender() {
        Tau::closeAllDbConnections();
    }

    public function hookBeforeInit() {
        
    }

    public function hookAfterInit() {
        
    }

    public function hookBeforeEnd() {
        
    }

    public static function getEnv($varname) {
        return getenv($varname);
    }

    public static function getTauGreek() {
        return "&tau;&alpha;&upsilon;";
    }

    public static function getTauFrameworkGreek() {
        return "&tau;&alpha;&upsilon; &phi;&rho;&alpha;&mu;&epsilon;&#989;o&rho;&kappa;";
    }

    public static function addDbInstance($instance) {
        self::$allDbInstances[] = $instance;
    }

    public static function closeAllDbConnections() {

        foreach (self::$allDbInstances as $db_instance) {

            if ($db_instance instanceof DataManager) {

                try {
                    $db_instance->close();
                } catch (Exception $ex) {
                    return $ex->getMessage();
                }
            }
        }
    }

    public static function tau_tokenizer($full_file_path, $token) {

        $ff = str_replace(APPLICATION_PATH, "", $full_file_path);
        $token = substr($token, 2, strlen($token) - 4);

        $sha1 = substr(sha1($ff), 0, 7);
        $tau_prefix = "tau_" . $sha1 . "_";

        if (substr($token, 0, 12) == $tau_prefix) {
            return $token;
        }

        return "tau_" . $sha1 . "_" . $token;
    }

    public static function tau_get_group($full_file_path) {

        $ff = $full_file_path;
        $sha1 = substr(sha1($ff), 0, 7);
        return "tau_" . $sha1;
    }

}