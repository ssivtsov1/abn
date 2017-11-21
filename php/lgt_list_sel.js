var lastSelLgt; 
var lgt_target_id=0;
var lgt_target_name=0;
var lgt_target_code=0;
var lgt_target_id_calc=0;
var lgt_target_name_calc=0;

var isLgtGridCreated = false;

var createLgtsGrid = function(){ 
    
  if (isLgtGridCreated) return;
  isLgtGridCreated =true;

  jQuery('#lgt_sel_table').jqGrid({
    url:'lgt_list_sel_data.php',
    //editurl: 'dov_fider_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:300,
    width:600,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {label:"Код",name:'ident', index:'ident', width:50, editable: true, align:'left',edittype:'text'},           
      {label:"Доп.код",name:'alt_code', index:'alt_code', width:50, editable: true, align:'left',edittype:'text'},
      {label:"КФК",name:'kfk_code', index:'kfk_code', width:50, editable: true, align:'left',edittype:'text', hidden:true},
      {label:'Пільга',name:'name', index:'name', width:200, editable: true, align:'left',edittype:"text"},
      {label:'Назва для рахунку',name:'bill_name', index:'bill_name', width:120, editable: true, align:'left',edittype:"text", hidden:true},
      {label:'id_kategor',name:'id_kategor', index:'id_kategor', width:40, editable: true, align:'right', hidden:true},
      {label:'Група',name:'kategor', index:'kategor', width:120, editable: true, align:'left',hidden:true},

      {label:'Бюджет',name:'id_budjet', index:'id_budjet', width:70, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lgi_budjet},stype:'select'},                       
      {label:'Метод розрахунку',name:'id_calc', index:'id_calc', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lgi_calc},stype:'select'},                       
      {label:'calc_name',name:'calc_name', index:'calc_name', width:100, editable: false, align:'left',hidden:true},

                        
      {label:'Стан',name:'state', index:'state', width:70, editable: true, align:'left',edittype:'text'}, 
                        
      {label:'Дата початкова',name:'dt_b', index:'dt_b', width:100, editable: true, 
                        align:'left',edittype:'text',formatter:'date', editrules:{required:true}},
      
      {label:'Дата кінцева',name:'dt_e', index:'dt_e', width:100, editable: true, 
                        align:'left',edittype:'text',formatter:'date', editrules:{required:false}, hidden:true},

      {name:'work_period', index:'work_period', width:80, editable: true, align:'left',edittype:'text', hidden:true},
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},        

    ],
    pager: '#lgt_sel_tablePager',
    rowNum:100,
    rowList:[20,50,100,300,500],
    sortname: 'dt',
    sortorder: 'desc',
    viewrecords: false,
    gridview: true,
    caption: 'Пільги',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelLgt = rowid; 
    },
    
    ondblClickRow: function(id){ 
        lgt_target_id.val(jQuery(this).jqGrid('getCell',lastSelLgt,'id') ); 

        if (lgt_target_name!=0)  lgt_target_name.val(jQuery(this).jqGrid('getCell',lastSelLgt,'name') );
        if (lgt_target_code!=0)  lgt_target_code.val(jQuery(this).jqGrid('getCell',lastSelLgt,'ident') );
        
        if (lgt_target_id_calc!=0)  lgt_target_id_calc.val(jQuery(this).jqGrid('getCell',lastSelLgt,'id_calc') );
        if (lgt_target_name_calc!=0)  lgt_target_name_calc.val(jQuery(this).jqGrid('getCell',lastSelLgt,'calc_name') );
        
        lgt_target_name.change();
        lgt_target_name.focus();
        jQuery('#grid_sellgt').toggle( );
    } ,  


  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#lgt_sel_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_lgt_sel_table").append("<button class ='btnOk' id='bt_lgtsel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_lgt_sel_table").append("<button class ='btnClose' id='bt_lgtclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

 
jQuery("#lgt_sel_table").jqGrid('navButtonAdd','#lgt_sel_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#lgt_sel_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#lgt_sel_table").jqGrid('filterToolbar','');

jQuery('#bt_lgtclose0').click( function() { jQuery('#grid_sellgt').toggle( ); }); 

jQuery('#bt_lgtsel0').click( function() { 
    lgt_target_id.val(jQuery('#lgt_sel_table').jqGrid('getCell',lastSelLgt,'id') ); 

    if (lgt_target_name!=0)  lgt_target_name.val(jQuery('#lgt_sel_table').jqGrid('getCell',lastSelLgt,'name') );
    if (lgt_target_code!=0)  lgt_target_code.val(jQuery('#lgt_sel_table').jqGrid('getCell',lastSelLgt,'ident') );
    if (lgt_target_id_calc!=0)  lgt_target_id_calc.val(jQuery('#lgt_sel_table').jqGrid('getCell',lastSelLgt,'id_calc') );
    if (lgt_target_name_calc!=0)  lgt_target_name_calc.val(jQuery('#lgt_sel_table').jqGrid('getCell',lastSelLgt,'calc_name') );

    lgt_target_name.change();
    lgt_target_name.focus();

    jQuery('#grid_sellgt').toggle( );
}); 

jQuery('#grid_sellgt').draggable({ handle: ".ui-jqgrid-titlebar" });
}; 

