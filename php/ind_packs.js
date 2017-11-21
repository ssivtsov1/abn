var cur_doc_id=0;
var cur_indic_id=0;
var validator = null;
var elapsed_seconds = 0;
var timerId ;
var spinner_opts;
var form_edit_lock=0;

jQuery(function(){ 
/*
   setTimeout(function(){
             jQuery('#accnt_table').trigger('reloadGrid');              
    },300);  
*/
  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");
  $("#fmmgg").datepicker( "setDate" , mmgg );

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
  //----------------------------------------------------------------------------  
  jQuery('#headers_table').jqGrid({
    url:     'ind_packs_data.php',
    editurl: '',
    datatype: 'json',
    mtype: 'POST',
    height:200,
    width:800,
    colNames:[],
    colModel :[ 
      {label:'Дата',name:'dt_pack', index:'dt_pack', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date', editrules:{required:true}},
      {label:'Номер',name:'num_pack', index:'num_pack', width:80,editable: true,edittype:"text",align:'left'},
      
      {label:'id_sector',name:'id_sector', index:'id_sector', width:40, editable: true, align:'right', hidden:true},                             
      {label:'Код дільн.',name:'code', index:'code', width:40, editable: true, align:'left',edittype:"text"},
      {label:'Дільниця',name:'sector', index:'sector', width:150, editable: true, align:'left',edittype:"text"},

      {label:'id_runner',name:'id_runner', index:'id_runner', width:40, editable: true, align:'right', hidden:true},                             
      {label:"Кур'єр / контролер",name:'runner', index:'runner', width:120, editable: true, align:'left'},                       

      {label:'id_operator',name:'id_operator', index:'id_operator', width:40, editable: true, align:'right', hidden:true},                             
      {label:"Оператор",name:'operator', index:'operator', width:100, editable: true, align:'left'},                       

      {label:'Операція',name:'id_ioper', index:'id_ioper', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lioper},stype:'text'},                       

      {label:'Період',name:'work_period', index:'work_period', width:80, editable: true, align:'left',edittype:'text', hidden:false,formatter:'date'},
      {name:'dt_input', index:'dt_input', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {name:'Виконавець', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:false},        
      {label:'Розр.',name:'fcalc', index:'fcalc', width:40, editable: false, align:'left',edittype:"text"},
      {name:'id_pack', index:'id_pack', width:40, editable: false, align:'center', key:true, hidden:false}        
                            
    ],
    pager: '#headers_tablePager',
    autowidth: true,
    rowNum:100,
    rowList:[20,50,100,300,500],
    sortname: 'dt_pack',
    sortorder: 'desc',
    viewrecords: true,
    gridview: true,
    caption: 'Відомості',
    //hiddengrid: false,
    hidegrid: false,    
    postData:{'p_mmgg': mmgg},
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
    
    onSelectRow: function(id) { 
        if (cur_doc_id != id)
        {    
          cur_doc_id = id;
          jQuery('#indic_table').jqGrid('setGridParam',{datatype:'json','postData':{'p_id':cur_doc_id}}).trigger('reloadGrid');        
        }  
      
    },
    
    ondblClickRow: function(id){ 
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            
            // jQuery(this).editGridRow(id,{width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
            validator.resetForm();  //для сброса состояния валидатора
            $("#fHeaderEdit").resetForm();
            $("#fHeaderEdit").clearForm();
          
            $("#headers_table").jqGrid('GridToForm',gsr,"#fHeaderEdit"); 
            $("#fHeaderEdit").find("#foper").attr('value','edit');              
            cur_doc_id = id;

            $("#fHeaderEdit").find("#bt_add").hide();
            $("#fHeaderEdit").find("#bt_edit").show();   
            $("#fHeaderEdit").find("#bt_refresh").show();   
            $("#dialog_editform").dialog('open');          
        
            if (r_edit==3)
            {
              $("#fHeaderEdit").find("#bt_edit").prop('disabled', false);
              $("#fHeaderEdit").find("#bt_refresh").prop('disabled', false);
            }
            else
            {
              $("#fHeaderEdit").find("#bt_edit").prop('disabled', true);
              $("#fHeaderEdit").find("#bt_refresh").prop('disabled', true);
            }
        
            $("#fHeaderEdit").find("#fnum_pack").focus();
        }

     } ,  

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#headers_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ).jqGrid('filterToolbar',''); 

jQuery("#headers_tablePager_right").css("width","150px");

//==============================================================================

  jQuery('#indic_table').jqGrid({
    url:'ind_packs_indic_data.php',
    editurl: 'ind_pack_indic_edit.php',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:200,
    width:400,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_pack', index:'id_pack', width:40, editable: false, align:'center',hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    {name:'id_meter', index:'id_meter', width:40, editable: false, align:'center',hidden:true},    
    {name:'id_type_meter', index:'id_type_meter', width:40, editable: false, align:'center',hidden:true},    
    {name:'id_p_indic', index:'id_p_indic', width:40, editable: false, align:'center',hidden:true},    
    
    {label:'#',name:'status', index:'status', width:20, editable: false, align:'left',edittype:'text',sortable:false},
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},                
    {label:'Адреса',name:'address', index:'address', width:150, editable: true, align:'left',edittype:'text'}, 
    {label:'Абонент',name:'abon', index:'abon', width:100, editable: true, align:'left',edittype:'text'},
    {label:'№ ліч.',name:'num_meter',index:'num_meter', width:80, editable: true, align:'left',edittype:'text'},
    {label:'Тип ліч.',name:'type_meter', index:'type_meter', width:80, editable: true, align:'left',edittype:'text'},                
    {label:'Розр. ліч.',name:'carry', index:'carry', width:40, editable: true, align:'left',edittype:'text'},                    
    {label:'К.тр',name:'k_tr', index:'k_tr', width:40, editable: true, align:'left',edittype:'text'},                        
    {label:'Зона',name:'id_zone', index:'id_zone', width:60, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text'},                       
 
    {label:'Попер.пок.',name:'p_indic', index:'p_indic', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           

    {label:'Дата попер.',name:'dt_p_indic', index:'dt_p_indic', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Поточні пок.',name:'indic', index:'indic', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           
    
    {label:'Дата',name:'dt_indic', index:'dt_indic', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},


    {label:'Спожито',name:'demand', index:'demand', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text'},           
                        
    {label:'Тип показників',name:'id_operation', index:'id_operation', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lindicoper}  },                    
                        
    {label:'Дійсні пок.',name:'indic_real', index:'indic_real', width:80, editable: true, align:'right',hidden:false},
                        
    {label:'Пільга',name:'lgt_code', index:'lgt_code', width:40, editable: false, align:'right',hidden:false,
                            sortable:false},

//    {name:'work_period', index:'work_period', width:80, editable: true, align:'left',edittype:'text', hidden:true},
    {label:'dt',name:'dt_input', index:'dt_input', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
    {label:'Оператор', name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true}

    ],
    pager: '#indic_tablePager',
    autowidth: true,
    //shrinkToFit : false,
    rowNum:500,
 //   rowList:[50,100,200],
    sortname: 'address',
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
    },
    onSelectRow: function(id) { 
          cur_indic_id = id;
    },
    
    ondblClickRow: function(id){ 
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            
            // jQuery(this).editGridRow(id,{width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
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
            
            $("#fIndicEdit").find("#findic").focus();
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
//==============================================================================

//jQuery("#headers_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
//      jQuery(this).editGridRow(id,{width:300,height:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});}} );


//jQuery("#accnt_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
//      jQuery(this).editGridRow(id,{width:300,height:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});}} );



$("#message_zone").dialog({autoOpen: false});
$("#calc_progress").dialog({autoOpen: false, resizable: false});
$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
$("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {jQuery("#message_zone").html('');});

jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});

jQuery("#fHeaderEdit :input").addClass("ui-widget-content ui-corner-all");
jQuery("#fIndicEdit :input").addClass("ui-widget-content ui-corner-all");


$("#dialog_editform").dialog({
			resizable: true,
		//	height:140,
                        width:600,
			modal: true,
                        autoOpen: false,
                        title:"Відомість"
});
$("#dialog_indicform").dialog({
			resizable: true,
                        width:750,
			modal: true,
                        autoOpen: false,
                        title:"Показники"
});

//------------------------------------------------------------------------------
$.ajaxSetup({type: "POST",      dataType: "json"});

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
	,	center__paneSelector:	"#pmain_center"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true

	});

 innerLayout = $("#pmain_center").layout({
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
            jQuery("#headers_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#headers_table").jqGrid('setGridHeight',_pane.innerHeight()-130);
        }
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#indic_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#indic_table").jqGrid('setGridHeight',_pane.innerHeight()-100);
        }

	});

        outerLayout.resizeAll();
        outerLayout.close('south');             
        
        
 var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FormSubmitResponse // функция, вызываемая при получении ответа
  };

fHeader_ajaxForm = $("#fHeaderEdit").ajaxForm(form_options);
        
// опции валидатора общей формы
var form_valid_options = { 

		rules: {
			dt_pack: "required",
                        sector:"required"
                        //runner: "required"
		},
		messages: {
			dt_pack: "Вкажіть дату",
                        sector:"Вкажіть дільницю"
                    //    runner: "Вкажіть працівника!"
		}
};

validator = $("#fHeaderEdit").validate(form_valid_options);


$("#fHeaderEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});
   
   
$("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       
       $('#headers_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
       
});
   
jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{caption:"Нова",
    id:"btn_header_new",
    onClickButton:function(){ 

        validator.resetForm();
        $("#fHeaderEdit").resetForm();
        $("#fHeaderEdit").clearForm();
          
        $("#fHeaderEdit").find("#fid").attr('value',-1 );    
        $("#fHeaderEdit").find("#foper").attr('value','add');              
        $("#fHeaderEdit").find("#fid_ioper").attr('value',1 );    
          
        $("#fHeaderEdit").find("#bt_add").show();
        $("#fHeaderEdit").find("#bt_edit").hide();            
        $("#fHeaderEdit").find("#bt_refresh").hide();            
        jQuery("#dialog_editform").dialog('open'); 
        $("#fHeaderEdit").find("#fnum_pack").focus();
            
    } 
});

jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{caption:"Редагувати",
    id:"btn_header_edit",
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

            $("#fHeaderEdit").find("#bt_add").hide();
            $("#fHeaderEdit").find("#bt_edit").show();   
            $("#fHeaderEdit").find("#bt_refresh").show();            
            $("#dialog_editform").dialog('open');          
            $("#fHeaderEdit").find("#fnum_pack").focus();
        }
    } 
});

jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{caption:"Видалити",
        id:"btn_header_del",
	onClickButton:function(){ 

      if ($("#headers_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити документ?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                                      
                                        jQuery("#calc_progress").dialog('open');
                                    
                                        elapsed_seconds = 0;
                                        timerId = setInterval(function() {
                                            elapsed_seconds = elapsed_seconds + 1;
                                            $('#progress_time').html(get_elapsed_time_string(elapsed_seconds));
                                        }, 1000);
        
                                        $("#progress_indicator").spin("large", "black");    
                                                      
                                                      
                                        fHeader_ajaxForm[0].id_pack.value = cur_doc_id;
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

jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{caption:"Показники",
        id:"btn_header_indic",
	onClickButton:function(){ 
            
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 

            validator.resetForm();  //для сброса состояния валидатора
            $("#fHeaderEdit").resetForm();
            $("#fHeaderEdit").clearForm();
          
            $("#headers_table").jqGrid('GridToForm',gsr,"#fHeaderEdit"); 

            $("#fHeaderEdit").attr('target',"ind_input_win" );           
            $("#fHeaderEdit").attr('action',"ind_packs_input.php" );               
    
            var ww = window.open("ind_packs_input.php", "ind_input_win", "toolbar=0,width=900,height=600");
            
            //$("#fHeaderEdit").attr("action","ind_packs_input.php");
            //$("#fHeaderEdit").attr('target',"_blank" );           

            
            document.fHeaderEdit.submit();
            ww.focus();
     
            $("#fHeaderEdit").attr('target',"" );           
            $("#fHeaderEdit").attr('action',"ind_packs_edit.php" );               
        }

     } 
});

jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{caption:"Друк",
    id:"btn_header_print",
    onClickButton:function(){ 
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            validator.resetForm();  //для сброса состояния валидатора
            $("#fHeaderEdit").resetForm();
            $("#fHeaderEdit").clearForm();
          
            $("#headers_table").jqGrid('GridToForm',gsr,"#fHeaderEdit"); 

            //$("#fHeaderEdit").attr('target',"ind_print_win" );           
            $("#fHeaderEdit").attr('target',"_blank" );     
            if (id_res==310)
               $("#fHeaderEdit").attr('action',"ind_packs_print.php" );               
            else
               $("#fHeaderEdit").attr('action',"ind_packs_print_res.php" );               
           
            //var ww = window.open("ind_packs_print.php", "ind_print_win", "width=800,height=600");
            //var ww = window.open("ind_packs_print.php", '_blank');
            document.fHeaderEdit.submit();
            //ww.focus();
     
            $("#fHeaderEdit").attr('target',"" );           
            $("#fHeaderEdit").attr('action',"ind_packs_edit.php" );               
        }
    } 
});

jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{caption:"Рахунки",
    id:"btn_header_bills",
    onClickButton:function(){ 
        gsr = jQuery("#headers_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 

            jQuery("#dialog-confirm").find("#dialog-text").html('Формувати рахунки по поточній відомості?');
    
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Формування рахунків',
                buttons: {
                    "Формувати": function() {
                                        
                        jQuery("#calc_progress").dialog('open');
     
                        elapsed_seconds = 0;
                        timerId = setInterval(function() {
                            elapsed_seconds = elapsed_seconds + 1;
                            $('#progress_time').html(get_elapsed_time_string(elapsed_seconds));
                        }, 1000);
        
                        $("#progress_indicator").spin("large", "black");    



                        var request = $.ajax({
                            url: "bill_calc_area_edit.php",
                            type: "POST",
                            data: {
                                id : cur_doc_id
                            },
                            dataType: "json"
                        });

                        request.done(function(data ) {  
        
                            jQuery("#calc_progress").dialog('close');
                            clearInterval(timerId);
                            $("#progress_indicator").spin(false);
        
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");                 
                                //jQuery("#message_zone").dialog('open');
            
                                if(data.errcode<=0) 
                                {
                                    if (data.id =1 )
                                    {
                                        alert("Рахунки зформовано!");
                                        jQuery('#bill_table').trigger('reloadGrid');                      
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

        }
    } 
});

if (r_edit!=3)
{
  $('#btn_header_bills').addClass('ui-state-disabled');
  $('#btn_header_indic').addClass('ui-state-disabled');
  $('#btn_header_new').addClass('ui-state-disabled');
  $('#btn_header_edit').addClass('ui-state-disabled');
  $('#btn_header_del').addClass('ui-state-disabled');
}
$('#btn_header_bills').addClass('ui-state-disabled');

//=========================================================================

//выбор участка
jQuery("#btSectorSel").click( function() {

    sector_target_id =jQuery("#fid_sector");
    sector_target_name=jQuery("#fsector");
    sector_target_runner_id=jQuery("#fid_runner");
    sector_target_runner_name=jQuery("#frunner");

    createSectorGrid(); 
    jQuery("#grid_selsector").css({'left': $(this).position().left+1, 'top': $(this).position().top+20});
    jQuery("#grid_selsector").toggle( );
    jQuery("#grid_selsector").find("input[type='text']:visible:enabled:first").focus();    
});


   jQuery("#btRunnerSel").click( function() { 

     createPersonGrid($("#fHeaderEdit").find("#fid_runner").val());
     person_target_id=$("#fHeaderEdit").find("#fid_runner");
     person_target_name =  $("#fHeaderEdit").find("#frunner");
     person_target_prof = 0;
    
     jQuery("#grid_selperson").css({'left': $("#fHeaderEdit").find("#frunner").offset().left+1, 'top': $("#fHeaderEdit").find("#frunner").offset().top+20});
     jQuery("#grid_selperson").toggle( );

/*
     if ($("#fHeaderEdit").find("#fid_runner").val() !='')
       $("#fcntrl_sel_params_id_cntrl").attr('value', $("#fHeaderEdit").find("#fid_runner").val() );  
     else  
       $("#fcntrl_sel_params_id_cntrl").attr('value', '0' );  
   
     var www = window.open("staff_list.php", "cntrl_win", "toolbar=0,width=800,height=600");
     document.cntrl_sel_params.submit();
     www.focus();
*/     
   });

//------------------редактирование одиночного показания ---------------------

 var indic_form_options = { 
    dataType:"json",
    beforeSubmit: IndFormBeforeSubmit, // функция, вызываемая перед передачей 
    success: IndFormSubmitResponse // функция, вызываемая при получении ответа
  };

fIndic_ajaxForm = $("#fIndicEdit").ajaxForm(indic_form_options);
        
// опции валидатора общей формы
var indic_form_valid_options = { 

		rules: {
			indic: "required",
                        dt_indic:"required"
		},
		messages: {
			dt_indic: "Вкажіть дату",
                        indic: "Вкажіть показники!"
		}
};

indic_validator = $("#fIndicEdit").validate(indic_form_valid_options);


$("#fIndicEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_indicform").dialog('close');                           
});

jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{caption:"Редагувати",
    id:"btn_indic_edit",
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

            $("#dialog_indicform").dialog('open'); 
            $("#fIndicEdit").find("#findic").focus();

        }
    } 
});

jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{caption:"Видалити",
        id:"btn_indic_del",
	onClickButton:function(){ 

      if ($("#indic_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити показники?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                                      
                                        fIndic_ajaxForm[0].id_pack.value = cur_doc_id;
                                        fIndic_ajaxForm[0].id.value = cur_indic_id;
                                        //fHeader_ajaxForm[0].change_date.value = cur_dt_change;
                                        fIndic_ajaxForm[0].oper.value = 'del';
                                        fIndic_ajaxForm.ajaxSubmit(indic_form_options);   

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

if (r_edit!=3)
{
  $('#btn_indic_edit').addClass('ui-state-disabled');
  $('#btn_indic_del').addClass('ui-state-disabled');
}


$("#fIndicEdit").find("#findic").change(function () {
    
                
    var p_ind = parseFloat(jQuery("#indic_table").jqGrid('getCell',cur_indic_id,'p_indic'));
    var k_tr = parseFloat(jQuery("#indic_table").jqGrid('getCell',cur_indic_id,'k_tr'));
    //var dt_ind = jQuery("#indic_table").jqGrid('getCell',cur_indic_id,iCol+1);
    var carry = parseFloat(jQuery("#indic_table").jqGrid('getCell',cur_indic_id,'carry'));
    var ind = parseFloat($("#fIndicEdit").find("#findic").val());
    //var length = dt_ind.length;
    var dem=0;
                
    if (Math.round(ind).toString().length>carry)    
    {
        //jQuery('#indic_table').setCell(rowid,name,'','err_column_class');  
                        
        $("#fIndEdit").find("#fdemand").attr('value','');              
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
            //if (dem > 1000)
            //{
            //    dem = 0;
            //jQuery('#indic_table').setCell(rowid,name,'','err_column_class');  
            //}
        }
                      
        $("#fIndicEdit").find("#fdemand").attr('value',dem);
                    
    //jQuery('#indic_table').setCell(rowid,name,'','mod_column_class');
    }
 
});


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

$("#show_peoples").click( function() {
     jQuery("#indic_table").jqGrid('showCol',["user_name"]);
     jQuery("#indic_table").jqGrid('showCol',["dt_input"]);
});


// обработчик, который вызываетя перед отправкой формы
function FormBeforeSubmit(formData, jqForm, options) { 

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

    if((btn=='edit')||(btn=='add')||(btn=='refresh'))
    {
       if (form_edit_lock == 1) return false; 
       if(!submit_form.validate().form())  {return false;}
       else {
           
            $("#fHeaderEdit").find("#bt_add").attr("disabled", true);
            $("#fHeaderEdit").find("#bt_reset").attr("disabled", true);
            $("#fHeaderEdit").find("#bt_refresh").attr("disabled", true);
            $("#fHeaderEdit").find("#lwait").show();
            
            if (btn=='refresh')
            {

               $("#dialog-changedate").find("#fdate_change").attr("value", $("#fHeaderEdit").find("#fdt_pack").attr("value"));
               $("#dialog-changedate").dialog({ 
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
			buttons: {
				"Ok": function() {
                                        
                                       var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
                                       submit_form[0].change_date.value = cur_dt_change;
                                       submit_form.ajaxSubmit(form_options);         
                                       form_edit_lock=1; 
					$( this ).dialog( "close" );
				},
				"Отмена": function() {
                                    
                                        $("#fHeaderEdit").find("#bt_add").attr("disabled", false);
                                        $("#fHeaderEdit").find("#bt_reset").attr("disabled", false);
                                        $("#fHeaderEdit").find("#bt_refresh").attr("disabled", false);
                                        $("#fHeaderEdit").find("#lwait").hide();
                                    
					$( this ).dialog( "close" );
				}
			}

                });
                
                $("#dialog-changedate").dialog('open');
                return false; 
                
            }
            
            form_edit_lock=1;
            return true; 

       }
    }
    else { return true; }
    //}
    
} ;

// обработчик ответа сервера после отправки формы
function FormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;
             
              $("#fHeaderEdit").find("#bt_add").attr("disabled", false);
              $("#fHeaderEdit").find("#bt_reset").attr("disabled", false);
              $("#fHeaderEdit").find("#bt_refresh").attr("disabled", false);
              $("#fHeaderEdit").find("#lwait").hide();
             

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#calc_progress").dialog('close');
               clearInterval(timerId);
               $("#progress_indicator").spin(false);                 
                 
               jQuery("#dialog_editform").dialog('close');                           
               jQuery('#headers_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              

             if (errorInfo.errcode==-2) { //refresh indication table
                 
               jQuery("#dialog_editform").dialog('close');                           
               jQuery('#indic_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              

             if (errorInfo.errcode==1) {
                 
               var fid = jQuery("#fid").val();
               if(fid) 
               { 
                 jQuery("#headers_table").jqGrid('FormToGrid',fid,"#fHeaderEdit"); 
               }  
               
               
               //jQuery('#headers_table').trigger('reloadGrid');        
               
               jQuery("#dialog_editform").dialog('close');                                            
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

}); 

function SelectPersonExternal(id, name) {
        $("#fHeaderEdit").find("#fid_runner").attr('value',id );
        $("#fHeaderEdit").find("#frunner").attr('value',name );    
    
}


function RefreshIndicExternal(id_doc)
{
  if( cur_doc_id == id_doc)
  {
        jQuery('#indic_table').jqGrid('setGridParam',{'postData':{'p_id':cur_doc_id}}).trigger('reloadGrid');        
  }
    
}


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