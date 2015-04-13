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
    protected $submitValue;
    protected $submitTheme;
    protected $formTheme;
    protected $formHash;
    protected $disableSubmit;
    protected $allowSubmitOnEnter;
    
    /**
     * Creates a new Form object
     * @param string $id The html id
     * @param string $action The form action value
     * @param string $theme The folder theme name, default 'default'
     * @param string $name The html name, default is equals to id
     * @param string $enctype The form enctype
     * @param array $extraAttributes string with key=form attr name, 
     * value=form attribute value,
     * like $myAttributeArray['accept']="jpg,png"
     */
    public function __construct( 
            $id, 
            $action,
            $theme ='default',
            $name=false,
            $enctype='multipart/form-data',
            $extraAttributes=false){
        
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
        $this->submitValue = "Undefined Text";
        $this->submitTheme = false;
        $this->formTheme = false;
        $this->disableSubmit = false;
        $this->allowSubmitOnEnter = false;
        $this->elements = array();
        $this->elementTypes = array();
        $this->elementNames = array();
        $this->elementValidation = array();
        $this->elementLabels = array();
        $this->modelData = false;
        $this->modelMapping = false;
        $this->formHash = uniqid();
        $this->setThemeMapping();
    }
    
    public function setSubmitButtonText($text){
        $this->submitValue = $text;
    }
    /**
     * Allows form to be submit if the user press enter in some non-textarea field.
     * This is set automatically if you fire disableSubmit()
     */
    public function allowSubmitOnEnter(){
        $this->allowSubmitOnEnter = true;
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
            'datepicker' => 'templates/{rep_theme}/forms/datepicker.html',
            'datepicker_custom' => 'templates/{rep_theme}/forms/datepicker_custom.html',
            'file' => 'templates/{rep_theme}/forms/file.html',
            'file_image' => 'templates/{rep_theme}/forms/file_image.html',
            'form' => 'templates/{rep_theme}/forms/form.html',
            'hidden' => 'templates/{rep_theme}/forms/hidden.html',
            'radio_button' => 'templates/{rep_theme}/forms/radio_button.html',
            'password' => 'templates/{rep_theme}/forms/password.html',
            'select' => 'templates/{rep_theme}/forms/select.html',
            'select_option' => 'templates/{rep_theme}/forms/select_option.html',
            'submit' => 'templates/{rep_theme}/forms/submit.html',
            'text' => 'templates/{rep_theme}/forms/text.html',
            'textarea' => 'templates/{rep_theme}/forms/textarea.html',
            'prevent_enter' => 'templates/{rep_theme}/forms/prevent_enter.html',
            'allow_enter' => 'templates/{rep_theme}/forms/allow_enter.html'
        );
    }
    
    protected function getTemplate($template, $theme = false){
        if($theme == false){ $theme = $this->defaultTheme; }
        $templateFile = APPLICATION_PATH . "/" . APP_SLUG ."/". str_replace('{rep_theme}',$theme, $this->themeMapping[$template]);
        
        Tau::addTemplate($templateFile);
        
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
     * Will not use submit button, and also will allow enter as submit in non
     * text-area fields.
     */
    public function disableSubmit(){
        $this->disableSubmit = true;
        $this->allowSubmitOnEnter = true;
    }
    /**
     * Set the caption of the form
     * @param string $title The caption of the form
     */
    public function setFormTitle($title){
        $this->formTitle = $title;
    }
        /**
     * Inserts an $type input, the order of insertion is the order of appearance
     * @param string $type The tau type of the field
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $theme Theme of the input. Same as form theme if not specified.
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value, like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers key - value array with replace_text - replace_with values to be replaced in template
     */
    protected function addGeneralInput(
            $type,
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
        
        $input = $this->getTemplate($type, $theme);
        $attributeReplacements = array('{replace_id}', '{replace_name}', '{replace_label}', '{replace_value}', '{extra_attributes}');
        $attributeReplacers = array($inputData['id'], $inputData['name'], $inputData['label'], $inputData['value'], $inputData['extraAttributesHtml']);
        $input = str_replace($attributeReplacements, $attributeReplacers, $input);
        
        $this->saveData($input, $type, $inputData);
        
    }
    /* TYPES: [input] text|password|checkbox|radio|submit|reset|file|hidden|image|button
     * OTHER TYPES: textarea,[list|select],reCaptcha
     */
    /**
     * Inserts an input text, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $theme Theme of the input. Same as form theme if not specified.
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
        
        $this->addGeneralInput('text', $id, $validationRules, $label, $theme,
                $name, $defaultValue, $extraAttributes, $autoReplacers);
        
    }
    /**
     * Inserts an input button, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $theme Theme of the input. Same as form theme if not specified.
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value, like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers key - value array with replace_text - replace_with values to be replaced in template
     */
    public function addInputButton(
            $id,
            $validationRules = false,
            $label = false,
            $theme = false, 
            $name = false,
            $defaultValue = false,
            array $extraAttributes = null,
            array $autoReplacers = null){
        
        $this->addGeneralInput('button', $id, $validationRules, $label, $theme,
                $name, $defaultValue, $extraAttributes, $autoReplacers);
        
    }
    
    /**
     * Inserts an input checkbox, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $theme Theme of the input. Same as form theme if not specified.
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If it's not false, the checkbox will be checked
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value, like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers key - value array with replace_text - replace_with values to be replaced in template
     */
    public function addInputCheckBox(
            $id,
            $validationRules = false,
            $label = false,
            $theme = false, 
            $name = false,
            $defaultValue = false,
            array $extraAttributes = null,
            array $autoReplacers = null){
        
        if($defaultValue){
            if(!is_array($extraAttributes)){
                $extraAttributes = array();
            }
            $extraAttributes['checked'] = 'checked';
        }
        
        $this->addGeneralInput('checkbox', $id, $validationRules, $label, $theme,
                $name, $defaultValue, $extraAttributes, $autoReplacers);
        
    }
        /**
     * Inserts an input radio button, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $theme Theme of the input. Same as form theme if not specified.
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If it's not false, the radio button will be checked
     * @param boolean $checked If true, the radio button will be checked 
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value, like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers key - value array with replace_text - replace_with values to be replaced in template
     */
    public function addInputRadioButton(
            $id,
            $validationRules = false,
            $label = false,
            $theme = false, 
            $name = false,
            $defaultValue = false,
            $checked = false,
            array $extraAttributes = null,
            array $autoReplacers = null){
        
        if($checked){
            if(!is_array($extraAttributes)){
                $extraAttributes = array();
            }
            $extraAttributes['checked'] = 'checked';
        }
        
        $this->addGeneralInput('radio_button', $id, $validationRules, $label, $theme,
                $name, $defaultValue, $extraAttributes, $autoReplacers);
        
    }
    /**
     * Inserts an input password, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $theme Theme of the input. Same as form theme if not specified.
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value, like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers key - value array with replace_text - replace_with values to be replaced in template
     */
    public function addInputPassword(
            $id,
            $validationRules = false,
            $label = false,
            $theme = false, 
            $name = false,
            $defaultValue = false,
            array $extraAttributes = null,
            array $autoReplacers = null){
        
        $this->addGeneralInput('password', $id, $validationRules, $label, $theme,
                $name, $defaultValue, $extraAttributes, $autoReplacers);
        
    }
       /**
     * Inserts an input hidden, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $theme Theme of the input. Same as form theme if not specified.
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value, like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers key - value array with replace_text - replace_with values to be replaced in template
     */
    public function addInputHidden(
            $id,
            $theme = false, 
            $name = false,
            $defaultValue = false,
            array $extraAttributes = null,
            array $autoReplacers = null){
        
        $label = false;
        $validationRules = false;
        
        $this->addGeneralInput('hidden', $id, $validationRules, $label, $theme,
                $name, $defaultValue, $extraAttributes, $autoReplacers);
        
    }
    /**
     * Inserts an input textarea, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $theme Theme of the input. Same as form theme if not specified.
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value, like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers key - value array with replace_text - replace_with values to be replaced in template
     */
    public function addInputTextArea(
            $id,
            $validationRules = false,
            $label = false,
            $theme = false, 
            $name = false,
            $defaultValue = false,
            array $extraAttributes = null,
            array $autoReplacers = null){
        
        $this->addGeneralInput('textarea', $id, $validationRules, $label, $theme,
                $name, $defaultValue, $extraAttributes, $autoReplacers);
        
    }
        /**
     * Inserts an input datepicker, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $theme Theme of the input. Same as form theme if not specified.
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value, like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers key - value array with replace_text - replace_with values to be replaced in template
     */
    public function addInputDate(
            $id,
            $validationRules = false,
            $label = false,
            $theme = false, 
            $name = false,
            $defaultValue = false,
            array $extraAttributes = null,
            array $autoReplacers = null){
        
        $this->addGeneralInput('datepicker', $id, $validationRules, $label, $theme,
                $name, $defaultValue, $extraAttributes, $autoReplacers);
        
    }
         /**
     * Inserts an input datepicker, but not the datepicker js code 
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $theme Theme of the input. Same as form theme if not specified.
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value, like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers key - value array with replace_text - replace_with values to be replaced in template
     */
    public function addInputDateCustom(
            $id,
            $validationRules = false,
            $label = false,
            $theme = false, 
            $name = false,
            $defaultValue = false,
            array $extraAttributes = null,
            array $autoReplacers = null){
        
        $this->addGeneralInput('datepicker_custom', $id, $validationRules, $label, $theme,
                $name, $defaultValue, $extraAttributes, $autoReplacers);
        
    }
     /**
     * Inserts an input file image, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $theme Theme of the input. Same as form theme if not specified.
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value, like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers key - value array with replace_text - replace_with values to be replaced in template
     */
    public function addInputFileImage(
            $id,
            $validationRules = false,
            $label = false,
            $theme = false, 
            $name = false,
            $defaultValue = false,
            array $extraAttributes = null,
            array $autoReplacers = null){
        
        if($defaultValue){
            $thumb = APPLICATION_BASE_URL ."/". $defaultValue;
            $thumb = str_replace('imagesu','thumbs',$thumb);
            $autoReplacers = array("{replace_thumb_path}" => $thumb);
        }else{
            $autoReplacers = array("{replace_thumb_path}" => "");
        }
        $this->addGeneralInput('file_image', $id, $validationRules, $label, $theme,
                $name, $defaultValue, $extraAttributes, $autoReplacers);
        
    }
    /**
     * Inserts an input file, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $theme Theme of the input. Same as form theme if not specified.
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value, like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers key - value array with replace_text - replace_with values to be replaced in template
     */
    public function addInputFile(
            $id,
            $validationRules = false,
            $label = false,
            $theme = false, 
            $name = false,
            $defaultValue = false,
            array $extraAttributes = null,
            array $autoReplacers = null){
             
        $this->addGeneralInput('file', $id, $validationRules, $label, $theme,
                $name, $defaultValue, $extraAttributes, $autoReplacers);
        
    }
    
     /**
     * Inserts a select or list, insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param array $data The option value -> text array
     * @param string $theme Theme of the input. Same as form theme if not specified.
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $linkedTo Array with db_name, getvalue, gettext, searchfield, searchvalue ( see help on wiki.tauframework.com )
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value, like $myAttributeArray['onChange']="javascript:testValue();"
     * @param array $autoReplacers key - value array with replace_text - replace_with values to be replaced in template
     */
    public function addInputSelect(
            $id,
            $validationRules = false,
            $label = false,
            array $data = null,
            $theme = false, 
            $name = false,
            $defaultValue = false,
            array $linkedTo = null,
            array $extraAttributes = null,
            array $autoReplacers = null){
        
        $inputData = $this->generalInputDataFormatter($id, $validationRules,
                $label, $name, $defaultValue, $extraAttributes, $autoReplacers);
        
        $input = $this->getTemplate('select', $theme);
        $attributeReplacements = array('{replace_id}', '{replace_name}', 
            '{replace_label}', '{replace_value}', '{extra_attributes}');
        $attributeReplacers = array($inputData['id'], $inputData['name'], 
            $inputData['label'], $inputData['value'], $inputData['extraAttributesHtml']);
        $input = str_replace($attributeReplacements, $attributeReplacers, $input);
        
        if($linkedTo){
            $data = $this->getDataList($linkedTo);
        }
        
        $optionTemplate = $this->getTemplate('select_option', $theme);
        $options = "";
        foreach($data as $key => $value){
            if( $key == "" ){
                $replace_selected = " disabled selected ";
            }else if( $key == $defaultValue ){
                $replace_selected = " selected ";
            }else{
                $replace_selected = "";
            }
            $replaceLabels = array('{replace_name}', '{replace_option_value}',
                '{replace_selected}','{replace_option_text}');
            $replaceWith = array($inputData['name'], $key, $replace_selected,
                    $value);
            $options .= str_replace($replaceLabels, $replaceWith, $optionTemplate);
            $options .= "\n";
        }
        $input = str_replace('{replace_select_options}', $options, $input);
        
        $this->saveData($input, 'select', $inputData);
        
    }
    public function addInputSubmitButton($text, $theme = false){
        $this->submitValue = $text;
        $this->submitTheme = $theme;
    }
    
    public function changeFormTheme($theme){
        $this->formTheme = $theme;
    }
    /**
     * Get results from db to fill in options in select input with data
     * @param array $linkedTo Input array to get values
     * @return array $data results from db, with getvalue and gettext for options in select
     */
    protected function getDataList($linkedTo){
        
        $errData = array('err' => 'bad select operation');

        if(isset($linkedTo['db_name'])){ 
            $db_name = $linkedTo['db_name'];
        }else{
            $db_name = "";
        }
        $validOperators = array('=','<>','>','<','<=','>=');
        if(in_array($linkedTo['operator'], $validOperators)){

            $query = "select ".$linkedTo['getvalue'].",".$linkedTo['gettext'].
                " from ".$linkedTo['table']." where ".$linkedTo['searchfield'].
                " ".$linkedTo['operator']."'".$linkedTo['searchvalue']."';";
            $res = DataManager::getInstance($db_name)->getResults($query);
            if(!$res){
                return $errData;
            }else{
                
                $data = array();
                
                foreach($res as $row){
                    $data[ $row[$linkedTo['getvalue']] ] = 
                            $row[ $linkedTo['gettext'] ];
                }
                return $data;
            }

        }else{
            return $errData;
        }
    }
     /**
     * Inserts an input reCaptcha, the order of insertion is the order of appearance
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
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
        
        $html .= $this->getTemplate('form', $this->formTheme);
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
        
        TauSession::put(
                'validationFor_' . $this->formHash . "_names"
                , $names);
        
        TauSession::put(
                'validationFor_' .  $this->formHash . "_rules"
                , $validation);
        if(!$this->disableSubmit){
            $html .= $this->getSubmitButton();
        }
        
        $hidden_form_hash ='<input type="hidden" id="form_hash" '
                . 'name="form_hash" value="'.$this->formHash.'" />' . "\n";
        
        $html .= $hidden_form_hash . $this->hiddenInputs;

        $html .= "</form>\n"; 
        
        $container = $this->getTemplate('container', $containerTheme);
        
        $html = str_replace("{replace_form}",$html,$container);
        
        if( $this->allowSubmitOnEnter ){
            $preventEnter = $this->getTemplate('allow_enter', $containerTheme);
            $preventEnter = str_replace('{formid}', $this->id, $preventEnter);
        }else{
            $preventEnter = $this->getTemplate('prevent_enter', $containerTheme);
            $preventEnter = str_replace('{formid}', $this->id, $preventEnter);
        }
        
        
        $html .= "<!--googleoff: all -->\n";
        $html .= "<span style='visibility:hidden' id='rr_names_" . $this->id . "'>" . $names . "</span>\n";
        $html .= "<span style='visibility:hidden' id='rr_rules_" . $this->id . "'>" . $validation . "</span>\n";
        $html .= "<!--googleon: all -->\n";
        $html .= $preventEnter . "\n";
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

        $sendButton = $this->getTemplate('submit', $this->submitTheme);
        $sendButton = str_replace("{replace_value}", $this->submitValue, $sendButton);
        $sendButton = str_replace("{replace_id}", $this->id, $sendButton);

        return $sendButton;
    }

     /**
     * Common input data parser, to be used for all get<InputElement>() methods
     * @param string $id The html id
     * @param string $validationRules The encoded validation rules for javascript and server side, see help on validation
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
        $value = ($value)?$value:"";
        $label = ($label)?$label:"";
        $extraAttributesHtml = "";
        
        if($extraAttributes){
            foreach($extraAttributes as $attrKey => $attrValue){
                $extraAttributesHtml .= " " . $attrKey . "='" . $attrValue . "' ";
            }
        }
        
        if( $this->modelData !== false && isset($this->modelMapping[$name]) ){ 
            $value = $this->modelData[$this->modelMapping[$name]]; 
        }
        
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
