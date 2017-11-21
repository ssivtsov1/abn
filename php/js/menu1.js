
var timeout    = 500;
var closetimer = 0;
var ddmenuitem = 0;

function jsddm1_open()
{  jsddm1_canceltimer();
   jsddm1_close();
   ddmenuitem = $(this).find('ul').css('visibility', 'visible');}

function jsddm1_close()
{  if(ddmenuitem) ddmenuitem.css('visibility', 'hidden');}

function jsddm1_timer()
{  closetimer = window.setTimeout(jsddm1_close, timeout);}

function jsddm1_canceltimer()
{  if(closetimer)
   {  window.clearTimeout(closetimer);
      closetimer = null;}}

$(document).ready(function()
{  $('#jsddm1 > li').bind('mouseover', jsddm1_open)
   $('#jsddm1 > li').bind('mouseout',  jsddm1_timer)});

document.onclick = jsddm1_close;
