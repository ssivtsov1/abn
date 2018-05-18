var edit_row_id=0;
var on_progress=0;
var is_alt = 0;
var is_ctrl = 0;
var form_edit_lock=0;

var form_options = { 
    dataType:"json",
    beforeSubmit: FindFormBeforeSubmit, 
    success: FindFormSubmitResponse 
  };


jQuery(function(){ 

  jQuery('#client_table').jqGrid({
    url:'abon_en_main_data.php',
    editurl: '',
    datatype: 'json',
    mtype: 'POST',
    //height:500,
    //width:800,
    autowidth: true,
    scroll: 0,
    colNames:['Книга','Особ.рахунок','Адреса','Місто','Вулиця','Буд.','Літ.','Корп.','Кв.','Абонент','Прим.','Пільга','Стан','Арх','Код'],
    colModel :[ 
      {name:'book', index:'book', width:50, editable: true, align:'left',edittype:'text'},           
      {name:'code', index:'code', width:50, editable: true, align:'left',edittype:'text'},                 
      {name:'addr', index:'addr', width:200, editable: true, align:'left',edittype:'text', hidden:true},           
      {name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text', hidden:false},
      {name:'street', index:'street', width:100, editable: true, align:'left',edittype:'text'},
      {name:'house', index:'house', width:40, editable: true, align:'left',edittype:'text'},
      {name:'house_letter', index:'house_letter', width:20, editable: true, align:'left',edittype:'text'},
      {name:'korp', index:'korp', width:30, editable: true, align:'left',edittype:'text'},
      {name:'flat', index:'flat', width:40, editable: true, align:'left',edittype:'text'},
      
      {name:'abon', index:'abon', width:200, editable: true, align:'left',edittype:'text'},
      {name:'note', index:'note', width:100, editable: true, align:'left',edittype:'text'},
      {name:'lgt', index:'lgt', width:10, editable: true, align:'left',edittype:'text',stype:'select',searchoptions:{value:': ;П:П'}},
      {name:'action', index:'action', width:50, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lactions},stype:'select'},
      {name:'archive', index:'archive', width:30, editable: false, align:'right',hidden:true,
          formatter:'checkbox',edittype:'checkbox'},
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:false}
                            
    ],
    pager: '#client_tablePager',
    rowNum:100,
    rowList:[50,100,300,500],
    sortname: 'book',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Абоненти',
    postData:{'arch_mode': 0},
    //hiddengrid: false,
    hidegrid: false,

    gridComplete:function(){
    on_progress=0;
    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
   },

   onSelectRow: function(rowid) { 
        edit_row_id = rowid; 
    },

    
    ondblClickRow: function(id){ 

            if(selmode==1)
            {
                window.opener.SelectPaccntExternal(id,jQuery(this).jqGrid('getCell',id,'book'),
                    jQuery(this).jqGrid('getCell',id,'code'),
                    jQuery(this).jqGrid('getCell',id,'abon'),
                    jQuery(this).jqGrid('getCell',id,'addr') );
                window.opener.focus();
                self.close();            
            }

            if(selmode==0)
            {
                var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
                if(gsr)
                { 

                    edit_row_id = id;
                    $("#fpaccnt_params").find("#pmode").attr('value',0 );
                    $("#fpaccnt_params").find("#pid_paccnt").attr('value',id );

                    $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                        $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                        $("#client_table").jqGrid('getCell',edit_row_id,'code')+
                        $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
                    $("#fpaccnt_params").attr('target',"_blank" );           
                    $("#fpaccnt_params").attr("action","abon_en_paccnt.php");                  
                    document.paccnt_params.submit();
        
                } 
            }
        } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');}

  }).navGrid('#client_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

jQuery("#client_table").jqGrid('bindKeys', {"onEnter":function( id ) {  }} );


jQuery('#client_table').jqGrid('navButtonAdd','#client_tablePager',{id:'btn_active', 
  caption:'Активні', title:"Тільки активні абоненти",
	onClickButton:function(){
            if (on_progress==0)
                {
                    on_progress=1;
                    jQuery('#btn_active').addClass('navButton_selected') ;
                    jQuery('#btn_archive').removeClass('navButton_selected') ;
                    jQuery('#btn_all').removeClass('navButton_selected') ;
                    jQuery("#client_table").jqGrid('hideCol',["archive"]);    
                    jQuery('#client_table').jqGrid('setGridParam',{'postData':{'arch_mode':0}}).trigger('reloadGrid');        
                    innerLayout.resizeAll();
                }
        } 
    });        
jQuery('#btn_active').addClass('navButton_selected') ;    
//jQuery('#btn_active').css('border','1px solid #999999');    
//jQuery('#btn_active').css('margin','-1px');    

//jQuery('#btn_active_text').css('font-weight','bold');    
//jQuery('#btn_active').addClass('ui-state-hover');
    
jQuery('#client_table').jqGrid('navButtonAdd','#client_tablePager',{id:'btn_archive',caption:"Архів", title:"Тільки архів",
	onClickButton:function(){ 
            if (on_progress==0)
                {
                    on_progress=1;
            
                    jQuery('#btn_active').removeClass('navButton_selected') ;
                    jQuery('#btn_archive').addClass('navButton_selected') ;
                    jQuery('#btn_all').removeClass('navButton_selected') ;
                    jQuery("#client_table").jqGrid('hideCol',["archive"]);                                        
                    jQuery('#client_table').jqGrid('setGridParam',{'postData':{'arch_mode':1}}).trigger('reloadGrid');                    
                    innerLayout.resizeAll();
                }
         } 
    });        
jQuery('#client_table').jqGrid('navButtonAdd','#client_tablePager',{
    id:'btn_all',   caption:"Всі",    title:"Показати всіх абонентів",
    onClickButton:function(){ 
        if (on_progress==0)
        {
            on_progress=1;
            jQuery('#btn_active').removeClass('navButton_selected') ;
            jQuery('#btn_archive').removeClass('navButton_selected') ;
            jQuery('#btn_all').addClass('navButton_selected') ;
            jQuery("#client_table").jqGrid('showCol',["archive"]);                            
            jQuery('#client_table').jqGrid('setGridParam',{'postData':{'arch_mode':2}}).trigger('reloadGrid');                    
            innerLayout.resizeAll();
        }
    } 
});        

//если нажат Альт - открываем карточку вместо поиска
//если нажат Контрол - открываем сальдо вместо поиска
jQuery("#client_table").jqGrid('filterToolbar',{
    afterSearch:function () {
//        alert('Filter Complete');            
    },
    beforeSearch :function () { 
        if ((is_alt==1)||(is_ctrl==1))
        {
          if(selmode==0)
          {
            var gsr = jQuery('#client_table').jqGrid('getGridParam','selrow'); 
            if(gsr)
            { 
                $("#fpaccnt_params").find("#pmode").attr('value',0 );
                $("#fpaccnt_params").find("#pid_paccnt").attr('value',edit_row_id );

                if (town_hidden==true)
                {
                   $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                    $("#client_table").jqGrid('getCell',edit_row_id,'code')+' '+
                    $("#client_table").jqGrid('getCell',edit_row_id,'addr')+' -  '+                    
                    $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
                }
                else
                {
                   $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                    $("#client_table").jqGrid('getCell',edit_row_id,'code')+' '+
                    $("#client_table").jqGrid('getCell',edit_row_id,'town')+' '+                                        
                    $("#client_table").jqGrid('getCell',edit_row_id,'addr')+' -  '+                    
                    $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
                }                
                
                $("#fpaccnt_params").find("#ppaccnt_book").attr('value',
                     $("#client_table").jqGrid('getCell',edit_row_id,'book') );

                $("#fpaccnt_params").find("#ppaccnt_code").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'code') );

                $("#fpaccnt_params").find("#ppaccnt_name").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'abon') );
                
                
                $("#fpaccnt_params").attr('target',"_blank" );           
                
                if (is_alt==1)
                    {
                      $("#fpaccnt_params").attr("action","abon_en_paccnt.php");                  
                      is_alt=0;
                    }  
                if (is_ctrl==1)
                    {
                      is_ctrl=0;
                      $("#fpaccnt_params").attr("action","abon_en_saldo.php");                                    
                    }
                
                document.paccnt_params.submit();
        
            } 
          }
            
            return true;
        }
    }
});

 // Binding keys

  $(document).bind('keydown', 'Alt+return', function Alt_Enter() {
        if(selmode==0)
        {
            var gsr = jQuery('#client_table').jqGrid('getGridParam','selrow'); 
            if(gsr)
            { 
                $("#fpaccnt_params").find("#pmode").attr('value',0 );
                $("#fpaccnt_params").find("#pid_paccnt").attr('value',edit_row_id );

                $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                    $("#client_table").jqGrid('getCell',edit_row_id,'code')+' '+
                    $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
                
                $("#fpaccnt_params").find("#ppaccnt_book").attr('value',
                     $("#client_table").jqGrid('getCell',edit_row_id,'book') );

                $("#fpaccnt_params").find("#ppaccnt_code").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'code') );

                $("#fpaccnt_params").find("#ppaccnt_name").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'abon') );
                
                
                $("#fpaccnt_params").attr('target',"_blank" );           
                $("#fpaccnt_params").attr("action","abon_en_paccnt.php");                  
                document.paccnt_params.submit();
        
            }  
        }
        return false;
  });
  
  $(document).bind('keydown', 'Ctrl+return', function Ctrl_Enter() {
        if(selmode==0)
        {
            var gsr = jQuery('#client_table').jqGrid('getGridParam','selrow'); 
            if(gsr)
            { 
                $("#fpaccnt_params").find("#pmode").attr('value',0 );
                $("#fpaccnt_params").find("#pid_paccnt").attr('value',edit_row_id );

                if (town_hidden==true)
                {
                   $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                    $("#client_table").jqGrid('getCell',edit_row_id,'code')+' '+
                    $("#client_table").jqGrid('getCell',edit_row_id,'addr')+' -  '+                    
                    $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
                }
                else
                {
                   $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                    $("#client_table").jqGrid('getCell',edit_row_id,'code')+' '+
                    $("#client_table").jqGrid('getCell',edit_row_id,'town')+' '+                                        
                    $("#client_table").jqGrid('getCell',edit_row_id,'addr')+' -  '+                    
                    $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
                }                
                
                $("#fpaccnt_params").find("#ppaccnt_book").attr('value',
                     $("#client_table").jqGrid('getCell',edit_row_id,'book') );

                $("#fpaccnt_params").find("#ppaccnt_code").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'code') );

                $("#fpaccnt_params").find("#ppaccnt_name").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'abon') );
                
                
                $("#fpaccnt_params").attr('target',"_blank" );           
                $("#fpaccnt_params").attr("action","abon_en_saldo.php");                  
                document.paccnt_params.submit();
        
            } 
        }
        return false;
  });
  
  
  $(".ui-search-toolbar input").bind('keydown', 'Alt', function AltDown() {
        is_alt = 1;
  });


  $(".ui-search-toolbar input").bind('keyup', 'Alt', function AltUp() {
        is_alt=0;
  });

  $(".ui-search-toolbar input").bind('keydown', 'Ctrl', function CtrlDown() {
        is_ctrl = 1;
  });


  $(".ui-search-toolbar input").bind('keyup', 'Ctrl', function CtrlUp() {
        is_ctrl = 0;
        //alert('ctrl up');            
  });

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
	});

    innerLayout = $("#pmain_content").layout({
		name:			"inner" 
	,	north__paneSelector:	"#pwork_header"
	,	north__closable:	true
	,	north__resizable:	true
        ,	north__size:		150
        ,	center__paneSelector:	"#pwork_grid"
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, $pane, state, options) 
        {
            jQuery("#client_table").jqGrid('setGridWidth',$pane.innerWidth()-9);
            jQuery("#client_table").jqGrid('setGridHeight',$pane.innerHeight()-142);
        }
        
	});
        
    innerLayout.hide('north');     
    outerLayout.close('south');     
    
    if(selmode==1)
    {
       outerLayout.hide('north');
       jQuery("#pActionBar").hide();
       
    }
    innerLayout.resizeAll();
    
    jQuery(".btn").button();
    jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});
        
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   
   $("#message_zone").dialog({autoOpen: false});
   
   
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_add").click( function(){ 

        $("#fpaccnt_params").find("#pmode").attr('value',1 );
        $("#fpaccnt_params").find("#pid_paccnt").attr('value',0 );

        $("#fpaccnt_params").attr('target',"_blank" );           
        $("#fpaccnt_params").attr("action","abon_en_paccnt.php");          
        
        document.paccnt_params.submit();
   });
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_edit").click( function(){ 

        if ($('#client_table').getDataIDs().length > 0) 
        {
          $("#fpaccnt_params").find("#pmode").attr('value',0 );
          
          $("#fpaccnt_params").find("#pid_paccnt").attr('value',edit_row_id );
          $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
            $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
            $("#client_table").jqGrid('getCell',edit_row_id,'code')+'  '+
            $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
          $("#fpaccnt_params").attr('target',"_blank" );           
          $("#fpaccnt_params").attr("action","abon_en_paccnt.php");          
          
          document.paccnt_params.submit();
        }
   });
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_saldo").click( function(){ 

        if ($('#client_table').getDataIDs().length > 0) 
        {
          $("#fpaccnt_params").find("#pmode").attr('value',0 );
          $("#fpaccnt_params").find("#pid_paccnt").attr('value',edit_row_id );
          
                if (town_hidden==true)
                {
                   $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                    $("#client_table").jqGrid('getCell',edit_row_id,'code')+' '+
                    $("#client_table").jqGrid('getCell',edit_row_id,'addr')+' -  '+                    
                    $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
                }
                else
                {
                   $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                    $("#client_table").jqGrid('getCell',edit_row_id,'code')+' '+
                    $("#client_table").jqGrid('getCell',edit_row_id,'town')+' '+                                        
                    $("#client_table").jqGrid('getCell',edit_row_id,'addr')+' -  '+                    
                    $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
                }                
        
          $("#fpaccnt_params").find("#ppaccnt_book").attr('value',
                $("#client_table").jqGrid('getCell',edit_row_id,'book') );

          $("#fpaccnt_params").find("#ppaccnt_code").attr('value',
                $("#client_table").jqGrid('getCell',edit_row_id,'code') );

          $("#fpaccnt_params").find("#ppaccnt_name").attr('value',
                $("#client_table").jqGrid('getCell',edit_row_id,'abon') );

          $("#fpaccnt_params").attr('target',"_blank" );  
          $("#fpaccnt_params").attr("action","abon_en_saldo.php");                    
          
          document.paccnt_params.submit();
        }
   });
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_plan").click( function(){ 

        if ($('#client_table').getDataIDs().length > 0) 
        {
          $("#fpaccnt_params").find("#pmode").attr('value',0 );
          $("#fpaccnt_params").find("#pid_paccnt").attr('value',edit_row_id );
          
                if (town_hidden==true)
                {
                   $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                    $("#client_table").jqGrid('getCell',edit_row_id,'code')+' '+
                    $("#client_table").jqGrid('getCell',edit_row_id,'addr')+' -  '+                    
                    $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
                }
                else
                {
                   $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                    $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                    $("#client_table").jqGrid('getCell',edit_row_id,'code')+' '+
                    $("#client_table").jqGrid('getCell',edit_row_id,'town')+' '+                                        
                    $("#client_table").jqGrid('getCell',edit_row_id,'addr')+' -  '+                    
                    $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
                }                
        
          $("#fpaccnt_params").attr('target',"_blank" );  
          $("#fpaccnt_params").attr("action","abon_en_plandem.php");                    
          
          document.paccnt_params.submit();
        }
   });
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_switch").click( function(){ 

        if ($('#client_table').getDataIDs().length > 0) 
        {
          $("#fpaccnt_params").find("#pmode").attr('value',0 );
          $("#fpaccnt_params").find("#pid_paccnt").attr('value',edit_row_id );
          
        if (town_hidden==true)
        {
            $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                $("#client_table").jqGrid('getCell',edit_row_id,'code')+' '+
                $("#client_table").jqGrid('getCell',edit_row_id,'addr')+' -  '+                    
                $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
        }
        else
        {
            $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                $("#client_table").jqGrid('getCell',edit_row_id,'code')+' '+
                $("#client_table").jqGrid('getCell',edit_row_id,'town')+' '+                                        
                $("#client_table").jqGrid('getCell',edit_row_id,'addr')+' -  '+                    
                $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
        }                
        
          $("#fpaccnt_params").attr('target',"_blank" );  
          $("#fpaccnt_params").attr("action","abon_en_switch.php");                    
          
          document.paccnt_params.submit();
        }
   });
   //-------------------------------------------------------------

   $("#pActionBar").find("#bt_bills").click( function(){ 

          $("#fpaccnt_params").find("#pmode").attr('value',0 );
          $("#fpaccnt_params").find("#pid_paccnt").attr('value',edit_row_id );
          $("#fpaccnt_params").attr('target',"_blank" );  
          $("#fpaccnt_params").attr("action","abon_en_bills.php");                    
          
          document.paccnt_params.submit();
   });
   //-------------------------------------------------------------

   $("#pActionBar").find("#bt_find").click( function(){ 
    
    jQuery('#fpaccnt_serch').find('#fnum_meter').attr('value','' );
    jQuery('#serch_result_text').html('');
    innerLayout.toggle('north');     
       
   });
   //-------------------------------------------------------------
/*
  $("#pActionBar").find("#bt_abons").click( function(){ 
        window.open ('dov_abon.php','_self',false)
   });
*/
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_subs").click( function(){ 

        if ($('#client_table').getDataIDs().length > 0) 
        {
          $("#fpaccnt_params").find("#pmode").attr('value',0 );
          $("#fpaccnt_params").find("#pid_paccnt").attr('value',edit_row_id );
          
        if (town_hidden==true)
        {
            $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                $("#client_table").jqGrid('getCell',edit_row_id,'code')+' '+
                $("#client_table").jqGrid('getCell',edit_row_id,'addr')+' -  '+                    
                $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
        }
        else
        {
            $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                $("#client_table").jqGrid('getCell',edit_row_id,'book')+'/'+
                $("#client_table").jqGrid('getCell',edit_row_id,'code')+' '+
                $("#client_table").jqGrid('getCell',edit_row_id,'town')+' '+                                        
                $("#client_table").jqGrid('getCell',edit_row_id,'addr')+' -  '+                    
                $("#client_table").jqGrid('getCell',edit_row_id,'abon')      );
        }                
        
          $("#fpaccnt_params").attr('target',"_blank" );  
          $("#fpaccnt_params").attr("action","abon_en_subs_recalc_manual.php");                    
          
          document.paccnt_params.submit();
        }
   });
//-------------------------------------------------------------

  var Find_ajaxForm = $("#fpaccnt_serch").ajaxForm(form_options);
  
  
  if (r_newabon==3)
      $("#pActionBar").find("#bt_add").prop('disabled', false);
  else
      $("#pActionBar").find("#bt_add").prop('disabled', true);
  
});

function FindFormBeforeSubmit(formData, jqForm, options) { 

    if (form_edit_lock == 1) return false;
    submit_form = jqForm;

    var queryString = $.param(formData);     
    $('#message_zone').append('Вот что мы передаем:' + queryString);  
    $('#message_zone').append("<br>");                 
    /*
    var btn = '';
    for (var i=0; i < formData.length; i++) { 
        if (formData[i].name =='submitButton') { 
           btn= formData[i].value; 
           submit_form[0].oper.value = btn;
        }  
    } 
*/
    return true;       
        
} ;

// обработчик ответа сервера после отправки формы
function FindFormSubmitResponse(responseText, statusText) 
{
             errorInfo = responseText;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==1) {
                 
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               jQuery('#serch_result_text').html(errorInfo.errstr);              
               jQuery('#client_table').jqGrid('setGridParam',{'postData':{'arch_mode': 0, 'id':errorInfo.id, 'user_search':true}}).trigger('reloadGrid');        
               jQuery('#presult_cnt').attr('value', parseFloat(jQuery('#presult_cnt').attr('value'))+1);
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               //jQuery('#message_zone').dialog('open');
               
               jQuery('#serch_result_text').html(errorInfo.errstr);
               jQuery('#presult_cnt').attr('value','0' );
               return [false,errorInfo.errstr]
             };   

             if (errorInfo.errcode==-1) {
                 
               jQuery('#serch_result_text').html('');              
               jQuery('#client_table').jqGrid('setGridParam',{'postData':{'arch_mode': 0, 'user_search':false}}).trigger('reloadGrid');        
               jQuery('#presult_cnt').attr('value','0' );
               
               return [true,errorInfo.errstr]};              

};
