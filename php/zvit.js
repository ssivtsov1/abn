/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function show_rep_detals(  row,  column )
{
  //alert (row+' '+column);
  
   $('#freps_params').find("#fgrid_params").attr('value',row ); 
   $('#freps_params').find("#fgrid_params2").attr('value',column ); 
   $('#freps_params').find("#foper").attr('value','zvit_detal' ); 
   $('#freps_params').find("#ftemplate_name").attr('value','zvit_detal' ); 
   
   $('#freps_params').attr('target','_blank' );  
   
   document.forms["freps_params"].submit();

};

function show_gek_detals( id )
{
  //alert (row+' '+column);
  
   $('#freps_params').find("#fgrid_params").attr('value',id ); 
   $('#freps_params').find("#foper").attr('value','gek_detail' ); 
   $('#freps_params').find("#ftemplate_name").attr('value','gek_detail' ); 
   
   $('#freps_params').attr('target','_blank' );  
   
   document.forms["freps_params"].submit();

};

function show_multif_detals( id )
{
  //alert (row+' '+column);
  
   $('#freps_params').find("#fgrid_params").attr('value',id ); 
   $('#freps_params').find("#foper").attr('value','multif_detail' ); 
   $('#freps_params').find("#ftemplate_name").attr('value','multif_detail' ); 
   
   $('#freps_params').attr('target','_blank' );  
   
   document.forms["freps_params"].submit();

};
function show_deb_cnt_3month_detals( id )
{
   $('#freps_params').find("#fgrid_params").attr('value',id ); 
   $('#freps_params').find("#foper").attr('value','debetor_cnt_3month_detail' ); 
   $('#freps_params').find("#ftemplate_name").attr('value','debetor_cnt_3month_detail' ); 
   
   $('#freps_params').attr('target','_blank' );  
   
   document.forms["freps_params"].submit();

};