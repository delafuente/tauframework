<?php

/**
 * Validates input data before to reach DB, and also
 * test reCaptcha fields and prevents re-writtign the same
 * form data.
 * CAUTION: Could output header data, if redirectOnErrors is true ( default )
 *
 * @abstract Validates input data before to reach DB
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 23-mar-2012
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
require_once('config.php');
require_once('recaptcha/recaptchalib.php');
require_once('DataManager.php');
require_once('LanguageLoader.php');
require_once( APPLICATION_PATH . '/tau/Tau.php');

class InputValidator {

    protected $inputArray;
    protected $numInputs;
    protected $reCaptchaResponse;
    protected $hasReCaptcha;
    protected $isCorrect;
    protected $errors; //array with error messages
    protected $redirectOnErrors;
    protected $db; // DataManager object
    protected $languageLoader; //LanguageLoader object
    protected static $localization;
    protected $mysqlDates = false; //date gets Y-m-d to be sanely inserted in mysql
    protected $changeDatesFormat = false;
    /**
     * Creates an InputValidator object
     * @param array $inputArray The array to clean, normally $_GET,$_POST or $_REQUEST
     * @param bool $adaptDatesToMySQL Modify the received date from current localization to MySQL format
     * @param bool $redirectOnErrors If true, will redirect to a page of error
     */

    function __construct($inputArray, $adaptDatesToMySQL = true, $redirectOnErrors = true) {
        
        $this->inputArray = $inputArray;
        $this->numInputs = count($this->inputArray);
        $this->isCorrect = true;
        $this->errors = array();
        $this->redirectOnErrors = $redirectOnErrors;
        $this->db = DataManager::getInstance();
        $this->changeDatesFormat = $adaptDatesToMySQL;
        
        $this->languageLoader = LanguageLoader::getInstance();
        $labels = $this->languageLoader->getTranslations('lang_labels', APPLICATION_BASE_URL, Tau::getInstance()->getLang());
        
        $validationTexts = $this->languageLoader->getTranslations('js_validation', APPLICATION_BASE_URL, Tau::getInstance()->getLang());
              
        if(empty($inputArray)){
            $this->redirectToError($labels['FRM_YET_RECEIVED']);
        }
        
        $inputArray = $this->db->escape($inputArray);
        
        $localization = unserialize( TauResponse::getCookie('localization') );
        if(!$localization){
            $loc = $this->db->getRow("select * from tau_localization where name='"
                .strtoupper(Tau::getInstance()->getLang()). "' limit 1;");
            
            self::$localization = $loc;
            TauResponse::setCookie(
                    'localization', 
                    serialize($loc), 
                    SECONDS_ONE_YEAR, 
                    LU_COOKIE_PATH, 
                    LU_COOKIE_DOMAIN);
        }
        
        if(!empty($_FILES)){
            foreach($_FILES as $nFile => $eFile){
                $inputArray[$nFile] = $eFile['name'];
            }
        }
        
        /* Apply validation rules specified when creating the form, and 
         * redirect to error page if we find something.
         * This validation is yet performed by javascript in client side, so
         * if we get some error here, it could be a hack attempt. */
        if(!empty($_POST)){
            $this->applyFormRules($inputArray, $validationTexts);
        }
        
        /* Put all dates in Y-m-d format */
        if($this->mysqlDates){
            foreach($this->mysqlDates as $field => $date){ $inputArray[$field] = $date; }
        }
        
        
        /* Check the form is not posted twice */
        if (isset($inputArray['form_hash']) && !ALLOW_FORM_DATA_REFRESH ) {

            if (TauSession::get('last_form_hash') && (TauSession::get('last_form_hash') == $inputArray['form_hash'])) {
                $texto .= $labels['FRM_YET_RECEIVED'];
                $this->errors['repeatedForm'] = $texto;
                $this->redirectToError($texto);
            } else {
                TauSession::put('last_form_hash', $inputArray['form_hash']);
            }
        }

        /* Check reCaptcha code, if any */
        if (isset($inputArray['recaptcha_challenge_field'])) {
            $this->reCaptchaResponse = recaptcha_check_answer(
                    RECAPTCHA_PRIVATE_KEY, 
                    $_SERVER["REMOTE_ADDR"], 
                    $inputArray["recaptcha_challenge_field"], 
                    $inputArray["recaptcha_response_field"]);
            $this->hasReCaptcha = true;

            if (!$this->reCaptchaResponse->is_valid) {
                $this->isCorrect = false;
                $this->errors['reCaptcha'] = $labels['CAPTCHA_ERROR'];
                /* $this->errors['reCaptcha'] .= "<br/><div>key:" . RECAPTCHA_PRIVATE_KEY .
                  " <br/>REMOTE_ADDR: " . $_SERVER["REMOTE_ADDR"] .
                  " <br/> challenge: " . $_POST["recaptcha_challenge_field"] .
                  " <br/> response: " . $_POST["recaptcha_response_field"] . "</div>"; */
                $this->redirectToError($this->errors['reCaptcha']);
            }
        } else {
            $this->reCaptchaResponse = false;
            $this->hasReCaptcha = false;
        }

        foreach ($inputArray as $key => $value) {
            $this->inputArray[$key] = $this->validateGeneral($value);
        }
    }

    protected function applyFormRules( array $input, array $validationTexts ){
        
        if(!isset($input['form_hash'])){
            return false;
        }
        $hash = $input['form_hash'];
        $valNames = TauSession::get("validationFor_$hash"."_names") ;
        $valRules = TauSession::get("validationFor_$hash"."_rules") ;
        $aNames = explode(",", $valNames);
        $aRules = explode(",", $valRules);
        $i = -1;
        
        foreach ($aNames as $fieldName){
            $i++;
            if($aRules == "" || $aRules[$i] == 'o' || $aRules[$i] == ""){
                continue;
            }
            $rules = explode("|", $aRules[$i]);
            if(!isset($input[$fieldName]) && !PRODUCTION_ENVIRONMENT){
                
                    die("TauFramework::InputValidator: Not found input[$fieldName], remember ".
                        " that the field name must be the same ".
                        "as the id of the field. You can also set the name ".
                        "parameter as 'false' when creating the field aRules:" . $aRules[$i] );
                
                
            }
            $this->validateField(
                    $fieldName, 
                    $input[$fieldName], 
                    $rules,
                    $validationTexts,
                    $input
                    );
        }
    }
    
    protected function validateField( 
            $fieldName, 
            $fieldValue, 
            array $rules, 
            array $validationTexts, 
            array $input){
        
        $i = 0;
        $initCounter = 0;
        $totRules = count($rules);
        
        if($rules[0] != "o"){
            if($fieldValue == ""){
                $this->redirectToError($validationTexts['required']);
            }
        }else{
            if($totRules > 0){
                $initCounter = 1;
            }
        }
        
        for( $i = $initCounter; $i < $totRules; $i++ ){
            $ruleName = "";
            $ruleValue = "";
            
            if($fieldValue == "" && $rules[0] == "o"){
                continue;
            }
            if(strpos($rules[$i], ":") !== false){
                $ruleArr = explode(":", $rules[$i]);
                $ruleName = $ruleArr[0];
                $ruleValue = $ruleArr[1];
            }else{
                $ruleName = $rules[$i];
            }
        
            if(!isset($ruleName) && !PRODUCTION_ENVIRONMENT){
                die("TauFramework: not set ruleName for field: $fieldName");
            }
            switch ($ruleName){

                case '*':
                    if($fieldValue == ""){
                        $this->redirectToError($validationTexts['required']);
                    }
                    break;
                case 'a':
                    if( !InputValidator::validateAlphanum($fieldValue, '_') ){
                        $this->redirectToError($validationTexts['alphanum'] . ": $fieldValue");
                    }
                    break;
                case 'aex':
                    if( !InputValidator::validateAlphanum($fieldValue, "áéíóúÁÉÍÓÚäëïöüÄËÏÖÜàèìòùÀÈÌÒÙçÇ_-ß")){
                        $this->redirectToError($validationTexts['bad_chars'] . ": $fieldValue");
                    }
                    break;
                case 'e':
                    if( !filter_var($fieldValue, FILTER_VALIDATE_EMAIL)){
                        $this->redirectToError($validationTexts['email'] . ": $fieldValue");
                    }
                    break;
                case 'url':
                    if( !filter_var($fieldValue, FILTER_VALIDATE_URL)){
                        $this->redirectToError($validationTexts['url'] . ": $fieldValue");
                    }
                    break;
                case 'num':
                    if( !InputValidator::validateNum($fieldValue)){
                        $this->redirectToError($validationTexts['only_numeric'] . ": $fieldValue");
                    }
                    break;    
                case 'int':
                    if( !filter_var($fieldValue, FILTER_VALIDATE_INT)){
                        $this->redirectToError($validationTexts['only_integer'] . ": $fieldValue");
                    }
                    break;
                case 'min':
                    if( !InputValidator::validateNum($fieldValue, $ruleValue)){
                        $endText = str_replace("rpl_param", $ruleValue, $validationTexts['min_value']);
                        $this->redirectToError($endText . ": $fieldValue");
                    }
                    break;
                case 'max':
                    if( !InputValidator::validateNum($fieldValue, false, $ruleValue)){
                        $endText = str_replace("rpl_param", $ruleValue, $validationTexts['max_value']);
                        $this->redirectToError($endText . ": $fieldValue");
                    }
                    break;
                case 'ml':
                    if( strlen($fieldValue) < $ruleValue){
                        $endText = str_replace("rpl_param", $ruleValue, $validationTexts['not_enough_chars']);
                        $this->redirectToError($endText . ": $fieldValue");
                    }
                    break;
                case 'Ml':
                    if( strlen($fieldValue) > $ruleValue){
                        $endText = str_replace("rpl_param", $ruleValue, $validationTexts['too_much_chars']);
                        $this->redirectToError($endText . ": $fieldValue");
                    }
                    break;
                case 'et':
                    if( $fieldValue != $input[$ruleValue]){
                        $this->redirectToError($validationTexts['not_equals_to'] . ": $fieldValue");
                    }
                    break;
                case 'dt':
                    $currentDate = InputValidator::validateDate($fieldValue);
                    if( !$currentDate ){
                        $this->redirectToError($validationTexts['bad_date'] . ": $fieldValue");
                    }else{
                        $this->mysqlDates[$fieldName] = $currentDate;
                    }
                    break;

            }
        
        }
    }
    public function allFieldsCorrect() {
        return $this->isCorrect;
    }

    protected function redirectToError($message) {
        if ($this->redirectOnErrors) {
            TauSession::put('last_error', $message);
            header("Location: " .APPLICATION_BASE_URL . "/". Tau::getInstance()->getLang() . "/error/");
            die();
        }
    }

    public static function validateInt($value, $minAllowed = false, $maxAllowed = false) {
        if (!is_int($value)) {
            return false;
        }
        return $this->validateNum($value, $minAllowed, $maxAllowed);
    }

    public static function validateNum($value, $minAllowed = false, $maxAllowed = false) {
        if (!is_numeric($value)) {
            return false;
        }
        if ($minAllowed) {
            if ($value < $minAllowed) {
                return false;
            }
        }
        if ($maxAllowed) {
            if ($value > $maxAllowed) {
                return false;
            }
        }
        return true;
    }

    public static function validateAlphanum($string, $extraAllowedChars) {
        
        return preg_match('/^[a-zA-Z0-9' . $extraAllowedChars . ']+$/', $string);
    }

    public static function validateString($value, $minLength = false, $maxLength = false, $allowedChars = false, $notAllowedChars = false) {
        $len = strlen($value);

        if ($notAllowedChars) {
            $lenNotAllowedChars = strlen($notAllowedChars);
            for ($i = 0; $i < $lenNotAllowedChars; $i++) {
                if (strpos($value, $notAllowedChars[$i]) === true) {
                    return false;
                }
            }
        }

        if ($allowedChars) {

            for ($i = 0; $i < $len; $i++) {
                if (strpos($allowedChars, $value[$i]) === false) {
                    return false;
                }
            }
        }
        if ($minLength) {
            if ($len < $minLength) {
                return false;
            }
        }
        if ($maxLength) {
            if ($len > $maxLength) {
                return false;
            }
        }

        return true;
    }

    public static function getDateSeparator($date, $sep = "/,-,., "){
        
        $separators = explode(",", $sep);
        
        foreach($separators as $splitter){
          if(strpos($date, $splitter) !== false){
              return $splitter;
          }  
        }
        return false;
    }
    
    public static function getPosition($data, $format){
        if($data[0] == $format){
            return 0;
        }else if($data[1] == $format){
            return 1;
        }else{
            return 2;
        }
    }
    
    public static function validateDate($date) {
        
        if(!$this->changeDatesFormat){
            return $date;
        }
        
        $separator = self::getDateSeparator($date);

        if($separator == ""){
            return false;
        }
        
        $valid_date = self::$localization['date_format'];
        $canonSep = self::getDateSeparator($valid_date);
        
        $vdat = explode($canonSep, trim($valid_date));
        
        $yearPos  = self::getPosition( $vdat, 'yy' );
        $monthPos = self::getPosition( $vdat, 'mm' );
        $dayPos   = self::getPosition( $vdat, 'dd' );

        $dt  = explode($separator, $date);
        
        if (count($dt) == 3) {
            if (checkdate($dt[$monthPos], $dt[$dayPos], $dt[$yearPos])) {
                return $dt[$yearPos]."-".$dt[$monthPos]."-".$dt[$dayPos];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function validateGeneral($str) {
        return $this->removeXSS($str);
    }

    //Return the original array, after passing removeXSS filter
    public function getCleanArray() {
        return $this->inputArray;
    }

    /**
     * External function of unknown author, replaces bad characters and
     * filters for xss injection
     * @param string Values unfiltered
     * @return  string Values filtered
     *
     */
    function removeXSS($val) {
        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
        // this prevents some character re-spacing such as <java\0script>
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
        $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);

        // straight replacements, the user should never need these since they're normal characters
        // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
            // &#x0040 @ search for the hex values
            $val = preg_replace('/(&#[x|X]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
            // &#00064 @ 0{0,7} matches '0' zero to seven times
            $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
        }

        // now the only remaining whitespace attacks are \t, \n, and \r
        //$ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'xml', 'blink', 'iframe', 'frameset', 'ilayer', 'bgsound');
        $ra1 = array();
        $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);

        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
                        $pattern .= '|(&#0{0,8}([9][10][13]);?)?';
                        $pattern .= ')?';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        return $val;
    }

}