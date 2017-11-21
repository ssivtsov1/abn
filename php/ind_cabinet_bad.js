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

//==============================================================================

  jQuery('#indic_table').jqGrid({
    url:'ind_cabinet_bad_data.php',
    datatype: 'json',
    mtype: 'POST',
    height:200,
    width:400,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    
    {label:'Книга/рах.',name:'lic', index:'lic', width:40, editable: true, align:'left',edittype:'text'},

    {label:'Адреса',name:'address', index:'address', width:150, editable: true, align:'left',edittype:'text'},
    {label:'Абонент',name:'abon', index:'abon', width:100, editable: true, align:'left',edittype:'text'},
    
    {label:'№ ліч.',name:'meter_num',index:'meter_num', width:80, editable: true, align:'left',edittype:'text'},

    {label:'Зона',name:'id_zone', index:'id_zone', width:60, editable: true, align:'right',hidden:false },   

    {label:'Дата',name:'dt_ind', index:'dt_ind', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Показники',name:'curr_ind', index:'curr_ind', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},

    {label:'Дата введення',name:'curr_dt', index:'curr_dt', width:100, editable: true, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
    
    {label:'Походження',name:'id_operation', index:'id_operation', width:60, editable: true, align:'left',
                            edittype:'select',formatter:'select',stype:'select',editoptions:{value:lid_operation}  }

    /*{label:'Статус',name:'id_status', index:'id_status', width:60, editable: true, align:'left',
                            edittype:'select',formatter:'select',stype:'select',editoptions:{value:lid_status}  },  */
        

    ],
    pager: '#indic_tablePager',
    autowidth: true,
    //shrinkToFit : false,
    rowNum:500,
 //   rowList:[50,100,200],
    sortname: 'lic',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Показники',
    //hiddengrid: false,
    hidegrid: false,   
    //multiselect: true,
    //pgbuttons: false,     // disable page control like next, back button
    //pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    postData:{'p_mmgg': mmgg},
    
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
            /*
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
            */
        }

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

 jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{caption:"Друкувати",
    onClickButton:function(){ 

        var postData = jQuery("#indic_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       $('#freps_params').find("#fdt_b").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#fperiod_str").attr('value',$("#pActionBar").find("#fmmgg").val() ); 

       $("#dialog-confirm").find("#dialog-text").html('Виберіть варіант друку');
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
//--------------------------------



$("#message_zone").dialog({autoOpen: false});

$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
$("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {jQuery("#message_zone").html('');});

jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});


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
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#indic_table").jqGrid('setGridWidth',_pane.innerWidth()-25);
            jQuery("#indic_table").jqGrid('setGridHeight',_pane.innerHeight()-140);
        }
        

	});


        outerLayout.resizeAll();
        outerLayout.close('south');             
        
        
   
   
$("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       
       $('#indic_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
       
});
   
/*
if (r_edit!=3)
{
  $("#pActionBar").find("#bt_load").prop('disabled', true);
}
*/   
/*
if (r_edit!=3)
{
  $('#btn_header_bills').addClass('ui-state-disabled');
  $('#btn_header_indic').addClass('ui-state-disabled');
  $('#btn_header_new').addClass('ui-state-disabled');
  $('#btn_header_edit').addClass('ui-state-disabled');
  $('#btn_header_del').addClass('ui-state-disabled');
}
*/
//=========================================================================
/*

 var indic_form_options = { 
    dataType:"json",
    beforeSubmit: IndBeforeSubmit, // функция, вызываемая перед передачей 
    success: IndSubmitResponse // функция, вызываемая при получении ответа
  };

fIndic_ajaxForm = $("#fLoad").ajaxForm(indic_form_options);
        
// опции валидатора общей формы
var indic_form_valid_options = { 

		rules: {
			mmgg: "required",
                        alldata:"required"
		},
		messages: {
			mmgg: "Вкажіть дату",
                        alldata: "Виберіть показники!"
		}
};

indic_validator = $("#fLoad").validate(indic_form_valid_options);
*/
/*
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
*/

/*
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
*/


}); 
