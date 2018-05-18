var validator_lgt = null;
var lgt_form_options;
var lgt_list_mode;
var cur_lgt_id = null;
var fLgtParam_ajaxForm;
var lgt_hist_visible =0;
var isLgtHistGridCreated = false;
var lgt_hist_edit = false;
jQuery(function(){ 
 
  if($(window).height()<700)
      gred_height = 50;
  else
      gred_height = 100;

  //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\  
  jQuery('#paccnt_lgt_table').jqGrid({
    url:     'abon_en_paccnt_lgt_data.php',
    editurl: 'abon_en_paccnt_lgt_edit.php',
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
      {label:'id_grp_lgt',name:'id_grp_lgt', index:'id_grp_lgt', width:40, editable: false, align:'center', hidden:true},                 
      {label:'id_calc',name:'id_calc', index:'id_calc', width:40, editable: false, align:'center', hidden:true},                       
      
      {label:'Код',name:'ident', index:'ident', width:30, editable: false, align:'center', hidden:false},                       
      {label:'Код РЕС',name:'alt_code', index:'alt_code', width:40, editable: false, align:'center', hidden:false},
      
      {label:'Пільга',name:'grp_lgt', index:'grp_lgt', width:150, editable: false, align:'center', hidden:false},                       
      {label:'Метод розрах.',name:'calc_name', index:'calc_name', width:100, editable: false, align:'center', hidden:true},
      {label:'Реальний розрах.',name:'lgt_calc_info', index:'lgt_calc_info', width:100, editable: false, align:'center', hidden:true},
      {label:"Cім'я",name:'family_cnt', index:'family_cnt', width:50, editable: false, align:'center', hidden:false},                             

      {label:'Приор.',name:'prior_lgt', index:'prior_lgt', width:30, editable: true, align:'left',edittype:'text',hidden:true},           

      {label:'Особа',name:'fio_lgt', index:'fio_lgt', width:300, editable: true, align:'left',edittype:'text',hidden:true},           
      
      {label:'Документ',name:'id_doc', index:'id_doc', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:ldocs},stype:'text',hidden:true},
      
      {label:'Серія',name:'s_doc', index:'s_doc', width:50, editable: true, align:'left',edittype:'text'},      
      {label:'Номер',name:'n_doc', index:'n_doc', width:50, editable: true, align:'left',edittype:'text'},
      
      {label:'Дата док.',name:'dt_doc', index:'dt_doc', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Дійсний до',name:'dt_doc_end', index:'dt_doc_end', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      
      {label:'ІНН',name:'ident_cod_l', index:'ident_cod_l', width:80, editable: true, align:'left',edittype:'text',hidden:true},           
           
      {label:'Дата початку',name:'dt_start', index:'dt_start', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},      
      {label:'Дата перереєстр.',name:'dt_reg', index:'dt_reg', width:80, editable: true, align:'left',edittype:'text',formatter:'date',hidden:false},
      {label:'Дата закінч.',name:'dt_end', index:'dt_end', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Закрито',name:'closed', index:'closed', width:10, editable: true, align:'left',edittype:'text'},      
      {label:'Період',name:'work_period', index:'work_period', width:80, editable: true, align:'left',edittype:'text',formatter:'date',hidden:false},
      {label:'dt',name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {label:'Прим',name:'note', index:'note', width:50, editable: true, align:'left',edittype:'text',hidden:true},
      {label:'Останнє редаг.',name:'edit_info', index:'edit_info', width:100, editable: false, align:'center', hidden:true}      
    ],
    pager: '#paccnt_lgt_tablePager',
    rowNum:100,
    sortname: 'dt_end',
    sortorder: 'asc',
    viewrecords: true,
    pgbuttons: false,
    pgtext: null, 
    gridview: true,
    caption: '',
    hidegrid: false,
    postData:{'p_id': id_paccnt, 'hist_mode': 0},
    jsonReader : {repeatitems: false},
 
    onSelectRow: function(id) { 
      cur_lgt_id = id;  
      
      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 

       if (lgt_hist_visible==0)
       {    
          validator_lgt.resetForm();  //для сброса состояния валидатора
          $("#fLgtParam").resetForm();
          $("#fLgtParam").clearForm();
          
          jQuery(this).jqGrid('GridToForm',gsr,"#fLgtParam"); 
          $("#fLgtParam").find("#foper").attr('value','edit');    
          CommitJQFormVal($("#fLgtParam"));

          $("#fLgtParam").find("#calc_lgt_info").html($(this).jqGrid('getCell',cur_lgt_id,'lgt_calc_info'));
          $("#fLgtParam").find("#lgt_edit_info").html($(this).jqGrid('getCell',cur_lgt_id,'edit_info'));
          $("#fLgtParam").find("#lid_reason").hide();

          $("#fLgtParam").find("#bt_add").hide();
          $("#fLgtParam").find("#bt_edit").show();   
          
          if (r_lgt_edit==3)
            $("#fLgtParam").find("#bt_edit").prop('disabled', false);
          else
            $("#fLgtParam").find("#bt_edit").prop('disabled', true);
          //jQuery('#paccnt_meter_zones_table').jqGrid('setGridParam',{'postData':{'p_id':id}}).trigger('reloadGrid');        
          
          dmmgg = Date.parse( mmgg, "dd.MM.yyyy");   
          //dt_start = Date.parse($(this).jqGrid('getCell',cur_lgt_id,'dt_start'), "dd.MM.yyyy"); 
          mmgg_lgt = Date.parse($(this).jqGrid('getCell',cur_lgt_id,'work_period'), "dd.MM.yyyy"); 
          if (mmgg_lgt.getTime() != dmmgg.getTime()) 
          {
              $("#bt_lgt_del").addClass('ui-state-disabled');
              //$("#fLgtParam").find("#btLgtSel").prop('disabled', true);
              $("#fLgtParam").find("#btLgtSel").prop('disabled', false);
          }
          else
          {
              $("#bt_lgt_del").removeClass('ui-state-disabled');
              $("#fLgtParam").find("#btLgtSel").prop('disabled', false);
          }
       }   
       else
       {
        jQuery('#hist_lgt_table').jqGrid('setGridParam',{'postData':{'lg_id':id}}).trigger('reloadGrid');                   
       }    
      }
      
    },

        
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');},
  
  gridComplete:function(){

   lgt_list_mode =0; //edit   
   
   dmmgg = Date.parse( mmgg, "dd.MM.yyyy");
    
   var ids = $(this).jqGrid('getDataIDs'); 
    
    if (ids.length > 0) 
    {      
     if (lgt_hist_visible==0)
     {
       $("#pLgtParam").show();        
     }  
     
     var first_id = parseInt($(this).getDataIDs()[0]);
     lgt_selected=0;
     //for(var i=0;i < ids.length;i++)
     for(var i=ids.length-1;i >= 0;i--)
     { 
        var cl = ids[i]; 
        
        dt_end = $(this).jqGrid('getCell',cl,'dt_end'); 
        if ($.trim(dt_end)!='')
        {
            ddt_e = Date.parse( dt_end, "dd.MM.yyyy");
//            ddt_e.add({days: -1, months: 1});

            if (ddt_e >dmmgg)
            {
              $("#fAccEdit").find("#lgt_label").html(jQuery(this).jqGrid('getCell',cl,'grp_lgt'));    
              $(this).setSelection(cl, true);
              lgt_selected=1;
              break;
            }
        }
        else
        {
         $("#fAccEdit").find("#lgt_label").html(jQuery(this).jqGrid('getCell',cl,'grp_lgt'));    
         $(this).setSelection(cl, true);
         lgt_selected=1;
         break;
        } 
     }
     if (lgt_selected==0)
        $(this).setSelection(first_id, true);
    /*
        if ($.trim(jQuery(this).jqGrid('getCell',first_id,'dt_end'))=='' )
        {
         $("#fAccEdit").find("#lgt_label").html(jQuery(this).jqGrid('getCell',first_id,'grp_lgt'));    
        } 
     */
    }
    else
    {
        $("#pLgtParam").hide();        
        $("#fAccEdit").find("#lgt_label").html('');    
    }
    
  }

  }).navGrid('#paccnt_lgt_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  //---------------------------------------------------------------------
  jQuery("#pLgtParam :input").addClass("ui-widget-content ui-corner-all");
  jQuery("#dialog-lgt_changedate :input").addClass("ui-widget-content ui-corner-all");
  jQuery("#dialog-lgt_confirm :input").addClass("ui-widget-content ui-corner-all");
  
  lgt_form_options = { 
    dataType:"json",
    beforeSubmit: LgtBeforeSubmit, // функция, вызываемая перед передачей 
    success: LgtSubmitResponse // функция, вызываемая при получении ответа
  };

fLgtParam_ajaxForm = $("#fLgtParam").ajaxForm(lgt_form_options);
  
jQuery("#paccnt_lgt_table").jqGrid('navButtonAdd','#paccnt_lgt_tablePager',{caption:"Історія пільг",
	onClickButton:function(){ 

     
       if (lgt_hist_visible==0)
           {
               createLgtHistGrid();
               lgt_hist_visible=1;
               $("#pLgtParam").hide();
               $("#hist_lgt_div").show();
               
              
               //$("#paccnt_meters_table").jqGrid('showCol',["dt_e"]); 
               $("#paccnt_lgt_table").jqGrid('setGridParam',{'postData':{'p_id': id_paccnt, 'hist_mode': 1}}).trigger('reloadGrid');        
               //innerLayout.resizeAll(); 
           }
       else
           {
               lgt_hist_visible=0;

               $("#pLgtParam").show();
               $("#hist_lgt_div").hide();

               //$("#paccnt_meters_table").jqGrid('hideCol',["dt_e"]);                
               $("#paccnt_lgt_table").jqGrid('setGridParam',{'postData':{'p_id': id_paccnt, 'hist_mode': 0}}).trigger('reloadGrid');                       
               //innerLayout.resizeAll(); 
           }
               
          
        ;} 
});


jQuery("#paccnt_lgt_table").jqGrid('navButtonAdd','#paccnt_lgt_tablePager',{caption:"Нова пільга",
        id:"bt_lgt_new",
	onClickButton:function(){ 

        if (lgt_hist_visible==1)
           {
               lgt_hist_visible=0;

               $("#pLgtParam").show();
               $("#hist_lgt_div").hide();

           }

          $("#pLgtParam").show();        
          
          validator_lgt.resetForm();
          $("#fLgtParam").resetForm();
          $("#fLgtParam").clearForm();
          
          $("#fLgtParam").find("[data_old_value]").attr('value',''); 
          $("#fLgtParam").find("[data_old_value]").attr('data_old_value',''); 

          $("#fLgtParam").find("#fid").attr('value',-1 );    
          $("#fLgtParam").find("#fid_paccnt").attr('value',id_paccnt );  
          $("#fLgtParam").find("#foper").attr('value','add');              
          
          $("#fLgtParam").find("#bt_add").show();
          $("#fLgtParam").find("#bt_edit").hide(); 
          $("#fLgtParam").find("#lid_reason").show();
          
          $("#lui_paccnt_lgt_table" ).show(); // disable grid
          $("#fLgtParam").find("#btFamily").prop('disabled', true);
          $("#fLgtParam").find("#btLgtSel").prop('disabled', false);
          //meterLayout.close('east');
          lgt_list_mode =1; //insert   
          //jQuery("#dialog_editform").dialog('open');          
          
        ;} 
});

jQuery("#paccnt_lgt_table").jqGrid('navButtonAdd','#paccnt_lgt_tablePager',{caption:"Видалити",
       id:"bt_lgt_del",
	onClickButton:function(){ 

      if ($("#paccnt_lgt_table").getDataIDs().length == 0) 
       {return} ;    

      if (lgt_hist_visible==1) return;
          
      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити пільгу?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                        
                                        $("#dialog-lgt_changedate").dialog({ 
                                            resizable: false,
                                            height:160,
                                            modal: true,
                                            autoOpen: false,
                                            buttons: {
                                                "Ok": function() {
                                                   var cur_dt_change = jQuery("#dialog-lgt_changedate").find("#fdate_change_lgt").val();
                                                   var reason = jQuery("#dialog-lgt_changedate").find("#fid_reason").val();
                                                   
                                                   fLgtParam_ajaxForm[0].change_date.value = cur_dt_change;
                                                   fLgtParam_ajaxForm[0].oper.value = 'del';
                                                   fLgtParam_ajaxForm[0].id_reason.value = reason;                                                   
                                                   
                                                   fLgtParam_ajaxForm.ajaxSubmit(lgt_form_options);       
                                                   

                                                    $( this ).dialog( "close" );
                                                },
                                                "Отмена": function() {
                                                    $( this ).dialog( "close" );
                                                }
                                            } /*
                                            ,focus: function() {
                                                $(this).on("keyup", function(e) {
                                                if (e.keyCode === 13) {
                                                        $(this).parent().find("button:contains('Ok')").trigger("click");
                                                        return false;
                                                        }
                                                    })
                                                }  */             
                                        });
                                        
                                        jQuery("#dialog-lgt_changedate").dialog('open');
                                    
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


jQuery("#paccnt_lgt_table").jqGrid('navButtonAdd','#paccnt_lgt_tablePager',{caption:"Велика таблиця",
        id:"btn_lgt_fullscreen",
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

//-------------------------------------------------------------
// опции валидатора 
var lgt_valid_options = { 

		rules: {
			grp_lgt: "required",
                        dt_start: "required",
                        family_cnt:{required: true,
                                number:true
                        }
		},
		messages: {
			grp_lgt: "Виберіть пільгу!",
                        dt_start: "Вкажіть дату початку",
			family_cnt:{required: "Вкажіть кільк. членів сім'ї",
                        number:"Повинно бути число!"
                        } 
                        
		}
};

validator_lgt = $("#fLgtParam").validate(lgt_valid_options);


//-------------------------------------------------------------
$("#pLgtParam").find("#bt_reset").click( function() 
{
    if (lgt_list_mode==0 )
    {
     validator_lgt.resetForm();
     ResetJQFormVal($("#fLgtParam"));
    } 

    if (lgt_list_mode==1 )
    {
     
        $("#lui_paccnt_lgt_table" ).hide();
        $("#fLgtParam").find("#btFamily").prop('disabled', false);
        //meterLayout.open('east');
        lgt_list_mode =0; //edit    
        
        if ($("#paccnt_lgt_table").getDataIDs().length > 0) 
        {      
     
             var first_id = parseInt($("#paccnt_lgt_table").getDataIDs()[0]);
            $("#paccnt_lgt_table").setSelection(first_id, true);

        }
        else
        {
            $("#pLgtParam").hide();        
        }
    }
  
});
//------------------------------------------------------------

   jQuery("#btFamily").click( function() { 
       
/*       
    $("#fLgtParam").attr('target',"lgtfamily_win" );           
    $("#fLgtParam").attr('action',"abon_en_lgtfamily.php" );               
    
     var ww = window.open("abon_en_lgtfamily.php", "lgtfamily_win", "toolbar=0,width=800,height=600");
     document.fLgtParam.submit();
     ww.focus();
     
    $("#fLgtParam").attr('target',"" );           
    $("#fLgtParam").attr('action',"abon_en_paccnt_lgt_edit.php" );               
*/     

    createFamilyGrid(cur_lgt_id);

    //jQuery("#grid_lgtfamily").css({'left': jQuery("#btFamily").position().left+1, 'top': jQuery("#btFamily").position().top+20});
    jQuery("#grid_lgtfamily").css({'left': 100, 'top': 300});
    jQuery("#grid_lgtfamily").toggle( );

   });


if (r_lgt_edit!=3)
{
    $('#bt_lgt_del').addClass('ui-state-disabled');
    $('#bt_lgt_new').addClass('ui-state-disabled');
    lgt_hist_edit = false;
}
else
{
    lgt_hist_edit = true;
}

//------------------------------------------------------
//----------------таблица истории  -------------
var createLgtHistGrid = function(){ 
    
  if (isLgtHistGridCreated) return;
  isLgtHistGridCreated =true;


  jQuery('#hist_lgt_table').jqGrid({
    url:'abon_en_paccnt_lgt_hist_data.php',
    editurl: 'abon_en_paccnt_lgt_hist_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:130,
    width:AllGridWidth,
    //autowidth: true,
    shrinkToFit : false,
    //scroll: 0,
    colModel :[ 
      {name:'id_key', index:'id_key', width:40, editable: false, align:'center', key:true,hidden:true},

      {label:'id',name:'id', index:'id', width:40, editable: false, align:'center', hidden:true},     
      {label:'id_paccnt',name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center', hidden:true},           
      {label:'id_grp_lgt',name:'id_grp_lgt', index:'id_grp_lgt', width:40, editable: false, align:'center', hidden:true},                 
      {label:'id_calc',name:'id_calc', index:'id_calc', width:40, editable: false, align:'center', hidden:true},                       
      
      {label:'Пільга',name:'grp_lgt', index:'grp_lgt', width:150, editable: false, align:'center', hidden:false},                       
      {label:'Метод розрах.',name:'calc_name', index:'calc_name', width:100, editable: false, align:'center', hidden:false},                       
      {label:"Сім'я",name:'family_cnt', index:'family_cnt', width:50, editable: false, align:'center', hidden:false},                             

      {label:'Приор.',name:'prior_lgt', index:'prior_lgt', width:30, editable: false, align:'left',edittype:'text'},           

      {label:'Особа',name:'fio_lgt', index:'fio_lgt', width:200, editable: false, align:'left',edittype:'text',hidden:false},           
      
      {label:'Документ',name:'id_doc', index:'id_doc', width:100, editable: false, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:ldocs},stype:'text'},                       
      
      {label:'Серія',name:'s_doc', index:'s_doc', width:50, editable: false, align:'left',edittype:'text'},                 
      {label:'Номер',name:'n_doc', index:'n_doc', width:50, editable: false, align:'left',edittype:'text'},           
      
      {label:'Дата док.',name:'dt_doc', index:'dt_doc', width:80, editable: false, align:'left',edittype:'text',formatter:'date'},
      {label:'Дійсний до',name:'dt_doc_end', index:'dt_doc_end', width:80, editable: false, align:'left',edittype:'text',formatter:'date'},      
      {label:'ІНН',name:'ident_cod_l', index:'ident_cod_l', width:80, editable: false, align:'left',edittype:'text',hidden:false},           
      
      {label:'Дата перереєстр.',name:'dt_reg', index:'dt_reg', width:80, editable: false, align:'left',edittype:'text',formatter:'date'},      
      
      {label:'Дата початку',name:'dt_start', index:'dt_start', width:80, editable: true, align:'left',edittype:'text',formatter:'date',
                formoptions:{label: 'Дата початку пільги'}   },      
      {label:'Дата закінч.',name:'dt_end', index:'dt_end', width:80, editable: true, align:'left',edittype:'text',formatter:'date',
                  formoptions:{label: 'Дата закінчення пільги'}   },          
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
    pager: '#hist_lgt_tablePager',
    rowNum:50,
    sortname: 'dt_b',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: '',
    pgbuttons: false,
    pgtext: null, 
    hiddengrid: false,
    jsonReader : {repeatitems: false},
    postData:{'lg_id':0},
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#hist_lgt_tablePager',
        //{edit:false,add:false,del:false,search:false}
        {edit:lgt_hist_edit,add:false,del:lgt_hist_edit,search:false,
            edittext: 'Редагувати',
            deltext: 'Видалити' },
        {width:400,reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterHistEdit}, 
        {}, 
        {reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterHistEdit}, 
        {} 
        ); 
        
};        
         


jQuery("#btLgtSel").click( function() { 
/*     var ww = window.open("lgt_list.php", "lgt_win", "toolbar=0,width=800,height=600");
     document.lgt_sel_params.submit();
     ww.focus();
*/
    createLgtsGrid(jQuery("#fid_grp_lgt").val());
    lgt_target_id=$("#fLgtParam").find("#fid_grp_lgt");
    lgt_target_name =  $("#fLgtParam").find("#fgrp_lgt");
    lgt_target_id_calc = $("#fLgtParam").find("#fid_calc_lgt");
    lgt_target_name_calc =  $("#fLgtParam").find("#fcalc_name_lgt");
    
    jQuery("#grid_sellgt").css({'left': $("#fLgtParam").find("#fgrp_lgt").offset().left+1, 'top': $("#fLgtParam").find("#fgrp_lgt").offset().top-100});
    jQuery("#grid_sellgt").toggle( );


   });


jQuery("#btLgtCalcSel").click( function() { 

    createLgtCalcGrid(jQuery("#fid_grp_lgt").val());
    calc_target_id=jQuery("#fid_calc_lgt");
    calc_target_name = jQuery("#fcalc_name_lgt");
    

    jQuery("#grid_selcalc").css({'left': jQuery("#fcalc_name_lgt").offset().left+1, 'top': jQuery("#fcalc_name_lgt").offset().top+20});
    jQuery("#grid_selcalc").toggle( );
});



// обработчик, который вызываетя перед отправкой формы
function LgtBeforeSubmit(formData, jqForm, options) { 

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
               
               if (($("#fLgtParam").find("#flgtdt_e").attr('data_old_value').trim()=="")&&
                   ($("#fLgtParam").find("#flgtdt_e").attr('value').trim()!=""))
               {
                      
                    $("#dialog-lgt_confirm").find("#dialog-text").html('Відмінити пільгу з '+$("#fLgtParam").find("#flgtdt_e").val()+'?');  
                    $("#dialog-lgt_confirm").dialog({
                        resizable: false,
                        height:160,
                        modal: true,
                        autoOpen: false,
                        title:'Відміна пільги',
                        buttons: {
                            "Так": function() {
                                var reason = jQuery("#dialog-lgt_confirm").find("#fid_reason").val();        
                                fLgtParam_ajaxForm[0].change_date.value = $("#fLgtParam").find("#flgtdt_e").val();
                                fLgtParam_ajaxForm[0].id_reason.value = reason;
                                fLgtParam_ajaxForm.ajaxSubmit(lgt_form_options);         
                                    
                                $( this ).dialog( "close" );
                            },
                            "Ні": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
    
                    jQuery("#dialog-lgt_confirm").dialog('open');
                      
                      
                     // fLgtParam_ajaxForm[0].change_date.value = $("#fLgtParam").find("#flgtdt_e").val();
                     // fLgtParam_ajaxForm.ajaxSubmit(meters_form_options);         
                     // return true;
                      
               }
               else
               {    
                  
                    $("#dialog-lgt_changedate").dialog({ 
			resizable: false,
			height:160,
			modal: true,
                        autoOpen: false,
			buttons: {
				"Ok": function() {
                                        //SaveLgtChanges();
                                          var cur_dt_change = jQuery("#dialog-lgt_changedate").find("#fdate_change_lgt").val();
                                          var reason = jQuery("#dialog-lgt_changedate").find("#fid_reason").val();
  
                                          fLgtParam_ajaxForm[0].change_date.value = cur_dt_change;
                                          fLgtParam_ajaxForm[0].id_reason.value = reason;
                                          
                                          fLgtParam_ajaxForm.ajaxSubmit(lgt_form_options);         

					$( this ).dialog( "close" );
				},
				"Отмена": function() {
                                        //CancelLgtChanges();
					$( this ).dialog( "close" );
				} /*
                                ,focus: function() {
                                    $(this).on("keyup", function(e) {
                                    if (e.keyCode === 13) {
                                        $(this).parent().find("button:contains('Ok')").trigger("click");
                                        return false;
                                        }
                                   })
                                }*/
                                
			}

                    });
                
                    $("#dialog-lgt_changedate").dialog('open');
                }
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
function LgtSubmitResponse(responseText, statusText)
{            
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {  // insert/delete  ok
                 
               //jQuery("#dialog_editform").dialog('close');                           
               $('#paccnt_lgt_table').trigger('reloadGrid');     
               
               if (lgt_list_mode==1 )
                { 
                    $("#lui_paccnt_lgt_table" ).hide();
                    $("#fLgtParam").find("#btFamily").prop('disabled', false);
                    //meterLayout.open('east');
                    lgt_list_mode =0; //edit    
        
                }
                var first_id = parseInt($("#paccnt_lgt_table").getDataIDs()[0]);
                $("#paccnt_lgt_table").setSelection(first_id, true);

               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
               /*
               var fid = $("#fLgtParam").find("#fid").val();
               if(fid) 
               { 
                 jQuery("#paccnt_lgt_table").jqGrid('FormToGrid',fid,"#fLgtParam"); 
               }  
               */
               $('#paccnt_lgt_table').trigger('reloadGrid');     
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};  
           
             if (errorInfo.errcode==3) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 

               jQuery("#dialog-confirm").css('background-color','red');
               jQuery("#dialog-confirm").css('color','white');
    
               jQuery("#dialog-confirm").find("#dialog-text").html('Пільговик з вказаним ідентифікаційним кодом вже зареєстрований! <br/> на особовому рахунку '+errorInfo.add_data);
    
               $("#dialog-confirm").dialog({
			resizable: false,
			height:160,
			modal: true,
                        autoOpen: false,
                        title:'Дубль ІНН',
			buttons: {
				"Ок": function() {
					$( this ).dialog( "close" );
				}
			}
		});
    
                jQuery("#dialog-confirm").dialog('open');

                return [false,errorInfo.errstr]
            };   

};

});


function SelectLgtExternal(id, name, id_calc, name_calc,code) {
        $("#fLgtParam").find("#fid_grp_lgt").attr('value',id );
        $("#fLgtParam").find("#fgrp_lgt").attr('value',name );    
        
        $("#fLgtParam").find("#fid_calc_lgt").attr('value',id_calc );
        $("#fLgtParam").find("#fcalc_name_lgt").attr('value',name_calc );    
        
}



