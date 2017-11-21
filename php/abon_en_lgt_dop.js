var edit_row_id=0;
var selICol=0; //iCol of selected cell
var selIRow=0; //iRow of selected cell
var newrowcnt = -1;
var is_change = 0;
jQuery(function(){ 

  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
		buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");
    
  $("#fmmgg").datepicker( "setDate" , mmgg );

  function number_ukr(val, nm) 
  {
           if (isNaN(val.replace(',', '.'))) 
               return [false, nm + ": " + $.jgrid.edit.msg.number] 
           else
               return [true, ""] 
  };
                
  jQuery('#lgt_table').jqGrid({
    url:'abon_en_lgt_dop_data.php',
    datatype: 'json',
    mtype: 'POST',
    height:200,
    width:400,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true,sortable:false},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    //{name:'id_grp_lgt', index:'id_grp_lgt', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    
    {label:'Книга',name:'book', index:'book', width:40, editable: false, align:'left',edittype:'text'},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: false, align:'left',edittype:'text'},                
    {label:'Абонент',name:'abon', index:'abon', width:150, editable: false, align:'left',edittype:'text'},
    {label:'Адреса',name:'addr', index:'addr', width:150, editable: false, align:'left',edittype:'text'},
    {label:'Код пільги',name:'alt_code', index:'alt_code', width:40, editable: false, align:'left',edittype:'text',hidden:true},    
    
    {label:'Пільга',name:'id_grp_lgt', index:'id_grp_lgt', width:150, editable: true, align:'left',
            edittype:'select',formatter:'select',editoptions:{value:lgtlist},
            classes: 'editable_column_class'    
    },
    
    {label:'Сума пільги',name:'sum_val', index:'sum_val', width:50, editable: true, align:'right',hidden:false,
            edittype:'text',
            sortable:false,
            classes: 'editable_column_class',
            editrules:{
                custom:true, custom_func:number_ukr
            },
            editoptions: { 
                dataInit : function (elem) {
                    $(elem).focus(function(){
                        this.select();
                    })
                },
                dataEvents: [
                { 
                    type: 'keydown', 
                    fn: function(e) { 
                        var key = e.charCode || e.keyCode;
                        if (key == 13)//enter
                        {
                            setTimeout("jQuery('#lgt_table').editCell(" + selIRow + " , " + selICol + " + 1, true);", 100);
                        }
                    }
                } 
                ]
            }                            
     },
    {label:'кВтг',name:'demand_val', index:'demand_val', width:50, editable: true, align:'right',hidden:false,
            edittype:'text',
            sortable:false,
            classes: 'editable_column_class',
            editrules:{
                number:true
            },
            editoptions: { 
                dataInit : function (elem) {
                    $(elem).focus(function(){
                        this.select();
                    })
                },
                dataEvents: [
                { 
                    type: 'keydown', 
                    fn: function(e) { 
                        var key = e.charCode || e.keyCode;
                        if (key == 13)//enter
                        {
                            setTimeout("jQuery('#lgt_table').editCell(" + selIRow + " , " + selICol + " + 1, true);", 100);
                        }
                    }
                } 
                ]
            }                            
     },
 
     {label:'Період пільги',name:'mmgg_lgt', index:'mmgg_lgt', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date',sortable:false,classes: 'editable_column_class',
            editoptions: {
                dataInit : function (elem) {
                    $(elem).focus(function(){
                        this.select();
                    })
                },
                dataEvents: [
                { 
                    type: 'keydown', 
                    fn: function(e) { 
                        var key = e.charCode || e.keyCode;
                        if (key == 13)//enter
                        {
                            //setTimeout("jQuery('#lgt_table').editCell(" + selIRow + " + 1, " + selICol + " - 1, true);", 100);
                            setTimeout("jQuery('#lgt_table').editCell(" + selIRow + " , " + selICol + " + 1, true);", 100);
                        }
                    }
                } 
                ]
            }                        
      },

    {label:'Корег.',name:'is_corr', index:'is_corr', width:30, editable: true, align:'right',hidden:false,
            edittype:'text',
            sortable:false,
            classes: 'editable_column_class',
            editrules:{
                number:true
            },
            editoptions: { 
                dataInit : function (elem) {
                    $(elem).focus(function(){
                        this.select();
                    })
                }
                /*,dataEvents: [
                { 
                    type: 'keydown', 
                    fn: function(e) { 
                        var key = e.charCode || e.keyCode;
                        if (key == 13)//enter
                        {
                            setTimeout("jQuery('#lgt_table').editCell(" + selIRow + " , " + selICol + " + 1, true);", 100);
                        }
                    }
                } 
                ]*/
            }                            
     },


    {label:'Субс.',name:'subs_value', index:'subs_value', width:40, editable: false, align:'right',edittype:'text',hidden:false},
    {label:'Оператор',name:'user_name', index:'user_name', width:80, editable: true, align:'left',edittype:'text',hidden:true},
    {name:'dt', index:'dt', width:70, editable: false, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
    {label:'Закр.',name:'flock', index:'flock', width:30, editable: false, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}}


    ],
    pager: '#lgt_tablePager',
    autowidth: true,
    //shrinkToFit : false,
    rowNum:500,
    //rowList:[50,100,200],
    sortname: 'code',
    sortorder: 'asc',
    viewrecords: true,
    //gridview: true,
    caption: 'Додаткові пільги',
    //hiddengrid: false,
    forceFit : true,
    hidegrid: false,    
    postData:{'p_mmgg': mmgg},
    cellEdit: true, 
    cellsubmit: 'clientArray',
    jsonReader : {repeatitems: false},
    //pgbuttons: false,     // disable page control like next, back button
    //pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    onPaging: function() {

      if (is_change==1)
      {
        alert('Запишіть змінені дані перед переходом на іншу сторінку!') ;
        return 'stop'; 
      }

    } ,
    gridComplete:function(){
/*
     if ($(this).getDataIDs().length > 0) 
     {      
       var first_id = parseInt($(this).getDataIDs()[0]);
       $(this).setSelection(first_id, true);
     }
     */
      is_change  =0;
    },
    
    onSelectCell: function(id) { 
          edit_row_id = id;
         // $('#lgt_tablePager_left').html(jQuery("#lgt_table").jqGrid('getCell',edit_row_id,'abon'));
    },
    
    beforeEditCell : function(rowid, cellname, value, iRow, iCol)
    {
        edit_row_id=rowid;
        //$('#lgt_tablePager_left').html(jQuery("#lgt_table").jqGrid('getCell',rowid,'abon'));
        selICol = iCol;
        selIRow = iRow;
    },    
    
    afterEditCell: function (id,name,val,iRow,iCol)
    { 
        if(name=='mmgg_lgt') 
        {
            jQuery("#"+iRow+"_mmgg_lgt","#lgt_table").mask("99.99.9999"); 
        }
    },    
     afterSaveCell : function(rowid,name,val,iRow,iCol) { 
             
            //jQuery('#lgt_table').setCell(rowid,name,'','mod_column_class');
            jQuery('#lgt_table').setCell(rowid,'sum_val','','mod_column_class');
            jQuery('#lgt_table').setCell(rowid,'id_grp_lgt','','mod_column_class');
            jQuery('#lgt_table').setCell(rowid,'mmgg_lgt','','mod_column_class');
            jQuery('#lgt_table').setCell(rowid,'demand_val','','mod_column_class');
            jQuery('#lgt_table').setCell(rowid,'is_corr','','mod_column_class');
            

            jQuery('#lgt_table').jqGrid("setCell", rowid, "id_grp_lgt", "", "dirty-cell");
            jQuery('#lgt_table').jqGrid("setCell", rowid, "sum_val", "", "dirty-cell");
            jQuery('#lgt_table').jqGrid("setCell", rowid, "mmgg_lgt", "", "dirty-cell");
            jQuery('#lgt_table').jqGrid("setCell", rowid, "is_corr", "", "dirty-cell");
            jQuery('#lgt_table').jqGrid("setCell", rowid, "demand_val", "", "dirty-cell");
            jQuery('#lgt_table').jqGrid("setCell", rowid, "id", "", "dirty-cell");
            
            is_change = 1;
            
        },    
    
    
    //ondblClickRow: function(id){ 
    //     jQuery(this).editGridRow(id,LgtNormEditOptions);  
    //} ,  

    loadError : function(xhr,st,err) {
      jQuery('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);
    }
  
  //  jsonReader : { repeatitems: false }

  }).navGrid('#lgt_tablePager',
         {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ).jqGrid('bindKeys'); 


jQuery('#lgt_table').jqGrid('filterToolbar', {
    beforeSearch:function() {

      if (is_change==1)
      {
        alert('Запишіть змінені дані перед фільтраціэю!') ;
        return true; 
      }
      
    }
 }
);

//------------------------------------------------------------------------------
jQuery("#bt_cancel").click( function() { 

    $(".mod_column_class").removeClass("mod_column_class");
    $(".edited").removeClass("edited");
    $(".dirty-cell").removeClass("dirty-cell");
    $('#lgt_table').trigger('reloadGrid');
    
});
//------------------------------------------------------------------------------
jQuery("#bt_save").click( function() { 

    //var gridData=$("#indic_table").jqGrid('getGridParam','data');
    if ((selICol!=0)&&(selIRow!=0))
    {
       jQuery('#lgt_table').editCell(selIRow,selICol, false); 
    }
    
    
    //var data_obj = $('#indic_table').getChangedCells('all');
    var data_obj = $('#lgt_table').getChangedCells('dirty');
    
    var json_str = JSON.stringify(data_obj);
    //alert(json);
    $.ajaxSetup({type: "POST",   dataType: "json"});
    
    var request = $.ajax({
            url: "abon_en_lgt_dop_edit.php",
            type: "POST",
            data: {
                oper: 'edit', json_data : json_str  
            },
            dataType: "json"
        });

        request.done(function(data ) {
            
            if (data.errcode!==undefined)
                {
                    $('#message_zone').append(data.errstr);  
                    $('#message_zone').append("<br>");                 
                    
                    if(data.errcode==1) 
                    {
                        $(".mod_column_class").removeClass("mod_column_class");
                        $(".edited").removeClass("edited");
                        $(".dirty-cell").removeClass("dirty-cell");
                        $('#lgt_table').trigger('reloadGrid');
                        is_change = 0;
                    }
                }
            
            // window.opener.RefreshIndicExternal(id_pack);
               //window.opener.focus();
               //self.close();            
        });
        request.fail(function(data ) {
            if (data.errcode!==undefined)
                {
                    $('#message_zone').append(data.errstr);  
                    $('#message_zone').append("<br>");                 
                }
                else
                    $('#message_zone').append(data);  
            
        });

   });



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
            $("#lgt_table").jqGrid('setGridWidth',$pane.innerWidth()-15);
            $("#lgt_table").jqGrid('setGridHeight',$pane.innerHeight()-175);
        }
        
	});
        
    outerLayout.resizeAll();
    outerLayout.close('south');     
   // innerLayout.hide('north');        
        
    jQuery(".btn").button();
    jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});
        
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   
   $("#message_zone").dialog({autoOpen: false});
   
   
    jQuery("#pActionBar :input").addClass("ui-widget-content ui-corner-all");
   // jQuery("#fBillEdit :input").addClass("ui-widget-content ui-corner-all");
    

//------------------------------------------------------------------------------
jQuery("#lgt_table").jqGrid('navButtonAdd','#lgt_tablePager',{caption:"Додати",
    id:"btn_lgt_new",
    onClickButton:function(){ 
/*
        var ww = window.open("abon_en_main.php", "paccnt_win", "toolbar=0,width=900,height=600");
        document.paccnt_sel_params.submit();
        ww.focus();
*/

        createAbonGrid();
        
        abon_target_id = -1;

       jQuery("#grid_selabon").css({'left': 200, 'top': 100});
       jQuery("#grid_selabon").toggle( );

    } 
});
//------------------------------------------------------------------------------
jQuery("#lgt_table").jqGrid('navButtonAdd','#lgt_tablePager',{caption:"Видалити",
    id:"btn_lgt_del",
    onClickButton:function(){ 

    if ($("#lgt_table").getDataIDs().length == 0) 
       {return} ;    

    flock = jQuery("#lgt_table").jqGrid('getCell',edit_row_id,'flock');
    if (flock==1) {return} ;    
    $("#dialog-confirm").find("#dialog-text").html('Видалити запис про додаткову пільгу?');
    $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                                      
                                    var request = $.ajax({
                                        url: "abon_en_lgt_dop_edit.php",
                                        type: "POST",
                                        data: {
                                        oper: 'del', 
                                        id : edit_row_id  
                                        },
                                       dataType: "json"
                                       });

                                    request.done(function(data ) {
            
                                        if (data.errcode!==undefined)
                                        {
                                            $('#message_zone').append(data.errstr);  
                                            $('#message_zone').append("<br>");                 
                    
                                            if(data.errcode==-1) 
                                            {
                                                
                                                
			                        if ((selICol!=0)&&(selIRow!=0))
		    				{
			    		    	   jQuery('#lgt_table').editCell(selIRow,selICol, false); 
			    		        }

			                       $("#lgt_table").jqGrid('delRowData',edit_row_id );
                       
                    				var first_id = parseInt($("#lgt_table").getDataIDs()[0]);
			                       $("#lgt_table").setSelection(first_id, true);
			                       selIRow=0;
			                       selICol=0;
                                                
                                                
                                            }
                                        }
            
                                    });
                                    request.fail(function(data ) {
                                        if (data.errcode!==undefined)
                                            {
                                                $('#message_zone').append(data.errstr);  
                                                $('#message_zone').append("<br>");                 
                                            }
                                        else
                                            $('#message_zone').append(data);  
            
                                    });

                                    $( this ).dialog( "close" );                                    
				},
				"Відмінити": function() {
					$( this ).dialog( "close" );
				}
			}
		});
    
      jQuery("#dialog-confirm").dialog('open');   
    

    } 
});

//---------------------------------------------------------------------------
 jQuery("#lgt_table").jqGrid('navButtonAdd','#lgt_tablePager',{caption:"Друкувати список",
    onClickButton:function(){ 

        var postData = jQuery("#lgt_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       $('#freps_params').find("#fdt_b").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#fperiod_str").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       
       $('#freps_params').find("#foper").attr('value', "doplgt_list");
       
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

//------------------------------------------------------------------------------
    jQuery("#lgt_table").jqGrid('navButtonAdd','#lgt_tablePager',{caption:"Розрах. всі",
    id:"btn_recalc_all",
    onClickButton:function(){ 
        
    jQuery("#dialog-confirm").find("#dialog-text").html('Виконати розрахунок по всім абонентам, що мають додаткові пільги?');
           
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Перерахунок',
                buttons: {
                    "Формувати": function() {

                        var request = $.ajax({
                            url: "bill_calc_doplgt_edit.php",
                            type: "POST",
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
                                    if (data.id =1 )
                                    {
                                        alert("Розрахунок виконано!");
                                    }
                                    else
                                    {
                                        alert("Помилка при формуванні рахунків!");
                                    }
                
                                }
                                else
                                {
                                    jQuery("#message_zone").dialog('open');                                    
                                }
                            }
                        });

                        request.fail(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");
                                $('#message_zone').dialog('open');
                            }
                            else
                            {
                                $('#message_zone').append(data);  
                                $('#message_zone').dialog('open');                                
                            }
        
                        });
                      
                        $( this ).dialog( "close" );
                    },
                    "Відмінити": function() {
                        $( this ).dialog( "close" );
                    }
                }
            });
    
            jQuery("#dialog-confirm").dialog('open');
        
    }
});    


    
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       
       $('#lgt_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
       is_change = 0;
       
   });

if (r_edit!=3)
{
  $('#btn_lgt_del').addClass('ui-state-disabled');
  $('#btn_lgt_new').addClass('ui-state-disabled');
  $('#bt_save').prop('disabled', true);
}
if (r_bill!=3)
{
  $('#btn_recalc_all').addClass('ui-state-disabled');
}


$("#show_peoples").click( function() {
     jQuery("#lgt_table").jqGrid('showCol',["user_name"]);
     jQuery("#lgt_table").jqGrid('showCol',["dt"]);
});

/*
$("#fBillEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_billedit").dialog('close');                           
});

*/
//------------------------------------------
   /*
$("#pActionBar").find("#bt_calc").click( function(){ 

    jQuery("#dialog-confirm").find("#dialog-text").html('Вибрати абонентів з пільгами?');
    
    $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Вибір',
        buttons: {
            "Вибрати": function() {
                                        
                var cur_mmgg = jQuery("#fmmgg").val();


                var request = $.ajax({
                    url: "abon_en_lgt_dop_init_edit.php",
                    type: "POST",
                    data: {
                        mmgg : cur_mmgg
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
                            if (data.id =1 )
                            {
                                //alert("Рахунки зформовано!");
                                $('#lgt_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
                            }
                            else
                            {
                                alert("Помилка !");                        
                            }
                
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
                                        
                      
                $( this ).dialog( "close" );
            },
            "Відмінити": function() {
                $( this ).dialog( "close" );
            }
        }
    });
    
    jQuery("#dialog-confirm").dialog('open');


});
*/
});


function SelectPaccnt(id, book, code, name, addr) {

    $.ajaxSetup({type: "POST",   dataType: "json"});
    
    var newRecord;  
    //var json_str = JSON.stringify(newRecord);
    var cur_mmgg = jQuery("#fmmgg").val();
    var request = $.ajax({
            url: "abon_en_lgt_dop_edit.php",
            type: "POST",
            data: {
                oper: 'add', mmgg : cur_mmgg, id_paccnt : id  
            },
            dataType: "json"
        });

        request.done(function(data ) {
            
            if (data.errcode!==undefined)
                {
                    $('#message_zone').append(data.errstr);  
                    $('#message_zone').append("<br>");                 
                    
                    if(data.errcode==-1) 
                    {
                       newRecord = {'id':data.id,'id_paccnt':id, 'book':book,'code':code, 'abon':name, 'addr':addr};
                       
                        if ((selICol!=0)&&(selIRow!=0))
		        {
		           jQuery('#lgt_table').editCell(selIRow,selICol, false); 
		        }

                       
                       $("#lgt_table").jqGrid('addRowData',data.id,newRecord, 'first' );
                       //var first_id = parseInt($("#lgt_table").getDataIDs()[0]);
                       $("#lgt_table").setSelection(data.id, true);
                       selIRow=1;
                       selICol=0;

                    }                   
                }
            
        });
        request.fail(function(data ) {
            if (data.errcode!==undefined)
                {
                    $('#message_zone').append(data.errstr);  
                    $('#message_zone').append("<br>");                 
                }
                else
                    $('#message_zone').append(data);  
            
        });

} 