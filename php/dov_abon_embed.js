var edit_abon_id = 0;
var abon_validator = null;
var fAbonEdit_ajaxForm = null;
var abon_id_name='';
var abon_name_name='';
var form_edit_lock=0;

 var abon_form_options = { 
    dataType:"json",
    beforeSubmit: AbonFormBeforeSubmit, 
    success: AbonFormSubmitResponse 
  };

jQuery(function(){ 
    


//-----------------------------------------


$("#dialog_editAbonform").dialog({
			resizable: true,
		//	height:140,
                        width:800,
			modal: true,
                        autoOpen: false,
                        title:'Фізична особа'
});


fAbonEdit_ajaxForm = $("#fAbonEdit").ajaxForm(abon_form_options);

jQuery("#fAbonEdit :input").addClass("ui-widget-content ui-corner-all");

// опции валидатора общей формы
var abon_form_valid_options = { 

		rules: {
			name: "required",
			last_name: "required",                        
			//patron_name: "required",
                        dt_b: "required"
		},
		messages: {
			name: "Вкажіть ім'я!",
			last_name: "Вкажіть прізвище!",
                       // patron_name: "Вкажіть по батькові",
                        dt_b: "Вкажіть дату"
		}
};

abon_validator = $("#fAbonEdit").validate(abon_form_valid_options);



$("#fAbonEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editAbonform").dialog('close');                           
});

//-----------------------------------------------------------------
jQuery("#btAddr1Sel").click( function() { 
        SelectAdrTarget='#faddr_reg';
        SelectAdrStrTarget='#faddr_reg_str';

        $("#fadr_sel_params_address").attr('value', $("#fAbonEdit").find("#faddr_reg").val() );    
    
        //$("#fadr_sel_params").attr('target',"_blank" );           
        var ww = window.open("adr_tree_selector.php", "adr_win", "toolbar=0,width=770,height=500");
        document.adr_sel_params.submit();
        ww.focus();
});

jQuery("#btAddr2Sel").click( function() { 
        SelectAdrTarget='#faddr_live';
        SelectAdrStrTarget='#faddr_live_str';

        $("#fadr_sel_params_address").attr('value', $("#fAbonEdit").find("#faddr_live").val() );    
    
        //$("#fadr_sel_params").attr('target',"_blank" );           
        var ww = window.open("adr_tree_selector.php", "adr_win", "toolbar=0,width=770,height=500");
        document.adr_sel_params.submit();
        ww.focus();
});

jQuery("#btAddr2Copy").click( function() { 
    
        $("#fAbonEdit").find('#faddr_live').attr('value',$("#fAbonEdit").find('#faddr_reg').attr('value') );
        $("#fAbonEdit").find('#faddr_live_str').attr('value',$("#fAbonEdit").find('#faddr_reg_str').attr('value') );    
});

//----------------------------------------


}); 
 
// обработчик, который вызываетя перед отправкой формы
function AbonFormBeforeSubmit(formData, jqForm, options) { 

    submit_form = jqForm;

    var queryString = $.param(formData);     
    $('#message_zone').append('Вот что мы передаем:' + queryString);  
    $('#message_zone').append("<br>");                 
    
    var btn = '';
    for (var i=0; i < formData.length; i++) { 
        if (formData[i].name =='submitButton') { 
           btn= formData[i].value; 
           submit_form[0].oper.value = btn;
        } 
    } 

    if((btn=='edit')||(btn=='add'))
    {
       if (form_edit_lock == 1) return false; 
       if(!submit_form.validate().form())  {return false;}
       else {
        if (btn=='edit')
            {
                
               $("#dialog-changedate").dialog({ 
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
			buttons: {
				"Ok": function() {
                                          var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
  
                                          submit_form[0].change_date.value = cur_dt_change;
                                          submit_form.ajaxSubmit(abon_form_options); 
                                          form_edit_lock=1;
					$( this ).dialog( "close" );
				},
				"Отмена": function() {
					$( this ).dialog( "close" );
				}
			}

                });
                
                $("#dialog-changedate").dialog('open');
                return false; 
                
            }
            else
                {return true;}        
       }
    }
    else {return true;}       
    //}
    
} ;

// обработчик ответа сервера после отправки формы
function AbonFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_editAbonform").dialog('close');                           
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
              
               jQuery(abon_id_name).attr('value', errorInfo.id);
               jQuery(abon_name_name).attr('value', errorInfo.errstr);
               
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               jQuery("#dialog_editAbonform").dialog('close');                                            
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};   
};


function ShowAbon( id_abon){ 


    var request = $.ajax({
        url: "dov_abon_embed_data.php",
        type: "POST",
        data: {
            id : id_abon
        },
        dataType: "json"
    });

    request.done(function(data ) {
     
        abon_validator.resetForm();  //для сброса состояния валидатора
        $("#fAbonEdit").resetForm();
        $("#fAbonEdit").clearForm();
          
        //jQuery(this).jqGrid('GridToForm',gsr,"#fAbonEdit"); 
        $("#fAbonEdit").find("#fid").attr('value',data.id );
        $("#fAbonEdit").find("#foper").attr('value', 'edit');
            
        $("#fAbonEdit").find("#flast_name").attr('value', data.last_name);
        $("#fAbonEdit").find("#fname").attr('value', data.name);
        $("#fAbonEdit").find("#fpatron_name").attr('value', data.patron_name);
            
        $("#fAbonEdit").find("#faddr_reg").attr('value', data.addr_reg);
        $("#fAbonEdit").find("#faddr_reg_str").attr('value', data.addr_reg_str);
        $("#fAbonEdit").find("#faddr_live").attr('value', data.addr_live);
        $("#fAbonEdit").find("#faddr_live_str").attr('value', data.addr_live_str);
            
        $("#fAbonEdit").find("#fs_doc").attr('value', data.s_doc);
        $("#fAbonEdit").find("#fn_doc").attr('value', data.n_doc);
        $("#fAbonEdit").find("#fwho_doc").attr('value', data.who_doc);
            
        $("#fAbonEdit").find("#fdt_doc_abon").datepicker( "setDate" , data.dt_doc );
            
        $("#fAbonEdit").find("#ftax_number").attr('value', data.tax_number);
        $("#fAbonEdit").find("#fhome_phone").attr('value', data.home_phone);
        $("#fAbonEdit").find("#fwork_phone").attr('value', data.work_phone);
        $("#fAbonEdit").find("#fmob_phone").attr('value', data.mob_phone);
        $("#fAbonEdit").find("#fe_mail").attr('value', data.e_mail);
            
        $("#fAbonEdit").find("#fnote").attr('value', data.note);
            
        $("#fAbonEdit").find("#fdt_b_abon").datepicker( "setDate" , data.dt_b );
        $("#fAbonEdit").find("#fdt_input_abon").datepicker( "setDate" , data.dt_input );
      
        $("#fAbonEdit").find("#bt_add").hide();
        $("#fAbonEdit").find("#bt_edit").show();   
        jQuery("#dialog_editAbonform").dialog('open');          
     
        if (r_abonedit==3)
        {
           $("#fAbonEdit").find("#bt_edit").prop('disabled', false);
        }
        else
        {
           $("#fAbonEdit").find("#bt_edit").prop('disabled', true);
        }
     
    });
    request.fail(function(data ) {
        alert("error");
    });

} 


function ShowEmptyAbon(){ 

    abon_validator.resetForm();
    $("#fAbonEdit").resetForm();
    $("#fAbonEdit").clearForm();
          
    edit_abon_id = -1;
    $("#fAbonEdit").find("#fid").attr('value',-1 );    
          
    $("#fAbonEdit").find("#bt_add").show();
    $("#fAbonEdit").find("#bt_edit").hide();            
    jQuery("#dialog_editAbonform").dialog('open');          
    
} 
