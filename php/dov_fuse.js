jQuery(function(){ 
  jQuery('#dov_fuse_table').jqGrid({
    url:'dov_fuse_data.php',
    editurl: 'dov_fuse_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:500,
    width:800,
    colNames:['Код','Назва','ГОСТ', 'Напруга ном.','Ток ном.','Потужн.'],
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
      {name:'power_nom', index:'power_nom', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},
                        formatoptions: { defaultValue: ' '}}
    ],
    pager: '#dov_fuse_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500], 
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Запобіжники',
    hidegrid: false,
    
    ondblClickRow: function(id){ 
      jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_fuse_tablePager',
        {edit:true,add:true,del:true},
        {width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {width:350,reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {reloadAfterSubmit:false,afterSubmit:processAfterEdit}, 
        {} 
        ); 

 
jQuery("#dov_fuse_table").jqGrid('navButtonAdd','#dov_fuse_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_fuse_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#dov_fuse_table").jqGrid('filterToolbar','');

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
            jQuery("#dov_fuse_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#dov_fuse_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
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
