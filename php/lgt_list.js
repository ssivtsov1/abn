var cur_lgt_grp=0;
var row_to_select =null;
var validator = null;
var fLgt_ajaxForm;
var form_edit_lock=0;

jQuery(function(){ 

  if (selmode==0)
  {
     setTimeout(function(){
             jQuery('#lgt_grp_table').trigger('reloadGrid');              
    },300);  
  };

  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");

  //----------------------------------------------------------------------------  
  jQuery('#lgt_grp_table').jqGrid({
    url:     'lgt_list_grp_data.php',
    editurl: 'lgt_list_grp_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:200,
    width:800,
    scroll: 0,
    scrollrows : true,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {label:"Код",name:'ident', index:'ident', width:50, editable: true, align:'left',edittype:'text'},           
      {label:"Доп.код",name:'alt_code', index:'alt_code', width:50, editable: true, align:'left',edittype:'text'},
      {label:"КФК",name:'kfk_code', index:'kfk_code', width:50, editable: true, align:'left',edittype:'text'},
      {label:'Пільга',name:'name', index:'name', width:200, editable: true, align:'left',edittype:"text"},
      {label:'Назва для рахунку',name:'bill_name', index:'bill_name', width:120, editable: true, align:'left',edittype:"text"},
      {label:'id_kategor',name:'id_kategor', index:'id_kategor', width:40, editable: true, align:'right', hidden:true},                             
      {label:'Група',name:'kategor', index:'kategor', width:120, editable: true, align:'left'},                       

      {label:'Бюджет',name:'id_budjet', index:'id_budjet', width:70, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lgi_budjet},stype:'select'},                       
      {label:'Метод розрахунку',name:'id_calc', index:'id_calc', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lgi_calc},stype:'select'},                       
      {label:'calc_name',name:'calc_name', index:'calc_name', width:100, editable: false, align:'left',hidden:true},

      {label:'Документ',name:'id_document', index:'id_document', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lgi_doc},stype:'text', hidden:true},
                        
      {label:'Стан',name:'id_state', index:'id_state', width:70, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lgi_grp_state},stype:'select'},                       
                        
      {label:'Дата початкова',name:'dt_b', index:'dt_b', width:100, editable: true, 
                        align:'left',edittype:'text',formatter:'date', editrules:{required:true}},
      
      {label:'Дата кінцева',name:'dt_e', index:'dt_e', width:100, editable: true, 
                        align:'left',edittype:'text',formatter:'date', editrules:{required:false}},

      {name:'work_period', index:'work_period', width:80, editable: true, align:'left',edittype:'text', hidden:true},
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},        
        
                            
    ],
    pager: '#lgt_grp_tablePager',
    autowidth: true,
    rowNum:100,
    //rowList:[20,50,100,300,500],
    sortname: 'ident',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Пільгові категорії',
    hidegrid: false,
    
    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     if (row_to_select)
     {
       $(this).setSelection(row_to_select, true);             
       row_to_select=null;
     }
     else
     {        
       var first_id = parseInt($(this).getDataIDs()[0]);
       $(this).setSelection(first_id, true);
     } 
    }
    
  },
    
    onSelectRow: function(id) { 
          cur_lgt_grp = id;
       //   jQuery('#lgt_norm_table').jqGrid('setGridParam',{'postData':{'p_id':id}}).trigger('reloadGrid');        
      
    },
    
    ondblClickRow: function(id){ 
      if(selmode==1)
      {
           window.opener.SelectLgtExternal(id,jQuery(this).jqGrid('getCell',id,'name'),
                                              jQuery(this).jqGrid('getCell',id,'id_calc'),
                                              jQuery(this).jqGrid('getCell',id,'calc_name'),
                                              jQuery(this).jqGrid('getCell',id,'ident')
                                      );
           window.opener.focus();
           self.close();            
      }

      if(selmode==0)
      {
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            
            // jQuery(this).editGridRow(id,{width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
            validator.resetForm();  //для сброса состояния валидатора
            $("#fLgtEdit").resetForm();
            $("#fLgtEdit").clearForm();
          
            $("#lgt_grp_table").jqGrid('GridToForm',gsr,"#fLgtEdit"); 
            $("#fLgtEdit").find("#foper").attr('value','edit');              
            cur_lgt_grp = id;

            $("#fLgtEdit").find("#bt_add").hide();
            $("#fLgtEdit").find("#bt_edit").show();   
            $("#dialog_editform").dialog('open');      
            
            if (r_edit==3)
               $("#fLgtEdit").find("#bt_edit").prop('disabled', false);
            else
               $("#fLgtEdit").find("#bt_edit").prop('disabled', true);
        
        }
      }
     } ,  

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#lgt_grp_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ).jqGrid('filterToolbar','');  


//==============================================================================


//jQuery("#lgt_grp_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
//      jQuery(this).editGridRow(id,{width:300,height:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});}} );


//jQuery("#lgt_norm_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
//      jQuery(this).editGridRow(id,{width:300,height:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});}} );



$("#message_zone").dialog({autoOpen: false});

$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
$("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {jQuery("#message_zone").html('');});

jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});
jQuery(".btnClear").button({icons: {primary:'ui-icon-cancel'}});
jQuery("#fLgtEdit :input").addClass("ui-widget-content ui-corner-all");


$("#dialog_editform").dialog({
			resizable: true,
		//	height:140,
                        width:700,
			modal: true,
                        autoOpen: false,
                        title:"Пільгова категорія"
});

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
            jQuery("#lgt_grp_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#lgt_grp_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }
        

	});
        
outerLayout.close('south');             
/*
 innerLayout = $("#pmain_center").layout({
		name:	"inner" 
	,	south__paneSelector:	"#pLgtNorm_table"
	,	south__closable:	true
	,	south__resizable:	true
        ,	south__size:		300
	,	center__paneSelector:	"#pLgtGrp_table"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#lgt_grp_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#lgt_grp_table").jqGrid('setGridHeight',_pane.innerHeight()-85);
        }
//	,       south__onresize:	function (pane, _pane, state, options) 
//        {
//            jQuery("#lgt_norm_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
//            jQuery("#lgt_norm_table").jqGrid('setGridHeight',_pane.innerHeight()-85);
//        }
     

	});
        */

if (selmode==1)
{
  outerLayout.hide('north');      
  $("#lgt_grp_table").jqGrid('hideCol',["bill_name"]);                
};

outerLayout.resizeAll();
        
        
 var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FormSubmitResponse // функция, вызываемая при получении ответа
  };

fLgt_ajaxForm = $("#fLgtEdit").ajaxForm(form_options);
        
// опции валидатора общей формы
var form_valid_options = { 

		rules: {
			lgt_name: "required"
		},
		messages: {
			lgt_name: "Вкажіть назву!"
		}
};

validator = $("#fLgtEdit").validate(form_valid_options);


$("#fLgtEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});
        
jQuery("#lgt_grp_table").jqGrid('navButtonAdd','#lgt_grp_tablePager',{ caption:"Нова",
    id:"btn_lgt_new",
    onClickButton:function(){ 

        validator.resetForm();
        $("#fLgtEdit").resetForm();
        $("#fLgtEdit").clearForm();
          
        edit_row_id = -1;
        $("#fLgtEdit").find("#fid").attr('value',-1 );    
        $("#fLgtEdit").find("#foper").attr('value','add');              
          
        $("#fLgtEdit").find("#bt_add").show();
        $("#fLgtEdit").find("#bt_edit").hide();            
        jQuery("#dialog_editform").dialog('open');          
            
    } 
});

jQuery("#lgt_grp_table").jqGrid('navButtonAdd','#lgt_grp_tablePager',{ caption:"Редагувати",
    id:"btn_lgt_edit",
    onClickButton:function(){ 

        gsr = jQuery("#lgt_grp_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            
            // jQuery(this).editGridRow(id,{width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
            validator.resetForm();  //для сброса состояния валидатора
            $("#fLgtEdit").resetForm();
            $("#fLgtEdit").clearForm();
          
            $("#lgt_grp_table").jqGrid('GridToForm',gsr,"#fLgtEdit"); 
            $("#fLgtEdit").find("#foper").attr('value','edit');              
            

            $("#fLgtEdit").find("#bt_add").hide();
            $("#fLgtEdit").find("#bt_edit").show();   
            $("#dialog_editform").dialog('open');          
        
        }
            
    } 
});

jQuery("#lgt_grp_table").jqGrid('navButtonAdd','#lgt_grp_tablePager',{caption:"Видалити",
        id:"btn_lgt_del",
	onClickButton:function(){ 

      if ($("#lgt_grp_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити пільгу?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                    
                                        $("#dialog-changedate").dialog({ 
                                            resizable: false,
                                            height:140,
                                            modal: true,
                                            autoOpen: false,
                                            buttons: {
                                                "Ok": function() {
                                    
                                                var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
                                                fLgt_ajaxForm[0].oper.value = 'del';
                                                fLgt_ajaxForm[0].change_date.value = cur_dt_change;
                                                fLgt_ajaxForm[0].id.value = cur_lgt_grp;
                                                fLgt_ajaxForm.ajaxSubmit(form_options);       

                                                    $( this ).dialog( "close" );
                                                },
                                                "Отмена": function() {
                                                    $( this ).dialog( "close" );
                                                }
                                            }

                                        });
                                        
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
      $('#btn_lgt_del').addClass('ui-state-disabled');
      $('#btn_lgt_new').addClass('ui-state-disabled');
      $('#btn_lgt_edit').addClass('ui-state-disabled');
    }

   jQuery("#btCategorSel").click( function() { 
     var ww = window.open("lgt_category.php", "lgtcat_win", "toolbar=0,width=800,height=600");
     document.lgtcatsel_params.submit();
     ww.focus();
   });

  jQuery("#btCategorClear").click( function() { 

        $('#fid_kategor').attr('value','' );
        $('#fkategor').attr('value','' );    

    });


  $('#fLgtEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fLgtEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 

     
// обработчик, который вызываетя перед отправкой формы
function FormBeforeSubmit(formData, jqForm, options) { 

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
                                        //SaveLgtChanges();
                                          var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
  
                                          fLgt_ajaxForm[0].change_date.value = cur_dt_change;
                                          fLgt_ajaxForm.ajaxSubmit(form_options);         
                                          form_edit_lock=1;  
					$( this ).dialog( "close" );
				},
				"Отмена": function() {
                                        //CancelLgtChanges();
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
function FormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
               row_to_select = null;  
               jQuery("#dialog_editform").dialog('close');                           
               jQuery('#lgt_grp_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               var fid = jQuery("#fid").val();
               if(fid) 
               { 
                   row_to_select = fid;
               //  jQuery("#lgt_category_table").jqGrid('FormToGrid',fid,"#fLgtCategoryEdit"); 
               }  
               
               jQuery('#lgt_grp_table').trigger('reloadGrid');        
               
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

}); 

function SelectTarCategoryExternal(id, name) {
        $("#fLgtEdit").find("#fid_kategor").attr('value',id );
        $("#fLgtEdit").find("#fkategor").attr('value',name );    
    
}


 
