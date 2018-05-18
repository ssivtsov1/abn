var lastSelAddParam;
var param_target_id;
var param_target_name;

var isAddParamGridCreated = false;

var createAddParamGrid = function(){ 
    
  if (isAddParamGridCreated) return;
  isAddParamGridCreated =true;

  jQuery('#dov_param_table').jqGrid({
    url:'dov_addparam_data.php',
    //editurl: 'dov_param_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:350,
    width:400,
    colNames:['Код','Назва'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true , hidden:false },     
      {name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text'},           
    ],
    pager: '#dov_param_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: false,
    gridview: true,
    caption: 'Додаткова ознака',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelAddParam = rowid; 
    },
    
    ondblClickRow: function(id){ 
        param_target_id.val(jQuery(this).jqGrid('getCell',lastSelAddParam,'id') ); 
        param_target_name.val(jQuery(this).jqGrid('getCell',lastSelAddParam,'name') );
        param_target_name.focus();
        jQuery('#grid_selparam').toggle( );
    } ,  


  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_param_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_dov_param_table").append("<button class ='btnOk' id='bt_paramsel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_dov_param_table").append("<button class ='btnClose' id='bt_paramclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });
jQuery("#dov_param_table").jqGrid('filterToolbar','');

jQuery('#bt_paramclose0').click( function() { jQuery('#grid_selparam').toggle( ); }); 

jQuery('#bt_paramsel0').click( function() { 
    param_target_id.val(jQuery('#dov_param_table').jqGrid('getCell',lastSelAddParam,'id') ); 
    param_target_name.val(jQuery('#dov_param_table').jqGrid('getCell',lastSelAddParam,'name') ); 
    param_target_name.focus();

    jQuery('#grid_selparam').toggle( );
}); 

}; 

