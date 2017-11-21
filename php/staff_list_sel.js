var lastSelPerson; 
var person_target_id;
var person_target_name;
var person_target_prof;

var isPersonGridCreated = false;

var createPersonGrid = function(){ 
    
  if (isPersonGridCreated) return; 
  isPersonGridCreated =true;

  jQuery('#person_sel_table').jqGrid({
    url:'staff_list_sel_data.php',
    //editurl: 'dov_fider_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:250,
    width:500,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      //{name:'id_department', index:'id_department', width:40, editable: false, align:'center'},
      {label:'Підрозділ',name:'id_department', index:'id_department', width:80, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:staff_dep},stype:'select',hidden:false},

      {label:"Працівник",name:'represent_name', index:'represent_name', width:150, editable: true, align:'left',edittype:"text"},      
      {label:'id_post',name:'id_post', index:'id_post', width:40, editable: true, align:'right', hidden:true},
      {label:'Посада',name:'name_post', index:'name_post', width:120, editable: true, align:'left'}
    ],
    pager: '#person_sel_tablePager',
    rowNum:300,
    rowList:[20,50,100,300,500],
    sortname: 'represent_name',
    sortorder: 'asc',
    viewrecords: false,
    gridview: true,
    caption: 'Працівники',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelPerson = rowid; 
    },
    
    ondblClickRow: function(id){ 
        person_target_id.val(jQuery(this).jqGrid('getCell',lastSelPerson,'id') ); 

        if (person_target_name!=0)  person_target_name.val(jQuery(this).jqGrid('getCell',lastSelPerson,'represent_name'));
        if (person_target_prof!=0)  person_target_prof.val(jQuery(this).jqGrid('getCell',lastSelPerson,'name_post'));
        
        person_target_name.change();
        person_target_name.focus();
        jQuery('#grid_selperson').toggle( );
    } ,  


  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#person_sel_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

jQuery("#person_sel_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
      
        person_target_id.val(jQuery(this).jqGrid('getCell',lastSelPerson,'id') ); 

        if (person_target_name!=0)  person_target_name.val(jQuery(this).jqGrid('getCell',lastSelPerson,'represent_name'));
        if (person_target_prof!=0)  person_target_prof.val(jQuery(this).jqGrid('getCell',lastSelPerson,'name_post'));
        
        person_target_name.change();
        person_target_name.focus();
        jQuery('#grid_selperson').toggle( );

  } } );

$("#t_person_sel_table").append("<button class ='btnOk' id='bt_personsel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_person_sel_table").append("<button class ='btnClose' id='bt_personclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

 
jQuery("#person_sel_table").jqGrid('navButtonAdd','#person_sel_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#person_sel_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#person_sel_table").jqGrid('filterToolbar','');

jQuery('#bt_personclose0').click( function() { jQuery('#grid_selperson').toggle( ); }); 

jQuery('#bt_personsel0').click( function() { 
    person_target_id.val(jQuery('#person_sel_table').jqGrid('getCell',lastSelPerson,'id') ); 

    if (person_target_name!=0)  person_target_name.val(jQuery('#person_sel_table').jqGrid('getCell',lastSelPerson,'represent_name') );
    if (person_target_prof!=0)  person_target_prof.val(jQuery('#person_sel_table').jqGrid('getCell',lastSelPerson,'name_post') );

    person_target_name.change();
    person_target_name.focus();

    jQuery('#grid_selperson').toggle( );
}); 

jQuery('#grid_selperson').draggable({ handle: ".ui-jqgrid-titlebar" });
}; 

