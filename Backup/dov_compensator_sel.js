var lastSelCompens;
var compens_target_id;
var compens_target_name;

var isCompensGridCreated = false;

var createCompensGrid = function(){ 
    
  if (isCompensGridCreated) return;
  isCompensGridCreated =true;

  jQuery('#dov_compensator_table').jqGrid({
    url:'dov_compensator_data.php',
    editurl: 'dov_compensator_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    width:500,
    colNames:['Код','Назва','ГОСТ','Напруга перв.','Ток перв.','Напруга макс.','Ток макс.','Напруга втор.','Ток втор.', 
     'Потужність, кВА','Ток ХХ,%','Напруга КЗ,%','Потери ХХ,кВт','Потери КЗ,кВт','*'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true , hidden:true },     
      {name:'name', index:'name', width:120, editable: true, align:'left',edittype:'text',formoptions:{colpos:1,rowpos:1},
                            editoptions:{size:35}},           
      {name:'normative', index:'normative', width:40, hidden:true, editable: true, align:'left',edittype:'text',
                            editrules:{edithidden:true},formoptions:{colpos:2,rowpos:1}}, 
      {name:'voltage_nom', index:'voltage_nom', width:100, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},formoptions:{colpos:1,rowpos:3} },           
      {name:'amperage_nom', index:'amperage_nom', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},formoptions:{colpos:2,rowpos:3} },                 
      {name:'voltage_max', index:'voltage_max', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},formoptions:{colpos:1,rowpos:4} },           
      {name:'amperage_max', index:'amperage_max', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},formoptions:{colpos:2,rowpos:4} }, 
      {name:'voltage2_nom', index:'voltage2_nom', width:100, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},formoptions:{colpos:1,rowpos:5} },           
      {name:'amperage2_nom', index:'amperage2_nom', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},formoptions:{colpos:2,rowpos:5} },                 
      {name:'power_nom', index:'power_nom', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer',editrules:{number:true,edithidden:true},formoptions:{colpos:1,rowpos:6} },           
      {name:'amperage_no_load', index:'amperage_no_load', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},formoptions:{colpos:2,rowpos:7} },           
      {name:'voltage_short_circuit', index:'voltage_short_circuit', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},formoptions:{colpos:1,rowpos:7} },           
      {name:'iron', index:'iron', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},formoptions:{colpos:1,rowpos:8} },           
      {name:'copper', index:'copper', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},formoptions:{colpos:2,rowpos:8} },           
      {name:'show_def', index:'show_def', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}}


    ],
    pager: '#dov_compensator_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: false,
    gridview: true,
    caption: 'Трансформатори',
    hidegrid: false,    
    //hiddengrid: false,
    toolbar: [true,'top'],
    
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:620,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
      
   onSelectRow: function(rowid) { 
        lastSelCompens = rowid; 
    },
    
    //ondblClickRow: function(id){ 
    //      jQuery(this).editGridRow(id,{width:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
    ondblClickRow: function(id){ 
        compens_target_id.val(jQuery(this).jqGrid('getCell',lastSelCompens,'id') ); 
        compens_target_name.val(jQuery(this).jqGrid('getCell',lastSelCompens,'name') );
        compens_target_name.focus();
        jQuery('#grid_selcompens').toggle( );
    } ,  
      
    loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_compensator_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_dov_compensator_table").append("<button class ='btnOk' id='bt_compenssel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_dov_compensator_table").append("<button class ='btnClose' id='bt_compensclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

jQuery("#dov_compensator_table").jqGrid('navButtonAdd','#dov_compensator_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_compensator_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#dov_compensator_table").jqGrid('filterToolbar','');


jQuery('#bt_compensclose0').click( function() { jQuery('#grid_selcompens').toggle( ); }); 


jQuery('#bt_compenssel0').click( function() { 
    compens_target_id.val(jQuery('#dov_compensator_table').jqGrid('getCell',lastSelCompens,'id') ); 
    compens_target_name.val(jQuery('#dov_compensator_table').jqGrid('getCell',lastSelCompens,'name') ); 
    compens_target_name.focus();

    jQuery('#grid_selcompens').toggle( );
}); 

}; 
 
