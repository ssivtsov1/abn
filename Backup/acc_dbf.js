jQuery(function(){ 
  jQuery('#acc_dbf_table').jqGrid({
    url:'acc_dbf_data.php',
    editurl:'acc_dbf_edit.php',
    datatype: 'json',
    mtype: 'POST',
    height:500,
    width:700,
    colNames:['id','Файл','Персона','Время'],
    colModel :[
      {name:'id', index:'id', width:10, editable: false, key:true, hidden:true },     
      {name:'filename', index:'filename', width:300, editable: false, align:'left'},
      {name:'id_person', index:'id_person', width:100, editable: false, align:'left'},
      {name:'dt', index:'dt', width:100, editable: false, align:'left', formatter:'date',
            formatoptions:{srcformat:'Y-m-d H:i:s', newformat:'d.m.Y H:i'}}
    ],
    pager: '#acc_dbf_tablePager',
    rowNum:50,
    rowList:[20,50,100,300,500],
    sortname: 'id',
    sortorder: 'asc',
    viewrecords: true,
    gridview: true,
    caption: 'Список завантажених файлів:',
    hiddengrid: false,
    
  loadError : function(xhr,st,err) {$('#message_zone').html('Type: '+st+'; Response: '+ xhr.status + ' '+xhr.responseText); $('#message_zone').dialog('open');}

  }).navGrid('#acc_dbf_tablePager',
        {edit:false,add:false,del:true},
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
