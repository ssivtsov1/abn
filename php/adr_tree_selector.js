var edit_row_id = 0;
var cur_row_id = 0;
var validator = null;
var gsr =null;
var address_array;
var id_class =0;
var id_active =0;
var addres_str1 ='';
//var id_root =0; 
var find_count=0;
var form_options;
var types_array;
var search_root;
var form_edit_lock=0;
//var id_ukraine = 10; // 0
//var id_region = 39820; // 3
//var id_district = 40108; // 125

jQuery(function(){ 
   
if (address_obj!='')
{   if (selmode==2)
        id_class = address_obj;
    else
    {
        address_array = address_obj.replace(/\(|\)/g,'').split(',');

        $("#fAddressEdit").find("#fid_class").attr('value', address_array[0]);
        $("#fAddressEdit").find("#findx").attr('value', address_array[1]);
        $("#fAddressEdit").find("#fhouse").attr('value', address_array[2]);
        $("#fAddressEdit").find("#fslash").attr('value', address_array[3]);
        $("#fAddressEdit").find("#fkorp").attr('value', address_array[4]);
        $("#fAddressEdit").find("#fflat").attr('value', address_array[5]);
        $("#fAddressEdit").find("#ff_slash").attr('value', address_array[6]);
        $("#fAddressEdit").find("#fnote").attr('value', address_array[7]);

        id_class = address_array[0];
    }
}
else
{
  id_root = id_district;  
}

InitAddrTree();

    var request = $.ajax({
        url: "adr_tree_selector_types_data.php",
        type: "POST",
        dataType: "json"
    });

    request.done(function(data ) {
        types_array = data;    
    });
    request.fail(function(data ) {
        alert("error");
    });

$("#btnAddr_lev1").click(function(){
    id_root = id_ukraine;
    find_count = 0;
    $('#pTreePanel').dynatree('destroy'); 
    InitAddrTree();
    
 });

$("#btnAddr_lev2").click(function(){
    id_root = id_region;  //Черниговская Область
    find_count = 0;
    $('#pTreePanel').dynatree('destroy'); 
    InitAddrTree();

 });

$("#btnAddr_lev3").click(function(){
    id_root = id_district;  // район
    find_count = 0;
    $('#pTreePanel').dynatree('destroy'); 
    InitAddrTree();

 });


$("#bt_ok").click(function(){
  if (selmode==1) 
  {
     address_obj_new = '('+
            $("#fAddressEdit").find("#fid_class").val()+','+
            $("#fAddressEdit").find("#findx").val()+','+
            $("#fAddressEdit").find("#fhouse").val()+','+
            $("#fAddressEdit").find("#fslash").val()+','+
            $("#fAddressEdit").find("#fkorp").val()+','+
            $("#fAddressEdit").find("#fflat").val()+','+
            $("#fAddressEdit").find("#ff_slash").val()+','+
            $("#fAddressEdit").find("#fnote").val()+')';

     addres_str_full ='';
     if ($("#fAddressEdit").find("#findx").val()!='') 
        addres_str_full = addres_str_full+$("#fAddressEdit").find("#findx").val()+' ';
    
     addres_str_full = addres_str_full+addres_str1;

     if ($("#fAddressEdit").find("#fhouse").val()!='') 
        addres_str_full = addres_str_full+' буд.'+$("#fAddressEdit").find("#fhouse").val();

     if ($("#fAddressEdit").find("#fslash").val()!='') 
        addres_str_full = addres_str_full+'/'+$("#fAddressEdit").find("#fslash").val();

     if ($("#fAddressEdit").find("#fkorp").val()!='') 
        addres_str_full = addres_str_full+''+$("#fAddressEdit").find("#fkorp").val();

     if ($("#fAddressEdit").find("#fflat").val()!='') 
        addres_str_full = addres_str_full+' кв.'+$("#fAddressEdit").find("#fflat").val();

     if ($("#fAddressEdit").find("#ff_slash").val()!='') 
        addres_str_full = addres_str_full+'/'+$("#fAddressEdit").find("#ff_slash").val();

     if ($("#fAddressEdit").find("#fnote").val()!='') 
        addres_str_full = addres_str_full+'('+$("#fAddressEdit").find("#fnote").val()+')';

     window.opener.SelectAddrExternal(address_obj_new,addres_str_full );
     window.opener.focus();
     self.close();      
  }

  if (selmode==2) 
  {
     window.opener.SelectAddrClassExternal($("#fAddressEdit").find("#fid_class").val(),addres_str1 );
     window.opener.focus();
     self.close();      
  }

 });


 $("#bt_reset").click(function(){
        if (address_obj!='')
        {
            address_array = address_obj.replace(/\(|\)/g,'').split(',');

            $("#fAddressEdit").find("#fid_class").attr('value', address_array[0]);
            $("#fAddressEdit").find("#findx").attr('value', address_array[1]);
            $("#fAddressEdit").find("#fhouse").attr('value', address_array[2]);
            $("#fAddressEdit").find("#fslash").attr('value', address_array[3]);
            $("#fAddressEdit").find("#fkorp").attr('value', address_array[4]);
            $("#fAddressEdit").find("#fflat").attr('value', address_array[5]);
            $("#fAddressEdit").find("#ff_slash").attr('value', address_array[6]);
            $("#fAddressEdit").find("#fnote").attr('value', address_array[7]);

            id_class = address_array[0];
            
            $("#pTreePanel").dynatree("getTree").activateKey(id_class);
            Node = $("#pTreePanel").dynatree("getTree").getNodeByKey(id_class);
            Node.select();
            
        }   
    
});
//----------------------------------------------

$("#fsearch").bind({
  'input': function() {
      find_count = 0;
  }
});

$("#flocalsearch").bind({
  'click': function() {
      find_count = 0;
  }
});

$('#fsearch').keydown(function(event) {
  if (event.which == 13) {
     $("#btn_search").click();
   }
});


 $("#btn_search").click(function(){
    
        var pattern = $("#fsearch").val();
        if (pattern==='') return;
        
        
        if (find_count == 0)
        {    
          if ($("#flocalsearch").prop('checked')==true)
             search_root = id_active;
          else
             search_root = id_root;
        }      
        
        var request = $.ajax({
            url: "adr_tree_selector_find_data.php",
            type: "POST",
            data: {
                pattern : pattern,
                cnt:find_count,
                id_root:id_root,
                id_find_root:search_root
            },
            dataType: "html"
        });
        
        request.done(function(data ) {
            find_count++;
            if (data=='') return;
            var tree = $("#pTreePanel").dynatree("getTree");
            tree.loadKeyPath(data, function(node, status){
              if(status == "loaded") {
                  node.expand();
              }else if(status == "ok") {
                node.activate();
                $("#pTreePanel").dynatree("getTree").activateKey(node.data.key);
              }
            });
            
        });
        request.fail(function(data ) {
            alert("error");
        });
    });
//---------------------------------------------------------------
jQuery(".btn").button();
jQuery(".btnSel").button({text: false,icons: {primary:'ui-icon-folder-open'}});

$("#dialog_editform").dialog({
			resizable: true,
		//	height:140,
                        width:700,
			modal: true,
                        autoOpen: false,
                        title:"Редагування"
});


 form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FormSubmitResponse // функция, вызываемая при получении ответа
  };

fClassEdit_ajaxForm = $("#fClassificatorEdit").ajaxForm(form_options);


$.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
jQuery(".dtpicker").mask("99.99.9999");


jQuery("#fAddressEdit :input").addClass("ui-widget-content ui-corner-all");
jQuery("#fClassificatorEdit :input").addClass("ui-widget-content ui-corner-all");

// опции валидатора общей формы
var form_valid_options = { 

		rules: {
			name: "required",
                        idk_class:"required"
		},
		messages: {
			name: "Вкажіть назву!",
                        idk_class:"Вкажіть тип!"
		}
};

validator = $("#fClassificatorEdit").validate(form_valid_options);



$("#fClassificatorEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_editform").dialog('close');                           
});


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
            //jQuery("#adr_class_table").jqGrid('setGridWidth',_pane.innerWidth()-20);
            //jQuery("#adr_class_table").jqGrid('setGridHeight',_pane.innerHeight()-120);
        }
}); 

if (selmode!=2)
      bottom_size = 140;
else  
      bottom_size = 85;

 inerLayout = $("#pmain_center").layout({
		name:	"iner" 
	,	north__paneSelector:	"#pTreeHeader"
	,	north__closable:	false
	,	north__resizable:	false
        ,	north__size:		40
	,	north__spacing_open:	0
	,	south__paneSelector:	"#pAddres"
	,	south__closable:	false
	,	south__resizable:	false
        ,	south__size:		bottom_size
	,	south__spacing_open:	0
	,	center__paneSelector:	"#pTreePanel"
	,	resizeWhileDragging:	true
	,	autoBindCustomButtons:	true
	,       center__onresize:	function (pane, _pane, state, options) 
        {
            //jQuery("#adr_class_table").jqGrid('setGridWidth',_pane.innerWidth()-20);
            //jQuery("#adr_class_table").jqGrid('setGridHeight',_pane.innerHeight()-120);
        }
}); 
outerLayout.resizeAll();
outerLayout.close('south');     


if(selmode!=0)
{
    outerLayout.hide('north');        
    
    if (selmode==2)
      $("#pHouse").hide();
}
else
{
    inerLayout.hide('south');        
}


  $('#fClassificatorEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fClassificatorEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 
}); 
 
function InitAddrTree() {
$.ajaxSetup({type: "POST",   dataType: "json"}); 

$("#pTreePanel").dynatree({
          initAjax: {url: "adr_tree_selector_data.php",
                      data: {id_root: id_root, 
                      id_class : id_class,
                      mode: "init"
                      }
          },
          
           onLazyRead: function(node){
               
                node.appendAjax({
                  url: "adr_tree_selector_data.php",
                  data: {id_root: node.data.key,
                         mode: "lazy"
                        }
              });
               
           },
           onCustomRender: function(node) {

            if(node.data.knd_id === ''){
                // Default rendering
                return false;
            }
            
            html = "<a class='dynatree-title' href='#'>";
            if(node.data.sh_prf !== ''){
                html += "<span class='pref'>" + node.data.sh_prf + "</span>";
            }
            
            html += "<span class='nam'>" + node.data.title + "</span>";
            
            if(node.data.sh_post !== ''){
                html += "<span class='postf'>" + node.data.sh_post + "</span>";
            }

            if(node.data.n_old !== ''){
                html += "<span class='o_nam'> (" + node.data.n_old + ")</span>";
            }

            return html + "</a>";
           },

          //persist: false,
          //selectMode: 1,
          autoFocus: false, 
          imagePath: "images/addr_icons/",
          
          fx: {height: "toggle", duration: 150},
          autoCollapse: false,

          onQueryActivate : function(isTrue,node) {
              /*
              if((tree_mode==0)||(node.data.key=="new_eqp")||(node.data.key=="new_eqp+"))
                  {
                      return true;
                  }
                  else
                  {
                      return false;
                  }    
              */
          },
          onDblClick: function(node, event) {
              node.toggleSelect();
          },

          onClick: function(node, event) {

             if ($("#flocalsearch").prop('checked')==true)
                 find_count=0;
          },

          onQuerySelect: function(flag, node) 
          {
              if(flag)
              {
               $("#pTreePanel").dynatree("getRoot").visit(function(node){
                 node.select(false);
               });

              }
              
              return true;
          },
          onSelect: function(flag, node) {
            if( flag )
            {
                id_class = node.data.key;
                $("#fAddressEdit").find("#fid_class").attr('value', id_class);

                var request = $.ajax({
                    url: "adr_tree_selector_selected_data.php",
                    type: "POST",
                    data: {
                        id_class : id_class,
                        full_addr_mode: full_addr_mode
                    },
                    dataType: "html"
                });

                request.done(function(data ) {
                    $("#lSelected_addr").html(data);
                    addres_str1 = data;
                });
                request.fail(function(data ) {
                    alert("error");
                });
                    
            }
  
          },

          onActivate: function( node) {   
              $("#current_addr_id").html(node.data.key);
              id_active = node.data.key;
          },
          onKeydown: function(node, event) {
                return false;},
          onCreate: function(node, span){
            bindContextMenu(span);
          },
          onPostInit:function(isReloading, isError)
          {
             if (id_class!=0) 
             {
                 setTimeout(function(){                 
                     $("#pTreePanel").dynatree("getTree").activateKey(id_class);
                     Node = $("#pTreePanel").dynatree("getTree").getNodeByKey(id_class);
                     Node.select();
                    },500);  
             }
                 
          }

 });     
    
};
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
function FormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) { //add
                 
               jQuery("#dialog_editform").dialog('close');                           
               jQuery('#lgt_category_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
              
               var node = $("#pTreePanel").dynatree("getActiveNode");
               if( node ){
                  
                var cl_type = $("#fClassificatorEdit").find("#fidk_class").val();   
                var childNode = node.addChild({
                  title: $("#fClassificatorEdit").find("#fname").val(),
                  n_old: $("#fClassificatorEdit").find("#fname_old").val(),
                  key:errorInfo.id,
                  sh_prf: types_array[cl_type]['sh_prf'],
                  sh_post: types_array[cl_type]['sh_post'],
                  icon: types_array[cl_type]['icon'],
                  knd_id:  types_array[cl_type]['ident']  });              
                node.expand(true);
               }

               return [true,errorInfo.errstr]
             };              
             if (errorInfo.errcode==-2) { //del
                 
               jQuery("#dialog_editform").dialog('close');                           
               jQuery('#lgt_category_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
              
               var node = $("#pTreePanel").dynatree("getActiveNode");
               if( node ){
                   node.remove();
               };              

               return [true,errorInfo.errstr]

              };              
             
             if (errorInfo.errcode==1) { //edit
                 
               var node = $("#pTreePanel").dynatree("getActiveNode");
               if( node ){
                node.data.title = $("#fClassificatorEdit").find("#fname").val();
                node.data.n_old = $("#fClassificatorEdit").find("#fname_old").val();
                
                var cl_type = $("#fClassificatorEdit").find("#fidk_class").val();
                node.data.sh_prf = types_array[cl_type]['sh_prf'];
                node.data.sh_post =types_array[cl_type]['sh_post'];
                node.data.icon = types_array[cl_type]['icon'];
                node.data.knd_id = types_array[cl_type]['ident'];
                    
                node.render();
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

function bindContextMenu(span) {
  // Add context menu to this node:
  $(span).contextMenu({
    menu: "myMenu"
  }, function(action, el, pos) {
    // The event was bound to the <span> tag, but the node object
    // is stored in the parent <li> tag
    var node = $.ui.dynatree.getNode(el);
    switch( action ) {
      case "contAdd":
        AddClass();
        break;
      case "contDelete":
        DelClass();
        break;
      case "contEdit":
        EditClass();
        break;
      case "buttAdd":
      case "buttEdit":
      case "buttDelete":
              break;
      case "contCancel":
          $(".contextMenu").hide();

       // treeActions(action);
        break;
      default:
        alert("Todo: appply action '" + action + "' to node " + node);
    }
  });
};
function AddClass()
{
    if (r_edit==0) return;
    
    validator.resetForm();
    $("#fClassificatorEdit").resetForm();
    $("#fClassificatorEdit").clearForm();
          
    edit_row_id = -1;
    
    $("#fClassificatorEdit").find("#fid").attr('value',-1 );    
    $("#fClassificatorEdit").find("#fid_parent").attr('value',id_active );    
    $("#fClassificatorEdit").find("#foper").attr('value','add');              
    
    node = $("#pTreePanel").dynatree("getTree").getNodeByKey(id_active);
    html = 'Попередник: <b>';
    if(node.data.sh_prf !== ''){
        html += "<span class='pref'>" + node.data.sh_prf + "</span>";
    }
            
    html += "<span class='nam'>" + node.data.title + "</span>";
            
    if(node.data.sh_post !== ''){
        html += "<span class='postf'>" + node.data.sh_post + "</span>";
    }
    html += "</b>";
    $("#fClassificatorEdit").find("#lparent_name").html(html );              
          
    $("#fClassificatorEdit").find("#bt_add").show();
    $("#fClassificatorEdit").find("#bt_edit").hide();            
    jQuery("#dialog_editform").dialog('open');          
    
};
function EditClass()
{

    var request = $.ajax({
        url: "adr_tree_selector_node_data.php",
        type: "POST",
        data: {
            id_class : id_active
        },
        dataType: "json"
    });

    request.done(function(data ) {
        //alert(data);
            
        validator.resetForm();
        $("#fClassificatorEdit").resetForm();
        $("#fClassificatorEdit").clearForm();
          
        edit_row_id = data.id;
        $("#fClassificatorEdit").find("#fid").attr('value',data.id );    
        $("#fClassificatorEdit").find("#fid_parent").attr('value',data.id_parent );    
        $("#fClassificatorEdit").find("#foper").attr('value','edit');              
                
        $("#fClassificatorEdit").find("#fname").attr('value',data.name);              
        $("#fClassificatorEdit").find("#fname_full").attr('value',data.name_full);              
        $("#fClassificatorEdit").find("#fname_old").attr('value',data.name_old);
        $("#fClassificatorEdit").find("#fident").attr('value',data.ident);              
        $("#fClassificatorEdit").find("#findx").attr('value',data.indx);              
        $("#fClassificatorEdit").find("#fidk_class").attr('value',data.idk_class);              
          
        node = $("#pTreePanel").dynatree("getTree").getNodeByKey(id_active);
          
        $("#fClassificatorEdit").find("#bt_add").hide();
        if (r_edit==1) 
          $("#fClassificatorEdit").find("#bt_edit").show();            
        jQuery("#dialog_editform").dialog('open');          
            
            
    });
    request.fail(function(data ) {
        alert("error");
    });
          
      
};
function DelClass()
{
      if (r_edit==0) return;    
      
      node = $("#pTreePanel").dynatree("getTree").getNodeByKey(id_active);
      if (node.countChildren()>0)
      {
        jQuery("#dialog-confirm").find("#dialog-text").html('Неможливо видалити вузол, що має потомків!');
    
        $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Відмінити": function() {
					$( this ).dialog( "close" );
				}
			}
		});
    
         jQuery("#dialog-confirm").dialog('open');   
         return;
              
      }

      jQuery("#dialog-confirm").find("#dialog-text").html('Видалити вузол?');
    
      $("#dialog-confirm").dialog({
			resizable: false,
			height:140,
			modal: true,
                        autoOpen: false,
                        title:'Видалення',
			buttons: {
				"Видалити": function() {
                                        fClassEdit_ajaxForm[0].oper.value = 'del';
                                        fClassEdit_ajaxForm[0].id.value = id_active;
                                        fClassEdit_ajaxForm.ajaxSubmit(form_options);       

                                        $( this ).dialog( "close" );
				},
				"Відмінити": function() {
					$( this ).dialog( "close" );
				}
			}
		});
    
        jQuery("#dialog-confirm").dialog('open');   
          
        ;} 
   
