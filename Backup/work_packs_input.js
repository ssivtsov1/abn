var cur_indic_id=0;
var validator = null;

var selICol=0; //iCol of selected cell
var selIRow=0; //iRow of selected cell

$(function(){ 
/*
   setTimeout(function(){
             $('#accnt_table').trigger('reloadGrid');              
    },300);  
*/
  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  $(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

  $(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  $(".dtpicker").mask("99.99.9999");
  
  date_mmgg = Date.parse( mmgg, "dd.MM.yyyy");
  date_mmgg_next = Date.parse( mmgg_next, "dd.MM.yyyy");
  
  $("#fdt_indic").datepicker( "setDate" , dt_work ); 

//==============================================================================

  $('#indic_table').jqGrid({
    url:'work_packs_input_data.php',
    datatype: 'json',
    mtype: 'POST',
    height:200,
    width:400,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true,sortable:false},
    {name:'id_pack', index:'id_pack', width:40, editable: false, align:'center',hidden:true,sortable:false},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_meter', index:'id_meter', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_type_meter', index:'id_type_meter', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_p_indic', index:'id_p_indic', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    
    {label:'#',name:'status', index:'status', width:20, editable: false, align:'left',edittype:'text',sortable:false},
    {label:'Книга',name:'book', index:'book', width:40, editable: false, align:'left',edittype:'text',sortable:false},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: false, align:'left',edittype:'text',sortable:false},                
    {label:'Адреса',name:'address', index:'address', width:200, editable: false, align:'left',edittype:'text',sortable:false,hidden:false},                    
    {label:'Абонент',name:'abon', index:'abon', width:100, editable: false, align:'left',edittype:'text',sortable:false,hidden:false},
    {label:'№ ліч.',name:'num_meter', index:'num_meter', width:80, editable: false, align:'left',edittype:'text',sortable:false},            
    {label:'Тип ліч.',name:'type_meter', index:'type_meter', width:80, editable: false, align:'left',edittype:'text',sortable:false,hidden:true},                
    {label:'Розр.',name:'carry', index:'carry', width:30, editable: false, align:'left',edittype:'text',sortable:false},                    
    {label:'Зона',name:'id_zone', index:'id_zone', width:40, editable: false, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text',sortable:false},                       
    {label:'П.спож',name:'p_demand', index:'p_demand', width:60, editable: false, align:'right',hidden:true,
                            edittype:'text',sortable:false,formatter:'integer'},           

    {label:'П.пок.',name:'p_indic', index:'p_indic', width:80, editable: false, align:'right',hidden:false,
                            edittype:'text',sortable:false,formatter:'integer'},           
    {label:'Дата попер.',name:'dt_p_indic', index:'dt_p_indic', width:80, editable: false, 
                        align:'left',edittype:'text',formatter:'date',sortable:false,hidden:false},
 

    {label:'Поточні пок.',name:'indic', index:'indic', width:80, editable: true, align:'right',hidden:false,
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
                            setTimeout("$('#indic_table').editCell(" + selIRow + " + 1, " + selICol + ", true);", 100);
                        }
                    }
                } 
                ]
            }                            
    },
    {label:'Дата',name:'dt_indic', index:'dt_indic', width:80, editable: true, 
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
                            setTimeout("$('#indic_table').editCell(" + selIRow + " + 1, " + selICol + ", true);", 100);
                        }
                    }
                } 
                ]
            }                        
      },

    {label:'Споживання',name:'demand', index:'demand', width:70, editable: false, align:'right',hidden:false,
                            edittype:'text',sortable:false},           
                        
     
   {label:'Дійсні пок.',name:'indic_real', index:'indic_real', width:70, editable: true, align:'right',hidden:false,
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
                            setTimeout("$('#indic_table').editCell(" + selIRow + " + 1, " + selICol + ", true);", 100);
                        }
                        
                    }
                } 
                ]
            }                            
    },
    {label:'№ акта',name:'act_num', index:'act_num', width:50, editable: true, align:'right',hidden:false,
            edittype:'text',
            sortable:false,
            classes: 'editable_column_class',
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
                        if(key == 9)   // tab
                        {
                            setTimeout("$('#indic_table').editCell(" + selIRow + " + 1, 18, true);", 100);
                        }
                        else if (key == 13)//enter
                        {
                            setTimeout("$('#indic_table').editCell(" + selIRow + " + 1, " + selICol + ", true);", 100);
                        }

                    }
                } 
                ]
            }                            
    }
    ],
    pager: '#indic_tablePager',
    autowidth: true,
    //shrinkToFit : false,
    rowNum:5000,
    //rowList:[50,100,200],
    sortname: 'address',
    sortorder: 'asc',
    viewrecords: true,
    //gridview: true,
    caption: 'Показники',
    //hiddengrid: false,
    forceFit : true,
    hidegrid: false,    
    postData:{'p_id':id_pack},
    cellEdit: true, 
    cellsubmit: 'clientArray',
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    
    gridComplete:function(){
/*
     if ($(this).getDataIDs().length > 0) 
     {      
       var first_id = parseInt($(this).getDataIDs()[0]);
       $(this).setSelection(first_id, true);
     }
     */
    },
    
    rowattr: function (rd) {
        if ((rd.status == 'Н')||(rd.status == 'В'))
            return {"style": "color:gray !important;"};
        
        if (rd.status == '#')
            return {"style": "color:Brown !important;"};
        
    },    
    onSelectCell: function(id) { 
          cur_indic_id = id;
          $('#indic_tablePager_left').html($("#indic_table").jqGrid('getCell',cur_indic_id,'abon'));
    },
    
    beforeEditCell : function(rowid, cellname, value, iRow, iCol)
    {
        cur_indic_id=rowid;
        $('#indic_tablePager_left').html($("#indic_table").jqGrid('getCell',rowid,'abon'));
        selICol = iCol;
        selIRow = iRow;
    },    
    
    afterEditCell: function (id,name,val,iRow,iCol)
    { 
        if(name=='dt_indic') 
        {
            $("#"+iRow+"_dt_indic","#indic_table").mask("99.99.9999"); 
        }
    },    
     afterSaveCell : function(rowid,name,val,iRow,iCol) { 
         
            var tr = $('#indic_table')[0].rows.namedItem(rowid); 
            var td = tr.cells[iCol];

            if(name == 'indic') { 
                var global_dt = $("#fdt_indic").val();
                var p_ind = parseFloat($("#indic_table").jqGrid('getCell',rowid,iCol-2));
                var k_tr = 1;
                var dt_ind = $("#indic_table").jqGrid('getCell',rowid,iCol+1);
                var dt_prev_ind = $("#indic_table").jqGrid('getCell',rowid,iCol-1);
                var carry = parseFloat($("#indic_table").jqGrid('getCell',rowid,iCol-5));
                var ind = parseFloat(val);
                var length = dt_ind.length;
                var dem=0;
                if (length<6)
                    {
                      var date_ind = Date.parse( global_dt, "dd.MM.yyyy");  
                      if((date_ind< date_mmgg)||(date_ind> date_mmgg_next))
                      {
                        jQuery("#dialog-confirm").find("#dialog-text").html('Дата показників не відповідає поточному місяцю!');
                         $("#dialog-confirm").dialog({
                          resizable: false,
                          height:140,
                          modal: true,
                          autoOpen: false,
                          title:'Увага',
                          buttons: {
                              "Ок": function() {
                                  $( this ).dialog( "close" );
                              }
                          }
                        });
    
                        jQuery("#dialog-confirm").dialog('open');                            
                      }
                      else
                      {
                        $("#indic_table").jqGrid('setRowData',rowid,{dt_indic: global_dt});
                        dt_ind = global_dt;
                      }
                    };
                    
                var id_operation = $("#indic_table").jqGrid('getCell',rowid,iCol+3);  
                var id_operation_def = $("#fid_operation_def").val();
                if (id_operation_def=='null') id_operation_def = 1;
                
                if (id_operation=='null')
                   $("#indic_table").jqGrid('setRowData',rowid,{id_operation: id_operation_def});    
                
                $(td).removeClass("err_column_class");                    
                $(td).removeClass("mod_column_class");                    
                
                if (val.length==0)    
                {
                    $("#indic_table").jqGrid('setRowData',rowid,{
                        demand: ''
                    });    
                }
                else
                {
                
                    if (Math.round(ind).toString().length>carry)    
                    {
                        jQuery("#dialog-confirm").find("#dialog-text").html('Показники перевищують розрядність лічильника!');
                        $("#dialog-confirm").dialog({
                            resizable: false,
                            height:140,
                            modal: true,
                            autoOpen: false,
                            title:'Помилкові показники',
                            buttons: {
                                "Ок": function() {
                                    $( this ).dialog( "close" );
                                }
                            }
                        });
    
                        jQuery("#dialog-confirm").dialog('open');   
                        
                        
                        $('#indic_table').setCell(rowid,name,'','err_column_class');  
                        
                        $("#indic_table").jqGrid('setRowData',rowid,{
                            demand: ''
                        });    
                        
                    }
                    else
                    {
                        var dem=0; 
                        if (ind >= p_ind)
                        {
                            dem = (ind - p_ind)* k_tr;       
                        }
                        else
                        { 
                            var max_val = parseFloat(str_pad('1',carry+1,'0'));
                            dem = (ind + (max_val - p_ind))* k_tr;   
                          
                        }

                        if (dem > 1000)
                        {
                            //dem = 0;
                            $('#indic_table').setCell(rowid,name,'','err_column_class');  
                            
                            jQuery("#dialog-confirm").find("#dialog-text").html('Споживання перевищує 1000 кВтч!');
                            $("#dialog-confirm").dialog({
                                resizable: false,
                                height:140,
                                modal: true,
                                autoOpen: false,
                                title:'Підозрілі показники',
                                buttons: {
                                    "Ок": function() {
                                        $( this ).dialog( "close" );
                                    }
                                }
                            });
    
                            jQuery("#dialog-confirm").dialog('open');   
                            
                        }

                        $("#indic_table").jqGrid('setRowData',rowid,{
                            demand: dem
                        });    
                    
                        $('#indic_table').setCell(rowid,name,'','mod_column_class');
                    }
                    
                    if (dt_prev_ind == dt_ind)
                    {
                        $('#indic_table').setCell(rowid,'dt_indic','','err_column_class');  
                        
                        jQuery("#dialog-confirm").find("#dialog-text").html('Вже є показники на цю дату!');
                        $("#dialog-confirm").dialog({
                            resizable: false,
                            height:140,
                            modal: true,
                            autoOpen: false,
                            title:'Увага',
                            buttons: {
                                "Ок": function() {
                                    $( this ).dialog( "close" );
                                }
                            }
                        });
    
                        jQuery("#dialog-confirm").dialog('open');   
                        
                    }
                }   
                    
             };
             
             if(name == 'dt_indic') {
                 
                $(td).removeClass("err_column_class");                                     
                
                var dt_ind = $("#indic_table").jqGrid('getCell',rowid,iCol);
                var dt_prev_ind = $("#indic_table").jqGrid('getCell',rowid,iCol-2);

                var date_ind = Date.parse( dt_ind, "dd.MM.yyyy");
                var date_prev_ind = Date.parse( dt_prev_ind, "dd.MM.yyyy");


                if ((dt_prev_ind == dt_ind)&&(dt_ind.trim()!=''))
                {
                    $('#indic_table').setCell(rowid,name,'','err_column_class');  
                    
                    jQuery("#dialog-confirm").find("#dialog-text").html('Вже є показники на цю дату!');
                    $("#dialog-confirm").dialog({
                        resizable: false,
                        height:140,
                        modal: true,
                        autoOpen: false,
                        title:'Увага',
                        buttons: {
                            "Ок": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
    
                    jQuery("#dialog-confirm").dialog('open');   
                   
                }
                if ((date_prev_ind > date_ind)&&(dt_ind.trim()!=''))
                {
                    $('#indic_table').setCell(rowid,name,'','err_column_class');  
                    
                    jQuery("#dialog-confirm").find("#dialog-text").html('Поточна дата менша за попередню!');
                    $("#dialog-confirm").dialog({
                        resizable: false,
                        height:140,
                        modal: true,
                        autoOpen: false,
                        title:'Увага',
                        buttons: {
                            "Ок": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
    
                    jQuery("#dialog-confirm").dialog('open');   
                    
                }

                if (( date_ind< date_mmgg )&&(dt_ind.trim()!=''))
                {
                    $('#indic_table').setCell(rowid,name,'','err_column_class');  
                    
                    jQuery("#dialog-confirm").find("#dialog-text").html('Поточна дата менша за початок поточного місяця!');
                    $("#dialog-confirm").dialog({
                        resizable: false,
                        height:140,
                        modal: true,
                        autoOpen: false,
                        title:'Увага',
                        buttons: {
                            "Ок": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
    
                    jQuery("#dialog-confirm").dialog('open');   
                    
                }


                if (( date_ind> date_mmgg_next )&&(dt_ind.trim()!=''))
                {
                    $('#indic_table').setCell(rowid,name,'','err_column_class');  
                    
                    jQuery("#dialog-confirm").find("#dialog-text").html('Поточна дата більша за кінець поточного місяця!');
                    $("#dialog-confirm").dialog({
                        resizable: false,
                        height:140,
                        modal: true,
                        autoOpen: false,
                        title:'Увага',
                        buttons: {
                            "Ок": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
    
                    jQuery("#dialog-confirm").dialog('open');   
                    
                }
             }

            //$('#indic_table').setCell(rowid,name,'','mod_column_class');
            $('#indic_table').setCell(rowid,'dt_indic','','mod_column_class');
            $('#indic_table').setCell(rowid,'demand','','mod_column_class');
            $('#indic_table').setCell(rowid,'indic_real','','mod_column_class');
            
            $('#indic_table').jqGrid("setCell", rowid, "indic", "", "dirty-cell");
            $('#indic_table').jqGrid("setCell", rowid, "dt_indic", "", "dirty-cell");
            $('#indic_table').jqGrid("setCell", rowid, "id", "", "dirty-cell");
            $('#indic_table').jqGrid("setCell", rowid, "id_pack", "", "dirty-cell");
            $('#indic_table').jqGrid("setCell", rowid, "act_num", "", "dirty-cell");
            $('#indic_table').jqGrid("setCell", rowid, "indic_real", "", "dirty-cell");
        },    
    
    
    //ondblClickRow: function(id){ 
    //     $(this).editGridRow(id,LgtNormEditOptions);  
    //} ,  

    loadError : function(xhr,st,err) {
      $('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);
    }
  
  //  jsonReader : { repeatitems: false }

  }).jqGrid('bindKeys'); 
  //.navGrid('#indic_tablePager',
   //      {edit:false,add:false,del:false},
   //     {}, 
   //     {}, 
    //    {}, 
    //    {} 
    //    ).jqGrid('bindKeys'); 

//==============================================================================

//$("#headers_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
//      $(this).editGridRow(id,{width:300,height:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});}} );


//$("#indic_table").jqGrid('bindKeys');



$("#message_zone").dialog({autoOpen: false});

$("#debug_ls1").click( function() {$("#message_zone").dialog('open');});
$("#debug_ls2").click( function() {$("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {$("#message_zone").html('');});

$(".btn").button();
$(".btnSel").button({icons: {primary:'ui-icon-folder-open'}});

$("#pheader :input").addClass("ui-widget-content ui-corner-all");


//------------------------------------------------------------------------------
$.ajaxSetup({type: "POST",      dataType: "json"});
 outerLayout = $("body").layout({
		name:	"outer" 
	//,	north__paneSelector:	"#pmain_header"
	//,	north__closable:	false
	//,	north__resizable:	false
        //,	north__size:		40
	//,	north__spacing_open:	0
	,	south__paneSelector:	"#pmain_footer"
	,	south__closable:	true
	,	south__resizable:	false
        ,	south__size:		40
	,	south__spacing_open:	5
        ,	south__spacing_closed:	3
	,	center__paneSelector:	"#pmain_center"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true

	});

 innerLayout = $("#pmain_center").layout({
		name:	"inner" 
	,	north__paneSelector:	"#pheader"
	,	north__closable:	false
	,	north__resizable:	false
        ,	north__spacing_open:	0
        ,	north__size:		128
	,	south__paneSelector:	"#pBottom"
	,	south__closable:	false
	,	south__resizable:	false
        ,	south__size:		35
        ,	south__spacing_open:	0
        ,	center__paneSelector:	"#pIndic_table"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       north__onresize:	function (pane, _pane, state, options) 
        {
            //$("#headers_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            //$("#headers_table").jqGrid('setGridHeight',_pane.innerHeight()-85);
        }
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            $("#indic_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            $("#indic_table").jqGrid('setGridHeight',_pane.innerHeight()-85);
        }

	});

        outerLayout.close('south');     
        outerLayout.resizeAll();
        
        
$("#bt_close").click( function() 
{
  var data_obj = $('#indic_table').getChangedCells('dirty');

  if (data_obj.length>0) 
  {
        jQuery("#dialog-confirm").find("#dialog-text").html('Є незбережені показники!');
        $("#dialog-confirm").dialog({
                    resizable: false,
                    //height:170,
                    width:330,
                    modal: true,
                    autoOpen: false,
                    title:'Закрити вікно?',
                    buttons: {
                        "Продовжити роботу": function() {
                            $( this ).dialog( "close" );
                        },
                	"Закрити вікно": function() {
                            self.close();    
        		}
                    }
                });
    
                jQuery("#dialog-confirm").dialog('open');   
      
  }
  else
  {
    self.close();          
  }
  
}); 
//----------------------------------------------------------------
$("#bt_save").click( function() { 

    //var gridData=$("#indic_table").jqGrid('getGridParam','data');
    if ((selICol!=0)&&(selIRow!=0))
    {
       $('#indic_table').editCell(selIRow,selICol, false); 
    }
    
    
    //var data_obj = $('#indic_table').getChangedCells('all');
    var data_obj = $('#indic_table').getChangedCells('dirty');
    
    if (data_obj.length==0) return;
    
    $('#indic_table').addClass('ui-state-disabled');
    
    var json_str = JSON.stringify(data_obj);
    //alert(json);
    $.ajaxSetup({type: "POST", dataType: "json"});
    
    var request = $.ajax({
            url: "work_packs_input_edit.php",
            type: "POST",
            data: {
                id_pack : id_pack,
                json_data : json_str  
            },
            dataType: "json"
        });

        request.done(function(data ) {
            if (data.errcode!==undefined)
                {
                    $('#message_zone').append(data.errstr);  
                    $('#message_zone').append("<br>");   
                    
                    if (data.errcode!==2)
                    {
                        $('#indic_table').removeClass('ui-state-disabled');
                        
                        $(".mod_column_class").removeClass("mod_column_class");
                        $(".edited").removeClass("edited");
                        $(".dirty-cell").removeClass("dirty-cell");
            
                         window.opener.RefreshIndicExternal(id_pack);
                            
                    }
                    else
                    {
                        $('#message_zone').dialog('open');
                    }
                }
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
        
            $('#message_zone').dialog('open');
        });
 
   });
   
   
window.onbeforeunload = function (evt) {
    
    if (typeof evt == "undefined") {
        evt = window.event;
    }
    
    var data_obj = $('#indic_table').getChangedCells('dirty');

    if (data_obj.length>0) 
    {
        var message = 'Є незбережені показники!';
        if (evt) {
            evt.returnValue = message;
        }
        return message;

    }
    else
    {
        return null;          
    }
}
/*
$("#bt_reset").click( function() { 
    $('#indic_table').trigger('reloadGrid');              
    $(".mod_column_class").removeClass("mod_column_class");
   });
*/
//-------------------поиск --------------
 $('#find_book').keydown( function(e){

        var keycode = (e.keyCode ? e.keyCode : e.which);
        if (keycode == '13') {
            $("#find_code").focus();
        }    
 });

 $('#find_code').keydown( function(e){

        var keycode = (e.keyCode ? e.keyCode : e.which);
        if (keycode == '13') {

            var allRowsInGrid = $('#indic_table').jqGrid('getRowData');
            for (i = 0; i < allRowsInGrid.length; i++) {
                pid = allRowsInGrid[i].id;
                pbook = allRowsInGrid[i].book;
                pcode = allRowsInGrid[i].code;
    
                if ((pbook==$('#find_book').val())&&(pcode==$('#find_code').val()))
                {
                    $('#indic_table').setSelection(pid, true);            
                    //$('#indic_table').editCell(" + selIRow + " + 1, " + selICol + ", true);
                    //$('#indic_table').editRow(pid, true); 
                }
            }

        }    
    });

}); 
