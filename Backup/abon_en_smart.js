var edit_row_id=0;
var elapsed_seconds = 0;
var timerId ;
var spinner_opts;
var id_file=-1;
//var r_edit = 3;
var validator = null;
var cur_indic_id =0;
var form_edit_lock=0;

jQuery(function(){ 


    $.fn.spin = function(opts, color) {
        var presets = {
            "tiny": {
                lines: 8, 
                length: 2, 
                width: 2, 
                radius: 3
            },
            "small": {
                lines: 8, 
                length: 4, 
                width: 3, 
                radius: 5
            },
            "large": {
                lines: 10, 
                length: 8, 
                width: 4, 
                radius: 8
            }
        };
        if (Spinner) {
            return this.each(function() {
                var $this = $(this),
                data = $this.data();
                if (data.spinner) {
                    data.spinner.stop();
                    delete data.spinner;
                }
                if (opts !== false) {
                    if (typeof opts === "string") {
                        if (opts in presets) {
                            opts = presets[opts];
                        } else {
                            opts = {};
                        }
                        if (color) {
                            opts.color = color;
                        }
                    }
                    data.spinner = new Spinner($.extend({
                        color: $this.css('color')
                        }, opts)).spin(this);
                }
            });
        } else {
            throw "Spinner class not available.";
        }
    };


  //$('#load_in_progress').hide();
  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
		buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");
    
  jQuery("#fHeaderEdit :input").addClass("ui-widget-content ui-corner-all");    
  jQuery("#fIndicEdit :input").addClass("ui-widget-content ui-corner-all");    
  
  dt_mmgg = Date.parse( mmgg, "dd.MM.yyyy");
  str_mmgg = dt_mmgg.toString("dd.MM.yyyy");
  $("#fmmgg").datepicker( "setDate" , dt_mmgg.toString("dd.MM.yyyy") );
  $("#fmmgg").datepicker( "setDate" , mmgg );


  jQuery('#headers_table').jqGrid({
    url:'abon_en_smart_headers_data.php',
    editurl: '',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    //width:800,
    //shrinkToFit : false,
    autowidth: true,
    //scroll: 0, 
    colNames:[],
    colModel :[ 

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_house', index:'id_house', width:40, editable: false, align:'center',hidden:true},     

    {label:'Файл',name:'name_file', index:'name_file', width:130, editable: true, align:'left',edittype:'text'},
    {label:'Ознака',name:'ident', index:'ident', width:50, editable: true, align:'left',edittype:'text'},
    {label:'Адреса',name:'addr_str', index:'addr_str', width:200, editable: true, align:'left',edittype:'text'},
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    
    {label:'Статус',name:'status', index:'status', width:70, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lstatus},stype:'select',hidden:false},
    {label:'Дата показ.',name:'date_ind', index:'date_ind', width:60, editable: true, align:'left',edittype:'text',formatter:'date', hidden:false},
    {label:'Період',name:'mmgg', index:'mmgg', width:60, editable: true, align:'left',edittype:'text',formatter:'date', hidden:false},
    {name:'dt', index:'dt', width:80, editable: true, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}
 //   {label:'Закр.',name:'flock', index:'flock', width:30, editable: true, align:'right',
//                            formatter:'checkbox',edittype:'checkbox',
//                            stype:'select', searchoptions:{value:': ;1:*'}}

    ],
    pager: '#headers_tablePager', 
    rowNum:300,
    rowList:[20,50,100,300,500],
    sortname: 'dt',
    sortorder: 'desc',
    viewrecords: true,
    gridview: true,
    caption: 'Файли СМАРТ',
    //multiselect: true,
    //hiddengrid: false,
    hidegrid: false,
    postData:{'p_mmgg':mmgg},
    //jsonReader : {repeatitems: false},

    gridComplete:function(){

      if ($(this).getDataIDs().length > 0) 
      {      
       var first_id = parseInt($(this).getDataIDs()[0]);
       $(this).setSelection(first_id, true);
      }
      else
      {
         jQuery('#indic_table').jqGrid('setGridParam',{'postData':{'p_id':0}}).trigger('reloadGrid');                
      }
    
   },

   onSelectRow: function(rowid) { 
        edit_row_id = rowid; 
       jQuery('#indic_table').jqGrid('setGridParam',{datatype:'json','postData':{'p_id':edit_row_id}}).trigger('reloadGrid');                
    },
/*
    onPaging : function(but) { 
                id_paccnt=0;
                $(this).jqGrid('setGridParam',{'postData':{'p_mmgg': mmgg, 'selected_id':id_paccnt}});        
    },    
  */  
    
    ondblClickRow: function(id){ 
      //jQuery(this).editGridRow(id,{width:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
    
      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
         validator.resetForm();  //для сброса состояния валидатора
         $("#fHeaderEdit").resetForm();
         $("#fHeaderEdit").clearForm();
          
         $("#headers_table").jqGrid('GridToForm',gsr,"#fHeaderEdit"); 
         $("#fHeaderEdit").find("#foper").attr('value','edit');              
         edit_row_id = id;

         $("#dialog_editHeader").dialog('open');         
                
         if (r_edit==3)
         {
            $("#fHeaderEdit").find("#bt_edit").prop('disabled', false);
         }
         else
         {
            $("#fHeaderEdit").find("#bt_edit").prop('disabled', true);
         }
        
      } else {alert("Please select Row")}       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');}

  }).navGrid('#headers_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

jQuery("#headers_table").jqGrid('filterToolbar','');
jQuery("#headers_table").jqGrid('bindKeys', {"onEnter":function( id ) {  }} );


jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{caption:"Редагувати",
    id:"bt_smart_edit",
    onClickButton:function(){ 

      if ($("#headers_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#headers_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
           validator.resetForm();  //для сброса состояния валидатора
           $("#fHeaderEdit").resetForm();
           $("#fHeaderEdit").clearForm();
          
           $("#headers_table").jqGrid('GridToForm',gsr,"#fHeaderEdit"); 
           $("#fHeaderEdit").find("#foper").attr('value','edit');              

           $("#dialog_editHeader").dialog('open');         
                
           if (r_edit==3)
           {
              $("#fHeaderEdit").find("#bt_edit").prop('disabled', false);
           }
           else
           {
              $("#fHeaderEdit").find("#bt_edit").prop('disabled', true);
           }

        }
    } 
});

jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{caption:"Видалити",
        id:"bt_smart_del",
	onClickButton:function(){ 

      if ($("#headers_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити файл?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                                      
                                        fHeader_ajaxForm[0].id.value = edit_row_id;
                                        //fHeader_ajaxForm[0].change_date.value = cur_dt_change;
                                        fHeader_ajaxForm[0].oper.value = 'del';
                                        fHeader_ajaxForm.ajaxSubmit(form_options);   

					$( this ).dialog( "close" );                                    
				},
				"Відмінити": function() {
					$( this ).dialog( "close" );
				}
			}
		});
    
        jQuery("#dialog-confirm").dialog('open');   
          
        ;} 
});

jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{caption:"Видалити місяць",
        id:"bt_smartall_del",
	onClickButton:function(){ 

      if ($("#headers_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити всі файли за місяць?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                                      
                                        fHeader_ajaxForm[0].mmgg.value = mmgg;
                                        //fHeader_ajaxForm[0].change_date.value = cur_dt_change;
                                        fHeader_ajaxForm[0].oper.value = 'delall';
                                        fHeader_ajaxForm.ajaxSubmit(form_options);   

					$( this ).dialog( "close" );                                    
				},
				"Відмінити": function() {
					$( this ).dialog( "close" );
				}
			}
		});
    
        jQuery("#dialog-confirm").dialog('open');   
          
        ;} 
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
//	,       center__onresize:	function (pane, $pane, state, options) 
//        {
//            $("#headers_table").jqGrid('setGridWidth',$pane.innerWidth()-7);
//            $("#headers_table").jqGrid('setGridHeight',$pane.innerHeight()-160);
//        }
        
	});

 innerLayout = $("#pmain_content").layout({
		name:	"inner"  
	,	north__paneSelector:	"#pHeaders_table"
	,	north__closable:	false
	,	north__resizable:	true
        ,	north__size:		300
        ,	north__spacing_open:	5
	,	center__paneSelector:	"#pIndic_table"
        ,	center__resizable:	true
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       north__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#headers_table").jqGrid('setGridWidth',_pane.innerWidth()-15);
            jQuery("#headers_table").jqGrid('setGridHeight',_pane.innerHeight()-135);
        }
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#indic_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#indic_table").jqGrid('setGridHeight',_pane.innerHeight()-100);
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
   $("#calc_progress").dialog({autoOpen: false, resizable: false});
    jQuery("#pActionBar :input").addClass("ui-widget-content ui-corner-all");
    
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       
       $('#headers_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
       
   });
   //--------------загрузка файла ----------------
   
   
    var file_form_options = { 
    dataType:"json",
    beforeSubmit: FileFormBeforeSubmit, 
    success: FileFormSubmitResponse,
    error: FileFormSubmitError    
/*
    ,beforeSend: function() {
        status.empty();
        var percentVal = '0%';
        bar.width(percentVal)
        percent.html(percentVal);
    },
    uploadProgress: function(event, position, total, percentComplete) {
        var percentVal = percentComplete + '%';
        bar.width(percentVal)
        percent.html(percentVal);
    }*/
 };
 
var fFileLoad_ajaxForm = $("#fLoad").ajaxForm(file_form_options);


var file_valid_options = { 
                errorPlacement: function(error, element) {
				error.appendTo( element.parent("label").parent("form"));
                },
		rules: {
			//smart_file: "required"
		},
		messages: {
			//smart_file: "Вкажіть файл!"
		}
};
load_validator = $("#fLoad").validate(file_valid_options);
 
 
 
  var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FormSubmitResponse // функция, вызываемая при получении ответа
  };

  fHeader_ajaxForm = $("#fHeaderEdit").ajaxForm(form_options);
        
  // опции валидатора общей формы
  var form_valid_options = { 

		rules: {
			book: "required",
                        code: "required",
                        name_lgt: "required"
		},
		messages: {
			reg_date: "Вкажіть дату",
			book: "Вкажіть книгу",
                        code: "Вкажіть рахунок",
                        name_lgt: "Вкажіть пільгу"
		}
};

validator = $("#fHeaderEdit").validate(form_valid_options);

 
$("#dialog_editHeader").dialog({
			resizable: true,
		//	height:140,
                        width:750,
			modal: true,
                        autoOpen: false,
                        title:"Коригування"
});
 
 //---------------------------------------------------------------------------
 /*
 jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{caption:"Друкувати",
    onClickButton:function(){ 

        var postData = jQuery("#headers_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       $('#freps_params').find("#ffilename").attr('value',$("#pActionBar").find("#fname_file").val() ); 
       $('#freps_params').find("#fid_file").attr('value',id_file ); 
       
       $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Друк списка',
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
*/

//----------------------------------------------------------------------------

  $("#fHeaderEdit").find("#bt_reset").click( function() 
  {
    jQuery("#dialog_editHeader").dialog('close');                           
  });
  
//-----------------------------------------------------------------------------  
  
 jQuery("#btHouseSel").click( function() {

    createSmartGrid();
    
    smart_target_id = jQuery("#fid_house");
    smart_target_ident = jQuery("#fident");
    smart_target_addr = jQuery("#faddr_str");
    smart_target_book = jQuery("#fbook");

    jQuery("#grid_selsmart").css({'left': jQuery("#fident").offset().left+1, 'top': jQuery("#fident").offset().top+15});
    jQuery("#grid_selsmart").toggle( );
 });
  
  
//==============================================================================

  jQuery('#indic_table').jqGrid({
    url:'abon_en_smart_indic_data.php',
    editurl: 'abon_en_smart_indic_edit.php',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:200,
    width:400,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_smart', index:'id_smart', width:40, editable: false, align:'center',hidden:true},
    {name:'id_indic', index:'id_indic', width:40, editable: false, align:'center',hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    //{name:'id_meter', index:'id_meter', width:40, editable: false, align:'center',hidden:true},    
    
    {label:'ТУ',name:'tu', index:'tu', width:20, editable: false, align:'left',edittype:'text'},
    {label:'Зона А',name:'ind_a', index:'ind_a', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           
    {label:'Зона В',name:'ind_b', index:'ind_b', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           
    {label:'Зона C',name:'ind_c', index:'ind_c', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           
    {label:'Зона D',name:'ind_d', index:'ind_d', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           

    {label:'Поточні пок.',name:'indic', index:'indic', width:70, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           
    {label:'Попер. пок.',name:'p_indic', index:'p_indic', width:70, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           

    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},
    {label:'Рах.',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},
    //{label:'Книга/рах',name:'bookcode', index:'bookcode', width:40, editable: true, align:'left',edittype:'text'},
    
    {label:'Адреса',name:'address', index:'address', width:150, editable: true, align:'left',edittype:'text'},
    //{label:'№ ліч.',name:'num_meter',index:'num_meter', width:80, editable: true, align:'left',edittype:'text'},

    
    {label:'Дата',name:'ind_date', index:'ind_date', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
    {label:'Загр.',name:'is_load', index:'is_load', width:20, editable: true, align:'right',hidden:false,
                            formatter:'checkbox'},
    {label:'Ознака',name:'period_flag', index:'period_flag', width:40, editable: false, align:'center',
         edittype:'select',formatter:'select',editoptions:{value:lindicst},stype:'select',hidden:false
    }

    ],
    pager: '#indic_tablePager',
    autowidth: true,
    //shrinkToFit : false,
    rowNum:500,
 //   rowList:[50,100,200],
    sortname: 'tu',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Показники',
    //hiddengrid: false,
    hidegrid: false,   
    //pgbuttons: false,     // disable page control like next, back button
    //pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    postData:{'p_id':0},
    
    gridComplete:function(){

     if ($(this).getDataIDs().length > 0) 
     {      
       var first_id = parseInt($(this).getDataIDs()[0]);
       $(this).setSelection(first_id, true);
     }
     innerLayout.resizeAll();     
    },
    onSelectRow: function(id) { 
          cur_indic_id = id;
    }, 
    
    rowattr: function (rd) {

        if (rd.book == null)
            return {"style": "color:red !important;"};
        else
        {
          if (rd.period_flag == '1')
            return {"style": "color:gray !important;"};
        }    
    },      
    ondblClickRow: function(id){ 
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            
            indic_validator.resetForm();  //для сброса состояния валидатора
            $("#fIndicEdit").resetForm();
            $("#fIndicEdit").clearForm();
          
            $("#indic_table").jqGrid('GridToForm',gsr,"#fIndicEdit"); 
            $("#fIndicEdit").find("#foper").attr('value','edit'); 
            
            if ($("#fIndicEdit").find("#findic").attr('value')=='0')
            {
                $("#fIndicEdit").find("#findic").attr('value','')
            }
            cur_indic_id = id;

            $("#dialog_indicform").dialog('open');          
            
            if (r_edit==3)
            {
              $("#fIndicEdit").find("#bt_edit").prop('disabled', false);
            }
            else
            {
              $("#fIndicEdit").find("#bt_edit").prop('disabled', true);
            }
            
        
        }
        
    //     jQuery(this).editGridRow(id,LgtNormEditOptions);  
    } ,  

    loadError : function(xhr,st,err) {
      jQuery('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);
    }
  
  //  jsonReader : { repeatitems: false }

  }).navGrid('#indic_tablePager',
         {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ).jqGrid('filterToolbar','');; 

jQuery("#indic_tablePager_right").css("width","150px");


jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{caption:"Редагувати",
    id:"bt_smart_ind_edit",
    onClickButton:function(){ 

      if ($("#indic_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#indic_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            indic_validator.resetForm();  //для сброса состояния валидатора
            $("#fIndicEdit").resetForm();
            $("#fIndicEdit").clearForm();
          
            $("#indic_table").jqGrid('GridToForm',gsr,"#fIndicEdit"); 
            $("#fIndicEdit").find("#foper").attr('value','edit'); 
            
            if ($("#fIndicEdit").find("#findic").attr('value')=='0')
            {
                $("#fIndicEdit").find("#findic").attr('value','')
            }

            $("#dialog_indicform").dialog('open');          
            
            if (r_edit==3)
            {
              $("#fIndicEdit").find("#bt_edit").prop('disabled', false);
            }
            else
            {
              $("#fIndicEdit").find("#bt_edit").prop('disabled', true);
            }

        }
    } 
});

jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{caption:"Видалити",
        id:"bt_smart_ind_del",
	onClickButton:function(){ 

      if ($("#indic_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити запис?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                                      
                                        fIndic_ajaxForm[0].id.value = cur_indic_id;
                                        //fHeader_ajaxForm[0].change_date.value = cur_dt_change;
                                        fIndic_ajaxForm[0].oper.value = 'del';
                                        fIndic_ajaxForm.ajaxSubmit(form_options);   

					$( this ).dialog( "close" );                                    
				},
				"Відмінити": function() {
					$( this ).dialog( "close" );
				}
			}
		});
    
        jQuery("#dialog-confirm").dialog('open');   
          
        ;} 
});


//------------------редактирование одиночного показания ---------------------

 var indic_form_options = { 
    dataType:"json",
    beforeSubmit: IndFormBeforeSubmit, // функция, вызываемая перед передачей 
    success: IndFormSubmitResponse // функция, вызываемая при получении ответа
  };

$("#dialog_indicform").dialog({
			resizable: true,
                        width:750,
			modal: true,
                        autoOpen: false,
                        title:"Показники"
});
fIndic_ajaxForm = $("#fIndicEdit").ajaxForm(indic_form_options);
        
// опции валидатора общей формы
var indic_form_valid_options = { 

		rules: {
			indic: "required",
                        ind_date:"required"
		},
		messages: {
			ind_date: "Вкажіть дату",
                        indic: "Вкажіть показники!"
		}
};

indic_validator = $("#fIndicEdit").validate(indic_form_valid_options);


$("#fIndicEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_indicform").dialog('close');                           
});


//------------------------------------------------------------------------------
    //поиск абонента по книге/счету
    jQuery("#btPaccntFind").click( function() {

        vbook = $('#fIndicEdit').find('#fbook').attr('value');
        vcode = $('#fIndicEdit').find('#fcode').attr('value');

        var request = $.ajax({
            url: "abon_en_get_abon_id_data.php",
            type: "POST",
            data: {
                book : vbook,
                code: vcode,
                param: 2
            },
            dataType: "json"
        });

        request.done(function(data ) {  
        
            if (data.errcode!==undefined)
            {
                if(data.errcode==1)
                {
                    $('#fIndicEdit').find('#fid_paccnt').attr('value',data.id );
                    $('#fIndicEdit').find('#faddress').attr('value',data.errstr );    
                }

                if(data.errcode==2)
                {
                    $('#fIndicEdit').find('#fid_paccnt').attr('value','' );
                    $('#fIndicEdit').find('#faddress').attr('value',data.errstr );    
                }
            }
        });        
        request.fail(function(data ) {
            alert("error");
        });

    });

//----------------------------------------------------------------------------
    jQuery("#btPaccntSel").click( function() { 
   /*
        // $("#fpaccnt_sel_params").attr('target',"_blank" );           
        var ww = window.open("abon_en_main.php", "paccnt_win", "toolbar=0,width=900,height=600");
        document.paccnt_sel_params.submit();
        ww.focus();
        */
        createAbonGrid();
        
        abon_target_id = $('#fIndicEdit').find('#fid_paccnt');
        abon_target_addr = $('#fIndicEdit').find('#faddress');
        abon_target_book = $("#fIndicEdit").find('#fbook');
        abon_target_code = $("#fIndicEdit").find('#fcode');

       jQuery("#grid_selabon").css({'left': $('#fIndicEdit').find('#faddress').offset().left+1, 'top': $('#fIndicEdit').find('#faddress').offset().top+20});
       jQuery("#grid_selabon").toggle( );
       
    });
/*
  $('#fHeaderEdit input').keypress(function(e){
    if ( e.which == 13 ) return false;
  });   

  $('#fIndicEdit input').keypress(function(e){
    if ( e.which == 13 ) return false;
  });   
*/
  $('#fHeaderEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fHeaderEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 

  $('#fIndicEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fIndicEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 


if (r_edit!=3)
{
    $('#bt_smart_del').addClass('ui-state-disabled');
    $('#bt_smartall_del').addClass('ui-state-disabled');
    $('#bt_smart_ind_del').addClass('ui-state-disabled');
    
    $('#bt_load').prop('disabled', true);
    $('#bt_apply').prop('disabled', true);

}



});



function FileFormBeforeSubmit(formData, jqForm, options) { 

    if (form_edit_lock == 1) return false;
    submit_form = jqForm;
    //$('#load_in_progress').show();

    var queryString = $.param(formData);     
    $('#message_zone').append('Вот что мы передаем:' + queryString);  
    $('#message_zone').append("<br>");                 
    
    var btn = '';
    for (var i=0; i < formData.length; i++) { 
        if (formData[i].name =='submitButton') { 
           btn= formData[i].value; 
           submit_form[0].oper.value = btn;
        }  
    } 

    if((btn=='load')&&($('#fsmart_file').attr('value')=='' ))
    {
      $("#dialog-confirm").find("#dialog-text").html('Не вибрано файл для завантаження!');
      $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Вкажіть файл!',
        buttons: {
            "Ок ": function() {
                $( this ).dialog( "close" );
            }
        }
       });
    
       jQuery("#dialog-confirm").dialog('open');
       return false;
    }
    

    if(!submit_form.validate().form())  {return false;}
    else {
        
               jQuery("#calc_progress").dialog('open');
     
               elapsed_seconds = 0;
               timerId = setInterval(function() {
                   elapsed_seconds = elapsed_seconds + 1;
                   $('#progress_time').html(get_elapsed_time_string(elapsed_seconds));
               }, 1000);
       
               $("#progress_indicator").spin("large", "black");    
               form_edit_lock=1;
               return true;
    }

} ;

function FileFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;
             jQuery("#calc_progress").dialog('close');
             clearInterval(timerId); 
             $("#progress_indicator").spin(false);

             if(!errorInfo.hasOwnProperty('errcode')){
               $('#message_zone').append(errorInfo);  
               $('#message_zone').append('<br>');                 
               $('#message_zone').dialog('open');
               return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
              // $('#load_in_progress').hide();
               id_file = errorInfo.id;
               
               $('#fLoad').find("#fid_file").attr('value',id_file ); 
               $('#fLoad').find("#fname_file").attr('value',errorInfo.errstr ); 
               
               $('#headers_table').jqGrid('setGridParam',{postData:{'p_file':id_file}}).trigger('reloadGrid');
               //jQuery('#headers_table').trigger('reloadGrid');        
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              
               return [true,errorInfo.errstr]};              

             if (errorInfo.errcode==1) {
              // $('#load_in_progress').hide();

               //$('#headers_table').jqGrid('setGridParam',{postData:{'p_file':id_file}}).trigger('reloadGrid');
               jQuery('#headers_table').trigger('reloadGrid');   
               
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              
               return [true,errorInfo.errstr]};              


             if (errorInfo.errcode==2) {
              // $('#load_in_progress').hide();  
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};   

};

function FileFormSubmitError(  jqXHR,  textStatus,  errorThrown )
{
             errorInfo = jqXHR.responseText;
             form_edit_lock=0;
             
             jQuery("#calc_progress").dialog('close');
             clearInterval(timerId);
             $("#progress_indicator").spin(false); 

             $("#message_zone").html('');
             $('#message_zone').append('<p style="color:red;" > Ошибка! </p>');  
             $('#message_zone').append(errorInfo);  
             $('#message_zone').append('<br>');                 
             $('#message_zone').dialog('open');
//               return [true,errorInfo.errstr]

};

// обработчик, который вызываетя перед отправкой формы
function FormBeforeSubmit(formData, jqForm, options) { 

    if (form_edit_lock == 1) return false;
    submit_form = jqForm;

    var queryString = $.param(formData);     
    $('#message_zone').append('Вот что мы передаем:' + queryString);  
    $('#message_zone').append("<br>");                 
    
    var btn = '';
    for (var i=0; i < formData.length; i++) { 
        if (formData[i].name =='submitButton') { 
           btn= formData[i].value; 
           submit_form[0].oper.value = btn;
        } 
    } 

    if((btn=='edit')||(btn=='add'))
    {
       if(!submit_form.validate().form())  {return false; }
       else {
        form_edit_lock=1;   
        return true; 
       }
    }
    else {return true; }       
    //}
    
} ;

// обработчик ответа сервера после отправки формы
function FormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_editHeader").dialog('close');                           
               jQuery('#headers_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               var fid = jQuery("#fid").val();
               if(fid) 
               { 
                 jQuery("#headers_table").jqGrid('FormToGrid',fid,"#fHeaderEdit"); 
                 jQuery('#indic_table').trigger('reloadGrid'); 
               }  
               jQuery("#dialog_editHeader").dialog('close');                                            
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};   

};

function IndFormBeforeSubmit(formData, jqForm, options) { 

    if (form_edit_lock == 1) return false;
    submit_form = jqForm;

    var queryString = $.param(formData);     
    $('#message_zone').append('Вот что мы передаем:' + queryString);  
    $('#message_zone').append("<br>");                 
    
    var btn = '';
    for (var i=0; i < formData.length; i++) { 
        if (formData[i].name =='submitButton') { 
           btn= formData[i].value; 
           submit_form[0].oper.value = btn;
        } 
    } 

    if((btn=='edit')||(btn=='add'))
    {
       if(!submit_form.validate().form())  {return false;}
       else {
           form_edit_lock=1;
           return true; 
       }
    }
    else {return true;}       
    //}
    
} ;
function IndFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             
             if (errorInfo.errcode==1) {
                 
               jQuery("#indic_table").jqGrid('FormToGrid',cur_indic_id,"#fIndicEdit"); 
               //jQuery('#indic_table').trigger('reloadGrid');        
               
               jQuery("#dialog_indicform").dialog('close');                                            
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_indicform").dialog('close');                                            
               jQuery('#indic_table').trigger('reloadGrid'); 
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  

               return [true,errorInfo.errstr]};              

             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};   

};

 function get_elapsed_time_string(total_seconds) {
  function pretty_time_string(num) {
    return ( num < 10 ? "0" : "" ) + num;
  }

  var hours = Math.floor(total_seconds / 3600);
  total_seconds = total_seconds % 3600;

  var minutes = Math.floor(total_seconds / 60);
  total_seconds = total_seconds % 60;

  var seconds = Math.floor(total_seconds);

  // Pad the minutes and seconds with leading zeros, if required
  hours = pretty_time_string(hours);
  minutes = pretty_time_string(minutes);
  seconds = pretty_time_string(seconds);

  // Compose the string for display
  var currentTimeString = hours + ":" + minutes + ":" + seconds;

  return currentTimeString;
}

function SelectPaccntExternal(id, book, code, name, addr) {
    
        $('#fIndicEdit').find('#fid_paccnt').attr('value',id );
        $('#fIndicEdit').find('#fbook').attr('value',book );    
        $('#fIndicEdit').find('#fcode').attr('value',code );    
       // $('#fIndicEdit').find('#fbookcode').attr('value', book+'/'+code );    
        $('#fIndicEdit').find('#faddress').attr('value',addr );    
    
} 

function SelectLgtExternal(id, name, id_calc, name_calc, code) {
        $("#fid_lgt").attr('value',id );
        $("#fname_lgt").attr('value',name );    
        $("#fcode_lgt").attr('value',code );    
        
}
