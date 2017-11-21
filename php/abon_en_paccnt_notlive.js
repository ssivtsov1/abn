var validator_notlive = null;
var notlive_form_options;
var notlive_list_mode;
var cur_notlive_id = null;
var fNotliveParam_ajaxForm;

jQuery(function(){ 
/*    
  setTimeout(function(){
      if (mode == 0)
        {
         jQuery('#paccnt_meter_zones_table').trigger('reloadGrid');              
        } 
  },300);  
*/  
  if($(window).height()<700)
      gred_height = 50;
  else
      gred_height = 100;

  //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\  
  jQuery('#paccnt_notlive_table').jqGrid({
    url:     'abon_en_paccnt_notlive_data.php',
    editurl: 'abon_en_paccnt_notlive_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:gred_height,
    width:AllGridWidth,
   // autowidth: true,
    scroll: 0,
    colNames:[], 
    colModel :[  
      {label:'id',name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {label:'id_paccnt',name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center', hidden:true},           
            
      {label:'Номер док.',name:'num_doc', index:'num_doc', width:100, editable: false, align:'center', hidden:false},                       
      {label:'Дата док.',name:'date_doc', index:'date_doc', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Початок непрож.',name:'dt_b', index:'dt_b', width:100, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Закінчення непрож.',name:'dt_e', index:'dt_e', width:100, editable: true, align:'left',edittype:'text',formatter:'date'},      
      {label:'Прим.',name:'comment', index:'comment', width:100, editable: true, align:'left',edittype:'text'},           
      
      {label:'dt',name:'dt_input', index:'dt_input', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{ srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i' }}
    ],
    pager: '#paccnt_notlive_tablePager',
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
      cur_notlive_id = id;  
      
      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
          validator_notlive.resetForm();  //для сброса состояния валидатора
          $("#fNotliveParam").resetForm();
          $("#fNotliveParam").clearForm();
          
          jQuery(this).jqGrid('GridToForm',gsr,"#fNotliveParam"); 
          $("#fNotliveParam").find("#foper").attr('value','edit');    
          CommitJQFormVal($("#fNotliveParam"));

          $("#fNotliveParam").find("#bt_add").hide();
          if (r_edit==3)
            $("#fNotliveParam").find("#bt_edit").show();   
          else
            $("#fNotliveParam").find("#bt_edit").hide();                 
      }
      
    },
        
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');},
  
  gridComplete:function(){

    notlive_list_mode =0; //edit   
    if ($(this).getDataIDs().length > 0) 
    {      
     
     $("#pNotliveParam").show();        
     
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);

    }
    else
    {
        $("#pNotliveParam").hide();        
    }
    
  }

  }).navGrid('#paccnt_notlive_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  //---------------------------------------------------------------------
  jQuery("#pNotliveParam :input").addClass("ui-widget-content ui-corner-all");
  
  notlive_form_options = { 
    dataType:"json",
    beforeSubmit: NotliveBeforeSubmit, // функция, вызываемая перед передачей 
    success: NotliveSubmitResponse // функция, вызываемая при получении ответа
  };

fNotliveParam_ajaxForm = $("#fNotliveParam").ajaxForm(notlive_form_options);
  

jQuery("#paccnt_notlive_table").jqGrid('navButtonAdd','#paccnt_notlive_tablePager',{caption:"Нова заява",
        id:"bt_notlive_new",
	onClickButton:function(){ 

          $("#pNotliveParam").show();        
          
          validator_notlive.resetForm();
          $("#fNotliveParam").resetForm();
          $("#fNotliveParam").clearForm();
          
          $("#fNotliveParam").find("[data_old_value]").attr('value',''); 
          $("#fNotliveParam").find("[data_old_value]").attr('data_old_value',''); 

          $("#fNotliveParam").find("#fid").attr('value',-1 );    
          $("#fNotliveParam").find("#fid_paccnt").attr('value',id_paccnt );  
          $("#fNotliveParam").find("#foper").attr('value','add');              
          
          $("#fNotliveParam").find("#bt_add").show();
          $("#fNotliveParam").find("#bt_edit").hide(); 
          
          $("#lui_paccnt_notlive_table" ).show(); // disable grid

          notlive_list_mode =1; //insert   
          
        ;} 
});

jQuery("#paccnt_notlive_table").jqGrid('navButtonAdd','#paccnt_notlive_tablePager',{caption:"Видалити",
        id:"bt_notlive_del",
	onClickButton:function(){ 

      if ($("#paccnt_notlive_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                        fNotliveParam_ajaxForm[0].oper.value = 'del';
                                        fNotliveParam_ajaxForm.ajaxSubmit(notlive_form_options);       

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

jQuery("#paccnt_notlive_table").jqGrid('navButtonAdd','#paccnt_notlive_tablePager',{caption:"Велика таблиця",
        id:"btn_notlive_fullscreen",
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


if (r_edit!=3)
{
    $('#bt_notlive_del').addClass('ui-state-disabled');
    $('#bt_notlive_new').addClass('ui-state-disabled');
}

$("#pNotliveParam").find("#fid_meter").change( function() 
{
    var mn = $("#pNotliveParam").find("#fid_meter :selected").text();
    $("#pNotliveParam").find("#fnum_meter").attr('value',mn );
});

//-------------------------------------------------------------
// опции валидатора 
var notlive_valid_options = { 

		rules: {
			date_doc: "required",
                        dt_b: "required",
                        dt_e: "required"
		},
		messages: {
			date_doc: "Вкажіть дату!",
                        dt_b: "Вкажіть дату початку",
                        dt_e: "Вкажіть дату закінчення"
		}
};

validator_notlive = $("#fNotliveParam").validate(notlive_valid_options);


//-------------------------------------------------------------
$("#pNotliveParam").find("#bt_reset").click( function() 
{
    if (notlive_list_mode==0 )
    {
     validator_notlive.resetForm();
     ResetJQFormVal($("#fNotliveParam"));
    } 

    if (notlive_list_mode==1 )
    {
     
        $("#lui_paccnt_notlive_table" ).hide();
        //meterLayout.open('east');
        notlive_list_mode =0; //edit    
        
        if ($("#paccnt_notlive_table").getDataIDs().length > 0) 
        {      
     
             var first_id = parseInt($("#paccnt_notlive_table").getDataIDs()[0]);
            $("#paccnt_notlive_table").setSelection(first_id, true);

        }
        else
        {
            $("#pNotliveParam").hide();        
        }
    }
  
});
//------------------------------------------------------------

// обработчик, который вызываетя перед отправкой формы
function NotliveBeforeSubmit(formData, jqForm, options) { 

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
        form_edit_lock=1;   
        if (btn=='edit')
            {
                return true; 
           
            }
            else
                {return true;}

       }
    }
    else {return true;}       
    //}
    
} ;

// обработчик ответа сервера после отправки формы
function NotliveSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {  // insert/delete  ok
                 
               //jQuery("#dialog_editform").dialog('close');                           
               $('#paccnt_notlive_table').trigger('reloadGrid');     
               
               if (notlive_list_mode==1 )
                {
                    $("#lui_paccnt_notlive_table" ).hide();
                    
                    //meterLayout.open('east');
                    notlive_list_mode =0; //edit    
        
                }
                var first_id = parseInt($("#paccnt_notlive_table").getDataIDs()[0]);
                $("#paccnt_notlive_table").setSelection(first_id, true);

               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
               
               var fid = $("#fNotliveParam").find("#fid").val();
               if(fid) 
               { 
                 jQuery("#paccnt_notlive_table").jqGrid('FormToGrid',fid,"#fNotliveParam"); 
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



