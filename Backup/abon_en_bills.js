var edit_row_id=0;
var elapsed_seconds = 0;
var timerId ;
var spinner_opts;
var edit_validator= null;
var id_pref = 10; 
var form_edit_lock=0;
var id_sector_filter =0;

$.validator.methods.number = function (value, element) {
    return this.optional(element) || /^-?(?:\d+|\d{1,3}(?:[\s\.,]\d{3})+)(?:[\.,]\d+)?$/.test(value);
}

jQuery(function(){ 


  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
		buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");
    
  //dt_mmgg = Date.parse( mmgg, "dd.MM.yyyy");
  //str_mmgg = dt_mmgg.toString("dd.MM.yyyy");
  //$("#fmmgg").datepicker( "setDate" , dt_mmgg.toString("dd.MM.yyyy") );
  $("#fmmgg").datepicker( "setDate" , mmgg );

  $('#fid_pref').val('10');
  

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


  jQuery('#bill_table').jqGrid({
    url:'abon_en_bills_data.php',
    editurl: '',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    //width:800,
    autowidth: true,
    scroll: 0,
    colNames:[],
    colModel :[ 

    {name:'id_doc', index:'id_doc', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},                
    {label:'Абонент',name:'abon', index:'abon', width:150, editable: true, align:'left',edittype:'text'},

    //{label:'Місто/село',name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text'},                    
    {label:'Дільниця',name:'sector', index:'sector', width:100, editable: true, align:'left',edittype:'text'},                        
    {label:'Прац.РЕМ',name:'rem_worker', index:'rem_worker', width:20, editable: true, align:'left',edittype:'text'},                        
    {label:'Адреса',name:'addr', index:'addr', width:150, editable: true, align:'left',edittype:'text',hidden:true},                            
    
    {label:'Місто',name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text', hidden:town_hidden},
    {label:'Вулиця',name:'street', index:'street', width:100, editable: true, align:'left',edittype:'text'},
    {label:'Буд',name:'house', index:'house', width:40, editable: true, align:'left',edittype:'text'},
    {label:'Корп',name:'korp', index:'korp', width:30, editable: true, align:'left',edittype:'text'},
    {label:'Кв.',name:'flat', index:'flat', width:40, editable: true, align:'left',edittype:'text'},
    
    {label:'№ .',name:'reg_num', index:'reg_num', width:40, editable: true, align:'left',edittype:'text', hidden:true},            
    {label:'Дата',name:'reg_date', index:'reg_date', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
    
    {label:'Тип док.',name:'idk_doc', index:'idk_doc', width:40, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lidk_doc},stype:'select'},                       
    
    {label:'Тип нарах.',name:'id_pref', index:'id_pref', width:40, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lid_pref},stype:'select',hidden:true},

    {label:'Квтг',name:'demand', index:'demand', width:50, editable: true, align:'right',hidden:false,
                            edittype:'text'},

    {label:'Сума нар.,грн',name:'value_calc', index:'value_calc', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
                        
    {label:'Пільги,грн',name:'value_lgt', index:'value_lgt', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           

    {label:'Субс.,грн',name:'value_subs', index:'value_subs', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number',hidden:true},           

    {label:'Сума,грн',name:'value', index:'value', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    {label:'ПДВ,грн',name:'value_tax', index:'value_tax', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    
    {label:'Період форм.',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', formatter:'date', hidden:false},
    {label:'Період спож.',name:'mmgg_bill', index:'mmgg_bill', width:80, editable: true, align:'left',edittype:'text', formatter:'date', hidden:false},
    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},
    {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
    {label:'Закр.',name:'flock', index:'flock', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}}

    ],
    pager: '#bill_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500,1000,5000],
    sortname: 'code',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Рахунки',
    //hiddengrid: false,
    hidegrid: false,
    postData:{'p_mmgg': mmgg, 'p_id_pref': id_pref, 'p_id_sector': id_sector_filter },
    jsonReader : {repeatitems: false},

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
      //jQuery(this).editGridRow(id,{width:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
    
      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { /*
            edit_row_id = id;
            edit_validator.resetForm();  //для сброса состояния валидатора
            $("#fBillEdit").resetForm();
            $("#fBillEdit").clearForm();
          
            $("#bill_table").jqGrid('GridToForm',gsr,"#fBillEdit"); 
            $("#fBillEdit").find("#foper").attr('value','edit');              

            $("#fBillEdit").find("#bt_add").hide();
            $("#fBillEdit").find("#bt_edit").show();   
            $("#dialog_billedit").dialog('open');          
        */
            jQuery('#bill_info1_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': edit_row_id}}).trigger('reloadGrid');        
            jQuery('#bill_info2_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': edit_row_id}}).trigger('reloadGrid');        
            jQuery('#bill_info3_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': edit_row_id}}).trigger('reloadGrid');
            jQuery('#bill_info4_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': edit_row_id}}).trigger('reloadGrid');

            $("#bill_info").dialog('option', 'title', 'Рахунок '+edit_row_id );
            $("#bill_info").dialog('open');          
                   
      } 
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#bill_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

jQuery("#bill_table").jqGrid('filterToolbar','');
jQuery("#bill_tablePager_right").css("width","150px");

jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"Друк один",
	onClickButton:function(){ 


       if((jQuery("#bill_table").jqGrid('getCell',edit_row_id,'idk_doc')!=200)&&
          (jQuery("#bill_table").jqGrid('getCell',edit_row_id,'idk_doc')!=220)||
          (jQuery("#bill_table").jqGrid('getCell',edit_row_id,'value_calc')<0)||
          (jQuery("#bill_table").jqGrid('getCell',edit_row_id,'id_pref')!=10))
       {
           return;
       }   


       //var filters = jQuery("#bill_table").jqGrid('getGridParam', 'postData').filters;
       //var bills = new Array();
       //bills[0] = edit_row_id;
       
       //var json_str = JSON.stringify(bills);

       $("#fprint_params").attr('action', 'bill_print_one.php');
       $("#fprint_params").find("#pid_bill").attr('value',edit_row_id ); 


       //$("#fprint_params").find("#pbill_list").attr('value',json_str ); 
       $("#fprint_params").find("#pcaption").attr('value', '');
       
       $("#fprint_params").attr('target',"_blank" );           
       document.print_params.submit();
  
       ;} 
});


jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"Друк всі",
	onClickButton:function(){ 

       //var filters = jQuery("#bill_table").jqGrid('getGridParam', 'postData').filters;
       var rows = $('#bill_table').getDataIDs();
       var json_str = JSON.stringify(rows);

       $("#fprint_params").attr('action', 'bill_print.php');
       $("#fprint_params").find("#pid_bill").attr('value','' ); 
       $("#fprint_params").find("#pbill_list").attr('value',json_str ); 
       $("#fprint_params").find("#pcaption").attr('value', '');
       
       $("#fprint_params").attr('target',"_blank" );           
       document.print_params.submit();
  
       ;} 
});




jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"+",
	onClickButton:function(){ 

        createBillCache();

      // jQuery("#grid_billcache").css({'left': 100, 'top': 100});
       jQuery("#grid_billcache").show( );

       var request = $.ajax({
               url: "bill_cache_edit.php",
               type: "POST",
               data: {
                    id_doc : edit_row_id,
                    oper: 'add'
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
            $("#bill_table").jqGrid('setGridWidth',$pane.innerWidth()-15);
            $("#bill_table").jqGrid('setGridHeight',$pane.innerHeight()-143);
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
    jQuery("#fBillEdit :input").addClass("ui-widget-content ui-corner-all");
    
   
    
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       id_pref = $("#pActionBar").find("#fid_pref").val();  
       
       $('#bill_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg, 'p_id_pref': id_pref}}).trigger('reloadGrid');
       
   });

//-------------------------
$("#dialog_billedit").dialog({
			resizable: true,
		//	height:140,
                        width:600,
			modal: true,
                        autoOpen: false,
                        title:"Редагування рахунку"
});

var edit_form_options = { 
    dataType:"json",
    beforeSubmit: EditFormBeforeSubmit, // функция, вызываемая перед передачей 
    success: EditFormSubmitResponse // функция, вызываемая при получении ответа
  };

fEdit_ajaxForm = $("#fBillEdit").ajaxForm(edit_form_options);
        
// опции валидатора общей формы
var editform_valid_options = { 

		rules: {
			reg_num: "required",
                        reg_date: "required",
                        value: {required:true,number:true},
                        value_tax: {required:true,number:true}
		},
		messages: {
			reg_num: "Вкажіть номер",
                        reg_date: "Вкажіть Дату",
                        value: {required:"Вкажіть суму",number:"Повинно бути число!"},
                        value_tax: {required:"Вкажіть суму ПДВ",number:"Повинно бути число!"}
		}
};


edit_validator = $("#fBillEdit").validate(editform_valid_options);


$("#fBillEdit").find("#bt_reset").click( function()  
{
  jQuery("#dialog_billedit").dialog('close');                           
});
//------------------------------------------
    jQuery("#btPaccntSel").click( function() { 
   /*
        var ww = window.open("abon_en_main.php", "paccnt_win", "toolbar=0,width=900,height=600");
        document.paccnt_sel_params.submit();
        ww.focus();
*/        
        createAbonGrid();
        
        abon_target_id = $("#fBillEdit").find('#fid_paccnt');
        abon_target_name = $("#fBillEdit").find('#fabon');
        abon_target_book = $("#fBillEdit").find('#fbook');
        abon_target_code = $("#fBillEdit").find('#fcode');

       jQuery("#grid_selabon").css({'left': $("#fBillEdit").find('#fabon').offset().left+1, 'top': $("#fBillEdit").find('#fabon').offset().top+20});
       jQuery("#grid_selabon").toggle( );

        
    });

//------------------------------------------------------------------------------
jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"Додати",
    id:"btn_bill_new",
    onClickButton:function(){ 

            edit_validator.resetForm();  //для сброса состояния валидатора
            $("#fBillEdit").resetForm();
            $("#fBillEdit").clearForm();
          
            //$("#bill_table").jqGrid('GridToForm',gsr,"#fBillEdit"); 
            $("#fBillEdit").find("#fid").attr('value','-1');
            $("#fBillEdit").find("#foper").attr('value','add');
            
            $("#fBillEdit").find("#fmmgg_b").attr('value',mmgg);
            $("#fBillEdit").find("#fmmgg_bill").attr('value',mmgg);

            $("#fBillEdit").find("#bt_add").show();
            $("#fBillEdit").find("#bt_edit").hide();   
            $("#dialog_billedit").dialog('open');          

    } 
});
//------------------------------------------------------------------------------

jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"Редагувати",
    id:"btn_bill_edit",
    onClickButton:function(){ 

      if ($("#bill_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#bill_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            edit_validator.resetForm();  //для сброса состояния валидатора
            $("#fBillEdit").resetForm();
            $("#fBillEdit").clearForm();
          
            $("#bill_table").jqGrid('GridToForm',gsr,"#fBillEdit"); 
            $("#fBillEdit").find("#foper").attr('value','edit');              

            $("#fBillEdit").find("#bt_add").hide();
            $("#fBillEdit").find("#bt_edit").show();   
            $("#dialog_billedit").dialog('open');          
        }
    } 
});
//------------------------------------------------------------------------------

jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"Видалити",
        id:"btn_bill_del",
	onClickButton:function(){ 

      if ($("#bill_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити рахунок?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                                      
                                        fEdit_ajaxForm[0].id_doc.value = edit_row_id;
                                        fEdit_ajaxForm[0].oper.value = 'del';
                                        fEdit_ajaxForm.ajaxSubmit(edit_form_options);   

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
  $('#btn_bill_del').addClass('ui-state-disabled');
  $('#btn_bill_new').addClass('ui-state-disabled');
  $('#btn_bill_edit').addClass('ui-state-disabled');
  $("#pActionBar").find("#bt_calc").prop('disabled', true);
}

//------------------------------------------
jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"Деталі",
    onClickButton:function(){ 

      if ($("#bill_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#bill_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            jQuery('#bill_info1_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': edit_row_id}}).trigger('reloadGrid');        
            jQuery('#bill_info2_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': edit_row_id}}).trigger('reloadGrid');        
            jQuery('#bill_info3_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': edit_row_id}}).trigger('reloadGrid');        
            jQuery('#bill_info4_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': edit_row_id}}).trigger('reloadGrid');  
            
            $("#bill_info").dialog('option', 'title', 'Рахунок '+edit_row_id );
            
            $("#bill_info").dialog('open');          
            
        }
    } 
});

//----------------------------------------------

$("#pActionBar").find("#bt_calc").click( function(){ 

    jQuery("#dialog-confirm").find("#dialog-text").html('Формувати рахунки?');
    
    $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Формування рахунків',
        buttons: {
            "Формувати": function() {
                                        
                var cur_mmgg = jQuery("#fmmgg").val();

                jQuery("#calc_progress").dialog('open');
     
                elapsed_seconds = 0;
                timerId = setInterval(function() {
                    elapsed_seconds = elapsed_seconds + 1;
                    $('#progress_time').html(get_elapsed_time_string(elapsed_seconds));
                }, 1000);
        
                $("#progress_indicator").spin("large", "black");    



                var request = $.ajax({
                    url: "bill_calc_all_edit.php",
                    type: "POST",
                    data: {
                        mmgg : cur_mmgg
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


});


$('#fBillEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fBillEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
 });


jQuery("#btSectorSel").click( function() {

     sector_target_id =jQuery("#fid_sector");
     sector_target_name=jQuery("#fsector");
     sector_target_runner_id=null;
     sector_target_runner_name=null;

     createSectorGrid(); 
     jQuery("#grid_selsector").css({'left': jQuery("#fsector").offset().left+1,'top': jQuery("#fsector").offset().top+20});
     jQuery("#grid_selsector").toggle( );
});

$("#fsector").change(function() {
    id_sector_filter = $("#fid_sector").val();
    
    $('#bill_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg, 'p_id_pref': id_pref, 'p_id_sector': id_sector_filter}}).trigger('reloadGrid');

    //innerLayout.resizeAll();
    
});

jQuery("#bt_print_all").click( function() {

       if(id_sector_filter==0)
       {
           return;
       }   

       $("#fprint_params").attr('action', 'bill_print.php');
       $("#fprint_params").find("#pid_bill").attr('value','' ); 

       $("#fprint_params").find("#pbill_list").attr('value','' ); 
       $("#fprint_params").find("#pid_sector_filter").attr('value',id_sector_filter ); 
       $("#fprint_params").find("#pmmgg").attr('value',mmgg ); 
       $("#fprint_params").find("#pcaption").attr('value', $("#fsector").attr('value'));
       
       $("#fprint_params").attr('target',"_blank" );           
       document.print_params.submit();

});

jQuery("#bt_print_allall").click( function() {

       $("#fprint_params").attr('action', 'bill_print.php');
       $("#fprint_params").find("#pid_bill").attr('value','' ); 

       $("#fprint_params").find("#pbill_list").attr('value','' ); 
       $("#fprint_params").find("#pid_sector_filter").attr('value',-1 ); 
       $("#fprint_params").find("#pmmgg").attr('value',mmgg ); 
       $("#fprint_params").find("#pcaption").attr('value', 'Всі рахунки');
       
       $("#fprint_params").attr('target',"_blank" );           
       document.print_params.submit();

});


$("#show_peoples").click( function() {
   jQuery("#bill_table").jqGrid('showCol',["user_name"]);
});

});

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

// обработчик, который вызываетя перед отправкой формы
function EditFormBeforeSubmit(formData, jqForm, options) { 

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

// обработчик ответа сервера после отправки формы
function EditFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_billedit").dialog('close');                           
               jQuery('#bill_table').trigger('reloadGrid');        
              
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
               
               jQuery('#bill_table').trigger('reloadGrid');        
               
               jQuery("#dialog_billedit").dialog('close');                                            
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

function SelectPaccntExternal(id, book, code, name, addr) {
    
        $("#fBillEdit").find('#fid_paccnt').attr('value',id );
        $("#fBillEdit").find('#fbook').attr('value',book );    
        $("#fBillEdit").find('#fcode').attr('value',code );    
        $("#fBillEdit").find('#fabon').attr('value',name );    
    
} 