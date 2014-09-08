
var languageCompleter = (function($, undefined){
    var myVar1 = '',
    myVar2 = '';
 
    var sendForm = function(theForm){
    
        var inputList = "";
        var formData = [];
        var resto = "";
        $(".inputs").each(function(){
            var isNew = true;
            if($(this).val() != ""){
                if( $(this).hasClass("notNewField") ){
                    isNew = false;
                }
                inputList += " - " + $(this).attr("name") + " - " + $(this).attr("id") + " isNew: " + isNew + " : " + $(this).val();
                formData[formData.length] = {
                    name: $(this).attr("name"), 
                    isNew : isNew, 
                    content : $(this).val()
                };
            }else{
                resto += $(this).attr("id") + " :" + $(this).val() + ":\n";
            }
        });
        //alert("Empty? : " + new Date().getTime() + "\n" + resto);
        //Checks
        if($("#replace_in_local").is(":checked")){ 
            formData[formData.length] = { name: "replace_in_local", isNew : true, content: "on"  };  
        }
        if($("#create_sql").is(":checked")){ 
            formData[formData.length] = { name: "create_sql", isNew : true, content: "on"  };  
        }
        if($("#execute_sql").is(":checked")){ 
            formData[formData.length] = { name: "execute_sql", isNew : true, content: "on"  };  
        }
    
        //var dataString = jQuery("#frmLangCompleter").serialize();
        var dataString = JSON.stringify(formData);
        $("#notifications").html("");
        $("#received_sql").html("");
        $.ajax(
        {
            url: './ajax_languageCompleter.php',
            type: "post",
            data: { frmData : dataString },

            dataType: "json",			
            success: function( jsonData ){                
               console.log("success:",jsonData);
               
               if(jsonData.execute_sql[0] == "success"){
                   $("#notifications").append("<p class='success'>" + jsonData.execute_sql[1] + "</p>");
               }else{
                   $("#notifications").append("<p class='error'>" + jsonData.execute_sql[1] + "</p>");
               }
               
               if(jsonData.create_sql){
                   var sql = jsonData.create_sql[1];
                   for(var theQuery in sql){
                       $("#received_sql").append("<p class='constant'>" + sql[theQuery] + "</p>");
                   }
               }
               
            },
            failure: function(errMsg) {
               console.error("error:",errMsg);
            }
        }
        );
    
        //alert("returned " + $("#t_group").html() + " nonEmpty: " + inputList);
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