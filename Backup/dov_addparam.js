var selmode=0;

jQuery(function(){ 
    
  if(r_edit==3)
      r_edit_bool = true;
  else
      r_edit_bool = false;    
    
  jQuery('#dov_addparam_table').jqGrid({
    url:'dov_addparam_data.php',
    editurl: 'dov_addparam_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:500,
    width:800,
    shrinkToFit : false,
    colNames:['Код','Назва'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true }, 
      {name:'name', index:'name', width:400, editable: true, align:'left',edittype:'text',editoptions:{size:60}}           
    ],
    pager: '#dov_addparam_tablePager',
    autowidth: true,
    rowNum:300,
    rowList:[20,50,100,300,500],
    sortname: 'id',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Додаткові ознаки',
    hidegrid: false,
    
    ondblClickRow: function(id){ 
      if(selmode==1)
      {
           window.opener.SelectPostExternal(id,jQuery(this).jqGrid('getCell',id,'name') );
           window.opener.focus();
           self.close();            
      }

      if(selmode==0)
      {
        if (r_edit_bool) jQuery(this).editGridRow(id,{width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
      }

     } ,  
      
      
//    loadError: function (jqXHR, textStatus, errorThrown) {
//        alert('HTTP status code: ' + jqXHR.status + '' +
//              'textStatus: ' + textStatus + '' +
//              'errorThrown: ' + errorThrown);
//        alert('HTTP message body (jqXHR.responseText): ' + '' + jqXHR.responseText);
//    }

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}
  
//  jsonReader : { repeatitems: false }

  }).navGrid('#dov_addparam_tablePager',
        {edit:r_edit_bool,add:r_edit_bool,del:r_edit_bool},
        {width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {width:500,reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {reloadAfterSubmit:false,afterSubmit:processAfterEdit}, 
        {} 
        ); 

jQuery('#dov_addparam_table').jqGrid('navButtonAdd','#dov_addparam_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_addparam_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery('#dov_addparam_table').jqGrid('filterToolbar','');
jQuery("#dov_addparam_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
      jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } } );


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
            jQuery("#dov_addparam_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#dov_addparam_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }

	});
        
        outerLayout.resizeAll();
        outerLayout.close('south');             

}); 
 

 //jQuery('#lshow_grid').click( function() { jQuery('#dov_addparam_table').jqGrid('setGridParam',{caption: 'Счетчики 111'}).trigger('reloadGrid')});

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
