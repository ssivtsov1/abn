var lastSelSwitch;
var switch_target_id;
var switch_target_name;

var isSwitchGridCreated = false;

var createSwitchGrid = function(){ 
    
  if (isSwitchGridCreated) return;
  isSwitchGridCreated =true;

  jQuery('#dov_switch_table').jqGrid({
    url:'dov_switch_data.php',
    editurl: 'dov_switch_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:350,
    width:400,
    colNames:['Код','Назва','ГОСТ', 'Напруга ном.','Ток ном.','Напруга макс.','Ток макс.','Група'],
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
      {name:'voltage_max', index:'voltage_max', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},
                        formatoptions: { defaultValue: ' '}},           
      {name:'amperage_max', index:'amperage_max', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},
                        formatoptions: { defaultValue: ' '}},                 
      {name:'id_gr', index:'id_gr', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lswgrp} }
    ],
    pager: '#dov_switch_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Комутаційне обладнання',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelSwitch = rowid; 
    },
    
    ondblClickRow: function(id){ 
        switch_target_id.val(jQuery(this).jqGrid('getCell',lastSelSwitch,'id') ); 
        switch_target_name.val(jQuery(this).jqGrid('getCell',lastSelSwitch,'name') );
        switch_target_name.focus();
        jQuery('#grid_selswitch').toggle( );
    } ,  


  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_switch_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_dov_switch_table").append("<button class ='btnOk' id='bt_switchsel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_dov_switch_table").append("<button class ='btnClose' id='bt_switchclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

 
jQuery("#dov_switch_table").jqGrid('navButtonAdd','#dov_switch_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_switch_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#dov_switch_table").jqGrid('filterToolbar','');

jQuery('#bt_switchclose0').click( function() { jQuery('#grid_selswitch').toggle( ); }); 

jQuery('#bt_switchsel0').click( function() { 
    switch_target_id.val(jQuery('#dov_switch_table').jqGrid('getCell',lastSelSwitch,'id') ); 
    switch_target_name.val(jQuery('#dov_switch_table').jqGrid('getCell',lastSelSwitch,'name') ); 
    switch_target_name.focus();

    jQuery('#grid_selswitch').toggle( );
}); 

jQuery('#grid_selswitch').draggable({ handle: ".ui-jqgrid-titlebar" });

}; 

