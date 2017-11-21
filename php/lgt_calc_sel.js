var lastSelCalc;
var calc_target_id;
var calc_target_name;

var isCalcGridCreated = false;

var createLgtCalcGrid = function(id_lgt){ 
    
if (isCalcGridCreated) 
    {
     if ( id_lgt )
     {
            //jQuery('#dov_strings_table').setSelection(id_org, true); 
            jQuery('#lgt_calc_sel_table').jqGrid('setGridParam',{
                'postData':{ 'p_id':id_lgt } }).trigger('reloadGrid'); 
     }
      
    return;    
}

isCalcGridCreated =true;

  jQuery('#lgt_calc_sel_table').jqGrid({
    url:'lgt_calc_sel_data.php',
    //editurl: '',
    datatype: 'json',
    mtype: 'POST',
    height:200,
    width:300,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {name:'id_group', index:'id_group', width:40, editable: false, align:'center', hidden:true},     
      {name:'id_calc', index:'id_calc', width:40, editable: false, align:'center', hidden:true},     
      {label:'Розрахунок',name:'name', index:'name', width:150, editable: true, align:'left',edittype:'text'}
    ],

    pager: '#lgt_calc_sel_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],
    
    pgbuttons: false,
    pgtext: null, 
    
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: false,
    gridview: true,
    caption: 'Методи розрахунку',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    postData:{'p_id': id_lgt},
    jsonReader : {repeatitems: false},
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelCalc = rowid; 
    },
    
    ondblClickRow: function(id){ 
        calc_target_id.val(jQuery(this).jqGrid('getCell',lastSelCalc,'id_calc') ); 
        calc_target_name.val(jQuery(this).jqGrid('getCell',lastSelCalc,'name') );
        calc_target_name.focus();
        jQuery('#grid_selcalc').toggle( );
    } ,  


  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');}

  }).navGrid('#lgt_calc_sel_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_lgt_calc_sel_table").append("<button class ='btnOk' id='bt_tpsel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_lgt_calc_sel_table").append("<button class ='btnClose' id='bt_tpclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({icons: {primary:'ui-icon-check'}});
jQuery(".btnClose").button({icons: {primary:'ui-icon-close'}});

 
jQuery("#lgt_calc_sel_table").jqGrid('navButtonAdd','#lgt_calc_sel_tablePager',{caption:"Все",
	onClickButton:function(){var sgrid = jQuery("#dov_tp_table")[0];
        sgrid.clearToolbar();  ;} 
});

//jQuery("#lgt_calc_sel_table").jqGrid('filterToolbar','');

jQuery('#bt_tpclose0').click( function() {jQuery('#grid_selcalc').toggle( );}); 

jQuery('#bt_tpsel0').click( function() { 
    calc_target_id.val(jQuery('#lgt_calc_sel_table').jqGrid('getCell',lastSelCalc,'id_calc') ); 
    calc_target_name.val(jQuery('#lgt_calc_sel_table').jqGrid('getCell',lastSelCalc,'name') ); 
    calc_target_name.focus();

    jQuery('#grid_selcalc').toggle( );
}); 

jQuery('#grid_selcalc').draggable({handle: ".ui-jqgrid-titlebar"});
}; 

