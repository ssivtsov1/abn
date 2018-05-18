var submit_form;
var form_edit_lock = 0;
var form_options = { 
    dataType:"json",
    beforeSubmit: ResBeforeSubmit, 
    success: ResSubmitResponse 
  };


jQuery(function(){ 

   

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
	,	center__paneSelector:	"#pmain_content"
	//,	center__onresize:		'innerLayout.resizeAll'
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
   //     ,	center__onresize:   $.layout.callbacks.resizeTabLayout        
	});
    
    $( "#pwork_center" ).tabs({
      //  show: $.layout.callbacks.resizeTabLayout        
    });
    outerLayout.close('south');             
    
    jQuery(".btn").button();
    jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});
    jQuery("#fResEdit :input").addClass("ui-widget-content ui-corner-all");
    jQuery("#dialog-newmeterzone :input").addClass("ui-widget-content ui-corner-all");
    
    
    $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
    jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

    jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
    jQuery(".dtpicker").mask("99.99.9999");
    
        
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   
   $("#message_zone").dialog({autoOpen: false});


$.ajaxSetup({type: "POST",   dataType: "json"});

var fResEdit_ajaxForm = $("#fResEdit").ajaxForm(form_options); 

// опции валидатора общей формы
var form_valid_options = { 
//                errorPlacement: function(error, element) {
//				error.appendTo( element.parent("label").parent("div"));
//                },
		rules: {
                        code:"required",
                        short_name: "required"
                        
		},
		messages: {
                        code:"Вкажіть код",
                        short_name: "Вкажіть назву підрозділа"
                        
		}
};

validator = $("#fResEdit").validate(form_valid_options);

$.ajaxSetup({type: "POST",   dataType: "json"});

jQuery("#fid_res_sel").change( function() { 

    id_res = jQuery("#fid_res_sel").val();

     var request = $.ajax({
         url: "res_params_data.php",
        data: {id_res : id_res},
        type: "POST",
        dataType: "json"});

     request.done(function(data ) {
         LoadResData(data);
    });
    request.fail(function(data ) {alert("error");});

  }); 


$("#fid_res_sel").attr('value', id_res);
$("#fid_res_sel").change();


$("#fResEdit").find("#bt_reset").click( function() 
{
      //self.close();    
      ResetJQFormVal($("#fResEdit"));
});


    jQuery("#btAddrSel").click( function() { 
        SelectAdrTarget='#faddr';
        SelectAdrStrTarget='#faddr_full';

        $("#fadr_sel_params_address").attr('value', $("#fResEdit").find("#faddr").val() );    
        $("#fadr_sel_params_select_mode").attr('value', 1 );    
    
        // $("#fadr_sel_params").attr('target',"_blank" );           
        var ww = window.open("adr_tree_selector.php", "adr_win", "toolbar=0,width=770,height=500");
        document.adr_sel_params.submit();
        ww.focus();
    });
    
    jQuery("#btAddrDistrictSel").click( function() { 

        $("#fadr_sel_params_address").attr('value', '0' );    
        $("#fadr_sel_params_select_mode").attr('value', 2 );    
    
        // $("#fadr_sel_params").attr('target',"_blank" );           
        var ww = window.open("adr_tree_selector.php", "adr_win", "toolbar=0,width=770,height=500");
        document.adr_sel_params.submit();
        ww.focus();
    });
    

    jQuery("#btBossSel").click( function() { 

        SelectPersonTarget='#fid_boss';
        SelectPersonStrTarget='#fboss_name';

        if ($("#fResEdit").find("#fid_boss").val()!='')
            $("#fperson_sel_params_id_person").attr('value', $("#fResEdit").find("#fid_boss").val() );    
        else
            $("#fperson_sel_params_id_person").attr('value', '0' );    
    
        var www = window.open("staff_list.php", "person_win", "toolbar=0,width=900,height=600");
        document.person_sel_params.submit();
        www.focus();
    });

    jQuery("#btSbutSel").click( function() { 

        SelectPersonTarget='#fid_sbutboss';
        SelectPersonStrTarget='#fsbutboss_name';

        if ($("#fResEdit").find("#fid_sbutboss").val()!='')
            $("#fperson_sel_params_id_person").attr('value', $("#fResEdit").find("#fid_sbutboss").val() );    
        else
            $("#fperson_sel_params_id_person").attr('value', '0' );    
    
        var www = window.open("staff_list.php", "person_win", "toolbar=0,width=900,height=600");
        document.person_sel_params.submit();
        www.focus();
    });

    jQuery("#btWarningSel").click( function() { 

        SelectPersonTarget='#fid_warningboss';
        SelectPersonStrTarget='#fwarningboss_name';

        if ($("#fResEdit").find("#fid_warningboss").val()!='')
            $("#fperson_sel_params_id_person").attr('value', $("#fResEdit").find("#fid_warningboss").val() );    
        else
            $("#fperson_sel_params_id_person").attr('value', '0' );    
    
        var www = window.open("staff_list.php", "person_win", "toolbar=0,width=900,height=600");
        document.person_sel_params.submit();
        www.focus();
    });

    jQuery("#btSpravSel").click( function() { 

        SelectPersonTarget='#fid_spravboss';
        SelectPersonStrTarget='#fspravboss_name';

        if ($("#fResEdit").find("#fid_spravboss").val()!='')
            $("#fperson_sel_params_id_person").attr('value', $("#fResEdit").find("#fid_spravboss").val() );    
        else
            $("#fperson_sel_params_id_person").attr('value', '0' );    
    
        var www = window.open("staff_list.php", "person_win", "toolbar=0,width=900,height=600");
        document.person_sel_params.submit();
        www.focus();
    });

    jQuery("#btBuhSel").click( function() { 

        SelectPersonTarget='#fid_buh';
        SelectPersonStrTarget='#fbuh_name';

        if ($("#fResEdit").find("#fid_buh").val()!='')
            $("#fperson_sel_params_id_person").attr('value', $("#fResEdit").find("#fid_buh").val() );    
        else
            $("#fperson_sel_params_id_person").attr('value', '0' );    
    
        var www = window.open("staff_list.php", "person_win", "toolbar=0,width=900,height=600");
        document.person_sel_params.submit();
        www.focus();
    });


    jQuery("#btBankSel").click( function() { 

        if ($("#fResEdit").find("#fae_mfo").val()!='')
            $("#fbank_sel_params_mfo").attr('value', $("#fResEdit").find("#fae_mfo").val() );    
        else
            $("#fbank_sel_params_mfo").attr('value', '0' );    
    
        var www = window.open("dov_banks.php", "bank_win", "toolbar=0,width=900,height=600");
        document.bank_sel_params.submit();
        www.focus();
    });


    if (r_edit!=3)
        $("#fResEdit").find("#bt_edit").prop('disabled', true);



  $('#fResEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fResEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 

});


function SelectPersonExternal(id, name) {
    
        $(SelectPersonTarget).attr('value',id );
        $(SelectPersonStrTarget).attr('value',name );    
    
}


function SelectAddrExternal(code, name) {
    
        $(SelectAdrTarget).attr('value',code );
        $(SelectAdrStrTarget).attr('value',name );    
    
} 

function SelectAddrClassExternal(code, name) {
    
        $('#faddr_district').attr('value',code );
        $('#faddr_district_name').attr('value',name );    
    
}  


function SelectBankExternal(code, name) {
    
        $("#fae_mfo").attr('value',code );
        $("#fae_bank_name").attr('value',name );    
    
} 


function LoadResData(data)
{
  //   var str = $.param(data); 
  //alert(str); 
  if (data.errcode===undefined)
  {    
    $("#fResEdit").resetForm();
    $("#fResEdit").clearForm();
      
 
    $("#fResEdit").find("#fid").attr('value',data.id_department);
    $("#fResEdit").find("#fcode").attr('value',data.code);
     
    $("#fResEdit").find("#fname").attr('value', data.name);
    $("#fResEdit").find("#fshort_name").attr('value', data.short_name);
    $("#fResEdit").find("#faddr").attr('value', data.addr); 
    $("#fResEdit").find("#faddr_full").attr('value', data.addr_full); 

    $("#fResEdit").find("#fid_boss").attr('value', data.id_boss); 
    $("#fResEdit").find("#fid_buh").attr('value', data.id_buh); 
    $("#fResEdit").find("#fboss_name").attr('value',data.boss_name ); 
    $("#fResEdit").find("#fbuh_name").attr('value',data.buh_name ); 

    $("#fResEdit").find("#fid_sbutboss").attr('value', data.id_sbutboss); 
    $("#fResEdit").find("#fsbutboss_name").attr('value',data.sbutboss_name ); 

    $("#fResEdit").find("#fid_warningboss").attr('value', data.id_warningboss); 
    $("#fResEdit").find("#fwarningboss_name").attr('value',data.warningboss_name ); 

    $("#fResEdit").find("#fid_spravboss").attr('value', data.id_spravboss); 
    $("#fResEdit").find("#fspravboss_name").attr('value',data.spravboss_name ); 

    $("#fResEdit").find("#fprint_name").attr('value',data.print_name ); 
    $("#fResEdit").find("#fsmall_name").attr('value',data.small_name ); 
    $("#fResEdit").find("#fwarning_addr").attr('value',data.warning_addr ); 

    $("#fResEdit").find("#fae_mfo").attr('value',data.ae_mfo ); 
    $("#fResEdit").find("#fae_account").attr('value',data.ae_account ); 
    $("#fResEdit").find("#fae_bank_name").attr('value',data.ae_bank_name ); 
    
    $("#fResEdit").find("#flicens_num").attr('value',data.licens_num ); 
    $("#fResEdit").find("#fokpo_num").attr('value',data.okpo_num ); 
    $("#fResEdit").find("#ftax_num").attr('value',data.tax_num ); 
    
    $("#fResEdit").find("#fphone_bill").attr('value',data.phone_bill ); 
    $("#fResEdit").find("#fphone_warning").attr('value',data.phone_warning ); 
    $("#fResEdit").find("#faddr_ikc").attr('value',data.addr_ikc );     
    $("#fResEdit").find("#fphone_ikc").attr('value',data.phone_ikc );     
    
    $("#fResEdit").find("#faddr_district").attr('value',data.addr_district );     
    $("#fResEdit").find("#faddr_district_name").attr('value',data.addr_district_name );     
    
    if (data.barcode_print=="1")
    {
      $("#fResEdit").find("#fbarcode_print").prop('checked',true);
    }
    else
    {
      $("#fResEdit").find("#fbarcode_print").prop('checked',false);
    }    

    if (data.qr_print=="1")
    {
      $("#fResEdit").find("#fqr_print").prop('checked',true);
    }
    else
    {
      $("#fResEdit").find("#fqr_print").prop('checked',false);
    }    

    CommitJQFormVal($("#fResEdit"));
  }
  else
  {
    $('#message_zone').append(data.errstr);  
    $('#message_zone').append("<br>");                 
    jQuery("#message_zone").dialog('open');
  }
};
//----------------------------------------------------------------------------
function ResetJQFormVal(form)
{
  form.find('[data_old_value]').each(function() {
        var vlastValue = $(this).attr('data_old_value');
        $(this).attr('value',vlastValue);
        $(this).focus();
  });
        
  form.find('[data_old_checked]').each(function() {
        var vlastValue = $(this).attr('data_old_checked');
        //alert(vlastValue);
        if (vlastValue=='true')
        {
          $(this).prop('checked',true);
        }
        else
        {
          $(this).prop('checked',false);
        }    
    
 });
};

function CommitJQFormVal(form)
{
   form.find('[data_old_value]').each(function() {
            var vlastValue = $(this).attr('value');
             $(this).attr('data_old_value',vlastValue);  
             //alert($(this).attr('data_old_value'));             
   });
        
   form.find('[data_old_checked]').each(function() {
            var vlastValue = $(this).prop('checked');
             $(this).attr('data_old_checked',vlastValue);  
   });    
}; 
//-----------------------------------------------------------------------------
function ResBeforeSubmit(formData, jqForm, options) { 

    submit_form = jqForm;

    var queryString = $.param(formData);     
    //$('#message_zone').append('Вот что мы передаем:' + queryString);  
    //$('#message_zone').append("<br>");                 
    
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
               jQuery("#dialog-confirm").find("#dialog-text").html('Записати?'); 
                    $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Збереження параметрів',
			buttons: {
				"Ok": function() {
                                        SaveResChanges();
                                        form_edit_lock=1;
					$( this ).dialog( "close" );
				},
				"Отмена": function() {
                                        ResetJQFormVal($("#fResEdit"));
                                        
					$( this ).dialog( "close" );
				}
			}

                });
                
                $("#dialog-confirm").dialog('open');
                return false; 
                
            }
            else
                {
                    form_edit_lock=1;
                    return true;
                }

       }
    }
    else {return true;}       
    //}
    
} ;

// обработчик ответа сервера после отправки формы
function ResSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 
             
             if (errorInfo.errcode==1) {
               
                                           
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
  
  
               //window.opener.RefreshMetersExternal();
               //window.opener.focus();
               //self.close();            
  
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};   

};

function SaveResChanges()
{
  //var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
  
  //submit_form[0].change_date.value = cur_dt_change;
  submit_form.ajaxSubmit(form_options);         
    
};
