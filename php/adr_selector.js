var edit_row_id = 0;
var cur_row_id = 0;
var validator = null;
var gsr =null;
var form_edit_lock=0;

jQuery(function(){ 
  jQuery('#adr_class_table').jqGrid({
    url:'adr_selector_data.php',
    editurl: 'adr_selector_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:500,
    width:800,
    autowidth: true,
    scroll: 0,
        treeGrid: true,
        treeGridModel: 'adjacency',
        ExpandColumn: 'name',
        ExpandColClick: true,    
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {name:'id_parent', index:'id_parent', width:40, editable: true, align:'center', hidden:true},     
      {name:'lvl', index:'lvl', width:40, editable: false, align:'center', hidden:true},           

      {label:"Назва",name:'name', index:'name', width:400, editable: true, align:'left',edittype:'text'},                 
   //   {label:"Назва повна",name:'name_full', index:'name_full', width:400, editable: true, align:'left',edittype:'text', hidden:true},                 
      {label:"Код",name:'ident', index:'ident', width:80, editable: true, align:'left',edittype:'text'},           
      {label:"Індекс",name:'indx', index:'indx', width:80, editable: true, align:'left',edittype:'text'},           

      {name:'idk_class', index:'idk_class', width:40, editable: true, align:'center', hidden:true},     
      {label:"Тип",name:'kind_name', index:'kind_name', width:200, editable: true, align:'left',edittype:'text'},                 
      {label:"Код типу",name:'kind_ident', index:'kind_ident', width:80, editable: true, align:'left',edittype:'text', hidden:true},           
      {label:"Префикс",name:'short_prefix', index:'short_prefix', width:80, editable: true, align:'left',edittype:'text'},           
      {label:"Постфикс",name:'short_postfix', index:'short_postfix', width:80, editable: true, align:'left',edittype:'text'},                 

      {label:'dt',name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}


    ],
    pager: '#adr_class_tablePager',
//    rowNum:100,
    sortname: 'ident',
    sortorder: 'asc',
//    viewrecords: true,
//    gridview: true,
    caption: "Райони, міста та вулиці",
    
//    hiddengrid: false,
 //   jsonReader : {repeatitems: false},
    
    onSelectRow: function(id) { 
      cur_row_id = id;  
    },
    
    ondblClickRow: function(id){ 

/*
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 

            if(selmode==1)
            {
                window.opener.SelectTarCategoryExternal(id,jQuery(this).getRowData(id)['name'] );

                window.opener.focus();
                self.close();            
            }

            if(selmode==0)
            {
                var request = $.ajax({
                    url: "lgt_category_sel_data.php",
                    type: "POST",
                    data: {
                        cur_node : cur_row_id
                    },
                    dataType: "html"
                });

                request.done(function(data ) {
                    //alert(data);
            
                    $("#fLgtCategoryEdit").find("#fid_parent").html(data);
                    
                    validator.resetForm();  //для сброса состояния валидатора
                    $("#fLgtCategoryEdit").resetForm();
                    $("#fLgtCategoryEdit").clearForm();
          
                    $("#lgt_category_table").jqGrid('GridToForm',gsr,"#fLgtCategoryEdit"); 
                    $("#fLgtCategoryEdit").find("#foper").attr('value','edit');              
                    edit_row_id = id;

                    $("#fLgtCategoryEdit").find("#bt_add").hide();
                    $("#fLgtCategoryEdit").find("#bt_edit").show();   
                    $("#dialog_editform").dialog('open');          
            
            
                });
                request.fail(function(data ) {
                    alert("error");
                });
         
            }



        }  
      */
    } ,  
  gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
  },
      
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#adr_class_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
jQuery("#adr_class_table").jqGrid('filterToolbar','');        

/*
jQuery("#lgt_category_table").jqGrid('navButtonAdd','#lgt_category_tablePager',{caption:"Нова",
	onClickButton:function(){ 


            var request = $.ajax({
                url: "lgt_category_sel_data.php",
                type: "POST",
                dataType: "html"
            });

            request.done(function(data ) {
                //alert(data);
            
                $("#fLgtCategoryEdit").find("#fid_parent").html(data);
                    
                 validator.resetForm();
                $("#fLgtCategoryEdit").resetForm();
                $("#fLgtCategoryEdit").clearForm();
          
                edit_row_id = -1;
                $("#fLgtCategoryEdit").find("#fid").attr('value',-1 );    
                $("#fLgtCategoryEdit").find("#fid_parent").attr('value',cur_row_id );    
                $("#fLgtCategoryEdit").find("#foper").attr('value','add');              
          
                $("#fLgtCategoryEdit").find("#bt_add").show();
                $("#fLgtCategoryEdit").find("#bt_edit").hide();            
               jQuery("#dialog_editform").dialog('open');          
            
            
            });
            request.fail(function(data ) {
                alert("error");
            });
          
        ;} 
});
*/
/*
jQuery("#lgt_category_table").jqGrid('navButtonAdd','#lgt_category_tablePager',{caption:"Видалити",
	onClickButton:function(){ 

      if ($("#lgt_category_table").getDataIDs().length == 0) 
       {return} ;    

      if ($('#lgt_category_table').jqGrid('getCell',cur_row_id,'isLeaf')=="false")
      {
        jQuery("#dialog-confirm").find("#dialog-text").html('Неможливо видалити категорію, яка має підкатегорії!');
    
        $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Відмінити": function() {
					$( this ).dialog( "close" );
				}
			}
		});
    
         jQuery("#dialog-confirm").dialog('open');   
         return;
              
      }

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити категорію?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                        fLgtCat_ajaxForm[0].oper.value = 'del';
                                        fLgtCat_ajaxForm[0].id.value = cur_row_id;
                                        fLgtCat_ajaxForm.ajaxSubmit(form_options);       

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


jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});

$("#dialog_editform").dialog({
			resizable: true,
		//	height:140,
                        width:700,
			modal: true,
                        autoOpen: false,
                        title:"Пільгова категорія"
});


 var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FormSubmitResponse // функция, вызываемая при получении ответа
  };

fLgtCat_ajaxForm = $("#fLgtCategoryEdit").ajaxForm(form_options);


$.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
jQuery(".dtpicker").mask("99.99.9999");


jQuery("#fLgtCategoryEdit :input").addClass("ui-widget-content ui-corner-all");

// опции валидатора общей формы
var form_valid_options = { 

		rules: {
			name: "required"
		},
		messages: {
			name: "Вкажіть назву!"
		}
};

validator = $("#fLgtCategoryEdit").validate(form_valid_options);



$("#fLgtCategoryEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});


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
	,	south__closable:	false
	,	south__resizable:	false
        ,	south__size:		40
	,	south__spacing_open:	0
	,	center__paneSelector:	"#grid_dform"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#adr_class_table").jqGrid('setGridWidth',_pane.innerWidth()-20);
            jQuery("#adr_class_table").jqGrid('setGridHeight',_pane.innerHeight()-120);
        }

}); 
outerLayout.resizeAll();

if(selmode!=0)
{
    outerLayout.hide('north');        
}


}); 
 

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
       if(!submit_form.validate().form())  {return false;}
       else {
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
               jQuery('#lgt_category_table').trigger('reloadGrid');        
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
               
               jQuery('#lgt_category_table').trigger('reloadGrid');        
               
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
