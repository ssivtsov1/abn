var edit_row_id=0;
var form_edit_lock=0;
 var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, 
    success: FormSubmitResponse 
  };

jQuery(function(){ 


  jQuery('#subs_table').jqGrid({
    url:'abon_en_onesubs_data.php',
    editurl: 'abon_en_onesubs_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    //width:800,
    autowidth: false,
    shrinkToFit : true,
    scroll: 0,
    colNames:[],
    colModel :[ 

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},

    {label:'Місяць',name:'mmgg', index:'mmgg', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
    {label:'Початок',name:'dt_b', index:'dt_b', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
    {label:'Закінчення',name:'dt_e', index:'dt_e', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Сума субсидії',name:'sum_subs', index:'sum_subs', width:100, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    {label:'Доплата субсидії',name:'sum_recalc', index:'sum_recalc', width:100, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    {label:"Обов'язкова плата",name:'ob_pay', index:'ob_pay', width:100, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           

    {label:"Осіб",name:'kol_subs', index:'kol_subs', width:50, editable: false, align:'center', hidden:false}

    ],
    pager: '#subs_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'mmgg',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Субсидія '+paccnt_info,
    //hiddengrid: false,
    //jsonReader : {repeatitems: false},
    hidegrid: false,
    postData:{'p_id': id_paccnt},

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

    
    ondblClickRow: function(id){ 
    var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
        edit_row_id = id;
     
          validator.resetForm();  //для сброса состояния валидатора
          $("#fSubsEdit").resetForm();
          $("#fSubsEdit").clearForm();
          $("#fSubsEdit").find("#foper").attr('value','edit');              
          jQuery(this).jqGrid('GridToForm',gsr,"#fSubsEdit"); 
      
          $("#fSubsEdit").find("#bt_add").hide();
          $("#fSubsEdit").find("#bt_edit").show();   
          
          if (r_edit==3)
          {
             $("#fSubsEdit").find("#bt_edit").prop('disabled', false);
          }
          else
          {
             $("#fSubsEdit").find("#bt_edit").prop('disabled', true);        
          }
          
          jQuery("#dialog_editform").dialog('open');          
             
      } else { alert("Please select Row") }       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#subs_tablePager',
       {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  jQuery("#subs_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
  //    jQuery(this).editGridRow(id,TableEditOptions);
  }} );


  jQuery("#subs_table").jqGrid('filterToolbar','');


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
            
            $("#subs_table").jqGrid('setGridWidth',$pane.innerWidth()-10);
            $("#subs_table").jqGrid('setGridHeight',$pane.innerHeight()-110);

        }
        
	});

        
    outerLayout.resizeAll();
    outerLayout.close('south');  
   // innerLayout.hide('north');        
        
    jQuery(".btn").button();
    jQuery(".btnSel").button({ icons: {primary:'ui-icon-folder-open'} });
    
   $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
   jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

   jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
   jQuery(".dtpicker").mask("99.99.9999");    
   
   jQuery("#fSubsEdit :input").addClass("ui-widget-content ui-corner-all");
   
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open'); });
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   $("#message_zone").dialog({autoOpen: false});
   
 //----------------------------------------------------------------  
jQuery("#subs_table").jqGrid('navButtonAdd','#subs_tablePager',{caption:"Відкрити",
        onClickButton:function(){
      var gsr = jQuery("#subs_table").jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
          validator.resetForm();  //для сброса состояния валидатора
          $("#fSubsEdit").resetForm();
          $("#fSubsEdit").clearForm();
          $("#fSubsEdit").find("#foper").attr('value','edit');              
          jQuery(this).jqGrid('GridToForm',gsr,"#fSubsEdit"); 
      
          $("#fSubsEdit").find("#bt_add").hide();
          $("#fSubsEdit").find("#bt_edit").show();   
          
          if (r_edit==3)
          {
             $("#fSubsEdit").find("#bt_edit").prop('disabled', false);
          }
          else
          {
             $("#fSubsEdit").find("#bt_edit").prop('disabled', true);        
          }
          
          jQuery("#dialog_editform").dialog('open');          
      } 
    }
});
/*
jQuery("#subs_table").jqGrid('navButtonAdd','#subs_tablePager',{caption:"Новий",
	onClickButton:function(){ 

          validator.resetForm();
          $("#fSubsEdit").resetForm();
          $("#fSubsEdit").clearForm();
          
          edit_row_id = -1;
          $("#fSubsEdit").find("#fid").attr('value',-1 );    
          $("#fSubsEdit").find("#fid_paccnt").attr('value', id_paccnt);
          $("#fSubsEdit").find("#foper").attr('value','add');              
          $("#fSubsEdit").find("#bt_add").show();
          $("#fSubsEdit").find("#bt_edit").hide();            
          jQuery("#dialog_editform").dialog('open');          
          
        ;} 
});
*/
//-----------------------------------------
/*
jQuery("#subs_table").jqGrid('navButtonAdd','#subs_tablePager',{caption:"Видалити",
	onClickButton:function(){ 

      if ($("#subs_table").getDataIDs().length == 0) 
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
                                                      
                                          fSubsEdit_ajaxForm[0].id.value = edit_row_id;
                                          //fSubsEdit_ajaxForm[0].change_date.value = cur_dt_change;
                                          fSubsEdit_ajaxForm[0].oper.value = 'del';
                                          fSubsEdit_ajaxForm.ajaxSubmit(form_options);   

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
$("#dialog_editform").dialog({
			resizable: true,
		//	height:140,
                        width:700,
			modal: true,
                        autoOpen: false,
                        title:'Редагування'
});


fSubsEdit_ajaxForm = $("#fSubsEdit").ajaxForm(form_options);

var form_valid_options = { 

		rules: {
			//dt_action: "required",
                        ob_pay:    {number:true},
                        kol_subs:{number:true}
		},
		messages: {
			//dt_action: "Вкажіть дату!",
                        ob_pay:     {number:"Повинно бути число!"},
			kol_subs: {number:"Повинно бути число!"}
		}
};

validator = $("#fSubsEdit").validate(form_valid_options);



$("#fSubsEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});

    
});

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
               $('#subs_table').jqGrid('setGridParam',{postData:{'p_id': id_paccnt}}).trigger('reloadGrid');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               var fid = jQuery("#fid").val();
               if(fid) 
               { 
                 jQuery("#subs_table").jqGrid('FormToGrid',fid,"#fSubsEdit"); 
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

