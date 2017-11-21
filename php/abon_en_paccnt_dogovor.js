var validator_dogovor = null;
var dogovor_form_options;
var dogovor_list_mode;
var cur_dogovor_id = null;
var fDogovorParam_ajaxForm;

jQuery(function(){ 
    
  if($(window).height()<700)
      gred_height = 50;
  else
      gred_height = 100;


  jQuery('#paccnt_dogovor_table').jqGrid({
    url:     'abon_en_paccnt_dogovor_data.php',
    editurl: 'abon_en_paccnt_dogovor_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:gred_height,
    width:AllGridWidth,
    autowidth: true,
    scroll: 0,
    colNames:[], 
    colModel :[  
      {label:'id',name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {label:'id_paccnt',name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center', hidden:true},           
      {label:'Тип',name:'id_iagreem', index:'id_iagreem', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:ldogovortype},stype:'text'},                       
      {label:'Номер',name:'num_agreem', index:'num_agreem', width:80, editable: true, align:'left',edittype:'text'},           
      {label:'Дата док.',name:'date_agreem', index:'date_agreem', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Шифр',name:'shifr', index:'shifr', width:70, editable: true, align:'left',edittype:'text'},                 
      {label:'Потужність',name:'power', index:'power', width:80, align:'right',hidden:false, edittype:'text',formatter:'number'},
      {label:'Категорія',name:'categ', index:'categ', width:70, editable: true, align:'left',edittype:'text'},
      {label:'id_abon',name:'id_abon', index:'id_abon', width:120, editable: true, align:'left',edittype:'text', hidden:true},
      {label:'Договор підписав',name:'agreem_abon', index:'agreem_abon', width:200, editable: true, align:'left',edittype:'text'},
      {label:'id_town',name:'id_town_agreem', index:'id_town_agreem', width:40, editable: true, align:'left',edittype:'text', hidden:true},
      {label:'Місто',name:'town', index:'town', width:120, editable: true, align:'left',edittype:'text'},
      {label:'Дата початку',name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Дата закінч.',name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'dt',name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{ srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i' }}
    ],
    pager: '#paccnt_dogovor_tablePager',
    rowNum:100,
    sortname: 'dt_b',
    sortorder: 'asc',
    viewrecords: true,
    pgbuttons: false,
    pgtext: null, 
    gridview: true,
    caption: '',
    hidegrid: false,
    postData:{'p_id': id_paccnt},
    jsonReader : {repeatitems: false},
 
    onSelectRow: function(id) { 
      cur_dogovor_id = id;  
      
      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 

          validator_dogovor.resetForm();  //для сброса состояния валидатора
          $("#fDogovorParam").resetForm();
          $("#fDogovorParam").clearForm();
          
          jQuery(this).jqGrid('GridToForm',gsr,"#fDogovorParam"); 
          $("#fDogovorParam").find("#foper").attr('value','edit');    
       
          CommitJQFormVal($("#fDogovorParam"));

          $("#fDogovorParam").find("#bt_add").hide();
          if (r_dog_edit==3)
            $("#fDogovorParam").find("#bt_edit").show();   
          else
            $("#fDogovorParam").find("#bt_edit").hide();                 
      }
      
    },

        
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');},
  
  gridComplete:function(){

    dogovor_list_mode =0; //edit   
    if ($(this).getDataIDs().length > 0) 
    {      
     
     $("#pDogovorParam").show();        
     
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);

    }
    else
    {
        $("#pDogovorParam").hide();        
    }
    
  }

  }).navGrid('#paccnt_dogovor_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  //---------------------------------------------------------------------
  jQuery("#pDogovorParam :input").addClass("ui-widget-content ui-corner-all");
  
  dogovor_form_options = { 
    dataType:"json",
    beforeSubmit: DogovorBeforeSubmit, // функция, вызываемая перед передачей 
    success: DogovorSubmitResponse // функция, вызываемая при получении ответа
  };

fDogovorParam_ajaxForm = $("#fDogovorParam").ajaxForm(dogovor_form_options);
  

jQuery("#paccnt_dogovor_table").jqGrid('navButtonAdd','#paccnt_dogovor_tablePager',{caption:"Новий договор",
    id:"bt_dogovor_new",
	onClickButton:function(){ 

          $("#pDogovorParam").show();        
          
          validator_dogovor.resetForm();
          $("#fDogovorParam").resetForm();
          $("#fDogovorParam").clearForm();
          
          $("#fDogovorParam").find("[data_old_value]").attr('value',''); 
          $("#fDogovorParam").find("[data_old_value]").attr('data_old_value',''); 

          $("#fDogovorParam").find("#fid").attr('value',-1 );    
          $("#fDogovorParam").find("#fid_paccnt").attr('value',id_paccnt );  
          
          $("#fDogovorParam").find("#fagreem_abon").attr('value', $("#fAccEdit").find("#fabon").attr('value'));
          $("#fDogovorParam").find("#fid_abon").attr('value', $("#fAccEdit").find("#fid_abon").attr('value'));
          
          $("#fDogovorParam").find("#foper").attr('value','add');              
          
          $("#fDogovorParam").find("#bt_add").show();
          $("#fDogovorParam").find("#bt_edit").hide(); 
          
          $("#lui_paccnt_dogovor_table" ).show(); // disable grid

          dogovor_list_mode =1; //insert   
          
        ;} 
});

jQuery("#paccnt_dogovor_table").jqGrid('navButtonAdd','#paccnt_dogovor_tablePager',{caption:"Видалити",
    id:"bt_dogovor_del",
	onClickButton:function(){ 

      if ($("#paccnt_dogovor_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити договор?');
    
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
                                                    DeleteDogovor();
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

jQuery("#paccnt_dogovor_table").jqGrid('navButtonAdd','#paccnt_dogovor_tablePager',{caption:"Велика таблиця",
        id:"btn_dogovor_fullscreen",
	onClickButton:function(){ 

        if (fullscreen_mode==0)
        {
            jQuery('#btn_meters_fullscreen').addClass('navButton_selected') ;    
            jQuery('#btn_lgt_fullscreen').addClass('navButton_selected') ;    
            jQuery('#btn_dogovor_fullscreen').addClass('navButton_selected') ;    
            jQuery('#btn_plomb_fullscreen').addClass('navButton_selected') ;    
            jQuery('#btn_notlive_fullscreen').addClass('navButton_selected') ;    
            jQuery('#btn_works_fullscreen').addClass('navButton_selected') ;    

            fullscreen_mode=1;
            innerLayout.close('north');     
            $("#paccnt_meters_table").jqGrid('setGridHeight',gred_height+220);      
            $("#paccnt_lgt_table").jqGrid('setGridHeight',gred_height+220);      
            $("#paccnt_dogovor_table").jqGrid('setGridHeight',gred_height+220);      
            $("#paccnt_plomb_table").jqGrid('setGridHeight',gred_height+220);      
            $("#paccnt_notlive_table").jqGrid('setGridHeight',gred_height+220);      
            $("#paccnt_works_table").jqGrid('setGridHeight',gred_height+220);      

        }
        else
        {
            jQuery('#btn_meters_fullscreen').removeClass('navButton_selected') ;   
            jQuery('#btn_lgt_fullscreen').removeClass('navButton_selected') ;    
            jQuery('#btn_dogovor_fullscreen').removeClass('navButton_selected') ;    
            jQuery('#btn_plomb_fullscreen').removeClass('navButton_selected') ;    
            jQuery('#btn_notlive_fullscreen').removeClass('navButton_selected') ;    
            jQuery('#btn_works_fullscreen').removeClass('navButton_selected') ;    

            fullscreen_mode=0;
            $("#paccnt_meters_table").jqGrid('setGridHeight',gred_height);
            
            $("#paccnt_lgt_table").jqGrid('setGridHeight',gred_height);      
            $("#paccnt_dogovor_table").jqGrid('setGridHeight',gred_height);      
            $("#paccnt_plomb_table").jqGrid('setGridHeight',gred_height);      
            $("#paccnt_notlive_table").jqGrid('setGridHeight',gred_height);      
            $("#paccnt_works_table").jqGrid('setGridHeight',gred_height);      
            
            innerLayout.open('north');     

        }
          
        ;} 
});

if (r_dog_edit!=3)
{
    $('#bt_dogovor_new').addClass('ui-state-disabled');
    $('#bt_dogovor_del').addClass('ui-state-disabled');
}

//-------------------------------------------------------------
// опции валидатора 
var dogovor_valid_options = { 

		rules: {
			num_agreem: "required",
                        date_agreem: "required",
                        dt_b: "required",
                        power: {number:true}
		},
		messages: {
			num_agreem: "Вкажіть номер договору",
                        date_agreem: "Вкажіть дату договору",
                        dt_b: "Вкажіть дату початку",
                        power: {number:"Повинно бути число!"}
		}
};

validator_dogovor = $("#fDogovorParam").validate(dogovor_valid_options);


//-------------------------------------------------------------
$("#pDogovorParam").find("#bt_reset").click( function() 
{
    if (dogovor_list_mode==0 )
    {
     validator_dogovor.resetForm();
     ResetJQFormVal($("#fDogovorParam"));
    } 

    if (dogovor_list_mode==1 )
    {
     
        $("#lui_paccnt_dogovor_table" ).hide();
        //meterLayout.open('east');
        dogovor_list_mode =0; //edit    
        
        if ($("#paccnt_dogovor_table").getDataIDs().length > 0) 
        {      
     
             var first_id = parseInt($("#paccnt_dogovor_table").getDataIDs()[0]);
            $("#paccnt_dogovor_table").setSelection(first_id, true);

        }
        else
        {
            $("#pDogovorParam").hide();        
        }
    }
  
});
//------------------------------------------------------------
   jQuery("#btAbonDogSel").click( function() { 
     SelectAbonTarget='fDogovorParam';
     var ww = window.open("dov_abon.php", "abon_win", "toolbar=0,width=800,height=600");
     document.abon_sel_params.submit();
     ww.focus();
   });
//------------------------------------------------------------

// обработчик, который вызываетя перед отправкой формы
function DogovorBeforeSubmit(formData, jqForm, options) { 

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
                                        fDogovorParam_ajaxForm[0].change_date.value = cur_dt_change;
                                        fDogovorParam_ajaxForm[0].oper.value = 'edit';
                                        fDogovorParam_ajaxForm.ajaxSubmit(dogovor_form_options);
                                        form_edit_lock=1;
					$( this ).dialog( "close" );
				},
				"Отмена": function() {
                                         //validator_dogovor.resetForm();
                                         //ResetJQFormVal($("#fDogovorParam"));

					$( this ).dialog( "close" );
				}
			}

                });
                
                $("#dialog-changedate").dialog('open');
                
                //form_edit_lock=1;
                return false; 
                
            }
            else
                {return true;}

       }
    }
    else {return true;}       
    //}
    
} ;

// обработчик ответа сервера после отправки формы
function DogovorSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {  // insert/delete  ok
                 
               //jQuery("#dialog_editform").dialog('close');                           
               $('#paccnt_dogovor_table').trigger('reloadGrid');     
               
               if (dogovor_list_mode==1 )
                {
                    $("#lui_paccnt_dogovor_table" ).hide();
                    
                    //meterLayout.open('east');
                    dogovor_list_mode =0; //edit    
        
                }
                var first_id = parseInt($("#paccnt_dogovor_table").getDataIDs()[0]);
                $("#paccnt_dogovor_table").setSelection(first_id, true);

               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
               
               var fid = $("#fDogovorParam").find("#fid").val();
               if(fid) 
               { 
                 jQuery("#paccnt_dogovor_table").jqGrid('FormToGrid',fid,"#fDogovorParam"); 
               }  
               
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

});


function  DeleteDogovor()
{
  var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
  fDogovorParam_ajaxForm[0].change_date.value = cur_dt_change;
  fDogovorParam_ajaxForm[0].oper.value = 'del';
  fDogovorParam_ajaxForm.ajaxSubmit(dogovor_form_options);       
};



