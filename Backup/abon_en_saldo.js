var bill_row_id=0;
var pay_row_id =0;
var corr_table_row_id = 0;
//var ind_date;
//var isNewIndicationGridCreated = false;
//var selICol ;
//var selIRow ;
var indic_edit_row_id=0;
var inpdemand_edit_row_id=0;
var newIndicationGridMode=0;
var lastSel =0;
var form_edit_lock=0;
var isIndicHistGridCreated =false;
var flock; 
var inpdemand_validator=null;
var corr_validator=null;


jQuery(function(){ 

  jQuery('#indic_table').jqGrid({
    url:'abon_en_saldo_indic_data.php',
    editurl: '',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    //width:800,
    autowidth: true,
    scroll: 0,
    colNames:[],
    colModel :[ 

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
//    {name:'id_pack', index:'id_pack', width:40, editable: false, align:'center',hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},     
    {label:'Код ліч.',name:'id_meter', index:'id_meter', width:40, editable: false, align:'center',hidden:true},    
    {name:'id_typemet', index:'id_typemet', width:40, editable: false, align:'center',hidden:true},    
    {name:'id_hwork', index:'id_hwork', width:40, editable: false, align:'center',hidden:true},    
    {name:'idk_work', index:'idk_work', width:40, editable: false, align:'center',hidden:true},    
   
    {label:'№ ліч.',name:'num_eqp', index:'num_eqp', width:80, editable: true, align:'left',edittype:'text'},            
    {label:'Тип ліч.',name:'type_meter', index:'type_meter', width:80, editable: true, align:'left',edittype:'text'},                
    {label:'Розр. ліч.',name:'carry', index:'carry', width:40, editable: true, align:'left',edittype:'text'},                    
    {label:'К.тр',name:'coef_comp', index:'coef_comp', width:40, editable: true, align:'left',edittype:'text'},                        
    {label:'Зона',name:'id_zone', index:'id_zone', width:60, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text'},                       


    {label:'Дата попер.',name:'p_dat_ind', index:'p_dat_ind', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Попер.пок.',name:'p_indic', index:'p_indic', width:80, editable: true, align:'right',hidden:false,edittype:'text',
                    formatter:'integer'},           

    {label:'Дата',name:'dat_ind', index:'dat_ind', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Поточні пок.',name:'value', index:'value', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           

    {label:'Спожив.',name:'value_cons', index:'value_cons', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           

    {label:'Період',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', hidden:false,formatter:'date'},
    {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
        
    {label:'Тип показників',name:'id_operation', index:'id_operation', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lindicoper},stype:'text'},                       
    {label:'№ відомості',name:'num_pack', index:'num_pack', width:60, editable: true, align:'left',edittype:'text'},
    {label:'Факт.пок.',name:'indic_real', index:'indic_real', width:60, editable: true, align:'left',edittype:'text'},

    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},        

    {label:'Вручну',name:'is_manual', index:'is_manual', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}},
    {label:'Корр',name:'is_corrected', index:'is_corrected', width:30, editable: false, 
        formatter:'checkbox',edittype:'checkbox',  align:'center',
        stype:'select', searchoptions:{value:': ;1:*'},
        hidden:false},        
    {label:'Закр.',name:'flock', index:'flock', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}},
    {name:'is_last', index:'is_last', width:30, editable: false, hidden:true},
    {name:'ind_mode', index:'ind_mode', width:30, editable: false, hidden:true}

    ],
    pager: '#indic_tablePager',
    rowNum:100,
   // rowList:[20,50,100,300,500],
    pgbuttons: false,
    pgtext: null, 

    sortname: 'dat_ind',
    sortorder: 'desc',
    viewrecords: true,
    gridview: false,
    caption: 'Показники',
    //hiddengrid: false,
    jsonReader : {repeatitems: false},
    hidegrid: false,
    postData:{'p_id': id_paccnt},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     //var first_id = parseInt($(this).getDataIDs()[$(this).getDataIDs().length-1]);
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
   },

   onSelectRow: function(rowid) { 
        indic_edit_row_id = rowid; 
    },

    rowattr: function (rd) {
        if (rd.is_corrected == '1')
            return {"style": "color:Brown !important;"};
        
    },    
    afterInsertRow: function(rowId, data)
    {
       if(data.is_last==1) 
          $(this).setCell(rowId, 'value', '', {'background-color':'#DDFFDD' });
      
       if(data.ind_mode==2) 
          $(this).setCell(rowId, 'value', '', {'background-color':'#EE9955' });
      
    },
    ondblClickRow: function(id){ 
      //jQuery(this).editGridRow(id,{width:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
    
    var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 

      //  edit_row_id = id;
      //  $("#fpaccnt_params").find("#pmode").attr('value',0 );
      //  $("#fpaccnt_params").find("#pid_paccnt").attr('value',id );
      //  document.paccnt_params.submit();
        
      } else {alert("Please select Row")}       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');}

  }).navGrid('#indic_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  jQuery("#paccnt_meter_zones_tablePager_center").hide();
  jQuery("#paccnt_meter_zones_tablePager_right").hide();


  jQuery("#indic_table").jqGrid('filterToolbar','');
  jQuery("#indic_tablePager_right").css("width","120px");
  jQuery("#indic_tablePager_center").css("width","180px");


    jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{
        id:"btn_indic_new",
        caption:"Завести пок.",
        onClickButton:function(){ 

            newIndicationGridMode =0;

            $("#dialog-indications").find("#fdt_ind").datepicker( "setDate" , Date.now().toString("dd.MM.yyyy") );
            createNewIndicationGrid(newIndicationGridMode);

            $("#dialog-indications").dialog({
                resizable: true,
                height:300,
                width:800,
                modal: true,
                autoOpen: false,
                dialogClass: 'StandartTitleClass',
                title:'Показники',
                resize: function(event, ui) 
                        {
                         if (isNewIndicationGridCreated)
                             {
                                jQuery("#new_indications_table").jqGrid('setGridWidth',$("#dialog-indications").innerWidth()-15);
                                jQuery("#new_indications_table").jqGrid('setGridHeight',$("#dialog-indications").innerHeight()-100);
                             }
                        },
                
                buttons: {
                    "Ок": function() {


                        if ((selICol!=0)&&(selIRow!=0))
                        {
                            jQuery('#new_indications_table').editCell(selIRow,selICol, false); 
                        }
    
    
                        var data_obj = $('#new_indications_table').getChangedCells('all');
                        var json_str = JSON.stringify(data_obj);
                        var id_reason = jQuery("#dialog-indications").find("#fid_reason").val();

                        //alert(json);
                        $.ajaxSetup({
                            type: "POST",   
                            dataType: "json"
                        });
    
                        var request = $.ajax({
                            url: "abon_ensaldo_new_indic_edit.php",
                            type: "POST",
                            data: {
                                oper : 'add' , 
                                reason: id_reason,
                                json_data : json_str  
                            },
                            dataType: "json"
                        });

                        request.done(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");                 
                                if (data.errcode==2)
                                    $('#message_zone').dialog('open');
                            }
                            $(".mod_column_class").removeClass("mod_column_class");
                            jQuery('#indic_table').trigger('reloadGrid');        
            
                        //window.opener.RefreshIndicExternal(id_pack);

                        });
                        request.fail(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");
                                $('#message_zone').dialog('open');
                            }
                            else
                            {
                                $('#message_zone').append(data);  
                                $('#message_zone').dialog('open');
                            }
            
                        });


                        $( this ).dialog( "close" );
                    },
                    "Відмінити": function() {
                        $( this ).dialog( "close" );
                    }
                }
            });
    
            jQuery("#dialog-indications").dialog('open');
            jQuery("#new_indications_table").jqGrid('setGridWidth',$("#dialog-indications").innerWidth()-15);
            jQuery("#new_indications_table").jqGrid('setGridHeight',$("#dialog-indications").innerHeight()-100);
            
        
        } 
    });
//------------------------------------------------------------------------------
    jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{
    caption:"Видалити",
    id:"btn_indic_del",
    onClickButton:function(){ 
        
       var gsr = jQuery("#indic_table").jqGrid('getGridParam','selrow'); 
       if(!gsr) return;
       
       if ($("#indic_table").jqGrid('getCell',indic_edit_row_id,'ind_mode')==2)
           return;
       
       flock=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'flock');
       var fmanual=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'is_manual');
       var foperation=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'id_operation');

//       if ((flock=='Yes')||(foperation==4)||(foperation==5))//не даем удалить строки по заменам
       if ((foperation==4)||(foperation==5))//не даем удалить строки по заменам
       {
           if (r_allmeter_edit!=3)
           {
             alert('Неможливо видалити показники з заміни лічильника!');
             return;
           }
       } 
       
       if (flock=='Yes')//   return;
       {
         jQuery("#dialog-confirm-reason").find("#dialog-text").html('Увага !!! Ви намагаєтесь видалити показники попереднього періоду!');
         jQuery("#dialog-confirm-reason").css('background-color','red');
         jQuery("#dialog-confirm-reason").css('color','white');
       }
       else
       {
         if ((foperation==4)||(foperation==5))
         {
          jQuery("#dialog-confirm-reason").find("#dialog-text").html('Видалити показники з заміни?');                   
          jQuery("#dialog-confirm-reason").css('background-color','red');
          jQuery("#dialog-confirm-reason").css('color','white');

         }
         else
         {
          jQuery("#dialog-confirm-reason").find("#dialog-text").html('Видалити показники?');                   
          jQuery("#dialog-confirm-reason").css('background-color','white');
          jQuery("#dialog-confirm-reason").css('color','black');
             
         }

       }        
        
    
        $("#dialog-confirm-reason").dialog({
            resizable: false,
            height:160,
            modal: true,
            autoOpen: false,
            title:'Видалення',
            buttons: {
                "Видалити": function() {
                                    
                    $.ajaxSetup({
                        type: "POST",   
                        dataType: "json"
                    });
    
                    var id_reason = jQuery("#dialog-confirm-reason").find("#fid_reason").val();
                    
                    var request = $.ajax({
                        url: "abon_ensaldo_new_indic_edit.php",
                        type: "POST",
                        data: {
                            oper : 'del' , 
                            reason:id_reason, 
                            id : indic_edit_row_id  
                        },
                        dataType: "json"
                    });

                    request.done(function(data ) {
                        if (data.errcode!==undefined)
                        {
                            $('#message_zone').append(data.errstr);  
                            $('#message_zone').append("<br>");  
                            if (data.errcode==2)
                                 $('#message_zone').dialog('open');
                            
                        }

                        jQuery('#indic_table').trigger('reloadGrid');        
            
                    });
                    request.fail(function(data ) {
                        if (data.errcode!==undefined)
                        {
                            $('#message_zone').append(data.errstr);  
                            $('#message_zone').append("<br>");                 
                            $('#message_zone').dialog('open');
                        }
                        else
                        {
                            $('#message_zone').append(data);  
                            $('#message_zone').dialog('open');
                        }
            
                    });
                    
                    $( this ).dialog( "close" );
                },
                "Відмінити": function() {
                    $( this ).dialog( "close" );
                }
            }
        });
    
        jQuery("#dialog-confirm-reason").dialog('open');
        
        
    }
});
//------------------------------------------------------------------------------    
    jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{
    caption:"Редагувати",
    id:"btn_indic_edit",    
    onClickButton:function(){ 
        
       var gsr = jQuery("#indic_table").jqGrid('getGridParam','selrow'); 
       if(!gsr) return;

       if ($("#indic_table").jqGrid('getCell',indic_edit_row_id,'ind_mode')==2)
           return;
       
       flock=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'flock');
       var fmanual=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'is_manual');
       var foperation=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'id_operation');
       var dat_ind=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'dat_ind');
       var id_work = jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'id_hwork');
       var idk_work = jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'idk_work');
       
       if (flock=='Yes')//   return;
       {
          grid_title = 'Редагування показників закритого періоду!';
          dlgTitleClass = 'RedTitleClass';
       }
       else
       {
          grid_title = 'Показники';
          dlgTitleClass = 'StandartTitleClass';
       }

       if ((foperation==4)||(foperation==5))
       {
         $("#fpaccnt_params").find("#pid_work").attr('value',id_work );   
         $("#fpaccnt_params").find("#pidk_work").attr('value',idk_work );          
         $("#fpaccnt_params").find("#pmode").attr('value','1' );   
         $("#fpaccnt_params").attr("action","meter_work.php");
         $("#fpaccnt_params").attr('target',"_blank" );           
         document.paccnt_params.submit();
       }
       else
       {

       newIndicationGridMode=1; 
       $("#dialog-indications").find("#fdt_ind").datepicker( "setDate" , dat_ind );
       createNewIndicationGrid(newIndicationGridMode);

       dlg = $("#dialog-indications").dialog({
                resizable: true,
                height:300,
                width:800,
                modal: true,
                autoOpen: false,
                dialogClass: dlgTitleClass,
                title:grid_title,
                resize: function(event, ui) 
                        {
                         if (isNewIndicationGridCreated)
                             {
                                jQuery("#new_indications_table").jqGrid('setGridWidth',$("#dialog-indications").innerWidth()-15);
                                jQuery("#new_indications_table").jqGrid('setGridHeight',$("#dialog-indications").innerHeight()-100);
                             }
                        },
                
                buttons: {
                    "Ок": function() {

                        if ((selICol!=0)&&(selIRow!=0))
                        {
                            jQuery('#new_indications_table').editCell(selIRow,selICol, false); 
                        }

                        var id_reason = jQuery("#dialog-indications").find("#fid_reason").val();
                        if ((flock=='Yes')&&(id_reason=='null'))
                         {
                            alert ('Вкажіть причину коригування!'); 
                            return;
                         }
                        
    
                        var data_obj = $('#new_indications_table').getChangedCells('all');
                        var json_str = JSON.stringify(data_obj);
                        
                        //alert(json);
                        $.ajaxSetup({
                            type: "POST",   
                            dataType: "json"
                        });
    
                        var request = $.ajax({
                            url: "abon_ensaldo_new_indic_edit.php",
                            type: "POST",
                            data: {
                                oper : 'edit' , 
                                reason: id_reason,
                                json_data : json_str  
                            },
                            dataType: "json"
                        });

                        request.done(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>"); 
                                if (data.errcode==2)
                                   $('#message_zone').dialog('open');

                            }
                            $(".mod_column_class").removeClass("mod_column_class");
                            jQuery('#indic_table').trigger('reloadGrid');        
            
                        //window.opener.RefreshIndicExternal(id_pack);
 
                        });
                        request.fail(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");  
                                $('#message_zone').dialog('open');
                            }
                            else
                            {
                                $('#message_zone').append(data);  
                                $('#message_zone').dialog('open');
                            }
            
                        });


                        $( this ).dialog( "close" );
                    },
                    "Відмінити": function() {
                        $( this ).dialog( "close" );
                    }
                }
            });
/*            
            if (flock=='Yes')
            {
              dlg.prev(".ui-dialog-titlebar").css("background","red");
            }
*/    
            jQuery("#dialog-indications").dialog('open');        
            jQuery("#new_indications_table").jqGrid('setGridWidth',$("#dialog-indications").innerWidth()-15);
            jQuery("#new_indications_table").jqGrid('setGridHeight',$("#dialog-indications").innerHeight()-100);
       } 
    }
});    
//-----------------------------------------------------------------------------
    jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{caption:"Розрах.кор.",
    id:"btn_recalc_bill",
    onClickButton:function(){ 
        
       var gsr = jQuery("#indic_table").jqGrid('getGridParam','selrow'); 
       if(!gsr) return;
       
       if ($("#indic_table").jqGrid('getCell',indic_edit_row_id,'ind_mode')==2)
           return;
       
       flock=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'flock');
       var fmanual=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'is_manual');
       var fmmgg=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'mmgg');
       
      // if (flock=='No')  return; ????

            jQuery("#dialog-confirm").find("#dialog-text").html('Увага !!! Ви намагаєтесь зробити перерахунок по показниках попереднього періоду!');
            jQuery("#dialog-confirm").css('background-color','red');
            jQuery("#dialog-confirm").css('color','white');

           
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Перерахунок',
                buttons: {
                    "Формувати": function() {

                        var request = $.ajax({
                            url: "bill_calc_one_edit.php",
                            type: "POST",
                            data: {
                                id_paccnt : id_paccnt,
                                mmgg : fmmgg
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
                                    if (data.id =1 )
                                    {
                                        //alert("Рахунки зформовано!");
                                        $( "[href='#tab_bill']").trigger( "click" );
                                        jQuery('#bill_table').trigger('reloadGrid');                      
                                        jQuery('#saldo_table').trigger('reloadGrid');                      
                                        jQuery('#corr_table').trigger('reloadGrid');                      
                                    }
                                    else
                                    {
                                        alert("Помилка при формуванні рахунків!");                        
                                    }
                
                                }
                                else
                                {
                                    jQuery("#message_zone").dialog('open');                                    
                                }
                            }
                        });

                        request.fail(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");
                                $('#message_zone').dialog('open');
                            }
                            else
                            {
                                $('#message_zone').append(data);  
                                $('#message_zone').dialog('open');                                
                            }
        
                        });
                      
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
//------------------------------------------------------------------------------
    jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{caption:"Кор.ПС.",
    id:"btn_distrib_ind",
    onClickButton:function(){ 
        
       var gsr = jQuery("#indic_table").jqGrid('getGridParam','selrow'); 
       if(!gsr) return;
       
       if ($("#indic_table").jqGrid('getCell',indic_edit_row_id,'ind_mode')==2)
           return;
       
       flock=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'flock');
       var fmanual=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'is_manual');
       var fmmgg=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'mmgg');
       
      // if (flock=='No')  return; ????

            jQuery("#dialog-confirm").find("#dialog-text").html('Виконати корегування планового споживання попередніх періодів?');
            jQuery("#dialog-confirm").css('background-color','red');
            jQuery("#dialog-confirm").css('color','white');

           
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Перерахунок',
                buttons: {
                    "Формувати": function() {

                        var request = $.ajax({
                            url: "indic_distrib_edit.php",
                            type: "POST",
                            data: {
                                id_indic : indic_edit_row_id
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
                                    if (data.id =1 )
                                    {
                                        //alert("Рахунки зформовано!");
                                        jQuery('#indic_table').trigger('reloadGrid');                                                              
                                        jQuery('#bill_table').trigger('reloadGrid');                      
                                        jQuery('#saldo_table').trigger('reloadGrid');                      
                                    }
                                    else
                                    {
                                        alert("Помилка при формуванні рахунків!");                        
                                    }
                
                                }
                                else
                                {
                                    jQuery("#message_zone").dialog('open');                                    
                                }
                            }
                        });

                        request.fail(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");
                                $('#message_zone').dialog('open');
                            }
                            else
                            {
                                $('#message_zone').append(data);  
                                $('#message_zone').dialog('open');                                
                            }
        
                        });
                      
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


//------------------------------------------------------------------------------
    jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{caption:"Ревізія",
    id:"btn_indic_repair",
    onClickButton:function(){ 
        
       var gsr = jQuery("#indic_table").jqGrid('getGridParam','selrow'); 
       if(!gsr) return;
       
       if ($("#indic_table").jqGrid('getCell',indic_edit_row_id,'ind_mode')==2)
           return;
       
       flock=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'flock');
       var fmanual=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'is_manual');
       var fmmgg=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'mmgg');
       var fid=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'id');
       
       //if (flock=='Yes')  return;


        var request = $.ajax({
            url: "indic_refresh_one_edit.php",
            type: "POST",
            data: {
                id : fid
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
                    jQuery('#indic_table').trigger('reloadGrid');    
                }
                else
                {
                    jQuery("#message_zone").dialog('open');
                }
            }
            
        });

        request.fail(function(data ) {
            if (data.errcode!==undefined)
            {
                $('#message_zone').append(data.errstr);  
                $('#message_zone').append("<br>");
                $('#message_zone').dialog('open');
            }
            else
            {
                $('#message_zone').append(data);  
                $('#message_zone').dialog('open');                                
            }

        
        });
        
    }
});    
//------------------------------------------------------------------------------
    jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{caption:"Розрахунок",
    id:"btn_indic_bill",
    onClickButton:function(){ 
        
       var gsr = jQuery("#indic_table").jqGrid('getGridParam','selrow'); 
       if(!gsr) return;
       
       if ($("#indic_table").jqGrid('getCell',indic_edit_row_id,'ind_mode')==2)
           return;
       
       flock=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'flock');
       var fmanual=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'is_manual');
       var fmmgg=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'mmgg');
       
       if (flock=='Yes')  return;

            if (fmmgg==mmgg)
               {
                 jQuery("#dialog-confirm").find("#dialog-text").html('Формувати рахунок по поточних показниках?');
                 jQuery("#dialog-confirm").css('background-color','white');
                 jQuery("#dialog-confirm").css('color','black');

               }
            else
               {
                 return;
               }
           
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Формування рахунків',
                buttons: {
                    "Формувати": function() {

                        var request = $.ajax({
                            url: "bill_calc_one_edit.php",
                            type: "POST",
                            data: {
                                id_paccnt : id_paccnt,
                                mmgg : fmmgg
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
                                    if (data.id =1 )
                                    {
                                        //alert("Рахунки зформовано!");
                                        $( "[href='#tab_bill']").trigger( "click" );
                                        jQuery('#bill_table').trigger('reloadGrid');                      
                                        jQuery('#saldo_table').trigger('reloadGrid');                      
                                    }
                                    else
                                    {
                                        alert("Помилка при формуванні рахунків!");                        
                                    }
                
                                }
                                else
                                {
                                    jQuery("#message_zone").dialog('open');                                    
                                }
                            }
                        });

                        request.fail(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");
                                $('#message_zone').dialog('open');
                            }
                            else
                            {
                                $('#message_zone').append(data);  
                                $('#message_zone').dialog('open');                                
                            }

        
                        });
                      
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

if (r_indic!=3)
{
    $('#btn_indic_new').addClass('ui-state-disabled');
    $('#btn_indic_del').addClass('ui-state-disabled');
    $('#btn_indic_edit').addClass('ui-state-disabled');
    $('#btn_indic_repair').addClass('ui-state-disabled');
    $('#btn_indic_bill').addClass('ui-state-disabled');
    $('#btn_recalc_bill').addClass('ui-state-disabled');
    $('#btn_recalc_all').addClass('ui-state-disabled');
    $('#btn_recalc_del').addClass('ui-state-disabled');
}

if (r_distrib!=3)
{
    $('#btn_distrib_ind').addClass('ui-state-disabled');
}

//------------------------------------------------------------------------------

   $("#dialog-indications").find("#btIndRefresh").click( function(){ 
       createNewIndicationGrid(newIndicationGridMode);
   });


   $("#fPayEdit").find("#fvalue").bind('input propertychange', function() {
      
      var sum_all = parseFloat($("#fPayEdit").find("#fvalue").val().replace(',', '.'));
      if (sum_all!=0)
          {
              var sum_nds = sum_all/6;
              
              $("#fPayEdit").find("#fvalue_tax").attr('value', sum_nds.toFixed(2) );
          }
   });
    
//------------------------------------------------------------------------------
  jQuery('#bill_table').jqGrid({
    url:'abon_en_saldo_bill_data.php',
    editurl: '',
    //datatype: 'json',
    datatype: "local",
    mtype: 'POST',
    height:400,
    //width:800,
    autowidth: true,
    scroll: 0,
    colNames:[],
    colModel :[ 

    {name:'id_doc', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    
    {label:'№ .',name:'reg_num', index:'reg_num', width:80, editable: true, align:'left',edittype:'text'},            
    {label:'Дата',name:'reg_date', index:'reg_date', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
    
    {label:'Тип док.',name:'idk_doc', index:'idk_doc', width:60, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lidk_doc},stype:'select'},                       
    
    {label:'Тип нар.',name:'id_pref', index:'id_pref', width:60, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lid_pref},
                            stype:'select',  searchoptions: {value:lid_pref, defaultValue:'10'},  hidden:false},
    {label:'кВтг',name:'demand', index:'demand', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           

    {label:'Сума нар.,грн',name:'value_calc', index:'value_calc', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
                        
    {label:'Пільги,грн',name:'value_lgt', index:'value_lgt', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           

    {label:'Субс.,грн',name:'value_subs', index:'value_subs', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number'},           


    {label:'Сума,грн',name:'value', index:'value', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    {label:'ПДВ,грн',name:'value_tax', index:'value_tax', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    
//    {label:'mmgg',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', hidden:false,formatter:'date'},
    {label:'Період форм.',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', formatter:'date', hidden:false},
    {label:'Період спож.',name:'mmgg_bill', index:'mmgg_bill', width:80, editable: true, align:'left',edittype:'text', formatter:'date', hidden:false},
    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},        
    {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
        
    {label:'Корр',name:'is_corrected', index:'is_corrected', width:30, editable: false, 
        formatter:'checkbox',edittype:'checkbox',  align:'center',
        stype:'select', searchoptions:{value:': ;1:*'},
        hidden:false},        
    {label:'Закр.',name:'flock', index:'flock', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}}

    ],
    pager: '#bill_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],
    sortname: 'reg_date',
    sortorder: 'desc',
    viewrecords: true,
    gridview: true,
    caption: 'Рахунки',
    jsonReader : {repeatitems: false},
    //hiddengrid: false,
    hidegrid: false,
    postData:{'p_id': id_paccnt},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     //var first_id = parseInt($(this).getDataIDs()[$(this).getDataIDs().length-1]);
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
   },

   onSelectRow: function(rowid) { 
        bill_row_id = rowid; 
    },

    
    ondblClickRow: function(id){ 

      bill_row_id = id; 
      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
        jQuery('#bill_info1_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': bill_row_id}}).trigger('reloadGrid');
        jQuery('#bill_info2_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': bill_row_id}}).trigger('reloadGrid');
        jQuery('#bill_info3_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': bill_row_id}}).trigger('reloadGrid');
        jQuery('#bill_info4_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': bill_row_id}}).trigger('reloadGrid');
        
        $("#bill_info").dialog('option', 'title', 'Рахунок ' + bill_row_id );
        $("#bill_info").dialog('open');          
        
      } else {alert("Please select Row")}       
       
    } ,  
    rowattr: function (rd) {
        if (rd.is_corrected == '1')
            return {"style": "color:Brown !important;"};
        
    },    
    
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');}

  }).navGrid('#bill_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  //$("#bill_table").jqGrid('filterToolbar','');
  
  $('#bill_table').jqGrid('filterToolbar', {autosearch: true});
  $('#bill_table').setGridParam({datatype: 'json'});
  $('#bill_table')[0].triggerToolbar();
  
  
  jQuery("#bill_tablePager_right").css("width","120px");
  jQuery("#bill_tablePager_center").css("width","180px");


  jQuery('#pay_table').jqGrid({
    url:'abon_en_saldo_pay_data.php',
    editurl: '',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    //width:800,
    autowidth: true,
    scroll: 0,
    colNames:[],
    colModel :[ 

    {name:'id_doc', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    {name:'id_headpay', index:'id_headpay', width:40, editable: false, align:'center',hidden:true},    
    
    {label:'№',name:'reg_num', index:'reg_num', width:20, editable: true, align:'left',edittype:'text'},            

    {label:'Дата опл.',name:'reg_date', index:'reg_date', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Дата надх.',name:'pay_date', index:'pay_date', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Пачка',name:'payheader', index:'payheader', width:100, editable: false, align:'left',hidden:false},
    {label:'Банк',name:'origin', index:'origin', width:80, editable: false, align:'left',edittype:'text'},

    {label:'Тип док.',name:'idk_doc', index:'idk_doc', width:60, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lidk_doc},stype:'text'},                       
    
    {label:'Тип нар.',name:'id_pref', index:'id_pref', width:50, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lid_pref},stype:'text', hidden:false},

    {label:'Сума,грн',name:'value', index:'value', width:70, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    
    {label:'ПДВ,грн',name:'value_tax', index:'value_tax', width:70, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    
    {label:'Період',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', hidden:false,formatter:'date'},
    {label:'Період опл.',name:'mmgg_pay', index:'mmgg_pay', width:80, editable: true, align:'left',edittype:'text', hidden:false,formatter:'date'},    
    {label:'Період пачки',name:'mmgg_hpay', index:'mmgg_hpay', width:80, editable: true, align:'left',edittype:'text', hidden:true,formatter:'date'},        
    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},        
    {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
    {label:'Закр.',name:'flock', index:'flock', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}},
    {label:'Прим',name:'note', index:'note', width:100, editable: false, align:'left',edittype:'text',hidden:true},                        

    ],
    pager: '#pay_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],
    sortname: 'reg_date',
    sortorder: 'desc',
    viewrecords: true,
    gridview: true,
    caption: 'Платежі',
    jsonReader : {repeatitems: false},
    //hiddengrid: false,
    hidegrid: false,
    postData:{'p_id': id_paccnt},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     //var first_id = parseInt($(this).getDataIDs()[$(this).getDataIDs().length-1]);
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
   },

   onSelectRow: function(rowid) { 
        pay_row_id = rowid; 
        
//        if ($(this).jqGrid('getCell',pay_row_id,'idk_doc')==100)
//        {
//           $('#btn_pay_del').addClass('ui-state-disabled');
//           $('#btn_pay_edit').addClass('ui-state-disabled');
//        }
//        else
//        {
           if (r_pay==3) {
               
            if (($(this).jqGrid('getCell',pay_row_id,'flock')==='Yes')||
                ($(this).jqGrid('getCell',pay_row_id,'idk_doc')==100))
                $('#btn_pay_del').addClass('ui-state-disabled');
            else 
                $("#btn_pay_del").removeClass('ui-state-disabled');            
            
        
            $("#btn_pay_edit").removeClass('ui-state-disabled');                   
           }
           else
           {
            $('#btn_pay_del').addClass('ui-state-disabled');
            $('#btn_pay_edit').addClass('ui-state-disabled');
           }
//        }
       
    },

    
    ondblClickRow: function(id){ 
      //jQuery(this).editGridRow(id,{width:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
    
    var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 

      //  edit_row_id = id;
      //  $("#fpaccnt_params").find("#pmode").attr('value',0 );
      //  $("#fpaccnt_params").find("#pid_paccnt").attr('value',id );
      //  document.paccnt_params.submit();
        
      } else {alert("Please select Row")}       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');}

  }).navGrid('#pay_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
  jQuery("#pay_table").jqGrid('filterToolbar','');
  
  jQuery("#pay_tablePager_right").css("width","120px");
  jQuery("#pay_tablePager_center").css("width","180px");
    
    
 //--------------------------------   
  jQuery('#corr_table').jqGrid({
    url:'abon_en_saldo_corr_data.php',
    editurl: '',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    //width:800,
    autowidth: false,
    shrinkToFit : false,
    scroll: 0,
    colNames:[],
    colModel :[ 

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    
    {label:'Період',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', hidden:false,formatter:'date'},
    {label:'Період коригування',name:'mmgg_corr', index:'mmgg_corr', width:130, editable: true, align:'left',edittype:'text', hidden:false,formatter:'date'},    
    {label:'Виконано',name:'is_calc', index:'is_calc', width:70, editable: false, align:'left',edittype:'text'},

    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},        
    {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}

    ],
    pager: '#corr_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],
    sortname: 'mmgg_corr',
    sortorder: 'desc',
    viewrecords: true,
    gridview: true,
    caption: 'Потрібні перерахунки',
    jsonReader : {repeatitems: false},
    //hiddengrid: false,
    hidegrid: false,
    postData:{'p_id': id_paccnt},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
   },

    onSelectRow: function(rowid) {
        corr_table_row_id = rowid;    
    },
    
    ondblClickRow: function(id){ 
    
    var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 

        corr_table_row_id = id;
      //  $("#fpaccnt_params").find("#pmode").attr('value',0 );
      //  $("#fpaccnt_params").find("#pid_paccnt").attr('value',id );
      //  document.paccnt_params.submit();
        
      } else {alert("Please select Row")}       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');}

  }).navGrid('#corr_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
  jQuery("#corr_table").jqGrid('filterToolbar','');
  
  jQuery("#corr_tablePager_right").css("width","120px");
  jQuery("#corr_tablePager_center").css("width","180px");

//------------------------------------------------------------------------------
    jQuery("#corr_table").jqGrid('navButtonAdd','#corr_tablePager',{caption:"Розрах. всі",
    id:"btn_recalc_all",
    onClickButton:function(){ 
        
       //var gsr = jQuery("#indic_table").jqGrid('getGridParam','selrow'); 
       //if(!gsr) return;
       
       //flock=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'flock');
       //var fmanual=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'is_manual');
       //var fmmgg=jQuery("#indic_table").jqGrid('getCell',indic_edit_row_id,'mmgg');
       
      // if (flock=='No')  return; ????

            jQuery("#dialog-confirm").find("#dialog-text").html('Увага !!! Ви намагаєтесь зробити перерахунок по показниках попередніх періодів!');
            jQuery("#dialog-confirm").css('background-color','red');
            jQuery("#dialog-confirm").css('color','white');

           
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Перерахунок',
                buttons: {
                    "Формувати": function() {

                        var request = $.ajax({
                            url: "bill_calc_corr_edit.php",
                            type: "POST",
                            data: {
                                id_paccnt : id_paccnt
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
                                    if (data.id =1 )
                                    {
                                        //alert("Рахунки зформовано!");
                                        $( "[href='#tab_bill']").trigger( "click" );
                                        jQuery('#bill_table').trigger('reloadGrid');                      
                                        jQuery('#saldo_table').trigger('reloadGrid');                      
                                        jQuery('#corr_table').trigger('reloadGrid');                      
                                    }
                                    else
                                    {
                                        alert("Помилка при формуванні рахунків!");                        
                                    }
                
                                }
                                else
                                {
                                    jQuery("#message_zone").dialog('open');                                    
                                }
                            }
                        });

                        request.fail(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");
                                $('#message_zone').dialog('open');
                            }
                            else
                            {
                                $('#message_zone').append(data);  
                                $('#message_zone').dialog('open');                                
                            }
        
                        });
                      
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

//------------------------------------------------------------------------------
jQuery("#corr_table").jqGrid('navButtonAdd','#corr_tablePager',{caption:"Додати",
    id:"btn_corr_new",    
    onClickButton:function(){ 

      
            recalc_validator.resetForm();  //для сброса состояния валидатора
            $("#fRecalcEdit").resetForm();
            $("#fRecalcEdit").clearForm();
            $("#fRecalcEdit").find("#fid_paccnt").attr('value',id_paccnt);
          
    
            $("#fRecalcEdit").find("#foper").attr('value','add');
            $("#fRecalcEdit").find("#bt_add").show();
            
            $("#dialog_recalcedit").dialog('open');          
  
    } 
});
//------------------------------------------------------------------------------

    jQuery("#corr_table").jqGrid('navButtonAdd','#corr_tablePager',{caption:"Видалити",
    id:"btn_recalc_del",
    onClickButton:function(){ 
        
           var gsr = jQuery("#corr_table").jqGrid('getGridParam','selrow'); 
           if(!gsr) return;
        
        
            jQuery("#dialog-confirm").find("#dialog-text").html('Видалити запис про перерахунок попередніх періодів?');
            jQuery("#dialog-confirm").css('background-color','red');
            jQuery("#dialog-confirm").css('color','white');

           
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Перерахунок',
                buttons: {
                    "Видалити": function() {

                        var request = $.ajax({
                            url: "bill_corr_del_edit.php",
                            type: "POST",
                            data: {
                                id : corr_table_row_id
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
                                    if (data.id =1 )
                                    {
                                        //alert("Рахунки зформовано!");
                                        //$( "[href='#tab_bill']").trigger( "click" );
                                        //jQuery('#bill_table').trigger('reloadGrid');                      
                                        //jQuery('#saldo_table').trigger('reloadGrid');                      
                                        jQuery('#corr_table').trigger('reloadGrid');                      
                                    }
                                    else
                                    {
                                        alert("Помилка!");                        
                                    }
                
                                }
                                else
                                {
                                    jQuery("#message_zone").dialog('open');                                    
                                }
                            }
                        });

                        request.fail(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");
                                $('#message_zone').dialog('open');
                            }
                            else
                            {
                                $('#message_zone').append(data);  
                                $('#message_zone').dialog('open');                                
                            }
        
                        });
                      
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

//------------------------------------------------------------------------------

  jQuery('#saldo_table').jqGrid({
    url:'abon_en_saldo_saldo_data.php',
    editurl: '',
    datatype: 'json',
    mtype: 'POST',
    //height:500,
    //width:800,
    autowidth: true,
    scroll: 0,
    colNames:[],
    colModel :[ 

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    
    {label:'Місяць',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text',formatter:'date', hidden:false},

    {label:'Тип нарах.',name:'id_pref', index:'id_pref', width:60, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lid_pref},stype:'text'},                       

    {label:'Борг поч.,грн',name:'b_val', index:'b_val', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           

    {label:'Борг початок ПДВ,грн',name:'b_valtax', index:'b_valtax', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number'},           

    {label:'Нарах,грн',name:'dt_val', index:'dt_val', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           

    {label:'Нарах. ПДВ,грн',name:'dt_valtax', index:'dt_valtax', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number'},           

    {label:'Сплачено,грн',name:'kt_val', index:'kt_val', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    {label:'в т.ч.субс.',name:'subs_all', index:'subs_all', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           

    {label:'Сплачено ПДВ,грн',name:'kt_valtax', index:'kt_valtax', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number'},           


    {label:'Борг кінець,грн',name:'e_val', index:'e_val', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           

    {label:'Борг кінець ПДВ,грн',name:'e_valtax', index:'e_valtax', width:80, editable: true, align:'right',hidden:true,
                            edittype:'text',formatter:'number'},           

    {label:'Стан',name:'sw_action', index:'sw_action', width:40, editable: true, align:'right',hidden:false,
                            edittype:'text'},           

    {label:'Закр.',name:'flock', index:'flock', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}}

    ],
    pager: '#saldo_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],
    sortname: 'mmgg',
    sortorder: 'desc', 
    viewrecords: true,
    gridview: true,
    caption: paccnt_info,
    jsonReader : {repeatitems: false},
    //hiddengrid: false,
    hidegrid: false,
    postData:{'p_id': id_paccnt},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
    
    //jQuery('#saldo_table').find(".ui-jqgrid-titlebar").html("--999--");
    
     //$("#psaldo_list span.ui-jqgrid-title").after('<div style="float:right;color:blue">'+paccnt_book+'/'+paccnt_code+'</div>');
    $("#psaldo_list span.ui-jqgrid-title").css("color","blue");     
    $("#psaldo_list span.ui-jqgrid-title").css("font-size","12px");     
   },

   onSelectRow: function(id) { 
     //   edit_row_id = rowid; 

      var $this = $(this);
      if (id !== lastSel && typeof lastSel !== 'undefined') {
         $this.jqGrid('setCell', lastSel, 'dt_val', '', {'font-weight': 'normal'});
         $this.jqGrid('setCell', lastSel, 'kt_val', '', {'font-weight': 'normal'});  
         $this.jqGrid('setCell', lastSel, 'b_val', '', {'font-weight': 'normal'});  
         $this.jqGrid('setCell', lastSel, 'e_val', '', {'font-weight': 'normal'});  
         $this.jqGrid('setCell', lastSel, 'mmgg', '', {'font-weight': 'normal'});  
         $this.jqGrid('setCell', lastSel, 'subs_all', '', {'font-weight': 'normal'});  
       
      }
      $this.jqGrid('setCell', id, 'dt_val', '', {'font-weight': 'bold'});     
      $this.jqGrid('setCell', id, 'kt_val', '', {'font-weight': 'bold'});  
      $this.jqGrid('setCell', id, 'b_val', '', {'font-weight': 'bold'});  
      $this.jqGrid('setCell', id, 'e_val', '', {'font-weight': 'bold'});  
      $this.jqGrid('setCell', id, 'mmgg', '', {'font-weight': 'bold'});  
      $this.jqGrid('setCell', id, 'subs_all', '', {'font-weight': 'bold'});        
      lastSel = id;     
    },

    
    ondblClickRow: function(id){ 
      //jQuery(this).editGridRow(id,{width:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
    
    var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 

      //  edit_row_id = id;
      //  $("#fpaccnt_params").find("#pmode").attr('value',0 );
      //  $("#fpaccnt_params").find("#pid_paccnt").attr('value',id );
      //  document.paccnt_params.submit();
        
      } else {alert("Please select Row")}       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');}

  }).navGrid('#saldo_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

jQuery("#saldo_tablePager_right").css("width","120px");
jQuery("#saldo_tablePager_center").css("width","180px");


//------------------------------------------------------------------------------
    jQuery("#saldo_table").jqGrid('navButtonAdd','#saldo_tablePager',{caption:"Перерахунок",
    id:"btn_recalc_saldo",
    onClickButton:function(){ 
        
           
        jQuery("#dialog-confirm").find("#dialog-text").html('Перерахувати сальдо абонента?');
        jQuery("#dialog-confirm").css('background-color','white');
        jQuery("#dialog-confirm").css('color','black');
           
        $("#dialog-confirm").dialog({
            resizable: false,
            height:140,
            modal: true,
            autoOpen: false,
            title:'Перерахунок',
            buttons: {
                "Перерахувати": function() {

                    var request = $.ajax({
                        url: "abon_en_saldo_calc.php",
                        type: "POST",
                        data: {
                            id_paccnt : id_paccnt,
                            mmgg : mmgg
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
                                jQuery('#saldo_table').trigger('reloadGrid');                      
                
                            }
                            else
                            {
                                jQuery("#message_zone").dialog('open');                                    
                            }
                        }
                    });

                    request.fail(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");
                                $('#message_zone').dialog('open');
                            }
                            else
                            {
                                $('#message_zone').append(data);  
                                $('#message_zone').dialog('open');                                
                            }

                    });
                      
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
//--------------------------------------------------------------------------

    jQuery("#saldo_table").jqGrid('navButtonAdd','#saldo_tablePager',{caption:"Друк за період",
    id:"btn_print_saldo",
    onClickButton:function(){ 

       //var filters = jQuery("#bill_table").jqGrid('getGridParam', 'postData').filters;
       //var rows = $('#bill_table').getDataIDs();
       //var bills = new Array();
       //bills[0] = bill_row_id;
       var mmgg_h = $("#saldo_table").jqGrid('getCell',lastSel,'mmgg')
       //var json_str = JSON.stringify(bills);

       $("#fprint_params").attr('action', 'bill_print.php');
       
       $("#fprint_params").find("#pmmgg").attr('value', mmgg_h); 
       $("#fprint_params").find("#pid_paccnt").attr('value', id_paccnt); 
       $("#fprint_params").find("#pcaption").attr('value', paccnt_book+'/'+paccnt_code); 
       $("#fprint_params").find("#pbill_list").attr('value','' ); 
       $("#fprint_params").find("#pid_bill").attr('value','' ); 
       
       $("#fprint_params").attr('target',"_blank" );           
       document.print_params.submit();

    }
});    

//------------------------------------------------------------------------------
jQuery("#saldo_table").jqGrid('filterToolbar','');

jQuery('#inpdemand_table').jqGrid({
    url:'abon_en_saldo_inpdemand_data.php',
    editurl: '',
    datatype: 'json',
    mtype: 'POST',
    height:600,
    //width:800,
    autowidth: false,
    shrinkToFit : false,

    scroll: 0,
    colNames:[],
    colModel :[ 

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    {label:'Код ліч.',name:'id_meter', index:'id_meter', width:40, editable: false, align:'center',hidden:true},    
    //{label:'№ ліч.',name:'num_eqp', index:'num_eqp', width:80, editable: true, align:'left',edittype:'text'},            
    {label:'Зона',name:'id_zone', index:'id_zone', width:60, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text'},

    {label:'Дата поч.',name:'dat_b', index:'dat_b', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
    {label:'Дата кінц.',name:'dat_e', index:'dat_e', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Споживання',name:'demand', index:'demand', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           

    {label:'Період',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', hidden:false,formatter:'date'},
    {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},        
    {label:'Закр.',name:'flock', index:'flock', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox',
                            stype:'select', searchoptions:{value:': ;1:*'}}
        

    ],
    pager: '#inpdemand_tablePager',
    rowNum:100,
   // rowList:[20,50,100,300,500],
    pgbuttons: false,
    pgtext: null, 

    sortname: 'mmgg',
    sortorder: 'desc',
    viewrecords: true,
    gridview: true,
    caption: 'Споживання по зеленому тарифу',
    //hiddengrid: false,
    jsonReader : {repeatitems: false},
    hidegrid: false,
    postData:{'p_id': id_paccnt},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     //var first_id = parseInt($(this).getDataIDs()[$(this).getDataIDs().length-1]);
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
   },

   onSelectRow: function(rowid) { 
        inpdemand_edit_row_id = rowid; 
    },
/*
    rowattr: function (rd) {
        if (rd.is_corrected == '1')
            return {"style": "color:Brown !important;"};
        
    },    
*/
    ondblClickRow: function(id){ 
      //jQuery(this).editGridRow(id,{width:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
    
    var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 

      //  edit_row_id = id;
      //  $("#fpaccnt_params").find("#pmode").attr('value',0 );
      //  $("#fpaccnt_params").find("#pid_paccnt").attr('value',id );
      //  document.paccnt_params.submit();
        
      } 
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');}

  }).navGrid('#inpdemand_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
        
  jQuery("#inpdemand_table").jqGrid('filterToolbar','');
  
  jQuery("#inpdemand_tablePager_right").css("width","120px");
  jQuery("#inpdemand_tablePager_center").css("width","180px");        
  
  
//------------------------------------------------------------------------------
  
//----------------------------------------------------------------


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
            
            $("#saldo_table").jqGrid('setGridWidth',$pane.innerWidth()-10);
            
            $("#indic_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            $("#bill_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            $("#pay_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            $("#corr_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            $("#inpdemand_table").jqGrid('setGridWidth',$pane.innerWidth()-20);

            $("#indic_table").jqGrid('setGridHeight',$pane.innerHeight()-$("#psaldo_list").outerHeight()-150);
            $("#bill_table").jqGrid('setGridHeight',$pane.innerHeight()-$("#psaldo_list").outerHeight()-150);
            $("#pay_table").jqGrid('setGridHeight',$pane.innerHeight()-$("#psaldo_list").outerHeight()-150);
            $("#corr_table").jqGrid('setGridHeight',$pane.innerHeight()-$("#psaldo_list").outerHeight()-150);
            $("#inpdemand_table").jqGrid('setGridHeight',$pane.innerHeight()-$("#psaldo_list").outerHeight()-150);

        }
        
	});

        
    outerLayout.resizeAll();
    outerLayout.close('south');     
   // innerLayout.hide('north');        
        
    jQuery(".btn").button();
    jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});
    jQuery(".btnRefresh").button({icons: {primary:'ui-icon-refresh'}});
    jQuery("#fBillEdit :input").addClass("ui-widget-content ui-corner-all");
    jQuery("#fPayEdit :input").addClass("ui-widget-content ui-corner-all");
    jQuery("#fRecalcEdit :input").addClass("ui-widget-content ui-corner-all");
    jQuery("#fInpdemandEdit :input").addClass("ui-widget-content ui-corner-all");
   
    $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
    jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

    jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
    jQuery(".dtpicker").mask("99.99.9999");
   
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   $("#message_zone").dialog({autoOpen: false});
   $( "#pTabs" ).tabs({
      //  show: $.layout.callbacks.resizeTabLayout        
    });
    
 //----------------------------------------------------------------------------   
   
    
$("#dialog_billedit").dialog({
			resizable: true,
		//	height:140,
                        width:600,
			modal: true,
                        autoOpen: false,
                        title:"Редагування рахунку"
});

var billedit_form_options = { 
    dataType:"json",
    beforeSubmit: BillEditFormBeforeSubmit, // функция, вызываемая перед передачей 
    success: BillEditFormSubmitResponse // функция, вызываемая при получении ответа
  };

fBillEdit_ajaxForm = $("#fBillEdit").ajaxForm(billedit_form_options);
        
// опции валидатора общей формы
var editform_valid_options = { 

		rules: {
			reg_num: "required",
                        reg_date: "required",
                        idk_doc: "required",
                        value: {required:true,number:true},
                        value_tax: {required:true,number:true}
		},
		messages: {
			reg_num: "Вкажіть номер",
                        reg_date: "Вкажіть дату",
                        idk_doc: "Вкажіть тип",
                        value: {required:"Вкажіть суму",number:"Повинно бути число!"},
                        value_tax: {required:"Вкажіть суму ПДВ",number:"Повинно бути число!"}
		}
};


billedit_validator = $("#fBillEdit").validate(editform_valid_options);


$("#fBillEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_billedit").dialog('close');                           
});
    

//------------------------------------------------------------------------------
jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"Додати",
    id:"btn_bill_add",    
    onClickButton:function(){ 

            billedit_validator.resetForm();  //для сброса состояния валидатора
            $("#fBillEdit").resetForm();
            $("#fBillEdit").clearForm();
          
            //$("#bill_table").jqGrid('GridToForm',gsr,"#fBillEdit"); 
            $("#fBillEdit").find("#fid").attr('value','-1');
            $("#fBillEdit").find("#foper").attr('value','add');
            
            $("#fBillEdit").find("#fid_paccnt").attr('value',id_paccnt);
            
            $("#fBillEdit").find("#fmmgg_b").attr('value',mmgg);
            $("#fBillEdit").find("#fmmgg_bill").attr('value',mmgg);

            $("#fBillEdit").find("#bt_add").show();
            $("#fBillEdit").find("#bt_edit").hide();   
            $("#dialog_billedit").dialog('open');          

    } 
});
//------------------------------------------------------------------------------

jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"Редагувати",
    id:"btn_bill_edit",    
    onClickButton:function(){ 

      if ($("#bill_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#bill_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            billedit_validator.resetForm();  //для сброса состояния валидатора
            $("#fBillEdit").resetForm();
            $("#fBillEdit").clearForm();
          
            $("#bill_table").jqGrid('GridToForm',gsr,"#fBillEdit"); 
            $("#fBillEdit").find("#foper").attr('value','edit');              
            
            flock=$("#bill_table").jqGrid('getCell',bill_row_id,'flock');
            var type= $("#bill_table").jqGrid('getCell',bill_row_id,'idk_doc');
            var value_calc=$("#bill_table").jqGrid('getCell',bill_row_id,'value_calc');
            var demand=$("#bill_table").jqGrid('getCell',bill_row_id,'demand');

            $("#fBillEdit").find("#bt_add").hide();
            $("#fBillEdit").find("#bt_edit").show();   
            $("#dialog_billedit").dialog('open');          
            
            if ((flock=='Yes')||(type==200)||(value_calc!=0))
            {
             $("#fBillEdit").find("#bt_edit").prop('disabled', true);       
            }
        }
    } 
});
//------------------------------------------------------------------------------

jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"Видалити",
        id:"btn_bill_del",    
	onClickButton:function(){ 

      if ($("#bill_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити рахунок?');
      jQuery("#dialog-confirm").css('background-color','white');
      jQuery("#dialog-confirm").css('color','black');
      
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                                      
                                        fBillEdit_ajaxForm[0].id_doc.value = bill_row_id;
                                        fBillEdit_ajaxForm[0].oper.value = 'del';
                                        fBillEdit_ajaxForm.ajaxSubmit(billedit_form_options);   

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

if (r_bill!=3)
{
    $('#btn_bill_del').addClass('ui-state-disabled');
    $('#btn_bill_add').addClass('ui-state-disabled');
    $('#btn_bill_edit').addClass('ui-state-disabled');
}


  jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"Друкувати",
        id:"btn_bill_print",    
	onClickButton:function(){ 

       if((jQuery("#bill_table").jqGrid('getCell',bill_row_id,'idk_doc')!=200)&&
          (jQuery("#bill_table").jqGrid('getCell',bill_row_id,'idk_doc')!=220)||
          (jQuery("#bill_table").jqGrid('getCell',bill_row_id,'value_calc')<0)||
          (jQuery("#bill_table").jqGrid('getCell',bill_row_id,'id_pref')!=10))
       {
           return;
       }   

       //var filters = jQuery("#bill_table").jqGrid('getGridParam', 'postData').filters;
       //var rows = $('#bill_table').getDataIDs();
       //var bills = new Array();
       //bills[0] = bill_row_id;
       
       //var json_str = JSON.stringify(bills);

       //$("#fprint_params").find("#pbill_list").attr('value',json_str ); 

       $("#fprint_params").attr('action', 'bill_print_one.php');
       $("#fprint_params").find("#pid_bill").attr('value',bill_row_id ); 
       
       //$("#fprint_params").find("#pcaption").attr('value', paccnt_info); 
       $("#fprint_params").find("#pcaption").attr('value', paccnt_book+'/'+paccnt_code); 
       
       $("#fprint_params").attr('target',"_blank" );           
       document.print_params.submit();
  
       ;} 
});
    
//------------------------------------------
jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"Деталі",
    id:"btn_bill_info",    
    onClickButton:function(){ 

      if ($("#bill_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#bill_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            jQuery('#bill_info1_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': bill_row_id}}).trigger('reloadGrid');        
            jQuery('#bill_info2_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': bill_row_id}}).trigger('reloadGrid');        
            jQuery('#bill_info3_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': bill_row_id}}).trigger('reloadGrid');        
            jQuery('#bill_info4_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': bill_row_id}}).trigger('reloadGrid');        
            
            $("#bill_info").dialog('option', 'title', 'Рахунок ' + bill_row_id );
            
            $("#bill_info").dialog('open');          
            
        }
    } 
});
//------------------------------------------

jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"Втрати",
    id:"btn_bill_losts",    
    onClickButton:function(){ 

      if ($("#bill_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#bill_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            jQuery('#bill_info5_table').jqGrid('setGridParam',{datatype: 'json','postData':{'id_doc': bill_row_id}}).trigger('reloadGrid');        
            $("#bill_info_lost").dialog('open');          
        }
    } 
});

//------------------------------------------

jQuery("#bill_table").jqGrid('navButtonAdd','#bill_tablePager',{caption:"Субсидія",
    id:"btn_bill_subs",    
    onClickButton:function(){ 

      if ($("#bill_table").getDataIDs().length == 0) 
       {return} ;    
       
        var fmmgg=jQuery("#bill_table").jqGrid('getCell',bill_row_id,'mmgg');
       
       
        $("#fpaccnt_params").find("#pmode").attr('value',0 );
        $("#fpaccnt_params").find("#pid_paccnt").attr('value',id_paccnt );
        $("#fpaccnt_params").find("#ppaccnt_mmgg").attr('value',fmmgg );
        
        $("#fpaccnt_params").attr('target',"_blank" );  
        $("#fpaccnt_params").attr("action","abon_en_subs.php");                    
          
        document.paccnt_params.submit();       

    } 
});

//----------------------------------------------
var payedit_form_options = { 
    dataType:"json",
    beforeSubmit: PayEditFormBeforeSubmit, // функция, вызываемая перед передачей 
    success: PayEditFormSubmitResponse // функция, вызываемая при получении ответа
  };

fPayEdit_ajaxForm = $("#fPayEdit").ajaxForm(payedit_form_options);
        
// опции валидатора общей формы
var payeditform_valid_options = { 

		rules: {
			reg_num: "required",
                        reg_date: "required",
                        payheader: "required",
                        value: {required:true,number:true},
                        value_tax: {required:true,number:true}
		},
		messages: {
			reg_num: "Вкажіть номер",
                        reg_date: "Вкажіть дату",
                        payheader: "Виберіть пачку",
                        value: {required:"Вкажіть суму",number:"Повинно бути число!"},
                        value_tax: {required:"Вкажіть суму ПДВ",number:"Повинно бути число!"}
		}
};


payedit_validator = $("#fPayEdit").validate(payeditform_valid_options);


$("#fPayEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_payedit").dialog('close');                           
});
   
$("#dialog_payedit").dialog({
			resizable: true,
		//	height:140,
                        width:600,
			modal: true,
                        autoOpen: false,
                        title:"Редагування оплати"
});
   
    jQuery("#btPayHeaderSel").click( function() { 
   
        var ww = window.open("payment_operations.php", "paccnt_win", "toolbar=0,width=900,height=600");
        document.payheader_sel_params.submit();
        ww.focus();
    });
   
//------------------------------------------------------------------------------
jQuery("#pay_table").jqGrid('navButtonAdd','#pay_tablePager',{caption:"Додати",
    id:"btn_pay_new",    
    onClickButton:function(){ 

      
            payedit_validator.resetForm();  //для сброса состояния валидатора
            $("#fPayEdit").resetForm();
            $("#fPayEdit").clearForm();
          
            //$("#pay_table").jqGrid('GridToForm',gsr,"#fPayEdit"); 
            $("#fPayEdit").find("#fid").attr('value','-1');
            $("#fPayEdit").find("#fid_paccnt").attr('value',id_paccnt);
            $("#fPayEdit").find("#fbook").attr('value',paccnt_book);
            $("#fPayEdit").find("#fcode").attr('value',paccnt_code);
            $("#fPayEdit").find("#fabon").attr('value',paccnt_name);
            
            //$("#fPayEdit").find("#fid_headpay").attr('value',pay_row_id);
            $("#fPayEdit").find("#foper").attr('value','add');
            
            $("#fPayEdit").find("#fmmgg_p").attr('value',mmgg);
            /*
            var mmgg_h = $("#headers_table").jqGrid('getCell',cur_doc_id,'mmgg_str')
            
            $("#fPayEdit").find("#fmmgg_p").attr('value',mmgg_h);
            $("#fPayEdit").find("#fmmgg_pay").attr('value',mmgg_h);
            $("#fPayEdit").find("#fmmgg_hpay").attr('value',mmgg_h);
*/
            $("#fPayEdit").find("#bt_add").show();
            $("#fPayEdit").find("#bt_edit").hide();   
            $("#dialog_payedit").dialog('open');          
  
    } 
});
//------------------------------------------------------------------------------

jQuery("#pay_table").jqGrid('navButtonAdd','#pay_tablePager',{caption:"Редагувати",
    id:"btn_pay_edit",    
    onClickButton:function(){ 

      if ($("#pay_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#pay_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            $(".paydtpicker").removeClass("readonly");            
            
            payedit_validator.resetForm();  //для сброса состояния валидатора
            $("#fPayEdit").resetForm();
            $("#fPayEdit").clearForm();
          
            $("#fPayEdit").find("#fbook").attr('value',paccnt_book);
            $("#fPayEdit").find("#fcode").attr('value',paccnt_code);
            $("#fPayEdit").find("#fabon").attr('value',paccnt_name);
            
            $("#pay_table").jqGrid('GridToForm',gsr,"#fPayEdit"); 
            $("#fPayEdit").find("#fid_paccnt").attr('value',id_paccnt);
            $("#fPayEdit").find("#foper").attr('value','edit');              

            $("#fPayEdit").find("#bt_add").hide();
            $("#fPayEdit").find("#bt_edit").show();   
            $("#dialog_payedit").dialog('open');    
            
            if ($("#pay_table").jqGrid('getCell',pay_row_id,'flock')=='Yes' )
            {
                $("#fPayEdit").find("#btPayHeaderSel").prop('disabled', true);
                $("#fPayEdit").find("#freg_num").attr('readonly', true);

                $("#fPayEdit").find("#fvalue_tax").attr('readonly', true);
                $("#fPayEdit").find("#fvalue").attr('readonly', true);

                $("#fPayEdit").find("#fidk_doc").addClass("readonly");
                $("#fPayEdit").find("#fidk_doc").css("color","#AAAAAA");

                $(".paydtpicker").addClass("readonly");       
                $(".paydtpicker").datepicker("disable");
                
            }
            else
            {
                $("#fPayEdit").find("#btPayHeaderSel").prop('disabled', false);
                $("#fPayEdit").find("#freg_num").attr('readonly', false);
                $("#fPayEdit").find("#fvalue_tax").attr('readonly', false);
                $("#fPayEdit").find("#fvalue").attr('readonly', false);

                $("#fPayEdit").find("#fidk_doc").removeClass("readonly");
                $("#fPayEdit").find("#fidk_doc").css("color","#000000");

                $(".paydtpicker").removeClass("readonly");
                $(".paydtpicker").datepicker("enable");
            }
            
        }
    } 
});

 $("#fPayEdit").find("#fidk_doc").bind("focus", function(){
        if($(this).hasClass('readonly'))
        {
          $(this).blur();   
          return;
        }
      });

 $(".paydtpicker").bind("focus", function(){
        if($(this).hasClass('readonly'))
        {
          $(this).blur();   
          return;
        }
      });

//------------------------------------------------------------------------------

jQuery("#pay_table").jqGrid('navButtonAdd','#pay_tablePager',{caption:"Видалити",
      id:"btn_pay_del",    
      onClickButton:function(){ 

      if ($("#pay_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити запис?');
      jQuery("#dialog-confirm").css('background-color','white');
      jQuery("#dialog-confirm").css('color','black');
      
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                                      
                                        fPayEdit_ajaxForm[0].id_doc.value = pay_row_id;
                                        fPayEdit_ajaxForm[0].oper.value = 'del';
                                        fPayEdit_ajaxForm.ajaxSubmit(payedit_form_options);   

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

if (r_pay!=3)
{
    $('#btn_pay_new').addClass('ui-state-disabled');
    $('#btn_pay_del').addClass('ui-state-disabled');
    $('#btn_pay_edit').addClass('ui-state-disabled');
}



jQuery("#btPaccntSel").click( function() { 
  /* 
     // $("#fpaccnt_sel_params").attr('target',"_blank" );           
     var ww = window.open("abon_en_main.php", "paccnt_win", "toolbar=0,width=900,height=600");
     document.paccnt_sel_params.submit();
     ww.focus();
     */
        createAbonGrid();
        
        abon_target_id = $("#fPayEdit").find('#fid_paccnt');
        abon_target_name = $("#fPayEdit").find('#fabon');
        abon_target_book = $("#fPayEdit").find('#fbook');
        abon_target_code = $("#fPayEdit").find('#fcode');

       jQuery("#grid_selabon").css({'left': $("#fPayEdit").find('#fbook').offset().left+1, 'top': $("#fPayEdit").find('#fbook').offset().top+20});
       jQuery("#grid_selabon").toggle( );
    
});


$("#fBillEdit").find("#fvalue").bind('input propertychange', function() {
      
   var sum_all = parseFloat($("#fBillEdit").find("#fvalue").val().replace(',', '.'));
   if (sum_all!=0)
       {
           var sum_nds = sum_all/6;
       }
   else    
       {
           var sum_nds = 0;
       }

       $("#fBillEdit").find("#fvalue_tax").attr('value', sum_nds.toFixed(2) );
});


$('#fBillEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fBillEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
 });

$('#fPayEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fPayEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
 });

$("#show_peoples").click( function() {

   jQuery("#indic_table").jqGrid('showCol',["user_name"]);
   jQuery("#indic_table").jqGrid('showCol',["id_meter"]);
   jQuery("#indic_table").jqGrid('showCol',["dt"]);
   jQuery("#bill_table").jqGrid('showCol',["user_name"]);
   jQuery("#pay_table").jqGrid('showCol',["user_name"]);
   jQuery("#corr_table").jqGrid('showCol',["user_name"]);
});

//----------------таблица истории  -------------
var createIndicHistGrid = function(mode){ 
    
  if (mode == 0)   
      indic_hist_param = indic_edit_row_id; 
  else
      indic_hist_param = -1;   
    
  if (isIndicHistGridCreated) 
  {
     jQuery('#indic_history_table').jqGrid('setGridParam',{datatype: 'json','postData':{'p_id': indic_hist_param , 'pid_paccnt': id_paccnt  }}).trigger('reloadGrid');
     return;
  }
  isIndicHistGridCreated =true;


  jQuery('#indic_history_table').jqGrid({
    url:'abon_en_saldo_indic_hist_data.php',
    //editurl: '',
    datatype: 'json',
    mtype: 'POST',
    height:150,
    //width:800,
    autowidth: true,
    shrinkToFit : false,
    scroll: 0,
    colModel :[ 


    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
//    {name:'id_pack', index:'id_pack', width:40, editable: false, align:'center',hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},    
    {name:'id_meter', index:'id_meter', width:40, editable: false, align:'center',hidden:true},    
    {name:'id_typemet', index:'id_typemet', width:40, editable: false, align:'center',hidden:true},    
//    {name:'id_hwork', index:'id_hwork', width:40, editable: false, align:'center',hidden:true},    
//    {name:'idk_work', index:'idk_work', width:40, editable: false, align:'center',hidden:true},    
   
    {label:'№ ліч.',name:'num_eqp', index:'num_eqp', width:80, editable: true, align:'left',edittype:'text'},            
    {label:'Тип ліч.',name:'type_meter', index:'type_meter', width:80, editable: true, align:'left',edittype:'text'},                
    {label:'Розр. ліч.',name:'carry', index:'carry', width:40, editable: true, align:'left',edittype:'text'},                    
    {label:'К.тр',name:'coef_comp', index:'coef_comp', width:40, editable: true, align:'left',edittype:'text'},                        
    {label:'Зона',name:'id_zone', index:'id_zone', width:60, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text'},                       

    {label:'Дата',name:'dat_ind', index:'dat_ind', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
/*
    {label:'Попер.пок.',name:'p_indic', index:'p_indic', width:80, editable: true, align:'right',hidden:false,edittype:'text',
                    formatter:'integer'},           

    {label:'Дата попер.',name:'p_dat_ind', index:'p_dat_ind', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
*/
    {label:'Показники',name:'value', index:'value', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           

    {label:'Спожив.',name:'value_cons', index:'value_cons', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},           

    {label:'Тип показників',name:'id_operation', index:'id_operation', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lindicoper},stype:'text'},                       
//    {label:'№ відомості',name:'num_pack', index:'num_pack', width:60, editable: true, align:'left',edittype:'text'},
//    {label:'Факт.пок.',name:'indic_real', index:'indic_real', width:60, editable: true, align:'left',edittype:'text'},

    {label:'Оператор',name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:false},        

    {label:'Період',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', hidden:false,formatter:'date'},
    {name:'dt', index:'dt', width:80, editable: true, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},

    {label:'Період кор.', name:'mmgg_change',index:'mmgg_change', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
    {label:'dt кор.', name:'dt_change',index:'dt_change', width:80, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
    {label:'Оператор.кор.', name:'user_name_change',index:'user_name_change', width:80, editable: true, align:'left',edittype:'text'}

    ],
    pager: '#indic_history_tablePager',
    rowNum:50,
    sortname: 'dat_ind',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: '',
    pgbuttons: false,
    pgtext: null, 
    hiddengrid: false,
    jsonReader : {repeatitems: false},
    postData:{'p_id': indic_hist_param, 'pid_paccnt': id_paccnt },
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');}

  }).navGrid('#indic_history_tablePager',
        {edit:false,add:false,del:false,search:false}
        ); 
        
};   


$("#dialog-indichistory").dialog({autoOpen: false ,resizable: true, height:253, width:750});

 jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{
        id:"btn_indic_hist",
        caption:"Історія",
        onClickButton:function(){ 

       if ($("#indic_table").jqGrid('getCell',indic_edit_row_id,'ind_mode')==2)
           return;

         createIndicHistGrid(0);
         jQuery("#dialog-indichistory").dialog('open');

        } 
 });

 jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{
        id:"btn_indic_delhist",
        caption:"Видалені",
        onClickButton:function(){ 

         createIndicHistGrid(1);
         jQuery("#dialog-indichistory").dialog('open');

        } 
 });

jQuery("#indic_table").jqGrid('navButtonAdd','#indic_tablePager',{
        caption:"Відомість",
        id:"btn_indic_pack",
	onClickButton:function(){ 


        $("#fpaccnt_params").find("#pid_paccnt").attr('value',id_paccnt );
        
        $("#fpaccnt_params").attr('target',"_blank" );  
        $("#fpaccnt_params").attr("action","ind_packs_input.php");                    
          
        document.paccnt_params.submit();       

     } 
});


   $("#bt_abon").click( function(){ 

          $("#fpaccnt_params").find("#pmode").attr('value',0 );
          
          $("#fpaccnt_params").find("#pid_paccnt").attr('value',id_paccnt );
          
          $("#fpaccnt_params").find("#ppaccnt_info").attr('value',paccnt_info  );
         // $("#fpaccnt_params").attr('target',"_blank" );           
          $("#fpaccnt_params").attr("action","abon_en_paccnt.php");          
          
          document.paccnt_params.submit();

   });


  

//------------------------------------------------------------------------------
jQuery("#inpdemand_table").jqGrid('navButtonAdd','#inpdemand_tablePager',{caption:"Додати",
    id:"btn_inpdemand_new",    
    onClickButton:function(){ 

      
            inpdemand_validator.resetForm();  //для сброса состояния валидатора
            $("#fInpdemandEdit").resetForm();
            $("#fInpdemandEdit").clearForm();
            $("#fInpdemandEdit").find("#fid_paccnt").attr('value',id_paccnt);
          
 //           $("#inpdemand_table").jqGrid('GridToForm',gsr,"#fInpdemandEdit"); 
/*            
            $("#fPayEdit").find("#fid").attr('value','-1');
            $("#fPayEdit").find("#fid_paccnt").attr('value',id_paccnt);
            $("#fPayEdit").find("#fbook").attr('value',paccnt_book);
            $("#fPayEdit").find("#fcode").attr('value',paccnt_code);
            $("#fPayEdit").find("#fabon").attr('value',paccnt_name);
  */          
    
            $("#fInpdemandEdit").find("#foper").attr('value','add');
            $("#fInpdemandEdit").find("#bt_add").show();
            $("#fInpdemandEdit").find("#bt_edit").hide();   
            $("#dialog_inpdemandedit").dialog('open');          
  
    } 
});
//------------------------------------------------------------------------------

jQuery("#inpdemand_table").jqGrid('navButtonAdd','#inpdemand_tablePager',{caption:"Редагувати",
    id:"btn_inpdemand_edit",    
    onClickButton:function(){ 

      if ($("#inpdemand_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#inpdemand_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            inpdemand_validator.resetForm();  //для сброса состояния валидатора
            $("#fInpdemandEdit").resetForm();
            $("#fInpdemandEdit").clearForm();
          
            //$("#fPayEdit").find("#fbook").attr('value',paccnt_book);
            //$("#fPayEdit").find("#fcode").attr('value',paccnt_code);
            //$("#fPayEdit").find("#fabon").attr('value',paccnt_name);
            
            $("#inpdemand_table").jqGrid('GridToForm',gsr,"#fInpdemandEdit"); 
            $("#fInpdemandEdit").find("#fid_paccnt").attr('value',id_paccnt);
            $("#fInpdemandEdit").find("#foper").attr('value','edit');              

            $("#fInpdemandEdit").find("#bt_add").hide();
            $("#fInpdemandEdit").find("#bt_edit").show();   
            $("#dialog_inpdemandedit").dialog('open');    
            
        }
    } 
});


//------------------------------------------------------------------------------

jQuery("#inpdemand_table").jqGrid('navButtonAdd','#inpdemand_tablePager',{caption:"Видалити",
      id:"btn_inpdemand_del",    
      onClickButton:function(){ 

      if ($("#inpdemand_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити запис?');
      jQuery("#dialog-confirm").css('background-color','white');
      jQuery("#dialog-confirm").css('color','black');
      
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                                      
                                        fInpdemandEdit_ajaxForm[0].id.value = inpdemand_edit_row_id;
                                        fInpdemandEdit_ajaxForm[0].oper.value = 'del';
                                        fInpdemandEdit_ajaxForm.ajaxSubmit(inpdemand_form_options);   

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


    jQuery("#inpdemand_table").jqGrid('navButtonAdd','#inpdemand_tablePager',{caption:"Розрахунок",
    id:"btn_inpdemand_bill",
    onClickButton:function(){ 
        
       var gsr = jQuery("#inpdemand_table").jqGrid('getGridParam','selrow'); 
       if(!gsr) return;
       
       flock=jQuery("#inpdemand_table").jqGrid('getCell',inpdemand_edit_row_id,'flock');
       
       var fmmgg=jQuery("#inpdemand_table").jqGrid('getCell',inpdemand_edit_row_id,'mmgg');
       
       if (flock=='Yes')  return;

            if (fmmgg==mmgg)
               {
                 jQuery("#dialog-confirm").find("#dialog-text").html('Формувати рахунок по поточних показниках?');
                 jQuery("#dialog-confirm").css('background-color','white');
                 jQuery("#dialog-confirm").css('color','black');

               }
            else
               {
                 return;
               }
           
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Формування рахунків',
                buttons: {
                    "Формувати": function() {

                        var request = $.ajax({
                            url: "bill_calc_one_edit.php",
                            type: "POST",
                            data: {
                                id_paccnt : id_paccnt,
                                mmgg : fmmgg
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
                                    if (data.id =1 )
                                    {
                                        //alert("Рахунки зформовано!");
                                        $( "[href='#tab_bill']").trigger( "click" );
                                        jQuery('#bill_table').trigger('reloadGrid');                      
                                        jQuery('#saldo_table').trigger('reloadGrid');                      
                                    }
                                    else
                                    {
                                        alert("Помилка при формуванні рахунків!");                        
                                    }
                
                                }
                                else
                                {
                                    jQuery("#message_zone").dialog('open');                                    
                                }
                            }
                        });

                        request.fail(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");
                                $('#message_zone').dialog('open');
                            }
                            else
                            {
                                $('#message_zone').append(data);  
                                $('#message_zone').dialog('open');                                
                            }

        
                        });
                      
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
  
//----------------------------------------------------------------
    jQuery("#inpdemand_table").jqGrid('navButtonAdd','#inpdemand_tablePager',{caption:"Розрах.корегування",
    id:"btn_inpdemand_recalc",
    onClickButton:function(){ 
        
       var gsr = jQuery("#inpdemand_table").jqGrid('getGridParam','selrow'); 
       if(!gsr) return;
       
       flock=jQuery("#inpdemand_table").jqGrid('getCell',inpdemand_edit_row_id,'flock');
       
       var fmmgg=jQuery("#inpdemand_table").jqGrid('getCell',inpdemand_edit_row_id,'mmgg');
       
       //if (flock=='Yes')  return;
       jQuery("#dialog-confirm").find("#dialog-text").html('Увага !!! Ви намагаєтесь зробити перерахунок за попередній період!');
       jQuery("#dialog-confirm").css('background-color','red');
       jQuery("#dialog-confirm").css('color','white');
       
           
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Формування рахунків',
                buttons: {
                    "Формувати": function() {

                        var request = $.ajax({
                            url: "bill_calc_one_edit.php",
                            type: "POST",
                            data: {
                                id_paccnt : id_paccnt,
                                mmgg : fmmgg
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
                                    if (data.id =1 )
                                    {
                                        //alert("Рахунки зформовано!");
                                        $( "[href='#tab_bill']").trigger( "click" );
                                        jQuery('#bill_table').trigger('reloadGrid');                      
                                        jQuery('#saldo_table').trigger('reloadGrid');                      
                                    }
                                    else
                                    {
                                        alert("Помилка при формуванні рахунків!");                        
                                    }
                
                                }
                                else
                                {
                                    jQuery("#message_zone").dialog('open');                                    
                                }
                            }
                        });

                        request.fail(function(data ) {
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");
                                $('#message_zone').dialog('open');
                            }
                            else
                            {
                                $('#message_zone').append(data);  
                                $('#message_zone').dialog('open');                                
                            }

        
                        });
                      
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
  
//----------------------------------------------------------------


/*
if (r_inpdemand!=3)
{
    $('#btn_inpdemand_new').addClass('ui-state-disabled');
    $('#btn_inpdemand_del').addClass('ui-state-disabled');
    $('#btn_inpdemand_edit').addClass('ui-state-disabled');
}
*/
$("#dialog_inpdemandedit").dialog({
			resizable: true,
		//	height:140,
                        width:500,
			modal: true,
                        autoOpen: false,
                        title:"Редагування споживання по зеленому тарифу "
});

var inpdemand_form_options = { 
    dataType:"json",
    beforeSubmit: InpdemandFormBeforeSubmit, // функция, вызываемая перед передачей 
    success: InpdemandFormSubmitResponse // функция, вызываемая при получении ответа
  };

fInpdemandEdit_ajaxForm = $("#fInpdemandEdit").ajaxForm(inpdemand_form_options);
        
// опции валидатора общей формы
var inpdemand_valid_options = { 

		rules: {
                        id_zone: "required",
                        dat_b: "required",    
                        dat_e: "required",
                        demand: {required:true,number:true}
		},
		messages: {
			id_zone: "Вкажіть зону",
                        dat_b: "Вкажіть початкову дату",
                        dat_e: "Вкажіть кінцеву дату",
                        demand: {required:"Вкажіть кВтг",number:"Повинно бути число!"}
		}
};
 

inpdemand_validator = $("#fInpdemandEdit").validate(inpdemand_valid_options);


$("#fInpdemandEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_inpdemandedit").dialog('close');                           
});
    
//-----------------------------------------------------------------------
$("#dialog_recalcedit").dialog({
			resizable: true,
		//	height:140,
                        width:500,
			modal: true,
                        autoOpen: false,
                        title:"Зробити перерахунки за період"
});


var recalc_form_options = { 
    dataType:"json",
    beforeSubmit: RecalcFormBeforeSubmit, // функция, вызываемая перед передачей 
    success: RecalcFormSubmitResponse // функция, вызываемая при получении ответа
  };

fRecalcEdit_ajaxForm = $("#fRecalcEdit").ajaxForm(recalc_form_options);
        
// опции валидатора общей формы
var recalc_valid_options = { 

		rules: {
                        mmgg_begin: "required",    
                        mmgg_end: "required"
		},
		messages: {
                        mmgg_begin: "Вкажіть початковий період",
                        mmgg_end: "Вкажіть кінцевий період"
		}
};
 

recalc_validator = $("#fRecalcEdit").validate(recalc_valid_options);


$("#fRecalcEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_recalcedit").dialog('close');                           
});



});

function BillEditFormBeforeSubmit(formData, jqForm, options) { 

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
function BillEditFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_billedit").dialog('close');                           
               jQuery('#bill_table').trigger('reloadGrid');        
              
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
               
               jQuery('#bill_table').trigger('reloadGrid');        
               jQuery('#saldo_table').trigger('reloadGrid');        
               
               jQuery("#dialog_billedit").dialog('close');                                            
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


// обработчик, который вызываетя перед отправкой формы
function PayEditFormBeforeSubmit(formData, jqForm, options) { 

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
function PayEditFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_payedit").dialog('close');                           
               jQuery('#pay_table').trigger('reloadGrid');  
               jQuery('#saldo_table').trigger('reloadGrid');        
              
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
               
               jQuery('#pay_table').trigger('reloadGrid');    
               jQuery('#saldo_table').trigger('reloadGrid');        
               
               jQuery("#dialog_payedit").dialog('close');                                            
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

// обработчик, который вызываетя перед отправкой формы
function InpdemandFormBeforeSubmit(formData, jqForm, options) { 

    //if (form_edit_lock == 1) return false;
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
           //form_edit_lock=1;
           return true; 
       }
    }
    else {return true;}       
    //}
    
} ;

// обработчик ответа сервера после отправки формы
function InpdemandFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             //form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_inpdemandedit").dialog('close');                           
               jQuery('#inpdemand_table').trigger('reloadGrid');  
              
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {

               jQuery("#dialog_inpdemandedit").dialog('close');                           
               jQuery('#inpdemand_table').trigger('reloadGrid');  

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

// обработчик, который вызываетя перед отправкой формы
function RecalcFormBeforeSubmit(formData, jqForm, options) { 

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
function RecalcFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_recalcedit").dialog('close');                           
               jQuery('#corr_table').trigger('reloadGrid');  
              
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {

               jQuery("#dialog_recalcedit").dialog('close');                           
               jQuery('#corr_table').trigger('reloadGrid');  

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

function SelectPayHeaderExternal(id, reg_date, reg_num, id_origin, mmgg) {
    
        $("#fPayEdit").find('#fid_headpay').attr('value',id );
        
        $("#fPayEdit").find('#fpayheader').attr('value', '№'+reg_num+' від '+reg_date );    
    
        $("#fPayEdit").find("#fmmgg_p").attr('value',mmgg);
        $("#fPayEdit").find("#fmmgg_pay").attr('value',mmgg);
        $("#fPayEdit").find("#fmmgg_hpay").attr('value',mmgg);
    
} 

function SelectPaccntExternal(id, book, code, name, addr) {
    
        $("#fPayEdit").find('#fid_paccnt').attr('value',id );
        $("#fPayEdit").find('#fbook').attr('value',book );    
        $("#fPayEdit").find('#fcode').attr('value',code );    
        $("#fPayEdit").find('#fabon').attr('value',name );    
    
} 

function RefreshMetersExternal(id, name) {
        $("#indic_table").trigger('reloadGrid');                       

}
