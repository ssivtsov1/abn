var edit_row_id = 0;
var validator = null;
var form_edit_lock=0;
var fEdit_ajaxForm = null;
var form_options;

jQuery(function(){ 
    
  if(r_edit==3)
      r_edit_bool = true;
  else
      r_edit_bool = false;    
    
  jQuery('#dov_gek_table').jqGrid({
    url:'dov_gek_data.php',
    editurl: 'dov_gek_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:500,
    width:800,
    scroll: 0,
    colNames:['Код','Дім', 'Номер жека','addr','Адреса'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text'},           
      {name:'num_gek', index:'num_gek', width:80, editable: true, align:'right',hidden:false,edittype:'text'},
      {name:'addr', index:'addr', width:50, editable: true, align:'right',hidden:true,edittype:'text'},           
      {name:'addr_str', index:'addr_str', width:200, editable: true, align:'left',edittype:'text'}
    ],
    pager: '#dov_gek_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'id',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'ЖЕКи',
    hidegrid: false,
    
   onSelectRow: function(rowid) { 
        edit_row_id = rowid; 
    },
    
    ondblClickRow: function(id){ 
      //jQuery(this).editGridRow(id,{width:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
          validator.resetForm();  //для сброса состояния валидатора
          $("#fBuildEdit").resetForm();
          $("#fBuildEdit").clearForm();
          
          jQuery(this).jqGrid('GridToForm',gsr,"#fBuildEdit"); 
          edit_row_id = id;

          $("#fBuildEdit").find("#bt_add").hide();
          $("#fBuildEdit").find("#bt_edit").show();   
          jQuery("#dialog_editform").dialog('open');     
          
          if (r_edit==3)
               $("#fBuildEdit").find("#bt_edit").prop('disabled', false);
          else
               $("#fBuildEdit").find("#bt_edit").prop('disabled', true);
          
          
      }       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_gek_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 


jQuery("#dov_gek_table").jqGrid('navButtonAdd','#dov_gek_tablePager',{caption:"Додати",
       id:"btn_build_new",
	onClickButton:function(){ 

          validator.resetForm();
          $("#fBuildEdit").resetForm();
          $("#fBuildEdit").clearForm();
          
          edit_row_id = -1;
          $("#fBuildEdit").find("#fid").attr('value',-1 );    
          
          $("#fBuildEdit").find("#bt_add").show();
          $("#fBuildEdit").find("#bt_edit").hide();            
          jQuery("#dialog_editform").dialog('open');          
          
        ;} 
});

jQuery("#dov_gek_table").jqGrid('navButtonAdd','#dov_gek_tablePager',{caption:"Редагувати",
        id:"btn_build_edit",
	onClickButton:function(){ 

            var gsr = jQuery("#dov_gek_table").jqGrid('getGridParam','selrow'); 
            if(gsr)
            { 
                validator.resetForm();  //для сброса состояния валидатора
                $("#fBuildEdit").resetForm();
                $("#fBuildEdit").clearForm();
          
                jQuery(this).jqGrid('GridToForm',gsr,"#fBuildEdit"); 

                $("#fBuildEdit").find("#bt_add").hide();
                $("#fBuildEdit").find("#bt_edit").show();   
                jQuery("#dialog_editform").dialog('open');          
          
            }        
          
        ;} 
});

//-----------------------------------------
jQuery("#dov_gek_table").jqGrid('navButtonAdd','#dov_gek_tablePager',{caption:"Видалити",
        id:"btn_build_del",
	onClickButton:function(){ 

      if ($("#dov_gek_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити запис?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                                      
                                        fEdit_ajaxForm[0].id.value = edit_row_id;
                                        fEdit_ajaxForm[0].oper.value = 'del';
                                        fEdit_ajaxForm.ajaxSubmit(form_options);   
                                        
                                        jQuery("#dialog-changedate").dialog('open');

					$( this ).dialog( "close" );
				},
				"Відмінити": function() {
					$( this ).dialog( "close" );
				}
			}
		});
    
       jQuery("#dialog-confirm").dialog('open');
          
        ;} 
});


if (r_edit!=3)
{
   $('#btn_build_edit').addClass('ui-state-disabled');
   $('#btn_build_new').addClass('ui-state-disabled');
   $('#btn_build_del').addClass('ui-state-disabled');
}

jQuery("#dov_gek_table").jqGrid('filterToolbar','');

jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});
jQuery(".btnClear").button({text: false,icons: {primary:'ui-icon-cancel'}});

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
	,	center__paneSelector:	"#grid_dform"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#dov_gek_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#dov_gek_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }

	});
        
        outerLayout.resizeAll();
        outerLayout.close('south');     

$("#dialog_editform").dialog({
			resizable: true,
		//	height:140,
                        width:700,
			modal: true,
                        autoOpen: false,
                        title:'ЖЕК'
});


 form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FormSubmitResponse // функция, вызываемая при получении ответа
  };

fEdit_ajaxForm = $("#fBuildEdit").ajaxForm(form_options);


$.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
jQuery(".dtpicker").mask("99.99.9999");


jQuery("#fBuildEdit :input").addClass("ui-widget-content ui-corner-all");

// опции валидатора общей формы
var form_valid_options = { 

		rules: {
			id: "required",
			num_gek: "required"
		},
		messages: {
			id: "Вкажіть код!",
			num_gek: "Вкажіть номер ЖЕК!"
		}
};

validator = $("#fBuildEdit").validate(form_valid_options);



$("#fBuildEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});


$("#fBuildEdit").find("#btAddrSel").click( function() { 
        SelectAdrTarget='#faddr';
        SelectAdrStrTarget='#faddr_str';

        $("#fadr_sel_params_address").attr('value', $("#fBuildEdit").find("#faddr").val() );    
    
        //$("#fadr_sel_params").attr('target',"_blank" );           
        var ww = window.open("adr_tree_selector.php", "adr_win", "toolbar=0,width=770,height=500");
        document.adr_sel_params.submit();
        ww.focus();
});


  $('#fBuildEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fBuildEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 


    jQuery("#btAddrClear").click( function() { 

        $('#faddr').attr('value','' );
        $('#faddr_str').attr('value','' );    

    });
}); 
 
function SelectAddrExternal(code, name) {
   $("#fBuildEdit").find(SelectAdrTarget).attr('value',code );
   $("#fBuildEdit").find(SelectAdrStrTarget).attr('value',name );    
} 


// обработчик, который вызываетя перед отправкой формы
function FormBeforeSubmit(formData, jqForm, options) { 

    if (form_edit_lock == 1) return false;
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
       if(!submit_form.validate().form())  {return false;}
       else {
        form_edit_lock=1;   
        return true; 
       }
    }
    else {return true;}       
    //}
    
} ;

// обработчик ответа сервера после отправки формы
function FormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_editform").dialog('close');                           
               jQuery('#dov_gek_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               var fid = jQuery("#fid").val();
               if(fid) 
               { 
                 jQuery("#dov_gek_table").jqGrid('FormToGrid',fid,"#fBuildEdit"); 
               }  
               jQuery("#dialog_editform").dialog('close');                                            
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


