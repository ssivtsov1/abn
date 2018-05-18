var edit_row_id=0;
var elapsed_seconds = 0;
var timerId ;
var spinner_opts;
//var r_edit = 3;
var cur_doc_id = 0;
var form_edit_lock=0;

$.validator.methods.number = function (value, element) {
    return this.optional(element) || /^-?(?:\d+|\d{1,3}(?:[\s\.,]\d{3})+)(?:[\.,]\d+)?$/.test(value);
}

jQuery(function(){ 


  //$('#load_in_progress').hide();
  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
		buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");
    
  jQuery("#fPayEdit :input").addClass("ui-widget-content ui-corner-all");        
  //dt_mmgg = Date.parse( mmgg, "dd.MM.yyyy");
  //str_mmgg = dt_mmgg.toString("dd.MM.yyyy");
  //$("#fmmgg").datepicker( "setDate" , dt_mmgg.toString("dd.MM.yyyy") );
  $("#fmmgg").datepicker( "setDate" , mmgg );


  //----------------------------------------------------------------------------  
  jQuery('#headers_table').jqGrid({
    url: 'abon_en_bankpay_header_data.php',
    editurl: '',
    datatype: 'json',
    mtype: 'POST',
    height:200,
    width:800,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {label:'Дата',name:'reg_date', index:'reg_date', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date', editrules:{required:true}},
      {label:'Номер',name:'reg_num', index:'reg_num', width:80, editable: true, align:'left',edittype:"text"},

      {label:'Походження',name:'id_origin', index:'id_origin', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lorigin},stype:'select'},
      {label:'origin',name:'origin', index:'origin', width:80, editable: true, align:'left',edittype:"text", hidden:true},
      {label:'Файл',name:'name_file', index:'name_file', width:80, editable: true, align:'left',edittype:"text"},
      
      {label:'Кільк. всього',name:'cnt_all', index:'cnt_all', width:70, editable: true, align:'right', hidden:false},
      {label:'Сума всього',name:'sum_all', index:'sum_all', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           

      {label:'Кільк.брак',name:'cnt_bad', index:'cnt_bad', width:70, editable: true, align:'right', hidden:false},
      {label:'Сума брак',name:'sum_bad', index:'sum_bad', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           


      {label:'Період',name:'mmgg_str', index:'mmgg_str', width:80, editable: true, align:'left',edittype:'text', hidden:true},
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},
      {label:'Закр.',name:'flock', index:'flock', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}}
    ],
    pager: '#headers_tablePager',
    autowidth: true,
    rowNum:100,
    rowList:[20,50,100,300,500],
    sortname: 'reg_date',
    sortorder: 'desc',
    viewrecords: true,
    gridview: true,
    caption: 'Пачки',
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
         jQuery('#pay_table').jqGrid('setGridParam',{'postData':{'p_id':0}}).trigger('reloadGrid');                
    }
    
  },
    
    onSelectRow: function(id) { 
          if (id!=cur_doc_id)
         {
          cur_doc_id = id;
          jQuery('#pay_table').jqGrid('setGridParam',{datatype:'json','postData':{'p_id':cur_doc_id}}).trigger('reloadGrid');        
         }          
          //$("#pFooterBar").find("#fcnt_all").attr('value',$("#headers_table").jqGrid('getCell',cur_doc_id,'real_cnt'));
          //$("#pFooterBar").find("#fsum_all").attr('value',$("#headers_table").jqGrid('getCell',cur_doc_id,'real_sum'));
      
    },
    
    ondblClickRow: function(id){ 
/*        
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 

            if(gsr)
            { 
            
            }
       */ 
     } ,  

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#headers_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  jQuery("#headers_table").jqGrid('filterToolbar','');
  jQuery("#headers_tablePager_right").css("width","150px");
//==============================================================================


  jQuery('#pay_table').jqGrid({
    url:'abon_en_bankpay_data.php',
    editurl: '',
   // datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:400,
    //width:800,
    shrinkToFit : false,
    autowidth: true,
    //scroll: 0, 
    colNames:[],
    colModel :[ 

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true}, 
    {name:'old_paccnt', index:'old_paccnt', width:40, editable: false, align:'center',hidden:true}, 
    {name:'id_headpay', index:'id_headpay', width:40, editable: false, align:'center',hidden:true}, 
    
    
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    {label:'Рах',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},                
    {label:'Абонент',name:'abon', index:'abon', width:150, editable: true, align:'left',edittype:'text'},
    {label:'Місто',name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text', hidden:town_hidden},
    {label:'Адреса',name:'addr', index:'addr', width:150, editable: true, align:'left',edittype:'text'},
    {label:'Дільниця',name:'sector', index:'sector', width:100, editable: true, align:'left',edittype:'text'},
    {label:'Ідент',name:'ident', index:'ident', width:50, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lstatus},stype:'select',hidden:false},

    {label:'Кн/Рах банк',name:'abcount', index:'abcount', width:60, editable: true, align:'left',edittype:'text'},
    {label:'ПІБ банк',name:'fio', index:'fio', width:150, editable: true, align:'left',edittype:'text'},

    {label:'Дата',name:'date_ob', index:'date_ob', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
    {label:'Дата плат.',name:'pdate', index:'pdate', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Сума',name:'summ', index:'summ', width:70, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           

    {label:'Місто/село',name:'name_citi', index:'name_citi', width:100, editable: true, align:'left',edittype:'text'},                    
    {label:'Вулиця',name:'name_strit', index:'name_strit', width:100, editable: true, align:'left',edittype:'text'},                        

    {label:'буд.',name:'house', index:'house', width:50, editable: true, align:'left',edittype:'text'},
    {label:'корп.',name:'korpus', index:'korpus', width:50, editable: true, align:'left',edittype:'text'},
    {label:'буква',name:'bukva', index:'bukva', width:50, editable: true, align:'left',edittype:'text'},
    {label:'кварт.',name:'kvartira', index:'kvartira', width:60, editable: true, align:'left',edittype:'text'},    


    {label:'Дата поч.',name:'dateb', index:'dateb', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
    {label:'Дата кін.',name:'datee', index:'datee', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Попер.пок.',name:'countb', index:'countb', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text' },           
    {label:'Поточні пок.',name:'counte', index:'counte', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text' },           

    {label:'Спожито',name:'countd', index:'countd', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text' },           

    {label:'Файл',name:'name_file', index:'name_file', width:50, editable: true, align:'left',edittype:'text', hidden:true},
    {label:'Походж.',name:'source', index:'source', width:50, editable: true, align:'left',edittype:'text', hidden:true},    
    {label:'Пачка',name:'reg_num', index:'reg_num', width:50, editable: true, align:'left',edittype:'text', hidden:true},    
    {label:'Період',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', hidden:true},
    //{label:'Идент',name:'ident', index:'ident', width:50, editable: true, align:'left',edittype:'text', hidden:false}

    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},
    {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}

    ],
    pager: '#pay_tablePager',
    rowNum:100,
    rowList:[20,50,100,300,500],
    sortname: 'book',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Оплата через банк',
    //hiddengrid: false,
    hidegrid: false,
    postData:{'p_id':0},
    //postData:{'p_mmgg': mmgg },
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
/*
    onPaging : function(but) { 
                id_paccnt=0;
                $(this).jqGrid('setGridParam',{'postData':{'p_mmgg': mmgg, 'selected_id':id_paccnt}});        
    },    
*/    
    
    ondblClickRow: function(id){ 
    
    var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 

            pay_validator.resetForm();  //для сброса состояния валидатора
            $("#fPayEdit").resetForm();
            $("#fPayEdit").clearForm();
          
            $("#pay_table").jqGrid('GridToForm',gsr,"#fPayEdit"); 
            $("#fPayEdit").find("#foper").attr('value','edit'); 
            
            edit_row_id = id;

            $("#dialog_payform").dialog('open');          
            
            if (r_edit==3)
            {
              $("#fPayEdit").find("#bt_edit").prop('disabled', false);
            }
            else
            {
              $("#fPayEdit").find("#bt_edit").prop('disabled', true);
            }
        
      } else { alert("Please select Row") }       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#pay_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

jQuery("#pay_table").jqGrid('filterToolbar','');
jQuery("#pay_tablePager_right").css("width","150px");

//------------------редактирование одиночного  ---------------------

 var pay_form_options = { 
    dataType:"json",
    beforeSubmit: PayFormBeforeSubmit, // функция, вызываемая перед передачей 
    success: PayFormSubmitResponse // функция, вызываемая при получении ответа
  };

$("#dialog_payform").dialog({
			resizable: true,
                        width:700,
			modal: true,
                        autoOpen: false,
                        title:"Оплата"
});
fPay_ajaxForm = $("#fPayEdit").ajaxForm(pay_form_options);
        
// опции валидатора общей формы
var pay_form_valid_options = { 

		rules: {
			//indic: "required",
                        //ind_date:"required"
		},
		messages: {
			//ind_date: "Вкажіть дату",
                        //indic: "Вкажіть показники!"
		}
};

pay_validator = $("#fPayEdit").validate(pay_form_valid_options);


$("#fPayEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_payform").dialog('close');                           
});

//-------------------------------------------------------------
    jQuery("#btPaccntSel").click( function() { 
        createAbonGrid();
        
        abon_target_id = $('#fPayEdit').find('#fid_paccnt');
        abon_target_name = $('#fPayEdit').find('#fabon');
        abon_target_book = $("#fPayEdit").find('#fbook');
        abon_target_code = $("#fPayEdit").find('#fcode');

       jQuery("#grid_selabon").css({'left': $('#fPayEdit').find('#fbook').offset().left+1, 'top': $('#fPayEdit').find('#fbook').offset().top+20});
       jQuery("#grid_selabon").toggle( );
       
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
	,	center__paneSelector:	"#ppay_table"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       north__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#headers_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#headers_table").jqGrid('setGridHeight',_pane.innerHeight()-135);
        }
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#pay_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#pay_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }

	});

        outerLayout.resizeAll();
        outerLayout.close('south');     


    jQuery(".btn").button();
    jQuery(".btnSel").button({ icons: {primary:'ui-icon-folder-open'} });
        
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open'); });
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   $("#message_zone").dialog({ autoOpen: false });
   $("#calc_progress").dialog({autoOpen: false, resizable: false});
    jQuery("#pActionBar :input").addClass("ui-widget-content ui-corner-all");
    
    
   
    
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       cur_doc_id = 0;
       $('#headers_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
       
   });
  
 //---------------------------------------------------------------------------
 
 jQuery("#pay_table").jqGrid('navButtonAdd','#pay_tablePager',{caption:"Редагувати",
    onClickButton:function(){ 

      if ($("#pay_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#pay_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            pay_validator.resetForm();  //для сброса состояния валидатора
            $("#fPayEdit").resetForm();
            $("#fPayEdit").clearForm();
          
            $("#pay_table").jqGrid('GridToForm',gsr,"#fPayEdit"); 
            $("#fPayEdit").find("#foper").attr('value','edit'); 
            
            $("#dialog_payform").dialog('open');          
            
            if (r_edit==3)
            {
              $("#fPayEdit").find("#bt_edit").prop('disabled', false);
            }
            else
            {
              $("#fPayEdit").find("#bt_edit").prop('disabled', true);
            }

        }
    } 
});
//----------------------------------------------------------------
 
 jQuery("#pay_table").jqGrid('navButtonAdd','#pay_tablePager',{caption:"Друкувати все",
    onClickButton:function(){ 

        var postData = jQuery("#pay_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
       //alert(json_str );
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       $('#freps_params').find("#fdt_b").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#foper").attr('value', "bank_load_list"); 
       
       $('#freps_params').find("#freport_caption").attr('value', "Пачка № "+
           $("#headers_table").jqGrid('getCell',cur_doc_id,'reg_num')+' від '+
           $("#headers_table").jqGrid('getCell',cur_doc_id,'reg_date')+' ('+ 
           $("#headers_table").jqGrid('getCell',cur_doc_id,'origin')+')' ); 
       
       
       $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Друк журналу',
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

 //---------------------------------------------------------------------------
 
 jQuery("#pay_table").jqGrid('navButtonAdd','#pay_tablePager',{caption:"Друкувати брак",
    onClickButton:function(){ 

        var postData = jQuery("#pay_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
        
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       $('#freps_params').find("#fdt_b").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#foper").attr('value', "bank_load_bad");        
       
       $('#freps_params').find("#freport_caption").attr('value', "Брак пачка № "+
           $("#headers_table").jqGrid('getCell',cur_doc_id,'reg_num')+' від '+
           $("#headers_table").jqGrid('getCell',cur_doc_id,'reg_date')+' ('+ 
           $("#headers_table").jqGrid('getCell',cur_doc_id,'origin')+')' ); 
       
       
       
       $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Друк журналу',
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
 
$('#fPayEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fPayEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
 }); 
 
$("#show_peoples").click( function() {
     jQuery("#headers_table").jqGrid('showCol',["user_name"]);
     jQuery("#pay_table").jqGrid('showCol',["user_name"]);
     jQuery("#pay_table").jqGrid('showCol',["dt"]);
});
 
 
});

function PayFormBeforeSubmit(formData, jqForm, options) { 

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
function PayFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;
             
             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             
             if (errorInfo.errcode==1) {
                 
               //jQuery("#pay_table").jqGrid('FormToGrid',cur_indic_id,"#fPayEdit"); 
               jQuery('#pay_table').trigger('reloadGrid');
               
               jQuery("#dialog_payform").dialog('close');
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_payform").dialog('close');                                            
               jQuery('#pay_table').trigger('reloadGrid'); 
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');

               return [true,errorInfo.errstr]};

             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};   

};
