var session_timerId ;

jQuery(function(){ 
    
    session_timerId = setInterval(function() {
        
        var request = $.ajax({
            url: "check_session_data.php",
            type: "POST",
            data: {
            },
            dataType: "json"
        });

        request.done(function(data ) {  
            
            if (data.errcode!==undefined)
            {
                if(data.errcode>0)
                {
                  clearInterval(session_timerId);  
                  alert("Зв'язок з сервером втрачено. Обновіть сторінку, нажавши F5.");
                }
            }
            
        });

        request.fail(function(data ) {
            clearInterval(session_timerId);
            alert("Зв'язок з сервером втрачено. Обновіть сторінку, нажавши F5.");
        });        
        
    }, 20000);
    
});