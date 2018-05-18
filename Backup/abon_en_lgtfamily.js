//var edit_row_id = 0;
var cur_family_row_id = 0;
var family_validator = null;
var current_lgt_id = null;
var isFamilyGridCreated = false;
var form_edit_lock=0;

//jQuery(function(){ 
var createFamilyGrid = function(lgt_id){ 
    
  current_lgt_id =  lgt_id; 
  if (isFamilyGridCreated) 
  {
      $('#lgt_family_table').jqGrid('setGridParam',{postData:{'p_id': lgt_id}}).trigger('reloadGrid');
      return;
  }
  isFamilyGridCreated =true;

  jQuery('#lgt_family_table').jqGrid({
    url:'abon_en_lgtfamily_data.php',
    editurl: 'abon_en_lgtfamily_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:200,
    width:600, 
    autowidth: true,
    scroll: 0,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true },     
      {name:'id_lgt', index:'id_lgt', width:40, editable: false, align:'center', hidden:true },     
      {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center', hidden:true },     
      {label:"Член сім'ї",name:'fio', index:'fio', width:200, editable: true, align:'left',edittype:'text'},           
      {label:'Сімейні відносини',name:'id_rel', index:'id_rel', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lrel}, 
                            stype:'text' },   
      {label:'Дата народження',name:'dt_birth', index:'dt_birth', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Сам',name:'lgt', index:'lgt', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox'},

      {label:'Дата початку',name:'dt_start', index:'dt_start', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},      
      {label:'Дата закінч.',name:'dt_end', index:'dt_end', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Активний',name:'active', index:'active', width:50, editable: true, align:'left',
             formatter:'checkbox',edittype:'checkbox'},
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
    postData:{'p_id': lgt_id, 'p_id_paccnt': id_paccnt }, 
    hidegrid: false,
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    jsonReader : {repeatitems: false},
    toolbar: [true,'top'],    
    
    onSelectRow: function(id) { 
      cur_family_row_id = id;  
    },
    
    ondblClickRow: function(id){ 

      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 

          family_validator.resetForm();  //для сброса состояния валидатора
          $("#fFamilyEdit").resetForm();
          $("#fFamilyEdit").clearForm();
          
          jQuery(this).jqGrid('GridToForm',gsr,"#fFamilyEdit"); 
          $("#fFamilyEdit").find("#foper").attr('value','edit');              
          //edit_row_id = id;

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

$("#t_lgt_family_table").append("<button class ='btnClose' id='bt_familyclose0' style='height:20px;font-size:-3' > Закр. </button> ");
    
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });
jQuery('#bt_familyclose0').click( function() { jQuery('#grid_lgtfamily').toggle( ); }); 


jQuery("#lgt_family_table").jqGrid('navButtonAdd','#lgt_family_tablePager',{caption:"Новий",
	onClickButton:function(){ 

          family_validator.resetForm();
          $("#fFamilyEdit").resetForm();
          $("#fFamilyEdit").clearForm();
          
          //edit_row_id = -1;
          $("#fFamilyEdit").find("#fid").attr('value',-1 );    
          $("#fFamilyEdit").find("#fid_lgt").attr('value',current_lgt_id );    
          $("#fFamilyEdit").find("#fid_paccnt").attr('value',id_paccnt );    
          $("#fFamilyEdit").find("#foper").attr('value','add');              
          
          $("#fFamilyEdit").find("#bt_add").show();
          $("#fFamilyEdit").find("#bt_edit").hide();            
          jQuery("#dialog_editlgtform").dialog('open');          
          
        ;} 
});

jQuery("#lgt_family_table").jqGrid('navButtonAdd','#lgt_family_tablePager',{caption:"Редагувати",
	onClickButton:function(){ 

      var gsr = $("#lgt_family_table").jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
          family_validator.resetForm();  //для сброса состояния валидатора
          $("#fFamilyEdit").resetForm();
          $("#fFamilyEdit").clearForm();
          
          $("#lgt_family_table").jqGrid('GridToForm',gsr,"#fFamilyEdit"); 
          $("#fFamilyEdit").find("#foper").attr('value','edit');              

          $("#fFamilyEdit").find("#bt_add").hide();
          $("#fFamilyEdit").find("#bt_edit").show();   
          jQuery("#dialog_editlgtform").dialog('open');          
      } 
   } 
});

jQuery("#lgt_family_table").jqGrid('navButtonAdd','#lgt_family_tablePager',{caption:"Видалити",
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
    beforeSubmit: FamilyFormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FamilyFormSubmitResponse // функция, вызываемая при получении ответа
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
			//dt_birth: "required",                        
			dt_b: "required"
		},
		messages: {
			fio: "Вкажіть ім'я!",
			//dt_birth: "Вкажіть дату народження",
                        dt_b: "Вкажіть початкову дату"
		}
};

family_validator = $("#fFamilyEdit").validate(form_valid_options);



$("#fFamilyEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editlgtform").dialog('close');                           
});


jQuery('#grid_lgtfamily').draggable({ handle: ".ui-jqgrid-titlebar" });


$('#fFamilyEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fFamilyEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
 }); 


}; 
 

// обработчик, который вызываетя перед отправкой формы
function FamilyFormBeforeSubmit(formData, jqForm, options) { 

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
function FamilyFormSubmitResponse(responseText, statusText)
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
                 
               //var fid = jQuery("#fid").val();
               //if(fid) 
               //{ 
               //  jQuery("#lgt_family_table").jqGrid('FormToGrid',fid,"#fFamilyEdit"); 
               //}  
               jQuery('#lgt_family_table').trigger('reloadGrid');        
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
            id : cur_family_row_id 
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

