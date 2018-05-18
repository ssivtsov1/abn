jQuery(function(){ 
  jQuery('#dov_compensator_table').jqGrid({
    url:'dov_compensator_data.php',
    editurl: 'dov_compensator_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    width:800,
    colNames:['Код','Назва','ГОСТ','Напруга перв.','Ток перв.','Напруга макс.','Ток макс.','Напруга втор.','Ток втор.', 
     'Потужність, кВА','Ток ХХ,%','Напруга КЗ,%','Потери ХХ,кВт','Потери КЗ,кВт','*'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true,hidden:true },     
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
    viewrecords: true,
    gridview: true,
    caption: 'Трансформатори',
    hidegrid: false,
    
    ondblClickRow: function(id){ 
      jQuery(this).editGridRow(id,{width:620,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
      
    loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_compensator_tablePager',
        {edit:true,add:true,del:true},
        {width:620,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {width:620,reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {reloadAfterSubmit:false,afterSubmit:processAfterEdit}, 
        {} 
        ); 

jQuery("#dov_compensator_table").jqGrid('navButtonAdd','#dov_compensator_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_compensator_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#dov_compensator_table").jqGrid('filterToolbar','');


$("#message_zone").dialog({ autoOpen: false });

$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open'); });
$("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {jQuery("#message_zone").html('');});

 outerLayout = $("body").layout({
		name:	"outer" 
	,	north__paneSelector:	"#pmain_header"
	,	north__closable:	false
	,	north__resizable:	false
        ,	north__size:		40
	,	north__spacing_open:	0
	,	south__paneSelector:	"#pmain_footer"
	,	south__closable:	true
	,	south__resizable:	false
        ,	south__size:		40
	,	south__spacing_open:	5
        ,	south__spacing_closed:	3
	,	center__paneSelector:	"#grid_dform"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#dov_compensator_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#dov_compensator_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }

	});
        
        outerLayout.resizeAll();
        outerLayout.close('south');             

}); 
 

 function processAfterEdit(response, postdata) {
            //alert(response.responseText);
            if (response.responseText=='') { return [true,'']; }
            else
            {
             errorInfo = jQuery.parseJSON(response.responseText);
             
             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]}; 
             
             if (errorInfo.errcode==1) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
               //jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};              
            }
        }
