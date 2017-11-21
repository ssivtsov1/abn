var lastSelCachespravka;
var isspravkaCacheCreated = false;

var createspravkaCache = function(){ 
    
  if (isspravkaCacheCreated) return;
  isspravkaCacheCreated =true;

  jQuery("#grid_spravkacache").css({'left': 660, 'top': 150});
  
  jQuery('#spravka_cache_table').jqGrid({
    url:'spravka_cache_data.php',
    //editurl: 'dov_fider_edit.php', 
    datatype: 'json',
    mtype: 'POST',
    height:300,
    width:600,
    colNames:[],
    colModel :[ 
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},                
    {label:'Адреса',name:'addr', index:'addr', width:150, editable: true, align:'left',edittype:'text'},                    
    {label:'Абонент',name:'abon', index:'abon', width:100, editable: true, align:'left',edittype:'text'},

    {label:'№ дов.',name:'num_sp',index:'num_sp', width:60, editable: true, align:'left',edittype:'text'},
    {label:'Дата дов.',name:'dt_sp', index:'dt_sp', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},


   // {label:'Тип дов.',name:'doc_type', index:'doc_type', width:60, editable: true, align:'left',
    //                        edittype:'select',formatter:'select',editoptions:{value:ltype},stype:'text'},

    {label:'Період з',name:'date_start', index:'date_start', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Період по',name:'date_end', index:'date_end', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'}

    ],
    pager: '#spravka_cache_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],

    pgbuttons: false,
    pgtext: null, 

    sortname: 'num_sp',
    sortorder: 'asc',
    viewrecords: false,
    gridview: true,
    caption: 'Вибрані особові рахунки',
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    jsonReader : {repeatitems: false},
    //ondblClickRow: function(id){ 
    //  jQuery(this).editGridRow(id,{width:350,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  } ,  
   onSelectRow: function(rowid) { 
        lastSelCachespravka = rowid; 
    },
    /*
    ondblClickRow: function(id){ 
        tarif_target_id.val(jQuery(this).jqGrid('getCell',lastSelCachespravka,'id') ); 
        tarif_target_name.val(jQuery(this).jqGrid('getCell',lastSelCachespravka,'nm') );
        tarif_target_name.focus();
        jQuery('#grid_spravkacache').toggle( );
    } ,  
*/

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#spravka_cache_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

//$("#t_spravka_cache_table").append("<button class ='btnOk' id='bt_cacheprint' style='height:20px;font-size:-3' > Друк </button> ");
$("#t_spravka_cache_table").append("<button class ='btnClose' id='bt_spravkaclose' style='height:20px;font-size:-3' > Закр. </button> ");
    
//jQuery(".btnOk").button({ icons: {primary:'ui-icon-check'} });
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

jQuery("#spravka_cache_table").jqGrid('filterToolbar','');

jQuery('#bt_spravkaclose').click( function() { jQuery('#grid_spravkacache').toggle( ); }); 
/*
jQuery('#bt_cacheprint').click( function() { 
    
       var rows = $('#spravka_cache_table').getDataIDs();
       var json_str = JSON.stringify(rows);

       $("#fprint_params").find("#pspravka_list").attr('value',json_str ); 
       
       $("#fprint_params").attr('target',"_blank" );           
       document.print_params.submit();    

}); 
*/
jQuery('#grid_spravkacache').draggable({ handle: ".ui-jqgrid-titlebar" });


jQuery("#spravka_cache_table").jqGrid('navButtonAdd','#spravka_cache_tablePager',{caption:"-",
	onClickButton:function(){ 

                  var request = $.ajax({
                    url: "spravka_cache_edit.php",
                    type: "POST",
                    data: {
                        id : lastSelCachespravka,
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
                           jQuery('#spravka_cache_table').trigger('reloadGrid');                      
                
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

jQuery("#spravka_cache_table").jqGrid('navButtonAdd','#spravka_cache_tablePager',{caption:"--",
	onClickButton:function(){ 

                  var request = $.ajax({
                    url: "spravka_cache_edit.php",
                    type: "POST",
                    data: {
                        id : lastSelCachespravka,
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
                           jQuery('#spravka_cache_table').trigger('reloadGrid');                      
                
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

 jQuery("#spravka_cache_table").jqGrid('navButtonAdd','#spravka_cache_tablePager',{caption:"Друкувати список",
    onClickButton:function(){ 

        var postData = jQuery("#spravka_cache_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       $('#freps_params').find("#foper").attr('value', "sprav_list");
       
       $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Друк списку',
        buttons: {
            "На екран": function() {
                $('#freps_params').find("#fxls2").attr('value',0 );       
                document.forms["freps_params"].submit();        
                $( this ).dialog( "close" );
            },
            "в файл Excel ": function() {
                $('#freps_params').find("#fxls2").attr('value',1 );       
                document.forms["freps_params"].submit();        
                $('#freps_params').find("#fxls2").attr('value',0 );       
                $( this ).dialog( "close" );
            }
        }
       });
    
       jQuery("#dialog-confirm").dialog('open');
            
    } 
});


jQuery("#spravka_cache_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
        
   // tarif_target_id.val(jQuery('#spravka_cache_table').jqGrid('getCell',lastSelCachespravka,'id') ); 
   // tarif_target_name.val(jQuery('#spravka_cache_table').jqGrid('getCell',lastSelCachespravka,'nm') ); 
   // tarif_target_name.focus();

   // jQuery('#grid_spravkacache').toggle( );  
} } );

}; 

