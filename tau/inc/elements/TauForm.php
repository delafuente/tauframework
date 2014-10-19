<?php
/**
 * 
 * @abstract Handles form creation
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 14-jul-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
//define('__ROOT__', str_replace("\\","/",dirname(dirname(__FILE__))) );

require_once(__ROOT__ . "/tau/inc/config.php");
require_once(__ROOT__ . "/tau/inc/DataManager.php");
require_once(__ROOT__ . '/tau/inc/recaptcha/recaptchalib.php');

class TauForm {
    
    protected $id;
    protected $name;
    protected $action;
    protected $encType;
    protected $extraFormAttributes;
    protected $defaultTheme;
    protected $themeMapping;
    protected $formTitle;
    
    protected $htmlBefore; //html to be prepended to the form
    protected $htmlAfter; //html to be appended to the form

    
    protected $elements; //array of elements
    protected $elementNames; //array with the elements' names
    protected $elementValidation; //array with the elements' validation rules
    protected $elementTypes; //string array of element types (input text, select, etc )
    protected $elementLabels; //string array with input label-for texts  
    protected $hiddenInputs; //String with all hidden inputs
    protected $elementReplacers; //Array of all replacements of replacement => replace with
    protected $modelData; //holds the row of the model if necessary
    protected $modelMapping; //holds the map of [table_field_name] = field_id
   
    /**
     * Creates a new Form object
     * @param string $id The html id
     * @param string $action The form action value
     * @param string $theme The folder theme name
     * @param string $name The html name, default is equals to id
     * @param string $enctype The form enctype
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value,
     * like $myAttributeArray['accept']="jpg,png"
     */
    public function __construct($id, $action, $theme ='default', $name=false, $enctype='multipart/form-data', $extraAttributes=false){
        

        
        $this->id = $id;
        $this->action = $action;
        $name = ($name)?$name:$id;
        $this->name = $name;

        $this->encType = $enctype;
        
        if($extraAttributes){
            foreach($extraAttributes as $extraKey => $extraValue){
                $this->extraFormAttributes[$extraKey] = $extraValue;
            }
        }
        $this->htmlAfter="";
        $this->htmlBefore="";
        $this->defaultTheme = $theme;
        $this->formTitle = $name;
        $this->hiddenInputs = "";


        
        $this->elements = array();
        $this->elementTypes = array();
        $this->elementNames = array();
        $this->elementValidation = array();
        $this->elementLabels = array();
        //$this->elementRequiredClasses = array();
        //$this->elementPostInputHtml = array();
        $this->modelData = false;
        $this->modelMapping = false;
        $this->setThemeMapping();
    }
    /**
     * For existing data, get the row information to show in form
     * @param type $model Table name
     * @param type $search_field_name Field name to select in where
     * @param type $search_field_value The value to be compared with field name
     * @param string $db_name Optional, to search the row in not default database
     */
    public function setModelAndRow($model, $search_field_name, $search_field_value, $db_name = false){
        if(!$db_name){
            $db_name = "";
        }
        $this->modelData = DataManager::getInstance($db_name)->getRow("select * from $model where $search_field_name = '$search_field_value' limit 1;");
        
        $this->modelMapping = array();
        foreach($this->modelData as $key => $value){
            $this->modelMapping[$key] = $key;
        }
    }
    /**
     * If table fields and html fields names aren't identical, we need to add
     * a mapping of type html_field => table_field. We need to map only fields needed.
     * @param array $modelMapping The array with the mapping
     */
    public function setModelMapping(array $modelMapping){
        $this->modelMapping = $modelMapping;
    }
    protected function setThemeMapping(){
        $this->themeMapping = array(
            'button' => 'templates/{rep_theme}/forms/button.html',  
            'checkbox' => 'templates/{rep_theme}/forms/checkbox.html',
            'checkboxes_group' => 'templates/{rep_theme}/forms/checkboxes_group.html',
            'container' => 'templates/{rep_theme}/forms/container.html',
            'datepicker' => 'templates/{rep_theme}/forms/datepicker',
            'file' => 'templates/{rep_theme}/forms/file.html',
            'file_image' => 'templates/{rep_theme}/forms/file_image.html',
            'form' => 'templates/{rep_theme}/forms/form.html',
            'hidden' => 'templates/{rep_theme}/forms/hidden.html',
            'linked_select' => 'templates/{rep_theme}/forms/linked_select.html',
            'options_group' => 'templates/{rep_theme}/forms/options_group.html',
            'password' => 'templates/{rep_theme}/forms/password.html',
            'select' => 'templates/{rep_theme}/forms/select.html',
            'select_option' => 'templates/{rep_theme}/forms/select_option.html',
            'submit' => 'templates/{rep_theme}/forms/submit.html',
            'text' => 'templates/{rep_theme}/forms/text.html',
            'textarea' => 'templates/{rep_theme}/forms/textarea'
        );
    }
    
    protected function getTemplate($template, $theme = false){
        if($theme == false){ $theme = $this->defaultTheme; }
        $templateFile = APPLICATION_PATH . "/" . APP_SLUG ."/". str_replace('{rep_theme}',$theme, $this->themeMapping[$template]);
        
        if(file_exists($templateFile)){
            return file_get_contents($templateFile);
        }else{
            if(!PRODUCTION_ENVIRONMENT){
                return "<p style='color:#f00;'>File not found for template '$template', while loading form for theme '$theme' : $templateFile</p>";    
            }else{
                return "";
            }
            
        }
    }
    
    /**
     * Set the caption of the form
     * @param string $title The caption of the form
     */
    public function setFormTitle($title){
        $this->formTitle = $title;
    }
    /* TYPES: [input] text|password|checkbox|radio|submit|reset|file|hidden|image|button
     * OTHER TYPES: textarea,[list|select],reCaptcha
     */
    /**
     * Inserts an input text, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript, see help on validation
     * @param string $theme Theme of the input. Same as form theme if not specified.
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value, like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers key - value array with replace_text - replace_with values to be replaced in template
     */
    public function addInputText(
            $id,
            $validationRules = false,
            $label = false,
            $theme = false, 
            $name = false,
            $defaultValue = false,
            array $extraAttributes = null,
            array $autoReplacers = null){
        
        $inputData = $this->generalInputDataFormatter($id, $validationRules,
                $label, $name, $defaultValue, $extraAttributes, $autoReplacers);
        
        $input = $this->getTemplate('text', $theme);
        $attributeReplacements = array('{replace_id}', '{replace_name}', '{replace_label}', '{replace_value}', '{extra_attributes}');
        $attributeReplacers = array($inputData['id'], $inputData['name'], $inputData['label'], $inputData['value'], $inputData['extraAttributesHtml']);
        $input = str_replace($attributeReplacements, $attributeReplacers, $input);
        
        $this->saveData($input, 'text', $inputData);
        
    }
    /**
     * Inserts an input checkbox, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $requiredClass If not false, adds an * after the label in a span with this class or space separated classes.
     * @param string $postInputHtml Html to be written just after the input, like help or hint
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $cssClasses The css classes, space separated
     * @param string $defaultValue The input default value. If true, will be checked.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value,
     * like $myAttributeArray['onChange']="javascript:testValue();" or $myAttributeArray['placeholder']='Enter your e-mail'
     */
    public function addInputCheckbox($id,$validationRules=false,$label=false,$requiredClass=false,
            $postInputHtml=false,$name=false,$cssClasses=false,$defaultValue=false
            ,$extraAttributes=false){
        
        $inputData = $this->generalInputDataFormatter($id, $validationRules,
                $label, $requiredClass, $postInputHtml, $name, $cssClasses,
                $defaultValue, $extraAttributes);

        $input = "\t<input type='checkbox' id='" .$inputData['id']. "' name='".
        $inputData['name']."' ";
        
        $checked = "";
        if($inputData['value']){
            $checked = " checked='checked' ";
        }
        
        $input .= $inputData['classes'] . $checked;
        $input .= $inputData['extraAttributesHtml'];
        $input .= " style='' ";
        $input .= " />\n";
        
        $this->saveData($input, 'checkbox', $inputData);
        
    }
     /**
     * Inserts textarea element, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $requiredClass If not false, adds an * after the label in a span with this class or space separated classes.
     * @param string $postInputHtml Html to be written just after the input, like help or hint
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $cssClasses The css classes, space separated
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value,
     * like $myAttributeArray['onChange']="javascript:testValue();" or $myAttributeArray['placeholder']='Enter your e-mail'
     */
    public function addTextArea($id,$validationRules=false,$label=false,$requiredClass=false,
            $postInputHtml=false,$name=false,$cssClasses=false,$defaultValue=""
            ,$extraAttributes=false){
        
        $inputData = $this->generalInputDataFormatter($id, $validationRules,
                $label, $requiredClass, $postInputHtml, $name, $cssClasses,
                $defaultValue, $extraAttributes);

        $input = "\t<textarea id='" .$inputData['id']. "' name='".
        $inputData['name']."' ";

        $input .= $inputData['classes'];
        $input .= $inputData['extraAttributesHtml'];
        $input .= " style=''";
        $input .= " >\n";
        $input .= $defaultValue . "</textarea>\n";
        
        $this->saveData($input, 'textarea', $inputData);
    }
 /**
     * Inserts an input password, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $requiredClass If not false, adds an * after the label in a span with this class or space separated classes.
     * @param string $postInputHtml Html to be written just after the input, like help or hint
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $cssClasses The css classes, space separated
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value,
     * like $myAttributeArray['onChange']="javascript:testValue();" or $myAttributeArray['placeholder']='Enter your e-mail'
     */
    public function addInputPassword($id,$validationRules=false,$label=false,$requiredClass=false,
            $postInputHtml=false,$name=false,$cssClasses=false,$defaultValue=""
            ,$extraAttributes=false){
        
        $inputData = $this->generalInputDataFormatter($id, $validationRules,
                $label, $requiredClass, $postInputHtml, $name, $cssClasses,
                $defaultValue, $extraAttributes);

        $input = "\t<input type='password' id='" .$inputData['id']. "' name='".
        $inputData['name']."' ";

        $input .= $inputData['classes'] . $inputData['value'];
        $input .= $inputData['extraAttributesHtml'];
        $input .= " style=''";
        $input .= " />\n";
        
        $this->saveData($input, 'password', $inputData);
        
    }
     /**
     * Inserts an input hidden, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value,
     * like $myAttributeArray['onChange']="javascript:testValue();" or $myAttributeArray['placeholder']='Enter your e-mail'
     */
    public function addInputHidden($id,$name=false,$defaultValue="",$extraAttributes=false){
        $requiredClass=false;
        $postInputHtml=false;
        $cssClasses=false;
        $label=false;
        $validationRules = "o";

        $inputData = $this->generalInputDataFormatter($id, $validationRules,
                $label, $requiredClass, $postInputHtml, $name, $cssClasses,
                $defaultValue, $extraAttributes);

        $input = "\t<input type='hidden' id='" .$inputData['id']. "' name='".
        $inputData['name']."' ";

        $input .=  $inputData['value'];
        $input .= $inputData['extraAttributesHtml'];
        $input .= " style=''";
        $input .= " />\n";

        $this->saveData($input, 'hidden', $inputData);

    }
     /**
     * Inserts an input reCaptcha, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $requiredClass If not false, adds an * after the label in a span with this class or space separated classes.
     * @param string $postInputHtml Html to be written just after the input, like help or hint
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $cssClasses The css classes, space separated
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value,
     * like $myAttributeArray['onChange']="javascript:testValue();" or $myAttributeArray['placeholder']='Enter your e-mail'
     */
    public function addInputReCaptcha($id,$validationRules,$label=false,$requiredClass=false,
            $postInputHtml=false){

         $name=false;
         $cssClasses=false;
         $defaultValue="";
         $extraAttributes=false;

        $captcha_html = recaptcha_get_html(RECAPTCHA_PUBLIC_KEY);

        $inputData = $this->generalInputDataFormatter($id, $validationRules,
                $label, $requiredClass, $postInputHtml, $name, $cssClasses,
                $defaultValue, $extraAttributes);


        $this->saveData($captcha_html, 'reCaptcha', $inputData);

    }
    public function addOptionsGroup(){
        
    }
    /**
     * Put some html or text after the form output
     * @param string $html The html or text to be appended to the form
     */
    public function appendHtml($html){
        $this->htmlAfter = $html;
    }
    /**
     * Put some html or text before the form output
     * @param string $html The html or text to be prepended to the form
     */
    public function prependHtml($html){
        $this->htmlBefore = $html;
    }
    
    /**
     * Get the constructed form in html format
     * @param string $containerTheme container theme, if you want to use other
     * @return string The html form
     */
    public function toString($containerTheme = false){

        $html = "\n";
        
        $extraAttributes = "";
        if($this->extraFormAttributes){
            foreach($this->extraFormAttributes as $attrName => $attrValue){
                $extraAttributes .= ' ' . $attrName . '="' . $attrValue . '" ';
            }
        }
        
        $html .= $this->getTemplate('form');
        $attributeReplacements = array('{replace_id}','{replace_name}','{replace_action}','{replace_method}', '{replace_enctype}', '{extra_attributes}');
        $attributeReplacers = array($this->id, $this->name, $this->action, 'post', $this->encType, $extraAttributes);
        $html = str_replace($attributeReplacements, $attributeReplacers, $html);

        $i=-1;
        foreach ($this->elements as $element){
            $i++;
            if($i==0){
                $validation = $this->elementValidation[$i];
                $names = $this->elementNames[$i];
            }else{
                $validation.= "," . $this->elementValidation[$i];
                $names .= "," . $this->elementNames[$i];

            }
            $elem = $this->getElement($i);
            if(isset($this->elementReplacers[$i])){
                $filters = $this->elementReplacers[$i];
                foreach($filters as $replacement => $replaceWith){
                    $elem = str_replace($replacement, $replaceWith, $elem);
                }
            }
            $html .= $elem;
        }

        $html .= $this->getSubmitButton();

        $html .= "</form>\n"; 
        
        $container = $this->getTemplate('container', $containerTemplate);
        
        $html = str_replace("{replace_form}",$html,$container);
        
        $html .= "<!--googleoff: all -->\n";
        $html .= "<span style='visibility:hidden' id='rr_names_" . $this->id . "'>" . $names . "</span>\n";
        $html .= "<span style='visibility:hidden' id='rr_rules_" . $this->id . "'>" . $validation . "</span>\n";
        $html .= "<!--googleon: all -->\n";
        $html .= "\n";
        
        
        return $html;
    }
    public function __toString(){
        return $this->toString();
    }
    /**
     * Obtains a formatted form input html element
     * @param int $counter The index of the element in the array
     * @return string The html for the element
     */
    protected function getElement($counter){
        
        if($this->elementTypes[$counter]=='hidden'){
            $this->hiddenInputs .= $this->elements[$counter] . "\n";
            return "";
        }
        
        return $this->elements[$counter];
    }
    /**
     * Obtains a formatted submit button
     * @return string The html for the element
     */
    protected function getSubmitButton(){

        $html ="";
        
        $hidden_form_hash ='<input type="hidden" id="form_hash" name="form_hash" value="replace_form_hash" />' . "\n";
        $sendButton='<input id="sendButton" name="sendButton" type="button"' .
        'value="' . $this->submitValue  .'" style="float:right;top:10px;" class="button highlight" onclick="javascript:tauValidation.testToSend(\'' .
                $this->id . '\');"/> <br/>' . "\n";
    
        $html =  $hidden_form_hash . $this->hiddenInputs . $sendButton ;

        return $html;
    }

     /**
     * Common input data parser, to be used for all get<InputElement>() methods
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail".
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $value The input value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value,
     * like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers array with key=>value replacement=>replaceWith for custom replacements in html form template
     * @return array Associative array with all formatted data, with the exception of defaultValue, returned as is for textarea like to use
     */
    protected function generalInputDataFormatter($id,$validationRules,$label,
        $name,$value,$extraAttributes,$autoReplacers){

        $validationRules = ($validationRules)?$validationRules:"o";
        $name = ($name)?$name:$id;
        $value = ($defaultValue)?$defaultValue:"";
        $label = ($label)?$label:"";
        
        if($extraAttributes){
            foreach($extraAttributes as $attrKey => $attrValue){
                $extraAttributesHtml .= " " . $attrKey . "='" . $attrValue . "' ";
            }
        }
        
        if($this->modelData !== false){ $value = $this->modelData[$this->modelMapping[$name]]; }
        
        $returnData = array();
        $returnData['id'] = $id;
        $returnData['validationRules'] = $validationRules;
        $returnData['label'] = $label;
        $returnData['name'] = $name;
        $returnData['value'] = $value;
        $returnData['extraAttributesHtml'] = $extraAttributesHtml;
        $returnData['autoReplacers'] = $autoReplacers;

        return $returnData;
    }
    protected function saveData($input,$type,$inputData){
        $this->elementNames[] = $inputData['id']; //IMPORTANT: This is for validation, we need the element id here, not the name
        $this->elementTypes[] = $type;
        $this->elementValidation[] = $inputData['validationRules'];
        $this->elements[] = $input;
        $this->elementLabels[] = $inputData['label'];
        $this->elementReplacers[] = $inputData['autoReplacers'];
    }



}
