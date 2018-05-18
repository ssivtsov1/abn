var edit_row_id=0;

jQuery(function(){ 

  if(r_edit==3)
      r_edit_bool = true;
  else
      r_edit_bool = false;    


  var TableEditOptions = {width:300, reloadAfterSubmit:true, closeAfterAdd:true,
        closeAfterEdit:true, 
        afterSubmit:processAfterEdit,
        onInitializeForm: function() {

            $('#mmgg').datepicker({
                showOn: "button", 
                buttonImage: "images/calendar.gif",
                buttonImageOnly: true, 
                dateFormat:'dd.mm.yy'
            });
            $('#mmgg').mask("99.99.9999");    
        },
        onClose: function() {
            $('.hasDatepicker').datepicker("hide");
        },
        beforeSubmit: function(postdata, formid){
            
            postdata.id_paccnt = id_paccnt;
                
         return[true,''];
        } 
    
    };

  jQuery('#plandem_table').jqGrid({
    url:'abon_en_plandem_data.php',
    editurl: 'abon_en_plandem_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    //width:800,
    autowidth: false,
    shrinkToFit : false,
    scroll: 0,
    colNames:[],
    colModel :[ 

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    {label:'Місяць',name:'mmgg', index:'mmgg', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
    {label:'Зона',name:'id_zone', index:'id_zone', width:80, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text'},                       

    {label:'Споживання',name:'demand', index:'demand', width:80, editable: true, align:'right',
                            edittype:'text',formatter:'integer',editrules:{number:true}},

    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:false},
    {label:'dt',name:'dt_input', index:'dt_input', width:100, editable: false, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}

    ],
    pager: '#plandem_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'mmgg',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Планове споживання '+paccnt_info,
    //hiddengrid: false,
    jsonReader : {repeatitems: false},
    hidegrid: false,
    postData:{'p_id': id_paccnt, 'p_mmgg':mmgg},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
   },

   onSelectRow: function(rowid) { 
        edit_row_id = rowid; 
    },

    
    ondblClickRow: function(id){ 
    var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
        edit_row_id = id;
        if (r_edit_bool) jQuery(this).editGridRow(id,TableEditOptions);          
        
      } else { alert("Please select Row") }       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#plandem_tablePager',
       {edit:r_edit_bool,add:r_edit_bool,del:r_edit_bool},
        TableEditOptions, 
        TableEditOptions, 
        {reloadAfterSubmit:false,afterSubmit:processAfterEdit}, 
        {} 
        ); 

  jQuery("#plandem_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
      jQuery(this).editGridRow(id,TableEditOptions);}} );


  jQuery("#plandem_table").jqGrid('filterToolbar','');


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
	,	center__paneSelector:	"#pmain_content"
	//,	center__onresize:		'innerLayout.resizeAll'
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, $pane, state, options) 
        {
            
            $("#plandem_table").jqGrid('setGridWidth',$pane.innerWidth()-10);
            $("#plandem_table").jqGrid('setGridHeight',$pane.innerHeight()-130);

        }
        
	});

         
    outerLayout.resizeAll();
    outerLayout.close('south');     
    
   // innerLayout.hide('north');        
        
    jQuery(".btn").button();
    jQuery(".btnSel").button({ icons: {primary:'ui-icon-folder-open'} });
    
   $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
   jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

   jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
   jQuery(".dtpicker").mask("99.99.9999");    
  $("#fmmgg").datepicker( "setDate" , mmgg );        
        
   $("#message_zone").dialog({autoOpen: false});        
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open'); });
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   
$("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       
       $('#plandem_table').jqGrid('setGridParam',{postData:{'p_id': id_paccnt,'p_mmgg': mmgg}}).trigger('reloadGrid');
       
});
   
   
 function processAfterEdit(response, postdata) {
            //alert(response.responseText);
            if (response.responseText=='') {return [true,''];}
            else
            {
             errorInfo = jQuery.parseJSON(response.responseText);
             
             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]}; 

             if (errorInfo.errcode==1) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
               return [true,errorInfo.errstr]};              

             if (errorInfo.errcode==-1) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
               //jQuery('#paccnt_lgt_table').jqGrid('setGridParam',{'postData':{'p_id':id_paccnt}}).trigger('reloadGrid');                       
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};              
            }
        }
    
});

