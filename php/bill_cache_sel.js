var lastSelCacheBill;
//var tarif_target_id;
//var tarif_target_name;

var isBillCacheCreated = false;

var createBillCache = function(){ 
    
  if (isBillCacheCreated) return;
  isBillCacheCreated =true;

  jQuery('#bill_cache_table').jqGrid({
    url:'bill_cache_data.php',
    //editurl: 'dov_fider_edit.php', 
    datatype: 'json',
    mtype: 'POST',
    height:300,
    width:500,
    colNames:[],
    colModel :[ 
    {name:'id_doc', index:'id_doc', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},                
    {label:'Абонент',name:'abon', index:'abon', width:150, editable: true, align:'left',edittype:'text'},
    {label:'Адреса',name:'addr', index:'addr', width:150, editable: true, align:'left',edittype:'text'},                            

    {label:'Квтг',name:'demand', index:'demand', width:50, editable: true, align:'right',hidden:false,
                           edittype:'text'},

    {label:'Сума,грн',name:'value', index:'value', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    
    {label:'Період',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', formatter:'date', hidden:false}

    ],
    pager: '#bill_cache_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],

    pgbuttons: false,
    pgtext: null, 

    sortname: 'book',
    sortorder: 'asc',
    viewrecords: false,
    gridview: true,
    caption: 'Вибрані рахунки',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    jsonReader : {repeatitems: false},
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelCacheBill = rowid; 
    },
    /*
    ondblClickRow: function(id){ 
        tarif_target_id.val(jQuery(this).jqGrid('getCell',lastSelCacheBill,'id') ); 
        tarif_target_name.val(jQuery(this).jqGrid('getCell',lastSelCacheBill,'nm') );
        tarif_target_name.focus();
        jQuery('#grid_billcache').toggle( );
    } ,  
*/

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#bill_cache_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_bill_cache_table").append("<button class ='btnOk' id='bt_cacheprint' style='height:20px;font-size:-3' > Друк </button> ");
$("#t_bill_cache_table").append("<button class ='btnClose' id='bt_billclose' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

jQuery("#bill_cache_table").jqGrid('filterToolbar','');

jQuery('#bt_billclose').click( function() { jQuery('#grid_billcache').toggle( ); }); 

jQuery('#bt_cacheprint').click( function() { 
    
       var rows = $('#bill_cache_table').getDataIDs();
       var json_str = JSON.stringify(rows);

       $("#fprint_params").find("#pbill_list").attr('value',json_str ); 
       
       $("#fprint_params").attr('target',"_blank" );           
       document.print_params.submit();    

}); 

jQuery('#grid_billcache').draggable({ handle: ".ui-jqgrid-titlebar" });


jQuery("#bill_cache_table").jqGrid('navButtonAdd','#bill_cache_tablePager',{caption:"-",
	onClickButton:function(){ 

                  var request = $.ajax({
                    url: "bill_cache_edit.php",
                    type: "POST",
                    data: {
                        id_doc : lastSelCacheBill,
                        oper: 'del'
                    },
                    dataType: "json"
                });

                request.done(function(data ) {  
        
                    if (data.errcode!==undefined)
                    {
                        $('#message_zone').append(data.errstr);  
                        $('#message_zone').append("<br>");                 
                        //jQuery("#message_zone").dialog('open');
            
                        if(data.errcode<=0) 
                        {
                           jQuery('#bill_cache_table').trigger('reloadGrid');                      
                
                        }
                        else
                        {
                            jQuery("#message_zone").dialog('open');                                    
                        }
                    }
                });

                request.fail(function(data ) {
                    alert("error");
        
                });

       ;} 
});

jQuery("#bill_cache_table").jqGrid('navButtonAdd','#bill_cache_tablePager',{caption:"--",
	onClickButton:function(){ 

                  var request = $.ajax({
                    url: "bill_cache_edit.php",
                    type: "POST",
                    data: {
                        id_doc : lastSelCacheBill,
                        oper: 'delall'
                    },
                    dataType: "json"
                });

                request.done(function(data ) {  
        
                    if (data.errcode!==undefined)
                    {
                        $('#message_zone').append(data.errstr);  
                        $('#message_zone').append("<br>");                 
                        //jQuery("#message_zone").dialog('open');
            
                        if(data.errcode<=0) 
                        {
                           jQuery('#bill_cache_table').trigger('reloadGrid');                      
                
                        }
                        else
                        {
                            jQuery("#message_zone").dialog('open');                                    
                        }
                    }
                });

                request.fail(function(data ) {
                    alert("error");
        
                });

       ;} 
});


jQuery("#bill_cache_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
        
   // tarif_target_id.val(jQuery('#bill_cache_table').jqGrid('getCell',lastSelCacheBill,'id') ); 
   // tarif_target_name.val(jQuery('#bill_cache_table').jqGrid('getCell',lastSelCacheBill,'nm') ); 
   // tarif_target_name.focus();

   // jQuery('#grid_billcache').toggle( );  
} } );

}; 

