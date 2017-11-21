var cur_tar_grp=0;
var cur_tar=0;
var show_closed = 0;

jQuery(function(){ 
/*
  if (selmode==0)
  {
     setTimeout(function(){
             jQuery('#tarif_grp_table').trigger('reloadGrid');              
    },300);  
  };
*/
  $.datepicker.setDefaults( $.datepicker.regional[ "uk" ] );
  jQuery(".dtpicker").datepicker({showOn: "button", buttonImage: "images/calendar.gif",
			buttonImageOnly: true});

  jQuery(".dtpicker").datepicker( "option", "dateFormat", "dd.mm.yy" );
  jQuery(".dtpicker").mask("99.99.9999");

  if(r_edit==3)
      r_edit_bool = true;
  else
      r_edit_bool = false;
  //----------------------------------------------------------------------------  
  jQuery('#tarif_grp_table').jqGrid({
    url:     'tarif_list_grp_data.php',
    editurl: 'tarif_list_grp_edit.php',
    datatype: 'json', 
    mtype: 'POST',
    height:200,
    width:800,
    colNames:[],
    colModel :[ 
      {name:'id', index:'id', width:40, editable: false, align:'center', key:true, hidden:true},
      {label:'Повна назва',name:'nm', index:'nm', width:200, editable: true, align:'left',edittype:"textarea", 
                editoptions:{rows:"2",cols:"50"} ,editrules:{required:true}},
      {label:'Скорочена назва',name:'sh_nm', index:'sh_nm', width:200, editable: true, align:'left',edittype:'text',
          editoptions:{size:50},editrules:{required:true}},
      {label:'Тип',name:'typ_tar', index:'typ_tar', width:40, editable: true, align:'center', hidden:true},
      {label:'Идент.',name:'ident', index:'ident', width:40, editable: true, align:'center', hidden:true,
            editrules:{required:true,edithidden:true}},
      {label:'Тип для пільги',name:'id_lgt_group', index:'id_lgt_group', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lgt_grp},editrules:{edithidden:true},
                            stype:'text', hidden:true},                       
      {name:'work_period', index:'work_period', width:80, editable: true, align:'left',edittype:'text', hidden:true},
      {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true},        
        
                            
    ],
    pager: '#tarif_grp_tablePager',
    autowidth: true,
    rowNum:50,
    //rowList:[20,50,100,300,500],
    sortname: 'ident',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Тарифні групи',
    hidegrid: false,
    
    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
    
  },
    
    onSelectRow: function(id) { 
          cur_tar_grp = id;
          jQuery('#tarif_table').jqGrid('setGridParam',{datatype: 'json','postData':{'p_id':cur_tar_grp}}).trigger('reloadGrid');        
      
    },
    
    ondblClickRow: function(id){ 
      if(selmode==1)
      {
           window.opener.SelectTarExternal(id,jQuery(this).jqGrid('getCell',id,'sh_nm') );
           window.opener.focus();
           self.close();            
      }

      if(selmode==0)
      {
         if (r_edit_bool)
            jQuery(this).editGridRow(id,{width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});  
      }

     } ,  

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#tarif_grp_tablePager',
        {edit:r_edit_bool,add:r_edit_bool,del:r_edit_bool},
        {width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {width:500,reloadAfterSubmit:true,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {reloadAfterSubmit:false,afterSubmit:processAfterEdit}, 
        {} 
        ); 

//==============================================================================

  var TarifEditOptions = {width:450, reloadAfterSubmit:true, closeAfterAdd:true,
        closeAfterEdit:true, 
        afterSubmit:processAfterEdit,
        onInitializeForm: function() {
            $('#per_min, #per_max').datepicker({
                showOn: "button", 
                buttonImage: "images/calendar.gif",
                buttonImageOnly: true,
                dateFormat:'dd.mm.yy'
                //dateFormat:'d MM'
            });

            $('#dt_b, #dt_e').datepicker({
                showOn: "button", 
                buttonImage: "images/calendar.gif",
                buttonImageOnly: true, 
                dateFormat:'dd.mm.yy'
            });

            $('#per_min, #per_max').attr('readonly','true'); 
            $('#per_min').parent("td").append('<button type="button" class ="btn btnSmall " id="btPer_minClear"> X </button>');
            $('#per_max').parent("td").append('<button type="button" class ="btn btnSmall " id="btPer_maxClear"> X </button>');
            $(".btn").button();            
            
            $("#btPer_minClear").click( function() 
            {
                $('#per_min').attr("value","");
            });

            $("#btPer_maxClear").click( function() 
            {
                $('#per_max').attr("value","");
            });

        },
        onClose: function() {
            $('.hasDatepicker').datepicker("hide");
        },
        beforeSubmit: function(postdata, formid){
            
            postdata.id_grptar = cur_tar_grp;
            if ((postdata.per_min!=' ')&&(postdata.per_min!=null))
            {
               // postdata.per_min = $.datepicker.formatDate('dd.mm.yy',
              //  $.datepicker.parseDate('d MM', postdata.per_min, {}));
            }
            if ((postdata.per_max!=' ')&&(postdata.per_max!=null))
            {
             //   postdata.per_max = $.datepicker.formatDate('dd.mm.yy',
             //   $.datepicker.parseDate('d MM', postdata.per_max, {}));
            }
                
         return[true,''];
        } 
    
    };


  jQuery('#tarif_table').jqGrid({
    url:'tarif_list_data.php',
    editurl: 'tarif_list_edit.php',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:120,
    width:400,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_grptar', index:'id_grptar', width:40, editable: false, align:'center',hidden:true},
    {name:'id_doc', index:'id_doc', width:40, editable: false, align:'center',hidden:true},
    
    {label:'Назва',name:'name', index:'name', width:200, editable: true, align:'left',edittype:'text',
          editoptions:{size:50},editrules:{required:true}},

    {label:'Спожив.мин', name:'lim_min', index:'lim_min',  width:60, editable: true,  align:'right', edittype:'text',
        formatter:'number',formatoptions: { defaultValue: ''},editrules:{number:true,edithidden:true}},
    {label:'Спожив.макс', name:'lim_max', index:'lim_max',  width:60, editable: true,  align:'right', edittype:'text',
        formatter:'number',formatoptions: { defaultValue: ''},editrules:{number:true,edithidden:true}},

    {label:'Діє з',name:'per_min', index:'per_min', width:100, editable: true, align:'left', 
        formatter:'date'
          //formatter:DayAndMonthFormatter
          //unformat:DayAndMonthUnFormatter
          //  ,formatter:'date', formatoptions:{srcformat:'Y-m-d', newformat:'d.m'}
         //   ,dataInit:function(el){ 
                                //$(el).datepicker( "option", "dateFormat", "dd.mm.yy" );
                                //$(el).datepicker({dateFormat:'yy-mm-dd'});
                                 // $(el).mask("99.99.9999");
                                 //$(el).datepicker({showOn: "button", buttonImage: "images/calendar.gif",buttonImageOnly: true});
       // }
    },
    {label:'Діє по',name:'per_max', index:'per_max', width:100, editable: true, align:'left', 
          formatter:'date'
//          formatter:DayAndMonthFormatter
    //    formatter:'date',  formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}
    },


    {label:'Дата встан.',name:'dt_b', index:'dt_b', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},
    {label:'Дата закінч.',name:'dt_e', index:'dt_e', width:80, editable: true, align:'left',edittype:'text',formatter:'date'},      
      
    {label:'Идент.',name:'ident', index:'ident', width:40, editable: true, align:'center', hidden:true,
        editrules:{edithidden:true}},
    
    {name:'work_period', index:'work_period', width:80, editable: true, align:'left',edittype:'text', hidden:true},
    {name:'dt', index:'dt', width:100, editable: true, align:'left', formatter:'date', hidden:true,
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
    {name:'user_name', index:'user_name', width:100, editable: true, align:'left',edittype:'text', hidden:true}        
    
    ],
    pager: '#tarif_tablePager',
    autowidth: true,
    rowNum:50,
    //rowList:[20,50,100,300,500],
    sortname: 'ident',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Тарифы',
    hiddengrid: tar_hidden,
    postData:{'p_id':0, show_closed: show_closed},
    
    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
    else
    {
       cur_tar=0;
       jQuery('#tarif_val_table').jqGrid('setGridParam',{'postData':{'p_id':cur_tar}}).trigger('reloadGrid');        
    }
    
  },
    
    onSelectRow: function(id) { 
          cur_tar=id;
          jQuery('#tarif_val_table').jqGrid('setGridParam',{datatype: 'json','postData':{'p_id':cur_tar}}).trigger('reloadGrid');        
      
    },
    
    ondblClickRow: function(id){ 
        if (r_edit_bool) jQuery(this).editGridRow(id,TarifEditOptions);  
    } ,  

    loadError : function(xhr,st,err) {
      jQuery('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);
    }
  
  //  jsonReader : { repeatitems: false }

  }).navGrid('#tarif_tablePager',
         {edit:r_edit_bool,add:r_edit_bool,del:r_edit_bool},
        TarifEditOptions, 
        TarifEditOptions, 
        {reloadAfterSubmit:false,afterSubmit:processAfterEdit}, 
        {} 
        ); 

//==============================================================================

  var TarifValEditOptions = {width:300, reloadAfterSubmit:true, closeAfterAdd:true,
        closeAfterEdit:true, 
        afterSubmit:processAfterEdit,
        onInitializeForm: function() {

            $('#dt_begin').datepicker({
                showOn: "button", 
                buttonImage: "images/calendar.gif",
                buttonImageOnly: true, 
                dateFormat:'dd.mm.yy'
            });
        },
        onClose: function() {
            $('.hasDatepicker').datepicker("hide");
        },
        beforeSubmit: function(postdata, formid){
            
            postdata.id_tarif = cur_tar;
                
         return[true,''];
        } 
    
    };


jQuery("#tarif_table").jqGrid('navButtonAdd','#tarif_tablePager',{id:'btn_all_tar', caption:"Показати закриті",
	onClickButton:function(){ 

      if (show_closed ==0)
      {
          jQuery('#btn_all_tar').addClass('navButton_selected') ;    
          show_closed =1;
          $('#tarif_table').jqGrid('setGridParam',{postData:{'p_id':cur_tar_grp ,show_closed: show_closed}}).trigger('reloadGrid');     
     }
     else
     {
        jQuery('#btn_all_tar').removeClass('navButton_selected') ;    
        show_closed=0;
        $('#tarif_table').jqGrid('setGridParam',{postData:{'p_id':cur_tar_grp, show_closed: show_closed}}).trigger('reloadGrid');              
     }

   ;} 

});


  jQuery('#tarif_val_table').jqGrid({
    url:'tarif_list_val_data.php',
    editurl: 'tarif_list_val_edit.php',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    height:200,
    width:400,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'id_tarif', index:'id_tarif', width:40, editable: false, align:'center',hidden:true},
    {label:'Дата встан.',name:'dt_begin', index:'dt_begin', width:100, editable: true, 
                        align:'left',edittype:'text',formatter:'date',
                        editrules:{required:true}},
    {label:'Сума,грн',name:'value', index:'value', width:100, editable: true, align:'right',hidden:false,
                        edittype:'text',formatter:'number',editrules:{required:true,number:true},
                        formatoptions: {decimalPlaces: 4, defaultValue: ' '}},           
    {name:'dt', index:'dt', width:110, editable: false, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}
    ],
    pager: '#tarif_val_tablePager',
    autowidth: true,
    shrinkToFit : false,
    rowNum:50,
    rowList:[20,50,100],
    sortname: 'dt_begin',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Суми тарифа',
    hiddengrid: tar_hidden,
    postData:{'p_id':0},
    
    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
    }
    
  },
    
    ondblClickRow: function(id){ 
         if (r_edit_bool) jQuery(this).editGridRow(id,TarifValEditOptions);  
    } ,  

    loadError : function(xhr,st,err) {
      jQuery('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);
    }
  
  //  jsonReader : { repeatitems: false }

  }).navGrid('#tarif_val_tablePager',
         {edit:r_edit_bool,add:r_edit_bool,del:r_edit_bool},
        TarifValEditOptions, 
        TarifValEditOptions, 
        {reloadAfterSubmit:false,afterSubmit:processAfterEdit}, 
        {} 
        ); 



jQuery("#tarif_grp_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
      jQuery(this).editGridRow(id,{width:500,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,afterSubmit:processAfterEdit});}} );

jQuery("#tarif_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
      jQuery(this).editGridRow(id,TarifEditOptions);}} );

jQuery("#tarif_val_table").jqGrid('bindKeys', {"onEnter":function( id ) { 
      jQuery(this).editGridRow(id,TarifValEditOptions);}} );



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
            jQuery("#tarif_grp_table").jqGrid('setGridWidth',_pane.innerWidth()-20);
            jQuery("#tarif_table").jqGrid('setGridWidth',_pane.innerWidth()-20);
            jQuery("#tarif_val_table").jqGrid('setGridWidth',_pane.innerWidth()-20);
        }

	});
        
        if(selmode!=0)
        {
            outerLayout.hide('north');        
        };    
        
        outerLayout.resizeAll();
        outerLayout.close('south');     

 function processAfterEdit(response, postdata) {
            //alert(response.responseText);
            if (response.responseText=='') {return [true,''];}
            else
            {
             errorInfo = jQuery.parseJSON(response.responseText);
             
             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]}; 

             if (errorInfo.errcode==1) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
               return [true,errorInfo.errstr]};              

             if (errorInfo.errcode==-1) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');  
               //jQuery('#paccnt_lgt_table').jqGrid('setGridParam',{'postData':{'p_id':id_paccnt}}).trigger('reloadGrid');                       
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               jQuery('#message_zone').append(errorInfo.errstr);  
               jQuery('#message_zone').append('<br>');                 
               jQuery('#message_zone').dialog('open');
               return [false,errorInfo.errstr]};              
            }
        }

}); 
 

 //jQuery('#lshow_grid').click( function() { jQuery('#dov_meters_table').jqGrid('setGridParam',{caption: 'Счетчики 111'}).trigger('reloadGrid')});



function DayAndMonthFormatter (cellvalue, options, rowObject)
{
   if (cellvalue!=null)
    return $.datepicker.formatDate('d MM', new Date(cellvalue), {});
   else 
   return " ";   
   
};

function DayAndMonthUnFormatter (cellvalue, options, cell )
{
   if (cellvalue!=null)
    return $.datepicker.parseDate('d MM', cellvalue, {});
   //else 
   //return " ";   
   
}
