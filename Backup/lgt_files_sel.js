var lastSelFile;
var file_target_id;
var file_target_name;

var isFileGridCreated = false;

var createFilesGrid = function(){ 
    
  if (isFileGridCreated) 
  {   
      jQuery('#loaded_files_table').trigger('reloadGrid');
      return;
  }
   
  isFileGridCreated =true;

  jQuery('#loaded_files_table').jqGrid({
    url:'lgt_files_data.php',
    //editurl: 'dov_fider_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:300,
    width:400,
    colNames:['Код','Файл','Регіон','Дата','Заст.'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true },
      {name:'filename', index:'filename', width:200, editable: true, align:'left',edittype:'text'},
      {name:'id_region', index:'id_region', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lregion},stype:'select',hidden:false},
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', 
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {name:'load_status', index:'load_status', width:40, editable: true, align:'left',edittype:'text'},        
    ],
    pager: '#loaded_files_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'dt',
    sortorder: 'desc',
    viewrecords: false,
    gridview: true, 
    caption: 'Файли',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelFile = rowid; 
    },
    
    ondblClickRow: function(id){ 
        file_target_id.val(jQuery(this).jqGrid('getCell',lastSelFile,'id') ); 
        file_target_name.val(jQuery(this).jqGrid('getCell',lastSelFile,'filename')+'('+
          jQuery(this).jqGrid('getCell',lastSelFile,'dt')+')');
        file_target_name.change();
        file_target_name.focus();
        jQuery('#grid_selfile').toggle( );
    } ,  


  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#loaded_files_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_loaded_files_table").append("<button class ='btnOk' id='bt_filesel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_loaded_files_table").append("<button class ='btnClose' id='bt_fileclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

 
jQuery("#loaded_files_table").jqGrid('navButtonAdd','#loaded_files_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#loaded_files_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#loaded_files_table").jqGrid('filterToolbar','');

jQuery('#bt_fileclose0').click( function() { jQuery('#grid_selfile').toggle( ); }); 

jQuery('#bt_filesel0').click( function() { 
    
    if ($("#loaded_files_table").getDataIDs().length == 0)   {return} ;    
      
    file_target_id.val(jQuery('#loaded_files_table').jqGrid('getCell',lastSelFile,'id') ); 
    file_target_name.val(jQuery('#loaded_files_table').jqGrid('getCell',lastSelFile,'filename')+'('+
          jQuery('#loaded_files_table').jqGrid('getCell',lastSelFile,'dt')+')');

    file_target_name.change();
    file_target_name.focus();

    jQuery('#grid_selfile').toggle( );
}); 

jQuery('#grid_selfile').draggable({ handle: ".ui-jqgrid-titlebar" });
}; 

