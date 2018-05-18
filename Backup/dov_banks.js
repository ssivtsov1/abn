jQuery(function(){ 
    
  if(r_edit==3)
      r_edit_bool = true;
  else
      r_edit_bool = false;    
    
    
  jQuery('#dov_bank_table').jqGrid({
    url:     'dov_banks_data.php',
    editurl: 'dov_banks_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    width:800,
    colNames:['Код МФО','Назва банку','Коротка назва'],
    colModel :[ 
      {name:'mfo', index:'mfo', width:100, editable: true, align:'center', key:true },     
      {name:'name', index:'name', width:320, editable: true, align:'left',edittype:'text',
                            editoptions:{size:50}},           
      {name:'short_name', index:'short_name', width:100, hidden:true, editable: true, align:'left',edittype:'text',
                            editoptions:{size:50}, editrules:{edithidden:true}}

    ],
    pager: '#dov_bank_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Банки та установи',
    hidegrid: false,
    
    ondblClickRow: function(id){ 
        
        if(selmode==1)
        {
           window.opener.SelectBankExternal(id,jQuery(this).jqGrid('getCell',id,'name'));
           window.opener.focus();
           self.close();            
        }
        else
        {    
          if (r_edit_bool) jQuery(this).editGridRow(id,{width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
        }
  
    } ,  
      
    loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_bank_tablePager',
        {edit:r_edit_bool,add:r_edit_bool,del:r_edit_bool},
        {width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {width:500,reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {reloadAfterSubmit:false,afterSubmit:processAfterEdit}, 
        {} 
        ); 

jQuery("#dov_bank_table").jqGrid('navButtonAdd','#dov_bank_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_bank_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#dov_bank_table").jqGrid('filterToolbar','');

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
            jQuery("#dov_bank_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#dov_bank_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }

	});
        
        if(selmode!=0)
        {
            outerLayout.hide('north');        
        };    
        
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
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};              
            }
        }
