var cur_lgt_grp=0;
var validator = null;
var form_edit_lock=0;

jQuery(function(){ 

/*  if (selmode==0)
  {
     setTimeout(function(){
             jQuery('#lgt_grp_table').trigger('reloadGrid');              
    },300);  
  };
*/
  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");

  if(r_edit==3)
      r_edit_bool = true;
  else
      r_edit_bool = false;

  //----------------------------------------------------------------------------  
  jQuery('#lgt_grp_table').jqGrid({
    url:     'lgt_calc_grp_data.php',
    editurl: 'lgt_calc_grp_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:200,
    width:800,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {label:"Код",name:'ident', index:'ident', width:80, editable: true, align:'left',edittype:'text', hidden:true},           
      {label:'Назва',name:'name', index:'name', width:200, editable: true, align:'left',edittype:"text"},

      {label:'Метод розрахунку',name:'id_calc_type', index:'id_calc_type', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lgi_calctype},stype:'text'},                       

      {label:'Дата початкова',name:'dt_b', index:'dt_b', width:100, editable: true, 
                        align:'left',edittype:'text',formatter:'date', editrules:{required:true}},
      
      {label:'Дата кінцева',name:'dt_e', index:'dt_e', width:100, editable: true, 
                        align:'left',edittype:'text',formatter:'date', editrules:{required:false}},

      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}

    ],
    pager: '#lgt_grp_tablePager',
    autowidth: true,
    rowNum:100,
    //rowList:[20,50,100,300,500],
    sortname: 'id',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Способи розрахунку пільг',
    hidegrid: false,
    
    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
    
  },
    
    onSelectRow: function(id) { 
          cur_lgt_grp = id;
          jQuery('#lgt_norm_table').jqGrid('setGridParam',{datatype: 'json','postData':{'p_id':id}}).trigger('reloadGrid');        
      
    },
    
    ondblClickRow: function(id){ 
      if(selmode==1)
      {
           window.opener.SelectLgtExternal(id,jQuery(this).jqGrid('getCell',id,'lgt_name') );
           window.opener.focus();
           self.close();            
      }

      if(selmode==0)
      {
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            
            // jQuery(this).editGridRow(id,{width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
            validator.resetForm();  //для сброса состояния валидатора
            $("#fLgtEdit").resetForm();
            $("#fLgtEdit").clearForm();
          
            $("#lgt_grp_table").jqGrid('GridToForm',gsr,"#fLgtEdit"); 
            $("#fLgtEdit").find("#foper").attr('value','edit');              
            cur_lgt_grp = id;

            $("#fLgtEdit").find("#bt_add").hide();
            $("#fLgtEdit").find("#bt_edit").show();   
            $("#dialog_editform").dialog('open');     

            if (r_edit==3)
               $("#fLgtEdit").find("#bt_edit").prop('disabled', false);
            else
               $("#fLgtEdit").find("#bt_edit").prop('disabled', true);
            
        
        }
      }
     } ,  

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#lgt_grp_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 


//==============================================================================

  var LgtNormEditOptions = {width:450, reloadAfterSubmit:true, closeAfterAdd:true,
        closeAfterEdit:true, 
        afterSubmit:processAfterEdit,
        onInitializeForm: function() {

            $('#dt_b').datepicker({
                showOn: "button", 
                buttonImage: "images/calendar.gif",
                buttonImageOnly: true, 
                dateFormat:'dd.mm.yy'
            });

            $('#dt_e').datepicker({
                showOn: "button", 
                buttonImage: "images/calendar.gif",
                buttonImageOnly: true, 
                dateFormat:'dd.mm.yy'
            });

        },
        onClose: function() {
            $('.hasDatepicker').datepicker("hide");
        },
        beforeSubmit: function(postdata, formid){
            
            postdata.id_grp_lgt = cur_lgt_grp;
                
         return[true,''];
        } 
    
    };


  jQuery('#lgt_norm_table').jqGrid({
    url:'lgt_calc_norm_data.php',
    editurl: 'lgt_calc_norm_edit.php',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:200,
    width:400,
    colNames:[], 
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_grp_lgt', index:'id_grp_lgt', width:40, editable: false, align:'center',hidden:true},
    
    {label:'Тип тарифа',name:'id_tar_grp', index:'id_tar_grp', width:150, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lgi_tar_grp},stype:'text'},                       
    
    {label:'% опл.',name:'percent', index:'percent', width:100, editable: true, align:'right',hidden:false,
                        edittype:'text',formatter:'number',editrules:{required:true,number:true},
                        formatoptions: {defaultValue: ' '}},           

    {label:'Норма мін, кВтг',name:'norm_min', index:'norm_min', width:120, editable: true, align:'right',hidden:false,
                        edittype:'text',formatter:'number',editrules:{required:false,number:true},
                        formatoptions: {defaultValue: ' '}},           

    {label:"Норма дод, кВтг",name:'norm_one', index:'norm_one', width:120, editable: true, align:'right',hidden:false,
                        edittype:'text',formatter:'number',editrules:{required:false,number:true},
                        formatoptions: {defaultValue: ' '}},           

    {label:'Норма макс, кВтг',name:'norm_max', index:'norm_max', width:120, editable: true, align:'right',hidden:false,
                        edittype:'text',formatter:'number',editrules:{required:false,number:true},
                        formatoptions: {defaultValue: ' '}},           

    {label:'Опал. кВтг на м2',name:'norm_heat_demand', index:'norm_heat_demand', width:120, editable: true, align:'right',hidden:false,
                        edittype:'text',formatter:'number',editrules:{required:false,number:true},
                        formatoptions: {defaultValue: ' '}},           

    {label:'Опал. м2 на особу',name:'norm_heat_one', index:'norm_heat_one', width:120, editable: true, align:'right',hidden:false,
                        edittype:'text',formatter:'number',editrules:{required:false,number:true},
                        formatoptions: {defaultValue: ' '}},           

    {label:"Опал. м2 на сім'ю",name:'norm_heat_family', index:'norm_heat_family', width:120, editable: true, align:'right',hidden:false,
                        edittype:'text',formatter:'number',editrules:{required:false,number:true},
                        formatoptions: {defaultValue: ' '}},           

    {label:'Дата встан.',name:'dt_b', index:'dt_b', width:100, editable: true, 
                        align:'left',edittype:'text',formatter:'date',
                        editrules:{required:true}},

    {label:'Дата зак.',name:'dt_e', index:'dt_e', width:100, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {name:'work_period', index:'work_period', width:80, editable: true, align:'left',edittype:'text', hidden:true},
    {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
    {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},        

    ],
    pager: '#lgt_norm_tablePager',
    autowidth: true,
    shrinkToFit : false,
    rowNum:50,
    rowList:[20,50,100],
    sortname: 'dt_b',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Норми',
    hiddengrid: false,
    postData:{'p_id':0},
    
    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
    
  },
    
    ondblClickRow: function(id){ 
         if (r_edit_bool) jQuery(this).editGridRow(id,LgtNormEditOptions);  
    } ,  

    loadError : function(xhr,st,err) {
      jQuery('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);
    }
  
  //  jsonReader : { repeatitems: false }

  }).navGrid('#lgt_norm_tablePager',
         {edit:r_edit_bool,add:r_edit_bool,del:r_edit_bool,
            addtext: 'Додати',
            edittext: 'Редагувати',
            deltext: 'Видалити'
         },
        LgtNormEditOptions, 
        LgtNormEditOptions, 
        {reloadAfterSubmit:false,afterSubmit:processAfterEdit}, 
        {} 
        ); 



//jQuery("#lgt_grp_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
//      jQuery(this).editGridRow(id,{width:300,height:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});}} );


//jQuery("#lgt_norm_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
//      jQuery(this).editGridRow(id,{width:300,height:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});}} );



$("#message_zone").dialog({autoOpen: false});

$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
$("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {jQuery("#message_zone").html('');});

jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});

jQuery("#fLgtEdit :input").addClass("ui-widget-content ui-corner-all");


$("#dialog_editform").dialog({
			resizable: true,
		//	height:140,
                        width:700,
			modal: true,
                        autoOpen: false,
                        title:"Пільгова категорія"
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
	,	south__paneSelector:	"#pLgtNorm_table"
	,	south__closable:	true
	,	south__resizable:	true
        ,	south__size:		300
	,	center__paneSelector:	"#pLgtGrp_table"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#lgt_grp_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#lgt_grp_table").jqGrid('setGridHeight',_pane.innerHeight()-85);
        }
	,       south__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#lgt_norm_table").jqGrid('setGridWidth',_pane.innerWidth()-10);
            jQuery("#lgt_norm_table").jqGrid('setGridHeight',_pane.innerHeight()-85);
        }
        

	});

        outerLayout.close('south');             
        outerLayout.resizeAll();
        
        
 var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FormSubmitResponse // функция, вызываемая при получении ответа
  };

fLgt_ajaxForm = $("#fLgtEdit").ajaxForm(form_options);
        
// опции валидатора общей формы
var form_valid_options = { 

		rules: {
			lgt_name: "required"
		},
		messages: {
			lgt_name: "Вкажіть назву!"
		}
};

validator = $("#fLgtEdit").validate(form_valid_options);


$("#fLgtEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});
        
jQuery("#lgt_grp_table").jqGrid('navButtonAdd','#lgt_grp_tablePager',{ caption:"Нова",
    id:"btn_lgt_new",
    onClickButton:function(){ 

        validator.resetForm();
        $("#fLgtEdit").resetForm();
        $("#fLgtEdit").clearForm();
          
        edit_row_id = -1;
        $("#fLgtEdit").find("#fid").attr('value',-1 );    
        $("#fLgtEdit").find("#foper").attr('value','add');              
          
        $("#fLgtEdit").find("#bt_add").show();
        $("#fLgtEdit").find("#bt_edit").hide();            
        jQuery("#dialog_editform").dialog('open');          
            
    } 
});

jQuery("#lgt_grp_table").jqGrid('navButtonAdd','#lgt_grp_tablePager',{ caption:"Редагувати",
    id:"btn_lgt_edit",
    onClickButton:function(){ 
        gsr = jQuery("#lgt_grp_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            validator.resetForm();  //для сброса состояния валидатора
            $("#fLgtEdit").resetForm();
            $("#fLgtEdit").clearForm();
          
            $("#lgt_grp_table").jqGrid('GridToForm',gsr,"#fLgtEdit"); 
            $("#fLgtEdit").find("#foper").attr('value','edit');              

            $("#fLgtEdit").find("#bt_add").hide();
            $("#fLgtEdit").find("#bt_edit").show();   
            $("#dialog_editform").dialog('open');          
        }
    } 
});

jQuery("#lgt_grp_table").jqGrid('navButtonAdd','#lgt_grp_tablePager',{caption:"Видалити",
        id:"btn_lgt_del",
	onClickButton:function(){ 

      if ($("#lgt_grp_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити пільгу?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                        fLgt_ajaxForm[0].oper.value = 'del';
                                        fLgt_ajaxForm[0].id.value = cur_lgt_grp;
                                        fLgt_ajaxForm.ajaxSubmit(form_options);       

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

   jQuery("#btCategorSel").click( function() { 
     var ww = window.open("lgt_category.php", "lgtcat_win", "toolbar=0,width=800,height=600");
     document.lgtcatsel_params.submit();
     ww.focus();
   });

    if (r_edit!=3)
    {
      $('#btn_lgt_del').addClass('ui-state-disabled');
      $('#btn_lgt_new').addClass('ui-state-disabled');
      $('#btn_lgt_edit').addClass('ui-state-disabled');
    }


  $('#fLgtEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fLgtEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
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
               jQuery('#lgt_grp_table').trigger('reloadGrid');        
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
               
               jQuery('#lgt_grp_table').trigger('reloadGrid');        
               
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

 function processAfterEdit(response, postdata) {
            //alert(response.responseText);
            if (response.responseText=='') {return [true,''];}
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

}); 

function SelectTarCategoryExternal(id, name) {
        $("#fLgtEdit").find("#fid_kategor").attr('value',id );
        $("#fLgtEdit").find("#fkategor").attr('value',name );    
    
}


 
