var elapsed_seconds = 0;
var timerId ;
var spinner_opts;
var dt_b;
var dt_e;
var ajaxForm;
var form_options_xl;
var form_options_html;
var SelectPersonTarget='';
var SelectPersonStrTarget='';
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
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	});

    innerLayout = $("#pmain_content").layout({
		name:			"inner" 
	,	north__paneSelector:	"#preps_header"
	,	north__closable:	true
	,	north__resizable:	true
        ,	north__size:		240
        ,	center__paneSelector:	"#preps_buttons"
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, $pane, state, options) 
        {
          //  jQuery("#client_table").jqGrid('setGridWidth',$pane.innerWidth()-9);
          //  jQuery("#client_table").jqGrid('setGridHeight',$pane.innerHeight()-142);
        }
        
	});
    outerLayout.close('south');             
    //innerLayout.resizeAll();
    //innerLayout.hide('north');        
        
    jQuery(".btn").button();
    jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});
    jQuery(".btnClear").button({text: false,icons: {primary:'ui-icon-cancel'}});
    jQuery(".btnPlan").button({text: false,icons: {primary:'ui-icon-pencil'}});
    jQuery(".btnPlanID").button({text: false,icons: {primary:'	ui-icon-script'}});
    
    jQuery(":input").addClass("ui-widget-content ui-corner-all");
    
    $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
    jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

    jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
    jQuery(".dtpicker").mask("99.99.9999");
    
    
        
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   
   $("#message_zone").dialog({autoOpen: false});
   $("#report_progress").dialog({autoOpen: false, resizable: false});
   //---------------------------------------------------------------------
   
   $.ajaxSetup({type: "POST",   dataType: "json"});
   
   form_options_xl = { 
    dataType:"json",
    url: 'rep_main_build.php',
    beforeSubmit: FormBeforeSubmit, 
    success: FormSubmitResponse 
  };
/*
   form_options_html = { 
    dataType:null,       
    url: 'rep_main_build_html.php',       
    beforeSubmit: FormBeforeSubmit, 
    success: FormSubmitResponse,
  };
*/
  ajaxForm = $("#freps_params").ajaxForm(form_options_xl);
   
   // опции валидатора общей формы
   var form_valid_options = { 
                //errorPlacement: function(error, element) {
		//		error.appendTo( element.parent("label").parent("div"));
                //},
		rules: {
			//book: "required",
			//code: "required"
		},
		messages: {
			//book: "Вкажіть номер книги!",
			//code: "Вкажіть особовий рахунок!"
		}
   };

   validator = $("#freps_params").validate(form_valid_options);   
   
   /*
   jQuery("#bt_test").click( function() { 
       //var fr = jQuery("#frame_hidden");
       //fr.src = "rep_download.php?path=../tmp/clientlist.xls";
       //jQuery("#frame_hidden").load("rep_download.php?path=../tmp/clientlist.xls");
       jQuery("#frame_hidden").attr("src", "rep_download.php?path=../tmp/clientlist.xls");
       
   });  
   */
    jQuery("#fxls").click(function(){
        //alert("clicked");
        if ($(this).attr("checked") == "checked"){
            //alert("checked");
            //$("#freps_params").attr('target',"_blank" );           
            $("#freps_params").attr("action","rep_main_build_html.php");   
            //ajaxForm = $("#freps_params").ajaxForm(form_options_xl);

        } else {
            //alert("not checked");
            $("#freps_params").attr("action","rep_main_build_html.php");          
            //ajaxForm = $("#freps_params").ajaxForm(form_options_html);
        }
    });

   jQuery("#btPeriodInc").click( function() { 
       dt_b.add({months: 1});
       dt_e = new Date(dt_b);
       dt_e.add({days: -1, months: 1});

       $("#fdt_b").datepicker( "setDate" , dt_b.toString("dd.MM.yyyy") );
       $("#fdt_e").datepicker( "setDate" , dt_e.toString("dd.MM.yyyy") );
       $("#fperiod_str").attr('value', dt_b.toString("MMMM yyyy")); 

   });   

   jQuery("#btPeriodDec").click( function() { 
       dt_b.add({months: -1});
       dt_e = new Date(dt_b);
       dt_e.add({days: -1, months: 1});

       $("#fdt_b").datepicker( "setDate" , dt_b.toString("dd.MM.yyyy") );
       $("#fdt_e").datepicker( "setDate" , dt_e.toString("dd.MM.yyyy") );
       $("#fperiod_str").attr('value', dt_b.toString("MMMM yyyy"));

   });   
   
   
   dt_b = Date.parse( mmgg, "dd.MM.yyyy");
   dt_e = Date.parse( mmgg, "dd.MM.yyyy");
   dt_e.add({days: -1, months: 1});
   

   $("#fdt_b").datepicker( "setDate" , dt_b.toString("dd.MM.yyyy") );
   $("#fdt_e").datepicker( "setDate" , dt_e.toString("dd.MM.yyyy") );
   $("#fperiod_str").attr('value', dt_b.toString("MMMM yyyy"));
   
   $("#fdt_rep").attr('value', Date.now().toString("dd.MM.yyyy") );
   
   
    jQuery("#btAddrTownSel").click( function() { 

        $("#fadr_sel_params_address").attr('value', '' );    
        $("#fadr_sel_params_select_mode").attr('value', 2 );    
    
        // $("#fadr_sel_params").attr('target',"_blank" );           
        var ww = window.open("adr_tree_selector.php", "adr_win", "toolbar=0,width=770,height=500");
        document.adr_sel_params.submit();
        ww.focus();
    });
    jQuery("#btAddrTownClear").click( function() { 

        $('#faddr_town').attr('value','' );
        $('#faddr_town_name').attr('value','' );    

    });


    jQuery("#btPaccntClear").click( function() { 

        $('#fbook').attr('value','' );
        $('#fcode').attr('value','' );    
        $('#fid_paccnt').attr('value','' );    
        $('#fpaccnt_name').attr('value','' );    
    });

    jQuery("#btPaccntSel").click( function() { 
   /*
        // $("#fpaccnt_sel_params").attr('target',"_blank" );           
        var ww = window.open("abon_en_main.php", "paccnt_win", "toolbar=0,width=900,height=600");
        document.paccnt_sel_params.submit();
        ww.focus();
        */
        createAbonGrid();
        
        abon_target_id = $('#fid_paccnt');
        abon_target_name = $('#fpaccnt_name');
        abon_target_book = $('#fbook');
        abon_target_code = $('#fcode');

       jQuery("#grid_selabon").css({'left': $('#fbook').offset().left+1, 'top': $('#fbook').offset().top+20});
       jQuery("#grid_selabon").toggle( );
       
    });

    //выбор участка
    jQuery("#btSectorSel").click( function() {

        sector_target_id =jQuery("#fid_sector");
        sector_target_name=jQuery("#fsector");
        sector_target_runner_id=null;
        sector_target_runner_name=null;

        createSectorGrid(); 
        jQuery("#grid_selsector").css({'left': jQuery("#fsector").offset().left+1,'top': jQuery("#fsector").offset().top+20});
        jQuery("#grid_selsector").toggle( );
    });
    
    //Вызов формы планирования для контролеров
    jQuery("#btSectorPlan").click( function() {
        var cntr = $("#fperson").attr("value");
        if(cntr==''){
            alert('Виберіть працівника(контролера)')
            return 0;
        }
        var dt_1 = $("#fdt_b").val();
        var url_par = 'controlers_counters_data.php?'+dt_1;
        //location.reload();
        createPlanGrid(); 
        jQuery("#pcontrolers_counters_table").toggle( );
    });
    
    
    jQuery("#btSectorPlanID").click( function() {
        var cntr = $("#fperson").attr("value");
        if(cntr==''){
            alert('Виберіть працівника(контролера)')
            return 0;
        }
        createPlanViewGrid(); 
        jQuery("#grid_planview").show( );
    });
    
    
    jQuery("#btSectorClear").click( function() { 
        jQuery("#fid_sector").attr('value','' );
        jQuery("#fsector").attr('value','' );    

    });

    jQuery("#btLgtSel").click( function() { 
        /*
     var ww = window.open("lgt_list.php", "lgt_win", "toolbar=0,width=800,height=600");
     document.lgt_sel_params.submit();
     ww.focus();
        */
       
     createLgtsGrid($("#fid_grp_lgt").val());
     lgt_target_id=$("#fid_grp_lgt")
     lgt_target_name =  $("#fgrp_lgt")
     lgt_target_id_calc = 0;
     lgt_target_name_calc =  0;
    
     jQuery("#grid_sellgt").css({'left': $("#fgrp_lgt").offset().left+1, 'top': $("#fgrp_lgt").offset().top+20});
     jQuery("#grid_sellgt").toggle( );
       
   });

    jQuery("#btLgtClear").click( function() { 
        $("#fid_grp_lgt").attr('value','' );
        $("#fgrp_lgt").attr('value','' );    
    });

    jQuery("#btPersonSel").click( function() { 

     createPersonGrid($("#fid_person").val());
     person_target_id=$("#fid_person")
     person_target_name =  $("#fperson")
     person_target_prof = 0;
    
     jQuery("#grid_selperson").css({'left': $("#fperson").offset().left+1, 'top': $("#fperson").offset().top+20});
     jQuery("#grid_selperson").toggle( );

/*
    SelectPersonTarget='#fid_person';
    SelectPersonStrTarget='#fperson';

    if ($(SelectPersonTarget).val()!='')
        $("#fcntrl_sel_params_id_cntrl").attr('value', $(SelectPersonTarget).val() );    
    else
        $("#fcntrl_sel_params_id_cntrl").attr('value', '0' );

     
     var www = window.open("staff_list.php", "cntrl_win", "toolbar=0,width=900,height=600");
     document.cntrl_sel_params.submit();
     www.focus();
        */
       
    });

   jQuery("#btTarSel").click( function() { 
/*
     var www = window.open("tarif_list.php", "tar_win", "toolbar=0,width=900,height=600");
     document.tarif_sel_params.submit();
     www.focus();
*/
    createTarifGrid();
    tarif_target_id=jQuery("#fid_gtar");
    tarif_target_name = jQuery("#fgtar");

    jQuery("#grid_seltarif").css({'left': jQuery("#fgtar").offset().left+1, 'top': jQuery("#fgtar").offset().top+20});
    jQuery("#grid_seltarif").toggle( );
       
   });

    jQuery("#btTarClear").click( function() { 
        jQuery("#fid_gtar").attr('value','' );
        jQuery("#fgtar").attr('value','' );    

    });

    jQuery("#btPersonClear").click( function() { 
        jQuery("#fid_person").attr('value','' );
        jQuery("#fperson").attr('value','' );    

    });

    jQuery("#btMeterSel").click( function() { 

       createMeterGrid(jQuery("#fid_type_meter").val());
       meter_target_id=jQuery("#fid_type_meter");
       meter_target_id_array=jQuery("#fid_type_meter_array");
       meter_target_name = jQuery("#ftype_meter");
       meter_target_carry = null;
       jQuery("#grid_selmeter").css({'left': jQuery("#ftype_meter").offset().left+1, 'top': jQuery("#ftype_meter").offset().top+20});
       jQuery("#grid_selmeter").toggle( );
    });

    jQuery("#btMeterClear").click( function() { 
        jQuery("#fid_type_meter").attr('value','' );
        jQuery("#ftype_meter").attr('value','' );    
        jQuery("#fid_type_meter_array").attr('value','' );

    });

   jQuery("#btAddParamSel").click( function() { 

    createAddParamGrid();
    param_target_id=jQuery("#fid_cntrl");
    param_target_name = jQuery("#fcntrl");

    jQuery("#grid_selparam").css({'left': jQuery("#fcntrl").offset().left+1, 'top': jQuery("#fcntrl").offset().top+20});
    jQuery("#grid_selparam").toggle( );

   });

    jQuery("#btAddParamClear").click( function() { 
        jQuery("#fid_cntrl").attr('value','' );
        jQuery("#fcntrl").attr('value','' );    
        
    });

    $( ".btnRep" ).mouseenter(function() {

        $($( this ).attr('highlight')).addClass("BrightClass");
        
        //$('#fdt_rep').toggle( "highlight" );

    }).mouseleave(function() {

        $($( this ).attr('highlight')).removeClass("BrightClass");
    });

    //поиск абонента по книге/счету
    jQuery("#btPaccntFind").click( function() {

        vbook = $('#fbook').attr('value');
        vcode = $('#fcode').attr('value');
         

        var request = $.ajax({
            url: "abon_en_get_abon_id_data.php",
            type: "POST",
            data: {
                book : vbook,
                code: vcode
            },
            dataType: "json"
        });

        request.done(function(data ) {  
        
            if (data.errcode!==undefined)
            {
                if(data.errcode==1)
                {
                    $('#fid_paccnt').attr('value',data.id );
                    $('#fpaccnt_name').attr('value',data.errstr );    
                }

                if(data.errcode==2)
                {
                    $('#fid_paccnt').attr('value','' );
                    $('#fpaccnt_name').attr('value',data.errstr );    
                }
            }
        });        
        request.fail(function(data ) {
            alert("error");
        });

    });


    $('#freps_params input').keypress(function(e){
       if ( e.which == 13 ) return false;
    }); 
    
    $('#fbook').keypress(function(ev){
        if(ev.which == 13){
            
        if ($('#fbook').val()!='')
               $('#fcode').select();
           
        return false;
        }
    });

    $('#fcode').keypress(function(ev){
        if(ev.which == 13){
            
        if ($('#fbook').val()!='')
        {

        vbook = $('#fbook').attr('value');
        vcode = $('#fcode').attr('value');
         

        var request = $.ajax({
            url: "abon_en_get_abon_id_data.php",
            type: "POST",
            data: {
                book : vbook,
                code: vcode
            },
            dataType: "json"
        });

        request.done(function(data ) {  
        
            if (data.errcode!==undefined)
            {
                if(data.errcode==1)
                {
                    $('#fid_paccnt').attr('value',data.id );
                    $('#fpaccnt_name').attr('value',data.errstr );    
                }

                if(data.errcode==2)
                {
                    $('#fid_paccnt').attr('value','' );
                    $('#fpaccnt_name').attr('value',data.errstr );    
                }
            }
        });        
        request.fail(function(data ) {
            alert("error");
        });

        }
           
        return false;
        }
    });
    
    
    if (r_off!=3)
    {
     $('#bwarning_build').prop('disabled', true);        
     $('#bwarning_del').prop('disabled', true);        
    }
    
    
    jQuery("#btShowAddParam").click( function() { 

        $('#dadd_params').toggle( );
    });
    
});


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
    
    if ((btn=='warning_build')&&(town_hidden==true))
    {
        if ($('#fsector').attr('value')=='')
        {
            alert('Виберіть дільницю для формування попереджень!');
            return false;
        }
     
    }
    
    if (btn=='warning_del')
    {
        if ((id_res==310)||(id_res==210))
        {
            if ($('#fsector').attr('value')=='')
            {
                alert('Виберіть дільницю для видалення попереджень!');
                return false;
            }
        }
    
        if ((id_res==330)||(id_res==320))
        {
            if (($('#fid_region').attr('value')=='null')&&($('#fsector').attr('value')==''))
            {
                alert('Виберіть регіон для видалення попереджень!');
                return false;
            }
        }
        
        $("#dialog-confirm").find("#dialog-text").html('Видалити попередження, для яких не вказана дата вручення?');
    
        $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Попередження',
			buttons: {
			    "Ок": function() {
				$( this ).dialog( "close" );
                                  document.forms["freps_params"].submit();
                                  setTimeout("setTemplate('');", 100);
				},
                            "Відмінити": function() {
                                $( this ).dialog( "close" );
                            }
                                
			}
		});
    
          jQuery("#dialog-confirm").dialog('open');
          return false;
        
    }

    if ((btn=='warning_calc')||(btn=='warning_build'))
    {
        if ($('#fsum_value').attr('value')=='')
        {
            alert('Вкажіть суму боргу!');
            return false;
        }
    }
    
/*
    if ( jQuery("#fxls").attr("checked") == "checked")
    {

      jQuery("#report_progress").dialog('open');
     
      elapsed_seconds = 0;
      timerId = setInterval(function() {
        elapsed_seconds = elapsed_seconds + 1;
        $('#report_progress_time').html(get_elapsed_time_string(elapsed_seconds));
        }, 1000);
        
      $("#report_progress_indicator").spin("large", "black");    
      return true;        
    } else
    */
    {
      document.forms["freps_params"].submit();
      setTimeout("setTemplate('');", 100);
      return false;
    }
    
} ;

// обработчик ответа сервера после отправки формы
function FormSubmitResponse(responseText, statusText)
{
   // if ( jQuery("#fxls").attr("checked") == "checked")
   // {
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
                 jQuery("#report_progress").dialog('close');
                 clearInterval(timerId);
                 $("#report_progress_indicator").spin(false);
                 return [true,errorInfo.errstr]
                 
             }; 

             if (errorInfo.errcode==1) {
                 
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
              jQuery("#report_progress").dialog('close');
              clearInterval(timerId);
              $("#report_progress_indicator").spin(false);
              jQuery("#frame_hidden").attr("src", "rep_download.php?path="+errorInfo.errstr);
              
               return [true,errorInfo.errstr]};              
   // }
   // else
   // {
        
   //     myWindow = window.open("data:text/html," + responseText,
   //                    "_blank");
   //     myWindow.focus();
   // }
};

function SelectAddrClassExternal(code, name) {
    
        $('#faddr_town').attr('value',code );
        $('#faddr_town_name').attr('value',name );    
    
} 

function SelectPaccntExternal(id, book, code, name, addr) {
    
        $('#fid_paccnt').attr('value',id );
        $('#fbook').attr('value',book );    
        $('#fcode').attr('value',code );    
        $('#fpaccnt_name').attr('value',name );    
    
} 

function SelectLgtExternal(id, name, id_calc, name_calc,code ) {
        $("#fid_grp_lgt").attr('value',id );
        $("#fgrp_lgt").attr('value',name );    
        
}

function SelectPersonExternal(id, name) {
    
        $(SelectPersonTarget).attr('value',id );
        $(SelectPersonStrTarget).attr('value',name );    
    
}

function SelectTarExternal(id, name) {
        $("#fid_gtar").attr('value',id );
        $("#fgtar").attr('value',name );    
    
}

function setTemplate(template) {
        $("#freps_params").find('#ftemplate_name').attr('value', template);
        
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

