var isIndicInfoCreated = false;

var createIndicInfoGrid = function(id_paccnt,id_zone, book, code){ 

  name_zone = '';
  if (id_zone==6) name_zone = '3-х ночь';
  if (id_zone==7) name_zone = '3-х полупик';
  if (id_zone==8) name_zone = '3-х пик';
  
  if (id_zone==9) name_zone = '2-х ночь';
  if (id_zone==10) name_zone = '2-х день';

  if (isIndicInfoCreated)
  {
      $("#indic_info_table").jqGrid('setGridParam',{'postData':{'p_id': id_paccnt, 'p_id_zone': id_zone}}).trigger('reloadGrid');
      $("#indic_info_table").jqGrid('setCaption', 'Історія споживання '+book+'/'+code+' '+name_zone);
      return;
  }    
  isIndicInfoCreated =true;
  
  jQuery('#indic_info_table').jqGrid({ 
    url:'ind_abon_info_data.php',
    editurl: '',
    datatype: 'json',
    mtype: 'POST',
    height:250,
    width:250,
    colNames:[],
    colModel :[ 
      {label:'Період',name:'mmgg', index:'mmgg', width:80, editable: true, align:'left',edittype:'text', hidden:false,formatter:'date'},
      {label:'Спожив.',name:'demand', index:'demand', width:60, editable: true, align:'right',hidden:false,
                            edittype:'text',formatter:'integer'},
      {label:'Походження',name:'source', index:'source', width:80, editable: true, align:'left',edittype:'text'}
    ],
    pager: '#indic_info_tablePager',
    rowNum:50,
    pgbuttons: false,
    pgtext: null, 
    sortname: 'mmgg',
    sortorder: 'desc',
    viewrecords: false,
    gridview: true,
    caption: 'Історія споживання '+book+'/'+code+' '+name_zone ,
    //hiddengrid: false,
    hidegrid: false,
    toolbar: [true,'top'],
    postData:{'p_id': id_paccnt, 'p_id_zone': id_zone},
    jsonReader : {repeatitems: false},
    
   onSelectRow: function(rowid) { 
        //lastSelFuse = rowid; 
    },
    

  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#indic_info_tablePager',
        {edit:false,add:false,del:false},
        {}, 
        {}, 
        {}, 
        {} 
        ); 

$("#t_indic_info_table").append("<button class ='btnClose' id='bt_indicinfoclose0' style='height:20px;font-size:-3;float:right;' > Закр. </button> ");
    
jQuery(".btnClose").button({ icons: {primary:'ui-icon-close'} });

jQuery('#bt_indicinfoclose0').click( function() { jQuery('#grid_abonindinfo').toggle( ); }); 

jQuery('#grid_abonindinfo').draggable({ handle: ".ui-jqgrid-titlebar" });

}; 

