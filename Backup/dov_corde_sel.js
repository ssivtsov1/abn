var lastSelCorde;
var corde_target_id;
var corde_target_name;

var isCordeGridCreated = false; 

var createCordeGrid = function(){ 
    
  if (isCordeGridCreated) return;
  isCordeGridCreated =true;

  jQuery('#dov_corde_table').jqGrid({
    url:'dov_corde_data.php',
    editurl: 'dov_corde_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:350,
    width:400,
    colNames:['Код','Назва','ГОСТ', 'Напруга ном.','Ток ном.','Напруга макс.','Ток макс.','S,мм2','Матеріал','D,мм','Проволок','Ro,ом/км','Xo,ом/км','Po,кВт/км','*'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true , hidden:true},     
      {name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text'},           
      {name:'normative', index:'normative', width:40, hidden:true, editable: true, align:'left',edittype:'text',
                            editrules:{edithidden:true}},           
      {name:'voltage_nom', index:'voltage_nom', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },           
      {name:'amperage_nom', index:'amperage_nom', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },                 
      {name:'voltage_max', index:'voltage_max', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },           
      {name:'amperage_max', index:'amperage_max', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },                 

      {name:'s_nom', index:'s_nom', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },           
                        
      {name:'materal', index:'materal', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lmateral},stype:'select' },                       
      {name:'cord_diam', index:'cord_diam', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },           
      {name:'cord_qn', index:'cord_qn', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'integer',editrules:{number:true,edithidden:true} },           
      {name:'ro', index:'ro', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },           
      {name:'xo', index:'xo', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },           
      {name:'dpo', index:'dpo', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },           
      {name:'show_def', index:'show_def', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}}
                            
    ],
    pager: '#dov_corde_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: false,
    gridview: true,
    caption: 'Провода',
    hidegrid: false,        
    //hiddengrid: false,
    toolbar: [true,'top'],    
   // ondblClickRow: function(id){ 
   //   jQuery(this).editGridRow(id,{width:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
     
   onSelectRow: function(rowid) { 
        lastSelCorde = rowid; 
    },
    
    ondblClickRow: function(id){ 
        corde_target_id.val(jQuery(this).jqGrid('getCell',lastSelCorde,'id') ); 
        corde_target_name.val(jQuery(this).jqGrid('getCell',lastSelCorde,'name') );
        corde_target_name.focus();
        jQuery('#grid_selcorde').toggle( );
    } ,  
     
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_corde_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_dov_corde_table").append("<button class ='btnOk' id='bt_cordesel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_dov_corde_table").append("<button class ='btnClose' id='bt_cordeclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

jQuery("#dov_corde_table").jqGrid('navButtonAdd','#dov_corde_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_corde_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#dov_corde_table").jqGrid('filterToolbar','');

jQuery('#bt_cordeclose0').click( function() { jQuery('#grid_selcorde').toggle( ); }); 

jQuery('#bt_cordesel0').click( function() { 
    corde_target_id.val(jQuery('#dov_corde_table').jqGrid('getCell',lastSelCorde,'id') ); 
    corde_target_name.val(jQuery('#dov_corde_table').jqGrid('getCell',lastSelCorde,'name') ); 
    corde_target_name.focus();

    jQuery('#grid_selcorde').toggle( );
}); 


}; 
 

