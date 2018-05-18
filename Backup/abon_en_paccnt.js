var SelectAbonTarget='';
var SelectPersonTarget='';
var SelectPersonStrTarget='';
var isMainHistGridCreated =false;
var form_options = { 
    dataType:"json",
    beforeSubmit: AccFormBeforeSubmit, 
    success: AccFormSubmitResponse 
  };
var AllGridWidth = 800;
var form_edit_lock=0;
var paccnt_hist_edit = false;
var paccnt_hist_del = false;

jQuery(function(){ 

  if (r_edit==3)
  {
    if (r_hist_edit>=1)  paccnt_hist_edit = true;
    if (r_hist_edit==3)  paccnt_hist_del = true;    
  }


    outerLayout = $("body").layout({
		name:	"outer" 
	,	north__paneSelector:	"#pmain_header"
	,	north__closable:	false
	,	north__resizable:	false
        ,	north__size:		30
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

      select: function(event, ui) {
          jQuery("#paccnt_lgt_table").jqGrid('setGridWidth',AllGridWidth);
          jQuery("#hist_lgt_table").jqGrid('setGridWidth',AllGridWidth);
    
          jQuery("#paccnt_dogovor_table").jqGrid('setGridWidth',AllGridWidth);
          jQuery("#paccnt_plomb_table").jqGrid('setGridWidth',AllGridWidth);
          jQuery("#paccnt_notlive_table").jqGrid('setGridWidth',AllGridWidth);
          jQuery("#paccnt_works_table").jqGrid('setGridWidth',AllGridWidth);
          jQuery("#paccnt_works_indic_table").jqGrid('setGridWidth',AllGridWidth);

      }
    });

    innerLayout = $("#pmain_content").layout({
		name:			"inner" 
	,	north__paneSelector:	"#pwork_header"
	,	north__closable:	true
	,	north__resizable:	false
        ,	north__spacing_open:	0
        ,	north__size:		235
        ,	center__paneSelector:	"#pwork_center"
	,	autoBindCustomButtons:	true
//        ,	center__onresize:   $.layout.callbacks.resizeTabLayout
	,       center__onresize:	function (pane, $pane, state, options) 
        {
            //jQuery("#paccnt_meters_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            //jQuery("#paccnt_meters_table").jqGrid('setGridWidth',jQuery("#paccnt_meters_list").innerWidth());
            //jQuery("#paccnt_lgt_table").jqGrid('setGridWidth',jQuery("#paccnt_lgt_list").innerWidth());            
            
            jQuery("#paccnt_meters_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            jQuery("#paccnt_lgt_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            jQuery("#hist_lgt_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            jQuery("#paccnt_dogovor_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            jQuery("#paccnt_plomb_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            jQuery("#paccnt_notlive_table").jqGrid('setGridWidth',$pane.innerWidth()-20);

            jQuery("#paccnt_works_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            jQuery("#paccnt_works_indic_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            AllGridWidth = $pane.innerWidth()-20;
            //jQuery("#client_table").jqGrid('setGridHeight',$pane.innerHeight()-142);

        }
        
	});
        
    meterLayout = $("#pMeterParam").layout({
		name:			"meter_param" 
        //,       initPanes:		false
        //,       resizeWithWindow:	false
	,	east__paneSelector:	"#pMeterParam_right"
	,	east__closable:	true
	,	east__resizable:	true
        ,	east__size:		230
        ,	center__paneSelector:	"#pMeterParam_left"
	,	autoBindCustomButtons:	true
        ,	south__paneSelector:	"#pMeterParam_buttons"
	,	south__closable:	false
	,	south__resizable:	false
        ,	south__spacing_open:	0
        ,	south__size:            40
	,       center__onresize:	function (pane, $pane, state, options) 
        {
            //jQuery("#client_table").jqGrid('setGridWidth',$pane.innerWidth()-9);
            //jQuery("#client_table").jqGrid('setGridHeight',$pane.innerHeight()-142);

        }
    });

/*
    meterLayout = $("#tab_meters").layout({
		name:			"meters" 
        ,       initPanes:		false
        ,       resizeWithWindow:	false
	,	south__paneSelector:	"#pMeterParam"
	,	south__closable:	true
	,	south__resizable:	true
        ,	south__size:		300
        ,	center__paneSelector:	"#paccnt_meters_list"
        ,       center__minSize:	100
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, $pane, state, options) 
        {
            //jQuery("#client_table").jqGrid('setGridWidth',$pane.innerWidth()-9);
            //jQuery("#client_table").jqGrid('setGridHeight',$pane.innerHeight()-142);

        }
        
	});*/

   innerLayout.resizeAll(); 
   outerLayout.close('south');     
 //  meterLayout.resizeAll();         


       
    jQuery(".btn").button();
    jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});
    jQuery(".btnGear").button({text: false,icons: {primary:'ui-icon-gear'}});
    jQuery(".btnInfo").button({text: false, icons: {primary:'ui-icon-info'}});
    jQuery(".btnFamily").button({text: false, icons: {primary:'ui-icon-person'}});
    jQuery(".btnCopy").button({text: false, icons: {primary:'ui-icon-copy'}});
    jQuery(".btnClear").button({text: false,icons: {primary:'ui-icon-cancel'}});
 
    jQuery("#fAccEdit :input").addClass("ui-widget-content ui-corner-all");
    jQuery("#dialog-changemeterdirect :input").addClass("ui-widget-content ui-corner-all");
    
    $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
    jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true
                        ,onClose: function ()  {this.focus();}});

    jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
    jQuery(".dtpicker").mask("99.99.9999");
    
        
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   
   $("#message_zone").dialog({autoOpen: false});
   $("#dialog-mainhistory").dialog({autoOpen: false ,resizable: true, height:253, width:750});
   
   $("#lgt_label").click( function() {
       
       //$( "#pwork_center" ).tabs( "option", "active", 1 );
       $( "[href='#tab_lgt']").trigger( "click" );
   });
//-----------------------------------------------------
   jQuery("#btAbonSel").click( function() { 
     SelectAbonTarget='fAccEdit';

    if ($("#fAccEdit").find("#fid_abon").val()!='')
        $("#fabon_sel_params_id_abon").attr('value', $("#fAccEdit").find("#fid_abon").val() );    
    else
        $("#fabon_sel_params_id_abon").attr('value', '0' );    
    
     var ww = window.open("dov_abon.php", "abon_win", "toolbar=0,width=900,height=600");
     document.abon_sel_params.submit();
     ww.focus();
   });


  jQuery("#btAbonOpen").click( function() { 

    abon_id_name="#fid_abon";
    abon_name_name="#fabon"
    
    if ($("#fAccEdit").find("#fid_abon").val()!='')
        ShowAbon($("#fAccEdit").find("#fid_abon").val() );    
    else

        ShowEmptyAbon();
    
        $("#faddr_reg_str").attr('value', $("#fAccEdit").find("#faddr_str").val() );
        $("#faddr_reg").attr('value', $("#fAccEdit").find("#faddr").val() );
        
        $("#faddr_live_str").attr('value', $("#fAccEdit").find("#faddr_str").val() );
        $("#faddr_live").attr('value', $("#fAccEdit").find("#faddr").val() );
        
        $("#fdt_b_abon").attr('value', $("#fAccEdit").find("#fdt_b").val() );
    
        $("#fAbonEdit").find("#flast_name").focus();
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


   jQuery("#btNameSubsSel").click( function() { 

    jQuery("#subs_name_panel").css({'left': jQuery("#fn_subs").offset().left-50, 'top': jQuery("#fn_subs").offset().top-20});
    jQuery("#subs_name_panel").toggle( );

   });

   jQuery("#bt_SubsNameclose").click( function() { 
      jQuery("#subs_name_panel").toggle( );
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

   jQuery("#btAddrSel").click( function() { 
        SelectAdrTarget='#faddr';
        SelectAdrStrTarget='#faddr_str';

        $("#fadr_sel_params_address").attr('value', $("#fAccEdit").find("#faddr").val() );    
    
       // $("#fadr_sel_params").attr('target',"_blank" );           
        var ww = window.open("adr_tree_selector.php", "adr_win", "toolbar=0,width=770,height=500");
        document.adr_sel_params.submit();
        ww.focus();
   });

   jQuery("#bt_mainhistory").click( function() { 

    createMainHistGrid();
    jQuery("#dialog-mainhistory").dialog('open');
   });

 //---------------------------------------------

$.ajaxSetup({type: "POST",   dataType: "json"});

var fAccEdit_ajaxForm = $("#fAccEdit").ajaxForm(form_options);

// опции валидатора общей формы
var form_valid_options = { 
                errorPlacement: function(error, element) {
				error.appendTo( element.parent("label").parent("div"));
                },
                focusInvalid: false,
                onkeyup: false,
                focusCleanup : true,
                onfocusout: false,
		rules: {
			book: "required",
			code: "required",                        
			addr_str: "required",
                        abon: "required",
                        //dt_b: "required",
                        dt_b: {required_date:true},
                        heat_area: {number:true}
		},
		messages: {
			book: "Вкажіть номер книги!",
			code: "Вкажіть особовий рахунок!",
			addr_str: "Вкажіть адресу",
                        abon: "Вкажіть абонента",
                        //dt_b: "Вкажіть дату",
                        heat_area: {number:"Повинно бути число!"}
		}
};

$.validator.addMethod("required_date", function (value, element) {
	        return value.replace(/\D+/g, '').length > 1;
	    },   " Вкажіть дату");

validator = $("#fAccEdit").validate(form_valid_options);

//$.ajaxSetup({  type: "POST",   dataType: "json" });

if (mode == 0)
{ 
 var request = $.ajax({
     url: "abon_en_paccnt_data.php",
     type: "POST",
     data: {id : id_paccnt},
     dataType: "json"});

 request.done(function(data ) {
     LoadAccData(data);
 });
 request.fail(function(data ) {alert("error");});

 $("#fAccEdit").find("#bt_add").hide();
 $("#fAccEdit").find("#bt_edit").show(); 

}
else
{
 $("#fAccEdit").find("#bt_add").show();
 $("#fAccEdit").find("#bt_edit").hide();   
 $("#fAccEdit").find("#bt_delabon").hide();   
 $("#fAccEdit").find("#bt_showtree").hide();   
 $("#fAccEdit").find("#bt_saldo").hide();   
 $("#fAccEdit").find("#bt_subs").hide();   
 $("#fAccEdit").find("#bt_mainhistory").hide();   
 $("#fAccEdit").find("#archive_label").hide();
 $("#fAccEdit").find("#bt_archabon").hide();
 $("#fAccEdit").find("#bt_unarchabon").hide();
 $("#fAccEdit").find("#bt_switch").hide();
 $("#fAccEdit").find("#btTarFamily").prop('disabled', true);

 $("#pwork_center").hide();

 $("#fAccEdit").find("#factiv").prop('checked',true);
 $("#fAccEdit").find("#fid_dep").attr('value', id_res);
 
 
 $("#fAccEdit").find("#fbook").focus();
 
}

$("#fAccEdit").find("#bt_reset").click( function() 
{
    if (mode==0)
    {        
        validator.resetForm();
        ResetJQFormVal($("#fAccEdit"));
    }
    else
    {
        window.location = 'abon_en_main.php';
    }
});

$("#fAccEdit").find("#bt_showtree").click( function() 
{
    $("#fpaccnt_params").attr("action","eqp_tree.php");
    $("#fpaccnt_params").attr('target',"_blank" );           
    document.paccnt_params.submit();
});

//---------------------------------------------------
$("#fAccEdit").find("#bt_delabon").click( function() 
{
 
      if (r_edit!=3){alert("Немає прав!");return;}
      
      jQuery("#dialog-confirm").css('background-color','red');
      jQuery("#dialog-confirm").css('color','white');
    
      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити особовий рахунок?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {

                                        $("#dialog-changedate").dialog({ 
                                            resizable: false,
                                            height:140,
                                            modal: true,
                                            autoOpen: false,
                                            buttons: {
                                                "Ok": function() {
                                                    
                                                      var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
                                                      
                                                      fAccEdit_ajaxForm[0].change_date.value = cur_dt_change;
                                                      fAccEdit_ajaxForm[0].oper.value = 'del';
                                                      fAccEdit_ajaxForm.ajaxSubmit(form_options);   
                                                      
                                                      jQuery("#dialog-confirm").css('background-color','white');
                                                      jQuery("#dialog-confirm").css('color','black');

                                                    $( this ).dialog( "close" );
                                                },
                                                "Отмена": function() {
                                                    jQuery("#dialog-confirm").css('background-color','white');
                                                    jQuery("#dialog-confirm").css('color','black');
                                                    
                                                    $( this ).dialog( "close" );
                                                }
                                            }/*
                                            ,focus: function() {
                                                $(this).on("keyup", function(e) {
                                                    if (e.keyCode === 13) {
                                                        $(this).parent().find("button:contains('Ok')").trigger("click");
                                                        return false;
                                                    }
                                                })
                                            }
                                            */

                                        });
                                        
                                        jQuery("#dialog-changedate").dialog('open');


					$( this ).dialog( "close" );
				},
				"Відмінити": function() {
					$( this ).dialog( "close" );
				}
			}
		});
    
       jQuery("#dialog-confirm").dialog('open');
          
});

   $("#fAccEdit").find("#bt_saldo").click( function() {

            $("#fpaccnt_params").find("#pmode").attr('value',0 );
            $("#fpaccnt_params").find("#pid_paccnt").attr('value',id_paccnt );
            $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                $("#fAccEdit").find("#fbook").val()+'/'+
                $("#fAccEdit").find("#fcode").val()+' '+ 
                $("#fAccEdit").find("#faddr_str").val()+' - '+ 
                $("#fAccEdit").find("#fabon").val() );
            
            $("#fpaccnt_params").find("#ppaccnt_book").attr('value',
                $("#fAccEdit").find("#fbook").val() );

            $("#fpaccnt_params").find("#ppaccnt_code").attr('value',
                $("#fAccEdit").find("#fcode").val() );

            $("#fpaccnt_params").find("#ppaccnt_name").attr('value',
                $("#fAccEdit").find("#fabon").val() );

            //$("#fpaccnt_params").attr('target',"_blank" );  
            $("#fpaccnt_params").attr("action","abon_en_saldo.php");                    
          
            document.paccnt_params.submit();

    });


   $("#subs_label").click( function() {

            $("#fpaccnt_params").find("#pmode").attr('value',0 );
            $("#fpaccnt_params").find("#pid_paccnt").attr('value',id_paccnt );
            $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                $("#fAccEdit").find("#fbook").val()+'/'+
                $("#fAccEdit").find("#fcode").val()+' '+ 
                $("#fAccEdit").find("#fabon").val() );
            $("#fpaccnt_params").attr('target',"_blank" );  
            $("#fpaccnt_params").attr("action","abon_en_onesubs.php");                    
          
            document.paccnt_params.submit();

    });

   $("#fAccEdit").find("#bt_subs").click( function() {

            $("#fpaccnt_params").find("#pmode").attr('value',0 );
            $("#fpaccnt_params").find("#pid_paccnt").attr('value',id_paccnt );
            $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                $("#fAccEdit").find("#fbook").val()+'/'+
                $("#fAccEdit").find("#fcode").val()+' '+ 
                $("#fAccEdit").find("#fabon").val() );
            $("#fpaccnt_params").attr('target',"_blank" );  
            $("#fpaccnt_params").attr("action","abon_en_onesubs.php");                    
          
            document.paccnt_params.submit();

    });

   $("#switch_label").click( function() {

            $("#fpaccnt_params").find("#pmode").attr('value',0 );
            $("#fpaccnt_params").find("#pid_paccnt").attr('value',id_paccnt );
            $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                $("#fAccEdit").find("#fbook").val()+'/'+
                $("#fAccEdit").find("#fcode").val()+' '+ 
                $("#fAccEdit").find("#fabon").val() );
            $("#fpaccnt_params").attr('target',"_blank" );  
            $("#fpaccnt_params").attr("action","abon_en_switch.php");                    
          
            document.paccnt_params.submit();

    });

   $("#bt_switch").click( function(){ 

            $("#fpaccnt_params").find("#pmode").attr('value',0 );
            $("#fpaccnt_params").find("#pid_paccnt").attr('value',id_paccnt );
            $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                $("#fAccEdit").find("#fbook").val()+'/'+
                $("#fAccEdit").find("#fcode").val()+' '+ 
                $("#fAccEdit").find("#fabon").val() );
            
            $("#fpaccnt_params").attr('target',"_blank" );  
            $("#fpaccnt_params").attr("action","abon_en_switch.php");                    
          
            document.paccnt_params.submit();
   });

//------------------------------------------------------
//----------------таблица истории  -------------
var createMainHistGrid = function(){ 
    
  if (isMainHistGridCreated) return;
  isMainHistGridCreated =true;


  jQuery('#mainhistory_table').jqGrid({
    url:'abon_en_paccnt_main_hist_data.php',
    editurl: 'abon_en_paccnt_main_hist_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:150,
    //width:800,
    autowidth: true,
    shrinkToFit : false,
    scroll: 0,
    colModel :[ 
      {name:'id_key', index:'id_key', width:40, editable: false, align:'center', key:true,hidden:true},
      {label:'id',name:'id', index:'id', width:40, editable: false, align:'center', hidden:true},     
      {label:'Книга',name:'book', index:'book', width:40, editable: false, align:'left',edittype:'text'},           
      {label:'Рах.',name:'code', index:'code', width:40, editable: false, align:'left',edittype:'text'},                 
      {label:'Адреса',name:'addr', index:'addr', width:200, editable: false, align:'left',edittype:'text'},           
      {label:'Абонент',name:'abon', index:'abon', width:200, editable: false, align:'left',edittype:'text'},
      {label:'Арх.',name:'archive', index:'archive', width:30, editable: false, align:'right',hidden:false,
          formatter:'checkbox',edittype:'checkbox'},
      {label:'Тариф',name:'gtar', index:'gtar', width:100, editable: false, align:'left',edittype:'text'},
      {label:'Інспектор',name:'cntrl', index:'cntrl', width:100, editable: false, align:'left',edittype:'text'},
      {label:'Підкл.',name:'activ', index:'activ', width:30, editable: false, align:'right',hidden:false,
          formatter:'checkbox',edittype:'checkbox'},
      {label:'Прац.РЕМ',name:'rem_worker', index:'rem_worker', width:30, editable: false, align:'right',hidden:false,
          formatter:'checkbox',edittype:'checkbox'},
      {label:'Не прож.',name:'not_live', index:'not_live', width:30, editable: false, align:'right',hidden:false,
          formatter:'checkbox',edittype:'checkbox'},
      {label:'Контроль',name:'pers_cntrl', index:'pers_cntrl', width:30, editable: false, align:'right',hidden:false,
          formatter:'checkbox',edittype:'checkbox'},
      {label:'Зел.тариф.',name:'green_tarif', index:'green_tarif', width:30, editable: false, align:'right',hidden:false,
          formatter:'checkbox',edittype:'checkbox'},
      {label:'№ субс.',name:'n_subs', index:'n_subs', width:50, editable: false, align:'left',edittype:'text'},              
      {label:'Опал.пл.',name:'heat_area', index:'heat_area', width:50, editable: false, align:'left',edittype:'text'},              
      {label:'Тип житла',name:'house_kind', index:'house_kind', width:100, editable: false, align:'left',edittype:'text'},
      {label:'Прим.',name:'note', index:'note', width:100, editable: false, align:'left',edittype:'text'},
 
      {label:'Дт.нач', name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Дт.кон',name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Мес.созд', name:'period_open',index:'period_open', width:80, editable: false, align:'left',edittype:'text',formatter:'date'},
      {label:'Время созд.', name:'dt_open',index:'dt_open', width:100, editable: false, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {label:'Сотр.созд', name:'user_name_open',index:'user_name_open', width:80, editable: false, align:'left',edittype:'text'},

      {label:'Мес.удал.', name:'period_close',index:'period_close', width:80, editable: false, align:'left',edittype:'text',formatter:'date'},
      {label:'Время удал.', name:'dt_close',index:'dt_close', width:100, editable: false, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {label:'Сотр.удал.', name:'user_name_close',index:'user_name_close', width:80, editable: false, align:'left',edittype:'text'}

    ],
    pager: '#mainhistory_tablePager',
    rowNum:50,
    sortname: 'dt_b',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: '',
    pgbuttons: false,
    pgtext: null, 
    hiddengrid: false,
    jsonReader : {repeatitems: false},
    postData:{'p_id': id_paccnt},
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');}

  }).navGrid('#mainhistory_tablePager',
        {edit:paccnt_hist_edit,add:false,del:paccnt_hist_del,search:false,
            edittext: 'Редагувати',
            deltext: 'Видалити' },
        {width:300,reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterHistEdit,
            zIndex:1234
        }, 
        {}, 
        {reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterHistEdit,
            zIndex:1234
        }, 
        {}         
        ); 
        
};   

$('#fAccEdit *').filter('input,select').keypress(function(e){ 
    if ( e.which == 13 )     
        {
            var focusable = $('#fAccEdit *').filter('input:text,select,textarea').filter(':visible').filter(':enabled');//.filter(':not([readonly]),:selected');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
 }); 

$('#pMeterParam *').filter('input,select').keypress(function(e){
    if ( e.which == 13 )     
        {
            var focusable = $('#pMeterParam *').filter('input:text,select,textarea').filter(':visible').filter(':enabled').filter(':not([readonly]),:selected');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
 }); 

$('#pLgtParam *').filter('input,select').keypress(function(e){
    if ( e.which == 13 )     
        {
            var focusable = $('#pLgtParam *').filter('input:text,select,textarea').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
 }); 

$('#pDogovorParam *').filter('input,select').keypress(function(e){
    if ( e.which == 13 )     
        {
            var focusable = $('#pDogovorParam *').filter('input:text,select,textarea').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
 }); 

$('#pPlombParam *').filter('input,select').keypress(function(e){
    if ( e.which == 13 )     
        {
            var focusable = $('#pPlombParam *').filter('input:text,select,textarea').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
 }); 

$('#pNotliveParam *').filter('input,select').keypress(function(e){
    if ( e.which == 13 )     
        {
            var focusable = $('#pNotliveParam *').filter('input:text,select,textarea').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
 }); 
 
$('#fAbonEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 )     
        {
            var focusable = $('#fAbonEdit *').filter('input:text,select,textarea').filter(':visible').filter(':enabled').filter(':not([readonly]),:selected');
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

   jQuery("#btTarFamily").click( function() { 

    createFamilyGrid(-1);

//    jQuery("#grid_lgtfamily").css({'left': jQuery("#fid_gtar").offset().left+1, 'top': jQuery("#fid_gtar").offset().top+20});
    jQuery("#grid_lgtfamily").css({'left': 120, 'top': 130});
    jQuery("#grid_lgtfamily").toggle( );

   });


});

function SelectAbonExternal(id, name) {
    if(SelectAbonTarget=='fAccEdit')
    {
        $("#fAccEdit").find("#fid_abon").attr('value',id );
        $("#fAccEdit").find("#fabon").attr('value',name );    
    }

    if(SelectAbonTarget=='fDogovorParam')
    {
        $("#fDogovorParam").find("#fid_abon").attr('value',id );
        $("#fDogovorParam").find("#fagreem_abon").attr('value',name );    
    }

}

function SelectPersonExternal(id, name) {
    
        $(SelectPersonTarget).attr('value',id );
        $(SelectPersonStrTarget).attr('value',name );    
    
}

function SelectTarExternal(id, name) {
        $("#fAccEdit").find("#fid_gtar").attr('value',id );
        $("#fAccEdit").find("#fgtar").attr('value',name );    
    
}

function SelectAddrExternal(code, name) {
    
        $(SelectAdrTarget).attr('value',code );
        $(SelectAdrStrTarget).attr('value',name );    
    
} 

function RefreshMetersExternal(id, name) {
        $("#paccnt_meters_table").jqGrid('setGridParam',{'postData':{'p_id': id_paccnt, 'free_only': 0, 'hist_mode': 0}}).trigger('reloadGrid');
        $("#paccnt_works_table").jqGrid('setGridParam',{'postData':{'p_id': id_paccnt}}).trigger('reloadGrid');
         innerLayout.resizeAll(); 


}


// обработчик, который вызываетя перед отправкой формы
function AccFormBeforeSubmit(formData, jqForm, options) { 

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

    if (btn=='arch')
    {
       if ($("#fAccEdit").find("#fsaldo").val()!='0.00' )
            {
                alert("У абонента ненульове сальдо! Неможна відправити в архив!")
                return false;
            }

       if ($("#fAccEdit").find("#factiv").prop('checked')==true)
            {
                alert("Абонент повинен бути відключений перед тим як відправлений в архив!")
                return false;
            }
    }


    if((btn=='edit')||(btn=='add')||(btn=='arch')||(btn=='unarch'))
    {
       if (r_edit!=3){alert("Немає прав!");return false;}        
       if (form_edit_lock == 1) return false;
       if(!submit_form.validate().form())  {return false;}
       else {
        if ((btn=='edit')||(btn=='arch')||(btn=='unarch'))
            {
               $("#dialog-changedate").dialog({ 
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
			buttons: {
				"Ok": function() {
                                          var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
  
                                          submit_form[0].change_date.value = cur_dt_change;
                                          if (btn=='arch')
                                          {
                                              $("#fAccEdit").find("#archive_label").show();  
                                              //submit_form[0].archive.value = 1;    
                                              $("#fAccEdit").find("#farchive").prop('checked',true);
                                              $("#fAccEdit").find("#fdt_archive").datepicker( "setDate" , cur_dt_change );
                                          }
                                          if (btn=='unarch')
                                          {
                                              $("#fAccEdit").find("#archive_label").hide();  
                                              $("#fAccEdit").find("#farchive").prop('checked',false);
                                              //$("#fAccEdit").find("#fdt_archive").datepicker( "setDate" , cur_dt_change );
                                          }


                                          submit_form.ajaxSubmit(form_options);    
                                          form_edit_lock=1;
					$( this ).dialog( "close" );
				},
				"Отмена": function() {
					$( this ).dialog( "close" );
				}
			}
                        /*
                        ,focus: function() {
                            $(this).on("keyup", function(e) {
                            if (e.keyCode === 13) {
                                $(this).parent().find("button:contains('Ok')").trigger("click");
                            return false;
                            }
                        })
                    }*/
                });
                
                $("#dialog-changedate").dialog('open');
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
function AccFormSubmitResponse(responseText, statusText)
{            
             errorInfo = responseText;
             form_edit_lock=0;
             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {  // insert
                 
                 
               id_paccnt =  errorInfo.id;
               $("#fAccEdit").find("#bt_add").hide();
               $("#fAccEdit").find("#bt_edit").show();   
               $("#fAccEdit").find("#bt_showtree").show(); 
               $("#fAccEdit").find("#bt_saldo").show(); 
               $("#fAccEdit").find("#bt_subs").show(); 
               $("#fAccEdit").find("#bt_switch").show(); 
               $("#fAccEdit").find("#bt_delabon").show();   
               $("#fAccEdit").find("#bt_mainhistory").show();   

               $("#pwork_center").show();
               $("#fpaccnt_params").find("#pid_paccnt").attr('value',id_paccnt );   
               $("#fAccEdit").find("#fid").attr('value',id_paccnt );
               
               jQuery('#paccnt_meters_table').jqGrid('setGridParam',{'postData':{'p_id':id_paccnt}}).trigger('reloadGrid');
               jQuery('#paccnt_lgt_table').jqGrid('setGridParam',{'postData':{'p_id':id_paccnt}}).trigger('reloadGrid');
               
               jQuery("#paccnt_dogovor_table").jqGrid('setGridParam',{'postData':{'p_id':id_paccnt}}).trigger('reloadGrid');
               jQuery("#paccnt_plomb_table").jqGrid('setGridParam',{'postData':{'p_id': id_paccnt, 'arch_mode':1}}).trigger('reloadGrid');
               jQuery("#paccnt_notlive_table").jqGrid('setGridParam',{'postData':{'p_id':id_paccnt}}).trigger('reloadGrid');
               jQuery("#paccnt_works_table").jqGrid('setGridParam',{'postData':{'p_id':id_paccnt}}).trigger('reloadGrid');
               
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
              
               return [true,errorInfo.errstr]
              };              
             
             if (errorInfo.errcode==-2) {  // delete
                window.location = 'abon_en_main.php';
             }
             if (errorInfo.errcode==1) {
                 
                if ($("#fAccEdit").find("#factiv").prop('checked')==true)
                {
                    $("#fAccEdit").find("#switch_label").html('' );    
                }
                 
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
              
               CommitJQFormVal(submit_form);
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};   

};

function LoadAccData(data)
{
  //   var str = $.param(data); 
  //alert(str); 
  if (data.errcode===undefined)
  {    
    $("#fAccEdit").resetForm();
    $("#fAccEdit").clearForm();
      
      
    $("#fAccEdit").find("#fid").attr('value',data.id );
    $("#fAccEdit").find("#fid_dep").attr('value', data.id_dep);
    
    $("#fAccEdit").find("#fbook").attr('value', data.book);
    $("#fAccEdit").find("#fcode").attr('value', data.code);
    $("#fAccEdit").find("#fsaldo").attr('value', data.e_val);

    $("#fAccEdit").find("#faddr").attr('value', data.addr);
    $("#fAccEdit").find("#faddr_str").attr('value', data.addr_str);

    $("#fAccEdit").find("#fid_abon").attr('value', data.id_abon);
    $("#fAccEdit").find("#fabon").attr('value', data.abon);

    $("#fAccEdit").find("#fid_gtar").attr('value', data.id_gtar);
    $("#fAccEdit").find("#fgtar").attr('value', data.gtar);
    
    //if ((data.id_gtar==5)||(data.id_gtar==6)||(data.id_gtar==8)||(data.id_gtar==9)||(data.id_gtar==12)||
    //    (data.id_gtar==13))
    // {
       $("#fAccEdit").find("#btTarFamily").prop('disabled', false);
    //}   
    //else
    //{
    //   $("#fAccEdit").find("#btTarFamily").prop('disabled', true);
    //}   

    $("#fAccEdit").find("#fid_cntrl").attr('value', data.id_cntrl);
    $("#fAccEdit").find("#fcntrl").attr('value', data.cntrl);
    $("#fAccEdit").find("#fidk_house").attr('value', data.idk_house);
    
    $("#fAccEdit").find("#fn_subs").attr('value', data.n_subs);
    $("#fAccEdit").find("#fheat_area").attr('value', data.heat_area);

    $("#fAccEdit").find("#fnote").attr('value', data.note);
    $("#fAccEdit").find("#fdt_b").datepicker( "setDate" , data.dt_b );
    $("#fAccEdit").find("#fdt_dod").datepicker( "setDate" , data.dt_dod );
    
    $("#fAccEdit").find("#fsubs_name").attr('value', data.subs_name);
    
    
    if (data.activ=="t")
    {
      $("#fAccEdit").find("#factiv").prop('checked',true);
    }
    else
    {
      $("#fAccEdit").find("#factiv").prop('checked',false);
    }

    if (data.rem_worker=="t")
    {
      $("#fAccEdit").find("#frem_worker").prop('checked',true);
    }
    else
    {
      $("#fAccEdit").find("#frem_worker").prop('checked',false);
    }

    if (data.not_live=="t")
    {
      $("#fAccEdit").find("#fnot_live").prop('checked',true);
    }
    else
    {
      $("#fAccEdit").find("#fnot_live").prop('checked',false);
    }

    if (data.archive==1)
    {
        
      $("#fAccEdit").find("#archive_label").show();  
      $("#fAccEdit").find("#farchive").prop('checked',true);
      $("#fAccEdit").find("#bt_archabon").hide();
      $("#fAccEdit").find("#bt_unarchabon").show();
      $("#fAccEdit").find("#fdt_archive").datepicker( "setDate" , data.dt_archive );
      
    }
    else
    {
      $("#fAccEdit").find("#archive_label").hide();
      $("#fAccEdit").find("#bt_archabon").show();
      $("#fAccEdit").find("#bt_unarchabon").hide();
    }

    if (data.pers_cntrl=="t")
    {
      $("#fAccEdit").find("#fpers_cntrl").prop('checked',true);
    }
    else
    {
      $("#fAccEdit").find("#fpers_cntrl").prop('checked',false);
    }

    if (data.green_tarif=="t")
    {
      $("#fAccEdit").find("#fgreen_tarif").prop('checked',true);
    }
    else
    {
      $("#fAccEdit").find("#fgreen_tarif").prop('checked',false);
    }

    if (data.recalc_subs==1)
    {
      $("#fAccEdit").find("#frecalc_subs").prop('checked',true);
    }
    else
    {
      $("#fAccEdit").find("#frecalc_subs").prop('checked',false);
    }


    $("#fAccEdit").find("#switch_label").html(data.sw_info );    
    //$("#fAccEdit").find("#sector_label").html(data.sector_info );    
    $("#fAccEdit").find("#fid_sector").attr('value', data.id_sector);
    $("#fAccEdit").find("#fsector").attr('value', data.sector_info);
    
    
    
    $("#fAccEdit").find("#subs_label").html(data.subs_info );    
    
    $("#fpaccnt_params").find("#pid_paccnt").attr('value',data.id );    


    //$("#fAccEdit").find("#bt_saldo").html("Сальдо("+data.e_val+")") ;
    
//    $("#fCommonParam").find("#fdt_change").datepicker( "setDate" , data.dt_change_str );
                    
    //if (tree_mode==1)                

    CommitJQFormVal($("#fAccEdit"));
    
    //jQuery('#hist1_table').jqGrid('setGridParam',{'postData':{'p_id':data.id}}).trigger('reloadGrid');
    //EditorLayout.resizeAll();
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

 function processAfterHistEdit(response, postdata) {
            //alert(response.responseText);
            if (response.responseText=='') { return [true,'']; }
            else
            {
             errorInfo = jQuery.parseJSON(response.responseText);
             
             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]}; 
             
             if (errorInfo.errcode==1) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
               //jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};              
            }
        }
//-----------------------------------------------------------------------------