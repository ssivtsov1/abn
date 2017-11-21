var cur_doc_id=0;
var cur_doc_lock=0;
var cur_pay_id=0;
var validator = null;
var form_options;
var edit_validator= null;
var input_validator= null;
var cnt_current=0;
var summ_current=0;
var mmgg_pay;

var timerId ;
var spinner_opts;
var pay_input_lock = 0;
var form_edit_lock=0;
var sum_find =0;
var id_paccnt = 0;
var indic_edit_row_id=0;
var new_doc_id = 0;

payinput_inner = 220;

$.validator.methods.number = function (value, element) {
    return this.optional(element) || /^-?(?:\d+|\d{1,3}(?:[\s\.,]\d{3})+)(?:[\.,]\d+)?$/.test(value);
}

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


  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");
  
  $("#fmmgg").datepicker( "setDate" , mmgg );
  
  mmgg_pay = Date.parse( mmgg, "dd.MM.yyyy");
  mmgg_pay.add({months: -1});
  
  
  date_mmgg = Date.parse( mmgg, "dd.MM.yyyy");
  date_mmgg_next = Date.parse( mmgg, "dd.MM.yyyy");
  date_mmgg_next.add({days: -1, months: 1});

   
  $("#fPayInput").find("#fmmgg_pay").datepicker( "setDate" , mmgg_pay.toString("dd.MM.yyyy") );
  $("#calc_progress").dialog({autoOpen: false, resizable: false});
  
  //----------------------------------------------------------------------------  
  jQuery('#headers_table').jqGrid({
    url: 'payment_operations_header_data.php',
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

      {label:'Кількість кв.',name:'count_pay', index:'count_pay', width:40, editable: true, align:'right', hidden:false},
      {label:'Сума,грн',name:'sum_pay', index:'sum_pay', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           

      //{label:'Кількість факт.',name:'real_cnt', index:'real_cnt', width:40, editable: true, align:'right', hidden:false},
      //{label:'Сума факт',name:'real_sum', index:'real_sum', width:80, editable: true, align:'right',hidden:false,
      //                      edittype:'text',formatter:'number'},           

      {label:'Файл',name:'name_file', index:'name_file', width:80, editable: true, align:'left',edittype:"text"},


      {label:'Період',name:'mmgg_str', index:'mmgg_str', width:80, editable: true, align:'left',edittype:'text', hidden:false},
      {name:'Час внесення',name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:false},        
      {label:'Бл.',name:'user_lock', index:'user_lock', width:20, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}},
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
    postData:{'p_mmgg': mmgg, 'sum_find':sum_find},
    
    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     
     if (new_doc_id!=0)
     {
       $(this).setSelection(new_doc_id, true);
       new_doc_id=0;
     }
     else
       $(this).setSelection(first_id, true);
   
    }
    else
    {
         jQuery('#pay_table').jqGrid('setGridParam',{'postData':{'p_id':0, 'sum_find':sum_find}}).trigger('reloadGrid');                
    }
    
  },
    
    onSelectRow: function(id) { 
          if ((id!=cur_doc_id) || (sum_find!=0))
         {
          cur_doc_id = id;
          
          if ((jQuery(this).jqGrid('getCell',id,'user_lock') == "Yes")||
              (jQuery(this).jqGrid('getCell',id,'flock') == "Yes")) 
             cur_doc_lock = 1;
          else
             cur_doc_lock = 0; 
         
          jQuery('#pay_table').jqGrid('setGridParam',{datatype:'json','postData':{'p_id':cur_doc_id , 'sum_find':sum_find}}).trigger('reloadGrid');        
         }          
          //$("#pFooterBar").find("#fcnt_all").attr('value',$("#headers_table").jqGrid('getCell',cur_doc_id,'real_cnt'));
          //$("#pFooterBar").find("#fsum_all").attr('value',$("#headers_table").jqGrid('getCell',cur_doc_id,'real_sum'));
      
         if (cur_doc_lock==1)
         {
          $('#btn_pay_del').addClass('ui-state-disabled');
          $('#btn_pay_new').addClass('ui-state-disabled');
          $('#btn_pay_edit').addClass('ui-state-disabled');

          $('#btn_header_del').addClass('ui-state-disabled');
          $('#btn_header_input').addClass('ui-state-disabled');
         }
         else
         {
           if (r_edit==3)  
           {
            $('#btn_pay_del').removeClass('ui-state-disabled');
            $('#btn_pay_new').removeClass('ui-state-disabled');
            $('#btn_pay_edit').removeClass('ui-state-disabled');

            $('#btn_header_del').removeClass('ui-state-disabled');
            $('#btn_header_input').removeClass('ui-state-disabled');
                   
           }
             
         }
    },
    
    ondblClickRow: function(id){ 
        
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        
        if(selmode==1)
        {
           window.opener.SelectPayHeaderExternal(id, jQuery(this).jqGrid('getCell',id,'reg_date'),
                                              jQuery(this).jqGrid('getCell',id,'reg_num'),
                                              jQuery(this).jqGrid('getCell',id,'id_origin'),
                                              jQuery(this).jqGrid('getCell',id,'mmgg_str') );
           window.opener.focus();
           self.close();            
        }

        if(selmode==0) 
        {

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
                $("#fHeaderEdit").find("#bt_lock").show();
                $("#fHeaderEdit").find("#bt_unlock").show();

                $("#dialog_editform").dialog('open');         

                if ((r_edit==3)&&(cur_doc_lock==0))
                {
                    $("#fHeaderEdit").find("#bt_edit").prop('disabled', false);
                }
                else
                {
                    $("#fHeaderEdit").find("#bt_edit").prop('disabled', true);
                }
                
                if ((cur_doc_lock==1)&&(r_unlock==3))
                {
                    $("#fHeaderEdit").find("#bt_unlock").prop('disabled', false);
                }
                else
                {
                    $("#fHeaderEdit").find("#bt_unlock").prop('disabled', true);
                }
                
                if ((cur_doc_lock==0)&&(r_lock==3))
                {
                    $("#fHeaderEdit").find("#bt_lock").prop('disabled', false);
                }
                else
                {
                    $("#fHeaderEdit").find("#bt_lock").prop('disabled', true);
                }
                
            }
        }
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
  jQuery("#headers_tablePager_right").css("width","150px");
//==============================================================================


  jQuery('#pay_table').jqGrid({
    url:'payment_operations_pay_data.php',
    editurl: '',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST', 
    height:200,
    width:400,
    colNames:[],
    colModel:[
    {name:'id_doc', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    {name:'id_headpay', index:'id_headpay', width:40, editable: false, align:'center',hidden:true},    
    
    {label:'№ .',name:'reg_num', index:'reg_num', width:50, editable: true, align:'left',edittype:'text'}, 

    {label:'Дата опл.',name:'reg_date', index:'reg_date', width:70, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Дата надх.',name:'pay_date', index:'pay_date', width:70, editable: true,  
                        align:'left',edittype:'text',formatter:'date'},
    
    //{label:'Тип док.',name:'idk_doc', index:'idk_doc', width:60, editable: true, align:'right',
//                            edittype:'select',formatter:'select',editoptions:{value:lidk_doc},stype:'text'},                       
    
  //  {label:'Тип нарах.',name:'id_pref', index:'id_pref', width:60, editable: true, align:'right',
    //                        edittype:'select',formatter:'select',editoptions:{value:lid_pref},stype:'text'},                       
                        
    {label:'Книга',name:'book', index:'book', width:30, editable: true, align:'left',edittype:'text'},           
    {label:'Особ.рах.',name:'code', index:'code', width:50, editable: true, align:'left',edittype:'text'},                 
    {label:'Місто',name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text', hidden:town_hidden},
    {label:'Адреса',name:'addr', index:'addr', width:200, editable: true, align:'left',edittype:'text'},           
    {label:'Абонент',name:'abon', index:'abon', width:200, editable: true, align:'left',edittype:'text'},

    {label:'Сума,грн',name:'value', index:'value', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    
    {label:'в.т.ч ПДВ,грн',name:'value_tax', index:'value_tax', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    
    {label:'Період',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', hidden:false,formatter:'date'},
    {label:'Період опл',name:'mmgg_pay', index:'mmgg_pay', width:80, editable: true, align:'left',edittype:'text', hidden:false,formatter:'date'},
    {label:'mmgg_hpay',name:'mmgg_hpay', index:'mmgg_hpay', width:80, editable: true, align:'left',edittype:'text', hidden:true,formatter:'date'},

    {label:'Тип док.',name:'idk_doc', index:'idk_doc', width:100, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lidk_doc},stype:'select'},

    {label:'Прим.',name:'note', index:'note', width:80, editable: true, align:'left',edittype:'text'}, 
    
    {name:'Час внесення',name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},
        
    {label:'Закр.',name:'flock', index:'flock', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}}

    ], 
    pager: '#pay_tablePager',
    autowidth: true,
    //footerrow: true,
    //shrinkToFit : false,
    rowNum:500,
    rowList:[50,100,200,500],
    sortname: 'reg_num',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Оплати',
    //hiddengrid: false,
    hidegrid: false,   
    //pgbuttons: false,     // disable page control like next, back button
    //pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    postData:{'p_id':0, 'sum_find':sum_find},
    
    gridComplete:function(){

     if ($(this).getDataIDs().length > 0) 
     {      
       var first_id = parseInt($(this).getDataIDs()[0]);
       $(this).setSelection(first_id, true);
     }

     var cnt = 0;
     var colSum = 0;

//     cnt =    $(this).jqGrid('getGridParam', 'userData')['count'];
//     colSum = $(this).jqGrid('getGridParam', 'userData')['sum_value'];
    var myUserData = $(this).jqGrid('getGridParam', 'userData')
    cnt = myUserData['count'];
    colSum = parseFloat(myUserData['sum_value']);
  
     //var colSum = $(this).jqGrid('getCol', 'value', false, 'sum');
     //$("#pFooterBar").find("#fcnt_all").attr('value',$(this).getDataIDs().length);
     $("#pFooterBar").find("#fcnt_all").attr('value',cnt);
     $("#pFooterBar").find("#fsum_all").attr('value',colSum.toFixed(2));
     
    },
    onSelectRow: function(id) { 
          cur_pay_id = id;
    },
    
    ondblClickRow: function(id){ 
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            edit_validator.resetForm();  //для сброса состояния валидатора
            $("#fPayEdit").resetForm();
            $("#fPayEdit").clearForm();
          
            $("#pay_table").jqGrid('GridToForm',gsr,"#fPayEdit"); 
            $("#fPayEdit").find("#foper").attr('value','edit');              

            $("#fPayEdit").find("#fpayheader").attr('value',
                '№'+$("#headers_table").jqGrid('getCell',cur_doc_id,'reg_num')+
                ' від '+$("#headers_table").jqGrid('getCell',cur_doc_id,'reg_date'));              

            $("#fPayEdit").find("#bt_add").hide();
            $("#fPayEdit").find("#bt_edit").show();   
            $("#dialog_payedit").dialog('open');          
            
            if ((r_edit==3)&&(cur_doc_lock==0))
            {
                $("#fPayEdit").find("#bt_edit").prop('disabled', false);
            }
            else
            {
                $("#fPayEdit").find("#bt_edit").prop('disabled', true);
            }
        }

     } ,  

    loadError : function(xhr,st,err) {
      jQuery('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);
    }
  
  //  jsonReader : { repeatitems: false }

  }).navGrid('#pay_tablePager',
         {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  jQuery("#pay_table").jqGrid('filterToolbar','');
  jQuery("#pay_tablePager_right").css("width","150px");
//==============================================================================

//jQuery("#headers_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
//      jQuery(this).editGridRow(id,{width:300,height:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});}} );


//jQuery("#accnt_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
//      jQuery(this).editGridRow(id,{width:300,height:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});}} );

$("#message_zone").dialog({autoOpen: false});

$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
$("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {jQuery("#message_zone").html('');});

jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});
jQuery(".btnRefresh").button({icons: {primary:'ui-icon-refresh'}});

jQuery("#fHeaderEdit :input").addClass("ui-widget-content ui-corner-all");
jQuery("#fPayEdit :input").addClass("ui-widget-content ui-corner-all");
jQuery("#fPayInput :input").addClass("ui-widget-content ui-corner-all");

$("#dialog_editform").dialog({
			resizable: true,
		//	height:140,
                        width:600,
			modal: true,
                        autoOpen: false,
                        title:"Пачка оплат"
});

$("#dialog_payinput").dialog({
			resizable: true,
			height:600,
                        width:700,
			modal: true,
                        autoOpen: false,
                        title:"Рознесення оплат",
                        resize: function(event, ui) 
                        {
                           jQuery("#payinput_outer").height( $("#dialog_payinput").innerHeight()-62 );
                           jQuery("#payinput_inner").height( $("#dialog_payinput").innerHeight()-payinput_inner );

                        }
});

$("#dialog_payedit").dialog({
			resizable: true,
		//	height:140,
                        width:600,
			modal: true,
                        autoOpen: false,
                        title:"Редагування оплати"
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
	,	center__paneSelector:	"#ppay_table"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       north__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#headers_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#headers_table").jqGrid('setGridHeight',_pane.innerHeight()-130);
        }
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#pay_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#pay_table").jqGrid('setGridHeight',_pane.innerHeight()-125);
        }

	});

        outerLayout.resizeAll();
        outerLayout.close('south');     
        
//------------------------------------------------------------------------------        
 form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FormSubmitResponse, // функция, вызываемая при получении ответа
    error : FormSubmitError
  };

fHeader_ajaxForm = $("#fHeaderEdit").ajaxForm(form_options);
        
// опции валидатора общей формы
var form_valid_options = { 

		rules: {
			reg_date: "required",
                        count_pay: {number:true},
                        sum_pay: {number:true}
		},
		messages: {
			reg_date: "Вкажіть дату",
                        count_pay: {number:"Повинно бути число!"},
                        sum_pay: {number:"Повинно бути число!"}
		}
};

validator = $("#fHeaderEdit").validate(form_valid_options);


$("#fHeaderEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});

//------------------------------------------------------------------------------        
jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{caption:"Нова пачка",
    id:"btn_header_new",
    onClickButton:function(){ 

     if (sum_find !=0)
     {
        jQuery('#btn_find_sum').removeClass('navButton_selected') ;    
        sum_find=0;
        $('#headers_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg, 'sum_find':sum_find}}).trigger('reloadGrid'); 
        $('#pay_table').jqGrid('setGridParam',{datatype:'json','postData':{'p_id':cur_doc_id , 'sum_find':sum_find}}).trigger('reloadGrid');
     }

      validator.resetForm();
      $("#fHeaderEdit").resetForm();
      $("#fHeaderEdit").clearForm();
          
      $("#fHeaderEdit").find("#fid").attr('value',-1 );    
      $("#fHeaderEdit").find("#foper").attr('value','add');              
          
      $("#fHeaderEdit").find("#bt_add").show();
      $("#fHeaderEdit").find("#bt_edit").hide();
      $("#fHeaderEdit").find("#bt_lock").hide();
      $("#fHeaderEdit").find("#bt_unlock").hide();
      jQuery("#dialog_editform").dialog('open');          
            
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
            
            $("#fHeaderEdit").find("#bt_lock").show();
            $("#fHeaderEdit").find("#bt_unlock").show();

            $("#dialog_editform").dialog('open');          
            
            if ((r_edit==3)&&(cur_doc_lock==0))
            {
                $("#fHeaderEdit").find("#bt_edit").prop('disabled', false);
            }
            else
            {
                $("#fHeaderEdit").find("#bt_edit").prop('disabled', true);
            }
            
            if ((cur_doc_lock==1)&&(r_unlock==3))
            {
                $("#fHeaderEdit").find("#bt_unlock").prop('disabled', false);
            }
            else
            {
                $("#fHeaderEdit").find("#bt_unlock").prop('disabled', true);
            }
                
            if ((cur_doc_lock==0)&&(r_lock==3))
            {
                $("#fHeaderEdit").find("#bt_lock").prop('disabled', false);
            }
            else
            {
                $("#fHeaderEdit").find("#bt_lock").prop('disabled', true);
            }
            
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
                                                      
                                        fHeader_ajaxForm[0].id.value = cur_doc_id;
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

jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{caption:"Занести оплату.",
        id:"btn_header_input",
	onClickButton:function(){ 
            
        gsr = jQuery("#headers_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            $("#dialog_payinput").find("#fid_headpay").attr('value',cur_doc_id); 
            
            $('#dialog_payinput_log').html('<tr>'+
                        '<th width="20px">№</th>'+
                        '<th width="60px">Рахунок</th>'+
                        '<th width="200px">Абонент</th>'+
                        '<th width="200px">Адреса</th>'+
                        '<th width="50px"> Період</th>'+
                        '<th width="50px" >Сума</th>'+
                    '</tr>');

            $('#fpay_book').attr('value','');
            $('#fpay_code').attr('value','');
            $('#fpay_summ').attr('value','');
            $('#fbarcode_data').attr('value','');


            $("#dialog_payinput").find("#ftotal_cnt_h").attr('value',
                $("#headers_table").jqGrid('getCell',cur_doc_id,'count_pay')  ); 
            $("#dialog_payinput").find("#ftotal_summ_h").attr('value',
                $("#headers_table").jqGrid('getCell',cur_doc_id,'sum_pay')  ); 

            cnt_current=parseInt($("#pFooterBar").find("#fcnt_all").val());
            summ_current=parseFloat($("#pFooterBar").find("#fsum_all").val().replace(',', '.'));

            $("#dialog_payinput").find("#ftotal_cnt").attr('value',cnt_current ); 
            $("#dialog_payinput").find("#ftotal_summ").attr('value',summ_current.toFixed(2) ); 

            input_validator.resetForm();  //для сброса состояния валидатора
            
            $("#fPayInput").find("#fmmgg_pay").datepicker( "setDate" , mmgg_pay.toString("dd.MM.yyyy") );
            
            $("#dialog_payinput").dialog('open');      
            
//            $("#payinput_outer").height( $("#dialog_payinput").innerHeight()-62 );
//            $("#payinput_inner").height( $("#dialog_payinput").innerHeight()-195 );210

            $("#payinput_outer").height( $("#dialog_payinput").innerHeight()-62 );
            $("#payinput_inner").height( $("#dialog_payinput").innerHeight()-payinput_inner );

            
            $('#fpay_book').select();            


        }

     }  
});

jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{id:'btn_find_sum', caption:"Шукати суму",
	onClickButton:function(){ 

      if (sum_find ==0)
      {
        $("#dialog-find-sum").find("#ffind_sum").attr('value','');
    
        $("#dialog-find-sum").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Пошук',
			buttons: {
				"Пошук": function() {
                                        sum_find = $('#ffind_sum').val()
                                        jQuery('#btn_find_sum').addClass('navButton_selected') ;    
                                        $('#headers_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg, 'sum_find':sum_find}}).trigger('reloadGrid');     
					$( this ).dialog( "close" );                                    
				},
				"Закрити": function() {
                                        jQuery('#btn_find_sum').removeClass('navButton_selected') ;    
                                        sum_find=0;
                                        $('#headers_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg, 'sum_find':sum_find}}).trigger('reloadGrid');     
                                        $('#pay_table').jqGrid('setGridParam',{datatype:'json','postData':{'p_id':cur_doc_id , 'sum_find':sum_find}}).trigger('reloadGrid');        
					$( this ).dialog( "close" );
				}
			}
		});
    
          jQuery("#dialog-find-sum").dialog('open');   
     }
     else
     {
        jQuery('#btn_find_sum').removeClass('navButton_selected') ;    
        sum_find=0;
        $('#headers_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg, 'sum_find':sum_find}}).trigger('reloadGrid');              
        $('#pay_table').jqGrid('setGridParam',{datatype:'json','postData':{'p_id':cur_doc_id , 'sum_find':sum_find}}).trigger('reloadGrid');        
     }
     

   ;} 

});

//---------------------------------------------------------------------------
 jQuery("#headers_table").jqGrid('navButtonAdd','#headers_tablePager',{caption:"Друкувати пачку",
    onClickButton:function(){ 

        var postData = jQuery("#pay_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       $('#freps_params').find("#fdt_b").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#fperiod_str").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#foper").attr('value', "pay_pack_print");
       $('#freps_params').find("#ftemplate_name").attr('value', "pay_list_pack");
       $('#freps_params').find("#fid_pack").attr('value', cur_doc_id);
       
       
       $("#dialog-confirm").find("#dialog-text").html('Виберіть варіант друку');
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

//------------------------------------------------------------------------------
jQuery("#pay_table").jqGrid('navButtonAdd','#pay_tablePager',{caption:"Додати",
    id:"btn_pay_new",
    onClickButton:function(){ 

      if ($("#headers_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#headers_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            edit_validator.resetForm();  //для сброса состояния валидатора
            $("#fPayEdit").resetForm();
            $("#fPayEdit").clearForm();
          
            //$("#pay_table").jqGrid('GridToForm',gsr,"#fPayEdit"); 
            $("#fPayEdit").find("#fid").attr('value','-1');
            $("#fPayEdit").find("#fid_headpay").attr('value',cur_doc_id);
            $("#fPayEdit").find("#foper").attr('value','add');
            
            mmgg_h = $("#headers_table").jqGrid('getCell',cur_doc_id,'mmgg_str')
            
            $("#fPayEdit").find("#fmmgg_p").attr('value',mmgg_h);
            $("#fPayEdit").find("#fmmgg_pay").attr('value',mmgg_h);
            $("#fPayEdit").find("#fmmgg_hpay").attr('value',mmgg_h);

            $("#fPayEdit").find("#bt_add").show();
            $("#fPayEdit").find("#bt_edit").hide();   
            $("#dialog_payedit").dialog('open');          
        }
    } 
});
//------------------------------------------------------------------------------

jQuery("#pay_table").jqGrid('navButtonAdd','#pay_tablePager',{caption:"Редагувати",
    id:"btn_pay_edit",
    onClickButton:function(){ 

      if ($("#pay_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#pay_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            edit_validator.resetForm();  //для сброса состояния валидатора
            $("#fPayEdit").resetForm();
            $("#fPayEdit").clearForm();
          
            $("#pay_table").jqGrid('GridToForm',gsr,"#fPayEdit"); 
            $("#fPayEdit").find("#fpayheader").attr('value',
                '№'+$("#headers_table").jqGrid('getCell',cur_doc_id,'reg_num')+
                ' від '+$("#headers_table").jqGrid('getCell',cur_doc_id,'reg_date'));              
            
            
            $("#fPayEdit").find("#foper").attr('value','edit');              

            $("#fPayEdit").find("#bt_add").hide();
            $("#fPayEdit").find("#bt_edit").show();   
            $("#dialog_payedit").dialog('open');     
            
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
//------------------------------------------------------------------------------

jQuery("#pay_table").jqGrid('navButtonAdd','#pay_tablePager',{caption:"Видалити",
        id:"btn_pay_del",
	onClickButton:function(){ 

      if ($("#pay_table").getDataIDs().length == 0) 
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
                                                      
                                        fEdit_ajaxForm[0].id_doc.value = cur_pay_id;
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

jQuery("#pay_table").jqGrid('navButtonAdd','#pay_tablePager',{caption:"Пошук",
	onClickButton:function(){ 

      if ($("#pay_table").getDataIDs().length == 0) 
       {return} ;    

      $("#dialog-find").find("#find_book").attr('value','');
      $("#dialog-find").find("#find_code").attr('value','');
    
      $("#dialog-find").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Пошук',
			buttons: {
				"Пошук": function() {

                                        var allRowsInGrid = $('#pay_table').jqGrid('getRowData');
                                        for (i = 0; i < allRowsInGrid.length; i++) {
                                            
                                            pid = allRowsInGrid[i].id_doc;
                                            pbook = allRowsInGrid[i].book;
                                            pcode = allRowsInGrid[i].code;
    
                                            if ((pbook==$('#find_book').val())&&(pcode==$('#find_code').val()))
                                            {
                                                $('#pay_table').setSelection(pid, true);            
                                            }
                                        }

					$( this ).dialog( "close" );                                    
				},
				"Закрити": function() {
					$( this ).dialog( "close" );
				}
			}
		});
    
        jQuery("#dialog-find").dialog('open');   
          
        ;} 
});


if (r_edit!=3)
{
  $('#btn_pay_del').addClass('ui-state-disabled');
  $('#btn_pay_new').addClass('ui-state-disabled');
  $('#btn_pay_edit').addClass('ui-state-disabled');

  $('#btn_header_del').addClass('ui-state-disabled');
  $('#btn_header_new').addClass('ui-state-disabled');
  $('#btn_header_edit').addClass('ui-state-disabled');
  $('#btn_header_input').addClass('ui-state-disabled');
}


 $('#find_book').keydown( function(e){

        var keycode = (e.keyCode ? e.keyCode : e.which);
        if (keycode == '13') {
            $("#find_code").focus();
        }    
 });
 $('#find_code').keydown( function(e){

        var keycode = (e.keyCode ? e.keyCode : e.which);
        if (keycode == '13') {

            var allRowsInGrid = $('#pay_table').jqGrid('getRowData');
            for (i = 0; i < allRowsInGrid.length; i++) {
                pid = allRowsInGrid[i].id_doc;
                pbook = allRowsInGrid[i].book;
                pcode = allRowsInGrid[i].code;
    
                if ((pbook==$('#find_book').val())&&(pcode==$('#find_code').val()))
                {
                    $('#pay_table').setSelection(pid, true);            
                }
            }

        }    
    });



$('#fpay_book').keypress(function(ev){
    if(ev.which == 13){
        
      if ($('#fpay_book').val()!='')
           $('#fpay_code').select();

      return false;
    }
});
$('#fpay_code').keypress(function(ev){
    if(ev.which == 13){

      if ($('#fpay_code').val()!='')
          $('#fpay_summ').select();

     return false;
    }
});


//------------------------------------------------------------------------------        
 var input_form_options = { 
    dataType:"json",
    beforeSubmit: InputFormBeforeSubmit, // функция, вызываемая перед передачей 
    success: InputFormSubmitResponse // функция, вызываемая при получении ответа
  };

fInput_ajaxForm = $("#fPayInput").ajaxForm(input_form_options);
        
// опции валидатора общей формы
var inputform_valid_options = { 
                errorPlacement: function(error, element) {
				error.appendTo( $('#dialog_payinput_err'));
                },
                //onkeyup: false,
                //onfocusout: false,
		rules: {
			book: "required",
                        code: "required",
                        summ: {required:true,number:true}
		},
		messages: {
			book: "Вкажіть книгу ",
                        code: "Вкажіть особовий рахунок ",
                        summ: {required:"Вкажіть суму ",
                               number:"Повинно бути число! "}
		}
};

input_validator = $("#fPayInput").validate(inputform_valid_options);

/*
$("#fPayInput").find("#bt_reset").click( function() 
{
   
  jQuery('#pay_table').jqGrid('setGridParam',{datatype:'json','postData':{'p_id':cur_doc_id , 'sum_find':sum_find }}).trigger('reloadGrid');            
  //$("#pFooterBar").find("#fcnt_all").attr('value',cnt_current);
  //$("#pFooterBar").find("#fsum_all").attr('value',summ_current.toFixed(2));

  //jQuery("#headers_table").jqGrid('setRowData',cur_doc_id,{ real_cnt: cnt_current });
  //jQuery("#headers_table").jqGrid('setRowData',cur_doc_id,{ real_sum: summ_current });

  jQuery("#dialog_payinput").dialog('close');                           
});
*/            
//------------------------------------------------------------------------------        
var edit_form_options = { 
    dataType:"json",
    beforeSubmit: EditFormBeforeSubmit, // функция, вызываемая перед передачей 
    success: EditFormSubmitResponse // функция, вызываемая при получении ответа
  };

fEdit_ajaxForm = $("#fPayEdit").ajaxForm(edit_form_options);
        
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


edit_validator = $("#fPayEdit").validate(editform_valid_options);


$("#fPayEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_payedit").dialog('close');                           
});

 
$("#fPayEdit").find("#fvalue").bind('input propertychange', function() {
      
   var sum_all = parseFloat($("#fPayEdit").find("#fvalue").val().replace(',', '.'));
   if (sum_all!=0)
       {
           var sum_nds = sum_all/6;
              
           $("#fPayEdit").find("#fvalue_tax").attr('value', sum_nds.toFixed(2) );
       }
});

//------------------------------------------------------------------------------        
    jQuery("#btPaccntSel").click( function() { 
/*   
        var ww = window.open("abon_en_main.php", "paccnt_win", "toolbar=0,width=900,height=600");
        document.paccnt_sel_params.submit();
        ww.focus();
*/

        createAbonGrid();
        
        abon_target_id = $("#fPayEdit").find('#fid_paccnt');
        abon_target_name = $("#fPayEdit").find('#fabon');
        abon_target_book = $("#fPayEdit").find('#fbook');
        abon_target_code = $("#fPayEdit").find('#fcode');

       jQuery("#grid_selabon").css({'left': $("#fPayEdit").find('#fabon').offset().left+1, 'top': $("#fPayEdit").find('#fabon').offset().top+20});
       jQuery("#grid_selabon").toggle( );

    });


   $("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       
       $('#headers_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg, 'sum_find':sum_find}}).trigger('reloadGrid');
       
   });


jQuery("#fpay_code").change( function() { 

    if (jQuery("#fpay_code").val()=='') return;
    if (jQuery("#fpay_book").val()=='null') return;
    
    $.ajaxSetup({type: "POST",   dataType: "json"});
    
    var abon_request = $.ajax({
            url: "payment_operations_abon_data.php",
            type: "POST",
            data: {
                code: jQuery("#fpay_code").val(), 
                book : jQuery("#fpay_book").val()
            },
            dataType: "json"
        });

        abon_request.done(function(data ) {
            
            if (data.errcode!==undefined)
                {
                    if(data.errcode==2) 
                    {
                      $('#message_zone').append(data.errstr);  
                      $('#message_zone').append("<br>");                 
                      $("#abon_label").html('');
                      $("#abon_info").html( data.errstr );  
                      $('#abon_info').css( "color", "red");
                    }
                    else
                    {
                        $('#fpay_paccnt').attr('value',data.id);
                        $("#abon_label").html(data.abon);
                        $("#abon_info").html(data.addr + ' <br/> Борг: ' + data.saldo+' грн.'+ 
                             '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Останній рахунок(врах.план): '+ data.bill_sum+' грн.');
                        $('#dialog_payinput_err').html('');
                        
                        if(data.errcode==-1) 
                          $('#abon_info').css( "color", "red");
                        else
                          $('#abon_info').css( "color", "blue");
                    }
                        
                }
            
        });
        abon_request.fail(function(data ) {
            if (data.errcode!==undefined)
                {
                    $('#message_zone').append(data.errstr);  
                    $('#message_zone').append("<br>");                 
                }
                else
                    $('#message_zone').append(data);  
            
        });

   });  
   
   
jQuery("#fbarcode_data").keypress(function(e){

    if ( e.which == 13 ) return false;
    if (e.which < 32) return true; // спец. символ
    //if ( e.which == 13 ) 
    
    if (jQuery("#fbarcode_data").val()=='') return;
    if (jQuery("#fbarcode_data").val()=='null') return;
    
    if (String.fromCharCode(e.which)=='+')
    {
      var str = jQuery("#fbarcode_data").val();
      var barcode = str.split(";"); 
      
      $("#fpay_book").attr('value','0');
      $("#fpay_code").attr('value','0');
      $("#fpay_summ").attr('value',barcode[3]);

      $("#fPayInput").find("#foper").attr('value','add');
      $("#fPayInput").ajaxSubmit(input_form_options);
      //alert(barcode[0]);
    }
    
 });    
   
jQuery("#pbarcode_data").hide();

jQuery("#fbarcode_input").click( function() {    
    if (jQuery("#fbarcode_input").prop('checked') == true)
    {
        jQuery("#pbarcode_data").show();
        payinput_inner = 250;
        $("#fbarcode_data").focus();
        
    }
    else
    {
        jQuery("#pbarcode_data").hide();
        payinput_inner = 220;
        $("#fpay_book").focus();
    }
    $("#payinput_inner").height( $("#dialog_payinput").innerHeight()-payinput_inner );
 }); 




/*
$('#fHeaderEdit input').keypress(function(e){
    if ( e.which == 13 ) return false;
 });    

$('#fPayEdit input').keypress(function(e){
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

  $('#fPayEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fPayEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 



   $("#abon_label").click( function() {
        id_paccnt = $('#fpay_paccnt').attr('value');
        
        $("#fpaccnt_params").find("#pmode").attr('value',0 );
        $("#fpaccnt_params").find("#pid_paccnt").attr('value',$('#fpay_paccnt').attr('value') );

        $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
              $('#fpay_book').attr('value')+'/'+
              $('#fpay_code').attr('value')+' '+
              $('#abon_label').html() );
                
        $("#fpaccnt_params").find("#ppaccnt_book").attr('value',
              $('#fpay_book').attr('value') );

        $("#fpaccnt_params").find("#ppaccnt_code").attr('value',
              $('#fpay_code').attr('value') );

        $("#fpaccnt_params").find("#ppaccnt_name").attr('value',
              $('#abon_label').html() );
                
                
        $("#fpaccnt_params").attr('target',"_blank" );           
        $("#fpaccnt_params").attr("action","abon_en_saldo.php");                  
        document.paccnt_params.submit();

    });

    jQuery("#btPayHeaderSel").click( function() { 
   
        var ww = window.open("payment_operations.php", "paccnt_win", "toolbar=0,width=900,height=600");
        document.payheader_sel_params.submit();
        ww.focus();
    });


   $("#show_peoples").click( function() {
     jQuery("#pay_table").jqGrid('showCol',["user_name"]);
   });



//------------------------------------------------------------------------------
   newIndicationGridMode = 0; 
   
   $("#abon_indic").click( function() {
            newIndicationGridMode =0;
            id_paccnt = $('#fpay_paccnt').attr('value');
            
            if ((id_paccnt=='')||(id_paccnt=='0')) return;
            
            $("#dialog-indications").find("#fdt_ind").datepicker( "setDate" , Date.now().toString("dd.MM.yyyy") );
            createNewIndicationGrid(newIndicationGridMode);

            $("#dialog-indications").dialog({
                resizable: true,
                height:300,
                width:800,
                modal: true,
                autoOpen: false,
                dialogClass: 'StandartTitleClass',
                title:'Показники',
                resize: function(event, ui) 
                        {
                         if (isNewIndicationGridCreated)
                             {
                                jQuery("#new_indications_table").jqGrid('setGridWidth',$("#dialog-indications").innerWidth()-15);
                                jQuery("#new_indications_table").jqGrid('setGridHeight',$("#dialog-indications").innerHeight()-100);
                             }
                        },
                
                buttons: {
                    "Ок": function() {


                        if ((selICol!=0)&&(selIRow!=0))
                        {
                            jQuery('#new_indications_table').editCell(selIRow,selICol, false); 
                        }
    
    
                        var data_obj = $('#new_indications_table').getChangedCells('all');
                        var json_str = JSON.stringify(data_obj);
                        var id_reason = jQuery("#dialog-indications").find("#fid_reason").val();

                        //alert(json);
                        $.ajaxSetup({
                            type: "POST",   
                            dataType: "json"
                        });
    
                        var request = $.ajax({
                            url: "abon_ensaldo_new_indic_edit.php",
                            type: "POST",
                            data: {
                                oper : 'add' , 
                                reason: id_reason,
                                json_data : json_str  
                            },
                            dataType: "json"
                        });

                        request.done(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");                 
                                if (data.errcode==2)
                                    $('#message_zone').dialog('open');
                            }
                            $(".mod_column_class").removeClass("mod_column_class");
                            //jQuery('#indic_table').trigger('reloadGrid');        
            
                        //window.opener.RefreshIndicExternal(id_pack);

                        });
                        request.fail(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");
                                $('#message_zone').dialog('open');
                            }
                            else
                            {
                                $('#message_zone').append(data);  
                                $('#message_zone').dialog('open');
                            }
            
                        });


                        $( this ).dialog( "close" );
                    },
                    "Відмінити": function() {
                        $( this ).dialog( "close" );
                    }
                }
            });
    
            jQuery("#dialog-indications").dialog('open');
            jQuery("#new_indications_table").jqGrid('setGridWidth',$("#dialog-indications").innerWidth()-15);
            jQuery("#new_indications_table").jqGrid('setGridHeight',$("#dialog-indications").innerHeight()-100);
            
        
    }) ;
   
   
   $("#dialog-indications").find("#btIndRefresh").click( function(){ 
       createNewIndicationGrid(newIndicationGridMode);
   });


});

//=========================================================================


        
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
        
        
       var date_operation = Date.parse( $("#freg_date").val(), "dd.MM.yyyy");          
       
       if((date_operation< date_mmgg)||(date_operation> date_mmgg_next))
       {
            jQuery("#dialog-confirm").find("#dialog-text").html('Дата пачки не відповідає поточному місяцю!');
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Увага',
                buttons: {
                    "Ок": function() {
                        $( this ).dialog( "close" );
                    }
                }
            });
    
            jQuery("#dialog-confirm").dialog('open');    
            //return false;
       }       
        
       if(!submit_form.validate().form())  {return false;}
       else {
           
            $("#fHeaderEdit").find("#bt_add").attr("disabled", true);
            $("#fHeaderEdit").find("#bt_reset").attr("disabled", true);
            
            $("#fHeaderEdit").find("#lwait").show();
            form_edit_lock=1;
            return true; 

       }
    }
    else {
      if(submit_form[0].oper.value=='del')
      {
        
          $("#calc_progress").dialog('open');
     
          elapsed_seconds = 0;
          timerId = setInterval(function() {
                   elapsed_seconds = elapsed_seconds + 1;
                   $('#progress_time').html(get_elapsed_time_string(elapsed_seconds));
               }, 1000);
       
          $("#progress_indicator").spin("large", "black");             
          
          return true;

    } 
    
            
    if ((btn=='lock')||(btn=='unlock'))
    {
        
            if (btn=='lock') $("#dialog-confirm").find("#dialog-text").html('Заблокувати пачку?');
            if (btn=='unlock') $("#dialog-confirm").find("#dialog-text").html('Розблокувати пачку?');
            
            $("#dialog-confirm").dialog({ 
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Підтвердження', 
                buttons: {
                    "Ok": function() {
                                          
                        fHeader_ajaxForm.ajaxSubmit(form_options);    
                        $( this ).dialog( "close" );
                    },
                    "Отмена": function() {
                        $( this ).dialog( "close" );
                    }
                }

            });
                
            $("#dialog-confirm").dialog('open');
            return false; 
                
     }
  }   
} ;

// обработчик ответа сервера после отправки формы
function FormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;
             
             $("#calc_progress").dialog('close');
             clearInterval(timerId);
             $("#progress_indicator").spin(false);             
             
              $("#fHeaderEdit").find("#bt_add").attr("disabled", false);
              $("#fHeaderEdit").find("#bt_reset").attr("disabled", false);
            
              $("#fHeaderEdit").find("#lwait").hide();
             

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_editform").dialog('close');                           
               jQuery('#headers_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               //var fid = jQuery("#fid").val();
               //if(fid) 
               //{ 
               //  jQuery("#lgt_category_table").jqGrid('FormToGrid',fid,"#fLgtCategoryEdit"); 
               //}  
               
               new_doc_id = errorInfo.id;
               jQuery('#headers_table').trigger('reloadGrid');     
               
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

function FormSubmitError(  jqXHR,  textStatus,  errorThrown )
{
             errorInfo = jqXHR.responseText;
             form_edit_lock=0;
             
             jQuery("#calc_progress").dialog('close');
             clearInterval(timerId);
             $("#progress_indicator").spin(false); 

             $("#message_zone").html('');
             $('#message_zone').append('<p style="color:red;" > Ошибка! </p>');  
             $('#message_zone').append('<br>');                 
             $('#message_zone').append(errorInfo);  
             $('#message_zone').append('<br>');                 
             $('#message_zone').dialog('open');
//               return [true,errorInfo.errstr]

};


// обработчик, который вызываетя перед отправкой формы
function InputFormBeforeSubmit(formData, jqForm, options) { 

    if (pay_input_lock == 1) return false;
    
    submit_form = jqForm;

    var btn = 'add';
    for (var i=0; i < formData.length; i++) { 
        if (formData[i].name =='submitButton') { 
           btn= formData[i].value; 
    //       submit_form[0].oper.value = btn;
        } 
    } 


    if(btn=='add')
    { 
      $('#dialog_payinput_err').show();  
      if(!submit_form.validate().form())  {return false;}
       else {
            pay_input_lock = 1;
            return true; 
            }
    }
    else
    {
            $('#dialog_payinput_err').hide();  
            $('#lwait_save').show();  
            return true;         
    }
    
} ;

// обработчик ответа сервера после отправки формы
function InputFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;
             
             if (errorInfo.errcode==0) {
                return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                
                $('#dialog_payinput_log tr:first').after(
                   '<tr><td>'+errorInfo.nn+ 
                    '</td> <td>'+ errorInfo.code+                        
                    '</td> <td>'+ errorInfo.abon+ 
                    '</td> <td>'+ errorInfo.addr+
                    '</td> <td>'+ errorInfo.mmgg_pay+
                    '</td> <td>'+ errorInfo.summ+
                    '</td></tr>'
                );

                cnt_current=cnt_current+1;
                summ_current=summ_current+parseFloat(errorInfo.summ);

                $("#dialog_payinput").find("#ftotal_cnt").attr('value',cnt_current ); 
                $("#dialog_payinput").find("#ftotal_summ").attr('value',summ_current.toFixed(2)); 


                $('#fpay_book').attr('value','');
                $('#fpay_code').attr('value','');
                $('#fpay_summ').attr('value','');
                $('#fpay_paccnt').attr('value','');
                $('#fbarcode_data').attr('value','');
                $("#fPayInput").find("#foper").attr('value','');
                
                $("#abon_info").html('');
                 $("#abon_label").html('');
                $('#dialog_payinput_err').html('');
                
                if (jQuery("#fbarcode_input").prop('checked') == true)
                  $('#fbarcode_data').select();
                else
                  $('#fpay_book').select();
                
                
                pay_input_lock = 0;
                
                return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==-2) {
               jQuery('#message_zone').append(errorInfo.errstr);
               jQuery('#message_zone').append('<br>');  
               $('#lwait_save').hide();  

               new_doc_id  = cur_doc_id;
               jQuery('#pay_table').jqGrid('setGridParam',{datatype:'json','postData':{'p_id':new_doc_id , 'sum_find':sum_find}}).trigger('reloadGrid');            
               jQuery('#headers_table').jqGrid('setGridParam',{datatype:'json','postData':{'p_mmgg': mmgg, 'sum_find':sum_find}}).trigger('reloadGrid');            
               
               jQuery("#dialog_payinput").dialog('close');                           

               return [true,errorInfo.errstr];
             }
             if (errorInfo.errcode==-3) {
                
                $('#fpay_book').attr('value','');
                $('#fpay_code').attr('value','');
                $('#fpay_summ').attr('value','');
                $('#fpay_paccnt').attr('value','');
                $('#fbarcode_data').attr('value','');
                $("#fPayInput").find("#foper").attr('value','');
                
                $("#abon_info").html('');
                $("#abon_label").html('');
                $('#dialog_payinput_err').html('');
                
                if (jQuery("#fbarcode_input").prop('checked') == true)
                  $('#fbarcode_data').select();
                else
                  $('#fpay_book').select();
                
                pay_input_lock = 0;
                
                return [true,errorInfo.errstr]};              
             
             
             if (errorInfo.errcode==3) {
                 
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
               $('#dialog_payinput_err').html(errorInfo.errstr);
               pay_input_lock = 0;
               return [true,errorInfo.errstr];
             };              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr];
               pay_input_lock = 0;
               
             };   

};

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
                 
               jQuery("#dialog_payedit").dialog('close');                           
               jQuery('#pay_table').trigger('reloadGrid');        
              
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
               
               jQuery('#pay_table').trigger('reloadGrid');        
               
               jQuery("#dialog_payedit").dialog('close');                                            
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
    
        $("#fPayEdit").find('#fid_paccnt').attr('value',id );
        $("#fPayEdit").find('#fbook').attr('value',book );    
        $("#fPayEdit").find('#fcode').attr('value',code );    
        $("#fPayEdit").find('#fabon').attr('value',name );    
    
} 

function SelectPayHeaderExternal(id, reg_date, reg_num, id_origin, mmgg) {
    
        $("#fPayEdit").find('#fid_headpay').attr('value',id );
        
        $("#fPayEdit").find('#fpayheader').attr('value', '№'+reg_num+' від '+reg_date );    
    
        $("#fPayEdit").find("#fmmgg_p").attr('value',mmgg);
        $("#fPayEdit").find("#fmmgg_pay").attr('value',mmgg);
        $("#fPayEdit").find("#fmmgg_hpay").attr('value',mmgg);
    
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