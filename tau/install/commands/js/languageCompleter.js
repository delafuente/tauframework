
var languageCompleter = (function($, undefined){
    var myVar1 = '',
    myVar2 = '';
 
    var sendForm = function(theForm){
    
        var inputList = "";
    
        $(".inputs").each(function(){
            var isNew = "true";
            if($(this).html() != ""){
                if( $(this).hasClass("notNewField") ){
                    isNew = "false";
                }
                inputList += " - " + $(this).attr("name") + " - " + $(this).attr("id") + " isNew: " + isNew + " : " + $(this).html();
            }
        });
    
    
        alert("returned " + $("#t_group").html() + " nonEmpty: " + inputList);
    };
 
    return {
        getMyVar1: function() {
            return myVar1;
        }, //myVar1 public getter
        setMyVar1: function(val) {
            myVar1 = val;
        }, //myVar1 public setter
        sendForm: sendForm
    }
})(jQuery);


//languageCompleter.sendForm("Hello World from languageCompleter module !!");