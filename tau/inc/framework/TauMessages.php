<?php

/**
 * 
 * @abstract
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 18-oct-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
class TauMessages {

    protected static $messages = array();
    protected static $messagesHtml = array();
    protected static $notice = "#a901db"; #facc2e";
    protected static $error = "#ff0000;font-weight:bolder;";
    protected static $warning = "#ff8000";

    /**
     * Adds a message to application list if in VERBOSE_MODE
     * @param string $message The message
     * @param string $type notice, warning or error
     * @param string $sender The file or method calling this 
     */
    public static function addMessage($message, $type = "notice", $sender = "anonymous") {
        if (!VERBOSE_MODE) {
            return false;
        }

        $style = "";        
        
        switch ($type) {
            case 'notice': $style = self::$notice; break;                
            case 'warning': $style = self::$warning; break;
            case 'error': $style = self::$error; break;
            default: $style = $type; break;
        }
        
            $pre = "<p class='tau_$type' style='color:$style'>";
            $post = "</p>";
            
        
        self::$messagesHtml[] = $pre." [".date("Y-m-d H:i:s",time())."][$sender] $message $post";
        self::$messages[] = " $type [".date("Y-m-d H:i:s",time())."][$sender] $message";
    }
    
    public static function addNotice($message, $sender = 'anonymous'){
        self::addMessage($message, 'notice', $sender);
    }
    public static function addWarning($message, $sender = 'anonymous'){
        self::addMessage($message, 'warning', $sender);
    }
    public static function addError($message, $sender = 'anonymous'){
        self::addMessage($message, 'error', $sender);
    }
    
    public static function getAllMessages(){
        return implode("\n", self::$messages);
    }
    
    public static function getAllMessagesHtml(){
        $mess = "<div id='tau_messages'><p style='color:#3104b4'>Tau LOG ( VERBOSE_MODE = true )</p>";
        $mess .= implode("\n", self::$messagesHtml);
        $mess .= "</div>";
        
        return $mess;
    }

}
