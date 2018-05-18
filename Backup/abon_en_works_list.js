var cur_works_id=0;

jQuery(function(){ 


  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
		buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");
    
 
  $("#fmmgg").datepicker( "setDate" , mmgg );



  jQuery('#paccnt_works_table').jqGrid({
    url:     'abon_en_works_list_data.php',
    editurl: 'abon_en_paccnt_works_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:100,
    width:800,
    autowidth: true,
    scroll: 0,
    colNames:[], 
    colModel :[  
      {label:'id',name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {label:'id_paccnt',name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center', hidden:true},           
            
      {label:'Дата роботи',name:'dt_work', index:'dt_work', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      
      {label:'Книга',name:'book', index:'book', width:50, editable: true, align:'left',edittype:'text'},            
      {label:'Рахунок',name:'code', index:'code', width:50, editable: true, align:'left',edittype:'text'},                
      {label:'Абонент',name:'abon', index:'abon', width:150, editable: true, align:'left',edittype:'text'},
      {label:'Адреса',name:'addr', index:'addr', width:150, editable: true, align:'left',edittype:'text'},                            
      
      {label:'Тип роботи',name:'idk_work', index:'idk_work', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lworktypes},stype:'select'},                       
      {label:'Виконавець',name:'position', index:'position', width:100, editable: true, align:'left',edittype:'text'},                 
      {label:'№ акта',name:'act_num', index:'act_num', width:50, editable: true, align:'left',edittype:'text'},                                  
      {label:'Прим.',name:'note', index:'note', width:100, editable: true, align:'left',edittype:'text'},           
      {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},
      {label:'dt',name:'dt_input', index:'dt_input', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}
    ],
    pager: '#paccnt_works_tablePager',
    rowNum:100,
    rowList:[20,50,100,300,500],
    sortname: 'dt_work', 
    sortorder: 'asc',
    viewrecords: true,
    //pgbuttons: false,
    //pgtext: null,
    gridview: true,
    caption: '',
    hidegrid: false,
    postData:{'p_mmgg': mmgg},
    jsonReader : {repeatitems: false},
 
    onSelectRow: function(id) { 
      cur_works_id = id;  
      jQuery('#paccnt_works_indic_table').jqGrid('setGridParam',{datatype:'json','postData':{'w_id':id}}).trigger('reloadGrid');        
    },
      
    ondblClickRow: function(id){ 

      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 

       $("#fpaccnt_params").find("#pid_work").attr('value',cur_works_id );   
       $("#fpaccnt_params").find("#pidk_work").attr('value',jQuery(this).jqGrid('getCell',id,'idk_work') );          
       $("#fpaccnt_params").find("#pmode").attr('value','1' );   
       $("#fpaccnt_params").attr("action","meter_work.php");
       $("#fpaccnt_params").attr('target',"_blank" );           
       document.paccnt_params.submit();
       
       //window.opener.SelectAbonExternal(id,jQuery(this).jqGrid('getCell',id,'last_name')+' '+ 
       // jQuery(this).jqGrid('getCell',id,'name')+' '+
       // jQuery(this).jqGrid('getCell',id,'patron_name'));
      } else {alert("Please select Row")}       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');},
  
  gridComplete:function(){

//    works_list_mode =0; //edit   
    if ($(this).getDataIDs().length > 0) 
    {      
//     $("#pNotliveParam").show();        
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);

    }
    else
    {
      jQuery('#paccnt_works_indic_table').jqGrid('setGridParam',{datatype:'local','postData':{'w_id':0}}).trigger('reloadGrid');                
    }
  }

  }).navGrid('#paccnt_works_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
  
  jQuery("#paccnt_works_table").jqGrid('filterToolbar','');
  
  //--------------------------------------------------------
  jQuery('#paccnt_works_indic_table').jqGrid({
    url:     'abon_en_paccnt_works_indic_data.php',
   // datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:80,
    width:800,
    autowidth: true,
    scroll: 0,
    colNames:[], 
    colModel :[  
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true,sortable:false},
    {name:'id_work', index:'id_work', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_accnt', index:'id_accnt', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_meter', index:'id_meter', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_type_meter', index:'id_type_meter', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_p_indic', index:'id_p_indic', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {label:'№ ліч.',name:'num_meter', index:'num_meter', width:80, editable: false, align:'left',edittype:'text',sortable:false},
    {label:'Тип ліч.',name:'type_meter', index:'type_meter', width:80, editable: false, align:'left',edittype:'text',sortable:false,hidden:false},
//    {label:'Розр. ліч.',name:'carry', index:'carry', width:40, editable: false, align:'left',edittype:'text',sortable:false},
    {label:'К.тр',name:'k_tr', index:'k_tr', width:40, editable: false, align:'right',edittype:'text',sortable:false},
    {label:'Зона',name:'id_zone', index:'id_zone', width:60, editable: false, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text',sortable:false},
    {label:'Показники',name:'indic', index:'indic', width:80, editable: true, align:'right',hidden:false,
            edittype:'text', editrules:{number:true}},
    {label:'Факт.пок.',name:'indic_real', index:'indic_real', width:60, editable: true, align:'right',hidden:false,
            edittype:'text', editrules:{number:true } },
        
    {label:'Ознака',name:'idk_oper', index:'idk_oper', width:60, editable: false, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lworkmetstatus},stype:'text',sortable:false},
    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true}
    ],
    pager: '#paccnt_works_indic_tablePager',
    rowNum:100,
    sortname: 'num_meter',
    sortorder: 'asc',
    viewrecords: true,
    pgbuttons: false,
    pgtext: null, 
    gridview: true,
    caption: 'Показники',
    hidegrid: false,
    postData:{'w_id': cur_works_id},
    jsonReader : {repeatitems: false},
 
    onSelectRow: function(id) { 
      
    },
        
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');},
  
  gridComplete:function(){

//    works_list_mode =0; //edit   
    if ($(this).getDataIDs().length > 0) 
    {      
//     $("#pNotliveParam").show();        
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);

    }
  }

  }).navGrid('#paccnt_works_indic_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 




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
        
	});

 innerLayout = $("#pmain_content").layout({
		name:	"inner" 
	,	north__paneSelector:	"#pActionBar"
	,	north__closable:	false
	,	north__resizable:	false
        ,	north__size:		30
	,	north__spacing_open:	0
                
	,	south__paneSelector:	"#paccnt_works_indic_list"
	,	south__closable:	true
	,	south__resizable:	true
        ,	south__size:		200
	,	center__paneSelector:	"#paccnt_works_list"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#paccnt_works_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#paccnt_works_table").jqGrid('setGridHeight',_pane.innerHeight()-85);
        }
	,       south__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#paccnt_works_indic_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#paccnt_works_indic_table").jqGrid('setGridHeight',_pane.innerHeight()-85);
        }
        

	});

        outerLayout.resizeAll();
        outerLayout.close('south');     

    //---------------------------------------------------------------------------
    jQuery("#paccnt_works_table").jqGrid('navButtonAdd','#paccnt_works_tablePager',{
    caption:"Друкувати список",
    onClickButton:function(){ 

        var postData = jQuery("#paccnt_works_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
        $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
        $('#freps_params').find("#fdt_b").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
        $('#freps_params').find("#fperiod_str").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       
        $('#freps_params').find("#foper").attr('value', "works_list");
       
        $("#dialog-confirm").find("#dialog-text").html('Виберіть варіант друку');
        $("#dialog-confirm").dialog({
            resizable: false, 
            height:140,
            modal: true,
            autoOpen: false,
            title:'Друк журналу',
            buttons: {
                "На екран": function() {
                    $('#freps_params').find("#fxls").attr('value',0 );       
                    document.forms["freps_params"].submit();        
                    $( this ).dialog( "close" );
                },
                "в файл Excel ": function() {
                    $('#freps_params').find("#fxls").attr('value',1 );       
                    document.forms["freps_params"].submit();        
                    $( this ).dialog( "close" );
                }
            }
        });
    
        jQuery("#dialog-confirm").dialog('open');
            
    } 
});
        
        
        
    jQuery(".btn").button();
    jQuery(".btnSel").button({icons: {primary:'ui-icon-folder-open'}});
        
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   $("#message_zone").dialog({autoOpen: false});
   
    jQuery("#pActionBar :input").addClass("ui-widget-content ui-corner-all");
    
    
   
    
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       
       $('#paccnt_works_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
       
   });
   
   $("#show_peoples").click( function() {
     jQuery("#paccnt_works_table").jqGrid('showCol',["user_name"]);
     jQuery("#paccnt_works_indic_table").jqGrid('showCol',["user_name"]);
  });
   

});