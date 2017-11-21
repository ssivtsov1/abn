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
        ,	north__size:		250
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
    jQuery(".btnClear").button({icons: {primary:'ui-icon-cancel'}});
    
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
            //$("#freps_params").attr("action","rep_main_build_html.php");   
            //ajaxForm = $("#freps_params").ajaxForm(form_options_xl);

        } else {
            //alert("not checked");
            //$("#freps_params").attr("action","rep_main_build_html.php");          
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
   
   $("#fdt_sp").attr('value', Date.now().toString("dd.MM.yyyy") ); 
   
   

    jQuery("#btPaccntClear").click( function() { 

        $('#fbook').attr('value','' );
        $('#fcode').attr('value','' );    
        $('#fid_paccnt').attr('value','' );    
        $('#fpaccnt_name').attr('value','' );    
        $('#fpaccnt_addr').attr('value','' );    
    });

    jQuery("#btPaccntSel").click( function() { 

        createAbonGrid();
        
        abon_target_id = $('#fid_paccnt');
        abon_target_name = $('#fpaccnt_name');
        abon_target_addr = $('#fpaccnt_addr');
        abon_target_book = $('#fbook');
        abon_target_code = $('#fcode');

       jQuery("#grid_selabon").css({'left': $('#fbook').offset().left+1, 'top': $('#fbook').offset().top+20});
       jQuery("#grid_selabon").toggle( );
       
    });



    jQuery("#btPersonSel").click( function() { 

     createPersonGrid($("#fid_person").val());
     person_target_id=$("#fid_person")
     person_target_name =  $("#fperson")
     person_target_prof = 0;
    
     jQuery("#grid_selperson").css({'left': $("#fperson").offset().left+1, 'top': $("#fperson").offset().top+20});
     jQuery("#grid_selperson").toggle( );

       
    });

    jQuery("#btPersonClear").click( function() { 
        jQuery("#fid_person").attr('value','' );
        jQuery("#fperson").attr('value','' );    
        

    });


    $( ".btnRep" ).mouseenter(function() {

        $($( this ).attr('highlight')).addClass("BrightClass");
        
        //$('#fdt_rep').toggle( "highlight" );

    }).mouseleave(function() {

        $($( this ).attr('highlight')).removeClass("BrightClass");
    });


    $('#freps_params input').keypress(function(e){
       if ( e.which == 13 ) return false;
    }); 
    
    
    jQuery("#toggle_param").click( function() {
        jQuery("#pFullParam").toggle( );
    });    
    
    $("#fwrite_protocol").prop('checked',true);
    $("#fnum_sp").attr('value', next_num );
    
    
    jQuery("#pFullParam").hide();
    
    
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
                    $('#fpaccnt_name').change();
                    $('#fpaccnt_addr').attr('value',data.add_data );    
                }

                if(data.errcode==2)
                {
                    $('#fid_paccnt').attr('value','' );
                    $('#fpaccnt_name').attr('value',data.errstr );  
                    $('#old_sprav_info').html(''); 
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


 $("#fpaccnt_name").change(function() {
    
        vid = $('#fid_paccnt').attr('value');

        var request = $.ajax({
            url: "spravka_main_get_zprav_data.php",
            type: "POST",
            data: {
                id_paccnt : vid
            },
            dataType: "json"
        });

        request.done(function(data ) {  
        
            if (data.errcode!==undefined)
            {
                if(data.errcode==1)
                {
                    $('#old_sprav_info').html(data.errstr );    
                }
                else
                {   $('#old_sprav_info').html(''); }
            }
        });        
        request.fail(function(data ) {
            alert("error");
        });
    
 });
//-------------------------------------------------------
    jQuery("#btPaccntOpen").click( function() {
       createspravkaCache();
       
       jQuery("#grid_spravkacache").show( );
    
    });
    
    jQuery("#btPaccntAdd").click( function() {

       if ($('#fid_paccnt').attr('value') =='') return;
       if ($('#fid_person').attr('value') =='') 
           {
             alert('Вкажіть оператора!');
             return
           };
       
       createspravkaCache();
       
       jQuery("#grid_spravkacache").show( );

       var request = $.ajax({
               url: "spravka_cache_edit.php",
               type: "POST",
               data: {
                id_paccnt : $('#fid_paccnt').attr('value'),
                dt_b : $('#fdt_b').attr('value'),
                dt_e : $('#fdt_e').attr('value'),
                num_sp : $('#fnum_sp').attr('value'),
                dt_sp : $('#fdt_sp').attr('value'),
                num_input : $('#fnum_input').attr('value'),
                dt_input : $('#fdt_input').attr('value'),
                people_count : $('#fpeople_count').attr('value'),
                heat_area : $('#fheat_area').attr('value'),
                id_person : $('#fid_person').attr('value'),
                hotw : $('#fhotw').prop('checked'),
                hotw_gas : $('#fhotw_gas').prop('checked'),
                coldw : $('#fcoldw').prop('checked'),
                plita : $('#fplita').prop('checked'),
                social_norm: $('#fsocial_norm').attr('value'),
                show_dte : $('#fshow_dte').prop('checked'),
                write_protocol : $('#fwrite_protocol').prop('checked'),
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
                           jQuery('#spravka_cache_table').trigger('reloadGrid');                      
                           $('#fnum_sp').attr('value', parseFloat($('#fnum_sp').attr('value'))+1);
                
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
    
    });


    jQuery("#btPaccntDtChange").click( function() {

       
       createspravkaCache();
       
       jQuery("#grid_spravkacache").show( );

       var request = $.ajax({
               url: "spravka_cache_edit.php",
               type: "POST",
               data: {
                id_paccnt : $('#fid_paccnt').attr('value'),
                dt_b : $('#fdt_b').attr('value'),
                dt_e : $('#fdt_e').attr('value'),
                dt_sp : $('#fdt_sp').attr('value'),
                oper: 'set_date'
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
    if (((btn=='sprav1')||(btn=='sprav1new')||(btn=='sprav6')||(btn=='sprav12')||(btn=='spravlgt'))&&
       ($('#fid_paccnt').attr('value')==''))
    {
        alert('Виберіть абонента!');
    }
    else
    {
      document.forms["freps_params"].submit();
      setTimeout("setTemplate('');", 100);
      
      next_num=next_num+1;
      $("#fnum_sp").attr('value', next_num );
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
    


function setTemplate(template) {
        $("#freps_params").find('#ftemplate_name').attr('value', template);
        
}

