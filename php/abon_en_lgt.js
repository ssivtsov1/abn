var edit_row_id=0;
var elapsed_seconds = 0;
var timerId ;
var spinner_opts;
var id_file=-1;
//var r_edit = 3;
var validator = null;
var form_edit_lock=0;
var fFileLoad_ajaxForm;
var file_form_options;

jQuery(function(){ 


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


  //$('#load_in_progress').hide();
  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
		buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");
    
  jQuery("#fLgtEdit :input").addClass("ui-widget-content ui-corner-all");    
  //dt_mmgg = Date.parse( mmgg, "dd.MM.yyyy");
  //str_mmgg = dt_mmgg.toString("dd.MM.yyyy");
  //$("#fmmgg").datepicker( "setDate" , dt_mmgg.toString("dd.MM.yyyy") );
  //$("#fmmgg").datepicker( "setDate" , mmgg );


  jQuery('#lgt_table').jqGrid({
    url:'abon_en_lgt_data.php',
    editurl: '',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    //width:800,
    shrinkToFit : false,
    //autowidth: true,
    //scroll: 0, 
    colNames:[],
    colModel :[ 

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},     
    {name:'id_file', index:'id_file', width:40, editable: false, align:'center',hidden:true},     
    
    {label:'Кн./рах.',name:'bookcod', index:'bookcod', width:70, editable: true, align:'left',edittype:'text'},            
    {label:'П.І.Б.пільговика',name:'fio_lgt', index:'fio_lgt', width:170, editable: true, align:'left',edittype:'text'},

    {label:'Статус',name:'status', index:'status', width:100, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lstatus},stype:'select',hidden:false},

    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},                
    {label:'Абонент',name:'abon', index:'abon', width:150, editable: true, align:'left',edittype:'text'},
    {label:'Місто',name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text', hidden:town_hidden},
    {label:'Адреса',name:'addr', index:'addr', width:150, editable: true, align:'left',edittype:'text'},

    {label:'Код пільги',name:'code_lgt', index:'code_lgt', width:40, editable: true, align:'left',edittype:'text'},            
    {name:'id_lgt', index:'id_lgt', width:40, editable: false, align:'center',hidden:true},     
    {label:'Назва пільги',name:'name_lgt', index:'name_lgt', width:100, editable: true, align:'left',edittype:'text'},
     
    {label:'Індекс',name:'indx', index:'indx', width:40, editable: true, align:'left',edittype:'text'},
    {label:'Нас.пункт',name:'index_town', index:'index_town', width:100, editable: true, align:'left',edittype:'text', hidden:town_hidden}, 
    {label:'Вулиця',name:'street', index:'street', width:100, editable: true, align:'left',edittype:'text'},
    {label:'Буд.',name:'house', index:'house', width:40, editable: true, align:'left',edittype:'text'},
    {label:'Корп.',name:'korp', index:'korp', width:40, editable: true, align:'left',edittype:'text'},
    {label:'Кв.',name:'flat', index:'flat', width:40, editable: true, align:'left',edittype:'text'},

    {label:'ІНН',name:'ident_cod_l', index:'ident_cod_l', width:70, editable: true, align:'left',edittype:'text'},
    {label:'Паспорт',name:'n_doc', index:'n_doc', width:70, editable: true, align:'left',edittype:'text'},


    {label:'Дата поч.',name:'date_b', index:'date_b', width:70, editable: true, 
                        align:'left',edittype:'text'//,formatter:'date'
                    },
    {label:'Дата кін.',name:'date_e', index:'date_e', width:70, editable: true, 
                        align:'left',edittype:'text'//,formatter:'date'
                    },

    {label:'Період',name:'work_period', index:'work_period', width:80, editable: true, align:'left',edittype:'text',formatter:'date', hidden:false}
//    {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:false,
//            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
 //   {label:'Закр.',name:'flock', index:'flock', width:30, editable: true, align:'right',
//                            formatter:'checkbox',edittype:'checkbox',
//                            stype:'select', searchoptions:{value:': ;1:*'}}

    ],
    pager: '#lgt_tablePager', 
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'bookcod',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Пільги',
    //multiselect: true,
    //hiddengrid: false,
    hidegrid: false,
    postData:{'p_file':id_file},
   // jsonReader : {repeatitems: false},

    gridComplete:function(){

        if ($(this).getDataIDs().length > 0) 
        {   /*   
            if (select_id!=0)
            {
                $(this).setSelection(select_id, true);
            }
            else
            {
                var first_id = parseInt($(this).getDataIDs()[0]);
                $(this).setSelection(first_id, true);
            }
            */
            var first_id = parseInt($(this).getDataIDs()[0]);
            $(this).setSelection(first_id, true);
                       
        }
    
   },

   onSelectRow: function(rowid) { 
        edit_row_id = rowid; 
    },
/*
    onPaging : function(but) { 
                id_paccnt=0;
                $(this).jqGrid('setGridParam',{'postData':{'p_mmgg': mmgg, 'selected_id':id_paccnt}});        
    },    
  */  
    
    ondblClickRow: function(id){ 
      //jQuery(this).editGridRow(id,{width:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
    
      var gsr = jQuery(this).jqGrid('getGridParam','selrow'); 
      if(gsr)
      { 
         validator.resetForm();  //для сброса состояния валидатора
         $("#fLgtEdit").resetForm();
         $("#fLgtEdit").clearForm();
          
         $("#lgt_table").jqGrid('GridToForm',gsr,"#fLgtEdit"); 
         $("#fLgtEdit").find("#foper").attr('value','edit');              
         edit_row_id = id;

         $("#dialog_editLgt").dialog('open');         
                
         if (r_edit==3)
         {
            $("#fLgtEdit").find("#bt_edit").prop('disabled', false);
         }
         else
         {
            $("#fLgtEdit").find("#bt_edit").prop('disabled', true);
         }
        
      } else {alert("Please select Row")}       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);$('#message_zone').dialog('open');}

  }).navGrid('#lgt_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

jQuery("#lgt_table").jqGrid('filterToolbar','');


jQuery("#lgt_table").jqGrid('navButtonAdd','#lgt_tablePager',{caption:"Редагувати",
    id:"blgt_edit",
    onClickButton:function(){ 

      if ($("#lgt_table").getDataIDs().length == 0) 
       {return} ;    

        gsr = $("#lgt_table").jqGrid('getGridParam','selrow'); 
        if(gsr)
        { 
           validator.resetForm();  //для сброса состояния валидатора
           $("#fLgtEdit").resetForm();
           $("#fLgtEdit").clearForm();
          
           $("#lgt_table").jqGrid('GridToForm',gsr,"#fLgtEdit"); 
           $("#fLgtEdit").find("#foper").attr('value','edit');              

           $("#dialog_editLgt").dialog('open');         
                
           if (r_edit==3)
           {
              $("#fLgtEdit").find("#bt_edit").prop('disabled', false);
           }
           else
           {
              $("#fLgtEdit").find("#bt_edit").prop('disabled', true);
           }

        }
    } 
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
	,       center__onresize:	function (pane, $pane, state, options) 
        {
            $("#lgt_table").jqGrid('setGridWidth',$pane.innerWidth()-7);
            $("#lgt_table").jqGrid('setGridHeight',$pane.innerHeight()-160);
        }
        
	});

        
    outerLayout.resizeAll();
    outerLayout.close('south');     
   // innerLayout.hide('north');        
        
    jQuery(".btn").button();
    jQuery(".btnSel").button({icons: {primary:'ui-icon-folder-open'}});
        
   $("#debug_ls1").click( function() {jQuery("#message_zone").dialog('open');});
   $("#debug_ls2").click( function() {jQuery("#message_zone").dialog('close');});
   $("#debug_ls3").click( function() {jQuery("#message_zone").html('');});
   $("#message_zone").dialog({autoOpen: false});
   $("#calc_progress").dialog({autoOpen: false, resizable: false});
    jQuery("#pActionBar :input").addClass("ui-widget-content ui-corner-all");
    
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_sel").click( function(){ 
     //  mmgg = $("#pActionBar").find("#fmmgg").val();  
       
     //  $('#lgt_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg}}).trigger('reloadGrid');
       
   });
   //--------------загрузка файла ----------------
   
   
    file_form_options = { 
     dataType:"json",
     beforeSubmit: FileFormBeforeSubmit, 
     success: FileFormSubmitResponse,
     error: FileFormSubmitError
/*
    ,beforeSend: function() {
        status.empty();
        var percentVal = '0%';
        bar.width(percentVal)
        percent.html(percentVal);
    },
    uploadProgress: function(event, position, total, percentComplete) {
        var percentVal = percentComplete + '%';
        bar.width(percentVal)
        percent.html(percentVal);
    }*/
 };
 
fFileLoad_ajaxForm = $("#fLoad").ajaxForm(file_form_options);


var file_valid_options = { 
                errorPlacement: function(error, element) {
				error.appendTo( element.parent("label").parent("form"));
                },
		rules: {
			//lgt_file: "required"
		},
		messages: {
			//lgt_file: "Вкажіть файл!"
		}
};
load_validator = $("#fLoad").validate(file_valid_options);
 
 
 
  var form_options = { 
    dataType:"json",
    beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
    success: FormSubmitResponse // функция, вызываемая при получении ответа
  };

  fLgt_ajaxForm = $("#fLgtEdit").ajaxForm(form_options);
        
  // опции валидатора общей формы
  var form_valid_options = { 

		rules: {
			book: "required",
                        code: "required",
                        name_lgt: "required"
		},
		messages: {
			reg_date: "Вкажіть дату",
			book: "Вкажіть книгу",
                        code: "Вкажіть рахунок",
                        name_lgt: "Вкажіть пільгу"
		}
};

validator = $("#fLgtEdit").validate(form_valid_options);

 
$("#dialog_editLgt").dialog({
			resizable: true,
		//	height:140,
                        width:700,
			modal: true,
                        autoOpen: false,
                        title:"Коригування"
});
 
 //---------------------------------------------------------------------------
 jQuery("#lgt_table").jqGrid('navButtonAdd','#lgt_tablePager',{caption:"Друкувати",
    onClickButton:function(){ 

        var postData = jQuery("#lgt_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       $('#freps_params').find("#ffilename").attr('value',$("#pActionBar").find("#fname_file").val() ); 
       $('#freps_params').find("#fid_file").attr('value',id_file ); 
       
       $("#dialog-confirm").find("#dialog-text").html('Виберіть варіант друку');
       $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Друк списка',
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

 jQuery("#show_files").click( function() {

    createFilesGrid();
    file_target_id=jQuery("#fid_file");
    file_target_name = jQuery("#fname_file");
    

    jQuery("#grid_selfile").css({'left': jQuery("#fname_file").offset().left+1, 'top': jQuery("#fname_file").offset().top+5});
    jQuery("#grid_selfile").toggle( );
 });

 $("#fname_file").change(function() {
    id_file = $("#fid_file").val();
    
    $('#lgt_table').jqGrid('setGridParam',{postData:{'p_file':id_file}}).trigger('reloadGrid');
    
 });

//------------------------------------------------------------------------------
    //поиск абонента по книге/счету
    jQuery("#btPaccntFind").click( function() {

        vbook = $('#fbook').attr('value');
        vcode = $('#fcode').attr('value');

        var request = $.ajax({
            url: "abon_en_get_abon_id_data.php",
            type: "POST",
            data: {
                book : vbook,
                code: vcode,
                param:2
            },
            dataType: "json"
        });

        request.done(function(data ) {  
        
            if (data.errcode!==undefined)
            {
                if(data.errcode==1)
                {
                    $('#fid_paccnt').attr('value',data.id );
                    $('#fpaccnt_name').attr('value',data.errstr );    
                }

                if(data.errcode==2)
                {
                    $('#fid_paccnt').attr('value','' );
                    $('#fpaccnt_name').attr('value',data.errstr );    
                }
            }
        });        
        request.fail(function(data ) {
            alert("error");
        });

    });

//----------------------------------------------------------------------------
    jQuery("#btPaccntSel").click( function() { 
/*   
        // $("#fpaccnt_sel_params").attr('target',"_blank" );           
        var ww = window.open("abon_en_main.php", "paccnt_win", "toolbar=0,width=900,height=600");
        document.paccnt_sel_params.submit();
        ww.focus();
        */
       
        createAbonGrid();
        
        abon_target_id = $('#fid_paccnt');
        //abon_target_name = $('#fpaccnt_name');
        abon_target_addr = $('#fpaccnt_name');
        abon_target_book = $('#fbook');
        abon_target_code = $('#fcode');

       jQuery("#grid_selabon").css({'left': $('#fpaccnt_name').offset().left+1, 'top': $('#fpaccnt_name').offset().top+20});
       jQuery("#grid_selabon").toggle( );
       
    });



    jQuery("#btLgtSel").click( function() { 
     //var ww = window.open("lgt_list.php", "lgt_win", "toolbar=0,width=800,height=600");
     //document.lgt_sel_params.submit();
     //ww.focus();
     
    createLgtsGrid(jQuery("#fid_grp_lgt").val());
    lgt_target_id=$("#fid_lgt");
    lgt_target_name =  $("#fname_lgt");
    lgt_target_code =  $("#fcode_lgt");
    lgt_target_id_calc = 0;
    lgt_target_name_calc =  0;
    
    jQuery("#grid_sellgt").css({'left': $("#fname_lgt").offset().left+1, 'top': $("#fname_lgt").offset().top});
    jQuery("#grid_sellgt").toggle( );
     
     
   });


   $("#fLgtEdit").find("#btPaccntOpen").click( function(){ 

        vbook = $('#fbook').attr('value');
        vcode = $('#fcode').attr('value');
        vid_paccnt = $('#fid_paccnt').attr('value');

        if ((vbook!='')&& (vcode!='')&& (vid_paccnt!=''))
        {
          $("#fpaccnt_params").find("#pmode").attr('value',0 );
          
          $("#fpaccnt_params").find("#pid_paccnt").attr('value',vid_paccnt );
          $("#fpaccnt_params").find("#ppaccnt_info").attr('value',
                     vbook+'/'+ vcode+' '+ $('#fpaccnt_name').attr('value'));
        
          $("#fpaccnt_params").attr('target',"_blank" );
          $("#fpaccnt_params").attr("action","abon_en_paccnt.php");
          
          document.paccnt_params.submit();
        }
   });



  $("#fLgtEdit").find("#bt_reset").click( function() 
  {
    jQuery("#dialog_editLgt").dialog('close');                           
  });
  
  
  $('#fPayEdit *').filter('input,select').keypress(function(e){
    if ( e.which == 13 ) 
        {
            var focusable = $('#fPayEdit *').filter('input,select,textarea,button:submit').filter(':visible').filter(':enabled').filter(':not([readonly])');
            focusable.eq(focusable.index(this)+1).focus();
            return false;
        }
  }); 
 
 
  if (r_edit!=3)
  {
   $('#bt_load').prop('disabled', true);
   $('#bt_apply').prop('disabled', true);
   $('#bt_delete').prop('disabled', true);
  }

  
});


function FileFormBeforeSubmit(formData, jqForm, options) { 

    if (form_edit_lock == 1) return false;
    submit_form = jqForm;
    //$('#load_in_progress').show();

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

    if((btn=='load')&&($('#flgt_file').attr('value')=='' ))
    {
      $("#dialog-confirm").find("#dialog-text").html('Не вибрано файл для завантаження!');
      $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Вкажіть файл!',
        buttons: {
            "Ок ": function() {
                $( this ).dialog( "close" );
            }
        }
       });
    
       jQuery("#dialog-confirm").dialog('open');
       return false;
    }

    if((btn=='load')&&(($('#fid_region').attr('value')=='' )||($('#fid_region').attr('value')=='null' )))
    {
      $("#dialog-confirm").find("#dialog-text").html('Не вибрано регіон!');
      $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Вкажіть файл!',
        buttons: {
            "Ок ": function() {
                $( this ).dialog( "close" );
            }
        }
       });
    
       jQuery("#dialog-confirm").dialog('open');
       return false;
    }

    if(((btn=='apply')||(btn=='delete'))&&($('#fLoad').find('#fid_file').attr('value')=='' ))
    {
      $("#dialog-confirm").find("#dialog-text").html('Не вибрано завантажений файл для обробки!');
      $("#dialog-confirm").dialog({
        resizable: false,
        height:140,
        modal: true,
        autoOpen: false,
        title:'Вкажіть файл!',
        buttons: {
            "Ок ": function() {
                $( this ).dialog( "close" );
            }
        }
       });
    
       jQuery("#dialog-confirm").dialog('open');
       return false;
    }


    if(!submit_form.validate().form())  {return false;}
    else {
        
        if (btn=='delete')
        {
           $("#dialog-confirm").find("#dialog-text").html('Видалити поточний завантажений файл?');
           $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Видалення',
                buttons: {
                    "Видалити ": function() {
                        fFileLoad_ajaxForm.ajaxSubmit(file_form_options);
                        $( this ).dialog( "close" );
                    },
                    "Відмінити ": function() {
                        $( this ).dialog( "close" );
                    }
                    
                }
            });
    
            jQuery("#dialog-confirm").dialog('open');
            return false;
               
        }
        else
        {   
               jQuery("#calc_progress").dialog('open');
     
               elapsed_seconds = 0;
               timerId = setInterval(function() {
                   elapsed_seconds = elapsed_seconds + 1;
                   $('#progress_time').html(get_elapsed_time_string(elapsed_seconds));
               }, 1000);
       
               $("#progress_indicator").spin("large", "black");    
               form_edit_lock=1;
               return true;
        }
    }

} ;

function FileFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;
             
             jQuery("#calc_progress").dialog('close');
             clearInterval(timerId);
             $("#progress_indicator").spin(false);


             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
              // $('#load_in_progress').hide();
               id_file = errorInfo.id;
               
               $('#fLoad').find("#fid_file").attr('value',id_file ); 
               $('#fLoad').find("#fname_file").attr('value',errorInfo.errstr ); 
               
               $('#lgt_table').jqGrid('setGridParam',{postData:{'p_file':id_file}}).trigger('reloadGrid');
               //jQuery('#lgt_table').trigger('reloadGrid');        
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              
               return [true,errorInfo.errstr]};              

             if (errorInfo.errcode==-2) {

               id_file = 0;
               
               $('#fLoad').find("#fid_file").attr('value',id_file ); 
               $('#fLoad').find("#fname_file").attr('value','' ); 
               
               $('#lgt_table').jqGrid('setGridParam',{postData:{'p_file':id_file}}).trigger('reloadGrid');
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              
               return [true,errorInfo.errstr]};              

             if (errorInfo.errcode==1) {
              // $('#load_in_progress').hide();

               //$('#lgt_table').jqGrid('setGridParam',{postData:{'p_file':id_file}}).trigger('reloadGrid');
               jQuery('#lgt_table').trigger('reloadGrid');        
               
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              
               return [true,errorInfo.errstr]};              


             if (errorInfo.errcode==2) {
              // $('#load_in_progress').hide();  
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};   

};

function FileFormSubmitError(  jqXHR,  textStatus,  errorThrown )
{
             errorInfo = jqXHR.responseText;
             form_edit_lock=0;
             
             jQuery("#calc_progress").dialog('close');
             clearInterval(timerId);
             $("#progress_indicator").spin(false); 

             $("#message_zone").html('');
             $('#message_zone').append('<p style="color:red;" > Ошибка! </p>');               
             $('#message_zone').append(errorInfo);  
             $('#message_zone').append('<br>');                 
             $('#message_zone').dialog('open');
//               return [true,errorInfo.errstr]

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
       if(!submit_form.validate().form())  {return false; }
       else {
        form_edit_lock=1;   
        return true; 
       }
    }
    else {return true; }       
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

             if (errorInfo.errcode==-1) {
                 
               jQuery("#dialog_editLgt").dialog('close');                           
               jQuery('#lgt_table').trigger('reloadGrid');        
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
              // jQuery('#message_zone').dialog('open');
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1) {
                 
               var fid = jQuery("#fid").val();
               if(fid) 
               { 
                 jQuery("#lgt_table").jqGrid('FormToGrid',fid,"#fLgtEdit"); 
               }  
               jQuery("#dialog_editLgt").dialog('close');                                            
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

function SelectPaccntExternal(id, book, code, name, addr) {
    
        $('#fid_paccnt').attr('value',id );
        $('#fbook').attr('value',book );    
        $('#fcode').attr('value',code );    
        $('#fpaccnt_name').attr('value',name );    
    
} 

function SelectLgtExternal(id, name, id_calc, name_calc, code) {
        $("#fid_lgt").attr('value',id );
        $("#fname_lgt").attr('value',name );    
        $("#fcode_lgt").attr('value',code );    
        
}
