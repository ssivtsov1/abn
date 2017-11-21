var validator_plomb = null;
var plomb_form_options;
var plomb_list_mode;
var cur_plomb_id = null;
var fPlombParam_ajaxForm;
var on_progress=0;

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
  jQuery('#paccnt_plomb_table').jqGrid({
    url:     'abon_en_paccnt_plomb_data.php',
    editurl: 'abon_en_paccnt_plomb_edit.php',
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
            
      {label:'№ пломби',name:'plomb_num', index:'plomb_num', width:100, editable: false, align:'center', hidden:false},                       
      {label:'Тип',name:'id_type', index:'id_type', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lplombtype},stype:'text'},                       
      {label:'Приналежність',name:'id_plomb_owner', index:'id_plomb_owner', width:120, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lplombowner},stype:'text', hidden:true},
      {label:'Місце встан.',name:'id_place', index:'id_place', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lplombplace},stype:'text'},
      {label:'№ ліч.',name:'num_meter', index:'num_meter', width:80, editable: true, align:'left',edittype:'text'},

      {label:'id_meter.',name:'id_meter', index:'id_meter', width:30, editable: true, align:'left',edittype:'text', hidden:true},           

      {label:'Дата встан.',name:'dt_on', index:'dt_on', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'id_person_on',name:'id_person_on', index:'id_person_on', width:300, editable: true, align:'left',edittype:'text', hidden:true},           
      {label:'Особа, що встановила',name:'person_on', index:'person_on', width:300, editable: true, align:'left',edittype:'text', hidden:true},                 
      {label:'Дата зняття',name:'dt_off', index:'dt_off', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},      
      {label:'id_person_off',name:'id_person_off', index:'id_person_off', width:300, editable: true, align:'left',edittype:'text', hidden:true},                 
      {label:'Особа, що зняла',name:'person_off', index:'person_off', width:300, editable: true, align:'left',edittype:'text', hidden:true},                       
      
      {label:'Прим.',name:'comment', index:'comment', width:100, editable: true, align:'left',edittype:'text'},           
      {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text'},
      {label:'dt',name:'dt_input', index:'dt_input', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{ srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i' }}
    ],
    pager: '#paccnt_plomb_tablePager',
    rowNum:100,
    sortname: 'dt_on',
    sortorder: 'asc',
    viewrecords: true,
    pgbuttons: false,
    pgtext: null, 
    gridview: true,
    caption: '',
    hidegrid: false,
    postData:{'p_id': id_paccnt, 'arch_mode':1},
    jsonReader : {repeatitems: false},
 
    onSelectRow: function(id) { 
      cur_plomb_id = id;  
      
      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
          validator_plomb.resetForm();  //для сброса состояния валидатора
          $("#fPlombParam").resetForm();
          $("#fPlombParam").clearForm();
          
          jQuery(this).jqGrid('GridToForm',gsr,"#fPlombParam"); 
          $("#fPlombParam").find("#foper").attr('value','edit');    
          CommitJQFormVal($("#fPlombParam"));

          $("#fPlombParam").find("#bt_add").hide();
          if (r_plomb_edit==3)
            $("#fPlombParam").find("#bt_edit").show();   
          else          
            $("#fPlombParam").find("#bt_edit").hide();                 
      }
      
    },
        
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');},
  
  gridComplete:function(){

    plomb_list_mode =0; //edit   
    on_progress=0;
    if ($(this).getDataIDs().length > 0) 
    {      
     
     $("#pPlombParam").show();        
     
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);

    }
    else
    {
        $("#pPlombParam").hide();        
    }
    
  }

  }).navGrid('#paccnt_plomb_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 


jQuery('#paccnt_plomb_table').jqGrid('navButtonAdd','#paccnt_plomb_tablePager',{id:'btn_active', 
  caption:'Поточні', title:"Тільки поточні пломби",
	onClickButton:function(){
            if ((on_progress==0)&&(plomb_list_mode==0))
                {
                    on_progress=1;
                    jQuery('#btn_active').addClass('navButton_selected') ;
                    jQuery('#btn_all').removeClass('navButton_selected') ;
                    jQuery('#paccnt_plomb_table').jqGrid('setGridParam',{'postData':{'arch_mode':1}}).trigger('reloadGrid');
                }
        } 
    });        

jQuery('#paccnt_plomb_table').jqGrid('navButtonAdd','#paccnt_plomb_tablePager',{id:'btn_all', 
  caption:'Всі', title:"Всі пломби",
	onClickButton:function(){
            if ((on_progress==0)&&(plomb_list_mode==0))
                {
                    on_progress=1;
                    jQuery('#btn_all').addClass('navButton_selected') ;
                    jQuery('#btn_active').removeClass('navButton_selected') ;

                    jQuery('#paccnt_plomb_table').jqGrid('setGridParam',{'postData':{'arch_mode':0}}).trigger('reloadGrid');
                }
        } 
    });        

jQuery('#btn_active').addClass('navButton_selected') ;    

  //---------------------------------------------------------------------
  jQuery("#pPlombParam :input").addClass("ui-widget-content ui-corner-all");
  
  plomb_form_options = { 
    dataType:"json",
    beforeSubmit: PlombBeforeSubmit, // функция, вызываемая перед передачей 
    success: PlombSubmitResponse // функция, вызываемая при получении ответа
  };

fPlombParam_ajaxForm = $("#fPlombParam").ajaxForm(plomb_form_options);
  
jQuery("#btPlombPersonOnSel").click( function() { 

     createPersonGrid($("#fid_person_on").val());
     person_target_id=$("#fid_person_on")
     person_target_name =  $("#fperson_on")
     person_target_prof = 0;
    
     jQuery("#grid_selperson").css({'left': $("#fperson_on").offset().left+1, 'top': $("#fperson_on").offset().top-200});
     jQuery("#grid_selperson").toggle( );

/*
    SelectPersonTarget='#fid_person_on';
    SelectPersonStrTarget='#fperson_on';

    if ($("#fPlombParam").find("#fid_person_on").val()!='')
        $("#fcntrl_sel_params_id_cntrl").attr('value', $("#fPlombParam").find("#fid_person_on").val() );    
    else
        $("#fcntrl_sel_params_id_cntrl").attr('value', '0' );

     
     var www = window.open("staff_list.php", "cntrl_win", "toolbar=0,width=900,height=600");
     document.cntrl_sel_params.submit();
     www.focus();
    */
});

jQuery("#btPlombPersonOffSel").click( function() { 

     createPersonGrid($("#fid_person_off").val());
     person_target_id=$("#fid_person_off")
     person_target_name =  $("#fperson_off")
     person_target_prof = 0;
    
     jQuery("#grid_selperson").css({'left': $("#fperson_off").offset().left+1, 'top': $("#fperson_off").offset().top-200});
     jQuery("#grid_selperson").toggle( );

/*
    SelectPersonTarget='#fid_person_off';
    SelectPersonStrTarget='#fperson_off';

    if ($("#fPlombParam").find("#fid_person_off").val()!='')
        $("#fcntrl_sel_params_id_cntrl").attr('value', $("#fPlombParam").find("#fid_person_off").val() );    
    else
        $("#fcntrl_sel_params_id_cntrl").attr('value', '0' );

     var www = window.open("staff_list.php", "cntrl_win", "toolbar=0,width=900,height=600");
     document.cntrl_sel_params.submit();
     www.focus();
*/    
});



jQuery("#paccnt_plomb_table").jqGrid('navButtonAdd','#paccnt_plomb_tablePager',{caption:"Нова пломба",
    id:"bt_plomb_new",
	onClickButton:function(){ 

          $("#pPlombParam").show();        
          
          validator_plomb.resetForm();
          $("#fPlombParam").resetForm();
          $("#fPlombParam").clearForm();
          
          $("#fPlombParam").find("[data_old_value]").attr('value',''); 
          $("#fPlombParam").find("[data_old_value]").attr('data_old_value',''); 

          $("#fPlombParam").find("#fid").attr('value',-1 );    
          $("#fPlombParam").find("#fid_paccnt").attr('value',id_paccnt );  
          $("#fPlombParam").find("#foper").attr('value','add');              
          
          $("#fPlombParam").find("#bt_add").show();
          $("#fPlombParam").find("#bt_edit").hide(); 
          
          $("#lui_paccnt_plomb_table" ).show(); // disable grid

          $("#fPlombParam").find("#fid_meter").val($("#fMeterParam").find("#fid").val());
          $("#fPlombParam").find("#fid_meter").change();

          $("#fPlombParam").find("#fplomb_num").focus();
          

/*          
 *          
          $("#fPlombParam").find("fid_meter").find('option').each(function( i, opt ) {
            if( opt.value != 'null' ) 
                $(opt).attr('selected', 'selected');
          });
*/
          plomb_list_mode =1; //insert   
          
        ;} 
});

jQuery("#paccnt_plomb_table").jqGrid('navButtonAdd','#paccnt_plomb_tablePager',{caption:"Видалити",
    id:"bt_plomb_del",
	onClickButton:function(){ 

      if ($("#paccnt_plomb_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити пломбу?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                        fPlombParam_ajaxForm[0].oper.value = 'del';
                                        fPlombParam_ajaxForm.ajaxSubmit(plomb_form_options);       

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

jQuery("#paccnt_plomb_table").jqGrid('navButtonAdd','#paccnt_plomb_tablePager',{caption:"Велика таблиця",
        id:"btn_plomb_fullscreen",
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


if (r_plomb_edit!=3)
{
    $('#bt_plomb_del').addClass('ui-state-disabled');
    $('#bt_plomb_new').addClass('ui-state-disabled');
}
$("#pPlombParam").find("#fid_meter").change( function() 
{
    var mn = $("#pPlombParam").find("#fid_meter :selected").text();
    $("#pPlombParam").find("#fnum_meter").attr('value',mn );
});

//-------------------------------------------------------------
// опции валидатора 
var plomb_valid_options = { 

		rules: {
			plomb_num: "required",
                        dt_on: "required"
		},
		messages: {
			plomb_num: "Вкажіть номер!",
                        dt_on: "Вкажіть дату встановлення"
		}
};

validator_plomb = $("#fPlombParam").validate(plomb_valid_options);


//-------------------------------------------------------------
$("#pPlombParam").find("#bt_reset").click( function() 
{
    if (plomb_list_mode==0 )
    {
     validator_plomb.resetForm();
     ResetJQFormVal($("#fPlombParam"));
    } 

    if (plomb_list_mode==1 )
    {
     
        $("#lui_paccnt_plomb_table" ).hide();
        //meterLayout.open('east');
        plomb_list_mode =0; //edit    
        
        if ($("#paccnt_plomb_table").getDataIDs().length > 0) 
        {      
     
             var first_id = parseInt($("#paccnt_plomb_table").getDataIDs()[0]);
            $("#paccnt_plomb_table").setSelection(first_id, true);

        }
        else
        {
            $("#pPlombParam").hide();        
        }
    }
  
});
//------------------------------------------------------------
/*
   jQuery("#btFamily").click( function() { 
       
    $("#fPlombParam").attr('target',"plombfamily_win" );           
    $("#fPlombParam").attr('action',"abon_en_plombfamily.php" );               
    
     var ww = window.open("abon_en_plombfamily.php", "plombfamily_win", "toolbar=0,width=800,height=600");
     document.fPlombParam.submit();
     ww.focus();
     
    $("#fPlombParam").attr('target',"" );           
    $("#fPlombParam").attr('action',"abon_en_paccnt_plomb_edit.php" );               
     
   });
*/
//------------------------------------------------------------

// обработчик, который вызываетя перед отправкой формы
function PlombBeforeSubmit(formData, jqForm, options) { 

    submit_form = jqForm;
    if (form_edit_lock == 1) return false;
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
function PlombSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {  // insert/delete  ok
                 
               //jQuery("#dialog_editform").dialog('close');                           
               $('#paccnt_plomb_table').trigger('reloadGrid');     
               
               if (plomb_list_mode==1 )
                {
                    $("#lui_paccnt_plomb_table" ).hide();
                    
                    //meterLayout.open('east');
                    plomb_list_mode =0; //edit    
        
                }
                var first_id = parseInt($("#paccnt_plomb_table").getDataIDs()[0]);
                $("#paccnt_plomb_table").setSelection(first_id, true);

               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
               
               var fid = $("#fPlombParam").find("#fid").val();
               if(fid) 
               { 
                 jQuery("#paccnt_plomb_table").jqGrid('FormToGrid',fid,"#fPlombParam"); 
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



