var cur_dep_id = 0;
var dep_validator = null;
var cur_person_id = 0;
var person_validator = null;
var form_edit_lock=0;

var gsr =null;

jQuery(function(){ 
  jQuery('#dep_tree_table').jqGrid({
    url:'staff_list_dep_data.php',
    editurl: 'staff_list_dep_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:500,
    width:250,
    autowidth: true,
    scroll: 0,
        treeGrid: true,
        treeGridModel: 'adjacency',
        ExpandColumn: 'name',
        ExpandColClick: true,    
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {name:'id_parent_department', index:'id_parent_department', width:40, editable: true, align:'center', hidden:true},     
      {name:'lvl', index:'lvl', width:40, editable: false, align:'center', hidden:true},           
      {label:"Назва",name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text'},                 
      {label:"Повна назва",name:'full_name', index:'full_name', width:200, editable: true, align:'left',edittype:'text',hidden:true}
    ],
    pager: '#dep_tree_tablePager',
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    
//    rowNum:100,
    sortname: 'name',
    sortorder: 'asc',
//    viewrecords: true,
//    gridview: true,
    caption: "Підрозділи",
    hidegrid: false,
//    hiddengrid: false,
 //   jsonReader : {repeatitems: false},
    
   // postData : {
   //   nodeid:rc.id,
   //   parentid:rc.parent_id,
   //   n_level:rc.level   
   // },
    onSelectRow: function(id) { 
      cur_dep_id = id;  
      jQuery('#persons_table').jqGrid('setGridParam',{datatype: 'json','postData':{'p_id':id}}).trigger('reloadGrid');              
    },
    
    ondblClickRow: function(id){ 


            gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
            if(gsr)
            { 

                var request = $.ajax({
                    url: "staff_list_dep_sel_data.php",
                    type: "POST",
                    data: {
                        cur_node : cur_dep_id
                    },
                    dataType: "html"
                });

                request.done(function(data ) {
                    //alert(data);
            
                    $("#fDepartmentEdit").find("#fid_parent").html(data);
                    
                    dep_validator.resetForm();  //для сброса состояния валидатора
                    $("#fDepartmentEdit").resetForm();
                    $("#fDepartmentEdit").clearForm();
          
                    $("#dep_tree_table").jqGrid('GridToForm',gsr,"#fDepartmentEdit"); 
                    $("#fDepartmentEdit").find("#foper").attr('value','edit');              
                    edit_row_id = id;

                    $("#fDepartmentEdit").find("#bt_add").hide();
                    $("#fDepartmentEdit").find("#bt_edit").show();   
                    $("#dialog_editdepform").dialog('open');          
                    
                    if (r_edit==3)
                       $("#fDepartmentEdit").find("#bt_edit").prop('disabled', false);
                    else
                       $("#fDepartmentEdit").find("#bt_edit").prop('disabled', true);
            
            
                });
                request.fail(function(data ) {
                    alert("error");
                });

            }  
  } ,  
  gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
  },
      
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#dep_tree_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

 jQuery("#dep_tree_tablePager_center").hide();
 jQuery("#dep_tree_tablePager_right").hide();


jQuery("#dep_tree_table").jqGrid('navButtonAdd','#dep_tree_tablePager',{caption:"Новий",
        id:"btn_dep_new",
	onClickButton:function(){ 


           var request = $.ajax({
               url: "staff_list_dep_sel_data.php",
               type: "POST",
               dataType: "html"
           });

            request.done(function(data ) {
                //alert(data);
            
                $("#fDepartmentEdit").find("#fid_parent").html(data);
                    
                 dep_validator.resetForm();
                $("#fDepartmentEdit").resetForm();
                $("#fDepartmentEdit").clearForm();
          
                $("#fDepartmentEdit").find("#fid").attr('value',-1 );    
                $("#fDepartmentEdit").find("#fid_parent").attr('value',cur_dep_id );    
                $("#fDepartmentEdit").find("#foper").attr('value','add');              
          
                $("#fDepartmentEdit").find("#bt_add").show();
                $("#fDepartmentEdit").find("#bt_edit").hide();            
               jQuery("#dialog_editdepform").dialog('open');          
            
            
            });
            request.fail(function(data ) {
                alert("error");
            });
          
        ;} 
});

jQuery("#dep_tree_table").jqGrid('navButtonAdd','#dep_tree_tablePager',{caption:"Видалити",
        id:"btn_dep_del",
	onClickButton:function(){ 

      if ($("#dep_tree_table").getDataIDs().length == 0) 
       {return} ;    

      if ($('#dep_tree_table').jqGrid('getCell',cur_dep_id,'isLeaf')=="false")
      {
        jQuery("#dialog-confirm").find("#dialog-text").html('Неможливо видалити підрозділ, який містить інші підрозділи !');
    
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

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити підрозділ?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                        fDeps_ajaxForm[0].oper.value = 'del';
                                        fDeps_ajaxForm[0].id.value = cur_dep_id;
                                        fDeps_ajaxForm.ajaxSubmit(dep_form_options);       

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
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});

$("#dialog_editdepform").dialog({
			resizable: true,
		//	height:140,
                        width:600,
			modal: true,
                        autoOpen: false,
                        title:"Підрозділ"
});


 var dep_form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: DepartmentFormSubmitResponse // функция, вызываемая при получении ответа
  };

fDeps_ajaxForm = $("#fDepartmentEdit").ajaxForm(dep_form_options);


$.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
jQuery(".dtpicker").mask("99.99.9999");


jQuery("#fDepartmentEdit :input").addClass("ui-widget-content ui-corner-all");

// опции валидатора общей формы
var depform_valid_options = { 

		rules: {
			name: "required"
		},
		messages: {
			name: "Вкажіть назву!"
		}
};

dep_validator = $("#fDepartmentEdit").validate(depform_valid_options);



$("#fDepartmentEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editdepform").dialog('close');                           
});


///=========================== persons table ===============================
  var init_datatype = 'local'; 

  if ( id_person >0)
  {
      init_datatype = 'json'; 
  }

  jQuery('#persons_table').jqGrid({
    url:     'staff_list_person_data.php',
    editurl: 'staff_list_person_edit.php',
    //datatype: 'json',
    datatype: init_datatype,
    mtype: 'POST',
    height:200,
    width:800,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {name:'id_department', index:'id_department', width:40, editable: false, align:'center',hidden:true},
      
      {label:"Працівник",name:'represent_name', index:'represent_name', width:150, editable: true, align:'left',edittype:"text"},      
      {label:"Прізвище",name:'soname', index:'soname', width:150, editable: true, align:'left',edittype:"text", hidden:true},      
      {label:"Ім'я",name:'name', index:'name', width:150, editable: true, align:'left',edittype:"text", hidden:true},
      {label:"По батькові",name:'father_name', index:'father_name', width:150, editable: true, align:'left',edittype:"text", hidden:true},      

      {label:'id_post',name:'id_post', index:'id_post', width:40, editable: true, align:'right', hidden:true},
      {label:'Посада',name:'name_post', index:'name_post', width:120, editable: true, align:'right'},                       
      {label:'Тел.',name:'phone', index:'phone', width:70, editable: true, align:'right'},                       
      {label:'id_abon',name:'id_abon', index:'id_abon', width:40, editable: true, align:'right', hidden:true},                             
      {label:'Абонент',name:'name_abon', index:'name_abon', width:120, editable: true, align:'right', hidden:true},                       

      {label:'Працює', name:'is_active', index:'is_active', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox'},
      {label:"Курь'єр", name:'is_runner', index:'is_runner', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox'},

                        
      {label:'Дата пр.',name:'date_start', index:'date_start', width:70, editable: true, 
                        align:'left',edittype:'text',formatter:'date', editrules:{required:true}},
      
      {label:'Дата звільн.',name:'date_end', index:'date_end', width:70, editable: true, 
                        align:'left',edittype:'text',formatter:'date', editrules:{required:false}},

      {name:'work_period', index:'work_period', width:80, editable: true, align:'left',edittype:'text', hidden:true},
      {name:'dt_input', index:'dt_input', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},        
        
                            
    ],
    pager: '#persons_tablePager',
    autowidth: true,
    rowNum:500,
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    //rowList:[20,50,100,300,500],
    sortname: 'represent_name',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Працівники',
    hidegrid: false,
    postData:{'p_id':0, person_id:id_person },
    
    gridComplete:function(){

            if ( id_person >0)
            {
                $(this).setSelection(id_person, true);       
                cur_person = id_person;
                
                cur_dep_id = jQuery(this).jqGrid('getCell',id_person,'id_department');
                id_person =0;
                
                $(this).jqGrid('setGridParam',{'postData':{'p_id':cur_dep_id, person_id:0 }});

                jQuery('#dep_tree_table').setSelection(cur_dep_id, true);       
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
    
    onSelectRow: function(id) { 
          cur_person = id;
      
    },
    
    ondblClickRow: function(id){ 
      if(selmode==1)
      {
           window.opener.SelectPersonExternal(id,jQuery(this).jqGrid('getCell',id,'represent_name') );
           window.opener.focus();
           self.close();            
      }

      if(selmode==0)
      {
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            
        var request = $.ajax({
            url: "staff_list_dep_sel_data.php",
            type: "POST",
            dataType: "html"
        });

           request.done(function(data ) {
            //alert(data);
            
            $("#fPersonEdit").find("#fid_department").html(data);
                    
            person_validator.resetForm();  //для сброса состояния валидатора
            $("#fPersonEdit").resetForm();
            $("#fPersonEdit").clearForm();
          
            $("#persons_table").jqGrid('GridToForm',gsr,"#fPersonEdit"); 
            $("#fPersonEdit").find("#foper").attr('value','edit');              
            cur_person = id;

            $("#fPersonEdit").find("#bt_add").hide();
            $("#fPersonEdit").find("#bt_edit").show();   
            $("#dialog_editpersonform").dialog('open');          
            
            if (r_edit==3)
               $("#fPersonEdit").find("#bt_edit").prop('disabled', false);
            else
               $("#fPersonEdit").find("#bt_edit").prop('disabled', true);
            
            
        });
        request.fail(function(data ) {
            alert("error");
        });

        }
      }
     } ,  

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#persons_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

jQuery("#persons_tablePager_center").hide();
jQuery("#persons_table").jqGrid('filterToolbar','');

jQuery("#persons_table").jqGrid('navButtonAdd','#persons_tablePager',{caption:"Відкрити",
        id:"btn_person_open",
        onClickButton:function(){
            
        gsr = jQuery("#persons_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            
        var request = $.ajax({
            url: "staff_list_dep_sel_data.php",
            type: "POST",
            dataType: "html"
        });

           request.done(function(data ) {
            //alert(data);
            
            $("#fPersonEdit").find("#fid_department").html(data);
                    
            person_validator.resetForm();  //для сброса состояния валидатора
            $("#fPersonEdit").resetForm();
            $("#fPersonEdit").clearForm();
          
            $("#persons_table").jqGrid('GridToForm',gsr,"#fPersonEdit"); 
            $("#fPersonEdit").find("#foper").attr('value','edit');              

            $("#fPersonEdit").find("#bt_add").hide();
            $("#fPersonEdit").find("#bt_edit").show();   
            $("#dialog_editpersonform").dialog('open');          
            
            
        });
        request.fail(function(data ) {
            alert("error");
        });

        }
            
        
    }
});


jQuery("#persons_table").jqGrid('navButtonAdd','#persons_tablePager',{
    caption:"Новий працівник",
    id:"btn_person_new",
    onClickButton:function(){ 

        var request = $.ajax({
            url: "staff_list_dep_sel_data.php",
            type: "POST",
            dataType: "html"
        });

        request.done(function(data ) {
            //alert(data);
            
            $("#fPersonEdit").find("#fid_department").html(data);
                    
            person_validator.resetForm();
            $("#fPersonEdit").resetForm();
            $("#fPersonEdit").clearForm();
          

            $("#fPersonEdit").find("#fid").attr('value',-1 );    
            $("#fPersonEdit").find("#foper").attr('value','add');              
            $("#fPersonEdit").find("#fid_department").attr('value',cur_dep_id );    
          
            $("#fPersonEdit").find("#bt_add").show();
            $("#fPersonEdit").find("#bt_edit").hide();            
            jQuery("#dialog_editpersonform").dialog('open');          
            
            
        });
        request.fail(function(data ) {
            alert("error");
        });
          

    } 
    
});

jQuery("#persons_table").jqGrid('navButtonAdd','#persons_tablePager',{caption:"Видалити",
        id:"btn_person_del",
	onClickButton:function(){ 

      if ($("#persons_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити працівника?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                        fPerson_ajaxForm[0].oper.value = 'del';
                                        fPerson_ajaxForm[0].id.value = cur_person;
                                        fPerson_ajaxForm.ajaxSubmit(person_form_options);       

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
   $('#btn_dep_del').addClass('ui-state-disabled');
   $('#btn_dep_new').addClass('ui-state-disabled');
   $('#btn_person_new').addClass('ui-state-disabled');
   $('#btn_person_del').addClass('ui-state-disabled');
}

/*
   jQuery("#btCategorSel").click( function() { 
     var ww = window.open("lgt_category.php", "lgtcat_win", "toolbar=0,width=800,height=600");
     document.lgtcatsel_params.submit();
     ww.focus();
   });
*/
$("#dialog_editpersonform").dialog({
			resizable: true,
		//	height:140,
                        width:700,
			modal: true,
                        autoOpen: false,
                        title:"Працівник"
});


 var person_form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: PersonFormSubmitResponse // функция, вызываемая при получении ответа
  };

fPerson_ajaxForm = $("#fPersonEdit").ajaxForm(person_form_options);


jQuery("#fPersonEdit :input").addClass("ui-widget-content ui-corner-all");

// опции валидатора общей формы
var personform_valid_options = { 

		rules: {
			represent_name: "required"
		},
		messages: {
			represent_name: "Вкажіть ім'я!"
		}
};

person_validator = $("#fPersonEdit").validate(personform_valid_options);



$("#fPersonEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editpersonform").dialog('close');                           
});


$("#btAbonSel").click( function() { 

     var ww = window.open("dov_abon.php", "abon_win", "toolbar=0,width=800,height=600");
     document.abon_sel_params.submit();
     ww.focus();
   });

$("#btPostSel").click( function() { 

     var ww = window.open("dov_posts.php", "posts_win", "toolbar=0,width=800,height=600");
     document.post_sel_params.submit();
     ww.focus();
   });

//==========================================================================
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
	,	center__paneSelector:	"#pmain_center"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            //jQuery("#lgt_category_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
           // jQuery("#lgt_category_table").jqGrid('setGridHeight',_pane.innerHeight()-90);
        }

}); 

 innerLayout = $("#pmain_center").layout({
		name:	"inner" 
	,	west__paneSelector:	"#pDepartmentTable"
	,	west__closable:	false
	,	west__resizable:	true
        ,	west__size:		250
	,	center__paneSelector:	"#pPersonsTable"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#persons_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#persons_table").jqGrid('setGridHeight',_pane.innerHeight()-105);
        }
	,       west__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#dep_tree_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#dep_tree_table").jqGrid('setGridHeight',_pane.innerHeight()-85);
        }

}); 

if(selmode!=0)
{
   outerLayout.hide('north');        
};    

outerLayout.resizeAll();
outerLayout.close('south');     

/*
  $('#fDepartmentEdit input').keypress(function(e){
    if ( e.which == 13 ) return false;
  });   

  $('#fPersonEdit input').keypress(function(e){
    if ( e.which == 13 ) return false;
  });   
*/
  $('#fDepartmentEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fDepartmentEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 

  $('#fPersonEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fPersonEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 


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
function DepartmentFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_editdepform").dialog('close');                           
               jQuery('#dep_tree_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {

               jQuery("#dialog_editdepform").dialog('close');                                            
               jQuery('#dep_tree_table').trigger('reloadGrid');        
               
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

function PersonFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_editpersonform").dialog('close');                           
               jQuery('#persons_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {

               jQuery("#dialog_editpersonform").dialog('close');                                            
               jQuery('#persons_table').trigger('reloadGrid');        
               
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

function SelectAbonExternal(id, name) {
        $("#fPersonEdit").find("#fid_abon").attr('value',id );
        $("#fPersonEdit").find("#fname_abon").attr('value',name );    
}

function SelectPostExternal(id, name) {
        $("#fPersonEdit").find("#fid_post").attr('value',id );
        $("#fPersonEdit").find("#fname_post").attr('value',name );    

}

