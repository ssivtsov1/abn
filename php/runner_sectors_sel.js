var lastSelSector;
var sector_target_id =null;
var sector_target_name=null;
var sector_target_runner_id=null;
var sector_target_runner_name=null;
//var sector_target_carry;
var isSectorGridCreated = false;

var createSectorGrid = function(){ 
    
  if (isSectorGridCreated) return;
  isSectorGridCreated =true;
    
  jQuery('#sectors_sel_table').jqGrid({
    url: 'runner_sectors_data.php',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    width:400,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {label:'Код',name:'code', index:'code', width:50, editable: true, align:'left',edittype:"text"},
      {label:'Дільниця',name:'name', index:'name', width:200, editable: true, align:'left',edittype:"text"},
      {label:'id_runner',name:'id_runner', index:'id_kategor', width:40, editable: true, align:'right', hidden:true},                             
      {label:"Кур'єр / контролер",name:'runner', index:'runner', width:170, editable: true, align:'left'},                       
      {label:'Примітка',name:'notes', index:'notes', width:200, editable: true, align:'left',edittype:"text",hidden:true}
    ],
    pager: '#sectors_sel_tablePager',
    rowNum:100,
    rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Дільниці',
    hidegrid: false,
    toolbar: [true,'top'],
    
    onSelectRow: function(rowid) { 
        lastSelSector = rowid; 
        //jQuery('#pdebug_ci').html(  jQuery(this).jqGrid('getCell',rowid,'name'))   
    },

    ondblClickRow: function(id){ 
        sector_target_id.val(jQuery(this).jqGrid('getCell',lastSelSector,'id') ); 
        sector_target_name.val(jQuery(this).jqGrid('getCell',lastSelSector,'name') );
        if (sector_target_runner_id!=null)
        {
           sector_target_runner_id.val(jQuery(this).jqGrid('getCell',lastSelSector,'id_runner') );
        }

        if (sector_target_runner_id!=null)
        {
           sector_target_runner_name.val(jQuery(this).jqGrid('getCell',lastSelSector,'runner') );
        }

        sector_target_name.change();
        sector_target_name.focus();
        jQuery('#grid_selsector').toggle( );
    } ,  
    
     
    loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#sectors_sel_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
        
    jQuery('#sectors_sel_table').jqGrid('filterToolbar','');        

    $("#t_sectors_sel_table").append("<button class ='btnOk' id='bt_cisel0' style='height:20px;font-size:-3' > Выбор </button> ");
    $("#t_sectors_sel_table").append("<button class ='btnClose' id='bt_ciclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
    jQuery(".btnOk").button({icons: {primary:'ui-icon-check'}});
    jQuery(".btnClose").button({icons: {primary:'ui-icon-close'}});

jQuery('#bt_ciclose0').click( function() {jQuery('#grid_selsector').toggle( );}); 

jQuery('#bt_cisel0').click( function() { 
    sector_target_id.val(jQuery('#sectors_sel_table').jqGrid('getCell',lastSelSector,'id') ); 
    sector_target_name.val(jQuery('#sectors_sel_table').jqGrid('getCell',lastSelSector,'name') ); 
    if (sector_target_runner_id!=null)
    {
        sector_target_runner_id.val(jQuery('#sectors_sel_table').jqGrid('getCell',lastSelSector,'id_runner') );
    }

    if (sector_target_runner_id!=null)
    {
        sector_target_runner_name.val(jQuery('#sectors_sel_table').jqGrid('getCell',lastSelSector,'runner') );
    }
    sector_target_name.change();
    sector_target_name.focus();
    jQuery('#grid_selsector').toggle( );
}); 


jQuery("#sectors_sel_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
      
    sector_target_id.val(jQuery('#sectors_sel_table').jqGrid('getCell',lastSelSector,'id') ); 
    sector_target_name.val(jQuery('#sectors_sel_table').jqGrid('getCell',lastSelSector,'name') ); 
    if (sector_target_runner_id!=null)
    {
        sector_target_runner_id.val(jQuery('#sectors_sel_table').jqGrid('getCell',lastSelSector,'id_runner') );
    }

    if (sector_target_runner_id!=null)
    {
        sector_target_runner_name.val(jQuery('#sectors_sel_table').jqGrid('getCell',lastSelSector,'runner') );
    }
    sector_target_name.change();
    sector_target_name.focus();
    jQuery('#grid_selsector').toggle( );

  } } );


jQuery('#grid_selsector').draggable({ handle: ".ui-jqgrid-titlebar" });
}; 
 
