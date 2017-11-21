var lastSelFuse;
var fuse_target_id;
var fuse_target_name;

var isFuseGridCreated = false;

var createFuseGrid = function(){ 
    
  if (isFuseGridCreated) return;
  isFuseGridCreated =true;

  jQuery('#dov_fuse_table').jqGrid({
    url:'dov_fuse_data.php',
    editurl: 'dov_fuse_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:350,
    width:400,
    colNames:['Код','Назва','ГОСТ', 'Напруга ном.','Ток ном.','Потужн.'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true , hidden:true },     
      {name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text'},           
      {name:'normative', index:'normative', width:40, hidden:true, editable: true, align:'left',edittype:'text',
                            editrules:{edithidden:true},formatoptions: { defaultValue: ' '}},           
      {name:'voltage_nom', index:'voltage_nom', width:100, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},
                        formatoptions: { defaultValue: ' '}},           
      {name:'amperage_nom', index:'amperage_nom', width:100, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},
                        formatoptions: { defaultValue: ' '}},                 
      {name:'power_nom', index:'power_nom', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},
                        formatoptions: { defaultValue: ' '}}
                    
    ],
    pager: '#dov_fuse_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: false,
    gridview: true,
    caption: 'Запобіжники',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelFuse = rowid; 
    },
    
    ondblClickRow: function(id){ 
        fuse_target_id.val(jQuery(this).jqGrid('getCell',lastSelFuse,'id') ); 
        fuse_target_name.val(jQuery(this).jqGrid('getCell',lastSelFuse,'name') );
        fuse_target_name.focus();
        jQuery('#grid_selfuse').toggle( );
    } ,  


  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_fuse_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_dov_fuse_table").append("<button class ='btnOk' id='bt_fusesel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_dov_fuse_table").append("<button class ='btnClose' id='bt_fuseclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });


jQuery('#bt_fuseclose0').click( function() { jQuery('#grid_selfuse').toggle( ); }); 

jQuery('#bt_fusesel0').click( function() { 
    fuse_target_id.val(jQuery('#dov_fuse_table').jqGrid('getCell',lastSelFuse,'id') ); 
    fuse_target_name.val(jQuery('#dov_fuse_table').jqGrid('getCell',lastSelFuse,'name') ); 
    fuse_target_name.focus();

    jQuery('#grid_selfuse').toggle( );
}); 

}; 

