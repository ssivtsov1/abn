//var lkind_meter = '1:Індукційний;2:Електронний';
//var lphase = '1:Однофазний;2:Трифазний';
var selmode;

jQuery(function(){ 
    
  if(r_edit==3)
      r_edit_bool = true;
  else
      r_edit_bool = false;    
    
  jQuery('#dov_meters_table').jqGrid({
    url:'dov_meters_data.php',
    editurl: 'dov_meters_edit.php', 
    datatype: 'json',
    mtype: 'POST',
    height:500,
    width:800,
    colNames:['Код','Назва','ГОСТ', 'Напруга ном.','Ток ном.','Тип','Фазність','Розр.','Зон','Мін. тривалісь зони','Період.повірки','Клас точності','Порог чутливості','АЕ','РЕ','АЕг','РЕг','*'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:false },     
      {name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text'},           
      {name:'normative', index:'normative', width:40, hidden:true, editable: true, align:'left',edittype:'text',
                            editrules:{edithidden:true}},           
      {name:'voltage_nom', index:'voltage_nom', width:100, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},
                        formatoptions: { defaultValue: ' '}},           
      {name:'amperage_nom', index:'amperage_nom', width:100, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true},
                        formatoptions: { defaultValue: ' '}},                 
      {name:'kind_meter', index:'kind_meter', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lkind_meter},
                            stype:'text'},                       
      {name:'phase', index:'phase', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lphase} , 
                            stype:'text' },   
      {name:'carry', index:'carry', width:100, editable: true, align:'right',
                            edittype:'text',formatter:'integer',editrules:{required:true,number:true},
                            formoptions:{label: 'Розрядність', colpos:1, elmprefix:'(*)'} },           
      {name:'zones', index:'zones', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer',editrules:{required:true,number:true,edithidden:true} },           
      {name:'zone_time_min', index:'zone_time_min', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },           
      {name:'term_control', index:'term_control', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'integer',editrules:{required:true,number:true,edithidden:true} },           
      {name:'cl', index:'cl', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{required:true,number:true,edithidden:true} },           
      {name:'buffle', index:'buffle', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },           
      {name:'ae', index:'ae', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',formoptions:{label:'Споживання АЕ'}},
      {name:'re', index:'re', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',formoptions:{label:'Споживання РЕ'}},
      {name:'aeg', index:'aeg', width:30, editable: true, align:'right',hidden:true,
                            formatter:'checkbox',edittype:'checkbox',formoptions:{label:'Генерація АЕ'},editrules:{edithidden:true}},
      {name:'reg', index:'reg', width:30, editable: true, align:'right',hidden:true,
                            formatter:'checkbox',edittype:'checkbox',formoptions:{label:'Генерація РЕ'},editrules:{edithidden:true}},
      {name:'show_def', index:'show_def', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}}
                            
    ],
    pager: '#dov_meters_tablePager',
    autowidth: true,
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Типи приладів обліку',
    hidegrid: false,
    
    
    ondblClickRow: function(id){ 
      if(selmode==1)
      {
           window.opener.SelectAddrExternal(id,jQuery(this).jqGrid('getCell',id,'name') );
           window.opener.focus();
           self.close();            
      }

      if(selmode==0)
      {
         if (r_edit_bool) jQuery(this).editGridRow(id,{width:400,height:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
      }

     } ,  
      

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}
  
//  jsonReader : { repeatitems: false }

  }).navGrid('#dov_meters_tablePager',
        {edit:r_edit_bool,add:r_edit_bool,del:r_edit_bool, 
            addtext: 'Додати',
            edittext: 'Редагувати',
            deltext: 'Видалити' },
        {width:400,height:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {width:400,height:500,reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {reloadAfterSubmit:false,afterSubmit:processAfterEdit}, 
        {} 
        ); 

jQuery('#dov_meters_table').jqGrid('navButtonAdd','#dov_meters_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_meters_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery('#dov_meters_table').jqGrid('filterToolbar','');
jQuery("#dov_meters_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
      jQuery(this).editGridRow(id,{width:400,height:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } } );

//, {"onEnter": function(id){ 
//      jQuery(this).editGridRow(id,{width:400,height:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  }}

//jQuery('#lshow_grid').click( function() { jQuery('#dov_meters_table').jqGrid('setCaption','New Caption')});

//jQuery('#dov_meters_table').jqGrid('gridResize',{minWidth:800,maxWidth:1000,minHeight:400, maxHeight:800});


//jQuery('#ls1').click( function() {jQuery('#grid_dform').css('display','block');});
//jQuery('#ls2').click( function() { jQuery('#grid_dform').css('display','none') });

//jQuery('#message_zone').dialog({ autoOpen: false });
//jQuery('#ls1').click( function() {jQuery('#message_zone').dialog('open'); });
//jQuery('#ls2').click( function() {jQuery('#message_zone').dialog('close');});

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
            jQuery("#dov_meters_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#dov_meters_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }

	});
        
        outerLayout.resizeAll();
        outerLayout.close('south');             

}); 
 

 //jQuery('#lshow_grid').click( function() { jQuery('#dov_meters_table').jqGrid('setGridParam',{caption: 'Счетчики 111'}).trigger('reloadGrid')});

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
