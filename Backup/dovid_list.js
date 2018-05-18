var cur_doc_id=0;
var cur_sprav_id=0;
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

  jQuery('#dovid_table').jqGrid({
    url:'dovid_list_data.php',
    //editurl: 'ind_pack_indic_edit.php',
    datatype: 'json',
    //datatype: 'local',
    mtype: 'POST',
    height:200,
    width:400,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
 
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},                
    {label:'Місто',name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text', hidden:town_hidden},
    {label:'Адреса',name:'address', index:'address', width:150, editable: true, align:'left',edittype:'text'},                    
    {label:'Абонент',name:'abon', index:'abon', width:100, editable: true, align:'left',edittype:'text'},

    {label:'№ довідки',name:'num_sp',index:'num_sp', width:80, editable: true, align:'left',edittype:'text'},
    {label:'Дата довідки',name:'dt_sp', index:'dt_sp', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},


    {label:'Тип дов.',name:'doc_type', index:'doc_type', width:60, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:ltype},stype:'text'},

    {label:'Період з',name:'date_start', index:'date_start', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Період по',name:'date_end', index:'date_end', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'№ запита',name:'num_input',index:'num_input', width:80, editable: true, align:'left',edittype:'text'},
    {label:'Дата запита',name:'dt_input', index:'dt_input', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Людей',name:'people_count', index:'people_count', width:40, editable: true, align:'left',edittype:'text'},
    {label:'Опал.площа',name:'heat_area', index:'heat_area', width:40, editable: true, align:'left',edittype:'text'},
    {label:'Норма з запиту',name:'social_norm', index:'social_norm', width:40, editable: true, align:'left',edittype:'text',hidden:true},
    {label:'Розрах.норму',name:'show_norm', index:'show_norm', width:40, editable: true, align:'left',edittype:'text',hidden:false},
    
    {label:'id_person',name:'id_person', index:'id_person', width:10, edittype:'text',hidden:true},
    {label:'Виконавець',name:'person', index:'person', width:60, editable: true, align:'left',edittype:'text'},
    {label:'Дата друку',name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
        
    {label:'hotw',name:'hotw', index:'hotw', width:10, edittype:'text',hidden:true},
    {label:'hotw_gas',name:'hotw_gas', index:'hotw_gas', width:10, edittype:'text',hidden:true},
    {label:'coldw',name:'coldw', index:'coldw', width:10, edittype:'text',hidden:true},
    {label:'plita',name:'plita', index:'plita', width:10, edittype:'text',hidden:true}

    ],
    pager: '#dovid_tablePager',
    autowidth: true,
    //shrinkToFit : false,
    rowNum:500,
 //   rowList:[50,100,200],
    sortname: 'dt_sp',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Довідки',
    //hiddengrid: false,
    hidegrid: false,   
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
          cur_sprav_id = id;
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
          
            $("#dovid_table").jqGrid('GridToForm',gsr,"#fIndicEdit"); 
            $("#fIndicEdit").find("#foper").attr('value','edit'); 
            
            if ($("#fIndicEdit").find("#findic").attr('value')=='0')
            {
                $("#fIndicEdit").find("#findic").attr('value','')
            }
            cur_sprav_id = id;

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

  }).navGrid('#dovid_tablePager',
         {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ).jqGrid('filterToolbar','');; 

jQuery("#dovid_tablePager_right").css("width","150px");
//==============================================================================

$("#message_zone").dialog({autoOpen: false});

$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
$("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {jQuery("#message_zone").html('');});

jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});


//jQuery("#fIndicEdit :input").addClass("ui-widget-content ui-corner-all");

/*
$("#dialog_indicform").dialog({
			resizable: true,
                        width:750,
			modal: true,
                        autoOpen: false,
                        title:"Показники"
});
*/
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
            jQuery("#dovid_table").jqGrid('setGridWidth',_pane.innerWidth()-20);
            jQuery("#dovid_table").jqGrid('setGridHeight',_pane.innerHeight()-135);
        }
        

	});


        outerLayout.resizeAll();
        outerLayout.close('south');             
        
        
   
   
$("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       
       $('#dovid_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
       
});


 jQuery("#dovid_table").jqGrid('navButtonAdd','#dovid_tablePager',{caption:"Друкувати",
    onClickButton:function(){ 

    var gsr = $("#dovid_table").jqGrid('getGridParam','selrow'); 
    if(gsr)
    { 
        //alert(json_str );
       $("#dovid_table").jqGrid('GridToForm',gsr,"#freps_params");
       
       $('#freps_params').find("#fdt_b").attr('value', $("#dovid_table").jqGrid('getCell',cur_sprav_id,'date_start')  ); 
       $('#freps_params').find("#fdt_e").attr('value', $("#dovid_table").jqGrid('getCell',cur_sprav_id,'date_end')  ); 
       $('#freps_params').find("#fperiod_str").attr('value', $("#pActionBar").find("#fmmgg").val()  ); 


       if($("#dovid_table").jqGrid('getCell',cur_sprav_id,'doc_type')==1)
           $('#freps_params').find("#foper").attr('value', "sprav1");
       
       if($("#dovid_table").jqGrid('getCell',cur_sprav_id,'doc_type')==2)
           $('#freps_params').find("#foper").attr('value', "sprav6");
       
       if($("#dovid_table").jqGrid('getCell',cur_sprav_id,'doc_type')==3)
           $('#freps_params').find("#foper").attr('value', "sprav12");           
           
       if($("#dovid_table").jqGrid('getCell',cur_sprav_id,'doc_type')==4)
           $('#freps_params').find("#foper").attr('value', "sprav1new");
       
       if($("#dovid_table").jqGrid('getCell',cur_sprav_id,'doc_type')==5)
           $('#freps_params').find("#foper").attr('value', "spravlgt");
       
       document.forms["freps_params"].submit();               

    } 
   }
});


}); 
