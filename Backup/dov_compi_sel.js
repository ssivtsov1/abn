var lastSelCompI;
var compi_target_id =null;
var compi_target_name=null;
var compi_target_ktr=null;
//var compi_target_carry;
var isCompIGridCreated = false;

var createCompIGrid = function(){ 
    
  if (isCompIGridCreated) return;
  isCompIGridCreated =true;
    
  jQuery('#dov_compi_table').jqGrid({
    url:'dov_compi_data.php',
    editurl: 'dov_compi_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    width:500,
    colNames:['Код','Назва','ГОСТ','Тип','К.тр', 'Напруга перв.','Ток перв.','Напруга втор.','Ток втор.', 'Фазність'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true },     
      {name:'name', index:'name', width:140, editable: true, align:'left',edittype:'text'},           
      {name:'normative', index:'normative', width:40, hidden:true, editable: true, align:'left',edittype:'text',
                            editrules:{edithidden:true}}, 
      {name:'conversion', index:'conversion', width:80, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:': ;1:Ток;2:Напруга'}, stype:'select' },                       
      {name:'k_tr', index:'k_tr', width:50, editable: false, align:'center'},                                                     
      {name:'voltage_nom', index:'voltage_nom', width:100, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },           
      {name:'amperage_nom', index:'amperage_nom', width:100, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },                 
      {name:'voltage2_nom', index:'voltage2_nom', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },           
      {name:'amperage2_nom', index:'amperage2_nom', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },                 
      {name:'phase', index:'phase', width:100, editable: true, align:'right',hidden:true,
                            edittype:'select',formatter:'select',editoptions:{value:lphase} }  
    ],
    pager: '#dov_compi_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: false,
    gridview: true,
    caption: 'Вимірювальні тр.',
    hidegrid: false,
    toolbar: [true,'top'],
    
    onSelectRow: function(rowid) { 
        lastSelCompI = rowid; 
        jQuery('#pdebug_ci').html(  jQuery(this).jqGrid('getCell',rowid,'name'))   
    },

    ondblClickRow: function(id){ 
        compi_target_id.val(jQuery(this).jqGrid('getCell',lastSelCompI,'id') ); 
        compi_target_name.val(jQuery(this).jqGrid('getCell',lastSelCompI,'name') );
        if (compi_target_ktr!=null)
        {
           compi_target_ktr.val(jQuery(this).jqGrid('getCell',lastSelCompI,'k_tr') );
        }
        //meter_target_carry.val(jQuery('#dov_meters_table').jqGrid('getCell',lastSelCompI,'carry') );         
        jQuery('#grid_selci').toggle( );
    } ,  
    
     
    loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_compi_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

    $("#t_dov_compi_table").append("<button class ='btnOk' id='bt_cisel0' style='height:20px;font-size:-3' > Выбор </button> ");
    $("#t_dov_compi_table").append("<button class ='btnClose' id='bt_ciclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
    jQuery("#dov_compi_table").jqGrid('filterToolbar','');
    jQuery('#dov_compi_table').jqGrid('navButtonAdd','#dov_compi_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_compi_table")[0];
        sgrid.clearToolbar();  ;} 
    });
    
    jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
    jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

jQuery('#bt_ciclose0').click( function() { jQuery('#grid_selci').toggle( ); }); 

jQuery('#bt_cisel0').click( function() { 
    compi_target_id.val(jQuery('#dov_compi_table').jqGrid('getCell',lastSelCompI,'id') ); 
    compi_target_name.val(jQuery('#dov_compi_table').jqGrid('getCell',lastSelCompI,'name') ); 
    //meter_target_carry.val(jQuery('#dov_compi_table').jqGrid('getCell',lastSelMeter,'carry') ); 
    jQuery('#grid_selci').toggle( );
}); 

}; 
 
