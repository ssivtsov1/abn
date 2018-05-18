var cur_grp_id = 0;
var grp_validator = null;
var cur_person_id = 0;
var person_validator = null;
var isEnvGridCreated =false;
var form_edit_lock=0;
var gsr =null;
var selICol=0;
var selIRow =0;

jQuery(function(){ 
  jQuery('#group_table').jqGrid({
    url:'user_list_grp_data.php',
    editurl: 'user_list_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:500,
    width:250,
    autowidth: true,
//    scroll: 0,
//        treeGrid: true,
//        treeGridModel: 'adjacency',
//        ExpandColumn: 'name',
//        ExpandColClick: true,    
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {name:'id_parent', index:'id_parent', width:40, editable: true, align:'center', hidden:true},     
      {name:'flag_type', index:'flag_type', width:40, editable: false, align:'center', hidden:true},           
      {label:"Назва",name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text'}
    ],
    pager: '#group_tablePager',
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    rowNum:100,
    sortname: 'id',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: "Групи користувачів",
    hidegrid: false,
//    hiddengrid: false,
    jsonReader : {repeatitems: false},
    
   // postData : {
   //   nodeid:rc.id,
   //   parentid:rc.parent_id,
   //   n_level:rc.level   
   // },
    onSelectRow: function(id) { 
      cur_grp_id = id;  
      jQuery('#persons_table').jqGrid('setGridParam',{'postData':{'p_id':id}}).trigger('reloadGrid');              
    },
    
    ondblClickRow: function(id){ 

        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            grp_validator.resetForm();  //для сброса состояния валидатора
            $("#fGroupEdit").resetForm();
            $("#fGroupEdit").clearForm();
          
            $("#group_table").jqGrid('GridToForm',gsr,"#fGroupEdit"); 
            $("#fGroupEdit").find("#foper").attr('value','edit');              
            edit_row_id = id;

            $("#fGroupEdit").find("#bt_add").hide();
            if (r_edit==1)
              $("#fGroupEdit").find("#bt_edit").show();   
            else          
              $("#fGroupEdit").find("#bt_edit").hide();     

            $("#dialog_editgrpform").dialog('open');          

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

  }).navGrid('#group_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

 jQuery("#group_tablePager_center").hide();
 jQuery("#group_tablePager_right").hide();


jQuery("#group_table").jqGrid('navButtonAdd','#group_tablePager',{
        id:"group_table_add",
        caption:"Новий",
        onClickButton:function(){ 

            grp_validator.resetForm();
            $("#fGroupEdit").resetForm();
            $("#fGroupEdit").clearForm();
          
            $("#fGroupEdit").find("#fid").attr('value',-1 );    
//            $("#fGroupEdit").find("#fid_parent").attr('value',cur_grp_id );    
            $("#fGroupEdit").find("#foper").attr('value','add');              
          
            $("#fGroupEdit").find("#bt_add").show();
            $("#fGroupEdit").find("#bt_edit").hide();            
            jQuery("#dialog_editgrpform").dialog('open');          
        } 
});

jQuery("#group_table").jqGrid('navButtonAdd','#group_tablePager',{caption:"Видалити",
        id:'group_table_del',
	onClickButton:function(){ 

      if ($("#group_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити групу?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                        fGroup_ajaxForm[0].oper.value = 'del';
                                        fGroup_ajaxForm[0].id.value = cur_grp_id;
                                        fGroup_ajaxForm.ajaxSubmit(dep_form_options);       

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

jQuery("#group_table").jqGrid('navButtonAdd','#group_tablePager',{
    caption:"Права",
    onClickButton:function(){ 
           ShowRightsDlg(cur_grp_id); 
    } 
    
});


jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});

$("#dialog_editgrpform").dialog({
			resizable: true,
		//	height:140,
                        width:400,
			modal: true,
                        autoOpen: false,
                        title:"Група"
});


 var dep_form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: DepartmentFormSubmitResponse // функция, вызываемая при получении ответа
  };

fGroup_ajaxForm = $("#fGroupEdit").ajaxForm(dep_form_options);


$.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
jQuery(".dtpicker").mask("99.99.9999");


jQuery("#fGroupEdit :input").addClass("ui-widget-content ui-corner-all");
jQuery("#fPasswd :input").addClass("ui-widget-content ui-corner-all");

// опции валидатора общей формы
var depform_valid_options = { 

		rules: {
			name: "required"
		},
		messages: {
			name: "Вкажіть назву!"
		}
};

grp_validator = $("#fGroupEdit").validate(depform_valid_options);



$("#fGroupEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editgrpform").dialog('close');                           
});


///=========================== persons table ===============================

  jQuery('#persons_table').jqGrid({
    url:     'user_list_person_data.php',
    editurl: 'user_list_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:200,
    width:800,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {name:'id_parent', index:'id_parent', width:40, editable: false, align:'center',hidden:true},
      {name:'flag_type', index:'flag_type', width:40, editable: false, align:'center', hidden:true},           
      {label:"Користувач",name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text'},      
      {label:'id_person',name:'id_person', index:'id_person', width:40, editable: true, align:'right', hidden:true},                             
      {label:"Працівник",name:'represent_name', index:'represent_name', width:150, editable: true, align:'left',edittype:"text"},      

      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {name:'pwd_code', index:'pwd_code', width:100, editable: true, align:'left',edittype:'text', hidden:true}        
                           
    ],
    pager: '#persons_tablePager',
    autowidth: true,
    rowNum:500,
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    //rowList:[20,50,100,300,500],
    sortname: 'name',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Користувачі',
    hidegrid: false,
    jsonReader : {repeatitems: false},
    postData:{'p_id':0, person_id:id_person},
    
    gridComplete:function(){

            if ( id_person >0)
            {
                $(this).setSelection(id_person, true);       
                cur_person = id_person;
                
                cur_grp_id = jQuery(this).jqGrid('getCell',id_person,'id_parent');
                id_person =0;
                
                $(this).jqGrid('setGridParam',{'postData':{'p_id':cur_grp_id, person_id:0}});

                jQuery('#group_table').setSelection(cur_grp_id, true);       
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
           window.opener.SelectUserExternal(id,jQuery(this).jqGrid('getCell',id,'name') );
           window.opener.focus();
           self.close();            
      }

      if(selmode==0)
      {
        gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            
        var request = $.ajax({
            url: "user_list_grp_sel_data.php",
            type: "POST",
            dataType: "html"
        });

           request.done(function(data ) {
            //alert(data);
            
            $("#fPersonEdit").find("#fid_parent").html(data);
                    
            person_validator.resetForm();  //для сброса состояния валидатора
            $("#fPersonEdit").resetForm();
            $("#fPersonEdit").clearForm();
          
            $("#persons_table").jqGrid('GridToForm',gsr,"#fPersonEdit"); 
            $("#fPersonEdit").find("#foper").attr('value','edit');              
            cur_person = id;

            $("#fPersonEdit").find("#bt_add").hide();
            if (r_edit==1)
              $("#fPersonEdit").find("#bt_edit").show();   
            else         
              $("#fPersonEdit").find("#bt_edit").hide();   

            $("#dialog_editpersonform").dialog('open');          
            
            
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

jQuery("#persons_table").jqGrid('navButtonAdd','#persons_tablePager',{caption:"Відкрити",
        onClickButton:function(){
            
        gsr = jQuery("#persons_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            
        var request = $.ajax({
            url: "user_list_grp_sel_data.php",
            type: "POST",
            dataType: "html"
        });

           request.done(function(data ) {
            //alert(data);
            
            $("#fPersonEdit").find("#fid_parent").html(data);
                    
            person_validator.resetForm();  //для сброса состояния валидатора
            $("#fPersonEdit").resetForm();
            $("#fPersonEdit").clearForm();
          
            $("#persons_table").jqGrid('GridToForm',gsr,"#fPersonEdit"); 
            $("#fPersonEdit").find("#foper").attr('value','edit');              

            $("#fPersonEdit").find("#bt_add").hide();
            if (r_edit==1)
              $("#fPersonEdit").find("#bt_edit").show();   
            else         
              $("#fPersonEdit").find("#bt_edit").hide();   

            $("#dialog_editpersonform").dialog('open');          
            
            
        });
        request.fail(function(data ) {
            alert("error");
        });

        }
            
        
    }
});


jQuery("#persons_table").jqGrid('navButtonAdd','#persons_tablePager',{
    caption:"Новий",
    id:'users_table_add',
    onClickButton:function(){ 

        var request = $.ajax({
            url: "user_list_grp_sel_data.php",
            type: "POST",
            dataType: "html"
        });

        request.done(function(data ) {
            //alert(data);
            
            $("#fPersonEdit").find("#fid_parent").html(data);
                    
            person_validator.resetForm();
            $("#fPersonEdit").resetForm();
            $("#fPersonEdit").clearForm();
          

            $("#fPersonEdit").find("#fid").attr('value',-1 );    
            $("#fPersonEdit").find("#foper").attr('value','add');              
            $("#fPersonEdit").find("#fid_parent").attr('value',cur_grp_id );    
          
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
        id:'users_table_del',
	onClickButton:function(){ 

      if ($("#persons_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити користувача?');
    
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

jQuery("#persons_table").jqGrid('navButtonAdd','#persons_tablePager',{
    caption:"Права",
    onClickButton:function(){ 
      if ($("#persons_table").getDataIDs().length == 0) 
       {return} ;    
        
      ShowRightsDlg(cur_person); 
    }     
});

jQuery("#persons_table").jqGrid('navButtonAdd','#persons_tablePager',{
    caption:"Пароль",
    id:'users_table_pass',
    onClickButton:function(){ 
    if ($("#persons_table").getDataIDs().length == 0) 
    {return} ;    
    ChangePasswdDlg();       
    }     
});


$("#dialog_editpersonform").dialog({
			resizable: true,
		//	height:140,
                        width:600,
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
			name: "required"
		},
		messages: {
			name: "Вкажіть ім'я!"
		}
};

person_validator = $("#fPersonEdit").validate(personform_valid_options);



$("#fPersonEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editpersonform").dialog('close');                           
});


$("#btPersonSel").click( function() {


     createPersonGrid($("#fid_person").val());
     person_target_id=$("#fid_person")
     person_target_name =  $("#frepresent_name")
     person_target_prof = 0;
    
     jQuery("#grid_selperson").css({'left': $("#frepresent_name").offset().left+1, 'top': $("#frepresent_name").offset().top+20});
     jQuery("#grid_selperson").toggle( );
/*
    if ($("#fPersonEdit").find("#fid_person").val()!='')
        $("#fperson_sel_params_id_person").attr('value', $("#fPersonEdit").find("#fid_person").val() );    
    else
        $("#fperson_sel_params_id_person").attr('value', '0' );    
    
     var www = window.open("staff_list.php", "cntrl_win", "toolbar=0,width=900,height=600");
     document.person_sel_params.submit();
     www.focus();
    */
 });

 $("#fPasswd").find("#fpasswd1").bind('input propertychange', function() {
        $("#fPasswd").find('#error_zone').html("");  
        //$("#fPasswd").find('#error_zone').hide();
 });

 $("#fPasswd").find("#fpasswd2").bind('input propertychange', function() {
        $("#fPasswd").find('#error_zone').html("");  
       // $("#fPasswd").find('#error_zone').hide();
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
	,	west__paneSelector:	"#pGroupTable"
	,	west__closable:	false
	,	west__resizable:	true
        ,	west__size:		300
	,	center__paneSelector:	"#pPersonsTable"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#persons_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#persons_table").jqGrid('setGridHeight',_pane.innerHeight()-85);
        }
	,       west__onresize:	function (pane, _pane, state, options) 
        {
            jQuery("#group_table").jqGrid('setGridWidth',_pane.innerWidth()-9);
            jQuery("#group_table").jqGrid('setGridHeight',_pane.innerHeight()-85);
        }

}); 

if(selmode!=0)
{
   outerLayout.hide('north');        
};    

outerLayout.resizeAll();
outerLayout.close('south');             

if (r_edit==0)
{
    $('#group_table_add').addClass('ui-state-disabled');
    $('#group_table_del').addClass('ui-state-disabled');

    $('#users_table_add').addClass('ui-state-disabled');
    $('#users_table_del').addClass('ui-state-disabled');
    $('#users_table_pass').addClass('ui-state-disabled');

}

  $('#fPersonEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fPersonEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 


//==============================================================================
function CreateEnvGrid(id_user)
{
  if (isEnvGridCreated)
      {
        jQuery('#enviroment_table').jqGrid('setGridParam',{'postData':{'p_id':id_user}}).trigger('reloadGrid');                        
        return;
      }
  isEnvGridCreated =true;

  jQuery('#enviroment_table').jqGrid({
    url:'user_list_enviroment_data.php',
    datatype: 'json',
    mtype: 'POST',
    height:450,
    width:650,
    //autowidth: true,
    colNames:[],
    colModel:[
        
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {name:'id_usr', index:'id_usr', width:40, editable: false, align:'center', key:true, hidden:true},      
      {label:"Назва",name:'name', index:'name', width:200, editable: false, align:'left',edittype:'text'},
      {label:"Ідент",name:'ident', index:'ident', width:50, editable: false, align:'right',edittype:'text'}, 
      {label:"Права",name:'rule', index:'rule', width:100, editable: true, align:'right',
            edittype:'select',formatter:'select',editoptions:{value:laccesslist},
            classes: 'editable_column_class'
            //editrules:{
            //    number:true
            //},
            /*
            editoptions: {
                //dataInit : function (elem) {
                //    $(elem).focus(function(){
                //        this.select();
                //    })
                //},
                dataEvents: [
                { 
                    type: 'keydown', 
                    fn: function(e) { 
                        var key = e.charCode || e.keyCode;
                        if (key == 13)//enter
                        {
                            setTimeout("jQuery('#indic_table').editCell(" + selIRow + " + 1, " + selICol + ", true);", 100);
                        }
                    }
                } 
                ]
                }
               */ 
      }

    ],
    pager: '#enviroment_tablePager',
    //autowidth: true,
    //shrinkToFit : false,
    rowNum:500,
    //rowList:[50,100,200],
    sortname: 'id',
    sortorder: 'asc',
    viewrecords: true,
    //gridview: true,
    caption: '',
    //hiddengrid: false,
    forceFit : true,
    hidegrid: false,    
    postData:{'p_id':id_user},
    cellEdit: true, 
    cellsubmit: 'clientArray',
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    
    gridComplete:function(){
/*
     if ($(this).getDataIDs().length > 0) 
     {      
       var first_id = parseInt($(this).getDataIDs()[0]);
       $(this).setSelection(first_id, true);
     }
     */
    },
    onSelectRow: function(id) { 
          //cur_indic_id = id;
    },
    
    beforeEditCell : function(rowid, cellname, value, iRow, iCol)
    {
        selICol = iCol;
        selIRow = iRow;
    },    
    
    afterEditCell: function (id,name,val,iRow,iCol)
    { 
       // if(name=='dt_indic') 
       // {
       //     jQuery("#"+iRow+"_dt_indic","#indic_table").mask("99.99.9999"); 
       // }
    },    
     afterSaveCell : function(rowid,name,val,iRow,iCol) {
            
            $(this).setCell(rowid,name,'','mod_column_class');
            //jQuery('#indic_table').setCell(rowid,'name','','mod_column_class');
            //jQuery('#indic_table').setCell(rowid,'demand','','mod_column_class');
        },    
    
    
    //ondblClickRow: function(id){ 
    //     jQuery(this).editGridRow(id,LgtNormEditOptions);  
    //} ,  

    loadError : function(xhr,st,err) {
      jQuery('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);
    }
  
  //  jsonReader : { repeatitems: false }

  }).navGrid('#enviroment_tablePager',
         {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ).jqGrid('bindKeys'); 
}

function ShowRightsDlg(id_user)
{
      //jQuery("#dialog-enviroment").find("#dialog-text").html('Видалити особовий рахунок?');
      $(".mod_column_class").removeClass("mod_column_class");
      CreateEnvGrid(id_user);
    
      $("#dialog-enviroment").dialog({
			resizable: true,
			height:600,
                        width:700,
			modal: true,
                        autoOpen: false,
                        title:'Права',
                        resize: function(event, ui) 
                        {
                         if (isEnvGridCreated)
                             {
                                jQuery("#enviroment_table").jqGrid('setGridWidth',$("#dialog-enviroment").innerWidth()-9);
                                jQuery("#enviroment_table").jqGrid('setGridHeight',$("#dialog-enviroment").innerHeight()-60);
                             }
                        },
			buttons: {
				"Ok": function() {

                                    if (r_edit==0){alert("Немає прав!");return;}
                                    
                                    if ((selICol!=0)&&(selIRow!=0))
                                    {
                                        jQuery('#enviroment_table').editCell(selIRow,selICol, false); 
                                    }
    
                                     var data_obj = $('#enviroment_table').getChangedCells('all');
                                     var json_str = JSON.stringify(data_obj);
                                     //alert(json);
                                     $.ajaxSetup({type: "POST",   dataType: "json"});
    
                                     var request = $.ajax({
                                     url: "user_list_enviroment_edit.php",
                                     type: "POST",
                                     data: {
                                       //id_pack : id_pack,
                                       json_data : json_str  
                                     },
                                     dataType: "json"
                                     });

                                     request.done(function(data ) {
                                            if (data.errcode!==undefined)
                                            {
                                                $('#message_zone').append(data.errstr);  
                                                $('#message_zone').append("<br>");                 
                                            }
                                            $(".mod_column_class").removeClass("mod_column_class");
                                            jQuery("#dialog-enviroment").dialog( "close" );

                                     });
                                     
                                     request.fail(function(data ) {
                                        if (data.errcode!==undefined)
                                        {
                                            $('#message_zone').append(data.errstr);  
                                            $('#message_zone').append("<br>");                 
                                        }
                                        else
                                            $('#message_zone').append(data);  
            
                                     });
					
				},
				"Відмінити": function() {
                                        $(".mod_column_class").removeClass("mod_column_class");
					jQuery("#dialog-enviroment").dialog( "close" );
				}
			}
		});
    
       jQuery("#dialog-enviroment").dialog('open');
       jQuery("#enviroment_table").jqGrid('setGridWidth',$("#dialog-enviroment").innerWidth()-9);
       jQuery("#enviroment_table").jqGrid('setGridHeight',$("#dialog-enviroment").innerHeight()-60);
    
    
}


function ChangePasswdDlg(id_user)
{
      $("#dialog_setpasswd").find("#fpasswd1").attr('value','');
      $("#dialog_setpasswd").find("#fpasswd2").attr('value','');
    
      $("#dialog_setpasswd").dialog({
			resizable: true,
			height:140,
                        width:450,
			modal: true,
                        autoOpen: false,
                        title:'Зміна пароля',
			buttons: {
				"Ok": function() {
                                    
                                    pass1 = $("#dialog_setpasswd").find("#fpasswd1").val();
                                    pass2 = $("#dialog_setpasswd").find("#fpasswd2").val();
                                    
                                    if (pass1!=pass2 )
                                    {
                                        $("#dialog_setpasswd").find("#error_zone").html('Пароль не співпадає!');
                                        return;
                                    }
    
                                     $.ajaxSetup({type: "POST",   dataType: "json"});
    
                                     var request = $.ajax({
                                     url: "user_list_passwd_edit.php",
                                     type: "POST",
                                     data: {
                                       id_usr : cur_person,
                                       passwd : pass1  
                                     },
                                     dataType: "json"
                                     });

                                     request.done(function(data ) {
                                            if (data.errcode!==undefined)
                                            {
                                                $('#message_zone').append(data.errstr);  
                                                $('#message_zone').append("<br>");                 
                                            }
                                            jQuery("#dialog_setpasswd").dialog( "close" );

                                     });
                                     
                                     request.fail(function(data ) {
                                        if (data.errcode!==undefined)
                                        {
                                            $('#message_zone').append(data.errstr);  
                                            $('#message_zone').append("<br>");                 
                                        }
                                        else
                                            $('#message_zone').append(data);  
            
                                     });
					
				},
				"Відмінити": function() {
					jQuery("#dialog_setpasswd").dialog( "close" );
				}
			}
		});
    
       jQuery("#dialog_setpasswd").dialog('open');
    
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
function DepartmentFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_editgrpform").dialog('close');                           
               jQuery('#group_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {

               jQuery("#dialog_editgrpform").dialog('close');                                            
               jQuery('#group_table').trigger('reloadGrid');        
               
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

function SelectPersonExternal(id, name) {
    
        $("#fPersonEdit").find("#fid_person").attr('value',id );
        $("#fPersonEdit").find("#frepresent_name").attr('value',name );        
}

