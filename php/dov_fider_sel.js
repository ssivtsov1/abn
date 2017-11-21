var lastSelFider;
var fider_target_id;
var fider_target_name;

var isFiderGridCreated = false;

var createFiderGrid = function(){ 
    
  if (isFiderGridCreated) return;
  isFiderGridCreated =true;

  jQuery('#dov_fider_table').jqGrid({
    url:'dov_fider_data.php',
    editurl: 'dov_fider_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:300,
    width:300,
    colNames:['Код','Назва','Напруга'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true },     
      {name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text'},           
      {name:'id_voltage', index:'id_voltage', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lvolt} }                       
    ],
    pager: '#dov_fider_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: false,
    gridview: true,
    caption: 'Фідери',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelFider = rowid; 
    },
    
    ondblClickRow: function(id){ 
        fider_target_id.val(jQuery(this).jqGrid('getCell',lastSelFider,'id') ); 
        fider_target_name.val(jQuery(this).jqGrid('getCell',lastSelFider,'name') );
        fider_target_name.focus();
        jQuery('#grid_selfider').toggle( );
    } ,  


  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_fider_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_dov_fider_table").append("<button class ='btnOk' id='bt_fidersel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_dov_fider_table").append("<button class ='btnClose' id='bt_fiderclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

 
jQuery("#dov_fider_table").jqGrid('navButtonAdd','#dov_fider_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_fider_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#dov_fider_table").jqGrid('filterToolbar','');

jQuery('#bt_fiderclose0').click( function() { jQuery('#grid_selfider').toggle( ); }); 

jQuery('#bt_fidersel0').click( function() { 
    fider_target_id.val(jQuery('#dov_fider_table').jqGrid('getCell',lastSelFider,'id') ); 
    fider_target_name.val(jQuery('#dov_fider_table').jqGrid('getCell',lastSelFider,'name') ); 
    fider_target_name.focus();

    jQuery('#grid_selfider').toggle( );
}); 

jQuery('#grid_selfider').draggable({ handle: ".ui-jqgrid-titlebar" });
}; 

