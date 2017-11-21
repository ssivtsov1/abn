var lastSelMeter;
var meter_target_id;
var meter_target_name;
var meter_target_carry;
var id_m=null;     

var isMeterGridCreated = false;

var createMeterGrid = function(id){ 
    
  id_m = id;  
  if (isMeterGridCreated) 
      {
       if (id_m)
       {
          jQuery('#dov_meters_table').jqGrid('setGridParam',{'postData':{'selected_id':id_m}}).trigger('reloadGrid');
       }
        return;
      }
  isMeterGridCreated =true;
  
  jQuery('#dov_meters_table').jqGrid({
    url:'dov_meters_data.php',
    editurl: 'dov_meters_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:300,
    width:500,
    scroll: 0,
    scrollrows : true,
    colNames:['Код','Назва','ГОСТ', 'Напруга ном.','Ток ном.','Тип','Фазність','Розр.','Зон','Мін. тривалісь зони','Період.повірки','Клас точності','Порог чутливості','АЕ','РЕ','АЕг','РЕг','*'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},     
      {name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text'},           
      {name:'normative', index:'normative', width:40, hidden:true, editable: true, align:'left',edittype:'text',
                            editrules:{edithidden:true}},           
      {name:'voltage_nom', index:'voltage_nom', width:100, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },           
      {name:'amperage_nom', index:'amperage_nom', width:100, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number',editrules:{number:true,edithidden:true} },                 
      {name:'kind_meter', index:'kind_meter', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lkind_meter},stype:'select' },                       
      {name:'phase', index:'phase', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lphase}, stype:'select' },  
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
      {name:'ae', index:'ae', width:30, editable: true, align:'right',hidden:true,editrules:{edithidden:true},
                            formatter:'checkbox',edittype:'checkbox',formoptions:{label:'Споживання АЕ'}},
      {name:'re', index:'re', width:30, editable: true, align:'right',hidden:true,editrules:{edithidden:true},
                            formatter:'checkbox',edittype:'checkbox',formoptions:{label:'Споживання РЕ'}},
      {name:'aeg', index:'aeg', width:30, editable: true, align:'right',hidden:true,editrules:{edithidden:true},
                            formatter:'checkbox',edittype:'checkbox',formoptions:{label:'Генерація АЕ'}},
      {name:'reg', index:'reg', width:30, editable: true, align:'right',hidden:true,editrules:{edithidden:true},
                            formatter:'checkbox',edittype:'checkbox',formoptions:{label:'Генерація РЕ'}},
      {name:'show_def', index:'show_def', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',stype:'select', searchoptions:{value:': ;1:*'}}
                            
    ],
    pager: '#dov_meters_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: false,
    gridview: true,
    postData:{'selected_id': id_m}, 
    caption: 'Лічильники',
    hidegrid: false,
    toolbar: [true,'top'],
    //hiddengrid: false,
    
    onSelectRow: function(rowid) { 
        lastSelMeter = rowid; 
        jQuery('#pdebug_met').html(  jQuery(this).jqGrid('getCell',rowid,'name'))   
    },

    ondblClickRow: function(id){ 
        meter_target_id.val(jQuery(this).jqGrid('getCell',lastSelMeter,'id') ); 
        meter_target_name.val(jQuery(this).jqGrid('getCell',lastSelMeter,'name') );
        
        if (meter_target_carry!=null) meter_target_carry.val(jQuery('#dov_meters_table').jqGrid('getCell',lastSelMeter,'carry') );         
        
        meter_target_name.focus();
        if (meter_target_carry!=null) meter_target_carry.focus();
        meter_target_name.focus();
        jQuery('#grid_selmeter').toggle( );
    } ,  
    onPaging : function(but) { 
                id_m=0;
                $(this).jqGrid('setGridParam',{'postData':{'selected_id':id_m}});        
    },    
    
    gridComplete:function(){

            if ( id_m )
            {
                $(this).setSelection(id_m, true);                    
            }
            else
            {
                if ($(this).getDataIDs().length > 0) 
                {      
                    var first_id = parseInt($(this).getDataIDs()[0]);
                    $(this).setSelection(first_id, true);
                }
                
            }
        },
    
//    ondblClickRow: function(id){ 
//      jQuery(this).editGridRow(id,{width:400,height:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
      
//    loadError: function (jqXHR, textStatus, errorThrown) {
//        alert('HTTP status code: ' + jqXHR.status + '' +
//              'textStatus: ' + textStatus + '' +
//              'errorThrown: ' + errorThrown);
//        alert('HTTP message body (jqXHR.responseText): ' + '' + jqXHR.responseText);
//    }

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_meters_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

jQuery("#dov_meters_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
      
        meter_target_id.val(jQuery(this).jqGrid('getCell',lastSelMeter,'id') ); 
        meter_target_name.val(jQuery(this).jqGrid('getCell',lastSelMeter,'name') );
        if (meter_target_carry!=null) meter_target_carry.val(jQuery('#dov_meters_table').jqGrid('getCell',lastSelMeter,'carry') );         
        meter_target_name.focus();
        if (meter_target_carry!=null) meter_target_carry.focus();
        meter_target_name.focus();
        jQuery('#grid_selmeter').toggle( );

  } } );


    //$("#t_dov_meters_table").append("<input type='button' class ='btn' value='Click Me' style='height:20px;font-size:-3'/>");
    $("#t_dov_meters_table").append("<button class ='btnOk' id='bt_metsel0' style='height:20px;font-size:-3' > Выбор </button> ");
    $("#t_dov_meters_table").append("<button class ='btnClose' id='bt_metclose0' style='height:20px;font-size:-3' > Закр. </button> ");

    jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
    jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

    jQuery("#dov_meters_table").jqGrid('filterToolbar','');
    jQuery('#dov_meters_table').jqGrid('navButtonAdd','#dov_meters_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#dov_meters_table")[0];
        sgrid.clearToolbar();  ;} 
    });
    
    
//jQuery('#lshow_grid').click( function() { jQuery('#dov_meters_table').jqGrid('setCaption','New Caption')});

//jQuery('#dov_meters_table').jqGrid('gridResize',{minWidth:800,maxWidth:1000,minHeight:400, maxHeight:800});


//jQuery('#ls1').click( function() {jQuery('#grid_dform').css('display','block');});
//jQuery('#ls2').click( function() { jQuery('#grid_dform').css('display','none') });

//jQuery('#message_zone').dialog({ autoOpen: false });
//jQuery('#ls1').click( function() {jQuery('#message_zone').dialog('open'); });
//jQuery('#ls2').click( function() {jQuery('#message_zone').dialog('close');});

jQuery('#bt_metclose0').click( function() { jQuery('#grid_selmeter').toggle( ); }); 


jQuery('#bt_metsel0').click( function() { 
    meter_target_id.val(jQuery('#dov_meters_table').jqGrid('getCell',lastSelMeter,'id') ); 
    meter_target_name.val(jQuery('#dov_meters_table').jqGrid('getCell',lastSelMeter,'name') ); 
    if (meter_target_carry!=null) meter_target_carry.val(jQuery('#dov_meters_table').jqGrid('getCell',lastSelMeter,'carry') ); 

    meter_target_name.focus();
    if (meter_target_carry!=null) meter_target_carry.focus();
    meter_target_name.focus();

    jQuery('#grid_selmeter').toggle( );
}); 

jQuery('#grid_selmeter').draggable({ handle: ".ui-jqgrid-titlebar" });

}; 
 

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
               jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};              
            }
        }


