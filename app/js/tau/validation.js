
    $.fn.getType = function(){ return this[0].tagName == "INPUT" ? this[0].type.toLowerCase() : this[0].tagName.toLowerCase(); }
    
    var tauValidation= (function($, undefined){
        
        
        var testToSend = function(form_id){
            var val_names = $('#rr_names_' + form_id).html();
            var val_rules = $('#rr_rules_' + form_id).html();
            //alert("val_names:[rr_names_" + form_id + "]:" + val_names + "\n" + "val_rules:" + val_rules);
            var ajaxFunctionName = $('#'+form_id+'_ajaxFunction').val();
            
            if(tauValidation.formValidate(form_id,val_names,val_rules)){
                if (ajaxFunctionName !== ''){
                    tauValidation.executeFunctionByName(ajaxFunctionName, window);
                }else{
                    $("#" + form_id).submit();
                }
                
            }
        };
        var executeFunctionByName = function(functionName, context /*, args */) {
            
            var args = Array.prototype.slice.call(arguments, 2);
            var namespaces = functionName.split(".");
            var func = namespaces.pop();
            for (var i = 0; i < namespaces.length; i++) {
                context = context[namespaces[i]];
            }
            return context[func].apply(context, args);
        };
        
        var formValidate = function(form_id,val_names,val_rules){
            
            
            
            var inputs ="";
            var inputType ="";
            var thisForm = $("#" + form_id);
            var fieldNames = val_names.split(",");
            var fieldRulesArr = val_rules.split(",");
            var fieldRules = new Array();
            var i=0;
            var hasToReturn = true;
            
            for(i=0;i<fieldNames.length;i++){
                fieldRules[fieldNames[i]]=fieldRulesArr[i];
                var aRules = new Array();
                aRules = fieldRulesArr[i].split("|");
                
                if(! tauValidation.validateField(fieldNames[i],aRules)){
                    hasToReturn = false;
                }
            
            }
            
            return hasToReturn;
        };
        
        var validateField = function(field_id,a_rules){
            
            
            var fieldValue = $("#" + field_id).val();
            var i=0;
            var initCounter =0;
            
            
            //If first val. rule is not "o" [optional]
            //then check if present or cause error.
            if(a_rules[0] != "o"){
                if(fieldValue==""){
                    tauValidation.setErrorField(field_id,"required");
                    return false;
                }
            }else{
                if(a_rules.length > 0){
                    initCounter=1;
                }
            
            }
            
            for(i=initCounter; i < a_rules.length; i++){
                var ruleName="";
                var ruleValue="";
                if(fieldValue=="" && a_rules[0]=="o"){
                    continue;
                }
                if(a_rules[i].indexOf(":") != -1){
                    var ruleArr = a_rules[i].split(":");
                    ruleName =ruleArr[0];
                    ruleValue=ruleArr[1];
                }else{
                    ruleName=a_rules[i];
                }
                
                switch(ruleName){
                    case '*':
                        var selectDisabled = false;
                        if( $("#" + field_id).getType() == 'select'){
                            $("#" + field_id + " > option").each(function() {
                            if(this != undefined){
                                if( this.selected && this.disabled ){
                                    selectDisabled = true;
                                }
                            }     
                            });
                        }
                        
                        if(fieldValue=="" || selectDisabled){
                            tauValidation.setErrorField(field_id,"required");
                            return false;
                        }
                        break;
                    //alphanumeric
                    case 'a':
                        if(!tauValidation.isAlphaNumeric(fieldValue,"_")){
                            tauValidation.setErrorField(field_id,"alphanum");
                            return false;
                        }
                        break;
                    case 'aex':
                        if(!tauValidation.isAlphaNumeric(fieldValue,"áéíóúÁÉÍÓÚäëïöüÄËÏÖÜàèìòùÀÈÌÒÙçÇ_-ß'")){
                            tauValidation.setErrorField(field_id,"alphanumex");
                            return false;
                        }
                        break;
                    //email
                    case 'e':
                        if(!tauValidation.isEmail(fieldValue)){
                            tauValidation.setErrorField(field_id,"email");
                            return false;
                        }
                        break;
                    //url
                    case 'url':
                        if(!tauValidation.isUrl(fieldValue)){
                            tauValidation.setErrorField(field_id,"url");
                            return false;
                        }
                        break;
                    //numeric
                    case 'num':
                        if(isNaN(fieldValue)){
                            tauValidation.setErrorField(field_id,"only_numeric");
                            return false;
                        }
                        break;
                    //integer
                    case 'int':
                        if(! tauValidation.isInteger(fieldValue)){
                            tauValidation.setErrorField(field_id,"only_integer");
                            return false;
                        }
                    //minimum value
                    case 'min':
                        if(parseInt(fieldValue) < parseInt(ruleValue)){
                            tauValidation.setErrorField(field_id,"min_value",ruleValue);
                            return false;
                        }
                        break;
                    //maximum value
                    case 'max':
                        if(parseInt(fieldValue) > parseInt(ruleValue)){
                            tauValidation.setErrorField(field_id,"max_value",ruleValue);
                            return false;
                        }
                        break;
                    //Minimum length
                    case 'ml':
                        if(fieldValue.length < ruleValue){
                            tauValidation.setErrorField(field_id,"not_enough_chars",ruleValue);
                            return false;
                        }
                        break;
                    //Maximum length
                    case 'Ml':
                        if(fieldValue.length > ruleValue){
                            tauValidation.setErrorField(field_id,"too_much_chars",ruleValue);
                            return false;
                        }
                        break;
                    //Equals to
                    case 'et':
                        if(fieldValue != $("#" + ruleValue).val()){
                            tauValidation.setErrorField(field_id,"not_equals_to");
                            return false;
                        }
                        break;
                    //Date #pendiente
                    case 'dt':
                        
                        
                        break;
                    //Function call
                    case 'fn':
                        var returned_val = eval(ruleValue)(fieldValue);
                        
                        if( returned_val != 'ok' ){
                            tauValidation.setErrorField(field_id,returned_val);
                            return false;
                        }
                        break;
                    default:
                        tauValidation.setErrorField(field_id,"internal_error",ruleName);
                        return false;
                        break;
                }
            
            
            }
            
            //If no previous tests exited the function, all is ok
            tauValidation.removeErrorField(field_id);
            
            return true;
        };
        
        var setErrorField = function(field_id,error_type,error_param){
            //first remove previous errors, and then, put new
            tauValidation.removeErrorField(field_id);
            
            var message = tauValidation.formatValidationMessage(field_id,error_type,error_param);
            
            $("#" + field_id).addClass(FIELD_ERROR_CLASS).after(message);
            //tauValidation.outlineRed(field_id);
        
        };
        
        var removeErrorField = function (field_id){
            var msg_id = "msgof_" + field_id;
            var p_message_ref = $("#" + msg_id);
            //tauValidation.outlineNone(field_id);
            if(p_message_ref != undefined){
                $("#" + field_id).removeClass(FIELD_ERROR_CLASS);
                p_message_ref.remove();
            }
        };
        
        var isInteger = function (s){
            var i;
            for (i = 0; i < s.length; i++){
                // Check that current character is number.
                var c = s.charAt(i);
                if (((c < "0") || (c > "9"))) return false;
            }
            // All characters are numbers.
            return true;
        };
        
        var stripCharsInBag = function (s, bag){
            var i;
            var returnString = "";
            // Search through string's characters one by one.
            // If character is not in bag, append to returnString.
            for (i = 0; i < s.length; i++){
                var c = s.charAt(i);
                if (bag.indexOf(c) == -1) returnString += c;
            }
            return returnString;
        };
        
        var isUrl = function(s) {
            var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
            return regexp.test(s);
        };
        
        var isEmail = function (str) {
            
            var at="@";
            var dot=".";
            var lat=str.indexOf(at);
            var lstr=str.length;
            var ldot=str.indexOf(dot);
            
            if (str.indexOf(at)==-1){
                return false;
            }
            if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
                return false;
            }
            if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
                return false;
            }
            if (str.indexOf(at,(lat+1))!=-1){
                return false;
            }
            if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
                return false;
            }
            if (str.indexOf(dot,(lat+2))==-1){
                return false;
            }
            if (str.indexOf(" ")!=-1){
                return false;
            }
            
            return true;
        };
        
        var isAlphaNumeric = function (str,strOtherCharsAllowed){
            //var base_allowed = "ÇÁÉÍÓÚÄËÏÖÜçáéíóúäëïöü";
            var base_allowed = "ABDCEFGHIJKLMNÑOPQRSTUVWXYZ1234567890abcdefghijklmnñopqrstuvwxyz";
            var otherChr = strOtherCharsAllowed;
            var allowed = base_allowed + otherChr;
            
            for(var j=0; j<str.length; j++){
                if(allowed.indexOf(str.charAt(j)) == -1){
                    return false;
                }
            
            }
            
            return true;
        };
        var refreshValidateFields = function (formId){
            var validation_names = $('#rr_names_' + formId ).html();
            var validation_rules = $('#rr_rules_' + formId ).html();
            
            tauValidation.formValidate(formId,validation_names,validation_rules);
        
        };
        
        var outlineRed = function (id){
            
            // $("#" + id).parent().css("border","2px solid #f00");
            $("#" + id).css("background-color","#f99");
            $("#" + id).css("color","#fff");
        
        };
        
        var outlineNone = function (id){
            
            $("#" + id).css("background-color","#fff");
            $("#" + id).css("color","#555");
        
        };
        
        var formatValidationMessage = function formatValidationMessage(fld_id,mtype,par){
            
            var msg = tau_validation[mtype];
            msg =  msg.replace("rpl_param",par);
            
            return " <span id='msgof_" + fld_id + "' class='" + SPAN_ERROR_CLASS 
                    +"' title='" + msg + "'>"+msg+"</span>";
        
        };
        return {
            testToSend: testToSend,
            formValidate : formValidate,
            validateField : validateField,
            setErrorField : setErrorField,
            removeErrorField : removeErrorField,
            isInteger : isInteger,
            stripCharsInBag : stripCharsInBag,
            isUrl : isUrl,
            isEmail : isEmail,
            isAlphaNumeric : isAlphaNumeric,
            refreshValidateFields : refreshValidateFields,
            outlineRed : outlineRed,
            outlineNone : outlineNone,
            formatValidationMessage : formatValidationMessage,
            executeFunctionByName : executeFunctionByName
        
        }
    })(jQuery);


