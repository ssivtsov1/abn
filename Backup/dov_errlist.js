var edit_row_id = 0;

jQuery(function(){ 
  jQuery('#dov_errlist_table').jqGrid({
    url:     'dov_errlist_data.php',
    editurl: 'dov_errlist_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    width:800,
    colNames:['Дата/час','Період','Повідомлення'],
    colModel :[ 
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},

      {name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', hidden:false},
      {name:'text', index:'text', width:400, editable: true, align:'left',edittype:'text'}
    ],
    pager: '#dov_errlist_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'dt',
    sortorder: 'desc',
    viewrecords: true,
    gridview: true,
    caption: 'Помилки та повідомлення',
    hidegrid: false,
    
   onSelectRow: function(rowid) { 
        edit_row_id = rowid; 
    },
    
    ondblClickRow: function(id){ 
        
    //   jQuery(this).editGridRow(id,{width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  

  
    } ,  
      
    loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_errlist_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
/*
jQuery("#dov_errlist_table").jqGrid('navButtonAdd','#dov_errlist_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_errlist_table")[0];
        sgrid.clearToolbar();  ;} 
});
*/
jQuery("#dov_errlist_table").jqGrid('filterToolbar','');

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
            jQuery("#dov_errlist_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#dov_errlist_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }

	});
        
       
        outerLayout.resizeAll();
        outerLayout.close('south');             


jQuery("#dov_errlist_table").jqGrid('navButtonAdd','#dov_errlist_tablePager',{caption:"Почистити",
    onClickButton:function(){ 

            jQuery("#dialog-confirm").find("#dialog-text").html('Почистити журнал помилок?');
    
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Очищення журналу',
                buttons: {
                    "Чистити": function() {
                                        

                        var request = $.ajax({
                            url: "dov_errlist_edit.php",
                            type: "POST",
                            data: {
                                oper : 'clear'
                            },
                            dataType: "json"
                        });

                        request.done(function(data ) {  
        
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");                 
                                //jQuery("#message_zone").dialog('open');
            
                                jQuery('#dov_errlist_table').trigger('reloadGrid');                      
                            }
                        });

                        request.fail(function(data ) {
                            alert("error");
        
                        });
                      
                        $( this ).dialog( "close" );
                    },
                    "Відмінити": function() {
                        $( this ).dialog( "close" );
                    }
                }
            });
    
            jQuery("#dialog-confirm").dialog('open');

    } 
});


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
