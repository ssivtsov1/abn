var edit_row_id = 0;
var validator = null;
var form_edit_lock=0;

jQuery(function(){ 
    
  if(r_edit==3)
      r_edit_bool = true;
  else
      r_edit_bool = false;    
    
  jQuery('#dov_tp_table').jqGrid({
    url:'dov_tp_data.php',
    editurl: 'dov_tp_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:500,
    width:800,
    scroll: 0,
    colNames:['Код','Назва','addr', 'Адреса','id fider','Фідер','Дата встановл.','Напруга','Потужність','Абон.ТП'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true },     
      {name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text'},           
      {name:'addr', index:'addr', width:50, editable: true, align:'right',hidden:true,edittype:'text'},           
      {name:'addr_str', index:'addr_str', width:200, editable: true, align:'left',edittype:'text'},           
      {name:'id_fider', index:'id_fider', width:50, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'integer'},           
      {name:'fider', index:'fider', width:200, editable: true, align:'left',edittype:'text'},           

      {name:'dt_install', index:'dt_install', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'id_voltage', index:'id_voltage', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lvolt} },      
      {name:'power', index:'power', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer' },           
                        
      {name:'abon_ps', index:'abon_ps', width:40, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox' }
                            
    ],
    pager: '#dov_tp_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Трансформаторні підстанції',
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
          $("#fTpEdit").resetForm();
          $("#fTpEdit").clearForm();
          
          jQuery(this).jqGrid('GridToForm',gsr,"#fTpEdit"); 
          edit_row_id = id;

          $("#fTpEdit").find("#bt_add").hide();
          $("#fTpEdit").find("#bt_edit").show();   
          jQuery("#dialog_editform").dialog('open');          
          
          if (r_edit==3)
               $("#fTpEdit").find("#bt_edit").prop('disabled', false);
          else
               $("#fTpEdit").find("#bt_edit").prop('disabled', true);
          
          
      } else { alert("Please select Row") }       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_tp_tablePager',
        {edit:false,add:false,del:r_edit_bool,deltext: 'Видалити'},
        {}, 
        {}, 
        {reloadAfterSubmit:false,afterSubmit:processAfterEdit}, 
        {} 
        ); 

jQuery("#dov_tp_table").jqGrid('navButtonAdd','#dov_tp_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_tp_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#dov_tp_table").jqGrid('navButtonAdd','#dov_tp_tablePager',{caption:"Нова",
        id:"btn_tp_new",
	onClickButton:function(){ 

          validator.resetForm();
          $("#fTpEdit").resetForm();
          $("#fTpEdit").clearForm();
          
          edit_row_id = -1;
          $("#fTpEdit").find("#fid").attr('value',-1 );    
          
          $("#fTpEdit").find("#bt_add").show();
          $("#fTpEdit").find("#bt_edit").hide();            
          jQuery("#dialog_editform").dialog('open');          
          
        ;} 
});

jQuery("#dov_tp_table").jqGrid('navButtonAdd','#dov_tp_tablePager',{caption:"Редагувати",
        id:"btn_tp_edit",
	onClickButton:function(){ 

      var gsr = jQuery("#dov_tp_table").jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
          validator.resetForm();  //для сброса состояния валидатора
          $("#fTpEdit").resetForm();
          $("#fTpEdit").clearForm();
          
          jQuery(this).jqGrid('GridToForm',gsr,"#fTpEdit"); 

          $("#fTpEdit").find("#bt_add").hide();
          $("#fTpEdit").find("#bt_edit").show();   
          jQuery("#dialog_editform").dialog('open');          
          
      }     
          
   } 
});

if (r_edit!=3)
{
   $('#btn_tp_edit').addClass('ui-state-disabled');
   $('#btn_tp_new').addClass('ui-state-disabled');
}

jQuery("#dov_tp_table").jqGrid('filterToolbar','');

jQuery(".btn").button();
jQuery(".btnSel").button({ icons: {primary:'ui-icon-folder-open'} });

$("#message_zone").dialog({ autoOpen: false });

$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open'); });
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
            jQuery("#dov_tp_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#dov_tp_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
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
                        title:'Підстанція'
});


 var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FormSubmitResponse // функция, вызываемая при получении ответа
  };

$("#fTpEdit").ajaxForm(form_options);


$.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
jQuery(".dtpicker").mask("99.99.9999");


jQuery("#fTpEdit :input").addClass("ui-widget-content ui-corner-all");

// опции валидатора общей формы
var form_valid_options = { 

		rules: {
			name: "required",
			dt_install: "required",
                        id_voltage: "required",
                        power:{required: false,
                               number:true         }
		},
		messages: {
			name: "Вкажіть назву!",
			dt_install: "Вкажіть дату встановлення!",
                        id_voltage: "Вкажіть напругу!",
			power:{required: "Вкажіть потужність",
                                number:"Повинно бути число!"
                              } 
                        
                        
		}
};

validator = $("#fTpEdit").validate(form_valid_options);



$("#fTpEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});

$("#fTpEdit").find("#btFiderSel").click( function() 
{
    createFiderGrid();
    fider_target_id=jQuery("#fid_fider");
    fider_target_name = jQuery("#ffider");

    jQuery("#grid_selfider").css({'left': $("#dialog_editform").offset().left+$(this).position().left-350, 
                                  'top':  $("#dialog_editform").offset().top+ $(this).position().top+15});
    jQuery("#grid_selfider").toggle( );
 
});

$("#fTpEdit").find("#btAddrSel").click( function() { 
        SelectAdrTarget='#faddr';
        SelectAdrStrTarget='#faddr_str';

        $("#fadr_sel_params_address").attr('value', $("#fTpEdit").find("#faddr").val() );    
    
        //$("#fadr_sel_params").attr('target',"_blank" );           
        var ww = window.open("adr_tree_selector.php", "adr_win", "toolbar=0,width=770,height=500");
        document.adr_sel_params.submit();
        ww.focus();
});


  $('#fTpEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fTpEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 


}); 
 
function SelectAddrExternal(code, name) {
    
        $("#fTpEdit").find(SelectAdrTarget).attr('value',code );
        $("#fTpEdit").find(SelectAdrStrTarget).attr('value',name );    
    
} 


 function processAfterEdit(response, postdata) {
            //alert(response.responseText);
            if (response.responseText=='') { return [true,'']; }
            else
            {
             errorInfo = jQuery.parseJSON(response.responseText);
             
             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]}; 
             
             if (errorInfo.errcode==1) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};              
            }
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
       if(!submit_form.validate().form())  {return false; }
       else {
        form_edit_lock=1;   
        return true; 
       }
    }
    else {return true; }       
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
               jQuery('#dov_tp_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               var fid = jQuery("#fid").val();
               if(fid) 
               { 
                 jQuery("#dov_tp_table").jqGrid('FormToGrid',fid,"#fTpEdit"); 
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


