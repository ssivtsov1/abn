jQuery(function(){ 

   $("#bill_info").dialog({

			resizable: true,
			//height:550,
                        width:905,
			modal: true,
                        autoOpen: false,
                        title:'Рахунок',
                        resize: function(event, ui) 
                        {
                           $("#bill_info1_table").jqGrid('setGridWidth',$("#bill_info").innerWidth()-10);
                           $("#bill_info2_table").jqGrid('setGridWidth',$("#bill_info").innerWidth()-10);
                           $("#bill_info3_table").jqGrid('setGridWidth',$("#bill_info").innerWidth()-10);
                           $("#bill_info4_table").jqGrid('setGridWidth',$("#bill_info").innerWidth()-10);
                        }
                        
                    });


   $("#bill_info_lost").dialog({

			resizable: true,
			//height:550,
                        width:905,
			modal: true,
                        autoOpen: false,
                        resize: function(event, ui) 
                        {
                           $("#bill_info5_table").jqGrid('setGridWidth',$("#bill_info").innerWidth()-10);
                           //$("#bill_info6_table").jqGrid('setGridWidth',$("#bill_info").innerWidth()-10);
                        }
                        
                    });

//--------------------------- детализация счетов -------------------------------

  jQuery('#bill_info1_table').jqGrid({
    url:'abon_en_bill_det1_data.php',
    editurl: '',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    //height:100,
    height:'auto',
    width:895,
    //autowidth: true,
    scroll: 0,
    colNames:[],
    colModel :[ 
    
    {label:'Лічильник',name:'num_meter', index:'num_meter', width:60, editable: true, align:'left',edittype:'text'},            
    {label:'Тариф',name:'tar_name', index:'tar_name', width:150, editable: true, align:'left',edittype:'text'},
    {label:'Сума тар.',name:'value', index:'value', width:60, editable: true, align:'right',hidden:false,
                            edittype:'text'},           
    {label:'Зона',name:'zone', index:'zone', width:60, editable: true, align:'left',edittype:'text'},                
    {label:'Коеф.',name:'koef', index:'koef', width:40, editable: true, align:'left',edittype:'text',formatter:'number'},

    {label:'Квтг',name:'demand', index:'demand', width:50, editable: true, align:'right',hidden:false, edittype:'text'},
    {label:'Квтг неокр.',name:'demand_add', index:'demand_add', width:70, editable: true, align:'right',hidden:false, edittype:'text'},
    
    {label:'Сума,грн',name:'summ', index:'summ', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    {label:'Дата нач.',name:'dat_b', index:'dat_b', width:80, editable: true, align:'left',edittype:'text', formatter:'date', hidden:false},
    {label:'Дата кінц.',name:'dat_e', index:'dat_e', width:80, editable: true, align:'left',edittype:'text', formatter:'date', hidden:false}
    ],
    pager: '#bill_info1_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],
    sortname: '',
    sortorder: '',
    viewrecords: true,
    gridview: true,
    caption: 'Тарифи',
    //hiddengrid: false,
    hidegrid: true,
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    postData:{'id_doc': 0},
    jsonReader : {repeatitems: false},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
     
     $(this).jqGrid('setGridState', 'visible');
    }
    else
       $(this).jqGrid('setGridState', 'hidden');
   },

   onSelectRow: function(rowid) { 
        
    },

    ondblClickRow: function(id){ 
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#bill_info1_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 


  jQuery('#bill_info2_table').jqGrid({
    url:'abon_en_bill_det2_data.php',
    editurl: '',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    //height:80,
    height:'auto',
    width:895,
    //autowidth: true,
    scroll: 0,
    colNames:[],
    colModel :[ 
    {label:'Код пільг.',name:'alt_code', index:'alt_code', width:60, editable: true, align:'left',edittype:'text'},            
    {label:'Пільга',name:'name_lgt', index:'name_lgt', width:100, editable: true, align:'left',edittype:'text'},            
    {label:'ФІО',name:'fio_lgt', index:'fio_lgt', width:50, editable: true, align:'left',edittype:'text'},            
    {label:'Тип тарифа',name:'tar_grp', index:'tar_grp', width:100, editable: true, align:'left',edittype:'text',hidden:true},
    {label:'мін, кВтг',name:'norm_min', index:'norm_min', width:60, editable: true, align:'right',hidden:false,
                        edittype:'text',formatter:'number',editrules:{required:false,number:true},
                        formatoptions: {defaultValue: ' '}},           

    {label:"дод, кВтг",name:'norm_one', index:'norm_one', width:60, editable: true, align:'right',hidden:false,
                        edittype:'text',formatter:'number',editrules:{required:false,number:true},
                        formatoptions: {defaultValue: ' '}},           

    {label:'макс, кВтг',name:'norm_max', index:'norm_max', width:60, editable: true, align:'right',hidden:false,
                        edittype:'text',formatter:'number',editrules:{required:false,number:true},
                        formatoptions: {defaultValue: ' '}},           
    {label:'% опл.',name:'percent', index:'percent', width:100, editable: true, align:'right',hidden:true,
                        edittype:'text',formatter:'number',
                        formatoptions: {defaultValue: ' '}},           


    {label:"Кільк.",name:'famyly_cnt', index:'famyly_cnt', width:60, editable: true, align:'left',edittype:'text'},                    

    {label:'Норма пільги',name:'norm_abon', index:'norm_abon', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    {label:'Норма опалення',name:'norm_abon_heat', index:'norm_abon_heat', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    {label:'Норма розр.',name:'norm_lgt', index:'norm_lgt', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           

    {label:'Норма розр.точн.',name:'norm_add_lgt', index:'norm_add_lgt', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text'},           

    {label:'Дата нач.',name:'dt_beg', index:'dt_beg', width:80, editable: true, align:'left',edittype:'text', formatter:'date', hidden:false},
    {label:'Дата кінц.',name:'dt_fin', index:'dt_fin', width:80, editable: true, align:'left',edittype:'text', formatter:'date', hidden:false}
    ],
    pager: '#bill_info2_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],
    sortname: '',
    sortorder: '',
    viewrecords: true,
    gridview: true,
    caption: 'Норма пільги',
    //hiddengrid: false,
    hidegrid: true,
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    postData:{'id_doc': 0},
    jsonReader : {repeatitems: false},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
     
     $(this).jqGrid('setGridState', 'visible');
    }
    else
       $(this).jqGrid('setGridState', 'hidden');

   },

   onSelectRow: function(rowid) { 
        
    },

    ondblClickRow: function(id){ 
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#bill_info2_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
        
        
  jQuery('#bill_info3_table').jqGrid({
    url:'abon_en_bill_det3_data.php',
    editurl: '',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    //height:80,
    height:'auto',
    width:895,
    //autowidth: true,
    scroll: 0,
    colNames:[], 
    colModel :[ 
    
    //{label:'Лічильник',name:'num_meter', index:'num_meter', width:60, editable: true, align:'left',edittype:'text'},            
    {label:'Тариф',name:'tar_name', index:'tar_name', width:100, editable: true, align:'left',edittype:'text'},
    {label:'Сума тар.',name:'value', index:'value', width:60, editable: true, align:'right',hidden:false,
                            edittype:'text'},           
    {label:'Зона',name:'zone', index:'zone', width:60, editable: true, align:'left',edittype:'text'},                
    {label:'Коеф.',name:'koef', index:'koef', width:40, editable: true, align:'left',edittype:'text',formatter:'number'},                    

    {label:'Пільга',name:'lgt_name', index:'lgt_name', width:60, editable: true, align:'left',edittype:'text',hidden:true},            

    {label:'Всього кВтг',name:'demand', index:'demand', width:50, editable: true, align:'right',hidden:false,
                            edittype:'text'},
    {label:'Пільга кВтг',name:'demand_lgt', index:'demand_lgt', width:50, editable: true, align:'right',hidden:false,
                            edittype:'text'},

    {label:'Пільга(6зн.)кВтг',name:'demand_add_lgt', index:'demand_add_lgt', width:50, editable: true, align:'right',hidden:false,
                            edittype:'text'},

    {label:'% опл.',name:'percent', index:'percent', width:40, editable: true, align:'right',hidden:false,
                        edittype:'text',formatter:'number',editrules:{required:true,number:true},
                        formatoptions: {defaultValue: ' '}},           
                        
                        
    {label:'пільг.тариф',name:'lgt_tar', index:'lgt_tar', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text'},           

    {label:'Пільга,грн',name:'summ_lgt', index:'summ_lgt', width:80, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'number'},           
    {label:'Дата нач.',name:'dt_beg', index:'dt_beg', width:80, editable: true, align:'left',edittype:'text', formatter:'date', hidden:false},
    {label:'Дата кінц.',name:'dt_fin', index:'dt_fin', width:80, editable: true, align:'left',edittype:'text', formatter:'date', hidden:false}
    ],
    pager: '#bill_info3_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],
    sortname: '',
    sortorder: '',
    viewrecords: true,
    gridview: true,
    caption: 'Розрахунок пільги',
    //hiddengrid: false,
    hidegrid: true,
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    postData:{'id_doc': 0},
    jsonReader : {repeatitems: false},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
     
     $(this).jqGrid('setGridState', 'visible');
    }
    else
       $(this).jqGrid('setGridState', 'hidden');
    
   },

   onSelectRow: function(rowid) { 
        
    },

    ondblClickRow: function(id){ 
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#bill_info3_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
        
        
  jQuery('#bill_info4_table').jqGrid({
    url:'abon_en_bill_det4_data.php',
    editurl: '',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    //height:60,
    height:'auto',
    width:895,
    //autowidth: true,
    scroll: 0,
    colNames:[], 
    colModel :[ 
    
    {label:'К-во мес.',name:'val_month', index:'val_month', width:60, editable: true, align:'left',edittype:'text'},
    {label:'К-во субс.',name:'kol_subs', index:'kol_subs', width:60, editable: true, align:'left',edittype:'text'},
    {label:'Обяз.пл.',name:'ob_pay', index:'ob_pay', width:100, editable: true, align:'left',edittype:'text'},
    {label:'Сума субс.',name:'subs_all', index:'subs_all', width:100, editable: true, align:'left',edittype:'text'},
    {label:'Сума субс. месяц',name:'subs_month', index:'subs_month', width:100, editable: true, align:'left',edittype:'text'},
    {label:'кВтг.норм',name:'norma_subskwt', index:'norma_subskwt', width:60, editable: true, align:'left',edittype:'text'},
    {label:'Перерах.кВт',name:'recalc_kwt', index:'recalc_kwt', width:60, editable: true, align:'left',edittype:'text'},
    {label:'Перерах.субс',name:'recalc_subs', index:'recalc_subs', width:100, editable: true, align:'left',edittype:'text'}
    ],
    pager: '#bill_info4_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],
    sortname: '',
    sortorder: '',
    viewrecords: true,
    gridview: true,
    caption: 'Розрахунок субсидії',
    //hiddengrid: false,
    hidegrid: true,
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    postData:{'id_doc': 0},
    //postData:{'id_client': 0, 'p_mmgg':0},
    jsonReader : {repeatitems: false},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
     
     $(this).jqGrid('setGridState', 'visible');
    }
    else
       $(this).jqGrid('setGridState', 'hidden');
    
   },

   onSelectRow: function(rowid) { 
        
    },

    ondblClickRow: function(id){ 
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#bill_info4_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 
        
        
$("#bill_info").find("#bt_bill_info_close").click( function() 
{
  jQuery("#bill_info").dialog('close');                           
});


//--------------------------- детализация потерь -------------------------------

  jQuery('#bill_info5_table').jqGrid({
    url:'abon_en_bill_det5_data.php',
    editurl: '',
    //datatype: 'json',
    datatype: 'local',
    mtype: 'POST',
    //height:100,
    height:'auto',
    width:895,
    shrinkToFit : false,
    autowidth: true,
    scroll: 0,
    colNames:[],
    colModel :[ 
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true},
    {name:'lvl', index:'lvl', width:40, editable: false, align:'center',hidden:true},
    {label:'Обладнання',name:'name_eqp', index:'name_eqp', width:60, editable: true, align:'left',edittype:'text'},            
    {label:'Категорія',name:'kind', index:'kind', width:60, editable: true, align:'left',edittype:'text'},            
    {label:'Тип',name:'type_name', index:'type_name', width:60, editable: true, align:'left',edittype:'text'},            
    {label:'Потужн/довж',name:'sn_len', index:'sn_len', width:60, editable: true, align:'left',edittype:'text'},
    {label:'Напруга',name:'voltage', index:'voltage', width:60, editable: true, align:'left',edittype:'text'},    
    {label:'Втрати,Квтг',name:'dw', index:'dw', width:50, editable: true, align:'right',hidden:false,
                            edittype:'text'},

    {label:'Акт.спож',name:'wp', index:'wp', width:50, editable: true, align:'right',hidden:false,
                            edittype:'text'},
    {label:'Реакт.спож',name:'wq', index:'wq', width:50, editable: true, align:'right',hidden:false,
                            edittype:'text'},
    {label:'Повне спож.',name:'wp_summ', index:'wp_summ', width:50, editable: true, align:'right',hidden:false,
                            edittype:'text'},
    {label:'Пот.ХХ',name:'s_xx_addwp', index:'s_xx_addwp', width:50, editable: true, align:'right',hidden:false,
                            edittype:'text'},
    {label:'Пот.КЗ',name:'s_kz_addwq', index:'s_kz_addwq', width:50, editable: true, align:'right',hidden:false,
                            edittype:'text'},

    {label:'Пот.КЗ',name:'s_kz_addwq', index:'s_kz_addwq', width:50, editable: true, align:'right',hidden:false,
                            edittype:'text'},
    {label:'Час tt',name:'tt', index:'tt', width:50, editable: true, align:'right',hidden:false,
                           edittype:'text'},
    {label:'Час tw',name:'tw', index:'tw', width:50, editable: true, align:'right',hidden:false,
                            edittype:'text'},
    {label:'Втр.ХХ/акт.опір',name:'pxx_r0', index:'pxx_r0', width:50, editable: true, align:'left',edittype:'text',formatter:'number'},
    {label:'Втр.КЗ/реакт.опір',name:'pkz_x0', index:'pkz_x0', width:50, editable: true, align:'left',edittype:'text',formatter:'number'},
    {label:'Струм ХХ',name:'ixx', index:'ixx', width:50, editable: true, align:'left',edittype:'text',formatter:'number'},
    {label:'Напруга КЗ',name:'ukz_un', index:'ukz_un', width:50, editable: true, align:'left',edittype:'text',formatter:'number'},
    {label:'Дата нач.',name:'dat_b', index:'dat_b', width:80, editable: true, align:'left',edittype:'text', formatter:'date', hidden:false},
    {label:'Дата кінц.',name:'dat_e', index:'dat_e', width:80, editable: true, align:'left',edittype:'text', formatter:'date', hidden:false}
    ],
    pager: '#bill_info5_tablePager',
    rowNum:100,
    //rowList:[20,50,100,300,500],
    sortname: 'lvl',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Втрати',
    //hiddengrid: false,
    hidegrid: true,
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    postData:{'id_doc': 0},
    jsonReader : {repeatitems: false},

    gridComplete:function(){

    if ($(this).getDataIDs().length > 0) 
    {      
     var first_id = parseInt($(this).getDataIDs()[0]);
     $(this).setSelection(first_id, true);
     
     //$(this).jqGrid('setGridState', 'visible');
    }
    //else
    //   $(this).jqGrid('setGridState', 'hidden');
   },

   onSelectRow: function(rowid) { 
        
    },

    ondblClickRow: function(id){ 
    } ,  
      
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#bill_info5_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

});

 