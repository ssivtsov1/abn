jQuery(function(){ 
  jQuery('#acc_oplata_table').jqGrid({
    url:'acc_oplata_data.php',
    editurl:'acc_oplata_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:500,
    width:700,
    colNames:['id','Файл','Персона','Время','номер','дата рег.','МФО'],
    colModel :[
      {name:'id_head', index:'id', width:10, editable: false, key:true, hidden:true },     
      {name:'name_file', index:'name_file', width:100, editable: false, align:'left'},
      {name:'id_person', index:'id_person', width:50, editable: false, align:'left'},
      {name:'dt', index:'dt', width:100, editable: false, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}},
      {name:'reg_num', index:'reg_num', width:100, editable: false, align:'left'},
      {name:'reg_date', index:'reg_date', width:100, editable: false, align:'left'},
      {name:'mfo', index:'mfo', width:100, editable: false, align:'left'}
    ],
    pager: '#acc_oplata_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'id_head',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Список завантажених файлів:',
    hiddengrid: false,
    
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#acc_oplata_tablePager',
        {edit:false,add:false,del:false},
        {width:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {width:300,reloadAfterSubmit:false,closeAfterAdd:true,closeAfterEdit:true,
            afterSubmit:processAfterEdit}, 
        {reloadAfterSubmit:false,afterSubmit:processAfterEdit}, 
        {} 
        ); 


jQuery("#acc_dbf_table").jqGrid('filterToolbar','');

}); 
 

 function processAfterEdit(response, postdata) {
            //alert(response.responseText);
            if (response.responseText=='') { return [true,'']; }
            else
            {
             errorInfo = jQuery.parseJSON(response.responseText);
             
             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]}; 
             
             if (errorInfo.errcode==1) {
               
               return [true,errorInfo.errstr]};              
               
             if (errorInfo.errcode==2) {
               
               return [false,errorInfo.errstr]};              
            }
        }
