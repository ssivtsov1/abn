var id_paccnt = 0;
var edit_row_id=0;
var form_edit_lock=0;
var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, 
    success: FormSubmitResponse 
  };
 var list_mode =0;

jQuery(function(){ 

  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
		buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");
    
  $("#fmmgg").datepicker( "setDate" , mmgg );

  $("#fperiod").prop('checked',true);
  $("#fstate").prop('checked',false);



  jQuery('#switch_table').jqGrid({
    url:'abon_en_switch_list_data.php',
    editurl: 'abon_en_switch_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    //width:800,
    autowidth: true,
    shrinkToFit : true,
    scroll: 0,
    colNames:[],
    colModel :[ 

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},

    {label:'Дата',name:'dt_action', index:'dt_action', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Книга',name:'book', index:'book', width:30, editable: true, align:'left',edittype:'text'},           
    {label:'Особ.рах.',name:'code', index:'code', width:30, editable: true, align:'left',edittype:'text'},                 
    {label:'Абонент',name:'abon', index:'abon', width:200, editable: true, align:'left',edittype:'text'},
    {label:'Місто',name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text', hidden:town_hidden},
    {label:'Адреса',name:'addr', index:'addr', width:200, editable: true, align:'left',edittype:'text'},           
    {label:'Дільниця',name:'sector', index:'sector', width:100, editable: true, align:'left',edittype:'text'},                        
    {label:'Подія',name:'action', index:'action', width:150, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lactions},stype:'select'},

    {label:'Дата друку (попер.)',name:'dt_create', index:'dt_create', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Сума боргу',name:'sum_warning', index:'sum_warning', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           

    {label:'кВтг боргу',name:'demand_varning', index:'demand_varning', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',hidden:true},           

 
    {label:'Борг станом на',name:'dt_sum', index:'dt_sum', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Борг виник з',name:'mmgg_debet', index:'mmgg_debet', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date',hidden:false},

    {label:'Оплатити до',name:'dt_warning', index:'dt_warning', width:120, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Місце',name:'id_switch_place', index:'id_switch_place', width:80, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lplace},stype:'select'},

    {label:'Примітка',name:'comment', index:'comment', width:150, editable: true, align:'left',
                            edittype:'text'},
    {name:'id_position', index:'id_position', width:40, editable: false, align:'center',hidden:true},
    {label:'Виконавець',name:'position', index:'position', width:100, editable: true, align:'left',edittype:'text'},

    {label:'№ акта',name:'act_num', index:'act_num', width:50, editable: true, align:'left',edittype:'text'},
    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},
    {label:'dt',name:'dt', index:'dt', width:100, editable: false, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}

    ],
    pager: '#switch_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'dt_action',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Відключення та попередження ',
    //hiddengrid: false,
    jsonReader : {repeatitems: false},
    hidegrid: false,
    postData:{'p_mmgg': mmgg, 'mode':list_mode},
    //postData:{'p_id': id_paccnt},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
   },

   onSelectRow: function(rowid) { 
        edit_row_id = rowid; 
        id_paccnt = jQuery(this).jqGrid('getCell',rowid,'id_paccnt')
    },

    
    ondblClickRow: function(id){ 
    var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
        edit_row_id = id;
     
          validator.resetForm();  //для сброса состояния валидатора
          $("#fSwitchEdit").resetForm();
          $("#fSwitchEdit").clearForm();
          $("#fSwitchEdit").find("#foper").attr('value','edit');              
          jQuery(this).jqGrid('GridToForm',gsr,"#fSwitchEdit"); 
      
          $("#fSwitchEdit").find("#bt_add").hide();
          $("#fSwitchEdit").find("#bt_edit").show();   
          jQuery("#dialog_editform").dialog('open');          
     
          if (r_edit==3)
          {
             $("#fSwitchEdit").find("#bt_edit").prop('disabled', false);
          }
          else
          {
             $("#fSwitchEdit").find("#bt_edit").prop('disabled', true);
          }
     
        
      } else { alert("Please select Row") }       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#switch_tablePager',
       {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  jQuery("#switch_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
  //    jQuery(this).editGridRow(id,TableEditOptions);
  }} );


  jQuery("#switch_table").jqGrid('filterToolbar','');
  jQuery("#switch_tablePager_right").css("width","150px");


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
            
            $("#switch_table").jqGrid('setGridWidth',$pane.innerWidth()-10);
            $("#switch_table").jqGrid('setGridHeight',$pane.innerHeight()-130);

        }
        
	});

        
    outerLayout.resizeAll();
    outerLayout.close('south');     
   // innerLayout.hide('north');        
        
    jQuery(".btn").button();
    jQuery(".btnSel").button({ icons: {primary:'ui-icon-folder-open'} });
    jQuery(".btnRefresh").button({icons: {primary:'ui-icon-refresh'}});
    
   jQuery("#fSwitchEdit :input").addClass("ui-widget-content ui-corner-all");
   
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open'); });
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   $("#message_zone").dialog({autoOpen: false});
   
 //----------------------------------------------------------------  
jQuery("#switch_table").jqGrid('navButtonAdd','#switch_tablePager',{caption:"Відкрити",
        onClickButton:function(){
      var gsr = jQuery("#switch_table").jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
          validator.resetForm();  //для сброса состояния валидатора
          $("#fSwitchEdit").resetForm();
          $("#fSwitchEdit").clearForm();
          $("#fSwitchEdit").find("#foper").attr('value','edit');              
          jQuery(this).jqGrid('GridToForm',gsr,"#fSwitchEdit"); 
      
          $("#fSwitchEdit").find("#bt_add").hide();
          $("#fSwitchEdit").find("#bt_edit").show();   
          jQuery("#dialog_editform").dialog('open');          
      } 
    }
});

jQuery("#switch_table").jqGrid('navButtonAdd','#switch_tablePager',{caption:"Новий",
        id:"btn_switch_new",
	onClickButton:function(){ 

          validator.resetForm();
          $("#fSwitchEdit").resetForm();
          $("#fSwitchEdit").clearForm();
          
          edit_row_id = -1;
          $("#fSwitchEdit").find("#fid").attr('value',-1 );    
          $("#fSwitchEdit").find("#fid_paccnt").attr('value', 0);
          $("#fSwitchEdit").find("#foper").attr('value','add');              
          $("#fSwitchEdit").find("#bt_add").show();
          $("#fSwitchEdit").find("#bt_edit").hide();            
          jQuery("#dialog_editform").dialog('open');          
          
        ;} 
});

//-----------------------------------------
jQuery("#switch_table").jqGrid('navButtonAdd','#switch_tablePager',{caption:"Видалити",
        id:"btn_switch_del",
	onClickButton:function(){ 

      if ($("#switch_table").getDataIDs().length == 0) 
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
                                                      
                                          fSwitchEdit_ajaxForm[0].id.value = edit_row_id;
                                          //fSwitchEdit_ajaxForm[0].change_date.value = cur_dt_change;
                                          fSwitchEdit_ajaxForm[0].oper.value = 'del';
                                          fSwitchEdit_ajaxForm.ajaxSubmit(form_options);   

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
//-----------------------------------------
/*
jQuery("#switch_table").jqGrid('navButtonAdd','#switch_tablePager',{caption:"Видалити попередження",
       id:"btn_switch_delall",
	onClickButton:function(){ 

      if ($("#switch_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити всі попередження за поточний місяць?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                                      
                                          fSwitchEdit_ajaxForm[0].id.value = edit_row_id;
                                          //fSwitchEdit_ajaxForm[0].change_date.value = cur_dt_change;
                                          fSwitchEdit_ajaxForm[0].oper.value = 'del_warning';
                                          fSwitchEdit_ajaxForm.ajaxSubmit(form_options);   

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
                */
//---------------------------------------------------------------------
 jQuery("#switch_table").jqGrid('navButtonAdd','#switch_tablePager',{caption:"Друк попередж.",
    id:"btn_switch_print",
    onClickButton:function(){ 

        //var postData = jQuery("#pay_table").jqGrid('getGridParam', 'postData');
        //var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#foper").attr('value', "warning_print"); 
       $('#freps_params').find("#ftemplate_name").attr('value', "warning_print"); 
       $('#freps_params').find("#fid_warning").attr('value',edit_row_id ); 

       document.forms["freps_params"].submit();        
             
    } 
});

if (r_edit!=3)
{
  $('#btn_switch_del').addClass('ui-state-disabled');
  $('#btn_switch_new').addClass('ui-state-disabled');
  $('#btn_switch_edit').addClass('ui-state-disabled');
  $('#btn_switch_delall').addClass('ui-state-disabled');
}

//---------------------------------------------------------------------------
 jQuery("#switch_table").jqGrid('navButtonAdd','#switch_tablePager',{caption:"Друкувати список",
    onClickButton:function(){ 

        var postData = jQuery("#switch_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       $('#freps_params').find("#fdt_b").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#fperiod_str").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#foper").attr('value', "switch_list");
       $('#freps_params').find("#ftemplate_name").attr('value', "switch_list");
       $('#freps_params').find("#flist_mode").attr('value', list_mode);
       
       
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


//---------------------------------------------------------------------
$("#dialog_editform").dialog({
			resizable: true,
		//	height:140,
                        width:740,
			modal: true,
                        autoOpen: false,
                        title:'Операція'
});


fSwitchEdit_ajaxForm = $("#fSwitchEdit").ajaxForm(form_options);

var form_valid_options = { 

		rules: {
			//dt_action: "required",
                        sum_warning: {number:true},
			action: "required"
		},
		messages: {
			//dt_action: "Вкажіть дату!",
                        sum_warning: {number:"Повинно бути число!"},
			action: "Вкажіть подію!"
		}
};

validator = $("#fSwitchEdit").validate(form_valid_options);



$("#fSwitchEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});

    
$("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       
       $('#switch_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg, 'mode':list_mode}}).trigger('reloadGrid');
       
});    

$("#fperiod").change(function() {
    if(this.checked) {
        $("#fstate").prop('checked',false);
        list_mode=0;
    }
});

$("#fstate").change(function() {
    if(this.checked) {
        $("#fperiod").prop('checked',false);
        list_mode=1;
    }
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

       jQuery("#grid_selabon").css({'left': $('#fpaccnt_name').offset().left+1, 'top': $('#fpaccnt_name').offset().top+20});
       jQuery("#grid_selabon").toggle( );
       
       
    });

  $('#fSwitchEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fSwitchEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 

   jQuery("#btCntrlSel").click( function() { 

     createPersonGrid($("#fSwitchEdit").find("#fid_position").val());
     person_target_id=     $("#fSwitchEdit").find("#fid_position");
     person_target_name =  $("#fSwitchEdit").find("#fposition");
     person_target_prof = 0;
    
     jQuery("#grid_selperson").css({'left': $("#fSwitchEdit").find("#fposition").offset().left+1, 'top': $("#fSwitchEdit").find("#fposition").offset().top+20});
     jQuery("#grid_selperson").toggle( );

   });
 
 
   $("#show_peoples").click( function() {
     jQuery("#switch_table").jqGrid('showCol',["user_name"]);
  });


//---------------------------------------------------------
$("#fSwitchEdit").find("#bt_indic").click( function() 
{
    //indic_flock
    //indic_fedit
    newIndicationGridMode =2;
    indic_edit_row_id=0;
    id_paccnt = $("#fSwitchEdit").find("#fid_paccnt").attr('value');

    $("#dialog-indications").find("#fdt_ind").datepicker( "setDate" , $("#fSwitchEdit").find("#fdt_action").attr('value')  );
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
                if (indic_flock=='1')
                {
                    alert('Закритий період!');
                    return;
                }
                if (r_indic!=3)
                {
                    alert('Немає прав змінювати показники!');
                    return;
                }
                    

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
                
                if (indic_fedit=='1')
                    voper = 'edit';
                else
                    voper = 'add';
                
                var request = $.ajax({
                    url: "abon_ensaldo_new_indic_edit.php",
                    type: "POST",
                    data: {
                        oper : voper , 
                        reason: '',
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
                    jQuery('#indic_table').trigger('reloadGrid');        
            
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
            

});


});

function SelectPaccntExternal(id, book, code, name, addr) {
    
        $('#fid_paccnt').attr('value',id );
        $('#fpaccnt_name').attr('value',name );    
    
} 
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
function FormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_editform").dialog('close');                           
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
               $('#switch_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg, 'mode':list_mode}}).trigger('reloadGrid');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               var fid = jQuery("#fid").val();
               if(fid) 
               { 
                 jQuery("#switch_table").jqGrid('FormToGrid',fid,"#fSwitchEdit"); 
               }  
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
