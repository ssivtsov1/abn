var id_paccnt = 0;
var edit_row_id=0;
var form_edit_lock=0;
var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, 
    success: FormSubmitResponse 
  };
// var list_mode =0;

jQuery(function(){ 

  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
		buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");
    
  $("#fmmgg").datepicker( "setDate" , mmgg );


  jQuery('#task_table').jqGrid({
    url:'abon_en_task_list_data.php',
    editurl: 'abon_en_task_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    //width:800,
    autowidth: true,
    shrinkToFit : true,
    scroll: 0,
    colNames:[],
    colModel :[ 

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},

    {label:'Книга',name:'book', index:'book', width:30, editable: true, align:'left',edittype:'text'},           
    {label:'Особ.рах.',name:'code', index:'code', width:30, editable: true, align:'left',edittype:'text'},                 
    {label:'Абонент',name:'abon', index:'abon', width:200, editable: true, align:'left',edittype:'text'},
    {label:'Місто',name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text', hidden:town_hidden},
    {label:'Адреса',name:'addr', index:'addr', width:200, editable: true, align:'left',edittype:'text'},           
    {label:'Дільниця',name:'sector', index:'sector', width:100, editable: true, align:'left',edittype:'text'},                        

    {label:'Подія',name:'idk_work', index:'idk_work', width:100, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:ltask},stype:'select'},

    {label:'№ завд.',name:'task_num', index:'task_num', width:40, editable: true, align:'left',edittype:'text'},
    
    {label:'Дата друку',name:'date_print', index:'date_print', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Дата виконання',name:'date_work', index:'date_work', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Підстава',name:'idk_reason', index:'idk_reason', width:100, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lreason},stype:'select'},

    {label:'Сума боргу',name:'sum_warning', index:'sum_warning', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           

    {label:'Стан абон.',name:'idk_abn_state', index:'idk_abn_state', width:100, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:labnstate},stype:'select'},

    {label:'Статус роботи',name:'task_state', index:'task_state', width:80, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lstate},stype:'select'},

    {label:'Примітка',name:'note', index:'note', width:150, editable: true, align:'left',
                            edittype:'text'},

    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},
    {label:'dt',name:'dt_input', index:'dt_input', width:100, editable: false, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}

    ],
    pager: '#task_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'date_print',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Завдання',
    //hiddengrid: false,
    jsonReader : {repeatitems: false},
    hidegrid: false,
    postData:{'p_mmgg': mmgg},
    //postData:{'p_id': id_paccnt},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
   },

   onSelectRow: function(rowid) { 
        edit_row_id = rowid; 
        id_paccnt = jQuery(this).jqGrid('getCell',rowid,'id_paccnt')
    },

    
    ondblClickRow: function(id){ 
    var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
        edit_row_id = id;
     
          validator.resetForm();  //для сброса состояния валидатора
          $("#fTaskEdit").resetForm();
          $("#fTaskEdit").clearForm();
          $("#fTaskEdit").find("#foper").attr('value','edit');              
          jQuery(this).jqGrid('GridToForm',gsr,"#fTaskEdit"); 
      
          $("#fTaskEdit").find("#bt_add").hide();
          $("#fTaskEdit").find("#bt_edit").show();   
          jQuery("#dialog_editform").dialog('open');          
     
          if (r_edit==3)
          {
             $("#fTaskEdit").find("#bt_edit").prop('disabled', false);
          }
          else
          {
             $("#fTaskEdit").find("#bt_edit").prop('disabled', true);
          }
        
      } else { alert("Please select Row") }       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#task_tablePager',
       {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  jQuery("#task_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
  //    jQuery(this).editGridRow(id,TableEditOptions);
  }} );


  jQuery("#task_table").jqGrid('filterToolbar','');
  jQuery("#task_tablePager_right").css("width","150px");


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
            
            $("#task_table").jqGrid('setGridWidth',$pane.innerWidth()-10);
            $("#task_table").jqGrid('setGridHeight',$pane.innerHeight()-130);

        }
        
	});

        
    outerLayout.resizeAll();
    outerLayout.close('south');     
   // innerLayout.hide('north');        
        
    jQuery(".btn").button();
    jQuery(".btnSel").button({ icons: {primary:'ui-icon-folder-open'} });
    jQuery(".btnRefresh").button({icons: {primary:'ui-icon-refresh'}});
    
   jQuery("#fTaskEdit :input").addClass("ui-widget-content ui-corner-all");
   
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open'); });
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   $("#message_zone").dialog({autoOpen: false});
   
 //----------------------------------------------------------------  
jQuery("#task_table").jqGrid('navButtonAdd','#task_tablePager',{caption:"Відкрити",
        onClickButton:function(){
      var gsr = jQuery("#task_table").jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
          validator.resetForm();  //для сброса состояния валидатора
          $("#fTaskEdit").resetForm();
          $("#fTaskEdit").clearForm();
          $("#fTaskEdit").find("#foper").attr('value','edit');              
          jQuery(this).jqGrid('GridToForm',gsr,"#fTaskEdit"); 
      
          $("#fTaskEdit").find("#bt_add").hide();
          $("#fTaskEdit").find("#bt_edit").show();   
          jQuery("#dialog_editform").dialog('open');          
      } 
    }
});

jQuery("#task_table").jqGrid('navButtonAdd','#task_tablePager',{caption:"Новий",
        id:"btn_task_new",
	onClickButton:function(){ 

          validator.resetForm();
          $("#fTaskEdit").resetForm();
          $("#fTaskEdit").clearForm();
          
          edit_row_id = -1;
          $("#fTaskEdit").find("#fid").attr('value',-1 );    
          $("#fTaskEdit").find("#fid_paccnt").attr('value', 0);
          $("#fTaskEdit").find("#foper").attr('value','add');              
          $("#fTaskEdit").find("#bt_add").show();
          $("#fTaskEdit").find("#bt_edit").hide();            
          
          $("#fdate_print").attr('value', Date.now().toString("dd.MM.yyyy") );
          $("#ftask_state").attr('value',1 );    

          jQuery("#dialog_editform").dialog('open');  
          
        ;} 
});

//-----------------------------------------
jQuery("#task_table").jqGrid('navButtonAdd','#task_tablePager',{caption:"Видалити",
        id:"btn_task_del",
	onClickButton:function(){ 

      if ($("#task_table").getDataIDs().length == 0) 
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
                                                      
                                          fTaskEdit_ajaxForm[0].id.value = edit_row_id;
                                          fTaskEdit_ajaxForm[0].oper.value = 'del';
                                          fTaskEdit_ajaxForm.ajaxSubmit(form_options);   

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
 jQuery("#task_table").jqGrid('navButtonAdd','#task_tablePager',{caption:"Друк завдання",
    id:"btn_task_print",
    onClickButton:function(){ 

        //var postData = jQuery("#pay_table").jqGrid('getGridParam', 'postData');
        //var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#foper").attr('value', "task_print"); 
       $('#freps_params').find("#ftemplate_name").attr('value', "task_print"); 
       $('#freps_params').find("#fid_warning").attr('value',edit_row_id ); 

       document.forms["freps_params"].submit();        
             
    } 
});

if (r_edit!=3)
{
  $('#btn_task_del').addClass('ui-state-disabled');
  $('#btn_task_new').addClass('ui-state-disabled');
  $('#btn_task_edit').addClass('ui-state-disabled');

}

//---------------------------------------------------------------------------
 jQuery("#task_table").jqGrid('navButtonAdd','#task_tablePager',{caption:"Друкувати список",
    onClickButton:function(){ 

        var postData = jQuery("#task_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       $('#freps_params').find("#fdt_b").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#fperiod_str").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#foper").attr('value', "task_list");
       $('#freps_params').find("#ftemplate_name").attr('value', "task_list");
   //    $('#freps_params').find("#flist_mode").attr('value', list_mode);
       
       
       $("#dialog-confirm").find("#dialog-text").html('Виберіть варіант друку');
       $("#dialog-confirm").dialog({
        resizable: false, 
        height:140,
        modal: true,
        autoOpen: false,
        title:'Друк журналу',
        buttons: {
            "На екран": function() {
                $('#freps_params').find("#fxls").attr('value',0 );       
                document.forms["freps_params"].submit();        
                $( this ).dialog( "close" );
            },
            "в файл Excel ": function() {
                $('#freps_params').find("#fxls").attr('value',1 );       
                document.forms["freps_params"].submit();        
                $( this ).dialog( "close" );
            }
        }
       });
    
       jQuery("#dialog-confirm").dialog('open');
            
    } 
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


fTaskEdit_ajaxForm = $("#fTaskEdit").ajaxForm(form_options);

var form_valid_options = { 

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

validator = $("#fTaskEdit").validate(form_valid_options);



$("#fTaskEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});

    
$("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       
       $('#task_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
       
});    


  jQuery("#btPaccntSel").click( function() { 

        createAbonGrid();
        
        abon_target_id = $('#fid_paccnt');
        abon_target_name = $('#fpaccnt_name');
        abon_target_book = $('#fbook');
        abon_target_code = $('#fcode');
        abon_target_addr = $('#fpaccnt_addr');        

       jQuery("#grid_selabon").css({'left': $('#fpaccnt_name').offset().left+1, 'top': $('#fpaccnt_name').offset().top+20});
       jQuery("#grid_selabon").toggle( );
       
  });

  $('#fTaskEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fTaskEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 

 
 
   $("#show_peoples").click( function() {
     jQuery("#task_table").jqGrid('showCol',["user_name"]);
  });

    $('#fbook').keypress(function(ev){
        if(ev.which == 13){
            
        if ($('#fbook').val()!='')
               $('#fcode').select();
           
        return false;
        }
    });

    $('#fcode').keypress(function(ev){
        if(ev.which == 13){
            
        if ($('#fbook').val()!='')
        {

        vbook = $('#fbook').attr('value');
        vcode = $('#fcode').attr('value');
         

        var request = $.ajax({
            url: "abon_en_get_abon_id_data.php",
            type: "POST",
            data: {
                book : vbook,
                code: vcode
            },
            dataType: "json"
        });

        request.done(function(data ) {  
        
            if (data.errcode!==undefined)
            {
                if(data.errcode==1)
                {
                    $('#fid_paccnt').attr('value',data.id );
                    $('#fpaccnt_name').attr('value',data.errstr );    
                    $('#fpaccnt_addr').attr('value',data.add_data );    
                }

                if(data.errcode==2)
                {
                    $('#fid_paccnt').attr('value','' );
                    $('#fpaccnt_name').attr('value',data.errstr );    
                }
            }
        });        
        request.fail(function(data ) {
            alert("error");
        });

        }
           
        return false;
        }
    });

//-------------------------------------------------------
  jQuery('#warning_late_table').jqGrid({
    url:'abon_en_task_warning_data.php',
    editurl: '', 
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:400,
    width:800,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center', hidden:true},
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},                
    {label:'Нас.пункт',name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text', hidden:town_hidden},
    {label:'Адреса',name:'addr', index:'addr', width:100, editable: true, align:'left',edittype:'text'},
    {label:'Абонент',name:'abon', index:'abon', width:100, editable: true, align:'left',edittype:'text'},
    {label:'Дільниця',name:'sector_name', index:'sector_name', width:100, editable: true, align:'left',edittype:'text'},
    {label:'Район',name:'id_region', index:'id_region', width:80, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lregion},stype:'select',hidden:false},
    {label:'Дата попер.',name:'dt_action', index:'dt_action', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
    {label:'Сума попер.',name:'sum_warning', index:'sum_warning', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},
    {label:'Сплатити до',name:'dt_warning', index:'dt_warning', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
    {label:'Залишок',name:'sum_delta', index:'sum_delta', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    {label:'Поточ.борг',name:'debet_now', index:'debet_now', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    {label:'Прим.',name:'note', index:'note', width:100, editable: true, align:'left',edittype:'text'},
    {label:'Завд.',name:'ftask', index:'ftask', width:40, editable: true, align:'left',edittype:'text'}
    ],
    pager: '#warning_late_tablePager',
    autowidth: true,
    //shrinkToFit : false,
    rowNum:2000,
    //rowList:[50,100,200],
    sortname: 'book',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: '',
    //hiddengrid: false,
    hidegrid: false,    
    multiselect: true,
    //recordpos: 'left',
    //postData:{'p_mmgg': mmgg},
    jsonReader : {repeatitems: false},
    
    gridComplete:function(){

  //  if ($(this).getDataIDs().length > 0) 
  //  {      
  //   var first_id = parseInt($(this).getDataIDs()[0]);
  //   $(this).setSelection(first_id, true);
  //  }
    
  },
    
  //  ondblClickRow: function(id){ 
  //       jQuery(this).editGridRow(id,LgtNormEditOptions);  
  //  } ,  

    loadError : function(xhr,st,err) {
      jQuery('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);
    }
  
  //  jsonReader : { repeatitems: false }

  }).navGrid('#warning_late_tablePager',
         {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

jQuery('#warning_late_table').jqGrid('filterToolbar','');


$.ajaxSetup({type: "POST",      dataType: "json"});

$("#pwarning_late_table").dialog({
    resizable: true,
    height:600,
    width:900,
    modal: true,
    autoOpen: false,
    title:"Протерміновані попередження",
    buttons: {
   "Завдання на відключення": function() {
                             

            $("#dialog-changedate").dialog({ 
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                buttons: {
                    "Ok": function() {
                                                    
                        var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
                        if (cur_dt_change=='') return;  
                        jQuery("#dialog-changedate").dialog('close');
                                   
                        var ids =jQuery("#warning_late_table").jqGrid('getGridParam','selarrrow');
                        //alert(ids);                                                  
                        var request = $.ajax({
                            url: "abon_en_task_warning_edit.php",
                            data: {
                                id_array: ids,
                                dt_task :cur_dt_change
                            }
                        });

                        request.done(function(data ) {
                              if (data.errcode!==undefined)
                               {
                                $('#message_zone').append(data.errstr);
                                $('#message_zone').append("<br>");
                                
                                if(data.errcode==2) 
                                {
                                    jQuery("#message_zone").dialog('open');
                                }
                                else
                                {
                                    $('#task_table').trigger('reloadGrid');
                                }
                                
                               }
                              $("#pwarning_late_table").dialog( "close" );
                        });
                        request.fail(function(data ) {alert("error");});
 
                    },
                    "Отмена": function() {
                        $( this ).dialog( "close" );
                    }
                }
                        
            });
                            
            jQuery("#dialog-changedate").dialog('open');
        //$( this ).dialog( "close" );                        

    },

    "Закрити": function() {
        $( this ).dialog( "close" );
    }
},
resize: function(event, ui) { 
    jQuery("#warning_late_table").jqGrid('setGridWidth',jQuery("#pwarning_late_table").innerWidth()-10);
    jQuery("#warning_late_table").jqGrid('setGridHeight',jQuery("#pwarning_late_table").innerHeight()-80);
                             
},
open: function(event, ui) { 
    jQuery("#warning_late_table").jqGrid('setGridWidth',jQuery("#pwarning_late_table").innerWidth()-10);
    jQuery("#warning_late_table").jqGrid('setGridHeight',jQuery("#pwarning_late_table").innerHeight()-80);
                         
}
});

//---------------------------------------------------------------------------
 jQuery("#task_table").jqGrid('navButtonAdd','#task_tablePager',{caption:"Попереджені",
    onClickButton:function(){ 

       $('#warning_late_table').jqGrid('setGridParam',{datatype:'json'}).trigger('reloadGrid');
       $("#pwarning_late_table").dialog('open');
            
    } 
});




});

function SelectPaccntExternal(id, book, code, name, addr) {
    
        $('#fid_paccnt').attr('value',id );
        $('#fpaccnt_name').attr('value',name );    
    
} 
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
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
               $('#task_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               var fid = jQuery("#fid").val();
               if(fid) 
               { 
                 jQuery("#task_table").jqGrid('FormToGrid',fid,"#fTaskEdit"); 
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
