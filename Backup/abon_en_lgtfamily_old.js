var edit_row_id = 0;
var cur_row_id = 0;
var validator = null;
var form_edit_lock=0;

jQuery(function(){ 
  jQuery('#lgt_family_table').jqGrid({
    url:'abon_en_lgtfamily_data.php',
    editurl: 'abon_en_lgtfamily_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:500,
    width:800,
    autowidth: true,
    scroll: 0,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true },     
      {name:'id_lgt', index:'id_lgt', width:40, editable: false, align:'center', hidden:true },     
      {label:"Член сім'ї",name:'fio', index:'fio', width:200, editable: true, align:'left',edittype:'text'},           
      {label:'Сімейні відносини',name:'id_rel', index:'id_rel', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lrel}, 
                            stype:'text' },   
      {label:'Дата народження',name:'dt_birth', index:'dt_birth', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Сам',name:'lgt', index:'lgt', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox'},

      {label:'Дата початку',name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},      
      {label:'Дата закінч.',name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'dt',name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{ srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i' }}


    ],
    pager: '#lgt_family_tablePager',
    rowNum:100,
    sortname: 'id',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: "Члени сім'ї",
    postData:{'p_id': id_lgt}, 
    hidegrid: false,
    jsonReader : {repeatitems: false},
    
    onSelectRow: function(id) { 
      cur_row_id = id;  
    },
    
    ondblClickRow: function(id){ 

      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 

          validator.resetForm();  //для сброса состояния валидатора
          $("#fFamilyEdit").resetForm();
          $("#fFamilyEdit").clearForm();
          
          jQuery(this).jqGrid('GridToForm',gsr,"#fFamilyEdit"); 
          $("#fFamilyEdit").find("#foper").attr('value','edit');              
          edit_row_id = id;

          $("#fFamilyEdit").find("#bt_add").hide();
          $("#fFamilyEdit").find("#bt_edit").show();   
          jQuery("#dialog_editlgtform").dialog('open');          

      } else { alert("Please select Row") }       
      
    } ,  
  gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
  },
      
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#lgt_family_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 


jQuery("#lgt_family_table").jqGrid('navButtonAdd','#lgt_family_tablePager',{caption:"Новий член родини",
	onClickButton:function(){ 

          validator.resetForm();
          $("#fFamilyEdit").resetForm();
          $("#fFamilyEdit").clearForm();
          
          edit_row_id = -1;
          $("#fFamilyEdit").find("#fid").attr('value',-1 );    
          $("#fFamilyEdit").find("#fid_lgt").attr('value',id_lgt );    
          $("#fFamilyEdit").find("#foper").attr('value','add');              
          
          $("#fFamilyEdit").find("#bt_add").show();
          $("#fFamilyEdit").find("#bt_edit").hide();            
          jQuery("#dialog_editlgtform").dialog('open');          
          
        ;} 
});

jQuery("#lgt_family_table").jqGrid('navButtonAdd','#lgt_family_tablePager',{caption:"Видалити члена родини",
	onClickButton:function(){ 

      if ($("#lgt_family_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити члена родини?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                        DeleteFamilyMember();
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


jQuery(".btn").button();
jQuery(".btnSel").button({ icons: {primary:'ui-icon-folder-open'} });

$("#dialog_editlgtform").dialog({
			resizable: true,
		//	height:140,
                        width:600,
			modal: true,
                        autoOpen: false,
                        title:"Член сім'ї"
});


 var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FormSubmitResponse // функция, вызываемая при получении ответа
  };

$("#fFamilyEdit").ajaxForm(form_options);


$.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
jQuery(".dtpicker").mask("99.99.9999");


jQuery("#fFamilyEdit :input").addClass("ui-widget-content ui-corner-all");

// опции валидатора общей формы
var form_valid_options = { 

		rules: {
			fio: "required",
			dt_birth: "required",                        
			dt_b: "required"
		},
		messages: {
			fio: "Вкажіть ім'я!",
			dt_birth: "Вкажіть дату народження",
                        dt_b: "Вкажіть початкову дату"
		}
};

validator = $("#fFamilyEdit").validate(form_valid_options);



$("#fFamilyEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editlgtform").dialog('close');                           
});


$("#message_zone").dialog({ autoOpen: false });

$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open'); });
$("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {jQuery("#message_zone").html('');});

 outerLayout = $("body").layout({
		name:	"outer" 
	//,	north__paneSelector:	"#pmain_header"
	//,	north__closable:	false
	//,	north__resizable:	false
        //,	north__size:		40
	//,	north__spacing_open:	0
	,	south__paneSelector:	"#pmain_footer"
	,	south__closable:	false
	,	south__resizable:	false
        ,	south__size:		40
	,	south__spacing_open:	0
	,	center__paneSelector:	"#grid_dform"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#lgt_family_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#lgt_family_table").jqGrid('setGridHeight',_pane.innerHeight()-110);
        }

}); 
outerLayout.resizeAll();

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
       if(!submit_form.validate().form())  {return false; }
       else {
        form_edit_lock=1;   
        return true; 
       }
    }
    else {return true; }       
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
                 
               jQuery("#dialog_editlgtform").dialog('close');                           
               jQuery('#lgt_family_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               var fid = jQuery("#fid").val();
               if(fid) 
               { 
                 jQuery("#lgt_family_table").jqGrid('FormToGrid',fid,"#fFamilyEdit"); 
               }  
               jQuery("#dialog_editlgtform").dialog('close');                                            
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

function  DeleteFamilyMember()
{
    
    //var cur_dt_change = $("#dialog-changedate").find("#fdate_change").val();    
    
    var request = $.ajax({
        url: "abon_en_lgtfamily_edit.php",
        type: "POST",
        data: {
            oper : 'del',
            id : cur_row_id 
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
                jQuery('#lgt_family_table').trigger('reloadGrid');                      
            }
        }
     });

    request.fail(function(data ) {
        alert("error");
        
    });

};

