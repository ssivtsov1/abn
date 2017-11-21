var edit_row_id=0;
var elapsed_seconds = 0;
var timerId ;
var spinner_opts;
var form_edit_lock=0;
var id_region = '';

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
    
  //dt_mmgg = Date.parse( mmgg, "dd.MM.yyyy");
  //str_mmgg = dt_mmgg.toString("dd.MM.yyyy");
  //$("#fmmgg").datepicker( "setDate" , dt_mmgg.toString("dd.MM.yyyy") );
  $("#fmmgg").datepicker( "setDate" , mmgg );


  jQuery('#subs_table').jqGrid({
    url:'abon_en_subs_data.php',
    editurl: '',
    datatype: 'json',
    mtype: 'POST',
    height:400,
    //width:800,
    shrinkToFit : false,
    autowidth: true,
    //scroll: 0,
    colNames:[],
    colModel :[ 

    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true},     
    
    {label:'Книга',name:'book', index:'book', width:40, editable: true, align:'left',edittype:'text'},            
    {label:'Рахунок',name:'code', index:'code', width:40, editable: true, align:'left',edittype:'text'},                
    {label:'Одержувач',name:'abon', index:'abon', width:150, editable: true, align:'left',edittype:'text'},

    //{label:'Місто/село',name:'town', index:'town', width:100, editable: true, align:'left',edittype:'text'},                    
    //{label:'Вулиця',name:'street', index:'street', width:100, editable: true, align:'left',edittype:'text'},                        
    {label:'Адреса',name:'addr', index:'addr', width:150, editable: true, align:'left',edittype:'text'},
    
    {label:'№ субс.',name:'num_subs', index:'num_subs', width:50, editable: true, align:'left',edittype:'text'},
    {label:'Кн/Рах',name:'bookcod', index:'bookcod', width:60, editable: true, align:'left',edittype:'text'},
    
    {label:'Абонент програми',name:'paccnt', index:'paccnt', width:220, editable: true, align:'left',edittype:'text'},

    {label:'Дата поч.',name:'dt_b', index:'dt_b', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},
    {label:'Дата кін.',name:'dt_e', index:'dt_e', width:80, editable: true, 
                        align:'left',edittype:'text',formatter:'date'},

    {label:'Місяців',name:'val_month', index:'val_month', width:60, editable: true, align:'left',edittype:'text'},

    {label:'Плат.норм.',name:'norma_pay', index:'norma_pay', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           

    {label:'кВтг.норм.субс',name:'norma_subskwt', index:'norma_subskwt', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           

    {label:'кВт.норм.',name:'norma_kwt', index:'norma_kwt', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           

    {label:'Кілк.субс.',name:'kol_subs', index:'kol_subs', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text' },           

    {label:'Обов.платіж',name:'ob_pay', index:'ob_pay', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           

    {label:'Обов.платіж.зон',name:'ob_zon', index:'ob_zon', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           

    {label:'Обов.платіж.кВтг',name:'ob_kwt', index:'ob_kwt', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           
  
  
    {label:'Сума всього',name:'subs_all', index:'subs_all', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           
  
    {label:'Сума за міс.',name:'subs_month', index:'subs_month', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           
  
    {label:'кВт.субс.',name:'subs_kwt', index:'subs_kwt', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           
  
    {label:'кВт.рах.',name:'kwt', index:'kwt', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text' },           
  
    {label:'Норма по пільзі',name:'norma_lgt', index:'norma_lgt', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           
    {label:'Пільг. тариф',name:'tarif_lgt', index:'tarif_lgt', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text' },           
    {label:'Розрах.субсидія',name:'subs_calc', index:'subs_calc', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           
    {label:'Субс.понад норму',name:'subs_svn', index:'subs_svn', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           
  
    {label:'в сальдо абон.',name:'sal_abn', index:'sal_abn', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           
    {label:'в сальдо субс.',name:'sal_subs', index:'sal_subs', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number' },           

    {label:'Перерах.кВт',name:'recalc_kwt', index:'recalc_kwt', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text' },           
  
    {label:'Перерах.сума',name:'recalc_subs', index:'recalc_subs', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text' },           
    {label:'Період',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', hidden:false},
    
    {label:'Файл',name:'name_file', index:'name_file', width:80, editable: true, align:'left',edittype:'text'},
    
    {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:false,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
//    {label:'Закр.',name:'flock', index:'flock', width:30, editable: true, align:'right',
//                            formatter:'checkbox',edittype:'checkbox',
//                            stype:'select', searchoptions:{value:': ;1:*'}}

    ],
    pager: '#subs_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'book',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Субсидія',
    //hiddengrid: false,
    hidegrid: false,
    postData:{'p_mmgg': mmgg, 'selected_id':id_paccnt, 'region': id_region},
    jsonReader : {repeatitems: false},

    gridComplete:function(){

   
        var myUserData = $(this).jqGrid('getGridParam', 'userData')
    
        var sum_subs_month = parseFloat(myUserData['sum_subs_month']);
        var sum_ob_pay = parseFloat(myUserData['sum_ob_pay']);
        var sum_subs_all = parseFloat(myUserData['sum_subs_all']);
        var sum_recalc = parseFloat(myUserData['sum_recalc']);
        var select_id = myUserData['select_id'];
  
        if ($(this).getDataIDs().length > 0) 
        {      
            if (select_id!=0)
            {
                $(this).setSelection(select_id, true);
                id_paccnt=0;
            }
            else
            {
                var first_id = parseInt($(this).getDataIDs()[0]);
                $(this).setSelection(first_id, true);
            }
        }
    
        $("#pFooterBar").find("#fsum_subs_month").attr('value',sum_subs_month.toFixed(2));
        $("#pFooterBar").find("#fsum_ob_pay").attr('value',sum_ob_pay.toFixed(2));
        $("#pFooterBar").find("#fsum_subs_all").attr('value',sum_subs_all.toFixed(2));
        $("#pFooterBar").find("#fsum_recalc").attr('value',sum_recalc.toFixed(2));
        
        //var postData = $(this).jqGrid('getGridParam', 'postData');
        //var json_str = JSON.stringify(postData);
        
        //alert(json_str );
   },

   onSelectRow: function(rowid) { 
        edit_row_id = rowid; 
    },

    onPaging : function(but) { 
                id_paccnt=0;
                $(this).jqGrid('setGridParam',{'postData':{'p_mmgg': mmgg, 'selected_id':id_paccnt}});        
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
        
      } else { alert("Please select Row") }       
      
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#subs_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

jQuery("#subs_table").jqGrid('filterToolbar','');


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
            $("#subs_table").jqGrid('setGridWidth',$pane.innerWidth()-7);
            $("#subs_table").jqGrid('setGridHeight',$pane.innerHeight()-160);
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
   $("#message_zone").dialog({ autoOpen: false });
   $("#calc_progress").dialog({autoOpen: false, resizable: false});
    jQuery("#pActionBar :input").addClass("ui-widget-content ui-corner-all");
    
    
   
    
   //-------------------------------------------------------------
   $("#pActionBar").find("#bt_sel").click( function(){ 
       mmgg = $("#pActionBar").find("#fmmgg").val();  
       id_region = $("#pActionBar").find("#fid_region").val();  
       $('#subs_table').jqGrid('setGridParam',{postData:{'p_mmgg': mmgg, 'region': id_region}}).trigger('reloadGrid');
       
   });
   //--------------загрузка файла ----------------
   
   
    var file_form_options = { 
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
 
var fFileLoad_ajaxForm = $("#fSubsLoad").ajaxForm(file_form_options);


var file_valid_options = { 
                errorPlacement: function(error, element) {
				error.appendTo( element.parent("label").parent("form"));
                },
		rules: {
			subs_file: "required"
		},
		messages: {
			subs_file: "Вкажіть файл!"
		}
};
load_validator = $("#fSubsLoad").validate(file_valid_options);
 
 
 //---------------------------------------------------------------------------
 jQuery("#subs_table").jqGrid('navButtonAdd','#subs_tablePager',{caption:"Друкувати",
    onClickButton:function(){ 

        var postData = jQuery("#subs_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       $('#freps_params').find("#fdt_b").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#foper").attr('value', "subs_list");
       $('#freps_params').find("#fid_region").attr('value', id_region);

       $("#dialog-confirm").find("#dialog-text").html('Виберіть варіант друку');
       $("#dialog-confirm").css('background-color','white');
       $("#dialog-confirm").css('color','black');


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

 jQuery("#subs_table").jqGrid('navButtonAdd','#subs_tablePager',{caption:"Друкувати брак",
    onClickButton:function(){ 

        var postData = jQuery("#subs_table").jqGrid('getGridParam', 'postData');
        var json_str = JSON.stringify(postData);
       
        //alert(json_str );
       $('#freps_params').find("#fgrid_params").attr('value',json_str ); 
       $('#freps_params').find("#fdt_b").attr('value',$("#pActionBar").find("#fmmgg").val() ); 
       $('#freps_params').find("#foper").attr('value', "subs_bad");   
       $('#freps_params').find("#fid_region").attr('value', id_region);
       
       
       $("#dialog-confirm").find("#dialog-text").html('Виберіть варіант друку');
       $("#dialog-confirm").css('background-color','white');
       $("#dialog-confirm").css('color','black');
       
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


 jQuery("#subs_table").jqGrid('navButtonAdd','#subs_tablePager',{caption:"Видалити файл",
    id:"btn_subs_del",
    onClickButton:function(){ 

       var gsr = jQuery("#subs_table").jqGrid('getGridParam','selrow'); 
       if(!gsr) return;
       
       filename=jQuery("#subs_table").jqGrid('getCell',edit_row_id,'name_file');
       
       jQuery("#dialog-confirm").find("#dialog-text").html('Видалити інформацію про субсидію з файлу '+
           filename+' ?');
       jQuery("#dialog-confirm").css('background-color','red');
       jQuery("#dialog-confirm").css('color','white');

           
            $("#dialog-confirm").dialog({
                resizable: false,
                height:140,
                modal: true,
                autoOpen: false,
                title:'Видалення',
                buttons: {
                    "Видалити": function() {

                        var request = $.ajax({
                            url: "abon_en_subs_delfile_edit.php", 
                            type: "POST",
                            data: {
                                name : filename,
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
                                    if (data.id =1 )
                                    {
                                        jQuery('#subs_table').trigger('reloadGrid');                      
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

if (r_load!=3)
{
    $('#btn_subs_del').addClass('ui-state-disabled');
}

});



function FileFormBeforeSubmit(formData, jqForm, options) { 

    if (form_edit_lock == 1) return false;
    submit_form = jqForm;
    //$('#load_in_progress').show();

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

    if((region_required==1)&&(($('#fid_region').attr('value')=='' )||($('#fid_region').attr('value')=='null' )))
    {
      $("#dialog-confirm").find("#dialog-text").html('Виберіть регіон!');
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

} ;

function FileFormSubmitResponse(responseText, statusText)
{
             errorInfo = responseText;
             form_edit_lock=0;
             
             jQuery("#calc_progress").dialog('close');
             clearInterval(timerId);
             $("#progress_indicator").spin(false); 

             if(!errorInfo.hasOwnProperty('errcode')){
               $('#message_zone').append(errorInfo);  
               $('#message_zone').append('<br>');                 
               $('#message_zone').dialog('open');
               return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 

             if (errorInfo.errcode==-1) {
              // $('#load_in_progress').hide();
               jQuery('#subs_table').trigger('reloadGrid');        
               
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
