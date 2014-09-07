<?php

session_start();

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
require_once('../../Tau.php');

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
    /**
     * Creates an InputValidator object
     * @param type $inputArray The array to clean, normally $_GET,$_POST or $_REQUEST
     * @param type $redirectOnErrors If true, will redirect to a page of error
     * @param type $dataManager DataManager object to clean with mysql_escape
     */

    function __construct($inputArray, $redirectOnErrors = true, $dataManager = false) {
        $this->inputArray = $inputArray;
        $this->numInputs = count($this->inputArray);
        $this->isCorrect = true;
        $this->errors = array();
        $this->redirectOnErrors = $redirectOnErrors;

        if ($dataManager instanceof DataManager) {
            $this->db = $dataManager;
        } else {
            $this->db = DataManager::getInstance();
        }
        $this->languageLoader = LanguageLoader::getInstance();
        $labels = $this->languageLoader->getTranslations('lang_labels', APPLICATION_BASE_URL, Tau::getInstance()->getLang());
        
        $inputArray = $this->db->escape($inputArray);

        /* Check the form is not posted twice */
        if (isset($inputArray['form_hash'])) {


            if (isset($_SESSION['last_form_hash']) && ($_SESSION['last_form_hash'] == $inputArray['form_hash'])) {
                $texto .= $labels['FRM_YET_RECEIVED'];
                $this->errors['repeatedForm'] = $texto;
                $this->redirectToError($texto);
            } else {
                $_SESSION['last_form_hash'] = $inputArray['form_hash'];
            }
        }

        /* Check reCaptcha code, if any */
        if (isset($inputArray['recaptcha_challenge_field'])) {
            $this->reCaptchaResponse = recaptcha_check_answer(RECAPTCHA_PRIVATE_KEY, $_SERVER["REMOTE_ADDR"], $inputArray["recaptcha_challenge_field"], $inputArray["recaptcha_response_field"]);
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

    public function allFieldsCorrect() {
        return $this->isCorrect;
    }

    protected function redirectToError($message) {
        if ($this->redirectOnErrors) {
            $_SESSION['last_error'] = $message;
            header("Location: /error/");
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

        return preg_match('/^[a-zA-Z0-9áéíóúÁÉÍÓÚäëïöüÄËÏÖÜàèìòùÀÈÌÒÙçÇ' . $extraAllowedChars . ']+$/', $string);
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

    public static function validateDate($date) {
        
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
        $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'xml', 'blink', 'iframe', 'frameset', 'ilayer', 'bgsound');
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

?>
