var edit_row_id=0;
var form_edit_lock=0;
var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, 
    success: FormSubmitResponse 
  };

var task_form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, 
    success: FormSubmitResponse 
  };

jQuery(function(){ 


  jQuery('#switch_table').jqGrid({
    url:'abon_en_switch_data.php',
    editurl: 'abon_en_switch_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    //width:800,
    autowidth: false,
    shrinkToFit : false,
    scroll: 0,
    colNames:[],
    colModel :[   

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},
    {name:'ident', index:'ident', width:40, editable: false, align:'center',hidden:true},

    {label:'Дата',name:'dt_action', index:'dt_action', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
/*
    {label:'Подія',name:'action', index:'action', width:150, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lactions},stype:'select'},
*/
    {label:'action',name:'action', index:'action', width:150, editable: true, align:'right',hidden:true},
    {label:'Подія',name:'action_name', index:'action_name', width:150, editable: true, align:'right',hidden:false},

    {label:'Дата друку (попер.)',name:'dt_create', index:'dt_create', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Сума боргу',name:'sum_warning', index:'sum_warning', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    {label:'кВтг боргу',name:'demand_varning', index:'demand_varning', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',hidden:true},           
                        
 
    {label:'Борг станом на',name:'dt_sum', index:'dt_sum', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
    {label:'Борг виник з',name:'mmgg_debet', index:'mmgg_debet', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date',hidden:false},
                    

    {label:'Оплатити до',name:'dt_warning', index:'dt_warning', width:120, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

/*
    {label:'Місце',name:'id_switch_place', index:'id_switch_place', width:80, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lplace},stype:'select'},
*/
    {label:'id_switch_place',name:'id_switch_place', index:'id_switch_place', width:80, editable: true, align:'right',hidden:true},
    {label:'Місце/причина',name:'switch_place', index:'switch_place', width:80, editable: true, align:'right',hidden:false},
    {label:'Статус',name:'task_state_name', index:'task_state_name', width:50, editable: true, align:'right',hidden:false},

    {label:'Примітка',name:'comment', index:'comment', width:150, editable: true, align:'left',
                            edittype:'text'},
                      
    {name:'id_position', index:'id_position', width:40, editable: false, align:'center',hidden:true},
    {label:'Виконавець',name:'position', index:'position', width:100, editable: true, align:'left',edittype:'text'},                                         

    {label:'№ акта',name:'act_num', index:'act_num', width:50, editable: true, align:'left',edittype:'text'},

    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:false},
    {label:'dt',name:'dt', index:'dt', width:100, editable: false, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
        
    {label:'idk_work',name:'idk_work', index:'idk_work', width:10, editable: true, align:'right',hidden:true},
    {label:'idk_reason',name:'idk_reason', index:'idk_reason', width:10, editable: true, align:'right',hidden:true},
    {label:'idk_abn_state',name:'idk_abn_state', index:'idk_abn_state', width:10, editable: true, align:'right',hidden:true},
    {label:'task_state',name:'task_state', index:'task_state', width:10, editable: true, align:'right',hidden:true},

    ],
    pager: '#switch_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'dt_action',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Відключення та попередження '+paccnt_info,
    //hiddengrid: false,
    jsonReader : {repeatitems: false},
    hidegrid: false,
    postData:{'p_id': id_paccnt},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
   },

   onSelectRow: function(rowid) { 
        edit_row_id = rowid; 
    },

    
    ondblClickRow: function(id){ 
    var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
        edit_row_id = id;
        
          if($(this).jqGrid('getCell',edit_row_id,'ident')==1)
          {
            validator.resetForm();  //для сброса состояния валидатора
            $("#fSwitchEdit").resetForm();
            $("#fSwitchEdit").clearForm();
            $("#fSwitchEdit").find("#foper").attr('value','edit');              
            $(this).jqGrid('GridToForm',gsr,"#fSwitchEdit"); 
      
            $("#fSwitchEdit").find("#bt_add").hide();
            $("#fSwitchEdit").find("#bt_edit").show();   
            $("#dialog_editform").dialog('open');          
     
            if (r_edit==3)
            {
                 $("#fSwitchEdit").find("#bt_edit").prop('disabled', false);
              }
              else
              {
                 $("#fSwitchEdit").find("#bt_edit").prop('disabled', true);
              }
          }

          if($(this).jqGrid('getCell',edit_row_id,'ident')==2)
          {
            task_validator.resetForm();  //для сброса состояния валидатора
            $("#fTaskEdit").resetForm();
            $("#fTaskEdit").clearForm();
            $("#fTaskEdit").find("#foper").attr('value','edit');              
            $(this).jqGrid('GridToForm',gsr,"#fTaskEdit"); 
      
            $("#fTaskEdit").find("#bt_add").hide();
            $("#fTaskEdit").find("#bt_edit").show();   
            $("#dialog_editform_task").dialog('open');          
     
            if (r_edit==3)
            {
                 $("#fTaskEdit").find("#bt_edit").prop('disabled', false);
              }
              else
              {
                 $("#fTaskEdit").find("#bt_edit").prop('disabled', true);
              }
          }


      } else {alert("Please select Row")}       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');}

  }).navGrid('#switch_tablePager',
       {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  jQuery("#switch_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
  //    jQuery(this).editGridRow(id,TableEditOptions);
  }} );


  jQuery("#switch_table").jqGrid('filterToolbar','');
  jQuery("#switch_tablePager_right").css("width","150px");

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
	,       center__onresize:	function (pane, $pane, state, options) 
        {
            
            $("#switch_table").jqGrid('setGridWidth',$pane.innerWidth()-10);
            $("#switch_table").jqGrid('setGridHeight',$pane.innerHeight()-110);

        }
        
	});

        
    outerLayout.resizeAll();
    outerLayout.close('south');  
   // innerLayout.hide('north');        
        
   jQuery(".btn").button();
   jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});
   jQuery(".btnRefresh").button({icons: {primary:'ui-icon-refresh'}});
    
    
   $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
   jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

   jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
   jQuery(".dtpicker").mask("99.99.9999");    
   
   jQuery("#fSwitchEdit :input").addClass("ui-widget-content ui-corner-all");
   jQuery("#fTaskEdit :input").addClass("ui-widget-content ui-corner-all");
   
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   $("#message_zone").dialog({autoOpen: false});
   
 //----------------------------------------------------------------  
jQuery("#switch_table").jqGrid('navButtonAdd','#switch_tablePager',{caption:"Відкрити",
        onClickButton:function(){
      var gsr = jQuery("#switch_table").jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
        if(jQuery("#switch_table").jqGrid('getCell',edit_row_id,'ident')==1)
        {
          
          validator.resetForm();  //для сброса состояния валидатора
          $("#fSwitchEdit").resetForm();
          $("#fSwitchEdit").clearForm();
          $("#fSwitchEdit").find("#foper").attr('value','edit');              
          jQuery(this).jqGrid('GridToForm',gsr,"#fSwitchEdit"); 
      
          $("#fSwitchEdit").find("#bt_add").hide();
          $("#fSwitchEdit").find("#bt_edit").show();  
          if (r_edit==3)
          {
             $("#fSwitchEdit").find("#bt_edit").prop('disabled', false);
          }
          else
          {
             $("#fSwitchEdit").find("#bt_edit").prop('disabled', true);
          }
          
          jQuery("#dialog_editform").dialog('open');          
        }
        if(jQuery("#switch_table").jqGrid('getCell',edit_row_id,'ident')==2)
          {
            task_validator.resetForm();  //для сброса состояния валидатора
            $("#fTaskEdit").resetForm();
            $("#fTaskEdit").clearForm();
            $("#fTaskEdit").find("#foper").attr('value','edit');              
            $(this).jqGrid('GridToForm',gsr,"#fTaskEdit"); 
      
            $("#fTaskEdit").find("#bt_add").hide();
            $("#fTaskEdit").find("#bt_edit").show();   
            $("#dialog_editform_task").dialog('open');          
     
            if (r_edit==3)
            {
                 $("#fTaskEdit").find("#bt_edit").prop('disabled', false);
              }
              else
              {
                 $("#fTaskEdit").find("#bt_edit").prop('disabled', true);
              }
         }
        
      } 
    }
});

jQuery("#switch_table").jqGrid('navButtonAdd','#switch_tablePager',{caption:"Новий",
        id:"btn_switch_new",
	onClickButton:function(){ 

          validator.resetForm();
          $("#fSwitchEdit").resetForm();
          $("#fSwitchEdit").clearForm();
          
          edit_row_id = -1;
          $("#fSwitchEdit").find("#fid").attr('value',-1 );    
          $("#fSwitchEdit").find("#fid_paccnt").attr('value', id_paccnt);
          $("#fSwitchEdit").find("#foper").attr('value','add');              
          $("#fSwitchEdit").find("#bt_add").show();
          $("#fSwitchEdit").find("#bt_edit").hide();            
          jQuery("#dialog_editform").dialog('open');          
          
        ;} 
});
//-----------------------------------------
jQuery("#switch_table").jqGrid('navButtonAdd','#switch_tablePager',{caption:"Видалити",
        id:"btn_switch_del",
	onClickButton:function(){ 

      if ($("#switch_table").getDataIDs().length == 0) 
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
                                                      
                                        if(jQuery("#switch_table").jqGrid('getCell',edit_row_id,'ident')==1)
                                        {
                                          fSwitchEdit_ajaxForm[0].id.value = edit_row_id;
                                          //fSwitchEdit_ajaxForm[0].change_date.value = cur_dt_change;
                                          fSwitchEdit_ajaxForm[0].oper.value = 'del';
                                          fSwitchEdit_ajaxForm.ajaxSubmit(form_options);   
                                        }

                                        if(jQuery("#switch_table").jqGrid('getCell',edit_row_id,'ident')==2)
                                        {
                                          fTaskEdit_ajaxForm[0].id.value = edit_row_id;
                                          fTaskEdit_ajaxForm[0].oper.value = 'del';
                                          fTaskEdit_ajaxForm.ajaxSubmit(form_options);   
                                        }

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

//---------------------------------------------------------------------
$("#dialog_editform").dialog({
			resizable: true,
		//	height:140,
                        width:740,
			modal: true,
                        autoOpen: false,
                        title:'Операція'
});

$("#dialog_editform_task").dialog({
			resizable: true,
                        width:600,
			modal: true,
                        autoOpen: false,
                        title:'Завдання'
});

fSwitchEdit_ajaxForm = $("#fSwitchEdit").ajaxForm(form_options);

var form_valid_options = { 

		rules: {
			//dt_action: "required",
                        sum_warning: {number:true},
			action: "required"
		},
		messages: {
			//dt_action: "Вкажіть дату!",
                        sum_warning: {number:"Повинно бути число!"},
			action: "Вкажіть подію!"
		}
};

validator = $("#fSwitchEdit").validate(form_valid_options);



$("#fSwitchEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});

//---------------------------------------------------------

fTaskEdit_ajaxForm = $("#fTaskEdit").ajaxForm(task_form_options);

var task_valid_options = { 

		rules: {
			date_print: "required",
                        sum_warning: {number:true},
			idk_work: "required"
		},
		messages: {
			date_print: "Вкажіть дату!",
                        sum_warning: {number:"Повинно бути число!"},
			idk_work: "Вкажіть подію!"
		}
};

task_validator = $("#fTaskEdit").validate(task_valid_options);



$("#fTaskEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform_task").dialog('close');                           
});

//--------------------------------------------
$("#fSwitchEdit").find("#bt_indic").click( function() 
{
    //indic_flock
    //indic_fedit
    newIndicationGridMode =2;
    indic_edit_row_id=0;

    $("#dialog-indications").find("#fdt_ind").datepicker( "setDate" , $("#fSwitchEdit").find("#fdt_action").attr('value')  );
    createNewIndicationGrid(newIndicationGridMode);

    $("#dialog-indications").dialog({
        resizable: true,
        height:300,
        width:800,
        modal: true,
        autoOpen: false,
        dialogClass: 'StandartTitleClass',
        title:'Показники',
        resize: function(event, ui) 
        {
            if (isNewIndicationGridCreated)
            {
                jQuery("#new_indications_table").jqGrid('setGridWidth',$("#dialog-indications").innerWidth()-15);
                jQuery("#new_indications_table").jqGrid('setGridHeight',$("#dialog-indications").innerHeight()-100);
            }
        },
                
        buttons: {
            "Ок": function() {
                if (indic_flock=='1')
                {
                    alert('Закритий період!');
                    return;
                }
                if (r_indic!=3)
                {
                    alert('Немає прав змінювати показники!');
                    return;
                }
                    

                if ((selICol!=0)&&(selIRow!=0))
                {
                    jQuery('#new_indications_table').editCell(selIRow,selICol, false); 
                }
    
    
                var data_obj = $('#new_indications_table').getChangedCells('all');
                var json_str = JSON.stringify(data_obj);
                var id_reason = jQuery("#dialog-indications").find("#fid_reason").val();

                //alert(json);
                $.ajaxSetup({
                    type: "POST",   
                    dataType: "json"
                });
                
                if (indic_fedit=='1')
                    voper = 'edit';
                else
                    voper = 'add';
                
                var request = $.ajax({
                    url: "abon_ensaldo_new_indic_edit.php",
                    type: "POST",
                    data: {
                        oper : voper , 
                        reason: '',
                        json_data : json_str  
                    },
                    dataType: "json"
                });

                request.done(function(data ) {
                    if (data.errcode!==undefined)
                    {
                        $('#message_zone').append(data.errstr);  
                        $('#message_zone').append("<br>");                 
                        if (data.errcode==2)
                            $('#message_zone').dialog('open');
                    }
                    $(".mod_column_class").removeClass("mod_column_class");
                    jQuery('#indic_table').trigger('reloadGrid');        
            
                //window.opener.RefreshIndicExternal(id_pack);

                });
                request.fail(function(data ) {
                    if (data.errcode!==undefined)
                    {
                        $('#message_zone').append(data.errstr);  
                        $('#message_zone').append("<br>");
                        $('#message_zone').dialog('open');
                    }
                    else
                    {
                        $('#message_zone').append(data);  
                        $('#message_zone').dialog('open');
                    }
            
                });


                $( this ).dialog( "close" );
            },
            "Відмінити": function() {
                $( this ).dialog( "close" );
            }
        }
    });
    
    jQuery("#dialog-indications").dialog('open');
    jQuery("#new_indications_table").jqGrid('setGridWidth',$("#dialog-indications").innerWidth()-15);
    jQuery("#new_indications_table").jqGrid('setGridHeight',$("#dialog-indications").innerHeight()-100);
            

});


    
$('#fSwitchEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fSwitchEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
 });

   jQuery("#btCntrlSel").click( function() { 

     createPersonGrid($("#fSwitchEdit").find("#fid_position").val());
     person_target_id=     $("#fSwitchEdit").find("#fid_position");
     person_target_name =  $("#fSwitchEdit").find("#fposition");
     person_target_prof = 0;
    
     jQuery("#grid_selperson").css({'left': $("#fSwitchEdit").find("#fposition").offset().left+1, 'top': $("#fSwitchEdit").find("#fposition").offset().top+20});
     jQuery("#grid_selperson").toggle( );

   });

  if (r_edit!=3)
  {
    $('#btn_switch_del').addClass('ui-state-disabled');
    $('#btn_switch_new').addClass('ui-state-disabled');
  
  }


});

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
               jQuery("#dialog_editform_task").dialog('close');
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');
               $('#switch_table').jqGrid('setGridParam',{postData:{'p_id': id_paccnt}}).trigger('reloadGrid');
               return [true,errorInfo.errstr]};
             
             if (errorInfo.errcode==1) {
                 
               //var fid = jQuery("#fid").val();
               //if(fid) 
               //{ 
               //  jQuery("#switch_table").jqGrid('FormToGrid',fid,"#fSwitchEdit"); 
               //}  
               $('#switch_table').jqGrid('setGridParam',{postData:{'p_id': id_paccnt}}).trigger('reloadGrid');               
               jQuery("#dialog_editform").dialog('close');
               jQuery("#dialog_editform_task").dialog('close');
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

