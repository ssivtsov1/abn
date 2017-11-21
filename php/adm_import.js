var edit_row_id = 0;

var elapsed_seconds = 0;
var timerId ;
var spinner_opts;

var calc_timerId ;

jQuery(function(){ 

  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
		buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");


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


$("#fmmgg").datepicker( "setDate" , mmgg );
$("#fmmgg_dbf").datepicker( "setDate" , mmgg );
$("#fmmgg2_dbf").datepicker( "setDate" , mmgg );

$("#message_zone").dialog({autoOpen: false});

$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
$("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {jQuery("#message_zone").html('');});

jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});

jQuery(":input").addClass("ui-widget-content ui-corner-all");

$("#calc_progress").dialog({autoOpen: false, resizable: false});

if (r_close==3)
  {
    $("#bt_close_month").prop('disabled', false);
    $("#bt_open_month").prop('disabled', false);    
  }
else
  {  
    $("#bt_close_month").prop('disabled', true);
    $("#bt_open_month").prop('disabled', true);
  }

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
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            //jQuery("#dov_errlist_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            //jQuery("#dov_errlist_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }

	});
        
       
        outerLayout.resizeAll();
        outerLayout.close('south');             


    var file_form_options = { 
        dataType:"json",
        beforeSubmit: FileFormBeforeSubmit, 
        success: FileFormSubmitResponse    
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
            data_file: "required"
        },
        messages: {
            data_file: "Вкажіть файл!"
        }
    };
    load_validator = $("#fLoad").validate(file_valid_options);


 //-------------------------


    var getfile_form_options = { 
        dataType:"json",
        beforeSubmit: GetFileFormBeforeSubmit, 
        success: GetFileFormSubmitResponse    
    };
 
    var fFileGet_ajaxForm = $("#fGet").ajaxForm(getfile_form_options);

    var getfile_valid_options = { 
        errorPlacement: function(error, element) {
            error.appendTo( element.parent("label").parent("form"));
        },
        rules: {
            dt_file: "required"
        },
        messages: {
            dt_file: "Вкажіть дату!"
        }
    };
    get_validator = $("#fGet").validate(getfile_valid_options);

//-----------------------------------------------------------------------------

    var fFileGetDbf_ajaxForm = $("#fGetDBF").ajaxForm(getfile_form_options);
 
    var getdbffile_valid_options = { 
        errorPlacement: function(error, element) {
            error.appendTo( element.parent("label").parent("form"));
        },
        rules: {
            mmgg: "required"
        },
        messages: {
            mmgg: "Вкажіть період!"
        }
    };
    getdbf_validator = $("#fGetDBF").validate(getdbffile_valid_options);

//-----------------------------------------------------------------------------
    var fFileGetDbfBank_ajaxForm = $("#fGetDBFBank").ajaxForm(getfile_form_options);

    getdbfbank_validator = $("#fGetDBFBank").validate(getdbffile_valid_options);

//-----------------------------------------------------------------------------

    var fFileGetUpszn_ajaxForm = $("#fGetDBFUpszn").ajaxForm(getfile_form_options);
 
    var getupsznfile_valid_options = { 
        errorPlacement: function(error, element) {
            error.appendTo( element.parent("label").parent("form"));
        },
        rules: {
            a_file: "required",
            af_file: "required"
        },
        messages: {
            a_file: "Вкажіть файл запиту!",
            af_file: "Вкажіть файл запиту!"
        }
    };
    getupszn_validator = $("#fGetDBFUpszn").validate(getupsznfile_valid_options);

//-----------------------------------------------------------------------------


    var fFileGetXml_ajaxForm = $("#fGetXml").ajaxForm(getfile_form_options);
 
    var getxml_valid_options = { 
        errorPlacement: function(error, element) {
            error.appendTo( element.parent("label").parent("form"));
        },
        rules: {
            mmgg: "required"
        },
        messages: {
            mmgg: "Вкажіть період!"
        }
    };
    
    getxml_validator = $("#fGetXml").validate(getxml_valid_options);

//-----------------------------------------------------------------------------


$("#bt_saldo").click( function(){ 

/*
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
*/
                jQuery("#calc_progress").dialog('open');
                elapsed_seconds = 0;
                timerId = setInterval(function() {
                    elapsed_seconds = elapsed_seconds + 1;
                    $('#progress_time').html(get_elapsed_time_string(elapsed_seconds));
                }, 1000);
        
                $("#progress_indicator").spin("large", "black");    


                var request = $.ajax({
                    url: "adm_calc_saldo_edit.php",
                    type: "POST",
                    //data: {
                    //    mmgg : cur_mmgg
                    //},
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
                           alert("Сальдо перераховано!");
                        }
                        else
                        {
                            alert("Помилка при перерахунку сальдо!");     
                            jQuery("#message_zone").dialog('open');
                        }
                        
                    }
                });

                request.fail(function(data ) {
                    
                    jQuery("#calc_progress").dialog('close');
                    clearInterval(timerId);
                    $("#progress_indicator").spin(false);
                    
                    $('#message_zone').append(data);  
                    alert("Помилка при перерахунку сальдо!");     
                    jQuery("#message_zone").dialog('open');
        
                });
                                        
                      /*
                $( this ).dialog( "close" );
            },
            "Відмінити": function() {
                $( this ).dialog( "close" );
            }
        }
    });
    
    jQuery("#dialog-confirm").dialog('open');
*/

});

//---------------------------------------------------------------------------
$("#bt_close_month").click( function(){ 

    jQuery("#dialog-confirm").css('background-color','red');
    jQuery("#dialog-confirm").css('color','white');
    jQuery("#dialog-confirm").find("#dialog-text").html('Ви бажаєте закрити місяць?');
    
    $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Закриття місяця',
        buttons: {
            "Закрити": function() {
                                        
                var cur_mmgg = jQuery("#fmmgg").val();
                jQuery("#calc_progress").dialog('open');
                elapsed_seconds = 0;
                timerId = setInterval(function() {
                    elapsed_seconds = elapsed_seconds + 1;
                    $('#progress_time').html(get_elapsed_time_string(elapsed_seconds));
                }, 1000);
        
                $("#progress_indicator").spin("large", "black");    


                var request = $.ajax({
                    url: "adm_clm_edit.php",
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
                           alert("Місяць закрито!");
                        }
                        else
                        {
                            alert("Помилка при закритті місяця!");     
                            jQuery("#message_zone").dialog('open');
                        }
                        
                    }
                });

                request.fail(function(data ) {
                    
                    jQuery("#calc_progress").dialog('close');
                    clearInterval(timerId);
                    $("#progress_indicator").spin(false);


                    $('#message_zone').append(data);  
                    alert("Помилка при закритті місяця!");
                    jQuery("#message_zone").dialog('open');
        
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
//------------------------------------------------------------------------------

$("#bt_open_month").click( function(){ 

    jQuery("#dialog-open-confirm").css('background-color','red');
    jQuery("#dialog-open-confirm").css('color','white');
    
    $("#dialog-open-confirm").dialog({
        resizable: false,
        //height:140,
        modal: true,
        autoOpen: false,
        title:'Выдкриття місяця',
        buttons: {
            "Відкрити": function() {
                                        
                var cur_mmgg = jQuery("#fmmgg").val();
                var opn_code = jQuery("#fopen_code").val();
                var opn_reason = jQuery("#fopen_reason").val();
                
                jQuery("#calc_progress").dialog('open');
                elapsed_seconds = 0;
                timerId = setInterval(function() {
                    elapsed_seconds = elapsed_seconds + 1;
                    $('#progress_time').html(get_elapsed_time_string(elapsed_seconds));
                }, 1000);
        
                $("#progress_indicator").spin("large", "black");    


                var request = $.ajax({
                    url: "adm_opclm_edit.php",
                    type: "POST",
                    data: {
                        mmgg : cur_mmgg,
                        open_code :opn_code,
                        open_reason:opn_reason
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
                           $("#fmmgg").datepicker( "setDate" , data.errstr ); 
                           alert("Місяць відкрито!");
                        }
                        else
                        {
                            alert("Помилка при відкритті місяця!");     
                            jQuery("#message_zone").dialog('open');
                        }
                        
                    }
                });

                request.fail(function(data ) {
                    
                    jQuery("#calc_progress").dialog('close');
                    clearInterval(timerId);
                    $("#progress_indicator").spin(false);


                    $('#message_zone').append(data);  
                    alert("Помилка при відкритті місяця!");
                    jQuery("#message_zone").dialog('open');
        
                });
                                        
                      
                $( this ).dialog( "close" );
            },
            "Відмінити": function() {
                $( this ).dialog( "close" );
            }
        }
    });
    
    jQuery("#dialog-open-confirm").dialog('open');


});
//---------------------------------------------------------------------------
$("#bt_calc_corr").click( function(){ 


    jQuery("#dialog-confirm").find("#dialog-text").html('Ви бажаєте виконати розрахунок коригувань?');
    jQuery("#dialog-confirm").css('background-color','red');
    jQuery("#dialog-confirm").css('color','white');

    
    $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Розрахунок корегувань',
        buttons: {
            "Розрахувати": function() {
                                        
                var cur_mmgg = jQuery("#fmmgg").val();

                jQuery("#calc_progress").dialog('open');
                elapsed_seconds = 0;
                timerId = setInterval(function() {
                    elapsed_seconds = elapsed_seconds + 1;
                    $('#progress_time').html(get_elapsed_time_string(elapsed_seconds));
                }, 1000);
        
                $("#progress_indicator").spin("large", "black");    


                var request = $.ajax({
                    url: "adm_calc_corr_edit.php",
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
                           alert("Розрахунок виконано!");
                        }
                        else
                        {
                            alert("Помилка при виконанні розрахунку!");
                            jQuery("#message_zone").dialog('open');
                        }
                        
                    }
                });

                request.fail(function(data ) {
                    
                    jQuery("#calc_progress").dialog('close');
                    clearInterval(timerId);
                    $("#progress_indicator").spin(false);
                    
                    
                    $('#message_zone').append(data);  
                    alert("Помилка при виконанні розрахунку!");
                    jQuery("#message_zone").dialog('open');
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
//------------------------------------------------------------------------------

$("#bt_calc_plan").click( function(){ 


    jQuery("#dialog-confirm").find("#dialog-text").html('Ви бажаєте виконати розрахунок відсутніх показників по плановому споживанню?');
    jQuery("#dialog-confirm").css('background-color','red');
    jQuery("#dialog-confirm").css('color','white');

    
    $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Розрахунок по плановому спож.',
        buttons: {
            "Розрахувати": function() {
                                        
                var cur_mmgg = jQuery("#fmmgg").val();

                jQuery("#calc_progress").dialog('open');
                elapsed_seconds = 0;
                timerId = setInterval(function() {
                    elapsed_seconds = elapsed_seconds + 1;
                    $('#progress_time').html(get_elapsed_time_string(elapsed_seconds));
                }, 1000);
        
                $("#progress_indicator").spin("large", "black");    


                var request = $.ajax({
                    url: "adm_calc_plan_demand_edit.php",
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
                           alert("Розрахунок виконано!");
                        }
                        else
                        {
                            alert("Помилка при виконанні розрахунку!");
                            jQuery("#message_zone").dialog('open');
                        }
                        
                    }
                });

                request.fail(function(data ) {
                    
                    jQuery("#calc_progress").dialog('close');
                    clearInterval(timerId);
                    $("#progress_indicator").spin(false);
                    
                    
                    $('#message_zone').append(data);  
                    alert("Помилка при виконанні розрахунку!");
                    jQuery("#message_zone").dialog('open');
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

//==============================================================================

$("#bt_calc_bill").click( function(){ 

    jQuery("#dialog-confirm").css('background-color','red');
    jQuery("#dialog-confirm").css('color','white');
    jQuery("#dialog-confirm").find("#dialog-text").html('Ви бажаєте виконати розрахунок рахунків?');
    
    $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Рахунки',
        buttons: {
            "Розрахувати": function() {
                                        
                var cur_mmgg = jQuery("#fmmgg").val();

                var request = $.ajax({
                    url: "adm_calc_bill_edit.php",
                    type: "POST",
                    data: {
                        mmgg : cur_mmgg
                    },
                    dataType: "json"
                });


                request.done(function(data ) {  
       
                    //jQuery("#calc_progress").dialog('close');
                    //clearInterval(timerId);
                    //$("#progress_indicator").spin(false);

        
                    if (data.errcode!==undefined)
                    {
                        $("#message_zone").html('');
                        $('#message_zone').append(data.errstr);  
                        $('#message_zone').append("<br>");                 
                        //jQuery("#message_zone").dialog('open');
            
                        if(data.errcode<=0) 
                        {
                           //alert("Розрахунок виконано!");
                           
                           jQuery("#message_zone").dialog('open');
                           
                            jQuery("#calc_progress").dialog('open');
                            elapsed_seconds = 0;
                            timerId = setInterval(function() {
                                elapsed_seconds = elapsed_seconds + 1;
                                $('#progress_time').html(get_elapsed_time_string(elapsed_seconds));
                            }, 1000);
        
                            $("#progress_indicator").spin("large", "black");    
                            
                            
                            
                            calc_timerId = setInterval(function() {
        
                            var request_m = $.ajax({
                                        url: "check_calc_progress_data.php",
                                        type: "POST",
                                        data: {
                                        },
                                        dataType: "json"
                                    });

                            request_m.done(function(data ) {  
            
                                if (data.errcode!==undefined)
                                        {
                                            
                                            $('#message_zone').append(data.errstr);  
                                            $('#message_zone').append("<br>");                 
                                            
                                            if(data.errcode==-1)
                                            {

                                                $("#calc_progress").dialog('close');
                                                clearInterval(timerId);
                                                $("#progress_indicator").spin(false);
                                                clearInterval(calc_timerId);
                                                alert("Розрахунок виконано!");

                                            }
                                            
                                        }
            
                                    });

                            request_m.fail(function(data ) {
                                        alert("Помилка при виконанні розрахунку!");
                                        //clearInterval(calc_timerId);
                                        //alert("Зв'язок з сервером втрачено. Обновіть сторінку, нажавши F5.");
                                    });        
        
                                }, 10000);                            
                           
                        }
                        else
                        {
                            alert("Помилка при виконанні розрахунку!");
                            jQuery("#message_zone").dialog('open');
                        }
                        
                    }
                });

                request.fail(function(data ) {
                    
                    $('#message_zone').append(data);  
                    alert("Помилка!");
                    jQuery("#message_zone").dialog('open');
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

//==============================================================================



  jQuery('#lgt_oper_table').jqGrid({
    url:'adm_lgt_check_data.php',
    editurl: '', 
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:400,
    width:800,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},                
    {label:'Адреса',name:'addr', index:'addr', width:100, editable: true, align:'left',edittype:'text'},

      {label:'Код',name:'ident', index:'ident', width:30, editable: false, align:'center', hidden:true},                       
      {label:'Код РЕС',name:'alt_code', index:'alt_code', width:40, editable: false, align:'center', hidden:true},
      
      {label:'Пільга',name:'grp_lgt', index:'grp_lgt', width:150, editable: false, align:'center', hidden:false},                       
      
      {label:'Серія',name:'s_doc', index:'s_doc', width:50, editable: true, align:'left',edittype:'text', hidden:true},      
      {label:'Номер',name:'n_doc', index:'n_doc', width:50, editable: true, align:'left',edittype:'text', hidden:true},
      
      {label:'Дата поч.',name:'dt_start', index:'dt_doc', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Дата кін.',name:'dt_end', index:'dt_doc', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      
      {label:'Дата док.',name:'dt_doc', index:'dt_doc', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Дійсний до',name:'dt_doc_end', index:'dt_doc_end', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},

      {label:'Дата народж.',name:'dt_birth', index:'dt_birth', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Дата 18 р.',name:'dt_child_end', index:'dt_child_end', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      
      {label:"Членів сім'ї",name:'family_cnt', index:'family_cnt', width:40, editable: false, align:'center', hidden:false},                             
      {label:"Членів сім'ї нов",name:'family_cnt_new', index:'family_cnt_new', width:40, editable: false, align:'center', hidden:false},                             

      {label:'Дата опер.',name:'dt_oper', index:'dt_oper', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Операція',name:'note', index:'note', width:120, editable: true, align:'left',edittype:'text'}
    ],
    pager: '#lgt_oper_tablePager',
    autowidth: true,
    //shrinkToFit : false,
    rowNum:2000,
    //rowList:[50,100,200],
    sortname: 'ident',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: '',
    //hiddengrid: false,
    hidegrid: false,    
    multiselect: true,
    //recordpos: 'left',
    postData:{'p_mmgg': mmgg},
    jsonReader : {repeatitems: false},
    
    gridComplete:function(){

  //  if ($(this).getDataIDs().length > 0) 
  //  {      
  //   var first_id = parseInt($(this).getDataIDs()[0]);
  //   $(this).setSelection(first_id, true);
  //  }
    
  },
    
  //  ondblClickRow: function(id){ 
  //       jQuery(this).editGridRow(id,LgtNormEditOptions);  
  //  } ,  

    loadError : function(xhr,st,err) {
      jQuery('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);
    }
  
  //  jsonReader : { repeatitems: false }

  }).navGrid('#lgt_oper_tablePager',
         {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

//jQuery('#lgt_oper_table').jqGrid('filterToolbar','');


$.ajaxSetup({type: "POST",      dataType: "json"});

$("#plgt_oper_table").dialog({
    resizable: true,
    height:600,
    width:900,
    modal: true,
    autoOpen: false,
    title:"Зміни пільг абонентів з плином часу",
    buttons: {
        "Виконати": function() {
/*                                         
            $("#dialog-changedate").dialog({ 
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                buttons: {
                    "Ok": function() {
                                                    
                        var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
                        if (cur_dt_change=='') return;        
  */                              
                        var ids =jQuery("#lgt_oper_table").jqGrid('getGridParam','selarrrow');
                        //alert(ids);                                                  
                        var request = $.ajax({
                            url: "adm_lgt_check_edit.php",
                            data: {
                                id_array: ids
                            }
                        });

                        request.done(function(data ) {
                              if (data.errcode!==undefined)
                               {
                                $('#message_zone').append(data.errstr);
                                $('#message_zone').append("<br>");
                                
                                if(data.errcode==2) 
                                {
                                    jQuery("#message_zone").dialog('open');
                                }                                
                                
                               }
                              $("#plgt_oper_table").dialog( "close" );
                        });
                        request.fail(function(data ) {alert("error");});
                                   
                        /*
                    },
                    "Отмена": function() {
                        $( this ).dialog( "close" );
                    }
                }
                        
            });
                        */                
            //jQuery("#dialog-changedate").dialog('open');
        //$( this ).dialog( "close" );
    },

    "Закрити": function() {
        $( this ).dialog( "close" );
    }
},
resize: function(event, ui) { 
    jQuery("#lgt_oper_table").jqGrid('setGridWidth',jQuery("#plgt_oper_table").innerWidth()-8);
    jQuery("#lgt_oper_table").jqGrid('setGridHeight',jQuery("#plgt_oper_table").innerHeight()-60);
                             
},
open: function(event, ui) { 
    jQuery("#lgt_oper_table").jqGrid('setGridWidth',jQuery("#plgt_oper_table").innerWidth()-8);
    jQuery("#lgt_oper_table").jqGrid('setGridHeight',jQuery("#plgt_oper_table").innerHeight()-60);
                         
}
});


   $("#bt_lgt_close").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       
       $('#lgt_oper_table').jqGrid('setGridParam',{datatype:'json', postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
       $("#plgt_oper_table").dialog('open');
       
   });


   if (res_code!=310)
       $("#f2_mem").hide();

   if (res_code!=210)
   {
      $("#f2_neg").hide();
      $("#f10_neg").hide();      
   }

   if (res_code!=240)
   {
      $("#f2_pril").hide();
      $("#f10_pril").hide();      
   }

   if (res_code!=180)
   {
      $("#f2_krp").hide();
      //$("#f10_pril").hide();      
   }
   if (res_code!=220)
   {
      $("#f2_ns").hide();
   }


   if (res_code!=170)
   {
      $("#f2_krk").hide();
   }

   if (res_code!=200)
   {
      $("#f2_mena").hide();
      $("#ff_mena").hide();
      $("#ff_sosn").hide();
      
   }

   if (res_code!=120)
   {
      $("#f2_brz").hide();
      //$("#f10_pril").hide();      
   }


  if ((res_code==210)||(res_code==240))
   {
      $("#bt_f10").hide();
   }


   if (res_code!=320)
       {
         $("#f2_cher").hide();
         $("#ff_cher").hide();
         $("#ff_slav").hide();
       }

   if (res_code==320)
       {
        $("#ff_all").hide();
        $("#bt_call_poks").hide();
        $("#bt_call_zip").hide();
       }

}); 
 

function FileFormBeforeSubmit(formData, jqForm, options) { 

    submit_form = jqForm;
    $('#load_in_progress').show();

    var queryString = $.param(formData);     
    $('#message_zone').append('Вот что мы передаем:' + queryString);  
    $('#message_zone').append("<br>");                 
    /*
    var btn = '';
    for (var i=0; i < formData.length; i++) { 
        if (formData[i].name =='submitButton') { 
           btn= formData[i].value; 
           submit_form[0].oper.value = btn;
        }  
    } 
*/
    if(!submit_form.validate().form())  {return false;}
    else {
               return true;
    }

} ;

function GetFileFormBeforeSubmit(formData, jqForm, options) { 

    submit_form = jqForm;
    $('#work_in_progress').show();

    var queryString = $.param(formData);     
    $('#message_zone').append('Вот что мы передаем:' + queryString);  
    $('#message_zone').append("<br>");                 
    /*
    var btn = '';
    for (var i=0; i < formData.length; i++) { 
        if (formData[i].name =='submitButton') { 
           btn= formData[i].value; 
           submit_form[0].oper.value = btn;
        }  
    } 
*/
    if(!submit_form.validate().form())  {return false;}
    else {
               return true;
    }

} ;

function FileFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;
             /*
              var percentVal = '100%';
              bar.width(percentVal)
              percent.html(percentVal);
             */

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
               $('#load_in_progress').hide();
              // jQuery('#subs_table').trigger('reloadGrid');        
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              
               return [true,errorInfo.errstr]};              

               
             if (errorInfo.errcode==2) {
               $('#load_in_progress').hide();  
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};   

};

function GetFileFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
               $('#work_in_progress').hide();
               
               filename = errorInfo.errstr;
               $("#frame_hidden").attr("src", "doc_download.php?path="+filename);
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              
               return [true,errorInfo.errstr]};              

               
             if (errorInfo.errcode==2) {
               $('#load_in_progress').hide();  
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