var edit_row_id = 0;
var validator = null;
var fAbonEdit_ajaxForm = null;
var form_edit_lock=0;

 var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, 
    success: FormSubmitResponse 
  };

jQuery(function(){ 
  jQuery('#dov_abon_table').jqGrid({
    url:'dov_abon_data.php',
    editurl: 'dov_abon_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:500,
    width:800,
    autowidth: true,
    scroll: 0,
    scrollrows : true,
    
    colNames:["Код","Прізвище","Ім'я", "По батькові","Серія пасп.","Номер пасп.","Дата пасп.","Паспорт виданий","id addr","Адреса реєстр.","id addr","Адреса прожив.","ІНН","Тел.дом.","Тел.роб.","Тел.моб.","Ел.пошта","dt_b","Оператор","dt","Прим"],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {name:'last_name', index:'last_name', width:200, editable: true, align:'left',edittype:'text'},           
      {name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text'},           
      {name:'patron_name', index:'patron_name', width:200, editable: true, align:'left',edittype:'text'},                 

      {name:'s_doc', index:'s_doc', width:50, editable: true, align:'left',edittype:'text',hidden:true},                 
      {name:'n_doc', index:'n_doc', width:50, editable: true, align:'left',edittype:'text',hidden:true},                       
      {name:'dt_doc', index:'dt_doc', width:80, editable: true, align:'left',edittype:'text',formatter:'date',hidden:true},
      {name:'who_doc', index:'who_doc', width:100, editable: true, align:'left',edittype:'text',hidden:true},
      
      {name:'addr_reg', index:'addr_reg', width:50, editable: true, align:'right',hidden:true,
                           edittype:'text'},           
      {name:'addr_reg_str', index:'addr_reg_str', width:200, editable: true, align:'left',edittype:'text',hidden:true},           

      {name:'addr_live', index:'addr_live', width:50, editable: true, align:'right',hidden:true,
                           edittype:'text'},           
      {name:'addr_live_str', index:'addr_live_str', width:200, editable: true, align:'left',edittype:'text',hidden:true},           

      {name:'tax_number', index:'tax_number', width:80, editable: true, align:'left',edittype:'text'},                 
      {name:'home_phone', index:'home_phone', width:80, editable: true, align:'left',edittype:'text'},                       
      {name:'work_phone', index:'work_phone', width:80, editable: true, align:'left',edittype:'text'},                             
      {name:'mob_phone', index:'mob_phone', width:80, editable: true, align:'left',edittype:'text'},                                   
      {name:'e_mail', index:'e_mail', width:80, editable: true, align:'left',edittype:'text'}, 

      {name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date',hidden:true},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},
      {name:'dt_input', index:'dt_input', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'},hidden:false},
      {name:'note', index:'note', width:50, editable: true, align:'right',hidden:true,
                           edittype:'text'}
        

    ],
    pager: '#dov_abon_tablePager',
    rowNum:100,
    rowList:[20,50,100,300,500],
    sortname: 'last_name',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    postData:{'selected_id': id_abon}, 
    caption: 'Фізичні особи ',
    hidegrid: false,
    
    gridComplete:function(){

            if ( id_abon >0)
            {
                $(this).setSelection(id_abon, true);                    
            }
            else
            {
                if ($(this).getDataIDs().length > 0) 
                {      
                    var first_id = parseInt($(this).getDataIDs()[0]);
                    $(this).setSelection(first_id, true);
                }
                
            }
        },
    
    onPaging : function(but) { 
                id_abon=0;
                $(this).jqGrid('setGridParam',{'postData':{'selected_id':id_abon}});        
    },    
    onSelectRow: function(rowid) { 
        edit_row_id = rowid; 
    },
    
    ondblClickRow: function(id){ 

      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 

        if(selmode==1)
        {
           window.opener.SelectAbonExternal(id,jQuery(this).jqGrid('getCell',id,'last_name')+' '+ 
            jQuery(this).jqGrid('getCell',id,'name')+' '+
            jQuery(this).jqGrid('getCell',id,'patron_name'));
           window.opener.focus();
           self.close();            
        }
        else
        {    
          validator.resetForm();  //для сброса состояния валидатора
          $("#fAbonEdit").resetForm();
          $("#fAbonEdit").clearForm();
          
          jQuery(this).jqGrid('GridToForm',gsr,"#fAbonEdit"); 
          edit_row_id = id;

          $("#fAbonEdit").find("#bt_add").hide();
          $("#fAbonEdit").find("#bt_edit").show();   
          jQuery("#dialog_editform").dialog('open');       
          
          if (r_edit==3)
               $("#fAbonEdit").find("#bt_edit").prop('disabled', false);
          else
               $("#fAbonEdit").find("#bt_edit").prop('disabled', true);
          
        }    
      } else {alert("Please select Row")}       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dov_abon_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

jQuery("#dov_abon_table").jqGrid('navButtonAdd','#dov_abon_tablePager',{caption:"Все",
	onClickButton:function(){var sgrid = jQuery("#dov_abon_table")[0];
        sgrid.clearToolbar();  ;} 
});

jQuery("#dov_abon_table").jqGrid('navButtonAdd','#dov_abon_tablePager',{caption:"Відкрити",
        onClickButton:function(){
      var gsr = jQuery("#dov_abon_table").jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
          validator.resetForm();  //для сброса состояния валидатора
          $("#fAbonEdit").resetForm();
          $("#fAbonEdit").clearForm();
          
          jQuery(this).jqGrid('GridToForm',gsr,"#fAbonEdit"); 
      
          $("#fAbonEdit").find("#bt_add").hide();
          $("#fAbonEdit").find("#bt_edit").show();   
          jQuery("#dialog_editform").dialog('open');          
      } 
    }
});

jQuery("#dov_abon_table").jqGrid('navButtonAdd','#dov_abon_tablePager',{caption:"Новий",
        id:"btn_abon_new",
	onClickButton:function(){ 

          validator.resetForm();
          $("#fAbonEdit").resetForm();
          $("#fAbonEdit").clearForm();
          
          edit_row_id = -1;
          $("#fAbonEdit").find("#fid").attr('value',-1 );    
          
          $("#fAbonEdit").find("#bt_add").show();
          $("#fAbonEdit").find("#bt_edit").hide();            
          jQuery("#dialog_editform").dialog('open');          
          
        ;} 
});
//-----------------------------------------
jQuery("#dov_abon_table").jqGrid('navButtonAdd','#dov_abon_tablePager',{caption:"Видалити",
        id:"btn_abon_del",
	onClickButton:function(){ 

      if ($("#dov_abon_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити абонента?');
    
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
                                                      
                                                      fAbonEdit_ajaxForm[0].id.value = edit_row_id;
                                                      fAbonEdit_ajaxForm[0].change_date.value = cur_dt_change;
                                                      fAbonEdit_ajaxForm[0].oper.value = 'del';
                                                      fAbonEdit_ajaxForm.ajaxSubmit(form_options);   
                                                      
                                                    $( this ).dialog( "close" );
                                                },
                                                "Отмена": function() {
                                                    $( this ).dialog( "close" );
                                                }
                                            }

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
          
        ;} 
});

if (r_edit!=3)
{
   $('#btn_abon_del').addClass('ui-state-disabled');
   $('#btn_abon_new').addClass('ui-state-disabled');
}


//-----------------------------------------
jQuery("#dov_abon_table").jqGrid('filterToolbar','');

jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});

$("#dialog_editform").dialog({
			resizable: true,
		//	height:140,
                        width:800,
			modal: true,
                        autoOpen: false,
                        title:'Фізична особа'
});


fAbonEdit_ajaxForm = $("#fAbonEdit").ajaxForm(form_options);


$.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
jQuery(".dtpicker").mask("99.99.9999");


jQuery("#fAbonEdit :input").addClass("ui-widget-content ui-corner-all");
jQuery(".btnCopy").button({ icons: {primary:'ui-icon-copy'} });

// опции валидатора общей формы
var form_valid_options = { 

		rules: {
			name: "required",
			last_name: "required",                        
			//patron_name: "required",
                        dt_b: "required"
		},
		messages: {
			name: "Вкажіть ім'я!",
			last_name: "Вкажіть прізвище!",
                       // patron_name: "Вкажіть по батькові",
                        dt_b: "Вкажіть дату"
		}
};

validator = $("#fAbonEdit").validate(form_valid_options);



$("#fAbonEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});

//-----------------------------------------------------------------
jQuery("#btAddr1Sel").click( function() { 
        SelectAdrTarget='#faddr_reg';
        SelectAdrStrTarget='#faddr_reg_str';

        $("#fadr_sel_params_address").attr('value', $("#fAbonEdit").find("#faddr_reg").val() );    
    
        //$("#fadr_sel_params").attr('target',"_blank" );           
        var ww = window.open("adr_tree_selector.php", "adr_win", "toolbar=0,width=770,height=500");
        document.adr_sel_params.submit();
        ww.focus();
});

jQuery("#btAddr2Sel").click( function() { 
        SelectAdrTarget='#faddr_live';
        SelectAdrStrTarget='#faddr_live_str';

        $("#fadr_sel_params_address").attr('value', $("#fAbonEdit").find("#faddr_live").val() );    
    
        //$("#fadr_sel_params").attr('target',"_blank" );           
        var ww = window.open("adr_tree_selector.php", "adr_win", "toolbar=0,width=770,height=500");
        document.adr_sel_params.submit();
        ww.focus();
});

jQuery("#btAddr2Copy").click( function() { 
    
        $("#fAbonEdit").find('#faddr_live').attr('value',$("#fAbonEdit").find('#faddr_reg').attr('value') );
        $("#fAbonEdit").find('#faddr_live_str').attr('value',$("#fAbonEdit").find('#faddr_reg_str').attr('value') );    
});

//----------------------------------------
$("#message_zone").dialog({autoOpen: false});

$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
$("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {jQuery("#message_zone").html('');});

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
	,	center__paneSelector:	"#grid_dform"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#dov_abon_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#dov_abon_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }

	});
        
        if(selmode!=0)
        {
            outerLayout.hide('north');        
        };    
        
        outerLayout.resizeAll();
        outerLayout.close('south');  
        
        
        $('#fAbonEdit *').filter('input,select').keypress(function(e){
            if ( e.which == 13 ) 
                {
                    var focusable = $('#fAbonEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
                    focusable.eq(focusable.index(this)+1).focus();
                    return false;
                }
        });        

   $("#show_peoples").click( function() {
     jQuery("#dov_abon_table").jqGrid('showCol',["user_name"]);
  });


}); 
 
function SelectAddrExternal(code, name) {
    
        $("#fAbonEdit").find(SelectAdrTarget).attr('value',code );
        $("#fAbonEdit").find(SelectAdrStrTarget).attr('value',name );    
    
} 
// обработчик, который вызываетя перед отправкой формы
function FormBeforeSubmit(formData, jqForm, options) { 

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
       if (form_edit_lock == 1) return false; 
       if(!submit_form.validate().form())  {return false;}
       else {
        if (btn=='edit')
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
                                          submit_form.ajaxSubmit(form_options);    
                                          form_edit_lock=1;
					$( this ).dialog( "close" );
				},
				"Отмена": function() {
					$( this ).dialog( "close" );
				}
			}

                });
                
                $("#dialog-changedate").dialog('open');
                return false; 
                
            }
            else
                {   form_edit_lock=1;
                    return true;
                }        
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

               id_abon = errorInfo.id;
               if(id_abon) 
               { 
                  jQuery('#dov_abon_table').jqGrid('setGridParam',{'postData':{'selected_id':id_abon}}).trigger('reloadGrid');        
               }  
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               var fid = jQuery("#fid").val();
               if(fid) 
               { 
                 jQuery("#dov_abon_table").jqGrid('FormToGrid',fid,"#fAbonEdit"); 
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


