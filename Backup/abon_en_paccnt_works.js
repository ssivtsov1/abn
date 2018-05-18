var works_form_options;
var works_list_mode;
var cur_works_id = null;


jQuery(function(){ 

  //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\  
  jQuery('#paccnt_works_table').jqGrid({
    url:     'abon_en_paccnt_works_data.php',
    editurl: 'abon_en_paccnt_works_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:100,
    width:AllGridWidth,
   // autowidth: true,
    scroll: 0,
    colNames:[], 
    colModel :[  
      {label:'id',name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {label:'id_paccnt',name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center', hidden:true},           
            
      {label:'Дата роботи',name:'dt_work', index:'dt_work', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Тип роботи',name:'idk_work', index:'idk_work', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lworktypes},stype:'text'},
      {label:'Виконавець',name:'position', index:'position', width:200, editable: true, align:'left',edittype:'text'},
      {label:'№ акта',name:'act_num', index:'act_num', width:50, editable: true, align:'left',edittype:'text'},
      {label:'Прим.',name:'note', index:'note', width:100, editable: true, align:'left',edittype:'text'},           
      {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text'},
      {label:'dt',name:'dt_input', index:'dt_input', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{ srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i' }}
    ],
    pager: '#paccnt_works_tablePager',
    rowNum:100,
    sortname: 'dt_work', 
    sortorder: 'asc',
    viewrecords: true,
    pgbuttons: false,
    pgtext: null, 
    gridview: true,
    caption: '',
    hidegrid: false,
    postData:{'p_id': id_paccnt},
    jsonReader : {repeatitems: false},
 
    onSelectRow: function(id) { 
      cur_works_id = id;  
      jQuery('#paccnt_works_indic_table').jqGrid('setGridParam',{datatype:'json','postData':{'w_id':id}}).trigger('reloadGrid');        
    },
      
    ondblClickRow: function(id){ 

      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 

       $("#fpaccnt_params").find("#pid_work").attr('value',cur_works_id );   
       $("#fpaccnt_params").find("#pidk_work").attr('value',jQuery(this).jqGrid('getCell',id,'idk_work') );          
       $("#fpaccnt_params").find("#pmode").attr('value','1' );   
       $("#fpaccnt_params").attr("action","meter_work.php");
       $("#fpaccnt_params").attr('target',"_blank" );           
       document.paccnt_params.submit();
       
       //window.opener.SelectAbonExternal(id,jQuery(this).jqGrid('getCell',id,'last_name')+' '+ 
       // jQuery(this).jqGrid('getCell',id,'name')+' '+
       // jQuery(this).jqGrid('getCell',id,'patron_name'));
      } else {alert("Please select Row")}       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');},
  
  gridComplete:function(){

//    works_list_mode =0; //edit   
    if ($(this).getDataIDs().length > 0) 
    {      
//     $("#pNotliveParam").show();        
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);

    }
  }

  }).navGrid('#paccnt_works_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
  
  
  
  //--------------------------------------------------------
  jQuery('#paccnt_works_indic_table').jqGrid({
    url:     'abon_en_paccnt_works_indic_data.php',
   // datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:120,
    width:AllGridWidth,
   // autowidth: true,
    scroll: 0,
    colNames:[], 
    colModel :[  
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true,sortable:false},
    {name:'id_work', index:'id_work', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_accnt', index:'id_accnt', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_meter', index:'id_meter', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_type_meter', index:'id_type_meter', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_p_indic', index:'id_p_indic', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {label:'№ ліч.',name:'num_meter', index:'num_meter', width:80, editable: false, align:'left',edittype:'text',sortable:false},
    {label:'Тип ліч.',name:'type_meter', index:'type_meter', width:80, editable: false, align:'left',edittype:'text',sortable:false,hidden:false},
//    {label:'Розр. ліч.',name:'carry', index:'carry', width:40, editable: false, align:'left',edittype:'text',sortable:false},
    {label:'К.тр',name:'k_tr', index:'k_tr', width:40, editable: false, align:'right',edittype:'text',sortable:false},
    {label:'Зона',name:'id_zone', index:'id_zone', width:60, editable: false, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text',sortable:false},
    {label:'Показники',name:'indic', index:'indic', width:80, editable: true, align:'right',hidden:false,
            edittype:'text', editrules:{number:true } },
    {label:'Факт.пок.',name:'indic_real', index:'indic_real', width:60, editable: true, align:'right',hidden:false,
            edittype:'text', editrules:{number:true } },

    {label:'Ознака',name:'idk_oper', index:'idk_oper', width:60, editable: false, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lworkmetstatus},stype:'text',sortable:false},

    ],
    pager: '#paccnt_works_indic_tablePager',
    rowNum:100,
    sortname: 'num_meter',
    sortorder: 'asc',
    viewrecords: true,
    pgbuttons: false,
    pgtext: null, 
    gridview: true,
    caption: 'Показники',
    hiddengrid: false,
    postData:{'w_id': cur_works_id},
    jsonReader : {repeatitems: false},
 
    onSelectRow: function(id) { 
      
    },
        
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');},
  
  gridComplete:function(){

//    works_list_mode =0; //edit   
    if ($(this).getDataIDs().length > 0) 
    {      
//     $("#pNotliveParam").show();        
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);

    }
  }

  }).navGrid('#paccnt_works_indic_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
//-------------------------------------------------    

jQuery("#paccnt_works_table").jqGrid('navButtonAdd','#paccnt_works_tablePager',{caption:"Нова ",
        id:"bt_work_new",
	onClickButton:function(){  

       $("#fpaccnt_params").find("#pid_meter").attr('value','-1' );   
       $("#fpaccnt_params").find("#pid_work").attr('value','' );          
       $("#fpaccnt_params").find("#pidk_work").attr('value','' );          
       $("#fpaccnt_params").find("#pmode").attr('value','0' );          
       $("#fpaccnt_params").attr("action","meter_work.php");
       $("#fpaccnt_params").attr('target',"_blank" );           
       document.paccnt_params.submit();
       ;} 
});


jQuery("#paccnt_works_table").jqGrid('navButtonAdd','#paccnt_works_tablePager',{caption:"Видалити",
        id:"bt_work_del",    
	onClickButton:function(){ 

      if ($("#paccnt_works_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                        DeleteWork('del');
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

jQuery("#paccnt_works_table").jqGrid('navButtonAdd','#paccnt_works_tablePager',{caption:"Аварійне видалення",
        id:"bt_work_del_extra",    
	onClickButton:function(){ 

      if ($("#paccnt_works_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити роботу без перевірки ?');
      jQuery("#dialog-confirm").css('background-color','red');
      jQuery("#dialog-confirm").css('color','white');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                        DeleteWork('del_extra');
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
//-------------------------------------------------------------
jQuery("#paccnt_works_table").jqGrid('navButtonAdd','#paccnt_works_tablePager',{caption:"Редагувати",
        id:"bt_work_edit",
	onClickButton:function(){ 

      if ($("#paccnt_works_table").getDataIDs().length == 0) 
       {return} ;    

       $("#fpaccnt_params").find("#pid_work").attr('value',cur_works_id );   
       $("#fpaccnt_params").find("#pidk_work").attr('value',jQuery("#paccnt_works_table").jqGrid('getCell',cur_works_id,'idk_work') );          
       $("#fpaccnt_params").find("#pmode").attr('value','1' );   
       $("#fpaccnt_params").attr("action","meter_work.php");
       $("#fpaccnt_params").attr('target',"_blank" );           
       document.paccnt_params.submit();
          
      ;} 
});

jQuery("#paccnt_works_table").jqGrid('navButtonAdd','#paccnt_works_tablePager',{caption:"Велика таблиця",
        id:"btn_works_fullscreen",
	onClickButton:function(){ 

        if (fullscreen_mode==0)
        {
            jQuery('#btn_meters_fullscreen').addClass('navButton_selected') ;    
            jQuery('#btn_lgt_fullscreen').addClass('navButton_selected') ;    
            jQuery('#btn_dogovor_fullscreen').addClass('navButton_selected') ;    
            jQuery('#btn_plomb_fullscreen').addClass('navButton_selected') ;    
            jQuery('#btn_notlive_fullscreen').addClass('navButton_selected') ;    
            jQuery('#btn_works_fullscreen').addClass('navButton_selected') ;    

            fullscreen_mode=1;
            innerLayout.close('north');     
            $("#paccnt_meters_table").jqGrid('setGridHeight',gred_height+220);      
            $("#paccnt_lgt_table").jqGrid('setGridHeight',gred_height+220);      
            $("#paccnt_dogovor_table").jqGrid('setGridHeight',gred_height+220);      
            $("#paccnt_plomb_table").jqGrid('setGridHeight',gred_height+220);      
            $("#paccnt_notlive_table").jqGrid('setGridHeight',gred_height+220);      
            $("#paccnt_works_table").jqGrid('setGridHeight',gred_height+220);      

        }
        else
        {
            jQuery('#btn_meters_fullscreen').removeClass('navButton_selected') ;   
            jQuery('#btn_lgt_fullscreen').removeClass('navButton_selected') ;    
            jQuery('#btn_dogovor_fullscreen').removeClass('navButton_selected') ;    
            jQuery('#btn_plomb_fullscreen').removeClass('navButton_selected') ;    
            jQuery('#btn_notlive_fullscreen').removeClass('navButton_selected') ;    
            jQuery('#btn_works_fullscreen').removeClass('navButton_selected') ;    

            fullscreen_mode=0;
            $("#paccnt_meters_table").jqGrid('setGridHeight',gred_height);
            
            $("#paccnt_lgt_table").jqGrid('setGridHeight',gred_height);      
            $("#paccnt_dogovor_table").jqGrid('setGridHeight',gred_height);      
            $("#paccnt_plomb_table").jqGrid('setGridHeight',gred_height);      
            $("#paccnt_notlive_table").jqGrid('setGridHeight',gred_height);      
            $("#paccnt_works_table").jqGrid('setGridHeight',gred_height);      
            
            innerLayout.open('north');     

        }
          
        ;} 
});

if (r_work_edit!=3)
{
    $('#bt_work_edit').addClass('ui-state-disabled');
    $('#bt_work_del').addClass('ui-state-disabled');
    $('#bt_work_new').addClass('ui-state-disabled');
}

if (r_work_extra_edit!=3)
{
    $('#bt_work_del_extra').addClass('ui-state-disabled');
}

//-------------------------------------------------------------

});

function  DeleteWork( del_mode )
{

$.ajaxSetup({  type: "POST",   dataType: "json" });

 var request = $.ajax({
     url: "meter_work_edit.php",
     type: "POST",
     data: {id : cur_works_id,
            oper : del_mode
     },
     dataType: "json" });

 request.done(function(data ) {
     
  
  if (data.errcode!=undefined)
    {    
      $('#message_zone').append(data.errstr);  
      $('#message_zone').append("<br>"); 
      if (data.errcode==2)
         jQuery("#message_zone").dialog('open');
      else
      {
         $("#paccnt_meters_table").jqGrid('setGridParam',{'postData':{'p_id': id_paccnt, 'free_only': 0, 'hist_mode': 0}}).trigger('reloadGrid');                                 
         
         jQuery('#paccnt_works_table').trigger('reloadGrid');             
         jQuery('#paccnt_works_indic_table').trigger('reloadGrid');             
         
      }
    }

 });
 request.fail(function(data ) { alert("error"); });

};