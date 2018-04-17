var lastSelTarif;
var tarif_target_id;
var tarif_target_name;

var isGridCreated = false;

var createCGrid = function(){ 
    
  if (isGridCreated) return;
  isGridCreated =true;

  jQuery('#controlers_counters_table').jqGrid({
    url:'controlers_counters_data.php',
    datatype: 'json',
    mtype: 'POST',
    height:800,
    width:900,
    colNames:['sector','runner','place1','place2','place3','place4','place5','place6'],
    colModel :[ 
      {label:'Дільниця',name:'sector', index:'sector', width:300, editable: true, align:'left'},
      {label:'Контролер',name:'runner', index:'runner', width:200, editable: true, align:'left'},
      {label:'Кільк.(прим.)',name:'place1', index:'place1', width:60, editable: true, align:'center'},
      {label:'Кільк.(С.К.)',name:'place2', index:'place2', width:60, editable: true, align:'center'},
      {label:'Кільк.(винос. конт.)',name:'place3', index:'place3', width:60, editable: true, align:'center'},
      {label:'Кільк.(ВБШ)', name:'place4',index:'place4', width:60, editable: true, align:'center'},
      {label:'Кільк.(У кв.)', name:'place5', index:'place5', width:60, editable: true, align:'center'},
      {label:'Кільк.(буд.)', name:'place6', index:'place6', width:60, editable: true, align:'center'}
      //{label:'Кільк.(буд.)', name:'place6', index:'place6', width:60, editable: true, align:'center'}
    ],
    pager: '#controlers_counters_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],

    pgbuttons: false,
    pgtext: null, 

    sortname: 'sector',
    sortorder: 'asc',
    viewrecords: false,
    gridview: true,
    caption: 'Звіт по кількості лічильників по місцям установки',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelTarif = rowid; 
    },
    
//    ondblClickRow: function(id){ 
//        tarif_target_id.val(jQuery(this).jqGrid('getCell',lastSelTarif,'id') ); 
//        tarif_target_name.val(jQuery(this).jqGrid('getCell',lastSelTarif,'nm') );
//        tarif_target_name.focus();
//        jQuery('#grid_seltarif').toggle( );
//    } ,  


  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#controlers_counters_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

//$("#t_dov_tarif_table").append("<button class ='btnOk' id='bt_tarsel0' style='height:20px;font-size:-3' > Выбор </button> ");
//$("#t_dov_tarif_table").append("<button class ='btnClose' id='bt_tarclose0' style='height:20px;font-size:-3' > Закр. </button> ");
//    
//jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
//jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

 
//jQuery("#controlers_counters_table").jqGrid('navButtonAdd','#controlers_counters_tablePager',{caption:"Все",
//	onClickButton:function(){ var sgrid = jQuery("#controlers_counters_table")[0];
//        sgrid.clearToolbar();  } 
//});
//
//jQuery("#controlers_counters_table").jqGrid('filterToolbar','');
//
//jQuery('#bt_tarclose0').click( function() { jQuery('#grid_seltarif').toggle( ); }); 
//
//jQuery('#bt_tarsel0').click( function() { 
//    tarif_target_id.val(jQuery('#dov_tarif_table').jqGrid('getCell',lastSelTarif,'id') ); 
//    tarif_target_name.val(jQuery('#controlers_counters_table').jqGrid('getCell',lastSelTarif,'nm') ); 
//    tarif_target_name.focus();
//
//    jQuery('#grid_seltarif').toggle( );
//}); 

//jQuery('#grid_seltarif').draggable({ handle: ".ui-jqgrid-titlebar" });
//
//jQuery("#controlers_counters_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
//        
//    tarif_target_id.val(jQuery('#controlers_counters_table').jqGrid('getCell',lastSelTarif,'id') ); 
//    tarif_target_name.val(jQuery('#controlers_counters_table').jqGrid('getCell',lastSelTarif,'sector') ); 
//    tarif_target_name.focus();
//
//    jQuery('#grid_seltarif').toggle( );  
//} } );

}; 

