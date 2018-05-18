var edit_row_id=0;
//var r_edit = 3;
var form_edit_lock=0;
var validator = null;
var id_region =0;

jQuery(function(){ 

  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
		buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");
    
  jQuery("#fSubsEdit :input").addClass("ui-widget-content ui-corner-all");  
  $("#calc_progress").dialog({autoOpen: false, resizable: false});
 // $("#fmmgg").datepicker( "setDate" , mmgg );


    $.fn.spin = function(opts, color) {
        var presets = {
            "tiny": {
                lines: 8, 
                length: 2, 
                width: 2, 
                radius: 3
            },
            "small": {
                lines: 8, 
                length: 4, 
                width: 3, 
                radius: 5
            },
            "large": {
                lines: 10, 
                length: 8, 
                width: 4, 
                radius: 8
            }
        };
        if (Spinner) {
            return this.each(function() {
                var $this = $(this),
                data = $this.data();
                if (data.spinner) {
                    data.spinner.stop();
                    delete data.spinner;
                }
                if (opts !== false) {
                    if (typeof opts === "string") {
                        if (opts in presets) {
                            opts = presets[opts];
                        } else {
                            opts = {};
                        }
                        if (color) {
                            opts.color = color;
                        }
                    }
                    data.spinner = new Spinner($.extend({
                        color: $this.css('color')
                        }, opts)).spin(this);
                }
            });
        } else {
            throw "Spinner class not available.";
        }
    };


  jQuery('#badsubs_table').jqGrid({
    url:'abon_en_subs_check_data.php',
    //editurl: 'abon_en_badindic_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    //width:800,
    //autowidth: false,
    //shrinkToFit : false,
    scroll: 0,
    colNames:[],
    colModel :[ 

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center', hidden:true},
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},
    {label:'Рахунок',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},
    {label:'Книга/рах субс.',name:'bookcod', index:'bookcod', width:80, editable: true, align:'left',edittype:'text'},
    {label:'Книга субс',name:'calc_book', index:'calc_book', width:40, editable: true, align:'left',edittype:'text'},
    {label:'Рахунок субс',name:'calc_code', index:'calc_code', width:40, editable: true, align:'left',edittype:'text'},
    {label:'№ субс.',name:'num_subs', index:'num_subs', width:80, editable: true, align:'left',edittype:'text'},
    {label:'№ субс. карт.',name:'acc_subs_num', index:'acc_subs_num', width:80, editable: true, align:'left',edittype:'text'},
    {label:'Абон.в субс',name:'fio', index:'fio', width:150, editable: true, align:'left',edittype:'text'},
    {label:'Адреса в субс',name:'addr', index:'addr', width:170, editable: true, align:'left',edittype:'text'},
   
    //{label:'Прізвише',name:'last_name', index:'last_name', width:200, editable: true, align:'left',edittype:'text'},
    //{label:"Ім'я",name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text'},
    //{label: "По батькові", name:'patron_name', index:'patron_name', width:200, editable: true, align:'left',edittype:'text'},
    {label:'Кн./рах карт.',name:'pbookcod', index:'pbookcod', width:80, editable: true, align:'left',edittype:'text'},
    {label: "Абон. карт.", name:'abon_name', index:'abon_name', width:200, editable: true, align:'left',edittype:'text'},
    {label:'Місто в карт',name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text', hidden:town_hidden},
    {label:'Адреса в карт.',name:'addr_abon', index:'addr_abon', width:170, editable: true, align:'left',edittype:'text'},
    
    {label: "Абонент, який має вказаний № субс.", name:'abon_s', index:'abon_s', width:200, editable: true, align:'left',edittype:'text',hidden:true},
    {label:'Поточна',name:'fcurrent', index:'fcurrent', width:40, editable: true, align:'left',edittype:'text',
              stype:'select', searchoptions:{value:': ;1:*'}  },
    {label:'Ідент',name:'ident', index:'ident', width:50, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lstatus},stype:'select',hidden:false}

    ],
    pager: '#badsubs_tablePager',
    rowNum:100,
    rowList:[20,50,100,300,500],
    sortname: 'book',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Субсидія ',
    //hiddengrid: false,
    jsonReader : {repeatitems: false},
    hidegrid: false,
    postData:{'region': id_region},

    gridComplete:function(){

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
    var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
        edit_row_id = id;
        
            validator.resetForm();  //для сброса состояния валидатора
            $("#fSubsEdit").resetForm();
            $("#fSubsEdit").clearForm();
          
            $("#badsubs_table").jqGrid('GridToForm',gsr,"#fSubsEdit"); 
            $("#fSubsEdit").find("#foper").attr('value','edit'); 
            
            $("#dialog_subsform").dialog('open');          
            
            if (r_edit==3)
            {
              $("#fSubsEdit").find("#bt_edit").prop('disabled', false);
            }
            else
            {
              $("#fSubsEdit").find("#bt_edit").prop('disabled', true);
            }
      }       
      
    } ,  

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#badsubs_tablePager',
       {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

  jQuery("#badsubs_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
  //    jQuery(this).editGridRow(id,TableEditOptions);
  }} );


  jQuery("#badsubs_table").jqGrid('filterToolbar','');


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
            
            $("#badsubs_table").jqGrid('setGridWidth',$pane.innerWidth()-10);
            $("#badsubs_table").jqGrid('setGridHeight',$pane.innerHeight()-140);

        }
        
	});

        
    outerLayout.resizeAll();
    outerLayout.close('south');  
   // innerLayout.hide('north');        
        
    jQuery(".btn").button();
    jQuery(".btnSel").button({ icons: {primary:'ui-icon-folder-open'} });
    
   
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open'); });
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   $("#message_zone").dialog({autoOpen: false});
   
   
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_sel").click( function(){ 
       
       id_region = $("#pActionBar").find("#fid_region").val();  
       $('#badsubs_table').jqGrid('setGridParam',{postData:{'region': id_region}}).trigger('reloadGrid');
       
   });
   

//------------------редактирование одиночного  ---------------------

 var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FormSubmitResponse // функция, вызываемая при получении ответа
  };

$("#dialog_subsform").dialog({
			resizable: true,
                        width:700,
			modal: true,
                        autoOpen: false,
                        title:"Абонент"
});
fSubs_ajaxForm = $("#fSubsEdit").ajaxForm(form_options);
        
// опции валидатора общей формы
var form_valid_options = { 

		rules: {
			//indic: "required",
                        //ind_date:"required"
		},
		messages: {
			//ind_date: "Вкажіть дату",
                        //indic: "Вкажіть показники!"
		}
};

validator = $("#fSubsEdit").validate(form_valid_options);


$("#fSubsEdit").find("#bt_reset").click( function() 
{
  jQuery("#dialog_subsform").dialog('close');                           
});

//-------------------------------------------------------------
 jQuery("#badsubs_table").jqGrid('navButtonAdd','#badsubs_tablePager',{caption:"Редагувати",
    onClickButton:function(){ 

      if ($("#badsubs_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#badsubs_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
            validator.resetForm();  //для сброса состояния валидатора
            $("#fSubsEdit").resetForm();
            $("#fSubsEdit").clearForm();
          
            $("#badsubs_table").jqGrid('GridToForm',gsr,"#fSubsEdit"); 
            $("#fSubsEdit").find("#foper").attr('value','edit'); 
            
            $("#dialog_subsform").dialog('open');          
            
            if (r_edit==3)
            {
              $("#fSubsEdit").find("#bt_edit").prop('disabled', false);
            }
            else
            {
              $("#fSubsEdit").find("#bt_edit").prop('disabled', true);
            }

        }
    } 
});

    jQuery("#btPaccntSel").click( function() { 
        createAbonGrid();
        
        abon_target_id = $('#fSubsEdit').find('#fid_paccnt');
        abon_target_name = $('#fSubsEdit').find('#fabon_name');
        abon_target_book = $("#fSubsEdit").find('#fbook');
        abon_target_code = $("#fSubsEdit").find('#fcode');

       jQuery("#grid_selabon").css({'left': $('#fSubsEdit').find('#fbook').offset().left+1, 'top': $('#fSubsEdit').find('#fbook').offset().top+20});
       jQuery("#grid_selabon").toggle( );
       
    });


 //---------------------------------------------------------------------------
 jQuery("#badsubs_table").jqGrid('navButtonAdd','#badsubs_tablePager',{caption:"Друкувати",
    onClickButton:function(){ 

        var postData = jQuery("#badsubs_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       //$('#freps_params').find("#fdt_b").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#foper").attr('value', "badsubs_list");
       $('#freps_params').find("#fid_region").attr('value', id_region);
       
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


jQuery("#badsubs_table").jqGrid('navButtonAdd','#badsubs_tablePager',{caption:"Розрахунок",
    id:"btn_header_bills",
    onClickButton:function(){ 

            jQuery("#dialog-confirm").find("#dialog-text").html('Виконати розрахунок субсидії?');
    
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Розрахунок',
                buttons: {
                    "Виконати": function() {
                                        
                        jQuery("#calc_progress").dialog('open');
     
                        elapsed_seconds = 0;
                        timerId = setInterval(function() {
                            elapsed_seconds = elapsed_seconds + 1;
                            $('#progress_time').html(get_elapsed_time_string(elapsed_seconds));
                        }, 1000);
        
                        $("#progress_indicator").spin("large", "black");    


                        var request = $.ajax({
                            url: "abon_en_subs_calc.php",
                            type: "POST",
                            dataType: "json"
                        });

                        request.done(function(data ) {  
        
                            jQuery("#calc_progress").dialog('close');
                            clearInterval(timerId);
                            $("#progress_indicator").spin(false);
        
                            if (data.errcode!==undefined)
                            {
                                $('#message_zone').append(data.errstr);  
                                $('#message_zone').append("<br>");                 
                                //jQuery("#message_zone").dialog('open');
            
                                if(data.errcode<=0) 
                                {
                                    if (data.id =1 )
                                    {
                                        alert("Розрахунок виконано!");
                                        jQuery('#bill_table').trigger('reloadGrid');
                                    }
                                    else
                                    {
                                        alert("Помилка при розрахунку!");
                                    }
                
                                }
                                else
                                {
                                    jQuery("#message_zone").dialog('open');                                    
                                }
                            }
                        });

                        request.fail(function(data ) {
                            
                            alert("Помилка!");
                            
                            jQuery("#calc_progress").dialog('close');
                            clearInterval(timerId);
                            $("#progress_indicator").spin(false);

        
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

if (r_edit!=3)
{
    $('#btn_header_bills').addClass('ui-state-disabled');
}


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
function FormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;
             
             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             
             if (errorInfo.errcode==1) {
                 
               jQuery("#badsubs_table").jqGrid('FormToGrid',edit_row_id,"#fSubsEdit"); 
               //jQuery('#badsubs_table').trigger('reloadGrid');
               
               jQuery("#dialog_subsform").dialog('close');
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
/*
             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_subsform").dialog('close');                                            
               jQuery('#badsubs_table').trigger('reloadGrid'); 
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');

               return [true,errorInfo.errstr]};
*/
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};   

};

function get_elapsed_time_string(total_seconds) {
  function pretty_time_string(num) {
    return ( num < 10 ? "0" : "" ) + num;
  }

  var hours = Math.floor(total_seconds / 3600);
  total_seconds = total_seconds % 3600;

  var minutes = Math.floor(total_seconds / 60);
  total_seconds = total_seconds % 60;

  var seconds = Math.floor(total_seconds);

  // Pad the minutes and seconds with leading zeros, if required
  hours = pretty_time_string(hours);
  minutes = pretty_time_string(minutes);
  seconds = pretty_time_string(seconds);

  // Compose the string for display
  var currentTimeString = hours + ":" + minutes + ":" + seconds;

  return currentTimeString;
}