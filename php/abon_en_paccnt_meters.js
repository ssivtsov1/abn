//var edit_row_id = 0;
var validator_meter = null;
var meters_form_options;
var meter_list_mode;
var cur_meter_id = null;
var cur_meter_zone_id = null;
var meter_hist_visible =0;
var isMeterHistGridCreated =false;
var fullscreen_mode = 0;
var met_hist_edit = false;
var fMeterParam_ajaxForm;
jQuery(function(){ 
    
  if($(window).height()<700)
      gred_height = 75;
  else
      gred_height = 100;
  //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\  
  jQuery('#paccnt_meters_table').jqGrid({
    url:     'abon_en_paccnt_meters_data.php',
    editurl: 'abon_en_paccnt_meters_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:gred_height,
    width:800,
    autowidth: true,
    scroll: 0,
    colNames:[], 
    colModel :[  
      {label:'id',name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {label:'accnt',name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center', hidden:true},           
      {label:'id_station',name:'id_station', index:'id_station', width:40, editable: false, align:'center', hidden:true},                 
      {label:'id_extra',name:'id_extra', index:'id_extra', width:40, editable: false, align:'center', hidden:true},                 
      {label:'id_type_meter',name:'id_type_meter', index:'id_type_meter', width:40, editable: false, align:'center', hidden:true},                       
      {label:'id_typecompa',name:'id_typecompa', index:'idtype_compa', width:40, editable: false, align:'center', hidden:true},                             
      {label:'id_typecompu',name:'id_typecompu', index:'idtype_compu', width:40, editable: false, align:'center', hidden:true},                                   
      {label:'code_eqp',name:'code_eqp', index:'code_eqp', width:40, editable: false, align:'center', hidden:true},                       

      {label:'Номер',name:'num_meter', index:'num_meter', width:100, editable: true, align:'left',edittype:'text'},           

      {label:'Тип',name:'type_meter', index:'type_meter', width:150, editable: true, align:'left',edittype:'text'},           
      {label:'Фаз',name:'phase_meter', index:'phase_meter', width:30, editable: true, align:'right',hidden:false,
                           edittype:'text',formatter:'integer'},           
      {label:'Розрядів',name:'carry', index:'carry', width:50, editable: true, align:'right',hidden:false,
                           edittype:'text',formatter:'integer'},           
      
      {label:'Дата повірки ліч.',name:'dt_control', index:'dt_control', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Дата повірки тр. струму',name:'dt_control_ca', index:'dt_control_ca', width:80, editable: true, align:'left',edittype:'text',formatter:'date', hidden:true},
      {label:'Дата повірки тр. напр.',name:'dt_control_cu', index:'dt_control_cu', width:80, editable: true, align:'left',edittype:'text',formatter:'date', hidden:true},

      {label:'Потужність',name:'power', index:'power', width:80, align:'right',hidden:false, edittype:'text',formatter:'number'},           

      {label:'Тр.струму',name:'typecompa', index:'typecompa', width:200, editable: true, align:'left',edittype:'text', hidden:true},           
      {label:'Тр.напруги',name:'typecompu', index:'typecompu', width:200, editable: true, align:'left',edittype:'text', hidden:true},           
      {label:'К.тр',name:'coef_comp', index:'coef_comp', width:50, editable: true, align:'right',hidden:false,
                           edittype:'text',formatter:'integer', hidden:true},           
      
      {label:'ТП',name:'station', index:'station', width:100, editable: true, align:'left',edittype:'text'},                 

      {label:'Втрат.',name:'calc_losts', index:'calc_losts', width:40, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox'},
      {label:'SMART',name:'smart', index:'smart', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox', hidden:true},
      {label:'Індикатор магніта',name:'magnet', index:'magnet', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox', hidden:true},

      {label:'Є показ.',name:'is_indic', index:'is_indic', width:30, editable: true, align:'right'
                            , hidden:true},

      {label:'Дата встан.',name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Дата зняття',name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date',hidden:true},      
      {label:'dt',name:'dt_input', index:'dt_input', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}
      
      
      
    ],
    pager: '#paccnt_meters_tablePager',
    rowNum:100,
    sortname: 'id',
    sortorder: 'asc',
    viewrecords: true,
    pgbuttons: false,
    pgtext: null, 
    gridview: true,
    caption: '',
    hidegrid: false,
    postData:{'p_id': id_paccnt, 'free_only': 0, 'hist_mode': 0},
    jsonReader : {repeatitems: false},
 
    onSelectRow: function(id) { 
      cur_meter_id = id;  
      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
          if (meter_hist_visible==0)
          {
            validator_meter.resetForm();  //для сброса состояния валидатора
            $("#fMeterParam").resetForm();
            $("#fMeterParam").clearForm();
          
            jQuery(this).jqGrid('GridToForm',gsr,"#fMeterParam"); 
            $("#fMeterParam").find("#foper").attr('value','edit');   
            $("#fMeterParam").find("#fcode_meter").html(cur_meter_id);
            $("#fcode_meter_direct").attr('value',cur_meter_id);
          
            if (($("#fMeterParam").find("#fid_typecompa").attr('value')=='')&&
                ($("#fMeterParam").find("#fid_typecompu").attr('value')==''))
                {
                 jQuery("#pMeterParam_comp").hide();
                }
            else
                {
                 jQuery("#pMeterParam_comp").show();
                }
              
            CommitJQFormVal($("#fMeterParam"));

            //$("#fMeterParam").find("#fnum_meter").addClass("readonly");
            //$("#fMeterParam").find("#fnum_meter").css("color","#AAAAAA");
            
            if (jQuery(this).jqGrid('getCell',cur_meter_id,'is_indic')=='1')
            {
               if (r_allmeter_edit!=3)                
                   $("#fMeterParam").find("#fnum_meter").attr('readonly', true);

                //$("#fMeterParam").find("#fnum_meter").prop('disabled', true);
               
               $("#fMeterParam").find("#show_mlist").prop('disabled', true);
               
              // $("#btn_del_meter_ultimate").addClass('ui-state-disabled');
              // $("#btn_del_zone").addClass('ui-state-disabled');
              
              // if (r_allmeter_edit==3)  //временно, пока правят ошибки          
              //      $("#btn_del_meter_ultimate").removeClass('ui-state-disabled');
              // else 
              //      $("#btn_del_meter_ultimate").addClass('ui-state-disabled');
              
            }
            else
            {
               $("#fMeterParam").find("#fnum_meter").prop('disabled', false);
               $("#fMeterParam").find("#show_mlist").prop('disabled', false);
               
               //$("#btn_del_zone").removeClass('ui-state-disabled');
               if (r_allmeter_edit==3)            
                    $("#btn_del_meter_ultimate").removeClass('ui-state-disabled');
               else 
                    $("#btn_del_meter_ultimate").addClass('ui-state-disabled');
            }

            if ((r_allmeter_direct!=3)||(r_allmeter_edit!=3))
                 $("#fMeterParam").find("#btedit_meter_direct").prop('disabled', true);

            $("#fMeterParam").find("#bt_add").hide();
            $("#fMeterParam").find("#bt_edit").show();   
            
            if (r_meter_edit==3)
              $("#fMeterParam").find("#bt_edit").prop('disabled', false);
            else
              $("#fMeterParam").find("#bt_edit").prop('disabled', true);
            
            if (r_allmeter_edit==3)            
            {
               $("#btn_del_zone").removeClass('ui-state-disabled');
               
               $("#bt_meter_new_ex").removeClass('ui-state-disabled');
               $("#btn_new_zone").removeClass('ui-state-disabled');                               
            }
            else
            {
               $("#bt_meter_new_ex").addClass('ui-state-disabled');                
               $("#btn_new_zone").addClass('ui-state-disabled');                
               $("#btn_del_zone").addClass('ui-state-disabled');
               $("#btn_del_meter_ultimate").addClass('ui-state-disabled');
            }
                
            $("#fMeterParam").find("#bt_edit") 
            
            //jQuery('#paccnt_meter_zones_table').jqGrid('setGridParam',{'postData':{'p_id':id}}).trigger('reloadGrid');        
            jQuery('#paccnt_meter_zones_table').jqGrid('setGridParam',{datatype:'json','postData':{'p_id':id}}).trigger('reloadGrid');             
          }
          else
          {
            jQuery('#hist2meter_table').jqGrid('setGridParam',{'postData':{'m_id':id}}).trigger('reloadGrid');        
            jQuery('#paccnt_meter_zones_hist_table').jqGrid('setGridParam',{'postData':{'m_id':id}}).trigger('reloadGrid');        
          }
      }  
    },

        
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');},
  
  gridComplete:function(){

    meter_list_mode =0; //edit   
    if ($(this).getDataIDs().length > 0) 
    {      
     
     $("#pMeterParam").show();        
     
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
     
     var myUserData = $(this).jqGrid('getGridParam', 'userData')
     lmetersselect = myUserData['lmetersselect'];

     $("#pPlombParam").find("#fid_meter").html(lmetersselect);
    
    }
    else
    {
        $("#pMeterParam").hide();        
    }
  }

  }).navGrid('#paccnt_meters_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

 jQuery("#paccnt_meters_tablePager_center").hide();
 jQuery("#paccnt_meters_tablePager_right").hide();


jQuery("#paccnt_meters_table").jqGrid('navButtonAdd','#paccnt_meters_tablePager',{caption:"Історія змін",
	onClickButton:function(){ 

      //if ($("#paccnt_meters_table").getDataIDs().length == 0) {return} ;    
       
       if (meter_hist_visible==0)
           {
               createMeterHistGrid();
               meter_hist_visible=1;
               $("#pMeterEditForm").hide();
               $("#hist2meter_div").show();
               
               $("#paccnt_meter_zones").hide();
               $("#paccnt_meter_zones_hist").show();
               
               $("#paccnt_meters_table").jqGrid('showCol',["dt_e"]); 
               $("#paccnt_meters_table").jqGrid('setGridParam',{'postData':{'p_id': id_paccnt, 'free_only': 0, 'hist_mode': 1}}).trigger('reloadGrid');        
               innerLayout.resizeAll(); 
           }
       else
           {
               meter_hist_visible=0;
               $("#hist2meter_div").hide();
               $("#pMeterEditForm").show();
               $("#paccnt_meter_zones").show();
               $("#paccnt_meter_zones_hist").hide();
               
               $("#paccnt_meters_table").jqGrid('hideCol',["dt_e"]);                
               $("#paccnt_meters_table").jqGrid('setGridParam',{'postData':{'p_id': id_paccnt, 'free_only': 0, 'hist_mode': 0}}).trigger('reloadGrid');                       
               innerLayout.resizeAll(); 
           }
               
          
        ;} 
});

jQuery("#paccnt_meters_table").jqGrid('navButtonAdd','#paccnt_meters_tablePager',{caption:"Встанов.новий",
        id:"bt_meter_new",
	onClickButton:function(){ 

       if ($("#paccnt_meters_table").getDataIDs().length != 0) 
         {
           $("#dialog-confirm").find("#dialog-text").html('Не дозволено заносити більше одного лічильника на особовий рахунок!');
    
           $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Попередження',
			buttons: {
				"Ок": function() {
                                        
					$( this ).dialog( "close" );
				}
			}
		});
    
          jQuery("#dialog-confirm").dialog('open');
          
          
          return
         } ;

       $("#fpaccnt_params").find("#pid_meter").attr('value',0 ); 
       $("#fpaccnt_params").find("#pid_work").attr('value','' );          
       $("#fpaccnt_params").find("#pidk_work").attr('value','1' );      
       $("#fpaccnt_params").find("#pmode").attr('value','0' );                 
       $("#fpaccnt_params").attr("action","meter_work.php");
       $("#fpaccnt_params").attr('target',"_blank" );           
       
       if ($("#paccnt_meters_table").getDataIDs().length == 0) 
           $("#fpaccnt_params").find("#pdate_work").attr('value',$("#fAccEdit").find("#fdt_b").attr('value') ); 
       else
           $("#fpaccnt_params").find("#pdate_work").attr('value','' );            
           
       document.paccnt_params.submit();
          
       ;} 
});

jQuery("#paccnt_meters_table").jqGrid('navButtonAdd','#paccnt_meters_tablePager',{caption:"Демонтувати",
        id:"bt_meter_del",
	onClickButton:function(){ 

       if (meter_hist_visible==1) return;
       
       if ($("#paccnt_meters_table").getDataIDs().length == 0) 
         {return} ;         

       $("#fpaccnt_params").find("#pid_meter").attr('value',cur_meter_id ); 
       $("#fpaccnt_params").find("#pid_work").attr('value','' );          
       $("#fpaccnt_params").find("#pidk_work").attr('value','3' );          
       $("#fpaccnt_params").find("#pmode").attr('value','0' );   
       $("#fpaccnt_params").attr("action","meter_work.php");
       $("#fpaccnt_params").attr('target',"_blank" );           
       document.paccnt_params.submit();
          
       ;} 
});

jQuery("#paccnt_meters_table").jqGrid('navButtonAdd','#paccnt_meters_tablePager',{caption:"Заміна ліч.",
        id:"bt_meter_change",
	onClickButton:function(){ 

       if (meter_hist_visible==1) return;
       if ($("#paccnt_meters_table").getDataIDs().length == 0) 
         {return} ;         

       $("#fpaccnt_params").find("#pid_meter").attr('value',cur_meter_id ); 
       $("#fpaccnt_params").find("#pid_work").attr('value','' );          
       $("#fpaccnt_params").find("#pidk_work").attr('value','2' );          
       $("#fpaccnt_params").find("#pmode").attr('value','0' );                 
       $("#fpaccnt_params").attr("action","meter_work.php");
       $("#fpaccnt_params").attr('target',"_blank" );           
       document.paccnt_params.submit();
          
       ;} 
});

if (r_meter_edit!=3)
{
    $('#bt_meter_new').addClass('ui-state-disabled');
    $('#bt_meter_del').addClass('ui-state-disabled');
    $('#bt_meter_change').addClass('ui-state-disabled');    
}

if (r_allmeter_edit!=3)
{
 met_hist_edit = false;
}
else
{
 met_hist_edit = true;
}



jQuery("#paccnt_meters_table").jqGrid('navButtonAdd','#paccnt_meters_tablePager',{
    caption:"Новий(!) ",
    id:"bt_meter_new_ex",  
    onClickButton:function(){ 


        jQuery("#dialog-confirm").find("#dialog-text").html('Додати лічильник без занесення до журналу робіт? <br/>(використовувати тільки під час початкової звірки!)');
    
        $("#dialog-confirm").dialog({
            resizable: false,
            height:140,
            modal: true,
            autoOpen: false,
            title:'Новий лічильник',
            buttons: {
                "Додати": function() {

                    if (meter_hist_visible==1)
                    {
                        meter_hist_visible=0;
                        $("#hist2meter_div").hide();
                        $("#pMeterEditForm").show();
                        $("#paccnt_meter_zones").show();
                        $("#paccnt_meter_zones_hist").hide();
               
                        $("#paccnt_meters_table").jqGrid('hideCol',["dt_e"]);                
                        //$("#paccnt_meters_table").jqGrid('setGridParam',{'postData':{'p_id': id_paccnt, 'free_only': 0, 'hist_mode': 0}}).trigger('reloadGrid');                       
                        innerLayout.resizeAll(); 
                    }


                    $("#pMeterParam").show();        
          
                    validator_meter.resetForm();
                    $("#fMeterParam").resetForm();
                    $("#fMeterParam").clearForm();
                    //$("#fMeterParam").find(':input').find(':hidden').val('');
          
                    $("#fMeterParam").find("[data_old_value]").attr('value',''); 
                    $("#fMeterParam").find("[data_old_value]").attr('data_old_value',''); 

                    //edit_row_id = -1;
                    $("#fMeterParam").find("#fid").attr('value',-1 );    
                    $("#fMeterParam").find("#fid_paccnt").attr('value',id_paccnt );  
                    $("#fMeterParam").find("#foper").attr('value','add');              
          
                    $("#fMeterParam").find("#fcoef_comp").attr('value','1');              
          
                    $("#fMeterParam").find("#fnum_meter").prop('disabled', false);
                    $("#fMeterParam").find("#show_mlist").prop('disabled', false);
                    $("#btn_del_zone").removeClass('ui-state-disabled');
          
                    $("#fMeterParam").find("#bt_add").show();
                    $("#fMeterParam").find("#bt_edit").hide(); 
          
                    $("#lui_paccnt_meters_table" ).show(); // disable grid
                    meterLayout.close('east');
                    meter_list_mode =1; //insert   
                    //jQuery("#dialog_editform").dialog('open');          


                    $( this ).dialog( "close" );
                },
                "Відмінити": function() {
                    $( this ).dialog( "close" );
                }
            }
        });
    
        jQuery("#dialog-confirm").dialog('open');

    } 
});

jQuery("#paccnt_meters_table").jqGrid('navButtonAdd','#paccnt_meters_tablePager',{caption:"Видалити(!)",
        id:"btn_del_meter_ultimate",
	onClickButton:function(){ 

      if ($("#paccnt_meters_table").getDataIDs().length == 0) 
       {return} ;    
       
      if (meter_hist_visible==1) return; 

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити лічильник без занесення до журналу робіт? <br/>(використовувати тільки під час початкової звірки!)');
    
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
                                                    DeleteMeter();
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

jQuery("#paccnt_meters_table").jqGrid('navButtonAdd','#paccnt_meters_tablePager',{caption:"Велика таблиця",
        id:"btn_meters_fullscreen",
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

//-----------------------Зоны счетчика -----------------
jQuery('#paccnt_meter_zones_table').jqGrid({
    url:'abon_en_paccnt_meters_zone_data.php',
    editurl: 'abon_en_paccnt_meters_zone_edit.php',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:80,
    width:220,
    //autowidth: true,
    //shrinkToFit : false,
    
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {name:'id_meter', index:'id_meter', width:40, editable: false, align:'center', hidden:true},     
      {name:'kind_energy', index:'kind_energy', width:40, editable: false, align:'center', hidden:true},           
      {label:'Зона',name:'id_zone', index:'id_zone', width:120, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text'},                       
      {label:'Дата встан.',name:'dt_b', index:'dt_b', width:100, editable: true, align:'left',edittype:'text',formatter:'date'}
    ],
    pager: '#paccnt_meter_zones_tablePager',
    rowNum:50,
    sortname:  'id_zone',
    sortorder: 'asc',
    //viewrecords: true,
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    viewrecords: false,       
    gridview: true,
 //   caption: 'Зони',
    hidegrid: false,
    postData:{'p_id': 0},    

    gridComplete:function(){
     if ($(this).getDataIDs().length > 0) 
     {      
       var first_id = parseInt($(this).getDataIDs()[0]);
       $(this).setSelection(first_id, true);
     }
    },

    onSelectRow: function(id) { 
      cur_meter_zone_id = id;  
    },

    loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');},
  
    jsonReader : {repeatitems: false}

  }).navGrid('#paccnt_meter_zones_tablePager',
        {edit:false,add:false,del:false,search:false}
        ); 
 
 jQuery("#paccnt_meter_zones_tablePager_center").hide();
 jQuery("#paccnt_meter_zones_tablePager_right").hide();

 jQuery("#paccnt_meter_zones_table").jqGrid('navButtonAdd','#paccnt_meter_zones_tablePager',{caption:"Нова зона",
        id:"btn_new_zone",     
	onClickButton:function(){ 

        //$("#dialog-newmeterzone").find("#fid_zone").attr('value','');
        //$("#dialog-newmeterzone").find("#fid_zone").find('option').attr("selected","") ;
        $("#dialog-newmeterzone").find('#fid_zone option').each(function(i, e)  {e.selected = false});
        $("#dialog-newmeterzone").find("#fdate_install").attr('value','');
        
        $("#dialog-newmeterzone").dialog({ 
            resizable: true,
//            height:170,
            width:350,
            modal: true,
            autoOpen: false,
            buttons: {
                "Ok": function() {
                    if(($("#dialog-newmeterzone").find("#fdate_install").val()!='')&&
                       ($("#dialog-newmeterzone").find("#fid_zone").val()!=''))
                        {
                          //$("#ddlViewBy option:selected").text()
                          AddMeterZone();  
                          $( this ).dialog( "close" );
                        } 
                },
                "Отмена": function() {
                    $( this ).dialog( "close" );
                }
            }/*
            ,focus: function() {
               $(this).on("keyup", function(e) {
               if (e.keyCode === 13) {
                      $(this).parent().find("button:contains('Ok')").trigger("click");
                      return false;
                     }
               })
            }
            */

        });
        jQuery("#dialog-newmeterzone").dialog('open');
          
    } 
});

 jQuery("#paccnt_meter_zones_table").jqGrid('navButtonAdd','#paccnt_meter_zones_tablePager',{caption:"Видалити зону",
       id:"btn_del_zone",
	onClickButton:function(){ 

      if ($("#paccnt_meter_zones_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити зону? <br/> (Використовувати тильки в разі крайньої неоюхідності!)');
    
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
                                                    DeleteMeterZone();
                                                    $( this ).dialog( "close" );
                                                },
                                                "Отмена": function() {
                                                    $( this ).dialog( "close" );
                                                }
                                            }/*
                                            ,focus: function() {
                                                $(this).on("keyup", function(e) {
                                                if (e.keyCode === 13) {
                                                    $(this).parent().find("button:contains('Ok')").trigger("click");
                                                    return false;
                                                    }
                                                })
                                            }*/
                                            

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
        } 
});


//jQuery("#paccnt_meters_table").jqGrid('filterToolbar','');


 meters_form_options = { 
    dataType:"json",
    beforeSubmit: MetersBeforeSubmit, // функция, вызываемая перед передачей 
    success: MetersSubmitResponse // функция, вызываемая при получении ответа
  };

fMeterParam_ajaxForm = $("#fMeterParam").ajaxForm(meters_form_options);


jQuery("#pMeterParam :input").addClass("ui-widget-content ui-corner-all");
jQuery("#dialog-newmeterzone :input").addClass("ui-widget-content ui-corner-all");
jQuery("#dialog-changedate :input").addClass("ui-widget-content ui-corner-all");


// опции валидатора 
var meter_valid_options = { 

		rules: {
			type_meter: "required",
			num_meter: "required",
			carry:{required: true,
                        number:true
                        },
                        dt_b: "required",
                        coef_comp:{required: true,
                                number:true
                        },
                        power: {number:true}
		},
		messages: {
			type_meter: "Виберіть тип лічильника!",
			num_meter: "Вкажіть номер лічильника",
			carry:{required: "Вкажіть розрядність лічильника",
                        number:"Повинно бути число!"
                        },
                        dt_b: "Вкажіть дату встановлення лічильника",
			coef_comp:{required: "Вкажіть к.тр",
                        number:"Повинно бути число!"
                        }, 
                        power: {number:"Повинно бути число!"}
		}
};

validator_meter = $("#fMeterParam").validate(meter_valid_options);


//-------------------------------------------------------------
$("#pMeterParam").find("#bt_reset").click( function() 
{
    if (meter_list_mode==0 )
    {
     validator_meter.resetForm();
     ResetJQFormVal($("#fMeterParam"));
    } 

    if (meter_list_mode==1 )
    {
     
        $("#lui_paccnt_meters_table" ).hide();
        meterLayout.open('east');
        meter_list_mode =0; //edit    
        
        if ($("#paccnt_meters_table").getDataIDs().length > 0) 
        {      
     
             var first_id = parseInt($("#paccnt_meters_table").getDataIDs()[0]);
            $("#paccnt_meters_table").setSelection(first_id, true);

        }
        else
        {
            $("#pMeterParam").hide();        
        }
    }
  
});
//------------------------------------------------------------


jQuery("#show_mlist").click( function() { 

    createMeterGrid(jQuery("#fid_type_meter").val());
    meter_target_id=jQuery("#fid_type_meter");
    meter_target_name = jQuery("#ftype_meter");
    meter_target_carry = jQuery("#fcarry");

    jQuery("#grid_selmeter").css({'left': jQuery("#ftype_meter").position().left+1, 'top': jQuery("#ftype_meter").position().top+20});
    jQuery("#grid_selmeter").toggle( );
    
    jQuery("#grid_selmeter").find("input[type='text']:visible:enabled:first").focus();    
});

jQuery("#show_mlist_direct").click( function() { 

    createMeterGrid(jQuery("#fid_type_meter_direct").val());
    meter_target_id=jQuery("#fid_type_meter_direct");
    meter_target_name = jQuery("#ftype_meter_direct");
    meter_target_carry = jQuery("#fcarry_direct");

    jQuery("#grid_selmeter").css({'left': 200, 'top': 200});
    jQuery("#grid_selmeter").toggle( );
    
    jQuery("#grid_selmeter").find("input[type='text']:visible:enabled:first").focus();    
});

// выбор тр. тока 
jQuery("#show_compa").click( function() {

    compi_target_id=jQuery("#fid_typecompa");
    compi_target_name = jQuery("#ftypecompa");
    compi_target_ktr = jQuery("#fcoef_comp");
    
    createCompIGrid(); 
    jQuery("#grid_selci").css({'left': $(this).position().left+1, 'top': $(this).position().top+20});
    jQuery("#grid_selci").toggle( );
    jQuery("#grid_selci").find("input[type='text']:visible:enabled:first").focus();        
});

//выбор тр. напряжения
jQuery("#show_compu").click( function() {

    compi_target_id=jQuery("#fid_typecompu");
    compi_target_name = jQuery("#ftypecompu");
    
    createCompIGrid(); 
    jQuery("#grid_selci").css({'left': $(this).position().left+1, 'top': $(this).position().top+20});
    jQuery("#grid_selci").toggle( );
    
    jQuery("#grid_selci").find("input[type='text']:visible:enabled:first").focus();    
});

jQuery("#show_tplist").click( function() {

    createTpGrid();
    tp_target_id=jQuery("#fid_station");
    tp_target_name = jQuery("#fstation");

    jQuery("#grid_seltp").css({'left': jQuery("#fstation").position().left+1, 'top': jQuery("#fstation").position().top+20});
    jQuery("#grid_seltp").toggle( );
    
    jQuery("#grid_seltp").find("input[type='text']:visible:enabled:first").focus();
});

jQuery("#toggle_comp").click( function() {
    jQuery("#pMeterParam_comp").toggle( );
});

//----------------таблица истории счетчика -------------

var createMeterHistGrid = function(){ 
    
  if (isMeterHistGridCreated) return;
  isMeterHistGridCreated =true;
  
  jQuery('#hist2meter_table').jqGrid({
    url:'eqp_hist_meter.php',
    editurl: 'eqp_hist_meter_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:80,
    //width:800,
    autowidth: true,
    shrinkToFit : false,
    scroll: 0,
    colModel :[ 
      {name:'id_key', index:'id_key', width:40, editable: false, align:'center', key:true,hidden:true},
      {label:'Код',name:'id', index:'id', width:40, editable: false, align:'center', hidden:false},
      {label:'Номер',name:'num_meter', index:'num_meter', width:100, editable: false, align:'left',edittype:'text'},           

      {label:'id типа',name:'id_type_meter', index:'id_type_meter', width:40, editable: false, align:'center', hidden:true},                       

      {label:'Тип',name:'type_meter', index:'type_meter', width:110, editable: false, align:'left',edittype:'text'},           
      {label:'Розрядів',name:'carry', index:'carry', width:50, editable: false, align:'right',hidden:false,
                           edittype:'text',formatter:'integer'},           
      
      {label:'Дата пов.ліч.',name:'dt_control', index:'dt_control', width:80, editable: false, align:'left',edittype:'text',formatter:'date'},
      {label:'Дата пов.тр.струму',name:'dt_control_ca', index:'dt_control_ca', width:80, editable: false, align:'left',edittype:'text',formatter:'date', hidden:true},
      {label:'Дата пов.тр.напр.',name:'dt_control_cu', index:'dt_control_cu', width:80, editable: false, align:'left',edittype:'text',formatter:'date', hidden:true},

      {label:'Потужність',name:'power', index:'power', width:80, align:'right',editable: false,hidden:false, edittype:'text',formatter:'number'},           

      {label:'id тр.струму',name:'id_typecompa', index:'idtype_compa', width:40, editable: false, align:'center', hidden:true},                             
      {label:'Тр.струму',name:'typecompa', index:'typecompa', width:200, editable: false, align:'left',edittype:'text', hidden:true},
      {label:'id тр. напруги',name:'id_typecompu', index:'idtype_compu', width:40, editable: false, align:'center', hidden:true},                                   
      {label:'Тр.напруги',name:'typecompu', index:'typecompu', width:200, editable: false, align:'left',edittype:'text', hidden:true},
      {label:'К.тр',name:'coef_comp', index:'coef_comp', width:50, editable: false, align:'right',hidden:false,
                           edittype:'text',formatter:'integer', hidden:false},           
      
      {label:'ТП',name:'station', index:'station', width:80, editable: false, align:'left',edittype:'text'},                 

      {label:'Втрат.',name:'calc_losts', index:'calc_losts', width:40, editable: false, align:'right',
                            formatter:'checkbox',edittype:'checkbox',hidden:true},
      {label:'Smart',name:'smart', index:'smart', width:30, editable: false, align:'right',
                            formatter:'checkbox',edittype:'checkbox', hidden:false},
      {label:'Магніт',name:'magnet', index:'magnet', width:30, editable: false, align:'right',
                            formatter:'checkbox',edittype:'checkbox', hidden:true},

      //{label:'Дата встан.',name:'dt_start', index:'dt_start', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      //{label:'Дата демонт.',name:'dt_end', index:'dt_end', width:80, editable: true, align:'left',edittype:'text',formatter:'date'} ,
      
      
      {label:'Дт.нач', name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Дт.кон',name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Мес.созд', name:'period_open',index:'period_open', width:80, editable: false, align:'left',edittype:'text',formatter:'date'},
      {label:'Время созд.', name:'dt_open',index:'dt_open', width:100, editable: false, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {label:'Сотр.созд', name:'user_name_open',index:'user_name_open', width:80, editable: false, align:'left',edittype:'text'},

      {label:'Мес.удал.', name:'period_close',index:'period_close', width:80, editable: false, align:'left',edittype:'text',formatter:'date'},
      {label:'Время удал.', name:'dt_close',index:'dt_close', width:100, editable: false, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {label:'Сотр.удал.', name:'user_name_close',index:'user_name_close', width:80, editable: false, align:'left',edittype:'text'}
      
    ],
    pager: '#hist2meter_tablePager',
    rowNum:50,
    sortname: 'dt_b',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: '',
    pgbuttons: false,
    pgtext: null, 
    hiddengrid: false,
    postData:{'m_id':0},
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#hist2meter_tablePager',
       // {edit:false,add:false,del:false,search:false}
        {edit:met_hist_edit,add:false,del:met_hist_edit,search:false,
            edittext: 'Редагувати',
            deltext: 'Видалити' },
        {width:300,reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterHistEdit},  
        {}, 
        {reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterHistEdit},  
        {} 
        ); 


jQuery('#paccnt_meter_zones_hist_table').jqGrid({
    url:'eqp_hist_meterzones.php',
    editurl: 'eqp_hist_meterzones_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:80,
    //width:220,
    autowidth: true,
    shrinkToFit : false,
    colNames:[],
    colModel :[ 
      {name:'id_key', index:'id_key', width:40, editable: false, align:'center', key:true, hidden:true},             
      {name:'id', index:'id', width:40, editable: false, align:'center',hidden:true},     
      {name:'id_meter', index:'id_meter', width:40, editable: false, align:'center', hidden:true},     
      {name:'kind_energy', index:'kind_energy', width:40, editable: false, align:'center', hidden:true},           
      {label:'Зона',name:'id_zone', index:'id_zone', width:100, editable: false, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text'},                       
      {label:'Дт.нач', name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Дт.кон',name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Мес.созд', name:'period_open',index:'period_open', width:80, editable: false, align:'left',edittype:'text',formatter:'date'},
      {label:'Время созд.', name:'dt_open',index:'dt_open', width:100, editable: false, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {label:'Сотр.созд', name:'user_name_open',index:'user_name_open', width:80, editable: false, align:'left',edittype:'text'},

      {label:'Мес.удал.', name:'period_close',index:'period_close', width:80, editable: false, align:'left',edittype:'text',formatter:'date'},
      {label:'Время удал.', name:'dt_close',index:'dt_close', width:100, editable: false, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {label:'Сотр.удал.', name:'user_name_close',index:'user_name_close', width:80, editable: false, align:'left',edittype:'text'}
    ],
    pager: '#paccnt_meter_zones_hist_tablePager',
    rowNum:50,
    sortname:  'id_zone',
    sortorder: 'asc',
    gridview: true,
    //caption: 'Зони(історія)',
    hidegrid: false,
    pgbuttons: false,
    pgtext: null, 
    postData:{'m_id':0},

    loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');},
  
    jsonReader : {repeatitems: false}

  }).navGrid('#paccnt_meter_zones_hist_tablePager',
        //{edit:false,add:false,del:false,search:false}
        {edit:met_hist_edit,add:false,del:met_hist_edit,search:false,
            edittext: 'Редагувати',
            deltext: 'Видалити' },
        {width:300,reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterHistEdit}, 
        {}, 
        {reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterHistEdit}, 
        {}         
        ); 

};
//---------------------------------------------------------------------------------
jQuery("#btedit_meter_direct").click( function() { 

      jQuery("#dialog-changemeterdirect").css('background-color','red');
      jQuery("#dialog-changemeterdirect").css('color','white');

      $("#dialog-changemeterdirect").dialog({
			resizable: true,
			height:190,
                        width:370,
			modal: true,
                        autoOpen: false,
                        title:'Редагування',
			buttons: {
				"Змінити": function() {

                                    new_type_meter = $('#fid_type_meter_direct').val();
                                    new_num_meter = $('#fnum_meter_direct').val();
                                    new_carry = $('#fcarry_direct').val();

                                    var request = $.ajax({
                                        url: "abon_en_paccnt_meter_direct_edit.php",
                                        type: "POST",
                                        data: {
                                            id: cur_meter_id,
                                            id_type_meter: new_type_meter,
                                            num_meter: new_num_meter,
                                            carry :new_carry 
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
                                                jQuery('#paccnt_meters_table').trigger('reloadGrid');                      
                                            }
                                            else
                                            {
                                                jQuery("#message_zone").dialog('open');                                    
                                            }
                                        }
                                    });

                                    request.fail(function(data ) {
                                        alert("error");
        
                                    });

				    $( this ).dialog( "close" );
				},
				"Відмінити": function() {
					$( this ).dialog( "close" );
				}
			}
		});
    
        jQuery("#dialog-changemeterdirect").dialog('open');      
});


}); 
 
// обработчик, который вызываетя перед отправкой формы
function MetersBeforeSubmit(formData, jqForm, options) { 

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
        if (btn=='edit')
            {
                
               $("#dialog-changedate").dialog({ 
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
			buttons: {
				"Ok": function() {
                                        SaveMeterChanges();
                                        form_edit_lock=1;
					$( this ).dialog( "close" );
				},
				"Отмена": function() {
                                        CancelMeterChanges();
					$( this ).dialog( "close" );
				}
			}
                        /*
                        ,focus: function() {
                            $(this).on("keyup", function(e) {
                            if (e.keyCode === 13) {
                                $(this).parent().find("button:contains('Ok')").trigger("click");
                            return false;
                            }
                        })
                    }
                        
                    */
                });
                
                $("#dialog-changedate").dialog('open');
                return false; 
                
            }
            else
                {
                    form_edit_lock=1;
                    return true;
                }

       }
    }
    else {return true;}       
    //}
    
} ;

// обработчик ответа сервера после отправки формы
function MetersSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {  // insert/delete  ok
                 
               //jQuery("#dialog_editform").dialog('close');                           
               $('#paccnt_meters_table').trigger('reloadGrid');     
               
               if (meter_list_mode==1 )
                {
                    $("#lui_paccnt_meters_table" ).hide();
                    
                    meterLayout.open('east');
                    meter_list_mode =0; //edit    
        
                }
                var first_id = parseInt($("#paccnt_meters_table").getDataIDs()[0]);
                $("#paccnt_meters_table").setSelection(first_id, true);

               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
               
               var fid = $("#fMeterParam").find("#fid").val();
               if(fid) 
               { 
                 jQuery("#paccnt_meters_table").jqGrid('FormToGrid',fid,"#fMeterParam"); 
               }  
               
               //jQuery("#dialog_editform").dialog('close');                                            
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

function SaveMeterChanges()
{
  var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
  
  submit_form[0].change_date.value = cur_dt_change;
  submit_form.ajaxSubmit(meters_form_options);         
    
};

function DeleteMeter()
{
  var cur_dt_change = jQuery("#dialog-changedate").find("#fdate_change").val();
//    $("#fMeterParam").find("#foper").attr('value','del');  
//    $("#fMeterParam").find("#fchange_date").attr('value',');  
  fMeterParam_ajaxForm[0].change_date.value = cur_dt_change;
  fMeterParam_ajaxForm[0].oper.value = 'del';
  fMeterParam_ajaxForm.ajaxSubmit(meters_form_options);         
};

function CancelMeterChanges()
{
//
};

function  AddMeterZone()
{
    var vid_zone = $("#dialog-newmeterzone").find("#fid_zone").val();
    var vdt_start = $("#dialog-newmeterzone").find("#fdate_install").val();
    
    var request = $.ajax({
        url: "abon_en_paccnt_meters_zone_edit.php",
        type: "POST",
        data: {
            oper : 'add',
            id_paccnt : id_paccnt,
            id_meter : cur_meter_id, 
            id_zone : vid_zone,
            dt_start : vdt_start
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
                jQuery('#paccnt_meter_zones_table').trigger('reloadGrid');                      
            }
        }
     });

    request.fail(function(data ) {
        alert("error");
        
    });
    
};

function  DeleteMeterZone()
{
    
    var cur_dt_change = $("#dialog-changedate").find("#fdate_change").val();    
    
    dt_del = Date.parse( cur_dt_change, "dd.MM.yyyy");
    dt_new = Date.parse( $("#paccnt_meter_zones_table").jqGrid('getCell',cur_meter_zone_id,'dt_b'), "dd.MM.yyyy");
    
    if (dt_del<dt_new)
    {
       alert("Дата видалення зони не може бути менше за дату встановлення!");
       return; 
    }
    var request = $.ajax({
        url: "abon_en_paccnt_meters_zone_edit.php",
        type: "POST",
        data: {
            oper : 'del',
            id_paccnt : id_paccnt,   
            id_meter : cur_meter_id,             
            id : cur_meter_zone_id, 
            dt_oper : cur_dt_change
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
                jQuery('#paccnt_meter_zones_table').trigger('reloadGrid');                      
            }
        }
     });

    request.fail(function(data ) {
        alert("error");
        
    });

};
