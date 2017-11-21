var holidays =  [];
var dt = new Date();

jQuery(function(){ 

    var request = $.ajax({
        url: "calendar_data.php",
        type: "POST",
        data: {
            year  : dt.getFullYear(),
            month : dt.getMonth()+1
        },
        dataType: "json"
    });

    request.done(function(data ) {
        holidays = []; 
        if (data.records>0)
        {
            for (var i =0; i<data.records;i++)
            {
                holidays.push(data[i]);
            }
        }
        //------------------------------------------------------------
        $( "#calendar" ).datepicker({
            dateFormat: "dd.mm.yy",
            onSelect: function(dateText, inst) {
               //alert(dateText);  
               $("#fCalendarEdit").find("#fdate").attr('value',dateText);
            },
            onChangeMonthYear: function(year, month, inst) 
            {
                ChangeMonthYear(year, month, inst);
                dt = new Date(year, month-1, 1);
                dateText = dt.toString("dd.MM.yyyy");
                $("#fCalendarEdit").find("#fdate").attr('value',dateText);
            },
            beforeShowDay: function(dateToShow) 
            {
                if($.inArray($.datepicker.formatDate('yy-mm-dd', dateToShow ), holidays) > -1)
                {
                    return [true,'calendar_holiday',"Вихідний"];
                }        
                else
                {
                    return [true, ''];
                }
            }
    
        });
        $( "#calendar" ).datepicker( "refresh" );
        
    //------------------------------------------------------------
            
            
    });   
    request.fail(function(data ) {
        if (data.errcode!==undefined)
        {
            $('#message_zone').append(data.errstr);  
            $('#message_zone').append("<br>");                 
        }
        else
            $('#message_zone').append(data);  
            
    });

//$('.ui-datepicker-calendar a').unbind('dblclick');

//$(".ui-datepicker-calendar a").live("dblclick", function() {
//     alert('Double click!');          
//  });
  
//$(".ui-datepicker-calendar a").on('dblclick', function(event){
//    event.preventDefault();
//    alert('Double click!');
//});  
  
 var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, 
    success: FormSubmitResponse 
  };


ajaxForm = $("#fCalendarEdit").ajaxForm(form_options);

$("#fCalendarEdit").find("#fdate").attr('value',Date.now().toString("dd.MM.yyyy"));

$("#message_zone").dialog({autoOpen: false});

$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
$("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {jQuery("#message_zone").html('');});

 outerLayout = $("body").layout({
		name:	"outer" 
	,	north__paneSelector:	"#pmain_header"
	,	north__closable:	false
	,	north__resizable:	false
        ,	north__size:		40
	,	north__spacing_open:	0
	,	south__paneSelector:	"#pmain_footer"
	,	south__closable:	true
	,	south__resizable:	false
        ,	south__size:		40
	,	south__spacing_open:	5
        ,	south__spacing_closed:	3
	,	center__paneSelector:	"#pmain_center"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#dov_letter_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#dov_letter_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }

	});
        
       
        outerLayout.resizeAll();
        outerLayout.close('south');        

        jQuery(".btn").button();
}); 
 
function ChangeMonthYear(year, month, inst)
{
                    var request = $.ajax({
                    url: "calendar_data.php",
                    type: "POST",
                    data: {
                        year  : year,
                        month : month
                    },
                    dataType: "json"
                });

                request.done(function(data ) {
                    holidays = []; 
                    if (data.records>0)
                    {
                        for (var i =0; i<data.records;i++)
                        {
                            holidays.push(data[i]);
                        }
                    }
                    $( "#calendar" ).datepicker( "refresh" );
            
            
                });   
                request.fail(function(data ) {
                    if (data.errcode!==undefined)
                    {
                        $('#message_zone').append(data.errstr);  
                        $('#message_zone').append("<br>");                 
                    }
                    else
                        $('#message_zone').append(data);  
            
                });

}

function FormBeforeSubmit(formData, jqForm, options) { 

    submit_form = jqForm;

    var queryString = $.param(formData);     
    $('#message_zone').append('Вот что мы передаем:' + queryString);  
    $('#message_zone').append("<br>");                 
    /*
    var btn = '';
    for (var i=0; i < formData.length; i++) { 
        if (formData[i].name =='submitButton') { 
           btn= formData[i].value; 
           submit_form[0].oper.value = btn;
        } 
    } */
    return true;

} ;

// обработчик ответа сервера после отправки формы
function FormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) { // fill
               
               str = $("#fCalendarEdit").find("#fdate").attr('value');
               month = str.substring(3,5);
               year = str.substring(6,10);
               ChangeMonthYear(year, month, 0);
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              
               return [true,errorInfo.errstr]};              

             if (errorInfo.errcode==1) {  //set holyday
               
               str = $("#fCalendarEdit").find("#fdate").attr('value');
               month = str.substring(3,5);
               year = str.substring(6,10);
               ChangeMonthYear(year, month, 0);

               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
               
               return [true,errorInfo.errstr]};              


             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};   

};
