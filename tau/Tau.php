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
class Tau {

    protected $environment;
    protected $lang;
    private static $uniqueInstance = null;

    protected function __construct() {

        $current_env = self::getEnv('APPLICATION_ENVIRONMENT');
        switch ($current_env) {
            case 'local':
            case 'pre':
            case 'pro':
                $this->environment = $current_env;
                break;
            default:
                $this->environment = 'pro';
        }

        if (isset($_COOKIE['lang'])) {
            $this->lang = $_COOKIE['lang'];
        } else {
            $this->lang = DEFAULT_LANG_ABBR;
        }
    }

    private final function __clone() {
        
    }

    private final function __wakeup() {
        
    }

    /**
     * Get the main singleton instance
     * @return LanguageLoader Singleton instance
     */
    public static function getInstance() {

        if (self::$uniqueInstance === null) {
            self::$uniqueInstance = new Tau();
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

    public static function tau_tokenizer($full_file_path, $token) {

        $ff = $full_file_path;
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

?>
