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



  jQuery('#lgt_table').jqGrid({
    url:'abon_en_lgt_manual_data.php',
    editurl: 'abon_en_lgt_manual_edit.php',
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

    {label:'Книга',name:'book', index:'book', width:30, editable: true, align:'left',edittype:'text'},           
    {label:'Особ.рах.',name:'code', index:'code', width:30, editable: true, align:'left',edittype:'text'},                 
    {label:'Абонент',name:'abon', index:'abon', width:200, editable: true, align:'left',edittype:'text'},
    {label:'Місто',name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text', hidden:town_hidden},
    {label:'Адреса',name:'addr', index:'addr', width:200, editable: true, align:'left',edittype:'text'},           

    {label:'Період рахунку',name:'mmgg_bill', index:'mmgg_bill', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},


    {label:'Операція',name:'id_action', index:'id_action', width:50, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lactions},stype:'select'},

    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:false},
    {label:'Місяць',name:'mmgg', index:'mmgg', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
    {label:'dt',name:'dt', index:'dt', width:100, editable: false, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}

    ],
    pager: '#lgt_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'book',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Керування пільгами',
    //hiddengrid: false,
    jsonReader : {repeatitems: false},
    hidegrid: false,
    postData:{'p_mmgg': mmgg},

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
          $("#flgtEdit").resetForm();
          $("#flgtEdit").clearForm();
          $("#flgtEdit").find("#foper").attr('value','edit');              
          jQuery(this).jqGrid('GridToForm',gsr,"#flgtEdit"); 
      
          $("#flgtEdit").find("#bt_add").hide();
          $("#flgtEdit").find("#bt_edit").show();   
          jQuery("#dialog_editform").dialog('open');          
     
          if (r_edit==3)
          {
             $("#flgtEdit").find("#bt_edit").prop('disabled', false);
          }
          else
          {
             $("#flgtEdit").find("#bt_edit").prop('disabled', true);
          }
     
        
      } else { alert("Please select Row") }       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#lgt_tablePager',
       {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  jQuery("#lgt_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
  //    jQuery(this).editGridRow(id,TableEditOptions);
  }} );


  jQuery("#lgt_table").jqGrid('filterToolbar','');
  jQuery("#lgt_tablePager_right").css("width","150px");


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
            
            $("#lgt_table").jqGrid('setGridWidth',$pane.innerWidth()-10);
            $("#lgt_table").jqGrid('setGridHeight',$pane.innerHeight()-130);

        }
        
	});

        
    outerLayout.resizeAll();
    outerLayout.close('south');     
   // innerLayout.hide('north');        
        
    jQuery(".btn").button();
    jQuery(".btnSel").button({ icons: {primary:'ui-icon-folder-open'} });
    jQuery(".btnRefresh").button({icons: {primary:'ui-icon-refresh'}});
    
   jQuery("#flgtEdit :input").addClass("ui-widget-content ui-corner-all");
   
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open'); });
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   $("#message_zone").dialog({autoOpen: false});
   
 //----------------------------------------------------------------  
jQuery("#lgt_table").jqGrid('navButtonAdd','#lgt_tablePager',{caption:"Відкрити",
        onClickButton:function(){
      var gsr = jQuery("#lgt_table").jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
          validator.resetForm();  //для сброса состояния валидатора
          $("#flgtEdit").resetForm();
          $("#flgtEdit").clearForm();
          $("#flgtEdit").find("#foper").attr('value','edit');              
          jQuery(this).jqGrid('GridToForm',gsr,"#flgtEdit"); 
      
          $("#flgtEdit").find("#bt_add").hide();
          $("#flgtEdit").find("#bt_edit").show();   
          jQuery("#dialog_editform").dialog('open');          
      } 
    }
});

jQuery("#lgt_table").jqGrid('navButtonAdd','#lgt_tablePager',{caption:"Новий",
        id:"btn_lgt_new",
	onClickButton:function(){ 

          validator.resetForm();
          $("#flgtEdit").resetForm();
          $("#flgtEdit").clearForm();
          
          edit_row_id = -1;
          $("#flgtEdit").find("#fid").attr('value',-1 );    
          $("#flgtEdit").find("#fid_paccnt").attr('value', 0);
          $("#flgtEdit").find("#foper").attr('value','add');              
          $("#flgtEdit").find("#bt_add").show();
          $("#flgtEdit").find("#bt_edit").hide();            
          jQuery("#dialog_editform").dialog('open');          
          
        ;} 
});

//-----------------------------------------
jQuery("#lgt_table").jqGrid('navButtonAdd','#lgt_tablePager',{caption:"Видалити",
        id:"btn_lgt_del",
	onClickButton:function(){ 

      if ($("#lgt_table").getDataIDs().length == 0) 
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
                                                      
                                          flgtEdit_ajaxForm[0].id.value = edit_row_id;
                                          //flgtEdit_ajaxForm[0].change_date.value = cur_dt_change;
                                          flgtEdit_ajaxForm[0].oper.value = 'del';
                                          flgtEdit_ajaxForm.ajaxSubmit(form_options);   

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
  $('#btn_lgt_del').addClass('ui-state-disabled');
  $('#btn_lgt_new').addClass('ui-state-disabled');
  $('#btn_lgt_edit').addClass('ui-state-disabled');
  
}

//---------------------------------------------------------------------------
 jQuery("#lgt_table").jqGrid('navButtonAdd','#lgt_tablePager',{caption:"Друкувати список",
    onClickButton:function(){ 

        var postData = jQuery("#lgt_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       $('#freps_params').find("#fdt_b").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#fperiod_str").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#foper").attr('value', "lgt_manual_list");
       $('#freps_params').find("#ftemplate_name").attr('value', "lgt_manual_list");

       
       
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
                        width:700,
			modal: true,
                        autoOpen: false,
                        title:'Операція'
});


flgtEdit_ajaxForm = $("#flgtEdit").ajaxForm(form_options);

var form_valid_options = { 

		rules: {
			abon: "required",
			id_action: "required",
                        mmgg_bill: "required"
		},
		messages: {
			abon: "Вкажіть абонента!",
			id_action: "Вкажіть операцію!",
                        mmgg_bill: "Вкажіть місяць"
		}
};

validator = $("#flgtEdit").validate(form_valid_options);


$("#flgtEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});

    
$("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       
       $('#lgt_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
       
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

  $('#flgtEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#flgtEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 

/* 
   $("#show_peoples").click( function() {
     jQuery("#lgt_table").jqGrid('showCol',["user_name"]);
  });
*/

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
               $('#lgt_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               var fid = jQuery("#fid").val();
               if(fid) 
               { 
                 jQuery("#lgt_table").jqGrid('FormToGrid',fid,"#flgtEdit"); 
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
