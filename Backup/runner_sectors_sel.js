var lastSelSector;
var coldays;
var s_name;
var temp_total=0;
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



var createSectorGrid_ind_packs = function(){ 
    
  if (isSectorGridCreated) return;
  isSectorGridCreated =true;
    
  jQuery('#sectors_sel_table').jqGrid({
    url: 'runner_sectors_plan_data.php',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    width:400,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {label:'Код',name:'code', index:'code', width:50, editable: true, align:'left',edittype:"text"},
      {label:'Дільниця1',name:'name', index:'name', width:200, editable: true, align:'left',edittype:"text"},
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




var isSectorGridCreated = false;

var createPlanViewGrid = function(){ 
    
  if (isSectorGridCreated) return;
  isSectorGridCreated =true;
  
  var dt_1 = $("#fdt_b").val();
  var cntr = $("#fperson").attr("value");
  //alert(cntr);
  var url_par = 'plan_view_data.php?date='+dt_1+'&cntr='+cntr;
  
  jQuery('#plan_view_table').jqGrid({
    url: url_par,
    datatype: 'json',
    mtype: 'POST',
    height:400,
    width:400,
    colNames:[],
    colModel :[ 
        {label:'Рік',name:'year',index: 'year', width:60, editable: true, align:'center'}, 
        {label:'Місяць',name:'month', index: 'month', width:60, editable: true, align:'center'},            
        {label:'Дільниця',name:'sector', key:true,  width:180, editable: true, align:'center'},
        {label:'Кількість днів',name:'days',  width:100, editable: true, align:'center'},
        {label:'Кількість усього',name:'total',  width:100, editable: true, align:'center',hidden:true},
    ],
    pager: '#plan_view_tablePager',
    rowNum:100,
    rowList:[20,50,100,300,500],
    sortname: 'sector',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Дільниці (перегляд план)',
    hidegrid: false,
    toolbar: [true,'top'],
    
    onSelectRow: function(rowid) { 
        lastSelSector = rowid; 
        //jQuery('#pdebug_ci').html(  jQuery(this).jqGrid('getCell',rowid,'name'))   
    },

   loadComplete:function(data){
                    //id list value
                    var ids = $(this).jqGrid('getDataIDs');
                    //get first id
                    var cl = ids[0];
                    var rowData = $(this).getRowData(cl); 
                    console.log(rowData);
                    temp_total= rowData['total'];
                    //alert(temp_total);
                    $('.txtcnt_plan').remove();
                    var stl = '';
                    if(temp_total>21 && temp_total<25)
                    $("#t_plan_view_table").append(
                            "<span class ='txtcnt_plan' id='txtcnt_plan' style='height:20px;color:blue;'"+stl+" > Днів:"+temp_total+" </span> ");
                    if(temp_total>=25)
                    $("#t_plan_view_table").append(
                            "<span class ='txtcnt_plan' id='txtcnt_plan' style='height:20px;color:red;'"+stl+" > Днів:"+temp_total+" </span> ");
                    if(temp_total<=21)
                    $("#t_plan_view_table").append(
                            "<span class ='txtcnt_plan' id='txtcnt_plan' style='height:20px;'"+stl+" > Днів:"+temp_total+" </span> ");
            },    
     
    loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#plan_view_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
        
    jQuery('#plan_view_table').jqGrid('filterToolbar','');        

   
    $("#t_plan_view_table").append("<button class ='btnClose' id='bt_ciclose1' style='height:20px;font-size:-3' > Закр. </button> ");
    
    
    jQuery(".btnClose").button({icons: {primary:'ui-icon-close'}});

jQuery('#bt_ciclose1').click( function() {jQuery('#grid_planview').toggle( );location.reload();}); 

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
    jQuery('#grid_planview').toggle( );
}); 


jQuery("#plan_view_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
      
    sector_target_id.val(jQuery('#plan_view_table').jqGrid('getCell',lastSelSector,'id_user') ); 
    sector_target_name.val(jQuery('#plan_view_table').jqGrid('getCell',lastSelSector,'sector') ); 
    

    if (sector_target_runner_id!=null)
    {
        sector_target_runner_name.val(jQuery('#plan_view_table').jqGrid('getCell',lastSelSector,'sector') );
    }
    
    jQuery('#grid_planview').toggle( );

  } } );

jQuery('#grid_planview').draggable({ handle: ".ui-jqgrid-titlebar" });
}; 
 
//Вызов формы планирования для контролеров
var isPlanGridCreated = false;

var createPlanGrid = function(){ 

  //alert(url_par);
  var dt_1 = $("#fdt_b").val();
  var url_par = 'controlers_counters_data.php?date='+dt_1;
  //alert(url_par);
  
  if (isPlanGridCreated) return;
  isPlanGridCreated =true;
    
  jQuery('#controlers_counters_table').jqGrid({
    url: url_par,
    datatype: 'json',
    mtype: 'POST',
    height:500,
    width:1260,
    colNames:[],
   colModel :[ 
      {label:'Дільниця',name:'sector', index:'sector', width:160, editable: true, align:'left'},
      {label:'Контролер',name:'cntr', index:'cntr', width:240, editable: true, align:'left'},
      {label:'Кільк.(прим.)',name:'place1', index:'place1', width:70, editable: true, align:'center'},
      {label:'Кільк.(С.К.)',name:'place2', index:'place2', width:70, editable: true, align:'center'},
      {label:'Кільк.(винос. конт.)',name:'place3', index:'place3', width:130, editable: true, align:'center'},
      {label:'Кільк.(ВБШ)', name:'place4',index:'place4', width:90, editable: true, align:'center'},
      {label:'Кільк.(У кв.)', name:'place5', index:'place5', width:88, editable: true, align:'center'},
      {label:'Кільк.(буд.)', name:'place6', index:'place6', width:70, editable: true, align:'center'},
      {label:'Днів(прим.)',name:'cost1', index:'cost1', width:70, editable: true, align:'center'},
      {label:'Днів(С.К.)',name:'cost2', index:'cost2', width:70, editable: true, align:'center'},
      {label:'Днів(винос. конт.)',name:'cost3', index:'cost3', width:135, editable: true, align:'center'},
      {label:'Днів(ВБШ)', name:'cost4',index:'cost4', width:80, editable: true, align:'center'},
      {label:'Днів(У кв.)', name:'cost5', index:'cost5', width:80, editable: true, align:'center'},
      {label:'Днів(буд.)', name:'cost6', index:'cost6', width:75, editable: true, align:'center'},
      {label:'Днів усього', name:'cost_all', index:'cost6', width:90, editable: true, align:'center'}
      
    ],
    pager: '#controlers_counters_tablePager',
    rowNum:300,
    rowList:[20,50,100,300,500],

//    pgbuttons: false,
//    pgtext: null, 

    sortname: 'sector',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Звіт по кількості лічильників по місцям установки',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    
    onSelectRow: function(rowid) { 
        lastSelSector = rowid; 
        
        var rowid1 = $(this).jqGrid('getGridParam', 'selrow');
        var row = $(this).jqGrid("getRowData", rowid1);
        coldays = row.cost_all;
        s_name = row.sector;
        var dt_1 = $("#fdt_b").val();
        //alert(dt_1);
        //var seldays = 
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
        jQuery('#controlers_counters_table').toggle( );
    } ,  
    
     
    loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#controlers_counters_table_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
        
    jQuery('#controlers_counters_table').jqGrid('filterToolbar','');        

    //$("#t_sectors_sel_table").append("<button class ='btnOk' id='bt_cisel0' style='height:20px;font-size:-3' > Выбор </button> ");
    $("#t_controlers_counters_table").append("<button class ='btnClose' id='bt_ciclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    $("#t_controlers_counters_table").append("<button class ='btnAdd' id='bt_add0' style='height:20px;font-size:-3' > + </button> ");    
    //$("#t_controlers_counters_table").append("<button class ='btnView' id='bt_view0' style='height:20px;font-size:-3' > Перегляд </button> ");    
    //$("#t_controlers_counters_table").append("<select class ='cmbCntr' id='cmbCntr' style='height:20px;font-size:-3' ><option></option> </select> ");    
    //jQuery(".btnOk").button({icons: {primary:'ui-icon-check'}});
    jQuery(".btnClose").button({icons: {primary:'ui-icon-close'}});

jQuery('#bt_ciclose0').click( function() {
    localStorage.setItem("add", 0);
    jQuery('#pcontrolers_counters_table').toggle( );
    location.reload();
}); 

$("#cmbCntr").click(function() {
    $("#cmbCntr").change();
});

jQuery('#cmbCntr').on('change', function(){
 var i,c;   
 c=$(this).val();
 $.ajax({
  type: 'post',
  url: 'getcntr.php',
  data: {
    cntrId : $(this).val()  
  },
  success: function(cntr){
    var options = '';
    console.log(cntr);
    for(i = 0; i<cntr.length; i++){
       options += '<option val="' + cntr[i].id + '">' + cntr[i].represent_name + '</option>';
    }
    jQuery('#cmbCntr').html(options).show();
    $('#cmbCntr').val(c);
  }
 });
});

//Вызов окна планирования(добаление подстанции) для контролеров
    jQuery("#bt_add0").click( function() {
        createPlanStoreGrid(); 
        var cntr = $("#fperson").attr("value");

        jQuery("#grid_plancache").show( );
        //if(localStorage.getItem("add")==1)
       
//        if(localStorage.getItem("add")==0){
//        var request = $.ajax({
//               url: "plan_cache_edit.php",
//               type: "POST",
//               data: {
//                    id :    lastSelSector,
//                    days :  coldays,
//                    sector : s_name,
//                    date :   dt_1,
//                    cntr :   cntr,
//                    oper:   'add_'
//               },
//               dataType: "json"
//       });
//       }
       //if(localStorage.getItem("add")==1){
        var request = $.ajax({
               url: "plan_cache_edit.php",
               type: "POST",
               data: {
                    id :    lastSelSector,
                    days :  coldays,
                    sector : s_name,
                    date :   dt_1,
                    cntr :   cntr,
                    oper:   'add'
               },
               dataType: "json"
       });
      // }
   
       // localStorage.setItem("add", 1);
        
       request.done(function(data ) {  
        
                    if (data.errcode!==undefined)
                    {
                        $('#message_zone').append(data.errstr);  
                        $('#message_zone').append("<br>");                 
                        //jQuery("#message_zone").dialog('open');
            
                        if(data.errcode<=0) 
                        {
                           jQuery('#plan_cache_table').trigger('reloadGrid');  
                           jQuery('#controlers_counters_table').trigger('reloadGrid');
                
                        }
                        else
                        {
                            jQuery("#message_zone").dialog('open');                                    
                        }
                    }
                });

        request.fail(function(data ) {
                    alert("error");
        
                });

    });
    
    //Вызов окна просмотра планирования для контролеров
    jQuery("#bt_view0").click( function() {
        createPlanViewGrid(); 
        jQuery("#grid_planview").show( );
    });


//jQuery('#bt_cisel0').click( function() { 
//    sector_target_id.val(jQuery('#controlers_counters_table').jqGrid('getCell',lastSelSector,'id') ); 
//    sector_target_name.val(jQuery('#controlers_counters_table').jqGrid('getCell',lastSelSector,'name') ); 
//    if (sector_target_runner_id!=null)
//    {
//        sector_target_runner_id.val(jQuery('#controlers_counters_table').jqGrid('getCell',lastSelSector,'id_runner') );
//    }
//
//    if (sector_target_runner_id!=null)
//    {
//        sector_target_runner_name.val(jQuery('#controlers_counters_table').jqGrid('getCell',lastSelSector,'runner') );
//    }
//    sector_target_name.change();
//    sector_target_name.focus();
//    jQuery('#controlers_counters_table').toggle( );
//}); 


//jQuery("#sectors_sel_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
//      
//    sector_target_id.val(jQuery('#sectors_sel_table').jqGrid('getCell',lastSelSector,'id') ); 
//    sector_target_name.val(jQuery('#sectors_sel_table').jqGrid('getCell',lastSelSector,'name') ); 
//    if (sector_target_runner_id!=null)
//    {
//        sector_target_runner_id.val(jQuery('#sectors_sel_table').jqGrid('getCell',lastSelSector,'id_runner') );
//    }
//
//    if (sector_target_runner_id!=null)
//    {
//        sector_target_runner_name.val(jQuery('#sectors_sel_table').jqGrid('getCell',lastSelSector,'runner') );
//    }
//    sector_target_name.change();
//    sector_target_name.focus();
//    jQuery('#controlers_counters_table').toggle( );
//
//  } } );


jQuery('#pcontrolers_counters_table').draggable({ handle: ".ui-jqgrid-titlebar" });
}; 





var lastSelCacheBill;
//var tarif_target_id;
//var tarif_target_name;

var isBillCacheCreated = false;
var createPlanStoreGrid = function(){ 
  
  var dt_1 = $("#fdt_b").val();
  var cntr = $("#fperson").attr("value");
  var url_par = 'plan_cache_data.php?date='+dt_1+'&cntr='+cntr;
  
  if (isBillCacheCreated) return;
  isBillCacheCreated =true;

  jQuery('#plan_cache_table').jqGrid({
    url: url_par,
    //editurl: 'dov_fider_edit.php', 
    datatype: 'json',
    mtype: 'POST',
    height:400,
    width:500,
    colNames:[],
    colModel :[ 
    {label:'Рік',name:'year',index: 'year', width:60, editable: true, align:'center'}, 
    {label:'Місяць',name:'month', index: 'month', width:60, editable: true, align:'center'},            
    {label:'Дільниця',name:'sector', key:true,  width:180, editable: true, align:'center'},
    {label:'Кількість днів',name:'days',  width:80, editable: true, align:'center'},
    {label:'Оператор',name:'cntr', width:50, editable: true, align:'center', hidden: true },
    {label:'Усього:',name:'total', width:50, editable: true, align:'center', hidden: true },
    ],
    pager: '#plan_cache_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],

//    pgbuttons: false,
//    pgtext: null, 

    sortname: 'tt',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Вибрані дільниці',
    hidegrid: false,
    toolbar: [true,'top'],
   // jsonReader : {repeatitems: false},
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelCacheBill = rowid; 
    },
    /*
    ondblClickRow: function(id){ 
        tarif_target_id.val(jQuery(this).jqGrid('getCell',lastSelCacheBill,'id') ); 
        tarif_target_name.val(jQuery(this).jqGrid('getCell',lastSelCacheBill,'nm') );
        tarif_target_name.focus();
        jQuery('#grid_billcache').toggle( );
    } ,  
*/

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');},
  
  loadComplete:function(data){
                    //id list value
                    var ids = $(this).jqGrid('getDataIDs');
                    //get first id
                    var cl = ids[0];
                    var rowData = $(this).getRowData(cl); 
                    temp_total= rowData['total'];
                    //alert(temp_total);
                    $('.txtcnt_plan').remove();
                    var stl = '';
                    if(temp_total>21 && temp_total<25)
                    $("#t_plan_cache_table").append(
                            "<span class ='txtcnt_plan' id='txtcnt_plan' style='height:20px;color:blue;'"+stl+" > Днів:"+temp_total+" </span> ");
                    if(temp_total>=25)
                    $("#t_plan_cache_table").append(
                            "<span class ='txtcnt_plan' id='txtcnt_plan' style='height:20px;color:red;'"+stl+" > Днів:"+temp_total+" </span> ");
                    if(temp_total<=21)
                    $("#t_plan_cache_table").append(
                            "<span class ='txtcnt_plan' id='txtcnt_plan' style='height:20px;'"+stl+" > Днів:"+temp_total+" </span> ");
            }

  }).navGrid('#plan_cache_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

//$("#t_bill_cache_table").append("<button class ='btnOk' id='bt_cacheprint' style='height:20px;font-size:-3' > Друк </button> ");
$("#t_plan_cache_table").append("<button class ='btnClose' id='bt_billclose' style='height:20px;font-size:-3' > Закр. </button> ");
 

    
//jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

jQuery("#plan_cache_table").jqGrid('filterToolbar','');

jQuery('#bt_billclose').click( function() { jQuery('#grid_plancache').toggle( ); }); 

//jQuery('#bt_cacheprint').click( function() { 
//    
//       var rows = $('#bill_cache_table').getDataIDs();
//       var json_str = JSON.stringify(rows);
//
//       $("#fprint_params").find("#pbill_list").attr('value',json_str ); 
//       
//       $("#fprint_params").attr('target',"_blank" );           
//       document.print_params.submit();    
//
//}); 

jQuery('#grid_plancache').draggable({ handle: ".ui-jqgrid-titlebar" });


jQuery("#plan_cache_table").jqGrid('navButtonAdd','#plan_cache_tablePager',{caption:"-",
	onClickButton:function(){ 
                  var cntr = $("#fperson").attr("value");
                  var request = $.ajax({
                    url: "plan_cache_edit.php",
                    type: "POST",
                    data: {
                        id : lastSelCacheBill,
                        date :   dt_1,
                        cntr :   cntr,
                        oper: 'del'
                    },
                    dataType: "json"
                });

                request.done(function(data ) {  
        
                    if (data.errcode!==undefined)
                    {
                        $('#message_zone').append(data.errstr);  
                        $('#message_zone').append("<br>");                 
                        //jQuery("#message_zone").dialog('open');
            
                        if(data.errcode<=0) 
                        {
                           jQuery('#plan_cache_table').trigger('reloadGrid'); 
                           jQuery('#controlers_counters_table').trigger('reloadGrid');
                
                        }
                        else
                        {
                            jQuery("#message_zone").dialog('open');                                    
                        }
                    }
                });

                request.fail(function(data ) {
                    alert("error");
        
                });

       ;} 
});

jQuery("#plan_cache_table").jqGrid('navButtonAdd','#plan_cache_tablePager',{caption:"--",
	onClickButton:function(){ 
                  var cntr = $("#fperson").attr("value");
                  var request = $.ajax({
                    url: "plan_cache_edit.php",
                    type: "POST",
                    data: {
                        id : lastSelCacheBill,
                        date :   dt_1,
                        cntr :   cntr,
                        oper: 'delall'
                    },
                    dataType: "json"
                });

                request.done(function(data ) {  
        
                    if (data.errcode!==undefined)
                    {
                        $('#message_zone').append(data.errstr);  
                        $('#message_zone').append("<br>");                 
                        //jQuery("#message_zone").dialog('open');
            
                        if(data.errcode<=0) 
                        {
                           jQuery('#plan_cache_table').trigger('reloadGrid');                      
                
                        }
                        else
                        {
                            jQuery("#message_zone").dialog('open');                                    
                        }
                    }
                });

                request.fail(function(data ) {
                    alert("error");
        
                });

       ;} 
});


jQuery("#plan_cache_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
        
   // tarif_target_id.val(jQuery('#bill_cache_table').jqGrid('getCell',lastSelCacheBill,'id') ); 
   // tarif_target_name.val(jQuery('#bill_cache_table').jqGrid('getCell',lastSelCacheBill,'nm') ); 
   // tarif_target_name.focus();

   // jQuery('#grid_billcache').toggle( );  
} } );

}; 

 
 