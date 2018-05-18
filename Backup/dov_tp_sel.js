var lastSelTp;
var tp_target_id;
var tp_target_name;

var isTpGridCreated = false;

var createTpGrid = function(){ 
    
  if (isTpGridCreated) 
      {
        //jQuery('#dov_tp_table').find(".ui-search-toolbar input").focus();
        
        //jQuery("#grid_seltp input:enabled:visible:first").focus();
        //jQuery("#gs_name").focus();
        return;
      }
  isTpGridCreated =true;

  jQuery('#dov_tp_table').jqGrid({
    url:'dov_tp_data.php',
    editurl: 'dov_tp_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:250,
    width:500,
    colNames:['Код','Назва','addr', 'Адреса','id fider','Фідер','Дата встановл.','Напруга','Потужність','Абон.ТП'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true },     
      {name:'name', index:'name', width:150, editable: true, align:'left',edittype:'text'},           
      {name:'addr', index:'addr', width:50, editable: true, align:'right',hidden:true,
                           edittype:'text'},           
      {name:'addr_str', index:'addr_str', width:200, editable: true, align:'left',edittype:'text'},           
      {name:'id_fider', index:'id_fider', width:50, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'integer'},           
      {name:'fider', index:'fider', width:150, editable: true, align:'left',edittype:'text'},           

      {name:'dt_install', index:'dt_install', width:80, editable: true, align:'left',edittype:'text',formatter:'date',hidden:true },
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
    viewrecords: false,
    gridview: true,
    caption: 'Підстанції',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelTp = rowid; 
    },
    
    ondblClickRow: function(id){ 
        tp_target_id.val(jQuery(this).jqGrid('getCell',lastSelTp,'id') ); 
        tp_target_name.val(jQuery(this).jqGrid('getCell',lastSelTp,'name') );
        tp_target_name.focus();
        jQuery('#grid_seltp').toggle( );
    } ,  


  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_tp_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_dov_tp_table").append("<button class ='btnOk' id='bt_tpsel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_dov_tp_table").append("<button class ='btnClose' id='bt_tpclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

 
jQuery("#dov_tp_table").jqGrid('navButtonAdd','#dov_tp_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_tp_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#dov_tp_table").jqGrid('filterToolbar','');

jQuery('#bt_tpclose0').click( function() { jQuery('#grid_seltp').toggle( ); }); 

jQuery('#bt_tpsel0').click( function() { 
    tp_target_id.val(jQuery('#dov_tp_table').jqGrid('getCell',lastSelTp,'id') ); 
    tp_target_name.val(jQuery('#dov_tp_table').jqGrid('getCell',lastSelTp,'name') ); 
    tp_target_name.focus();

    jQuery('#grid_seltp').toggle( );
}); 

jQuery('#grid_seltp').draggable({ handle: ".ui-jqgrid-titlebar" });

jQuery("#dov_tp_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
        
    tp_target_id.val(jQuery('#dov_tp_table').jqGrid('getCell',lastSelTp,'id') ); 
    tp_target_name.val(jQuery('#dov_tp_table').jqGrid('getCell',lastSelTp,'name') ); 
    tp_target_name.focus();

    jQuery('#grid_seltp').toggle( );
} } );

//jQuery('#grid_seltp').focus();
//jQuery('#grid_seltp').find("input[type='text']:visible:enabled:first").focus();
}; 

