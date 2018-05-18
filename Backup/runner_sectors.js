var cur_sector_id=0;
var cur_paccnt_id=0;
var validator = null;
var form_edit_lock=0;

jQuery(function(){ 
/*
   setTimeout(function(){
             jQuery('#accnt_table').trigger('reloadGrid');              
    },300);  
*/
  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");

  //----------------------------------------------------------------------------  
  jQuery('#sectors_table').jqGrid({
    url:     'runner_sectors_data.php',
    editurl: 'runner_sectors_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:200,
    width:800,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {label:'Код',name:'code', index:'code', width:50, editable: true, align:'left',edittype:"text"},
      {label:'Дільниця',name:'name', index:'name', width:200, editable: true, align:'left',edittype:"text"},
      {label:'id_runner',name:'id_runner', index:'sort_flag', width:40, editable: true, align:'right', hidden:true},                             
      {label:"Кур'єр / контролер",name:'runner', index:'runner', width:100, editable: true, align:'left'},                       
      {label:"Оператор",name:'operator', index:'operator', width:100, editable: true, align:'left'},
      {label:'id_kontrol',name:'id_kontrol', index:'id_kontrol', width:40, editable: true, align:'right', hidden:true},
      {label:"Контролер",name:'controler', index:'controler', width:100, editable: true, align:'left'},
      {label:'Район',name:'id_region', index:'id_region', width:80, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lregion},stype:'select',hidden:false},
      {label:'Примітка',name:'notes', index:'notes', width:200, editable: true, align:'left',edittype:"text"},
      {label:'Дата',name:'dt_b', index:'dt_b', width:100, editable: true, 
                        align:'left',edittype:'text',formatter:'date', editrules:{required:true}},
      {name:'work_period', index:'work_period', width:80, editable: true, align:'left',edittype:'text', hidden:true},
      {name:'dt_input', index:'dt_input', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},       
      {label:'sort_flag',name:'sort_flag', index:'sort_flag', width:40, editable: true, align:'right', hidden:true}
                            
    ],
    pager: '#sectors_tablePager',
    autowidth: true,
    rowNum:100,
    rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Дільниці',
    //hiddengrid: false,
    hidegrid: false,    
    
    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
    
  },
    
    onSelectRow: function(id) { 
          cur_sector_id = id;
          jQuery('#accnt_table').jqGrid('setGridParam',{datatype: 'json','postData':{'p_id':cur_sector_id}}).trigger('reloadGrid');        
      
    },
    
    ondblClickRow: function(id){ 
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            
            // jQuery(this).editGridRow(id,{width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
            validator.resetForm();  //для сброса состояния валидатора
            $("#fSectorEdit").resetForm();
            $("#fSectorEdit").clearForm();
          
            $("#sectors_table").jqGrid('GridToForm',gsr,"#fSectorEdit"); 
            $("#fSectorEdit").find("#foper").attr('value','edit');              
            cur_sector_id = id;

            $("#fSectorEdit").find("#bt_add").hide();
            $("#fSectorEdit").find("#bt_edit").show();   
            $("#dialog_editform").dialog('open');          
        
            if (r_edit==3)
               $("#fSectorEdit").find("#bt_edit").prop('disabled', false);
            else
               $("#fSectorEdit").find("#bt_edit").prop('disabled', true);
        
        }

     } ,  

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#sectors_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ).jqGrid('filterToolbar','');


//==============================================================================

  jQuery('#accnt_table').jqGrid({
    url:'runner_sectors_accnt_data.php',
    editurl: 'runner_sectors_accnt_edit.php',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:200,
    width:400,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_sector', index:'id_sector', width:40, editable: false, align:'center',hidden:true},
    {name:'id_accnt', index:'id_accnt', width:40, editable: false, align:'center',hidden:true},    
    
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},                
    {label:'Адреса',name:'address', index:'address', width:200, editable: true, align:'left',edittype:'text'},                    
    {label:'Абонент',name:'abon', index:'abon', width:100, editable: true, align:'left',edittype:'text'},                        
    
    {label:'Дата',name:'dt_b', index:'dt_b', width:100, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},


    {name:'work_period', index:'work_period', width:80, editable: true, align:'left',edittype:'text', hidden:true},
    {name:'dt_input', index:'dt_input', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
    {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},        

    ],
    pager: '#accnt_tablePager',
    autowidth: true,
    //shrinkToFit : false,
    rowNum:50,
    rowList:[50,100,200],
    sortname: 'book',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Абоненти',
    //hiddengrid: false,
    hidegrid: false,    
    postData:{'p_id':0},
    
    gridComplete:function(){

     if ($(this).getDataIDs().length > 0) 
     {      
       var first_id = parseInt($(this).getDataIDs()[0]);
       $(this).setSelection(first_id, true);
     }
    },
    onSelectRow: function(id) { 
          cur_paccnt_id = id;
    },
    
    //ondblClickRow: function(id){ 
    //     jQuery(this).editGridRow(id,LgtNormEditOptions);  
    //} ,  

    loadError : function(xhr,st,err) {
      jQuery('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);
    }
  
  //  jsonReader : { repeatitems: false }

  }).navGrid('#accnt_tablePager',
         {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ).jqGrid('filterToolbar','');

//==============================================================================

  jQuery('#accnt_oper_table').jqGrid({
    url:'runner_sectors_accnt_oper_data.php',
    editurl: '', 
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:400,
    width:770,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},                
    {label:'Місто/село',name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text'},                    
    {label:'Вулиця',name:'street', index:'street', width:100, editable: true, align:'left',edittype:'text'},                        
    {label:'Буд.',name:'house', index:'house', width:40, editable: true, align:'left',edittype:'text'}, 
    {label:'Буд.доп.',name:'slash', index:'slash', width:50, editable: true, align:'left',edittype:'text',hidden:true},
    {label:'Корпус',name:'korp', index:'korp', width:50, editable: true, align:'left',edittype:'text'},                            
    {label:'Кв.',name:'flat', index:'flat', width:40, editable: true, align:'left',edittype:'text'},                        
    {label:'Кв.доп.',name:'f_slash', index:'f_slash', width:50, editable: true, align:'left',edittype:'text',hidden:true},                            
    {label:'Абонент',name:'abon', index:'abon', width:100, editable: true, align:'left',edittype:'text'},
    {label:'Дільниця',name:'sector', index:'sector', width:100, editable: true, align:'left',edittype:'text'}
    ],
    pager: '#accnt_oper_tablePager',
    autowidth: true,
    //shrinkToFit : false,
    rowNum:50,
    rowList:[50,100,200],
    sortname: 'book',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: '',
    //hiddengrid: false,
    hidegrid: false,    
    multiselect: true,
    //recordpos: 'left',
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

  }).navGrid('#accnt_oper_tablePager',
         {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

jQuery('#accnt_oper_table').jqGrid('filterToolbar','');

//jQuery("#sectors_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
//      jQuery(this).editGridRow(id,{width:300,height:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});}} );


//jQuery("#accnt_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
//      jQuery(this).editGridRow(id,{width:300,height:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});}} );



$("#message_zone").dialog({autoOpen: false});

$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
$("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {jQuery("#message_zone").html('');});

jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});
jQuery(".btnClear").button({text: false,icons: {primary:'ui-icon-cancel'}});

jQuery("#fSectorEdit :input").addClass("ui-widget-content ui-corner-all");


$("#dialog_editform").dialog({
			resizable: true,
		//	height:140,
                        width:600,
			modal: true,
                        autoOpen: false,
                        title:"Дільниця"
});
//------------------------------------------------------------------------------
$.ajaxSetup({    type: "POST",      dataType: "json"});

$("#pAccnt_oper_table").dialog({
    resizable: true,
    height:600,
    width:800,
    modal: true,
    autoOpen: false,
    title:"Вибір абонентів на дільницю",
    buttons: {
        "Додати до дільниці": function() {
                                         
            $("#dialog-changedate").dialog({ 
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                buttons: {
                    "Ok": function() {
                                                    
                        var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
                        if (cur_dt_change=='') return;        
                                
                        var ids =jQuery("#accnt_oper_table").jqGrid('getGridParam','selarrrow');
                        //alert(ids);                                                  
                        var request = $.ajax({
                            url: "runner_sectors_accnt_oper_edit.php",
                            data: {
                                id_sector: cur_sector_id, 
                                change_date:cur_dt_change,
                                oper : 'add', 
                                id_array: ids
                            }
                        });

                        request.done(function(data ) {
                              if (data.errcode!==undefined)
                               {
                                $('#message_zone').append(data.errstr);
                                $('#message_zone').append("<br>");
                                if (data.errcode ==2) 
                                    {
                                        $('#message_zone').dialog('open');
                                    }
                               }
                               jQuery('#accnt_table').jqGrid('setGridParam',{'postData':{'p_id':cur_sector_id } }).trigger('reloadGrid');
                        });
                        request.fail(function(data ) {    alert("error");   });
                                   
                        $( "#pAccnt_oper_table" ).dialog( "close" );                              
                        $( this ).dialog( "close" );
                    },
                    "Отмена": function() {
                        $( this ).dialog( "close" );
                    }
                }
            });
                                        
            jQuery("#dialog-changedate").dialog('open');
        //$( this ).dialog( "close" );
    },

    "Видалити з дільниці": function() {
            $("#dialog-changedate").dialog({ 
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                buttons: {
                    "Ok": function() {
                                                    
                        var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
                        if (cur_dt_change=='') return;        
                                   
                        var ids =jQuery("#accnt_oper_table").jqGrid('getGridParam','selarrrow');
                        //alert(ids);                                                  
                        var request = $.ajax({
                            url: "runner_sectors_accnt_oper_edit.php",
                            data: {
                                id_sector: cur_sector_id, 
                                change_date:cur_dt_change,
                                oper : 'del', 
                                id_array: ids
                            }
                        });

                        request.done(function(data ) {
                              if (data.errcode!==undefined)
                               {
                                $('#message_zone').append(data.errstr);
                                $('#message_zone').append("<br>");
                               }
                               jQuery('#accnt_table').jqGrid('setGridParam',{'postData':{'p_id':cur_sector_id } }).trigger('reloadGrid');
                        });
                        request.fail(function(data ) {    alert("error");   });
                                   
                        $( "#pAccnt_oper_table" ).dialog( "close" );                              
                        $( this ).dialog( "close" );
                    },
                    "Отмена": function() {
                        $( this ).dialog( "close" );
                    }
                }
            });
                                        
            jQuery("#dialog-changedate").dialog('open');

},

"Закрити": function() {
    $( this ).dialog( "close" );
}
},
resize: function(event, ui) { 
    jQuery("#accnt_oper_table").jqGrid('setGridWidth',jQuery("#pAccnt_oper_table").innerWidth()-8);
    jQuery("#accnt_oper_table").jqGrid('setGridHeight',jQuery("#pAccnt_oper_table").innerHeight()-80);
                             
},
open: function(event, ui) { 
    jQuery("#accnt_oper_table").jqGrid('setGridWidth',jQuery("#pAccnt_oper_table").innerWidth()-8);
    jQuery("#accnt_oper_table").jqGrid('setGridHeight',jQuery("#pAccnt_oper_table").innerHeight()-80);
                         
}
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

	});

 innerLayout = $("#pmain_center").layout({
		name:	"inner" 
	,	north__paneSelector:	"#pSectors_table"
	,	north__closable:	false
	,	north__resizable:	true
        ,	north__size:		300
	,	center__paneSelector:	"#pAccnt_table"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       north__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#sectors_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#sectors_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#accnt_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#accnt_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }
        

	});

        outerLayout.resizeAll();
        outerLayout.close('south');     
        
        
 var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FormSubmitResponse // функция, вызываемая при получении ответа
  };

fSector_ajaxForm = $("#fSectorEdit").ajaxForm(form_options);
        
// опции валидатора общей формы
var form_valid_options = { 

		rules: {
			name: "required",
                        dt_b: "required"
                        //,runner: "required"
		},
		messages: {
			name: "Вкажіть назву!",
                        dt_b: "Вкажіть дату"
                        //,runner: "Вкажіть працівника!"
		}
};

validator = $("#fSectorEdit").validate(form_valid_options);


$("#fSectorEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});
        
jQuery("#sectors_table").jqGrid('navButtonAdd','#sectors_tablePager',{caption:"Новий",
    id:"btn_sector_new",
    onClickButton:function(){ 

        validator.resetForm();
        $("#fSectorEdit").resetForm();
        $("#fSectorEdit").clearForm();
          
        $("#fSectorEdit").find("#fid").attr('value',-1 );    
        $("#fSectorEdit").find("#foper").attr('value','add');              
          
        $("#fSectorEdit").find("#bt_add").show();
        $("#fSectorEdit").find("#bt_edit").hide();            
        jQuery("#dialog_editform").dialog('open');          
            
    } 
});

jQuery("#sectors_table").jqGrid('navButtonAdd','#sectors_tablePager',{caption:"Редагувати",
    id:"btn_sector_edit",
    onClickButton:function(){ 

        gsr = jQuery("#sectors_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            
            validator.resetForm();  //для сброса состояния валидатора
            $("#fSectorEdit").resetForm();
            $("#fSectorEdit").clearForm();
          
            $("#sectors_table").jqGrid('GridToForm',gsr,"#fSectorEdit"); 
            $("#fSectorEdit").find("#foper").attr('value','edit');              

            $("#fSectorEdit").find("#bt_add").hide();
            $("#fSectorEdit").find("#bt_edit").show();   
            $("#dialog_editform").dialog('open');          
        
        }
            
    } 
});

jQuery("#sectors_table").jqGrid('navButtonAdd','#sectors_tablePager',{caption:"Видалити",
       id:"btn_sector_del",
	onClickButton:function(){ 

      if ($("#sectors_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити дільницю?');
    
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
                                                      if (cur_dt_change=='') return;        
                                                      
                                                      fSector_ajaxForm[0].id.value = cur_sector_id;
                                                      fSector_ajaxForm[0].change_date.value = cur_dt_change;
                                                      fSector_ajaxForm[0].oper.value = 'del';
                                                      fSector_ajaxForm.ajaxSubmit(form_options);   
                                                      
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

//----------------------------------
jQuery("#accnt_table").jqGrid('navButtonAdd','#accnt_tablePager',{caption:"Видалити з дільниці",
      id:"btn_acc_del",
	onClickButton:function(){ 

      if ($("#accnt_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити абонента з дільниці?');
    
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
                                                      if (cur_dt_change=='') return;        

                                                      var request = $.ajax({
                                                        url: "runner_sectors_accnt_oper_edit.php",
                                                        data: {id_sector: cur_sector_id, oper : 'del_one', change_date:cur_dt_change,id: cur_paccnt_id}
                                                      });

                                                      request.done(function(data ) {
                                                        jQuery('#accnt_table').jqGrid('setGridParam',{'postData':{'p_id':cur_sector_id}}).trigger('reloadGrid');        
                                                        if (data.errcode!==undefined)
                                                           {
                                                            $('#message_zone').append(data.errstr);
                                                            $('#message_zone').append("<br>");
                                                           }
                                                        
                                                      });
                                                      request.fail(function(data ) {alert("error");});
                                                      
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



jQuery("#sectors_table").jqGrid('navButtonAdd','#sectors_tablePager',{caption:"Вибір абон.",
    id:"btn_sector_sel",
    onClickButton:function(){ 

        //$('#accnt_oper_table').trigger('reloadGrid');
        $('#accnt_oper_table').jqGrid('setGridParam',{datatype:'json'}).trigger('reloadGrid');           
        $("#pAccnt_oper_table").dialog({title:'Вибір абонентів на дільницю '+jQuery('#sectors_table').jqGrid('getCell',cur_sector_id,'name')});
        $("#pAccnt_oper_table").dialog('open');
            
    } 
});


jQuery('#accnt_oper_table').jqGrid('navButtonAdd','#accnt_oper_tablePager',{caption:"Чистити фільтр",
	onClickButton:function(){ 
        var sgrid = jQuery("#accnt_oper_table")[0];
        sgrid.clearToolbar();  ;} 
});


if (r_edit!=3)
{
   $('#btn_sector_sel').addClass('ui-state-disabled');
   $('#btn_sector_new').addClass('ui-state-disabled');
   $('#btn_sector_del').addClass('ui-state-disabled');
   $('#btn_sector_edit').addClass('ui-state-disabled');
   $('#btn_acc_del').addClass('ui-state-disabled');
}
//=========================================================================


   jQuery("#btRunnerSel").click( function() {

     createPersonGrid($("#fid_runner").val());
     person_target_id=$("#fid_runner")
     person_target_name =  $("#frunner")
     person_target_prof = 0;
    
     jQuery("#grid_selperson").css({'left': $("#frunner").offset().left+1, 'top': $("#frunner").offset().top+20});
     jQuery("#grid_selperson").toggle( );

   });
   
    jQuery("#btRunnerClear").click( function() { 

        $('#fid_runner').attr('value','' );
        $('#frunner').attr('value','' );    

    });   
   

   jQuery("#btOperatorSel").click( function() {

     createPersonGrid($("#fid_operator").val());
     person_target_id=$("#fid_operator")
     person_target_name =  $("#foperator")
     person_target_prof = 0;
    
     jQuery("#grid_selperson").css({'left': $("#foperator").offset().left+1, 'top': $("#foperator").offset().top+20});
     jQuery("#grid_selperson").toggle( );

   });
   
    jQuery("#btOperatorClear").click( function() { 

        $('#fid_operator').attr('value','' );
        $('#foperator').attr('value','' );    
    });   
   

   jQuery("#btKontrolSel").click( function() {

     createPersonGrid($("#fid_kontrol").val());
     person_target_id=$("#fid_kontrol")
     person_target_name =  $("#fcontroler")
     person_target_prof = 0;
    
     jQuery("#grid_selperson").css({'left': $("#fcontroler").offset().left+1, 'top': $("#fcontroler").offset().top+20});
     jQuery("#grid_selperson").toggle( );

   });

    jQuery("#btKontrolClear").click( function() { 

        $('#fid_kontrol').attr('value','' );
        $('#fcontroler').attr('value','' );    
    });   


  $('#fSectorEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fSectorEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
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
                                          var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
                                          if (cur_dt_change=='') return;        
  
                                          submit_form[0].change_date.value = cur_dt_change;
                                          submit_form.ajaxSubmit(form_options);    
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
                {   form_edit_lock=1;
                    return true;
                }

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
               jQuery('#sectors_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               //var fid = jQuery("#fid").val();
               //if(fid) 
               //{ 
               //  jQuery("#lgt_category_table").jqGrid('FormToGrid',fid,"#fLgtCategoryEdit"); 
               //}  
               
               jQuery('#sectors_table').trigger('reloadGrid');        
               
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

function SelectPersonExternal(id, name) {
        $("#fSectorEdit").find("#fid_runner").attr('value',id );
        $("#fSectorEdit").find("#frunner").attr('value',name );    
    
}


 
