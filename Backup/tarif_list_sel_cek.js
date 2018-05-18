var lastSelTarif;
var tarif_target_id;
var tarif_target_name;

var isTarifGridCreated = false;

var createTarifGrid = function(){ 
    
  if (isTarifGridCreated) return;
  isTarifGridCreated =true;

  jQuery('#dov_tarif_table').jqGrid({
    url:'tarif_list_grp_data.php',
    //editurl: 'dov_fider_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:300,
    width:400,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {label:'Тариф',name:'nm', index:'nm', width:300, editable: true, align:'left'},
      {label:'Скорочена назва',name:'sh_nm', index:'sh_nm', width:200, editable: true, align:'left', hidden:true },
      {label:'Тип',name:'typ_tar', index:'typ_tar', width:40, editable: true, align:'center', hidden:true},
      {label:'Идент.',name:'ident', index:'ident', width:40, editable: true, align:'center', hidden:true},
      {label:'Тип для пільги',name:'id_lgt_group', index:'id_lgt_group', width:120, editable: true, align:'left', hidden:true},
      {name:'work_period', index:'work_period', width:80, editable: true, align:'left',edittype:'text', hidden:true},
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true}
    ],
    pager: '#dov_tarif_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],

    pgbuttons: false,
    pgtext: null, 

    sortname: 'ident',
    sortorder: 'asc',
    viewrecords: false,
    gridview: true,
    caption: 'Тарифи',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelTarif = rowid; 
    },
    
    ondblClickRow: function(id){ 
        tarif_target_id.val(jQuery(this).jqGrid('getCell',lastSelTarif,'id') ); 
        tarif_target_name.val(jQuery(this).jqGrid('getCell',lastSelTarif,'nm') );
        tarif_target_name.focus();
        jQuery('#grid_seltarif').toggle( );
    } ,  
    
    gridComplete: function() {
          var rowData = $("#dov_tarif_table").getDataIDs();
	  // ЦЕК: Изменение некоторых пунктов меню на серый цвет  	
          for (var ai = 0; ai < rowData.length; ai++)
          {   var $t_metka = rowData[ai];
              if($t_metka==13 || $t_metka==11)
              $("#dov_tarif_table").jqGrid('setRowData', rowData[ai], false, {'color': '#CCCCCC'});
          }
      
      },

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_tarif_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_dov_tarif_table").append("<button class ='btnOk' id='bt_tarsel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_dov_tarif_table").append("<button class ='btnClose' id='bt_tarclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

 
jQuery("#dov_tarif_table").jqGrid('navButtonAdd','#dov_tarif_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_tarif_table")[0];
        sgrid.clearToolbar();  } 
});

jQuery("#dov_tarif_table").jqGrid('filterToolbar','');

jQuery('#bt_tarclose0').click( function() { jQuery('#grid_seltarif').toggle( ); }); 

jQuery('#bt_tarsel0').click( function() { 
    tarif_target_id.val(jQuery('#dov_tarif_table').jqGrid('getCell',lastSelTarif,'id') ); 
    tarif_target_name.val(jQuery('#dov_tarif_table').jqGrid('getCell',lastSelTarif,'nm') ); 
    tarif_target_name.focus();

    jQuery('#grid_seltarif').toggle( );
}); 

jQuery('#grid_seltarif').draggable({ handle: ".ui-jqgrid-titlebar" });

jQuery("#dov_tarif_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
        
    tarif_target_id.val(jQuery('#dov_tarif_table').jqGrid('getCell',lastSelTarif,'id') ); 
    tarif_target_name.val(jQuery('#dov_tarif_table').jqGrid('getCell',lastSelTarif,'nm') ); 
    tarif_target_name.focus();

    jQuery('#grid_seltarif').toggle( );  
} } );

}; 

