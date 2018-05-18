var lastSelSmart;
var smart_target_id;
var smart_target_ident;
var smart_target_addr;
var smart_target_book;


var isSmartGridCreated = false;

var createSmartGrid = function(){ 
    
  if (isSmartGridCreated) return;
  isSmartGridCreated =true;

  jQuery('#dov_smart_table').jqGrid({
    url:'dov_smart_data.php',
    //editurl: 'dov_switch_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:300,
    width:500,
    colNames:['Код','Ідент','addr', 'Адреса','Файл','Папка','Книга','Рах.'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true },     
      {name:'ident', index:'ident', width:50, editable: true, align:'left',edittype:'text'},  
      {name:'addr', index:'addr', width:50, editable: true, align:'right',hidden:true,edittype:'text'},           
      {name:'addr_str', index:'addr_str', width:200, editable: true, align:'left',edittype:'text'},           
      {name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text',hidden:true},           
      {name:'path', index:'path', width:200, editable: true, align:'left',edittype:'text',hidden:true},           
      {name:'book', index:'book', width:50, editable: true, align:'left',edittype:'text'},           
      {name:'code', index:'code', width:50, editable: true, align:'left',edittype:'text'}
    ],
    pager: '#dov_smart_tablePager',
    rowNum:500,
    //rowList:[20,50,100,300,500],
    pgbuttons: false,
    pgtext: null, 

    sortname: 'ident',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Будинки зі СМАРТ',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelSmart = rowid; 
    },
    
    ondblClickRow: function(id){ 
        smart_target_id.val(jQuery(this).jqGrid('getCell',lastSelSmart,'id') ); 
        smart_target_ident.val(jQuery(this).jqGrid('getCell',lastSelSmart,'ident') );
        smart_target_addr.val(jQuery(this).jqGrid('getCell',lastSelSmart,'addr_str') );
        smart_target_book.val(jQuery(this).jqGrid('getCell',lastSelSmart,'book') );
        smart_target_ident.focus();
        jQuery('#grid_selsmart').toggle( );
    } ,  


  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_smart_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_dov_smart_table").append("<button class ='btnOk' id='bt_smartsel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_dov_smart_table").append("<button class ='btnClose' id='bt_smartclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

 
jQuery("#dov_smart_table").jqGrid('navButtonAdd','#dov_smart_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_smart_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#dov_smart_table").jqGrid('filterToolbar','');

jQuery('#bt_smartclose0').click( function() { jQuery('#grid_selsmart').toggle( ); }); 

jQuery('#bt_smartsel0').click( function() { 
 smart_target_id.val(jQuery(this).jqGrid('getCell',lastSelSmart,'id') ); 
 smart_target_ident.val(jQuery(this).jqGrid('getCell',lastSelSmart,'ident') );
 smart_target_addr.val(jQuery(this).jqGrid('getCell',lastSelSmart,'addr_str') );
 smart_target_book.val(jQuery(this).jqGrid('getCell',lastSelSmart,'book') );
 smart_target_ident.focus();

    jQuery('#grid_selsmart').toggle( );
}); 

jQuery('#grid_selsmart').draggable({ handle: ".ui-jqgrid-titlebar" });

}; 

