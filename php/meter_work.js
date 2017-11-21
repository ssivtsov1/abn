var cur_meter_zone_id=0;
var submit_form;
var selICol=0;
var selIRow=0;
var indic_array=[];
var form_edit_lock=0;
var today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1; //January is 0!
var yyyy = today.getFullYear();
if(dd<10){dd='0'+dd};
if(mm<10){mm='0'+mm};
var work_date = dd+'.'+mm+'.'+yyyy;

var form_options = { 
    dataType:"json",
    beforeSubmit: WorkBeforeSubmit, 
    success: WorkSubmitResponse  
  };


jQuery(function(){ 

   

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
   //     ,	center__onresize:   $.layout.callbacks.resizeTabLayout        
	});
    
    $( "#pwork_center" ).tabs({
      //  show: $.layout.callbacks.resizeTabLayout        
    });

    innerLayout = $("#pmain_content").layout({
		name:			"inner" 
	,	north__paneSelector:	"#pwork_header"
	,	north__closable:	false
	,	north__resizable:	false
        ,	north__spacing_open:	0
        ,	north__size:		180
        ,	center__paneSelector:	"#pwork_center"
	,	autoBindCustomButtons:	true
//        ,	center__onresize:   $.layout.callbacks.resizeTabLayout
	,       center__onresize:	function (pane, $pane, state, options) 
        {
            //jQuery("#paccnt_meters_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            //jQuery("#paccnt_meters_table").jqGrid('setGridWidth',jQuery("#paccnt_meters_list").innerWidth());
            //jQuery("#paccnt_lgt_table").jqGrid('setGridWidth',jQuery("#paccnt_lgt_list").innerWidth());            
            
           // jQuery("#paccnt_meters_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
           // jQuery("#paccnt_lgt_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
           // jQuery("#paccnt_dogovor_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
           // jQuery("#paccnt_plomb_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
           // jQuery("#paccnt_notlive_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            
            //jQuery("#client_table").jqGrid('setGridHeight',$pane.innerHeight()-142);

        }
        
	});
     
    meterLayout = $("#pMeterParam").layout({
		name:			"meter_param" 
        //,       initPanes:		false
        //,       resizeWithWindow:	false
	,	east__paneSelector:	"#pMeterParam_right"
	,	east__closable:	        true
	,	east__resizable:	true
        ,	east__size:		250
        ,	center__paneSelector:	"#pMeterParam_left"
	,	autoBindCustomButtons:	true
        //,	south__paneSelector:	"#pMeterParam_buttons"
	//,	south__closable:	false
	//,	south__resizable:	false
        //,	south__spacing_open:	0
        //,	south__size:            40
	,       center__onresize:	function (pane, $pane, state, options) 
        {
            //jQuery("#client_table").jqGrid('setGridWidth',$pane.innerWidth()-9);
            //jQuery("#client_table").jqGrid('setGridHeight',$pane.innerHeight()-142);

        }
    });

   innerLayout.resizeAll(); 
   outerLayout.close('south');             
 //  meterLayout.resizeAll();         

    if (mode ==1)
    {
     $("#fWorkEdit").find("#bt_add").hide();
     $("#fWorkEdit").find("#bt_edit").show();   
    }
    else
    {
     $("#fWorkEdit").find("#bt_add").show();
     $("#fWorkEdit").find("#bt_edit").hide();   
     
     $("#pMeterParam_comp").hide();
     
     $("#fWorkEdit").find("#fdt_work").focus();
     //$("#fWorkEdit").find("#fdt_work").attr('value',date_work );  
     
    }
    
    jQuery(".btn").button();
    jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});
    jQuery("#fWorkEdit :input").addClass("ui-widget-content ui-corner-all");
    jQuery("#dialog-newmeterzone :input").addClass("ui-widget-content ui-corner-all");
    
    
    $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
    jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true
                        ,onClose: function ()  {this.focus();}    
                });

    jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
    jQuery(".dtpicker").mask("99.99.9999");
    
    date_mmgg = Date.parse( mmgg, "dd.MM.yyyy");
    date_mmgg_next = Date.parse( mmgg, "dd.MM.yyyy");
    date_mmgg_next.add({days: -1, months: 1});    
    
    work_date_prev = 0;
    
    jQuery("#fdt_work").datepicker( "option", "onSelect", function(date) {
            work_date = date;
            
            dt_w = Date.parse( work_date, "dd.MM.yyyy");
            dt_min  = Date.parse( meter_min_date, "dd.MM.yyyy");
            
            if(((dt_w< date_mmgg)||(dt_w> date_mmgg_next))&&(work_date!=work_date_prev))
            {
                jQuery("#dialog-confirm").find("#dialog-text").html('Дата роботи не належить поточному місяцю!');
    
                $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Попередження',
			buttons: {
				"Ок": function() {
                                        work_date_prev=work_date;
					$( this ).dialog( "close" );
				}
			}
		});
                jQuery("#dialog-confirm").dialog('open');          
                
            }

            if ((mode==1)&&((idk_work==3)||(idk_work==2)))
                return;

            if ((dt_w <dt_min)&&(work_date!=work_date_prev))
            {
                $("#fWorkEdit").find("#bt_add").prop('disabled', true);  
                $("#fWorkEdit").find("#bt_edit").prop('disabled', true);  
                jQuery("#dialog-confirm").find("#dialog-text").html('Дата роботи не повинна бути менше ніж дата встановлення лічильника!');
    
                $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Попередження',
			buttons: {
				"Ок": function() {
                                        work_date_prev=work_date; 
					$( this ).dialog( "close" );
				}
			}
		});
    
                jQuery("#dialog-confirm").dialog('open');          
        
            }
            else
            {
                $("#fWorkEdit").find("#bt_add").prop('disabled', false);          
                $("#fWorkEdit").find("#bt_edit").prop('disabled', false);          
            }
            
            var rows = $('#indic_table').getDataIDs();
            indic_array = [];    
            for(i=0;i<rows.length;i++) 
            {
                row=$('#indic_table').getRowData(rows[i]);
       
                if (row.indic!='')
                {
                    indic_array[rows[i]]=row.indic;
                }
            }
            
            jQuery('#indic_table').jqGrid('setGridParam',{postData:{'w_id':id_work, 'p_id':id_paccnt,'m_id':id_meter,'mode':mode, 'idk_work':idk_work,'w_date': work_date}}).trigger('reloadGrid');           
         } );
    
   
   
   
   
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   
   $("#message_zone").dialog({autoOpen: false});
//-----------------------------------------------------

   jQuery("#btCntrlSel").click( function() { 

     createPersonGrid($("#fWorkEdit").find("#fid_position").val());
     person_target_id=     $("#fWorkEdit").find("#fid_position");
     person_target_name =  $("#fWorkEdit").find("#fposition");
     person_target_prof = 0;
    
     jQuery("#grid_selperson").css({'left': $("#fWorkEdit").find("#fposition").offset().left+1, 'top': $("#fWorkEdit").find("#fposition").offset().top+20});
     jQuery("#grid_selperson").toggle( );

/*
    $("#fcntrl_sel_params_id_cntrl").attr('value', $("#fWorkEdit").find("#fid_cntrl").val() );    
     
     var www = window.open("staff_list.php", "cntrl_win", "toolbar=0,width=900,height=600");
     document.cntrl_sel_params.submit();
     www.focus();
*/
   });

 //---------------------------------------------
 /*$("#fdt_work").bind({
   'input': function() {
    
    //work_date = $("#fWorkEdit").("#fdt_work").val();  
    work_date = $("#fWorkEdit").("#fdt_work").datepicker( "getDate" );
    jQuery('#indic_table').jqGrid('setGridParam',{postData:{'w_id':id_work, 'p_id':id_paccnt,'m_id':id_meter,'mode':mode, 'w_date': work_date}}).trigger('reloadGrid');           
   }
  });
*/
$('#fdt_work').change(function() { 
    //work_date = $("#fdt_work").datepicker( "getDate" );
    
    work_date =$("#fWorkEdit").find("#fdt_work").val();  


    dt_w = Date.parse( work_date, "dd.MM.yyyy");
    dt_min  = Date.parse( meter_min_date, "dd.MM.yyyy");
    
    if(((dt_w< date_mmgg)||(dt_w> date_mmgg_next))&&(work_date!=work_date_prev))
    {
        jQuery("#dialog-confirm").find("#dialog-text").html('Дата роботи не належить поточному місяцю!');
    
        $("#dialog-confirm").dialog({
            resizable: false,
            height:140,
            modal: true,
            autoOpen: false,
            title:'Попередження',
            buttons: {
                "Ок": function() {
                    work_date_prev=work_date;
                    $( this ).dialog( "close" );
                }
            }
        });
        jQuery("#dialog-confirm").dialog('open');          
                
    }
    
    
    if ((mode==1)&&((idk_work==3)||(idk_work==2)))
          return;
    
    if ((dt_w <dt_min)&&(work_date!=work_date_prev))
    {
      $("#fWorkEdit").find("#bt_add").prop('disabled', true);  
      $("#fWorkEdit").find("#bt_edit").prop('disabled', true);  
      jQuery("#dialog-confirm").find("#dialog-text").html('Дата роботи не повинна бути менше ніж дата встановлення лічильника!');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Попередження',
			buttons: {
				"Ок": function() {
                                        work_date_prev=work_date;
					$( this ).dialog( "close" );
				}
			}
		});
    
        jQuery("#dialog-confirm").dialog('open');          
        
    }
    else
    {
      $("#fWorkEdit").find("#bt_add").prop('disabled', false);          
      $("#fWorkEdit").find("#bt_edit").prop('disabled', false);          
    }

    var rows = $('#indic_table').getDataIDs();
    indic_array = [];    
    for(i=0;i<rows.length;i++) 
    {
       row=$('#indic_table').getRowData(rows[i]);
       
       if (row.indic!='')
       {
           indic_array[rows[i]]=row.indic;
       }
    }

    jQuery('#indic_table').jqGrid('setGridParam',{postData:{'w_id':id_work, 'p_id':id_paccnt,'m_id':id_meter,'mode':mode, 'idk_work':idk_work, 'w_date': work_date}}).trigger('reloadGrid');           
    
});


$.ajaxSetup({type: "POST",   dataType: "json"});


var fWorkEdit_ajaxForm = $("#fWorkEdit").ajaxForm(form_options); 

// опции валидатора общей формы
var form_valid_options = { 
                errorPlacement: function(error, element) {
				error.appendTo( element.parent("label"));
                },
                focusInvalid: false,
                onkeyup: false,
                focusCleanup : true,
                onfocusout: false,
		rules: {
                        idk_work:"required",
                        //dt_work: "required",
                        dt_work: {required_date:true},
                        
			type_meter: "required",
			num_meter: "required",
			carry:{required: true,
                        number:true
                        },
                        dt_start: "required",
                        coef_comp:{required: true,
                                number:true
                        },
                        power: {number:true}
                        
		},
		messages: {
                        idk_work:"Вкажіть тип роботи",
                        //dt_work: "Вкажіть дату",
                        
			type_meter: "Виберіть тип лічильника!",
			num_meter: "Вкажіть номер лічильника",
			carry:{required: "Вкажіть розрядність лічильника",
                        number:"Повинно бути число!"
                        },
                        dt_start: "Вкажіть дату встановлення лічильника",
			coef_comp:{required: "Вкажіть к.тр",
                        number:"Повинно бути число!"
                        }, 
                        power: {number:"Повинно бути число!"}
                        
		}
};

$.validator.addMethod("required_date", function (value, element) {
	        return value.replace(/\D+/g, '').length > 1;
	    },   "Вкажіть дату");
            
validator = $("#fWorkEdit").validate(form_valid_options);

$.ajaxSetup({type: "POST",   dataType: "json"});

//if (mode == 0)
{
 var request = $.ajax({
     url: "meter_work_data.php",
     type: "POST",
     data: {id_work : id_work,
            idk_work : idk_work,
            id_paccnt : id_paccnt,
            id_meter : id_meter,
            id_session :id_session,
            mode: mode
     },
     dataType: "json"});

 request.done(function(data ) {
     LoadWorkData(data);
 });
 request.fail(function(data ) {alert("error");});

 //$("#fWorkEdit").find("#bt_add").hide();
 //$("#fWorkEdit").find("#bt_edit").show();   

}
//else
//{
 //$("#fWorkEdit").find("#bt_add").show();
 //$("#fWorkEdit").find("#bt_edit").hide();   
 //$("#fWorkEdit").find("#bt_delabon").hide();   
 //$("#fWorkEdit").find("#bt_showtree").hide();   
// $("#pwork_center").hide();

//}


$("#fWorkEdit").find("#bt_reset").click( function() 
{
      self.close();    
});


//---------------------------------------------------

  jQuery('#indic_table').jqGrid({
    url:'meter_work_indic_data.php',
   // datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:100,
    width:400,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true,sortable:false},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_meter', index:'id_meter', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_type_meter', index:'id_type_meter', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_p_indic', index:'id_p_indic', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_metzone', index:'id_metzone', width:40, editable: false, align:'center',hidden:true,sortable:false},    

    {label:'№ ліч.',name:'num_meter', index:'num_meter', width:80, editable: false, align:'left',edittype:'text',sortable:false},            
    {label:'Тип ліч.',name:'type_meter', index:'type_meter', width:80, editable: false, align:'left',edittype:'text',sortable:false,hidden:false},                
    {label:'Розр. ліч.',name:'carry', index:'carry', width:40, editable: false, align:'left',edittype:'text',sortable:false},                    
    {label:'К.тр',name:'k_tr', index:'k_tr', width:40, editable: false, align:'left',edittype:'text',sortable:false},                        
    {label:'Зона',name:'id_zone', index:'id_zone', width:60, editable: false, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text',sortable:false},                       
 
    {label:'Попер.пок.',name:'p_indic', index:'p_indic', width:80, editable: false, align:'right',hidden:false,
                            edittype:'text',sortable:false},           

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
                            setTimeout("jQuery('#indic_table').editCell(" + selIRow + " + 1, " + selICol + ", true);", 100);
                        }
                    }
                } 
                ]
            }                            
    },

    {label:'Споживання',name:'demand', index:'demand', width:80, editable: false, align:'right',hidden:false,
                            edittype:'text',sortable:false},
    {label:'Факт пок.',name:'indic_real', index:'indic_real', width:80, editable: true, align:'right',hidden:false,
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
                            setTimeout("jQuery('#indic_table').editCell(" + selIRow + " + 1, " + selICol + ", true);", 100);
                        }
                    }
                } 
                ]
            }                            
    },
                        
                        
    {label:'Ознака',name:'idk_oper', index:'idk_oper', width:60, editable: false, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lworkmetstatus},stype:'text',sortable:false},
                        
    ],
    pager: '#indic_tablePager',
    autowidth: true,
    //shrinkToFit : false,
    rowNum:500,
    //rowList:[50,100,200],
    sortname: 'num_meter',
    sortorder: 'asc',
    viewrecords: true,
    //gridview: true,
    caption: 'Показники перевірених/демонтованих лічильників',
    //hiddengrid: false,
    forceFit : true,
    hidegrid: false,    
    postData:{'w_id':id_work, 'p_id':id_paccnt,'m_id':id_meter,'mode':mode, 'idk_work': idk_work,'w_date': work_date},
    cellEdit: true, 
    cellsubmit: 'clientArray',
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    
    gridComplete:function(){

     var rows = $(this).getDataIDs();
     
     if (rows.length > 0) 
     {      
      // if (indic_array.length>0)  
      // {
         for(i=0;i<rows.length;i++) 
         {
             if( indic_array[rows[i]])
             {
                
                //$(this).jqGrid('setRowData',rows[i],{ indic: indic_array[rows[i]] });
                //$(this).setCell(rows[i],'indic','','mod_column_class');                
                $(this).editCell(i+1,13,true);
                $(this).find("#"+(i+1)+"_indic").attr('value',indic_array[rows[i]]);
                $(this).saveCell(i+1,13);
             }
      
         }

      // }
         
       var first_id = parseInt(rows[0]);
       $(this).setSelection(first_id, true);
     }
     
    },
    onSelectRow: function(id) { 
          cur_indic_id = id;
    },
    
    beforeEditCell : function(rowid, cellname, value, iRow, iCol)
    {
        selICol = iCol;
        selIRow = iRow;
    },    
    
     afterSaveCell : function(rowid,name,val,iRow,iCol) {
         
            if(name == 'indic') {
/*                var p_ind = jQuery("#indic_table").jqGrid('getCell',rowid,iCol-2);
                var k_tr = jQuery("#indic_table").jqGrid('getCell',rowid,iCol-4);
                var ind = val;
                var dem = (ind - p_ind)* k_tr;       
                jQuery("#indic_table").jqGrid('setRowData',rowid,{demand: dem});    
  */
 
                var p_ind = parseFloat(jQuery("#indic_table").jqGrid('getCell',rowid,iCol-2));
                var k_tr = parseFloat(jQuery("#indic_table").jqGrid('getCell',rowid,iCol-4));
               // var dt_ind = jQuery("#indic_table").jqGrid('getCell',rowid,iCol+1);
                var carry = parseFloat(jQuery("#indic_table").jqGrid('getCell',rowid,iCol-5));
                var ind = parseFloat(val);

                var dem=0;
                    
                var tr = jQuery('#indic_table')[0].rows.namedItem(rowid); 
                var td = tr.cells[iCol];
                $(td).removeClass("err_column_class");                    
                $(td).removeClass("mod_column_class");                    
                
                if (val.length==0)    
                {
                    jQuery("#indic_table").jqGrid('setRowData',rowid,{
                        demand: ''
                    });    
                }
                else
                {
                
                    if (Math.round(ind).toString().length>carry)    
                    {
                        jQuery('#indic_table').setCell(rowid,name,'','err_column_class');  
                        
                        jQuery("#indic_table").jqGrid('setRowData',rowid,{
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
                            
                            if (dem > 1000)
                            {
                                //dem = 0;
                                jQuery('#indic_table').setCell(rowid,name,'','err_column_class');  
                            }
                            
                          
                        }
                      
                        jQuery("#indic_table").jqGrid('setRowData',rowid,{
                            demand: dem
                        });    
                    
                        jQuery('#indic_table').setCell(rowid,name,'','mod_column_class');
                    }
                }   
             }; 
             
             
             
             
             
            jQuery('#indic_table').setCell(rowid,name,'','mod_column_class');
            jQuery('#indic_table').setCell(rowid,'demand','','mod_column_class');
          
        },    
    
    
    //ondblClickRow: function(id){ 
    //     jQuery(this).editGridRow(id,LgtNormEditOptions);  
    //} ,  

    loadError : function(xhr,st,err) {
      jQuery('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);
    },
  
    jsonReader : {repeatitems: false}

  }).navGrid('#indic_tablePager',
         {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ).jqGrid('bindKeys'); 

//------------------------------------------------------------------------------

jQuery('#paccnt_meter_zones_table').jqGrid({
    url:'meter_work_newmeter_zone_data.php',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:90,
    width:240,
    //autowidth: true,
    //shrinkToFit : false,
    
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {name:'id_meter', index:'id_meter', width:40, editable: false, align:'center', hidden:true},     
      {name:'id_metzone', index:'id_metzone', width:40, editable: false, align:'center', hidden:true},     
      {name:'id_work_indic', index:'id_work_indic', width:40, editable: false, align:'center', hidden:true},           
      {name:'kind_energy', index:'kind_energy', width:40, editable: false, align:'center', hidden:true},           
      {label:'Зона',name:'id_zone', index:'id_zone', width:120, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text'},                       
      {label:'Показники',name:'indic', index:'indic', width:100, editable: true, align:'right',edittype:'text'}
    ],
    pager: '#paccnt_meter_zones_tablePager',
    rowNum:50,
    sortname:  'id_zone',
    sortorder: 'asc',
    //viewrecords: true,
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    viewrecords: false,        
    gridview: true,
    caption: 'Зони',
    hidegrid: false,
    postData:{'w_id': id_work, 's_id':id_session},    

    gridComplete:function(){
     if ($(this).getDataIDs().length > 0) 
     {      
       var first_id = parseInt($(this).getDataIDs()[0]);
       $(this).setSelection(first_id, true);
     }
    },

    onSelectRow: function(id) { 
      cur_meter_zone_id = id;  
    },

    ondblClickRow: function(id){ 

      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
        ////jQuery(this).jqGrid('GridToForm',gsr,"#dialog-newmeterzone"); 
        
        $("#dialog-newmeterzone").find("#fnewmeterzone_id").attr('value',id);
        $("#dialog-newmeterzone").find("#fid_zone").attr('value',jQuery(this).jqGrid('getCell',id,'id_zone'));
        $("#dialog-newmeterzone").find("#findic").attr('value',jQuery(this).jqGrid('getCell',id,'indic'));

//        $("#dialog-newmeterzone").find("#fid_zone").attr('value',jQuery(this).jqGrid('getCell',id,'id_zone'));
//        $("#dialog-newmeterzone").find("#findic").attr('value','');
        
        if (jQuery(this).jqGrid('getCell',id,'id_work_indic')!='-1')
        {
            $("#dialog-newmeterzone").find("#fid_zone").addClass("readonly");
            $("#dialog-newmeterzone").find("#fid_zone").css("color","#AAAAAA");
        }
        else
        {
            $("#dialog-newmeterzone").find("#fid_zone").removeClass("readonly");
            $("#dialog-newmeterzone").find("#fid_zone").css("color","#000000");
        }

        $("#dialog-newmeterzone").dialog({ 
            resizable: true,
            width:350,
            modal: true,
            autoOpen: false,
            buttons: {
                "Ok": function() {
                    if(($("#dialog-newmeterzone").find("#findic").val()!='')&&
                       ($("#dialog-newmeterzone").find("#fid_zone").val()!=''))
                        {
                          //$("#ddlViewBy option:selected").text()
                          EditMeterZone();  
                          $( this ).dialog( "close" );
                        } 
                },
                "Отмена": function() {
                    $( this ).dialog( "close" );
                }
            },
            focus: function() {
                /*            
              $(this).on("keyup", function(e) {
                      if (e.keyCode === 13) {
                          $(this).parent().find("button:contains('Ok')").trigger("click");
                       return false;
                      }
                  })
                */
              }
            

        });
        jQuery("#dialog-newmeterzone").dialog('open');
        $("#dialog-newmeterzone").find("#findic").focus();

      } else {alert("Please select Row")}       
      
    } ,  

    loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');},
  
    jsonReader : {repeatitems: false}

  }).navGrid('#paccnt_meter_zones_tablePager',
        {edit:false,add:false,del:false,search:false}
        ); 

 jQuery("#paccnt_meter_zones_tablePager_center").hide();
 jQuery("#paccnt_meter_zones_tablePager_right").hide();

 jQuery("#paccnt_meter_zones_table").jqGrid('navButtonAdd','#paccnt_meter_zones_tablePager',{caption:"Нова зона",
       id:"btn_new_zone",     
	onClickButton:function(){ 

        //$("#dialog-newmeterzone").find("#fid_zone").attr('value','');
        //$("#dialog-newmeterzone").find("#fid_zone").find('option').attr("selected","") ;
        $("#dialog-newmeterzone").find('#fid_zone option').each(function(i, e)  {e.selected = false});
        $("#dialog-newmeterzone").find("#findic").attr('value','');

        $("#dialog-newmeterzone").find("#fid_zone").removeClass("readonly");
        $("#dialog-newmeterzone").find("#fid_zone").css("color","#000000");

        $("#dialog-newmeterzone").dialog({ 
            resizable: true,
//            height:170,
            width:350,
            modal: true,
            autoOpen: false,
            buttons: {
                "Ok": function() {
                    if(($("#dialog-newmeterzone").find("#findic").val()!='')&&
                       ($("#dialog-newmeterzone").find("#fid_zone").val()!=''))
                        {
                          //$("#ddlViewBy option:selected").text()
                          AddMeterZone();  
                          $( this ).dialog( "close" );
                        } 
                },
                "Отмена": function() {
                    $( this ).dialog( "close" );
                }
            }

        });
        jQuery("#dialog-newmeterzone").dialog('open');
          
    } 
});

 jQuery("#paccnt_meter_zones_table").jqGrid('navButtonAdd','#paccnt_meter_zones_tablePager',{caption:"Видалити зону",
        id:"btn_del_zone",          
	onClickButton:function(){ 

      if ($("#paccnt_meter_zones_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити зону?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                          DeleteMeterZone();
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


 $("#dialog-newmeterzone").find("#fid_zone").bind("focus", function(){
        if($(this).hasClass('readonly'))
        {
          $(this).blur();   
          return;
        }
      });

 $("#fWorkEdit").find("#fidk_work").bind("focus", function(){
        if($(this).hasClass('readonly'))
        {
          $(this).blur();   
          return;
        }
      });

//jQuery("#paccnt_meters_table").jqGrid('filterToolbar','');

/*
 meters_form_options = { 
    dataType:"json",
    beforeSubmit: MetersBeforeSubmit, // функция, вызываемая перед передачей 
    success: MetersSubmitResponse // функция, вызываемая при получении ответа
  };

fMeterParam_ajaxForm = $("#fMeterParam").ajaxForm(meters_form_options);


jQuery("#pMeterParam :input").addClass("ui-widget-content ui-corner-all");
jQuery("#dialog-newmeterzone :input").addClass("ui-widget-content ui-corner-all");
jQuery("#dialog-changedate :input").addClass("ui-widget-content ui-corner-all");

// опции валидатора 
var meter_valid_options = { 

		rules: {
			type_meter: "required",
			num_meter: "required",
			carry:{required: true,
                        number:true
                        },
                        dt_start: "required",
                        coef_comp:{required: true,
                                number:true
                        },
                        power: {number:true}
		},
		messages: {
			type_meter: "Виберіть тип лічильника!",
			num_meter: "Вкажіть номер лічильника",
			carry:{required: "Вкажіть розрядність лічильника",
                        number:"Повинно бути число!"
                        },
                        dt_start: "Вкажіть дату встановлення лічильника",
			coef_comp:{required: "Вкажіть к.тр",
                        number:"Повинно бути число!"
                        }, 
                        power: {number:"Повинно бути число!"}
		}
};

validator_meter = $("#fMeterParam").validate(meter_valid_options);
*/

jQuery("#show_mlist").click( function() {

    createMeterGrid(jQuery("#fid_type_meter").val());
    meter_target_id=jQuery("#fid_type_meter");
    meter_target_name = jQuery("#ftype_meter");
    meter_target_carry = jQuery("#fcarry");

    jQuery("#grid_selmeter").css({'left': jQuery("#ftype_meter").position().left+1, 'top': jQuery("#ftype_meter").position().top+20});
    jQuery("#grid_selmeter").toggle( );
    
    jQuery("#grid_selmeter").find("input[type='text']:visible:enabled:first").focus();            
});
// выбор тр. тока 
jQuery("#show_compa").click( function() {

    compi_target_id=jQuery("#fid_typecompa");
    compi_target_name = jQuery("#ftypecompa");
    compi_target_ktr = jQuery("#fcoef_comp");
    
    createCompIGrid(); 
    jQuery("#grid_selci").css({'left': $(this).position().left+1, 'top': $(this).position().top+20});
    jQuery("#grid_selci").toggle( );
});

//выбор тр. напряжения
jQuery("#show_compu").click( function() {

    compi_target_id=jQuery("#fid_typecompu");
    compi_target_name = jQuery("#ftypecompu");
    
    createCompIGrid(); 
    jQuery("#grid_selci").css({'left': $(this).position().left+1, 'top': $(this).position().top+20});
    jQuery("#grid_selci").toggle( );
});

jQuery("#show_tplist").click( function() {

    createTpGrid();
    tp_target_id=jQuery("#fid_station");
    tp_target_name = jQuery("#fstation");

    jQuery("#grid_seltp").css({'left': jQuery("#fstation").position().left+1, 'top': jQuery("#fstation").position().top+20});
    jQuery("#grid_seltp").toggle( );
    
    jQuery("#grid_seltp").find("input[type='text']:visible:enabled:first").focus();            
});

jQuery("#toggle_comp").click( function() {
    jQuery("#pMeterParam_comp").toggle( );
});


  $('#pwork_header *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#pwork_header *').filter('input,select,textarea').filter(':visible').filter(':enabled').filter(':not([readonly])').filter(':not(.readonly)');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 

  $('#pMeterParam *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#pMeterParam *').filter('input,select,textarea').filter(':visible').filter(':enabled').filter(':not([readonly])').filter(':not(.readonly)');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 

});


function SelectPersonExternal(id, name) {
        $("#fWorkEdit").find("#fid_position").attr('value',id );
        $("#fWorkEdit").find("#fposition").attr('value',name );    
    
}


function LoadWorkData(data)
{
  //   var str = $.param(data); 
  //alert(str); 
  if (data.errcode===undefined)
  {    
    $("#fWorkEdit").resetForm();
    $("#fWorkEdit").clearForm();
      
 
    $("#fWorkEdit").find("#addr_str").html(data.addr_str);
    $("#fWorkEdit").find("#abon").html(data.abon);
    $("#fWorkEdit").find("#fbook").attr('value', data.book);
    $("#fWorkEdit").find("#fcode").attr('value', data.code);
    $("#fWorkEdit").find("#fid_paccnt").attr('value', id_paccnt); 
    $("#fWorkEdit").find("#fid_meter").attr('value', id_meter); 
    $("#fWorkEdit").find("#fid_session").attr('value', id_session); 
    $("#fWorkEdit").find("#fwork_period").datepicker( "setDate" , data.work_period );
    
    if (mode==0)
    {
        if (idk_work!=0)
        {
            $("#fWorkEdit").find("#fidk_work").attr('value', idk_work); 
            //$("#fWorkEdit").find("#fidk_work").attr("readonly", true);
            //$("#fWorkEdit").find("#fidk_work").prop("readonly","readonly");
            $("#fWorkEdit").find("#fidk_work").addClass("readonly");
            $("#fWorkEdit").find("#fidk_work").css("color","#AAAAAA");
            
        }
        else
        {
            $("#fWorkEdit").find('#fidk_work option').each(function(i, e)  
            {
                if ((e.value == '1')||(e.value == '2')||(e.value == '3'))
                        e.disabled="disabled";
                e.selected = false
            });
        }
        
        $("#fWorkEdit").find("#fcoef_comp").attr('value',1 );  
        jQuery("#fdt_work").datepicker( "setDate" , date_work );        
        
    }
    
    if (mode==1)
    { 
     $("#fWorkEdit").find("#fid").attr('value',data.id );

     $("#fWorkEdit").find("#fidk_work").attr('value', data.idk_work);
     idk_work = data.idk_work;
     work_date = data.dt_work;
     
     if ((idk_work==1)||(idk_work==2)||(idk_work==3))
     {
            //$("#fWorkEdit").find("#fidk_work").attr("readonly", true);
            $("#fWorkEdit").find("#fidk_work").addClass("readonly");
            $("#fWorkEdit").find("#fidk_work").css("color","#AAAAAA");
     }
     else
     {
            $("#fWorkEdit").find('#fidk_work option').each(function(i, e)  
            {
                if ((e.value == '1')||(e.value == '2')||(e.value == '3'))
                        e.disabled="disabled";
            });

     }

     $("#fWorkEdit").find("#fid_position").attr('value', data.id_position);
     $("#fWorkEdit").find("#fposition").attr('value', data.position);
    
     $("#fWorkEdit").find("#fnote").attr('value', data.note);
     $("#fWorkEdit").find("#fdt_work").datepicker( "setDate" , data.dt_work );
     $("#fWorkEdit").find("#fact_num").attr('value', data.act_num);
    }   
    
    if ((mode==0)&&(idk_work==2)||(mode==1)&&((idk_work==1)||(idk_work==2)))
    {
    
        $("#fWorkEdit").find("#fnewmeter_id").attr('value',data.newmeter_id );                
        $("#fWorkEdit").find("#fcode_eqp").attr('value',data.code_eqp );                
        $("#fWorkEdit").find("#fid_type_meter").attr('value',data.id_type_meter );        
        $("#fWorkEdit").find("#ftype_meter").attr('value',data.type_meter );        

        $("#fWorkEdit").find("#fid_typecompa").attr('value',data.id_typecompa );        
        $("#fWorkEdit").find("#ftypecompa").attr('value',data.typecompa );        

        $("#fWorkEdit").find("#fid_typecompu").attr('value',data.id_typecompu );        
        $("#fWorkEdit").find("#ftypecompu").attr('value',data.typecompu );  
        
        $("#fWorkEdit").find("#fnum_meter").attr('value',data.num_meter );  
        $("#fWorkEdit").find("#fcarry").attr('value',data.carry );  
        
        $("#fWorkEdit").find("#fdt_control").datepicker( "setDate" , data.dt_control );
        $("#fWorkEdit").find("#fdt_control_ca").datepicker( "setDate" , data.dt_control_ca );
        $("#fWorkEdit").find("#fdt_control_cu").datepicker( "setDate" , data.dt_control_cu );
        
        $("#fWorkEdit").find("#fcoef_comp").attr('value',data.coef_comp );  
        if(data.coef_comp==1)
        {
           $("#pMeterParam_comp").hide();
        } 
        else
        {
            $("#pMeterParam_comp").show();
        }
             
        $("#fWorkEdit").find("#fpower").attr('value',data.power );  

        $("#fWorkEdit").find("#fid_station").attr('value',data.id_station );  
        $("#fWorkEdit").find("#fstation").attr('value',data.station );  
        
        $("#fWorkEdit").find("#fid_extra").attr('value',data.id_extra );  

        if (data.calc_losts==1)
        {
            $("#fWorkEdit").find("#fcalc_losts").prop('checked',true);
        }
        else
        {
            $("#fWorkEdit").find("#fcalc_losts").prop('checked',false);
        }

        if (data.smart==1)
        {
            $("#fWorkEdit").find("#fsmart").prop('checked',true);
        }
        else
        {
            $("#fWorkEdit").find("#fsmart").prop('checked',false);
        }

        if (data.magnet==1)
        {
            $("#fWorkEdit").find("#fmagnet").prop('checked',true);
        }
        else
        {
            $("#fWorkEdit").find("#fmagnet").prop('checked',false);
        }
        
        if (mode==1)
        {
         jQuery('#pMeterParam *').attr("disabled", true);
         jQuery('#pMeterParam #toggle_comp').attr("disabled", false);
         jQuery('#pMeterParam #fnewmeter_id').attr("disabled", false);
         jQuery('#pMeterParam #fcode_eqp').attr("disabled", false);
        } 
    
    }
    
    if ((idk_work==1)||(idk_work==2))
    {
       $('#paccnt_meter_zones_table').jqGrid('setGridParam',{postData:{'w_id': id_work, 's_id':id_session}});
       $('#paccnt_meter_zones_table').jqGrid('setGridParam',{datatype:'json'}).trigger('reloadGrid');           
       $("#pMeterParam").show();
    }       
    else  
       $("#pMeterParam").hide();

    if (idk_work==1)
       $("#pindic_table").hide();
    else  
    {
       $("#pindic_table").show();
       
       if (mode==1)
           {
            $('#indic_table').jqGrid('setGridParam',{'postData':{'w_id':id_work, 'p_id':id_paccnt,'m_id':id_meter,'mode':mode, 'idk_work': idk_work,'w_date': work_date}});
           }
       $('#indic_table').jqGrid('setGridParam',{datatype:'json'}).trigger('reloadGrid');           
    }
    
    CommitJQFormVal($("#fWorkEdit"));
    
    if (r_meter_edit!=3)
    {
      $('#btn_new_zone').addClass('ui-state-disabled');
      $('#btn_del_zone').addClass('ui-state-disabled');
    }
  
    if ((r_work_edit!=3)&&((idk_work==4)||(idk_work==5)))
    {
      $("#fWorkEdit").find("#bt_edit").prop('disabled', true);
    }

    if (((r_work_edit!=3)||(r_meter_edit!=3))&&((idk_work==1)||(idk_work==2)||(idk_work==3)))
    {
      $("#fWorkEdit").find("#bt_edit").prop('disabled', true);
    }


  }
  else
  {
    $('#message_zone').append(data.errstr);  
    $('#message_zone').append("<br>");                 
    jQuery("#message_zone").dialog('open');
  }
};
//----------------------------------------------------------------------------
function ResetJQFormVal(form)
{
  form.find('[data_old_value]').each(function() {
        var vlastValue = $(this).attr('data_old_value');
        $(this).attr('value',vlastValue);
        $(this).focus();
  });
        
  form.find('[data_old_checked]').each(function() {
        var vlastValue = $(this).attr('data_old_checked');
        //alert(vlastValue);
        if (vlastValue=='true')
        {
          $(this).prop('checked',true);
        }
        else
        {
          $(this).prop('checked',false);
        }    
    
 });
};

function CommitJQFormVal(form)
{
   form.find('[data_old_value]').each(function() {
            var vlastValue = $(this).attr('value');
             $(this).attr('data_old_value',vlastValue);  
             //alert($(this).attr('data_old_value'));             
   });
        
   form.find('[data_old_checked]').each(function() {
            var vlastValue = $(this).prop('checked');
             $(this).attr('data_old_checked',vlastValue);  
   });    
}; 
//-----------------------------------------------------------------------------
function WorkBeforeSubmit(formData, jqForm, options) { 

    if ((selICol!=0)&&(selIRow!=0))
    {
       jQuery('#indic_table').editCell(selIRow,selICol, false); 
    }
    
    var btn = '';
    submit_form = jqForm;
    for (var i=0; i < formData.length; i++) { 
        
        if (formData[i].name =='submitButton') { 
           btn= formData[i].value; 
           submit_form[0].oper.value = btn;
        } 
    }
    
    if((btn=='edit')||(btn=='add'))
    {
       if (form_edit_lock == 1) return false;
    }    
    
    
    
    //var rn = $('#indic_table').jqGrid('getGridParam','records');
    if (idk_work!=1)
{
        var data_obj = $('#indic_table').getChangedCells('all');
    
        var err_cnt =0;
        var rows = $('#indic_table').getDataIDs();
        
        for(i=0;i<rows.length;i++) 
        {
            row=$('#indic_table').getRowData(rows[i]);
            if (row.indic=='')
            {
                err_cnt++;
            }
        }
    
        if (err_cnt > 0)
        {
        
            jQuery("#dialog-confirm").find("#dialog-text").html('Необхідно заповнити показники для всіх лічильників!');
    
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Внесіть показники',
                buttons: {
                    "Ок": function() {
                        $( this ).dialog( "close" );
                    }
                }
            });
    
            jQuery("#dialog-confirm").dialog('open');          
        
            return false;
        }
            
    }
    
    if ((idk_work==1)||(idk_work==2))
    {
      var rn = $('#paccnt_meter_zones_table').jqGrid('getGridParam','records');  
      if (rn==0)
      {
          
            jQuery("#dialog-confirm").find("#dialog-text").html('Необхідно вказати зони встановленого лічильника!');
    
            $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Внесіть зони !',
			buttons: {
				"Ок": function() {
					$( this ).dialog( "close" );
				}
			}
		});
    
            jQuery("#dialog-confirm").dialog('open');          
        
            return false;
          
      }
        
    }
        
    var json_str = JSON.stringify(data_obj);


    var queryString = $.param(formData);     
    $('#message_zone').append('Вот что мы передаем:' + queryString);  
    $('#message_zone').append("<br>");                 
    

    for (var i=0; i < formData.length; i++) { 
        
        if (formData[i].name =='indication_json') { 
           formData[i].value = json_str; 
           submit_form[0].indication_json.value = json_str;
        } 
        
    } 

    if((btn=='edit')||(btn=='add'))
    {
       //if (form_edit_lock == 1) return false;
       if(!submit_form.validate().form())  {return false;}
       else {
        if (btn=='edit')
            {
               jQuery("#dialog-confirm").find("#dialog-text").html('Записати?'); 
                    $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Збереження роботи',
			buttons: {
				"Ok": function() {
                                        form_edit_lock=1;
                                        SaveWorkChanges();
					$( this ).dialog( "close" );
				},
				"Отмена": function() {
                                        //CancelMeterChanges();
					$( this ).dialog( "close" );
				}
			}

                });
                
                $("#dialog-confirm").dialog('open');
                return false; 
                
            }

        if (btn=='edit')
            {
               jQuery("#dialog-confirm").find("#dialog-text").html('Записати?'); 
                    $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Збереження роботи',
			buttons: {
				"Ok": function() {
                                        form_edit_lock=1;
                                        SaveWorkChanges();
					$( this ).dialog( "close" );
				},
				"Отмена": function() {
                                        //CancelMeterChanges();
					$( this ).dialog( "close" );
				}
			}

                });
                
                $("#dialog-confirm").dialog('open');
                return false; 
                
            }

            else
                {
                    form_edit_lock=1;
                    return true;
               }

       }
    }
    else {return true;}       
    //}
    
} ;

// обработчик ответа сервера после отправки формы
function WorkSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             //form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 
             
             if (errorInfo.errcode==1) {
               
                                           
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
  
  
               window.opener.RefreshMetersExternal();
               window.opener.focus();
               self.close();            
  
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               form_edit_lock=0;
               return [false,errorInfo.errstr]};   

};

function SaveWorkChanges()
{
  //var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
  
  //submit_form[0].change_date.value = cur_dt_change;
  submit_form.ajaxSubmit(form_options);         
    
};

function  AddMeterZone()
{
    var vid_zone = $("#dialog-newmeterzone").find("#fid_zone").val();
    var vindic = $("#dialog-newmeterzone").find("#findic").val();
    
    var request = $.ajax({
        url: "meter_work_newmeter_zone_edit.php",
        type: "POST",
        data: {
            oper : 'add',
            id_paccnt : id_paccnt,
            id_meter : $("#fWorkEdit").find("#fnewmeter_id").val(), 
            id_work : id_work,                         
            id_zone : vid_zone,
            indic : vindic,
            id_session : id_session
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
                jQuery('#paccnt_meter_zones_table').trigger('reloadGrid');                      
            }
        }
     });

    request.fail(function(data ) {
        alert("error");
        
    });
    
};

function  EditMeterZone()
{
    var vid_zone = $("#dialog-newmeterzone").find("#fid_zone").val();
    var vindic = $("#dialog-newmeterzone").find("#findic").val();
    var vid = $("#dialog-newmeterzone").find("#fnewmeterzone_id").val();
    
    var request = $.ajax({
        url: "meter_work_newmeter_zone_edit.php",
        type: "POST",
        data: {
            oper : 'edit',
            id : vid,
            id_paccnt : id_paccnt,
            id_meter : $("#fWorkEdit").find("#fnewmeter_id").val(), 
            id_work : id_work,                         
            id_zone : vid_zone,
            indic : vindic,
            id_session : id_session
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
                jQuery('#paccnt_meter_zones_table').trigger('reloadGrid');                      
            }
        }
     });

    request.fail(function(data ) {
        alert("error");
        
    });
    
};

function  DeleteMeterZone()
{
    
    var cur_dt_change = $("#dialog-changedate").find("#fdate_change").val();    
    
    var request = $.ajax({
        url: "meter_work_newmeter_zone_edit.php",
        type: "POST",
        data: {
            oper : 'del',
            id_paccnt : id_paccnt,   
            id_meter : 0,             
            id_work : id_work,             
            id : cur_meter_zone_id, 
            id_session : id_session
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
                jQuery('#paccnt_meter_zones_table').trigger('reloadGrid');                      
            }
        }
     });

    request.fail(function(data ) {
        alert("error");
        
    });

};

