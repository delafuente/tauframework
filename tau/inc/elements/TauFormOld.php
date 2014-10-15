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
define('__ROOT__', str_replace("\\","/",dirname(dirname(__FILE__))) );

require_once(__ROOT__ . "/tau/inc/config.php");
require_once(__ROOT__ . '/tau/inc/recaptcha/recaptchalib.php');

class TauFormOld {
    
    protected $id;
    protected $name;
    protected $action;
    protected $divWidth; // %
    protected $divLeftMargin; //px
    protected $encType;
    protected $extraFormAttributes;

    protected $formTitle;
    
    protected $htmlBefore; //html to be prepended to the form
    protected $htmlAfter; //html to be appended to the form
    protected $tabeledForm; //boolean, if set, the output will be within a table (default), or into divs otherwise
    
    protected $elements; //array of elements
    protected $elementNames; //array with the elements' names
    protected $elementValidation; //array with the elements' validation rules
    protected $elementTypes; //string array of element types (input text, select, etc )
    protected $elementLabels; //string array with input label-for texts
    protected $elementRequiredClasses; //string array with span "required" * class, if required
    protected $elementPostInputHtml; //string array with html to put after the input, like hints or help
    protected $hiddenInputs; //String with all hidden inputs
  
    
    protected $mainContainerDivId;
    protected $mainContainerDivName;
    protected $mainContainerDivClasses;
    protected $elementsWidth; //Forced width of inputs 
    protected $submitValue; //Submit button text
    /**
     * Creates a new Form object
     * @param string $id The html id
     * @param string $action The form action value
     * @param string $name The html name, default is equals to id
     * @param string $enctype The form enctype
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value,
     * like $myAttributeArray['accept']="jpg,png"
     */
    public function __construct($id,$action,$name=false,$enctype='multipart/form-data',$extraAttributes=false){
        
        $this->divWidth=70; //default hardcoded
        $this->divLeftMargin=0; //default hardcoded
        
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
        $this->tabeledForm = true;
        $this->formTitle = $name;
        $this->hiddenInputs = "";

        $this->mainContainerDivId = "container_" . $this->name;
        $this->mainContainerDivName = $this->mainContainerDivId;
        $this->mainContainerDivClasses = false;
        
        $this->elements = array();
        $this->elementTypes = array();
        $this->elementNames = array();
        $this->elementValidation = array();
        $this->elementLabels = array();
        $this->elementRequiredClasses = array();
        $this->elementPostInputHtml = array();
        $this->submitValue = "Enviar";
        $this->elementsWidth = 300; //forced width of inputs
    }

    public function setSubmitButtonText($text){
        $this->submitValue = $text;
    }
    public function changeElementsWidth($width){
        $this->elementsWidht = $width;
    }
    /**
     * Css width percentaje of container div
     * @param int $divWidth The % of width of container div
     */
    public function setDivWidth($divWidth){
        $this->divWidth = $divWidth;
    }
    /**
     * Css left margin pixels of container div
     * @param int $leftMarginDiv Left-margin div css property in pixels
     */
    public function setLeftMarginDiv($leftMarginDiv){
        $this->divLeftMargin = $leftMarginDiv;
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
     * @param string $label The 'label for' text of the element, like "Insert your e-mail"
     * @param string $requiredClass If not false, adds an * after the label in a span with this class or space separated classes.
     * @param string $postInputHtml Html to be written just after the input, like help or hint
     * @param string $name The html name, if false (default) it's equals to id. Must be unique.
     * @param string $cssClasses The css classes, space separated
     * @param string $defaultValue The input default value. If you want a 'placeholder' attribute, use the extraAttributes array.
     * @param array $extraAttributes Array string with key=form attribute name, value=form attribute value,
     * like $myAttributeArray['onChange']="javascript:testValue();" or $myAttributeArray['placeholder']='Enter your e-mail'
     */
    public function addInputText($id,$validationRules=false,$label=false,$requiredClass=false,
            $postInputHtml=false,$name=false,$cssClasses=false,$defaultValue=""
            ,$extraAttributes=false){
        
        $inputData = $this->generalInputDataFormatter($id, $validationRules,
                $label, $requiredClass, $postInputHtml, $name, $cssClasses,
                $defaultValue, $extraAttributes);

        $input = "\t<input type='text' id='" .$inputData['id']. "' name='".
        $inputData['name']."' ";

        $input .= $inputData['classes'] . $inputData['value'];
        $input .= $inputData['extraAttributesHtml'];
        $input .= " style='width:" . $this->elementsWidth . "px'";
        $input .= " />\n";
        
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
        $input .= " style='width:" . $this->elementsWidth . "px'";
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
        $input .= " style='width:" . $this->elementsWidth . "px'";
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
        $input .= " style='width:" . $this->elementsWidth . "px'";
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

        /*$input = "\t<input type='password' id='" .$inputData['id']. "' name='".
        $inputData['name']."' ";

        $input .= $inputData['classes'] . $inputData['value'];
        $input .= $inputData['extraAttributesHtml'];
        $input .= " style='width:" . $this->elementsWidth . "px'";
        $input .= " />\n";*/

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
     * The form will be output contained in divs
     */
    public function setDivOutput(){
        $this->tabeledForm = false;
    }
    /**
     * The form will be output contained in a table
     */
    public function setTableOutput(){
        $this->tabeledForm = true;
    }
    /**
     * Overrides the main container data
     * @param string $id The html id of the div
     * @param string $name The html name of the div
     * @param string $classes The space separated css classes of the div
     */
    public function setMainContainerDiv($id,$name=false,$classes=false){
        $this->mainContainerDivId = $id;
        $this->mainContainerDivClasses = $classes;
        if($name){
            $this->mainContainerDivName = $name;
        }
    }
    
    /**
     * Get the constructed form in html format
     * @return string The html form
     */
    public function toString(){
        //debug
        //$container = "<textarea name='testtextarea' cols='130' rows='50' style='width:100%'>";
        //$end_container = "</textarea>";
        //end debug
        $html = $this->htmlBefore . "\n";
        
        $html .= "<div id=\"" . $this->mainContainerDivId . "\" name=\"" .
                $this->mainContainerDivName . "\" ";
        if($this->mainContainerDivClasses){ 
            $html .= " class=\"" . $this->mainContainerDivClasses .
                "\" ";
        }
        $html .= " style=\"width:" . $this->divWidth . "%;margin-left:" .
                $this->divLeftMargin . "px;\" >\n"; //End of outer div signature

        $html .= '<form id="' . $this->id . '" name="' . $this->name . '" '.
                'action="' . $this->action . '" method="post" ' .
                'enctype="' . $this->encType . '" ';

        if($this->extraFormAttributes){
            foreach($this->extraFormAttributes as $attrName => $attrValue){
                $html .= ' ' . $attrName . '="' . $attrValue . '" ';
            }
        }
        $html .= " >\n"; //End of form signature
        
        if($this->tabeledForm){
            $html .= "\t<table>\n";
            $html .= "<tr><th colspan='2'>" . $this->formTitle . "</th></tr>";
            $closeTable = "\t</table>\n";
        }else{
            $html .= "<span>" . $this->formTitle . "</span>";
            $closeTable = "";
        }
        
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
            $html .= $this->getElement($i);
        }

        $html .= $this->getSubmitButton();
        
        $html .= $closeTable;
        $html .= "</form>\n</div>\n"; //main div container end
        $html .= "<!--googleoff: all -->\n";
        $html .= "<span style='visibility:hidden' id='rr_names_" . $this->id . "'>" . $names . "</span>\n";
        $html .= "<span style='visibility:hidden' id='rr_rules_" . $this->id . "'>" . $validation . "</span>\n";
        $html .= "<!--googleon: all -->\n";
        $html .= $this->htmlAfter . "\n";
        
        //$html = $container .  $html . $end_container . "<br/>" . $html;
        
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
        
        $tdLeftAlign = "";
        if($this->elementTypes[$counter]=='checkbox'){
            $tdLeftAlign = " style='text-align:left;' ";
        }
        if($this->elementTypes[$counter]=='hidden'){
            $this->hiddenInputs .= $this->elements[$counter] . "\n";
            return "";
        }
        $html ="";
        if($this->tabeledForm){
            $beginRow = "\t\t<tr><td>\n";
            $innerRow = "\t\t</td><td$tdLeftAlign>\n";
            $endRow = "\n\t\t</td></tr>\n\n";
        }else{
            $beginRow = "\t\t<span class='beginRowForm'>\n";
            $innerRow = "\t\t</span><span class='endRowForm'>\n";
            $endRow = "\t\t</span>\n";
        }
        
        if($this->elementRequiredClasses[$counter]){
            $required = "<span class='" . $this->elementRequiredClasses[$counter] . "'> *</span>";
        }else{
            $required = "";
        }
        
        $html .= $beginRow . " \t<label for='" . $this->elementNames[$counter] . "'>";
        $html .= $this->elementLabels[$counter] . $required . "</label> ";
        $html .= $innerRow;
        $html .= $this->elements[$counter];
        if($this->elementPostInputHtml[$counter]){
            $html .= $this->elementPostInputHtml[$counter];
        }
        $html .= $endRow;
        
        
        return $html;
    }
    /**
     * Obtains a formatted submit button
     * @return string The html for the element
     */
    protected function getSubmitButton(){

        $html ="";
        if($this->tabeledForm){
            $beginRow = "\t\t<tr><td></td><td style='padding-top:20px;text-align: center;padding-right: 56px;'>\n";
            $innerRow = "";
            $endRow = "\n\t\t</td></tr>\n\n";
        }else{
            $beginRow = "\t\t<span class='beginRowForm'>\n";
            $innerRow = "\t\t</span><span class='endRowForm'>\n";
            $endRow = "\t\t</span>\n";
        }

        $hidden_form_hash ='<input type="hidden" id="form_hash" name="form_hash" value="replace_form_hash" />' . "\n";
        $sendButton='<input id="sendButton" name="sendButton" type="button"' .
        'value="' . $this->submitValue  .'" style="float:right;top:10px;" class="button highlight" onclick="javascript:tauValidation.testToSend(\'' .
                $this->id . '\');"/> <br/>' . "\n";
    
        $html = $beginRow . $innerRow . $hidden_form_hash . $this->hiddenInputs . $sendButton . $endRow;

        return $html;
    }

     /**
     * Common input data parser, to be used for all get<InputElement>() methods
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
     * @param string $typeInput The type of the input if needed ( like 'hidden' )
     * @return array Associative array with all formatted data, with the exception of defaultValue, returned as is for textarea like to use
     */
    protected function generalInputDataFormatter($id,$validationRules,$label,$requiredClass,$postInputHtml,
        $name,$cssClasses,$defaultValue,$extraAttributes,$isHidden=false){

        $requiredClass = ($requiredClass)?$requiredClass:"";
        $postInputHtml = ($postInputHtml)?$postInputHtml:"";
        $validationRules = ($validationRules)?$validationRules:"o";
        $name = ($name)?$name:$id;
        $classes = ($cssClasses)?" class='" . $cssClasses . "' ":"";
        $value = ($defaultValue)?" value='" . $defaultValue . "' ":"";
        $label = ($label)?$label:"";
        if($extraAttributes){
            foreach($extraAttributes as $attrKey => $attrValue){
                $extraAttributesHtml .= " " . $attrKey . "='" . $attrValue . "' ";
            }
        }
        $returnData = array();
        $returnData['id']=$id;
        $returnData['validationRules']=$validationRules;
        $returnData['label']=$label;
        $returnData['requiredClass']=$requiredClass;
        $returnData['postInputHtml']=$postInputHtml;
        $returnData['name']=$name;
        $returnData['classes']=$classes;
        $returnData['value']=$value;
        $returnData['defaultValue']=$defaultValue; //for use in textarea, for example.
        $returnData['extraAttributesHtml']=$extraAttributesHtml;

        return $returnData;
    }
    protected function saveData($input,$type,$inputData){
        $this->elementNames[] = $inputData['id']; //IMPORTANT: This is for validation, we need the element id here, not the name
        $this->elementTypes[] = $type;
        $this->elementValidation[] = $inputData['validationRules'];
        $this->elements[] = $input;
        $this->elementPostInputHtml[] = $inputData['postInputHtml'];
        $this->elementRequiredClasses[] = $inputData['requiredClass'];
        $this->elementLabels[] = $inputData['label'];
    }



}

?>
