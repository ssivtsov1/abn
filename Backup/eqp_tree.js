var tree_mode=0; // 0 - edit; 1 - new ; 2- move ; 3-del ; 5 new tree
var node_ovner= null;
var cur_node= null;
var del_node= null;
var cur_meter_zone_id = null;
var cur_meter_id;
var cur_eqp_id = 0;
var cur_abon_id = 0;

$(function(){ 


$("#message_zone").dialog({autoOpen: false});

$("#dialog-changedate").dialog({ 
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
			buttons: {
				"Ok": function() {
                                        SaveChanges();
					$( this ).dialog( "close" );
				},
				"Відмінити": function() {
                                        CancelChanges();
					$( this ).dialog( "close" );
				}
			}

});

$("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
$("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
$("#debug_ls3").click( function() {jQuery("#message_zone").html('');});

jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});

$("#pActionBar").find("#bt_ok").click( function(){ 
   jQuery("#dialog-changedate").dialog('open');
});
//--------------------------------------------------------
$("#pActionBar").find("#bt_cancel").click( function(){ 
    CancelChanges();
});
//----------------------------------------------------------
$("#pActionBar").find("#bt_ok").hide();
$("#pActionBar").find("#bt_cancel").hide();

$.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
jQuery(".dtpicker").mask("99.99.9999");


jQuery(":input").addClass("ui-widget-content ui-corner-all");


 var form_options = { 
    dataType:"json",
    beforeSubmit: MainBeforeSubmit, // функция, вызываемая перед передачей 
    success: MainSubmitResponse // функция, вызываемая при получении ответа
  };

$("#fCommonParam").ajaxForm(form_options);
//$("#fMeterParam").ajaxForm(form_options);

$("#fCommonParam").find("#bt_reset").click( function() 
{
    validator_common.resetForm();
    ResetJQFormVal($("#fCommonParam"));

});

$("#fCommonParam").find("#bt_canceladd").click( function(){ 
    tree_mode=0;

    validator_common.resetForm();  //для сброса состояния валидатора
    $("#fCommonParam").resetForm();
    $("#fCommonParam").clearForm();
    CommitJQFormVal($("#fCommonParam"));
    
     $("#equipment_table").trigger('reloadGrid');                       
    //$("#pTreePanel").dynatree("getTree").reload();
    
    $("#fCommonParam").find("#bt_add").hide();
    $("#fCommonParam").find("#bt_edit").show();            
    $("#fCommonParam").find("#bt_reset").show();            
    $("#fCommonParam").find("#bt_canceladd").hide();
    $("#lui_equipment_table" ).hide(); // disable grid    
   //////////// $("#pTreePanel").dynatree("enable");
    
});

var types_list_hidden = true;

$("#pActionBar").find("#bt_new").click( function() { 
    if (types_list_hidden )
      { 
         //if (cur_node!==null)
         //{
          $("#types_list").slideDown(100);
          types_list_hidden = false;
         //} 
      }
    else
      { 
        $("#types_list").slideUp(100);
        types_list_hidden = true;
      }
        
});
//-----------------------------------------------------------------
$("#pActionBar").find("#bt_refresh").hide();
$("#pActionBar").find("#bt_refresh").click( function() {
    
    //$("#pTreePanel").dynatree("reload");
    //$("#pTreePanel").dynatree("getTree").reload();
    //$("#pTreePanel").dynatree("enable");
});

//-----------------------------------------------------------------
/*
$("#pActionBar").find("#bt_newtree").click( function() {
    
    var node = $("#pTreePanel").dynatree("getRoot");

    var childNode = node.addChild({title: "*Нова гілка*",key:"new_tree", eqp_type:-1});              
    node.expand(true);
    childNode.select();
    childNode.activate(); 
    node_ovner=null;
    
    $("#pActionBar").find("#bt_ok").show();
    $("#pActionBar").find("#bt_cancel").show();
    tree_mode=5;
});
*/

//-----------------------------------------------------------------
$("#pActionBar").find("#bt_del").click( function() {
    
    
    //var node = $("#pTreePanel").dynatree("getActiveNode");
    if( cur_eqp_id!=0 ){
    
      jQuery("#dialog-changedate").find("#dialog-text").html('Видалити обладнання?');
    
      tree_mode = 3;
    
       jQuery("#dialog-changedate").dialog('open');
        
    }
});

//-----------------------------------------------------------------
$('#types_list li').click(function() {   // новое оборудование - тип выбран
          //alert ($(this).attr('data-key'));  
          $("#types_list").slideUp(100);
          types_list_hidden = true;
          var new_eqp_type = $(this).attr('data-key');

          //var node = $("#pTreePanel").dynatree("getActiveNode");
          //if( node ){
 
            if(new_eqp_type==12)
            {
                /*
                jQuery("#dialog-confirm").find("#dialog-text").html("Створити новий облік чи під'єднати існуючий?");
    
                $("#dialog-confirm").dialog({
                    resizable: false,
                    height:140,
                    modal: true,
                    autoOpen: false,
                    title:'Новий облік',
                    buttons: {
                        "Новий": function() {
                                        
                            var childNode = node.addChild({
                                title: "*Новое оборудование*",
                                key:"new_eqp", 
                                eqp_type:new_eqp_type
                            });              
                            node.expand(true);
                            node_ovner=node;            
                            tree_mode =1;              
                            childNode.select();
                            childNode.activate(); // далее сработает метод OnActivate для нового узла
         
                            $("#fCommonParam").find("#bt_add").show();
                            $("#fCommonParam").find("#bt_edit").hide();            
                            $("#fCommonParam").find("#bt_reset").hide();            
                            $("#fCommonParam").find("#bt_canceladd").show();
                                   
                                   
                            $( this ).dialog( "close" );
                        },
                        "Існуючий": function() {
                            
                                $("#paccnt_meters_list").dialog({
                                resizable: false,
                                width:650,
                                modal: true,
                                autoOpen: false,
                                title:'Виберіть облік',
                                buttons: {
                                    "Вибрати": function() {
                                        
                                        if ($("#paccnt_meters_table").getDataIDs().length != 0) 
                                        {
                                           
                                            var childNode = node.addChild({
                                                title: "*Новий облік*",
                                                key:"new_eqp+", 
                                                eqp_type:new_eqp_type
                                            });              
                                            node.expand(true);
                                            node_ovner=node;            
                                            tree_mode =1;              
                                            childNode.select();
                                            childNode.activate(); // далее сработает метод OnActivate для нового узла
         
                                            $("#fCommonParam").find("#bt_add").show();
                                            $("#fCommonParam").find("#bt_edit").hide();            
                                            $("#fCommonParam").find("#bt_reset").hide();            
                                            $("#fCommonParam").find("#bt_canceladd").show();

                                            
                                        }
                                        $( this ).dialog( "close" );
                                    },
                                    "Відмінити": function() {
                            
                                        $( this ).dialog( "close" );
                                    }
                                }
                            });
    
                            jQuery("#paccnt_meters_list").dialog('open');

                            
                            $( this ).dialog( "close" );
                        }
                    }
                });
    
                jQuery("#dialog-confirm").dialog('open');
                */
            }
            else
            
            {
                /*
                var childNode = node.addChild({
                    title: "*Новое оборудование*",
                    key:"new_eqp", 
                    eqp_type:new_eqp_type
                });              
                node.expand(true);
                node_ovner=node;            
                tree_mode =1;              
                childNode.select();
                childNode.activate(); // далее сработает метод OnActivate для нового узла
               */
              
                jQuery("#fCommonParam").show();                                
                var request = $.ajax({
                  url: "eqp_tree_getmain.php",
                  type: "POST",
                  data: {id : 'new_eqp', eqp_type : new_eqp_type},
                  dataType: "json"});

                request.done(function(data ) {
                    LoadEqpData(data);
                    });
                request.fail(function(data ) {alert("error");});
              
              
                $("#fCommonParam").find("#bt_add").show();
                $("#fCommonParam").find("#bt_edit").hide();            
                $("#fCommonParam").find("#bt_reset").hide();            
                $("#fCommonParam").find("#bt_canceladd").show();
                $("#lui_equipment_table" ).show(); // disable grid
            }
         // }
});

//-----------------------------------------------------
// опции валидатора общей формы
var common_valid_options = { 

		rules: {
			name_eqp: "required",
			dt_install: "required",
                        type_compens: "required",
                        type_switch: "required",
                        type_corde: "required",
                        type_cable: "required",
                        type_fuse: "required",
                        length_cable:{required: true,
                               number:true},
                        length_corde:{required: true,
                               number:true
                              },
                        id_voltage_cable: "required",
                        id_voltage_corde: "required",
			type_meter: "required",
			num_meter: "required",
			carry:{required: true,
                               number:true
                              }
		},
		messages: {
			name_eqp: "Вкажіть назву обладнання!",
			dt_install: "Вкажіть дату встановлення обладнання!",
                        type_compens: "Вкажіть тип!",
                        type_switch: "Вкажіть тип!",
                        type_corde:"Вкажіть тип!",
                        type_cable:"Вкажіть тип!",
                        type_fuse:"Вкажіть тип!",
                        length_cable:{required: "Вкажіть довжину!",
                               number:"Потрібре число!"},
                        length_corde:{required: "Вкажіть довжину!",
                               number:"Потрібре число!"},
                        id_voltage_cable: "Вкажіть напругу!",
                        id_voltage_corde: "Вкажіть напругу!",
			type_meter: "Виберіть тип лічильника!",
			num_meter: "Вкажіть номер лічильника",
			carry:{required: "Вкажіть розрядність лічильника",
                                number:"Повинно бути число!"
                              } 
                        
                        
		}
};

// опции валидатора формы учета
/*
var meter_valid_options = { 

		rules: {
			type_meter: "required",
			num_meter: "required",
			carry:{required: true,
                               number:true
                              }
		},
		messages: {
			type_meter: "Виберіть тип лічильника!",
			num_meter: "Вкажіть номер лічильника",
			carry:{required: "Вкажіть розрядність лічильника",
                                number:"Повинно бути число!"
                              } 
		}
};
*/
var validator_common = $("#fCommonParam").validate(common_valid_options);
//var validator_meter = $("#fMeterParam").validate(meter_valid_options);
//var validator_common = $("#fCommonParam").validate(meter_valid_options);

//$('body').layout({ applyDefaultStyles: true });
    outerLayout = $("body").layout({
		name:	"outer" 
	,	north__paneSelector:	"#pmain_header"
	,	north__closable:	false
	,	north__resizable:	false
        ,	north__size:		30
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
   //     ,	center__onresize:   $.layout.callbacks.resizeTabLayout        
	});
    

$( "#pPanel2" ).tabs();

var EditorLayout = $('#pmain_content').layout({
    
            north__paneSelector:	"#pActionBar"
	,   north__closable:            false
	,   north__resizable:           false
        ,   north__spacing_open:         2   
        ,   north__showOverflowOnHover:	true
        ,   south__paneSelector:	"#pFooterBar"            
        ,   south__size:		20
	,   south__closable:            false
	,   south__resizable:           false
        ,   south__spacing_open:	2        
        ,   west__paneSelector:         "#pTreePanel"  
        ,   center__paneSelector:	"#pPanel2"
        ,   west__size:                 350	
    
	,   center__onresize:	function (pane, $pane, state, options) {
            
            jQuery("#hist1_table").jqGrid('setGridWidth',$pane.innerWidth()-20);
            jQuery("#hist3_table").jqGrid('setGridWidth',$pane.innerWidth()-20);   
            jQuery("#abon_table").jqGrid('setGridWidth',$pane.innerWidth()-20);   

            if(jQuery("#hist2compens_div").is(":visible") )
            {
               jQuery("#hist2compens_table").jqGrid('setGridWidth',$pane.innerWidth()-20);   
            }
            if(jQuery("#hist2switch_div").is(":visible") )
            {
               jQuery("#hist2switch_table").jqGrid('setGridWidth',$pane.innerWidth()-20);   
            }
            if(jQuery("#hist2linea_div").is(":visible") )
            {
               jQuery("#hist2linea_table").jqGrid('setGridWidth',$pane.innerWidth()-20);   
            }
            if(jQuery("#hist2linec_div").is(":visible") )
            {
               jQuery("#hist2linec_table").jqGrid('setGridWidth',$pane.innerWidth()-20);   
            }
            if(jQuery("#hist2meter_div").is(":visible") )
            {
               jQuery("#hist2meter_table").jqGrid('setGridWidth',$pane.innerWidth()-20);   
            }

            if(jQuery("#hist2fuse_div").is(":visible") )
            {
               jQuery("#hist2fuse_table").jqGrid('setGridWidth',$pane.innerWidth()-20);   
            }

            if(jQuery("#paccnt_meter_zones").is(":visible") )
            {
               jQuery("#paccnt_meter_zones_table").jqGrid('setGridWidth',jQuery("#paccnt_meter_zones").innerWidth());   
            }

        }
,   west__onresize:	function (pane, $pane, state, options) {
            
            jQuery("#equipment_table").jqGrid('setGridWidth',$pane.innerWidth()-15);

        }        

});

//EditorLayout.sizePane("west", 350);
EditorLayout.resizeAll();
outerLayout.close('south');     

$.ajaxSetup({type: "POST",   dataType: "json"}); 
//--------------------------------------------------------------------
  jQuery('#equipment_table').jqGrid({
    url:     'eqp_equipment_data.php',
    //editurl: 'abon_en_paccnt_lgt_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:250,
    width:200,
    autowidth: true,
    scroll: 0,
    colNames:[], 
    colModel :[  
      {label:'id',name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {label:'id_paccnt',name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center', hidden:true},
      {label:'type_eqp',name:'type_eqp', index:'type_eqp', width:40, editable: false, align:'center', hidden:true},
      {label:'Обладнання',name:'name_eqp', index:'name_eqp', width:150, editable: false, align:'left', hidden:false},
      {label:'Порядок',name:'lvl', index:'lvl', width:40, editable: false, align:'center', hidden:false}
    ],
    pager: '#equipment_tablePager',
    rowNum:100,
    sortname: 'lvl',
    sortorder: 'asc',
    
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    viewrecords: false,       
    
    gridview: true,
    caption: '',
    hidegrid: false,
    postData:{'p_id': id_acc },
    jsonReader : {repeatitems: false},
 
    onSelectRow: function(id) { 
      cur_eqp_id = id;  
      
      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
          
           edit_eqp_type = $(this).jqGrid('getCell',cur_eqp_id,'type_eqp')
           
           $("#fCommonParam").show();                                
           var request = $.ajax({
                  url: "eqp_tree_getmain.php",
                  type: "POST",
                  data: {id : cur_eqp_id, eqp_type : edit_eqp_type},
                  dataType: "json"});

                request.done(function(data ) {
                    LoadEqpData(data);
                    });
                request.fail(function(data ) {alert("error");});

   
      }
      
    },

        
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');},
  
  gridComplete:function(){

   var ids = $(this).jqGrid('getDataIDs'); 
    
    if (ids.length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
    else
    {
         $("#fCommonParam").hide();        
    }        
    
  }

  }).navGrid('#equipment_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
        
 jQuery("#equipment_tablePager_center").hide();
 jQuery("#equipment_tablePager_right").hide();        
//---------------------------------------------------------------------        

  jQuery('#abon_table').jqGrid({
    url:     'eqp_abon_data.php',
    //editurl: 'abon_en_paccnt_lgt_edit.php',
    datatype: 'local',
    mtype: 'POST',
    height:300,
    width:200,
    autowidth: true,
    scroll: 0,
    colNames:[], 
    colModel :[  
      {label:'id',name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {label:'code_eqp',name:'code_eqp', index:'code_eqp', width:40, editable: false, align:'center', hidden:true},
      {label:'id_paccnt',name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center', hidden:true},

      {label:'Книга',name:'book', index:'book', width:50, editable: true, align:'left',edittype:'text'},           
      {label:'Рахунок',name:'code', index:'code', width:50, editable: true, align:'left',edittype:'text'},                 
      {label:'Адреса',name:'addr', index:'addr', width:200, editable: true, align:'left',edittype:'text'},
      {label:'Абонент',name:'abon', index:'abon', width:200, editable: true, align:'left',edittype:'text'},
    ],
    pager: '#abon_tablePager',
    rowNum:100,
    sortname: 'book',
    sortorder: 'asc',
    
    pgbuttons: true,     // disable page control like next, back button
    //pgtext: null,         // disable pager text like 'Page 0 of 10'
    viewrecords: true,       
    
    gridview: true,
    caption: '',
    hidegrid: false,
    postData:{'eqp_id': cur_eqp_id },
    jsonReader : {repeatitems: false},
 
    onSelectRow: function(id) { 
      cur_abon_id = id;  
      
      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
//          
      }
      
    },

        
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');},
  
  gridComplete:function(){

   var ids = $(this).jqGrid('getDataIDs'); 
    
    if (ids.length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
     
    
  }

  }).navGrid('#abon_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
        


jQuery("#abon_table").jqGrid('navButtonAdd','#abon_tablePager',{caption:"Додати",
    id:"btn_abon_new",
    onClickButton:function(){ 

        createAbonGrid();
        
        abon_target_id = -1;

       jQuery("#grid_selabon").css({'left': 200, 'top': 100});
       jQuery("#grid_selabon").toggle( );

    } 
});

//------------------------------------------------------------------------------
jQuery("#abon_table").jqGrid('navButtonAdd','#abon_tablePager',{caption:"Видалити",
    id:"btn_abon_del",
    onClickButton:function(){ 

    if ($("#abon_table").getDataIDs().length == 0) 
       {return} ;    
    jQuery("#dialog-confirm").find("#dialog-text").html("Відключити абонента від обладнання?");
    $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                                      
                                    var request = $.ajax({
                                        url: "eqp_tree_oper.php",
                                        type: "POST",
                                        data: {
                                            operation: 'meter_disconnect', id: cur_abon_id  
                                        },
                                        
                                       dataType: "json"
                                       });

                                    request.done(function(data ) {
            
                                        if (data.errcode!==undefined)
                                        {
                                            $('#message_zone').append(data.errstr);  
                                            $('#message_zone').append("<br>");                 
                    
                                            if(data.errcode==-1) 
                                            {
                                                jQuery('#abon_table').jqGrid('setGridParam',{datatype: 'json','postData':{'eqp_id':cur_eqp_id}}).trigger('reloadGrid');
                                            }
                                        }
            
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


//---------------------------------------------------------------------        
        
/*
$("#pTreePanel").dynatree({
          initAjax: {url: "eqp_tree_data.php",
                      data: {id_paccnt: id_acc, 
                      mode: "all"
                      }
          },

          persist: false,
          selectMode: 1,
          imagePath: "images/icons/",
          
          fx: {height: "toggle", duration: 150},
          autoCollapse: false,
          onDblClick: function(node, event) {
              if ((tree_mode==0)||((tree_mode==5)&&(node == cur_node)))
              {    
                editNode(node);
              }  
              return false;
          },

          onQueryActivate : function(isTrue,node) {
              if((tree_mode==0)||(node.data.key=="new_eqp")||(node.data.key=="new_eqp+"))
                  {
                      return true;
                  }
                  else
                  {
                      return false;
                  }    
          },
          onActivate: function( node) {   
              if (node.data.eqp_type==-1)
              {
                jQuery("#fCommonParam").hide();              
              }
              else
              {
                jQuery("#fCommonParam").show();                                
                var request = $.ajax({
                  url: "eqp_tree_getmain.php",
                  type: "POST",
                  data: {id : node.data.key, eqp_type : node.data.eqp_type, id_point: cur_meter_id},
                  dataType: "json"});

                request.done(function(data ) {
                    LoadEqpData(data);
                    });
                request.fail(function(data ) {alert("error");});
              } 
              cur_node = node ;
          },
          onKeydown: function(node, event) {
                return false;},
          onCreate: function(node, span){
           // bindContextMenu(span);
          },
          onPostInit:function(isReloading, isError)
          {
              //alert(this.getRoot().countChildren())  ;
              
            this.getRoot().visit(function(node){
                    node.expand(true);});
             
            jQuery("#fCommonParam").hide();                            
            // if(this.getRoot().countChildren()>1 ) 
            //     {
            //      child = this.getRoot().getChildren();
            //      child[0].activate();
            //     }
                 
          },
          dnd: {
            preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
            autoExpandMS: 1000,
            onDragStart: function(node) {
              if(tree_mode!=0)
                return false;
              else    
                return true;
            },
            onDragEnter: function(node, sourceNode) {
              return "over";
            },
            onDragOver: function(node, sourceNode, hitMode) {

               // Prevent dropping a parent below it's own child
              if(node.isDescendantOf(sourceNode)){
              return false;
              }
              return true;
            },
         
            onDrop: function(node, sourceNode, hitMode, ui, draggable) {
              if(!(node.isDescendantOf(sourceNode))){
                 sourceNode.move(node, hitMode);
                 node.expand(true);
                 
                 $("#pActionBar").find("#bt_ok").show();
                 $("#pActionBar").find("#bt_cancel").show();
                 
                 node_ovner=node;
                 cur_node=sourceNode;                 
                 tree_mode=2;
                 ////////////$("#pTreePanel").dynatree("disable");
              }  
            }
          }
          
 });
 */
////////////////////////////////////////////// 
    jQuery("#btAddrSel").click( function() { 
        //var ww = window.open("Dov_meters.php", "addr_win", "toolbar=0,width=800,height=600");
        //document.addr_sel_params.submit();
        SelectAdrTarget='#faddr';
        SelectAdrStrTarget='#faddr_str';

        $("#fadr_sel_params_address").attr('value', $("#fCommonParam").find("#faddr").val() );    
    
        // $("#fadr_sel_params").attr('target',"_blank" );           
        var ww = window.open("adr_tree_selector.php", "adr_win", "toolbar=0,width=770,height=500");
        document.adr_sel_params.submit();
        ww.focus();
   
    });

 // выбор счетчика из выпадающего грида
jQuery("#show_mlist").click( function() {

    createMeterGrid(jQuery("#fid_type_meter").val());
    meter_target_id=jQuery("#fid_type_meter");
    meter_target_name = jQuery("#ftype_meter");
    meter_target_carry = jQuery("#fcarry");

    jQuery("#grid_selmeter").css({'left': jQuery("#ftype_meter").offset().left+1, 'top': jQuery("#ftype_meter").offset().top+20});
    jQuery("#grid_selmeter").toggle( );
});
// выбор тр. тока 
jQuery("#show_compa").click( function() {

    compi_target_id=jQuery("#fid_typecompa");
    compi_target_name = jQuery("#ftypecompa");
    
    createCompIGrid(); 
    jQuery("#grid_selci").css({'left': $(this).position().left+1, 'top': $(this).position().top+20});
    jQuery("#grid_selci").toggle( );
});

//выбор тр. напряжения
jQuery("#show_compu").click( function() {

    compi_target_id=jQuery("#fid_typecompu");
    compi_target_name = jQuery("#ftypecompu");
    
    createCompIGrid(); 
    jQuery("#grid_selci").css({'left': $(this).position().left+1, 'top': $(this).position().top+20});
    jQuery("#grid_selci").toggle( );
});

jQuery("#show_cablelist").click( function() {

    createCableGrid();
    cable_target_id=jQuery("#fid_type_cable");
    cable_target_name = jQuery("#ftype_cable");

    jQuery("#grid_selcable").css({'left': $(this).position().left+1, 'top': $(this).position().top+20});
    jQuery("#grid_selcable").toggle( );
});

jQuery("#show_cordelist").click( function() {

    createCordeGrid();
    corde_target_id=jQuery("#fid_type_corde");
    corde_target_name = jQuery("#ftype_corde");

    jQuery("#grid_selcorde").css({'left': $(this).position().left+1, 'top': $(this).position().top+20});
    jQuery("#grid_selcorde").toggle( );
});

jQuery("#show_switchlist").click( function() {

    createSwitchGrid();
    switch_target_id=jQuery("#fid_type_switch");
    switch_target_name = jQuery("#ftype_switch");

    jQuery("#grid_selswitch").css({'left': $(this).position().left+1, 'top': $(this).position().top+20});
    jQuery("#grid_selswitch").toggle( );
});

jQuery("#show_fuselist").click( function() {

    createFuseGrid();
    fuse_target_id=jQuery("#fid_type_fuse");
    fuse_target_name = jQuery("#ftype_fuse");

    jQuery("#grid_selfuse").css({'left': $(this).position().left+1, 'top': $(this).position().top+20});
    jQuery("#grid_selfuse").toggle( );
});

jQuery("#show_compenslist").click( function() {

    createCompensGrid();
    compens_target_id=jQuery("#fid_type_compens");
    compens_target_name = jQuery("#ftype_compens");

    jQuery("#grid_selcompens").css({'left': $(this).position().left+1, 'top': $(this).position().top+20});
    jQuery("#grid_selcompens").toggle( );
});

jQuery("#show_tplist").click( function() {

    createTpGrid();
    tp_target_id=jQuery("#fid_station");
    tp_target_name = jQuery("#fstation");

    jQuery("#grid_seltp").css({'left': jQuery("#fstation").offset().left+1, 'top': jQuery("#fstation").offset().top+20});
    jQuery("#grid_seltp").toggle( );
});

// Таблици истории
  jQuery('#hist1_table').jqGrid({
    url:'eqp_hist1.php',
    //editurl: '',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:100,
    //width:800,
    //autowidth: true,
    shrinkToFit: false,
    scroll: 0,
    colNames:['','Код','Назва','Номер', 'Адреса','Рах.втрати','Власн.','Дата уст.','Дата зміни','Дата поч.','Дата кінц.','Користувач','mmgg','dt'],
    colModel :[ 
      {name:'id_key', index:'id_key', width:40, editable: false, align:'center', key:true,hidden:true},
      {name:'id', index:'id', width:40, editable: false, align:'center'},
      {name:'name_eqp', index:'name_eqp', width:150, editable: true, align:'left',edittype:'text'},
      {name:'num_eqp', index:'num_eqp', width:40, editable: true, align:'left',edittype:'text'},
      {name:'addr_str', index:'addr_str', width:100, editable: true, align:'left',edittype:'text'},
      {name:'loss_power', index:'loss_power', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox'},
      {name:'is_owner', index:'is_owner', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox'},
      {name:'dt_install', index:'dt_install', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'dt_change', index:'dt_change', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text'},
      {name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text'},
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}
    ],
  /*  pager: '#hist1_tablePager', */
    rowNum:50,
    sortname: 'dt_b',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Основні параметри',
    hiddengrid: false,
    postData:{'p_id':0},
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  });/*.navGrid('#hist1_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        );  */


  jQuery('#hist3_table').jqGrid({
    url:'eqp_hist3.php',
    //editurl: '',
//    datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:100,
    //width:800,
    autowidth: true,
    scroll: 0,
    colNames:['id','Код гілки','Код попер.', 'Назва попер.','Рівень','Дата поч.','Дата кінц.','Користувач','mmgg','dt'],
    colModel :[ 
      {name:'id_key', index:'id_key', width:40, editable: false, align:'center', key:true,hidden:true},
      {name:'id_tree', index:'id_tree', width:50, editable: true, align:'left',edittype:'text'},
      {name:'code_eqp_e', index:'code_eqp_e', width:50, editable: true, align:'left',edittype:'text'},      
      {name:'name_eqp_e', index:'name_eqp_e', width:150, editable: true, align:'left',edittype:'text'},
      {name:'lvl', index:'lvl', width:50, editable: true, align:'left',edittype:'text'},            
      
      {name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text'},
      {name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text'},
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}
    ],
  /*  pager: '#hist3_tablePager', */
    rowNum:50,
    sortname: 'dt_b',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Дерево',
    hiddengrid: false,
    postData:{'p_id':0},
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }); /*.navGrid('#hist3_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        );  */

  jQuery('#hist2switch_table').jqGrid({
    url:'eqp_hist_switch.php',
    //editurl: '',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:100,
    //width:800,
    autowidth: true,
    scroll: 0,
    colNames:['id','Тип','Код типу', 'Дата поч.','Дата кінц.','Користувач','mmgg','dt'],
    colModel :[ 
      {name:'id_key', index:'id_key', width:40, editable: false, align:'center', key:true,hidden:true},
      {name:'type_eqp', index:'type_eqp', width:150, editable: true, align:'left',edittype:'text'},
      {name:'id_type_eqp', index:'id_type_eqp', width:50, editable: true, align:'left',edittype:'text'},      
      
      {name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text'},
      {name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text'},
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}
    ],
   /* pager: '#hist2switch_tablePager',*/
    rowNum:50,
    sortname: 'dt_b',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Комутаційне обладнання',
    hiddengrid: false,
    postData:{'p_id':0},
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  });
  /*.navGrid('#hist2switch_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); */

  jQuery('#hist2compens_table').jqGrid({
    url:'eqp_hist_compens.php',
    //editurl: '',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:100,
    //width:800,
    autowidth: true,
    scroll: 0,
    colNames:['id','Тип','Код типу', 'Дата поч.','Дата кінц.','Користувач','mmgg','dt'],
    colModel :[ 
      {name:'id_key', index:'id_key', width:40, editable: false, align:'center', key:true,hidden:true},
      {name:'type_eqp', index:'type_eqp', width:150, editable: true, align:'left',edittype:'text'},
      {name:'id_type_eqp', index:'id_type_eqp', width:50, editable: true, align:'left',edittype:'text'},      
      
      {name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text'},
      {name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text'},
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}
    ],
   /* pager: '#hist2switch_tablePager',*/
    rowNum:50,
    sortname: 'dt_b',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Трансформатор',
    hiddengrid: false,
    postData:{'p_id':0},
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  });

  jQuery('#hist2linea_table').jqGrid({
    url:'eqp_hist_linea.php',
    //editurl: '',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:100,
    //width:800,
    autowidth: true,
    scroll: 0,
    colNames:['id','Тип провода','Код типу провода','Довжина','Напруга','Тип опори', 'Дата поч.','Дата кінц.','Користувач','mmgg','dt'],
    colModel :[ 
      {name:'id_key', index:'id_key', width:40, editable: false, align:'center', key:true,hidden:true},
      {name:'type_eqp', index:'type_eqp', width:150, editable: true, align:'left',edittype:'text'},
      {name:'id_type_eqp', index:'id_type_eqp', width:50, editable: true, align:'left',edittype:'text'},      
      
      {name:'length', index:'length', width:60, editable: true, align:'left',edittype:'text'},      
      {name:'voltage', index:'voltage', width:60, editable: true, align:'left',edittype:'text'},            
      {name:'pillar', index:'pillar', width:60, editable: true, align:'left',edittype:'text'},                  
      
      {name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text'},
      {name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text'},
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}
    ],
   /* pager: '#hist2switch_tablePager',*/
    rowNum:50,
    sortname: 'dt_b',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Повітряна лінія',
    hiddengrid: false,
    postData:{'p_id':0},
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  });

  jQuery('#hist2linec_table').jqGrid({
    url:'eqp_hist_linec.php',
    //editurl: '',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:100,
    //width:800,
    autowidth: true,
    scroll: 0,
    colNames:['id','Тип кабеля','Код типу кабеля','Довжина','Напруга', 'Дата поч.','Дата кінц.','Користувач','mmgg','dt'],
    colModel :[ 
      {name:'id_key', index:'id_key', width:40, editable: false, align:'center', key:true,hidden:true},
      {name:'type_eqp', index:'type_eqp', width:150, editable: true, align:'left',edittype:'text'},
      {name:'id_type_eqp', index:'id_type_eqp', width:50, editable: true, align:'left',edittype:'text'},      
      
      {name:'length', index:'length', width:60, editable: true, align:'left',edittype:'text'},      
      {name:'voltage', index:'voltage', width:60, editable: true, align:'left',edittype:'text'},            
      
      {name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text'},
      {name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text'},
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}
    ],
   /* pager: '#hist2switch_tablePager',*/
    rowNum:50,
    sortname: 'dt_b',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Кабельна лінія',
    hiddengrid: false,
    postData:{'p_id':0},
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  });

  jQuery('#hist2meter_table').jqGrid({
    url:'eqp_hist_meter.php',
    //editurl: '',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:100,
    //width:800,
    autowidth: true,
    shrinkToFit : false,
    scroll: 0,
    colModel :[ 
      {name:'id_key', index:'id_key', width:40, editable: false, align:'center', key:true,hidden:true},

      {label:'Номер',name:'num_meter', index:'num_meter', width:100, editable: true, align:'left',edittype:'text'},           

      {label:'id типа',name:'id_type_meter', index:'id_type_meter', width:40, editable: false, align:'center', hidden:true},                       

      {label:'Тип',name:'type_meter', index:'type_meter', width:200, editable: true, align:'left',edittype:'text'},           
      {label:'Розрядів',name:'carry', index:'carry', width:50, editable: true, align:'right',hidden:false,
                           edittype:'text',formatter:'integer'},           
      
      {label:'Дата пов.ліч.',name:'dt_control', index:'dt_control', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {label:'Дата пов.тр.струму',name:'dt_control_ca', index:'dt_control_ca', width:80, editable: true, align:'left',edittype:'text',formatter:'date', hidden:true},
      {label:'Дата пов.тр.напр.',name:'dt_control_cu', index:'dt_control_cu', width:80, editable: true, align:'left',edittype:'text',formatter:'date', hidden:true},

      {label:'Потужність',name:'power', index:'power', width:80, align:'right',hidden:false, edittype:'text',formatter:'number'},           

      {label:'id тр.струму',name:'id_typecompa', index:'idtype_compa', width:40, editable: false, align:'center', hidden:true},                             
      {label:'Тр.струму',name:'typecompa', index:'typecompa', width:200, editable: true, align:'left',edittype:'text', hidden:true},           
      {label:'id тр. напруги',name:'id_typecompu', index:'idtype_compu', width:40, editable: false, align:'center', hidden:true},                                   
      {label:'Тр.напруги',name:'typecompu', index:'typecompu', width:200, editable: true, align:'left',edittype:'text', hidden:true},           
      {label:'К.тр',name:'coef_comp', index:'coef_comp', width:50, editable: true, align:'right',hidden:false,
                           edittype:'text',formatter:'integer', hidden:true},           
      
      {label:'ТП',name:'station', index:'station', width:150, editable: true, align:'left',edittype:'text'},                 

      {label:'Втрат.',name:'calc_losts', index:'calc_losts', width:40, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox'},
      {label:'Неопал.',name:'smart', index:'smart', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox', hidden:true},
      {label:'Магніт',name:'magnet', index:'magnet', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox', hidden:true},

      //{label:'Дата встан.',name:'dt_start', index:'dt_start', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      //{label:'Дата демонт.',name:'dt_end', index:'dt_end', width:80, editable: true, align:'left',edittype:'text',formatter:'date'} ,
      {name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'period_open', index:'period_open', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'dt_open', index:'dt_open', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {name:'user_name_open', index:'user_name_open', width:100, editable: true, align:'left',edittype:'text'},

      {name:'period_close', index:'period_close', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'dt_close', index:'dt_close', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {name:'user_name_close', index:'user_name_close', width:100, editable: true, align:'left',edittype:'text'},

    ],
   /* pager: '#hist2switch_tablePager',*/
    rowNum:50,
    sortname: 'dt_b',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Облік',
    hiddengrid: false,
    postData:{'p_id':0},
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  });
  
  jQuery('#hist2fuse_table').jqGrid({
    url:'eqp_hist_fuse.php',
    //editurl: '',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:100,
    //width:800,
    autowidth: true,
    scroll: 0,
    colNames:['id','Тип','Код типу', 'Дата поч.','Дата кінц.','Користувач','mmgg','dt'],
    colModel :[ 
      {name:'id_key', index:'id_key', width:40, editable: false, align:'center', key:true,hidden:true},
      {name:'type_eqp', index:'type_eqp', width:150, editable: true, align:'left',edittype:'text'},
      {name:'id_type_eqp', index:'id_type_eqp', width:50, editable: true, align:'left',edittype:'text'},      
      
      {name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text'},
      {name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text'},
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}
    ],
   /* pager: '#hist2switch_tablePager',*/
    rowNum:50,
    sortname: 'dt_b',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Запобіжник',
    hiddengrid: false,
    postData:{'p_id':0},
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  });

//--------------------счетчики для выбора -----------
/*
  jQuery('#paccnt_meters_table').jqGrid({
    url:     'abon_en_paccnt_meters_data.php',
    editurl: 'abon_en_paccnt_meters_edit.php',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:100,
    width:600,
    autowidth: true,
    scroll: 0,
    colNames:[], 
    colModel :[  
      {label:'id',name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},     
      {label:'ccnt',name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center', hidden:true},           
      {label:'id_station',name:'id_station', index:'id_station', width:40, editable: false, align:'center', hidden:true},                 
      {label:'id_extra',name:'id_extra', index:'id_extra', width:40, editable: false, align:'center', hidden:true},                 
      {label:'id_type_meter',name:'id_type_meter', index:'id_type_meter', width:40, editable: false, align:'center', hidden:true},                       
      {label:'id_typecompa',name:'id_typecompa', index:'idtype_compa', width:40, editable: false, align:'center', hidden:true},                             
      {label:'id_typecompu',name:'id_typecompu', index:'idtype_compu', width:40, editable: false, align:'center', hidden:true},                                   
      {label:'code_eqp',name:'code_eqp', index:'code_eqp', width:40, editable: false, align:'center', hidden:true},                       

      {label:'Номер',name:'num_meter', index:'num_meter', width:100, editable: true, align:'left',edittype:'text'},           

      {label:'Тип',name:'type_meter', index:'type_meter', width:200, editable: true, align:'left',edittype:'text'},           
      {label:'Розрядів',name:'carry', index:'carry', width:50, editable: true, align:'right',hidden:false,
                           edittype:'text',formatter:'integer'},           
      
      {label:'Дата повірки ліч.',name:'dt_control', index:'dt_control', width:80, editable: true, align:'left',edittype:'text',formatter:'date', hidden:true},
      {label:'Дата повірки тр. струму',name:'dt_control_ca', index:'dt_control_ca', width:80, editable: true, align:'left',edittype:'text',formatter:'date', hidden:true},
      {label:'Дата повірки тр. напр.',name:'dt_control_cu', index:'dt_control_cu', width:80, editable: true, align:'left',edittype:'text',formatter:'date', hidden:true},

      {label:'Потужність',name:'power', index:'power', width:80, align:'right',hidden:false, edittype:'text',formatter:'number'},           

      {label:'Тр.струму',name:'typecompa', index:'typecompa', width:200, editable: true, align:'left',edittype:'text', hidden:true},           
      {label:'Тр.напруги',name:'typecompu', index:'typecompu', width:200, editable: true, align:'left',edittype:'text', hidden:true},           
      {label:'К.тр',name:'coef_comp', index:'coef_comp', width:50, editable: true, align:'right',hidden:false,
                           edittype:'text',formatter:'integer', hidden:true},           
      
      {label:'ТП',name:'station', index:'station', width:150, editable: true, align:'left',edittype:'text',hidden:true},                 

      {label:'Втрат.',name:'calc_losts', index:'calc_losts', width:40, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox', hidden:true},
      {label:'Неопал.',name:'smart', index:'smart', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox', hidden:true},
      {label:'Індикатор магніта',name:'magnet', index:'magnet', width:30, editable: true, align:'right',
                            formatter:'checkbox',edittype:'checkbox', hidden:true},

      {label:'Дата встан.',name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
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
    hiddengrid: false,
    postData:{'p_id': id_acc, 'free_only': 1},
    jsonReader : {repeatitems: false},
 
    onSelectRow: function(id) { 
      cur_meter_id = id;  
      
    },

    ondblClickRow: function(id){ 
       // corde_target_id.val(jQuery(this).jqGrid('getCell',lastSelCorde,'id') ); 
       // corde_target_name.val(jQuery(this).jqGrid('getCell',lastSelCorde,'name') );
       // corde_target_name.focus();
       // jQuery('#grid_selcorde').toggle( );
    } ,  
        
    loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');},
  
    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
  }

  }).navGrid('#paccnt_meters_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

*/

//---------------------------------------------


//-----------------------Зоны счетчика -----------------
/*
jQuery('#paccnt_meter_zones_table').jqGrid({
    url:'abon_en_paccnt_meters_zone_data.php',
    editurl: 'abon_en_paccnt_meters_zone_edit.php',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:80,
    //width:220,
    autowidth: true,
    shrinkToFit : false,
    
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
    caption: 'Зони',
    hiddengrid: false,
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
            }

        });
        jQuery("#dialog-newmeterzone").dialog('open');
          
    } 
});

 jQuery("#paccnt_meter_zones_table").jqGrid('navButtonAdd','#paccnt_meter_zones_tablePager',{caption:"Видалити зону",
	onClickButton:function(){ 

      if ($("#paccnt_meter_zones_table").getDataIDs().length == 0) 
       {return} ;    

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити зону?');
    
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
        } 
});
*/

//$("#pActionBar").hover(
//	function(){ $(this).addClass('ui-state-hover'); },
//	function(){ $(this).removeClass('ui-state-hover'); }
//);



//--------------------------------------------------------------------------
/*
function editNode(node){
  var prevTitle = node.data.title,
    tree = node.tree;
  // Disable dynatree mouse- and key handling
  tree.$widget.unbind();
  // Replace node with <input>
  $(".dynatree-title", node.span).html("<input id='editNode' value='" + prevTitle + "'>");
  // Focus <input> and bind keyboard handler
  $("input#editNode")
    .focus()
    .keydown(function(event){
      switch( event.which ) {
      case 27: // [esc]
        // discard changes on [esc]
        $("input#editNode").val(prevTitle);
        $(this).blur();
        break;
      case 13: // [enter]
        // simulate blur to accept new value
        $(this).blur();
        break;
      }
    }).blur(function(event){
      // Accept new value, when user leaves <input>
      var title = $("input#editNode").val();
      node.setTitle(title);
      
      //----------------------------
      if ((title!=prevTitle)&&(tree_mode!=5))
      {    
       var request;
     
       $.ajaxSetup({
       url: "eqp_tree_oper.php",
       type: "POST",
       dataType: "json"});

       request = $.ajax({data: {id : cur_node.data.key,new_name: title, eqp_type: node.data.eqp_type,operation: "node_rename"}});

       request.done(function(data ) {  
        
        if (data.errcode!==undefined)
        {
            $('#message_zone').append(data.errstr);  
            $('#message_zone').append("<br>");                 
            //jQuery("#message_zone").dialog('open');
        }
       });
       request.fail(function(data ) {alert("error");});
      }
      //----------------------------------------------------------
      
      // Re-enable mouse and keyboard handlling
      tree.$widget.bind();
      node.focus();
    });
};
*/
function ResetJQFormVal(form)
{
  form.find('[data_old_value]').each(function() {
        var vlastValue = $(this).attr('data_old_value');
        $(this).attr('value',vlastValue);
        $(this).focus();
  });
        
  form.find('[data_old_checked]').each(function() {
        var vlastValue = $(this).attr('data_old_checked');
        //alert(vlastValue);
        if (vlastValue=='true')
        {
          $(this).prop('checked',true);
        }
        else
        {
          $(this).prop('checked',false);
        }    
    
 });
};

function CommitJQFormVal(form)
{
   form.find('[data_old_value]').each(function() {
            var vlastValue = $(this).attr('value');
             $(this).attr('data_old_value',vlastValue);  
             //alert($(this).attr('data_old_value'));             
   });
        
   form.find('[data_old_checked]').each(function() {
            var vlastValue = $(this).prop('checked');
             $(this).attr('data_old_checked',vlastValue);  
   });    
};

function LoadEqpData(data)
{
  //   var str = $.param(data); 
  //alert(str); 
  if (data.errcode===undefined)
  {    
    $("#fCommonParam").resetForm();
    $("#fCommonParam").clearForm();
    
    $("#fCommonParam").find("[data_old_value]").attr('value',''); 
    $("#fCommonParam").find("[data_old_value]").attr('data_old_value',''); 
    
    
      
    //jQuery('#hist2switch_div').hide();      
    jQuery('#hist2compens_div').hide();          
    jQuery('#hist2linea_div').hide();          
    jQuery('#hist2linec_div').hide();          
    //jQuery('#hist2meter_div').hide();          
    //jQuery('#hist2fuse_div').hide();          
      
    $("#fCommonParam").find("#leqptype").html(data.typename);  
    $("#fCommonParam").find("#fid").attr('value',data.id );
    //$("#fCommonParam").find("#fid_paccnt").attr('value',data.id_paccnt );
    $("#fCommonParam").find("#fid_paccnt").attr('value',id_acc );
    //$("#fCommonParam").find("#fid_client").attr('value',id_client );    
    $("#fCommonParam").find("#fname_eqp").attr('value', data.name_eqp);
    $("#fCommonParam").find("#fnum_eqp").attr('value', data.num_eqp);
    $("#fCommonParam").find("#ftype_eqp").attr('value',data.type_eqp );
    $("#fCommonParam").find("#faddr").attr('value', data.addr);
    $("#fCommonParam").find("#flvl").attr('value', data.lvl);
    $("#fCommonParam").find("#faddr_str").attr('value', data.addr_str);
    $("#fCommonParam").find("#fdt_install").datepicker( "setDate" , data.dt_install_str );
    $("#fCommonParam").find("#fdt_change").datepicker( "setDate" , data.dt_change_str );
                    
    //if (tree_mode==1)                
    /*
    if((cur_node.data.key=="new_eqp")||(cur_node.data.key=="new_eqp+")) // 
    {
     $("#fCommonParam").find("#fcode_eqp_e").attr('value',node_ovner.data.key );
     $("#fCommonParam").find("#fid_tree").attr('value',node_ovner.data.id_tree );    
    }
    else
    {
     $("#fCommonParam").find("#fid_tree").attr('value',cur_node.data.id_tree );    
    }
    */
    if (data.loss_power==1)
    {
      $("#fCommonParam").find("#floss_power").prop('checked',true);
    }
    else
    {
      $("#fCommonParam").find("#floss_power").prop('checked',false);
    }
    
    if (data.is_owner==1)
    {
      $("#fCommonParam").find("#fis_owner").prop('checked',true);
    }
    else
    {
      $("#fCommonParam").find("#fis_owner").prop('checked',false);
    }

    //if (data.id_type_compens!==undefined)
    if (data.type_eqp==2)
    {
        $("#fCommonParam").find("#fid_type_compens").attr('value',data.id_type_compens );        
        $("#fCommonParam").find("#ftype_compens").attr('value',data.type_compens );        
        
        jQuery('#hist2compens_div').show();
        jQuery('#hist2compens_table').jqGrid('setGridParam',{datatype: 'json','postData':{'p_id':data.id}}).trigger('reloadGrid');        
        
    }

    //if (data.id_type_switch!==undefined)
    /*
    if (data.type_eqp==3)
    {
        $("#fCommonParam").find("#fid_type_switch").attr('value',data.id_type_switch );        
        $("#fCommonParam").find("#ftype_switch").attr('value',data.type_switch );        
        
        jQuery('#hist2switch_div').show();
        jQuery('#hist2switch_table').jqGrid('setGridParam',{'postData':{'p_id':data.id}}).trigger('reloadGrid');        
    }

    if (data.type_eqp==5)
    {
        $("#fCommonParam").find("#fid_type_fuse").attr('value',data.id_type_fuse );        
        $("#fCommonParam").find("#ftype_fuse").attr('value',data.type_fuse );        
        
        jQuery('#hist2fuse_div').show();
        jQuery('#hist2fuse_table').jqGrid('setGridParam',{'postData':{'p_id':data.id}}).trigger('reloadGrid');        
    }
    */
    //if (data.id_type_cable!==undefined)
    if (data.type_eqp==6)
    {
        $("#fCommonParam").find("#fid_type_cable").attr('value',data.id_type_cable );        
        $("#fCommonParam").find("#ftype_cable").attr('value',data.type_cable );        
        
        $("#fCommonParam").find("#flength_cable").attr('value',data.length_cable );        
        $("#fCommonParam").find("#fid_voltage_cable").attr('value',data.id_voltage_cable );        
        
        jQuery('#hist2linec_div').show();
        jQuery('#hist2linec_table').jqGrid('setGridParam',{datatype: 'json','postData':{'p_id':data.id}}).trigger('reloadGrid');        
        
    }

    //if (data.id_type_corde!==undefined)
    if (data.type_eqp==7)
    {
        $("#fCommonParam").find("#fid_type_corde").attr('value',data.id_type_corde );        
        $("#fCommonParam").find("#ftype_corde").attr('value',data.type_corde );        
        
        $("#fCommonParam").find("#flength_corde").attr('value',data.length_corde );        
        $("#fCommonParam").find("#fid_voltage_corde").attr('value',data.id_voltage_corde );        
        $("#fCommonParam").find("#fid_pillar").attr('value',data.id_pillar );        

        jQuery('#hist2linea_div').show();
        jQuery('#hist2linea_table').jqGrid('setGridParam',{datatype: 'json','postData':{'p_id':data.id}}).trigger('reloadGrid');        

    }

    //if (data.id_type_meter!==undefined)
    /*
    if (data.type_eqp==12)
    {
        $("#fCommonParam").find("#fid_mp").attr('value',data.id_mp );                
        $("#fCommonParam").find("#fid_type_meter").attr('value',data.id_type_meter );        
        $("#fCommonParam").find("#ftype_meter").attr('value',data.type_meter );        

        $("#fCommonParam").find("#fid_typecompa").attr('value',data.id_typecompa );        
        $("#fCommonParam").find("#ftypecompa").attr('value',data.typecompa );        

        $("#fCommonParam").find("#fid_typecompu").attr('value',data.id_typecompu );        
        $("#fCommonParam").find("#ftypecompu").attr('value',data.typecompu );  
        
        $("#fCommonParam").find("#fnum_meter").attr('value',data.num_meter );  
        $("#fCommonParam").find("#fcarry").attr('value',data.carry );  
        
        $("#fCommonParam").find("#fdt_control").datepicker( "setDate" , data.dt_control );
        $("#fCommonParam").find("#fdt_control_ca").datepicker( "setDate" , data.dt_control_ca );
        $("#fCommonParam").find("#fdt_control_cu").datepicker( "setDate" , data.dt_control_cu );
        
        $("#fCommonParam").find("#fcoef_comp").attr('value',data.coef_comp );  
        $("#fCommonParam").find("#fpower").attr('value',data.power );  

        $("#fCommonParam").find("#fid_station").attr('value',data.id_station );  
        $("#fCommonParam").find("#fstation").attr('value',data.station );  
        
        $("#fCommonParam").find("#fid_extra").attr('value',data.id_extra );  

        if (data.calc_losts==1)
        {
            $("#fCommonParam").find("#fcalc_losts").prop('checked',true);
        }
        else
        {
            $("#fCommonParam").find("#fcalc_losts").prop('checked',false);
        }

        if (data.smart==1)
        {
            $("#fCommonParam").find("#fsmart").prop('checked',true);
        }
        else
        {
            $("#fCommonParam").find("#fsmart").prop('checked',false);
        }

        if (data.magnet==1)
        {
            $("#fCommonParam").find("#fmagnet").prop('checked',true);
        }
        else
        {
            $("#fCommonParam").find("#fmagnet").prop('checked',false);
        }

        if(cur_node.data.key=="new_eqp") 
        {    
            jQuery("#paccnt_meter_zones").hide();
        }
        else
        {    
            jQuery('#paccnt_meter_zones_table').jqGrid('setGridParam',{'postData':{'p_id':data.id_mp}}).trigger('reloadGrid');        
            jQuery("#paccnt_meter_zones").show();
        }
        
        jQuery('#hist2meter_div').show();
        jQuery('#hist2meter_table').jqGrid('setGridParam',{'postData':{'p_id':data.id}}).trigger('reloadGrid');        
        
            //$("#fCommonParam").find("#fid_type_meter").rules("add",meter_valid_options);
        //jQuery.validator.addClassRules(meter_valid_options);
        //validator_meter = $("#fCommonParam").validate(meter_valid_options);
        
        if(cur_node.data.key=="new_eqp+") 
        {
            jQuery('#pMeterParam *').attr("disabled", true);
        }
    }
    */
    //$("#fCommonParam").find("#pMeterParam").hide();
    $("#fCommonParam").find("#pLineAParam").hide();
    $("#fCommonParam").find("#pLineCParam").hide();
    //$("#fCommonParam").find("#pSwitchParam").hide();
    $("#fCommonParam").find("#pCompensParam").hide();    
    //$("#fCommonParam").find("#pFuseParam").hide();    
    
    $("#fCommonParam").find("#p"+data.edit_form).show();

    CommitJQFormVal($("#fCommonParam"));
    
    jQuery('#hist1_table').jqGrid('setGridParam',{datatype: 'json','postData':{'p_id':data.id}}).trigger('reloadGrid');
    jQuery('#hist3_table').jqGrid('setGridParam',{datatype: 'json','postData':{'p_id':data.id}}).trigger('reloadGrid');
    
    jQuery('#abon_table').jqGrid('setGridParam',{datatype: 'json','postData':{'eqp_id':data.id}}).trigger('reloadGrid');
    
    EditorLayout.resizeAll();
  }
  else
  {
    $('#message_zone').append(data.errstr);  
    $('#message_zone').append("<br>");                 
    jQuery("#message_zone").dialog('open');
  }
};

// обработчик, который вызываетя перед отправкой формы
function MainBeforeSubmit(formData, jqForm, options) { 
    // formData - массив; здесь используется $.param чтобы преобразовать его в строку для вывода в alert(),
    // (только в демонстрационных целях), но в самом плагине jQuery Form это совершается автоматически.

    submit_form = jqForm;

    var queryString = $.param(formData);     
    $('#message_zone').append('Вот что мы передаем:' + queryString);  
    $('#message_zone').append("<br>");                 
    
    var btn = '';
    for (var i=0; i < formData.length; i++) { 
        if (formData[i].name =='submitButton') { 
           btn= formData[i].value; 
           submit_form[0].operation.value = btn;
        } 
    } 

    //if(btn=='bt_del')
    //{
    //  jQuery("#dialog-confirm").dialog('open');
    //  return false; 
    //}
    //else
    //{
    //alert(btn);
    if((btn=='bt_edit')||(btn=='bt_add'))
    {
       if(!submit_form.validate().form())  {return false;}
       else {
        if (btn=='bt_edit')
            {
              jQuery("#dialog-changedate").dialog('open');
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
function MainSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return true; 
             }; 
             if (errorInfo.errcode==-1) { //ins ok

               $('#message_zone').append(errorInfo.errstr);  
               $('#message_zone').append("<br>");  

                tree_mode=0;
                //$("#pTreePanel").dynatree("getTree").reload();
                $("#equipment_table").trigger('reloadGrid');   
    
                $("#fCommonParam").find("#bt_add").hide();
                $("#fCommonParam").find("#bt_edit").show();            
                $("#fCommonParam").find("#bt_reset").show();            
                $("#fCommonParam").find("#bt_canceladd").hide();
                $("#lui_equipment_table" ).hide(); // disable grid
                //jQuery('#pMeterParam *').attr("disabled", false);
                //////$("#pTreePanel").dynatree("enable");

                //window.location.reload();
             };   
             if (errorInfo.errcode==1) { //upd ok
               $('#message_zone').append(errorInfo.errstr);  
               $('#message_zone').append("<br>");  
               CommitJQFormVal(submit_form);
               $("#equipment_table").trigger('reloadGrid');
               
               return true;              
             }  
             if (errorInfo.errcode==2) { //error
               $('#message_zone').append(errorInfo.errstr);  
               $('#message_zone').append("<br>");                 
               jQuery("#message_zone").dialog('open');
               return true;              
            }


};

function SaveChanges()
{
  var cur_dt_change = jQuery("#fdate_change").val();
  
  if (tree_mode==0)  // сохраниние изменений параметров оборудования
      {
        //submit_form[0].change_date.value = jQuery("#fdate_change").datepicker( "getDate" );
        //submit_form[0].change_date.datepicker( "setDate" , jQuery("#fdate_change").datepicker( "getDate" ) ) ;
        submit_form[0].change_date.value = cur_dt_change;
        submit_form.ajaxSubmit(form_options);         
      }
  else
  {
    var request;
     
    $.ajaxSetup({
     url: "eqp_tree_oper.php",
     type: "POST",
     dataType: "json"});
/*
     if (tree_mode==2)
     {
        request = $.ajax({data: {id : cur_node.data.key,id_parent: node_ovner.data.key, id_tree: node_ovner.data.id_tree, operation: "node_move", change_date:cur_dt_change}});
     }
*/
     if (tree_mode==3)
     {
//        if (del_node.eqp_type ==-1) 
//        {
//           request = $.ajax({data: {id : del_node.id_tree, operation: "tree_del", change_date:cur_dt_change}});                        
//        }
//        else
        {
           request = $.ajax({data: {id : cur_eqp_id, operation: "node_del", change_date:cur_dt_change}});
        }    
     }
/*
     if (tree_mode==31) //отсоединение счетчика без удаления
     {
        request = $.ajax({data: {id : del_node.key, operation: "meter_disconnect", change_date:cur_dt_change}});            
     }
*/
/*
     if (tree_mode==5)
     {
        request = $.ajax({data: {id_paccnt : id_acc, tree_name: cur_node.data.title, operation: "tree_new", change_date:cur_dt_change}});
     }
*/
     request.done(function(data ) {  
        
        if (data.errcode!==undefined)
        {
            $('#message_zone').append(data.errstr);  
            $('#message_zone').append("<br>");                 
            //jQuery("#message_zone").dialog('open');
            
            if(data.errcode<=0)
            {
                $("#pActionBar").find("#bt_ok").hide();
                $("#pActionBar").find("#bt_cancel").hide();
                $("#equipment_table").trigger('reloadGrid');
                
                //$("#pTreePanel").dynatree("getTree").reload();
                ///////////$("#pTreePanel").dynatree("enable");    
            }
            if(data.errcode!=2)
                {tree_mode=0;}
        }
     });
     request.fail(function(data ) {alert("error");});
      
      
  }
}

function CancelChanges()
{
  if(tree_mode!=0)
  {
    $("#equipment_table").trigger('reloadGrid');
    //$("#pActionBar").find("#bt_ok").hide();
    //$("#pActionBar").find("#bt_cancel").hide();
    //$("#pTreePanel").dynatree("getTree").reload();
    ////////////$("#pTreePanel").dynatree("enable");    
    tree_mode=0;
  }  

}

//---------------------------------------------------------



}); 

/////////////////////////////////////////////

function SelectAddrExternal(code, name) {
    
        $("#fCommonParam").find(SelectAdrTarget).attr('value',code );
        $("#fCommonParam").find(SelectAdrStrTarget).attr('value',name );    
    
} 



function SelectPaccnt(id, book, code, name, addr) {

    $.ajaxSetup({type: "POST",   dataType: "json"});
    
    var newRecord;  
    //var json_str = JSON.stringify(newRecord);
    var cur_mmgg = jQuery("#fmmgg").val();
    var request = $.ajax({
            url: "eqp_tree_oper.php",
            type: "POST",
            data: {
                operation: 'meter_connect', id: cur_eqp_id, id_paccnt : id  
            },
            dataType: "json"
        });

        request.done(function(data ) {
            
            if (data.errcode!==undefined)
                {
                    $('#message_zone').append(data.errstr);  
                    $('#message_zone').append("<br>");                 
                    
                    if(data.errcode==-1) 
                    {
                        jQuery('#abon_table').jqGrid('setGridParam',{datatype: 'json','postData':{'eqp_id':cur_eqp_id}}).trigger('reloadGrid');
                    }                   
                }
            
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

} 