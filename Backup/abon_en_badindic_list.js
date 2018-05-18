var edit_row_id=0;

$.validator.methods.number = function (value, element) {
    return this.optional(element) || /^-?(?:\d+|\d{1,3}(?:[\s\.,]\d{3})+)(?:[\.,]\d+)?$/.test(value);
}

jQuery(function(){ 

  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
		buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");
    
  $("#fmmgg").datepicker( "setDate" , mmgg );


  jQuery('#badindic_table').jqGrid({
    url:'abon_en_badindic_list_data.php',
    //editurl: 'abon_en_badindic_edit.php',
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
//    {name:'id_pack', index:'id_pack', width:40, editable: false, align:'center',hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    {name:'id_meter', index:'id_meter', width:40, editable: false, align:'center',hidden:true},    
    {name:'id_typemet', index:'id_typemet', width:40, editable: false, align:'center',hidden:true},    
    {name:'id_hwork', index:'id_hwork', width:40, editable: false, align:'center',hidden:true},    
    {name:'idk_work', index:'idk_work', width:40, editable: false, align:'center',hidden:true},    
   
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},                
    //{label:'Адреса',name:'address', index:'address', width:150, editable: true, align:'left',edittype:'text'},                    
    {label:'Абонент',name:'abon', index:'abon', width:150, editable: true, align:'left',edittype:'text'},
   
    {label:'№ ліч.',name:'num_eqp', index:'num_eqp', width:80, editable: true, align:'left',edittype:'text'},            
    {label:'Тип ліч.',name:'type_meter', index:'type_meter', width:80, editable: true, align:'left',edittype:'text'},                
    {label:'Розр. ліч.',name:'carry', index:'carry', width:40, editable: true, align:'left',edittype:'text'},                    
    {label:'К.тр',name:'coef_comp', index:'coef_comp', width:40, editable: true, align:'left',edittype:'text'},                        
    {label:'Зона',name:'id_zone', index:'id_zone', width:60, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text'},                       

    {label:'Дата',name:'dat_ind', index:'dat_ind', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Попер.пок.',name:'p_indic', index:'p_indic', width:80, editable: true, align:'right',hidden:false,edittype:'text',
                    formatter:'integer'},           

    //{label:'Дата попер.',name:'dt_p_indic', index:'dt_p_indic', width:80, editable: true, 
    //                    align:'left',edittype:'text',formatter:'date'},

    {label:'Поточні пок.',name:'value', index:'value', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           

    {label:'Спожив.',name:'value_cons', index:'value_cons', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           

    {label:'Період',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', hidden:false,formatter:'date'},
    {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
        
    {label:'Тип показників',name:'id_operation', index:'id_operation', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lindicoper},stype:'text'},                       
    //{label:'№ відомості',name:'num_pack', index:'num_pack', width:60, editable: true, align:'left',edittype:'text'},
    {label:'Помилка',name:'error_text', index:'error_text', width:120, editable: true, align:'left',edittype:'text'},
//    {label:'Вручну',name:'is_manual', index:'is_manual', width:30, editable: true, align:'right',
//                            formatter:'checkbox',edittype:'checkbox',
//                            stype:'select', searchoptions:{value:': ;1:*'}},
    {label:'Закр.',name:'flock', index:'flock', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}}

    ],
    pager: '#badindic_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'code',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Показники з помилками ',
    //hiddengrid: false,
    jsonReader : {repeatitems: false},
    hidegrid: false,
    postData:{'p_mmgg': mmgg },

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
        
        
          $("#fpaccnt_params").find("#pmode").attr('value',0 );
          $("#fpaccnt_params").find("#pid_paccnt").attr('value',
              $("#badindic_table").jqGrid('getCell',edit_row_id,'id_paccnt'));
          
          $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
            $("#badindic_table").jqGrid('getCell',edit_row_id,'book')+'/'+
            $("#badindic_table").jqGrid('getCell',edit_row_id,'code')+' '+ 
            $("#badindic_table").jqGrid('getCell',edit_row_id,'abon')      );
        
          $("#fpaccnt_params").find("#ppaccnt_book").attr('value',
                $("#badindic_table").jqGrid('getCell',edit_row_id,'book') );

          $("#fpaccnt_params").find("#ppaccnt_code").attr('value',
                $("#badindic_table").jqGrid('getCell',edit_row_id,'code') );

          $("#fpaccnt_params").find("#ppaccnt_name").attr('value',
                $("#badindic_table").jqGrid('getCell',edit_row_id,'abon') );

          $("#fpaccnt_params").attr('target',"_blank" );  
          $("#fpaccnt_params").attr("action","abon_en_saldo.php");                    
          
          document.paccnt_params.submit();
        
        
      } else { alert("Please select Row") }       
      
    } ,  

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#badindic_tablePager',
       {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  jQuery("#badindic_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
  //    jQuery(this).editGridRow(id,TableEditOptions);
  }} );


  jQuery("#badindic_table").jqGrid('filterToolbar','');


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
            
            $("#badindic_table").jqGrid('setGridWidth',$pane.innerWidth()-15);
            $("#badindic_table").jqGrid('setGridHeight',$pane.innerHeight()-130);

        }
        
	});

        
    outerLayout.resizeAll();
    outerLayout.close('south');  
   // innerLayout.hide('north');        
        
    jQuery(".btn").button();
    jQuery(".btnSel").button({ icons: {primary:'ui-icon-folder-open'} });
    
   
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open'); });
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   $("#message_zone").dialog({autoOpen: false});
   
 //----------------------------------------------------------------  


    jQuery("#badindic_table").jqGrid('navButtonAdd','#badindic_tablePager',{caption:"Ревізія",
    onClickButton:function(){ 
        
       var gsr = jQuery("#badindic_table").jqGrid('getGridParam','selrow'); 
       if(!gsr) return;
       
       var flock=jQuery("#badindic_table").jqGrid('getCell',edit_row_id,'flock');
       /*
       var fmanual=jQuery("#badindic_table").jqGrid('getCell',edit_row_id,'is_manual');
       var fmmgg=jQuery("#badindic_table").jqGrid('getCell',edit_row_id,'mmgg');
       var fid=jQuery("#badindic_table").jqGrid('getCell',edit_row_id,'id');
       */
       if (flock=='Yes')  return;


        var request = $.ajax({
            url: "indic_refresh_one_edit.php",
            type: "POST",
            data: {
                id : edit_row_id
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
                    jQuery('#badindic_table').trigger('reloadGrid');    
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
        
    }
});    
   
   
$("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       
       $('#badindic_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
       
});    
   
});

