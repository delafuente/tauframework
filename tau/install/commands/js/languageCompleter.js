
var languageCompleter = (function($, undefined){

 
    var sendForm = function(theForm){
    
        var inputList = "";
        var formData = [];
        var resto = "";
        var hideButton = false;
        $(".inputs").each(function(){
            var isNew = true;
            if($(this).val() != ""){
                if( $(this).hasClass("notNewField") ){
                    isNew = false;
                }
                
                formData[formData.length] = {
                    name: $(this).attr("name"), 
                    isNew : isNew, 
                    content : $(this).val()
                };
            }else{
                resto += $(this).attr("id") + " :" + $(this).val() + ":\n";
            }
        });
        //Checks
        if($("#replace_in_local").is(":checked")){ 
            formData[formData.length] = { name: "replace_in_local", isNew : true, content: "on"  };  
        }
        if($("#create_sql").is(":checked")){ 
            formData[formData.length] = { name: "create_sql", isNew : true, content: "on"  };  
        }
        if($("#execute_sql").is(":checked")){ 
            hideButton = true;
            formData[formData.length] = { name: "execute_sql", isNew : true, content: "on"  };  
        }
        var filepath = $("input#filepath").val();
        formData[formData.length] = { name: "filepath", isNew : true, content: filepath }; 
    
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
               }else if(jsonData.execute_sql[0] == "error"){
                   $("#notifications").append("<p class='error'>: " + jsonData.execute_sql[1] + "</p>");
               }
               
               if(jsonData.replace_in_local[0] == "success"){
                   $("#notifications").append("<p class='success'>Saved in file " + filepath + "</p><textarea style='width:80%;' rows='10'>" + jsonData.replace_in_local[1] + "</textarea>");
               }else if(jsonData.replace_in_local[0] == "error"){
                   $("#notifications").append("<p class='error'>" + jsonData.replace_in_local[1] + "</p>");
               }
               
               if(jsonData.create_sql){
                   var sql = jsonData.create_sql[1];
                   for(var theQuery in sql){
                       $("#received_sql").append("<p class='constant'>" + sql[theQuery] + "</p>");
                   }
               }
               if(hideButton){
                   $('input[name=btnSubmit]').hide();
               }
               
            },
            failure: function(errMsg) {
               console.error("error:",errMsg);
               $('input[name=btnSubmit]').hide();
            }
        }
        );

    };
 
    return {
        sendForm: sendForm
    }
})(jQuery);
