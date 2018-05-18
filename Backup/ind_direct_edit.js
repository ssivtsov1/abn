var isNewIndicationGridCreated = false;
var ind_date;
var selICol ;
var selIRow ;
var cur_indic_id;
var indic_flock=0;
var indic_fedit=0;

var createNewIndicationGrid = function(fmode){ 
    
  if (isNewIndicationGridCreated)
      { 
        ind_date = $("#dialog-indications").find("#fdt_ind").val();  
        jQuery('#new_indications_table').jqGrid('setGridParam',{'postData':{'p_id': id_paccnt, 'w_date': ind_date, 'ind_id': indic_edit_row_id, 'mode': fmode}}).trigger('reloadGrid');                       
        return; 
      }
  isNewIndicationGridCreated =true;
  
  //$("#dialog-indications").find("#fdt_ind").datepicker( "setDate" , Date.now().toString("dd.MM.yyyy") );

  ind_date = $("#dialog-indications").find("#fdt_ind").val();

  jQuery('#new_indications_table').jqGrid({
    url:'abon_ensaldo_new_indic_data.php',
    datatype: 'json',
    mtype: 'POST',
    height:215,
    //width:785,
    width:785,
    colNames:[],
    colModel:[
    {name:'id', index:'id', width:40, editable: false, align:'center', key:true ,hidden:true,sortable:false},
    {name:'id_paccnt', index:'id_paccnt', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {label:'Код',name:'id_meter', index:'id_meter', width:60, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_type_meter', index:'id_type_meter', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    {name:'id_p_indic', index:'id_p_indic', width:40, editable: false, align:'center',hidden:true,sortable:false},    
    //{name:'id_metzone', index:'id_metzone', width:40, editable: false, align:'center',hidden:true,sortable:false},    

    {label:'№ ліч.',name:'num_meter', index:'num_meter', width:80, editable: false, align:'left',edittype:'text',sortable:false},            
    {label:'Тип ліч.',name:'type_meter', index:'type_meter', width:80, editable: false, align:'left',edittype:'text',sortable:false,hidden:false},                
    {label:'Розр. ліч.',name:'carry', index:'carry', width:40, editable: false, align:'left',edittype:'text',sortable:false},                    
    {label:'К.тр',name:'k_tr', index:'k_tr', width:40, editable: false, align:'left',edittype:'text',sortable:false,hidden:true},
    {label:'Зона',name:'id_zone', index:'id_zone', width:60, editable: true, align:'right',
                            edittype:'select',formatter:'select',editoptions:{value:lzones},stype:'text',sortable:false},                       
 
    {label:'Попер.пок.',name:'p_indic', index:'p_indic', width:80, editable: false, align:'right',hidden:false,
                            edittype:'text',sortable:false,formatter:'integer'},           

    {label:'Дата поп.',name:'dt_p_indic', index:'dt_p_indic', width:80, editable: false, 
                        align:'left',edittype:'text',formatter:'date',sortable:false,hidden:false},

    {label:'Пот.показ.',name:'indic', index:'indic', width:80, editable: true, align:'right',hidden:false,
            edittype:'text',
            sortable:false,
            classes: 'editable_column_class',
            //formatter:'integer',
            editrules:{
                number:true
            },
            editoptions: {
                dataInit : function (elem) {
                    $(elem).focus(function(){
                        this.select();
                    })
                },
                dataEvents: [
                { 
                    type: 'keydown', 
                    fn: function(e) { 
                        var key = e.charCode || e.keyCode;
                        if (key == 13)//enter
                        {
                            setTimeout("jQuery('#new_indications_table').editCell(" + selIRow + " + 1, " + selICol + ", true);", 100);
                        }
                    }
                } 
                ]
            }                            
                        },
    {label:'Дата',name:'dt_indic', index:'dt_indic', width:75, editable: true, 
                        align:'left',edittype:'text',formatter:'date',sortable:false,classes: 'editable_column_class',
            editoptions: {
                dataInit : function (elem) {
                    $(elem).focus(function(){
                        this.select();
                    })
                },
                dataEvents: [
                { 
                    type: 'keydown', 
                    fn: function(e) { 
                        var key = e.charCode || e.keyCode;
                      //  if(key == 9)   // tab
                      //  {
                      //      setTimeout("jQuery('#new_indications_table').editCell(" + selIRow + " + 1, 17, true);", 100);
                      //  }
                        if (key == 13)//enter
                        {
                            setTimeout("jQuery('#new_indications_table').editCell(" + selIRow + " + 1, " + selICol + ", true);", 100);
                        }
                    }
                } 
                ]
            }                        
                    },

    {label:'Споживання',name:'demand', index:'demand', width:80, editable: false, align:'right',hidden:false,
                            edittype:'text',sortable:false,formatter:'integer'},           

    {label:'Тип показників',name:'id_operation', index:'id_operation', width:120, editable: true, align:'left',
                            edittype:'select',formatter:'select',editoptions:{value:lindicoper},
            classes: 'editable_column_class'    
/*
editoptions: {
                //dataInit : function (elem) {
                //    $(elem).focus(function(){
                //        this.select();
                //    })
                //},
                dataEvents: [
                { 
                    type: 'keydown', 
                    fn: function(e) { 
                        var key = e.charCode || e.keyCode;
                        if (key == 13)//enter
                        {
                            setTimeout("jQuery('#new_indications_table').editCell(" + selIRow + " + 1, " + selICol + ", true);", 100);
                        }
                    }
                } 
                ]
            }                            */
                        },
    {label:'Період',name:'mmgg', index:'mmgg', width:80, editable: true, 
           align:'left',edittype:'text',formatter:'date',sortable:false,classes: 'editable_column_class' },
       
    {label:'Факт пок.',name:'indic_real', index:'indic_real', width:80, editable: true, align:'right',hidden:false,
            edittype:'text',
            sortable:false,
            classes: 'editable_column_class',
            //formatter:'integer',
            editrules:{
                number:true
            },
            editoptions: {
                dataInit : function (elem) {
                    $(elem).focus(function(){
                        this.select();
                    })
                },
                dataEvents: [
                { 
                    type: 'keydown', 
                    fn: function(e) { 
                        var key = e.charCode || e.keyCode;
                        if (key == 13)//enter
                        {
                            setTimeout("jQuery('#new_indications_table').editCell(" + selIRow + " + 1, " + selICol + ", true);", 100);
                        }
                    }
                } 
                ]
            }                            
    }
       
                        

    ],
    pager: '#new_indications_tablePager',
    //autowidth: true,
    //shrinkToFit : false,
    rowNum:500,
    //rowList:[50,100,200],
    sortname: 'id',
    sortorder: 'desc',
    viewrecords: true,
    //gridview: true,
    caption: 'Показники',
    //hiddengrid: false,
    forceFit : true,
    hidegrid: false,    
    postData:{'p_id': id_paccnt, 'w_date': ind_date, 'ind_id': indic_edit_row_id, 'mode': fmode},
    cellEdit: true, 
    cellsubmit: 'clientArray',
    pgbuttons: false,     // disable page control like next, back button
    pgtext: null,         // disable pager text like 'Page 0 of 10'
    
    
    gridComplete:function(){

      var myUserData = $(this).jqGrid('getGridParam', 'userData')
      indic_flock = myUserData['flock'];
      indic_fedit = myUserData['fedit'];
  
    },
    onSelectRow: function(id) { 
          cur_indic_id = id;
    },
    
    beforeEditCell : function(rowid, cellname, value, iRow, iCol)
    {
        selICol = iCol;
        selIRow = iRow;
    },    
    
    afterEditCell: function (id,name,val,iRow,iCol)
    { 
        if(name=='dt_indic') 
        {
            jQuery("#"+iRow+"_dt_indic","#new_indications_table").mask("99.99.9999"); 
        }
        
        if(name=='mmgg') 
        {
            jQuery("#"+iRow+"_mmgg","#new_indications_table").mask("99.99.9999"); 
        }
        
    },     
     afterSaveCell : function(rowid,name,val,iRow,iCol) {
            if(name == 'indic') {
                var global_dt = ind_date;
                var p_ind = parseFloat(jQuery("#new_indications_table").jqGrid('getCell',rowid,iCol-2));
                var k_tr = parseFloat(jQuery("#new_indications_table").jqGrid('getCell',rowid,iCol-4));
                var dt_ind = jQuery("#new_indications_table").jqGrid('getCell',rowid,iCol+1);
                var dt_prev_ind = $.trim(jQuery("#new_indications_table").jqGrid('getCell',rowid,iCol-1));
                //alert(dt_prev_ind);
                var carry = parseFloat(jQuery("#new_indications_table").jqGrid('getCell',rowid,iCol-5));
                var ind = parseFloat(val);
                var length = dt_ind.length;
                var dem=0;
                if (length<6)
                    {
                      jQuery("#new_indications_table").jqGrid('setRowData',rowid,{dt_indic: global_dt});
                    };
                    
                var tr = jQuery('#new_indications_table')[0].rows.namedItem(rowid); 
                var td = tr.cells[iCol];
                $(td).removeClass("err_column_class");                    
                $(td).removeClass("mod_column_class");                    
                
                if ((val.length==0)||(dt_prev_ind==''))
                {
                    jQuery("#new_indications_table").jqGrid('setRowData',rowid,{
                        demand: ''
                    });    
                }
                else
                {

                    if (Math.round(ind).toString().length>carry)    
                    {
                        
                        jQuery("#dialog-confirm").find("#dialog-text").html('Показники перевищують розрядність лічильника!');
                        $("#dialog-confirm").dialog({
                            resizable: false,
                            height:140,
                            modal: true, 
                            autoOpen: false,
                            title:'Помилкові показники',
                            buttons: {
                                "Ок": function() {
                                    $( this ).dialog( "close" );
                                }
                            }
                        });
    
                        jQuery("#dialog-confirm").dialog('open');   
                        
                        jQuery('#new_indications_table').setCell(rowid,name,'','err_column_class');  
                        
                        jQuery("#new_indications_table").jqGrid('setRowData',rowid,{
                            demand: ''
                        });    
                        
                    }
                    else
                    {
                        var dem=0;
                        if (ind >= p_ind)
                        {
                            dem = (ind - p_ind)* k_tr;       
                            jQuery("#dialog-confirm").find("#dialog-text").html('Споживання перевищує 1000 кВтч!');
                        }
                        else
                        { 
                            var max_val = parseFloat(str_pad('1',carry+1,'0'));
                            dem = (ind + (max_val - p_ind))* k_tr;   
                            jQuery("#dialog-confirm").find("#dialog-text").html('Поточні показники менші за попередні!');
                        }

                        if ((dem > 1000)&&(dt_prev_ind!=''))
                        {
                               // dem = 0;
                            
                              //jQuery("#dialog-confirm").find("#dialog-text").html('Споживання перевищує 1000 кВтч!');
                              $("#dialog-confirm").dialog({
                                    resizable: false,
                                    height:140,
                                    modal: true,
                                    autoOpen: false,
                                    title:'Підозрілі показники',
                                    buttons: {
                                        "Ок": function() {
                                            $( this ).dialog( "close" );
                                        }
                                    }
                                });
    
                              jQuery("#dialog-confirm").dialog('open');                                 
                              jQuery('#new_indications_table').setCell(rowid,name,'','err_column_class');  

                        }

                        jQuery("#new_indications_table").jqGrid('setRowData',rowid,{
                            demand: dem
                        });    
                    
                        jQuery('#new_indications_table').setCell(rowid,name,'','mod_column_class');
                    }
                }   
                     
             };
             
             if(name == 'dt_indic') {
                 
                var dt_ind = $("#new_indications_table").jqGrid('getCell',rowid,iCol);
                var dt_prev_ind = $("#new_indications_table").jqGrid('getCell',rowid,iCol-2);
                var mmgg_ind = $("#new_indications_table").jqGrid('getCell',rowid,iCol+3);
                
                if (dt_ind.trim()!='')
                {
                
                  var date_ind = Date.parse( dt_ind, "dd.MM.yyyy");
                  var date_prev_ind = Date.parse( dt_prev_ind, "dd.MM.yyyy");
                  var yyyy = date_ind.getFullYear(); 
                  var date_mmgg_ind = Date.parse( mmgg_ind, "dd.MM.yyyy");
                  var date_mmgg_next = new Date(date_mmgg_ind);
                  date_mmgg_next.add({months: 1});

                  if (((yyyy>2050)||(yyyy<1900))&&(dt_ind.trim()!=''))
                  {
                    
                    jQuery("#dialog-confirm").find("#dialog-text").html('Неправильна дата!');
                    $("#dialog-confirm").dialog({
                        resizable: false,
                        height:140,
                        modal: true,
                        autoOpen: false,
                        title:'Увага',
                        buttons: {
                            "Ок": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
    
                    jQuery("#dialog-confirm").dialog('open');   
                  }
                  
                  if (dt_prev_ind == dt_ind)
                  {
                    $('#indic_table').setCell(rowid,name,'','err_column_class');  
                    
                    jQuery("#dialog-confirm").find("#dialog-text").html('Вже є показники на цю дату!');
                    $("#dialog-confirm").dialog({
                        resizable: false,
                        height:140,
                        modal: true,
                        autoOpen: false,
                        title:'Увага',
                        buttons: {
                            "Ок": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
    
                    jQuery("#dialog-confirm").dialog('open');   
                   
                  }
                  if (date_prev_ind > date_ind)
                  {
                    $('#indic_table').setCell(rowid,name,'','err_column_class');  
                    
                    jQuery("#dialog-confirm").find("#dialog-text").html('Поточна дата менша за попередню!');
                    $("#dialog-confirm").dialog({
                        resizable: false,
                        height:140,
                        modal: true,
                        autoOpen: false,
                        title:'Увага',
                        buttons: {
                            "Ок": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
    
                    jQuery("#dialog-confirm").dialog('open');   
                    
                  }
                  
                  if (( date_ind< date_mmgg_ind )&&(dt_ind.trim()!='')&&(dt_ind.trim()!='__.__.____'))
                  {
                    //alert (dt_ind);
                    $('#indic_table').setCell(rowid,name,'','err_column_class');  
                    
                    jQuery("#dialog-confirm").find("#dialog-text").html('Поточна дата менша за початок поточного місяця!');
                    $("#dialog-confirm").dialog({
                        resizable: false,
                        height:140,
                        modal: true,
                        autoOpen: false,
                        title:'Увага',
                        buttons: {
                            "Ок": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
    
                    jQuery("#dialog-confirm").dialog('open');   
                  }
                  if (( date_ind>= date_mmgg_next )&&(dt_ind.trim()!='')&&(dt_ind.trim()!='__.__.____'))
                  {
                    $('#indic_table').setCell(rowid,name,'','err_column_class');  
                    
                    jQuery("#dialog-confirm").find("#dialog-text").html('Поточна дата більша за кінець поточного місяця!');
                    $("#dialog-confirm").dialog({
                        resizable: false,
                        height:140,
                        modal: true,
                        autoOpen: false,
                        title:'Увага',
                        buttons: {
                            "Ок": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
    
                    jQuery("#dialog-confirm").dialog('open');   
                    
                  }                  
                  
                  
                }
                else
                {
                    jQuery("#dialog-confirm").find("#dialog-text").html('Порожня дата!');
                    $("#dialog-confirm").dialog({
                        resizable: false,
                        height:140,
                        modal: true,
                        autoOpen: false,
                        title:'Увага',
                        buttons: {
                            "Ок": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
    
                    jQuery("#dialog-confirm").dialog('open');   
                    
                }
                    
                
             }             

             if(name == 'mmgg') {
                 
                var mmgg_ind = $("#new_indications_table").jqGrid('getCell',rowid,iCol);
                if (mmgg_ind.trim()!='')
                {
                
                  var date_mmgg_ind = Date.parse( mmgg_ind, "dd.MM.yyyy");
                  var yyyy = date_mmgg_ind.getFullYear(); 

                  if (((yyyy>2050)||(yyyy<1900))&&(mmgg_ind.trim()!=''))
                  {
                    
                    jQuery("#dialog-confirm").find("#dialog-text").html('Неправильний період!');
                    $("#dialog-confirm").dialog({
                        resizable: false,
                        height:140,
                        modal: true,
                        autoOpen: false,
                        title:'Увага',
                        buttons: {
                            "Ок": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
    
                    jQuery("#dialog-confirm").dialog('open');   
                }
              }
             }             

            //jQuery('#new_indications_table').setCell(rowid,name,'','mod_column_class');
            jQuery('#new_indications_table').setCell(rowid,'dt_indic','','mod_column_class');
            jQuery('#new_indications_table').setCell(rowid,'demand','','mod_column_class');
        },    
    
    
    //ondblClickRow: function(id){ 
    //     jQuery(this).editGridRow(id,LgtNormEditOptions);  
    //} ,  

    loadError : function(xhr,st,err) {
      jQuery('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText);
    },
  
    jsonReader : {repeatitems: false}

  }).navGrid('#accnt_tablePager',
         {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ).jqGrid('bindKeys'); 
  
        
};   