var lastSelAbon;
var abon_target_id;
var abon_target_name=0;
var abon_target_book=0;
var abon_target_code=0;
var abon_target_addr=0;

var isAbonGridCreated = false;

var createAbonGrid = function(){ 
    
  if (isAbonGridCreated) return;
  isAbonGridCreated =true;

  jQuery('#abon_en_sel_table').jqGrid({
    url:'abon_en_sel_data.php',
    //editurl: 'dov_abon_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:300,
    width:700,
    scroll: 0,
    colNames:['','Книга','Особ.рахунок','Місто','Вулиця','Буд.','Корп.','Кв.','Абонент','Прим.','adr'],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {name:'book', index:'book', width:50, editable: true, align:'left',edittype:'text'},
      {name:'code', index:'code', width:50, editable: true, align:'left',edittype:'text'},
      //{name:'addr', index:'addr', width:200, editable: true, align:'left',edittype:'text'},
      {name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text', hidden:town_hidden},
      {name:'street', index:'street', width:100, editable: true, align:'left',edittype:'text'},
      {name:'house', index:'house', width:40, editable: true, align:'left',edittype:'text'},
      {name:'korp', index:'korp', width:40, editable: true, align:'left',edittype:'text'},
      {name:'flat', index:'flat', width:40, editable: true, align:'left',edittype:'text'},
      {name:'abon', index:'abon', width:200, editable: true, align:'left',edittype:'text'},
      {name:'note', index:'note', width:100, editable: true, align:'left',edittype:'text'},
      {name:'addr', index:'addr', width:100, editable: true, align:'left',edittype:'text', hidden:true}
    ],

    pager: '#abon_en_sel_tablePager',
    rowNum:100,
    rowList:[20,50,100,300,500],
    sortname: 'book',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Абоненти',
    hidegrid: false,    
    //hiddengrid: false,
    toolbar: [true,'top'],

   onSelectRow: function(rowid) { 
        lastSelAbon = rowid; 
    },
    
    //ondblClickRow: function(id){ 
    //      jQuery(this).editGridRow(id,{width:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
    ondblClickRow: function(id){ 
        if (abon_target_id!=-1) 
        {
         abon_target_id.val(jQuery(this).jqGrid('getCell',lastSelAbon,'id') ); 
         if (abon_target_name!=0)  abon_target_name.val(jQuery(this).jqGrid('getCell',lastSelAbon,'abon') );
         if (abon_target_book!=0) abon_target_book.val(jQuery(this).jqGrid('getCell',lastSelAbon,'book') );
         if (abon_target_code!=0) abon_target_code.val(jQuery(this).jqGrid('getCell',lastSelAbon,'code') );
         if (abon_target_addr!=0) abon_target_addr.val(jQuery(this).jqGrid('getCell',lastSelAbon,'addr') );        
       
         if (abon_target_name!=0) 
             {   abon_target_name.change();
                 abon_target_name.focus();
             }
        }
        else
        {
            SelectPaccnt(jQuery(this).jqGrid('getCell',lastSelAbon,'id'),
            jQuery(this).jqGrid('getCell',lastSelAbon,'book'),
            jQuery(this).jqGrid('getCell',lastSelAbon,'code'),
            jQuery(this).jqGrid('getCell',lastSelAbon,'abon'), 
            jQuery(this).jqGrid('getCell',lastSelAbon,'addr'))            
        }
        jQuery('#grid_selabon').toggle( );
    } ,  
    
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#abon_en_sel_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_abon_en_sel_table").append("<button class ='btnOk' id='bt_abonsel0' style='height:20px;font-size:-3' > Выбор </button> ");
$("#t_abon_en_sel_table").append("<button class ='btnClose' id='bt_abonclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });


jQuery("#abon_en_sel_table").jqGrid('navButtonAdd','#abon_en_sel_tablePager',{caption:"Все",
	onClickButton:function(){ var sgrid = jQuery("#abon_en_sel_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#abon_en_sel_table").jqGrid('filterToolbar','');


jQuery('#bt_abonclose0').click( function() { jQuery('#grid_selabon').toggle( ); }); 


jQuery('#bt_abonsel0').click( function() { 
    
        if (abon_target_id!=-1)
        {
         abon_target_id.val($("#abon_en_sel_table").jqGrid('getCell',lastSelAbon,'id') ); 
         if (abon_target_name!=0)  abon_target_name.val($("#abon_en_sel_table").jqGrid('getCell',lastSelAbon,'abon') );
         if (abon_target_book!=0) abon_target_book.val($("#abon_en_sel_table").jqGrid('getCell',lastSelAbon,'book') );
         if (abon_target_code!=0) abon_target_code.val($("#abon_en_sel_table").jqGrid('getCell',lastSelAbon,'code') );
         if (abon_target_addr!=0) abon_target_addr.val($("#abon_en_sel_table").jqGrid('getCell',lastSelAbon,'addr') );        
       
         if (abon_target_name!=0) 
             {   abon_target_name.change();
                 abon_target_name.focus();
             }

        }
        else
        {
            SelectPaccnt($("#abon_en_sel_table").jqGrid('getCell',lastSelAbon,'id'),
            $("#abon_en_sel_table").jqGrid('getCell',lastSelAbon,'book'),
            $("#abon_en_sel_table").jqGrid('getCell',lastSelAbon,'code'),
            $("#abon_en_sel_table").jqGrid('getCell',lastSelAbon,'abon'), 
            $("#abon_en_sel_table").jqGrid('getCell',lastSelAbon,'addr'))            
        }

    jQuery('#grid_selabon').toggle();
}); 

jQuery('#grid_selabon').draggable({ handle: ".ui-jqgrid-titlebar" });

}; 