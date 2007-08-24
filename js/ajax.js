/* Copyright 2006
 * - Julien Etelain < julien at pmad dot net >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

function openInContents( name, page, data)
{
  if (window.ActiveXObject)
    var XhrObj = new ActiveXObject("Microsoft.XMLHTTP") ;
  else
    var XhrObj = new XMLHttpRequest();
  
  if ( !XhrObj ) return false;

  var content = document.getElementById(name);
  
  XhrObj.open("GET", page+"?"+data);

  XhrObj.onreadystatechange = function()
  {
    if (XhrObj.readyState == 4 && XhrObj.status == 200)
      content.innerHTML = XhrObj.responseText ;
  }    

	XhrObj.send(null);
	
	return true;
}

function evalCommand( page, data )
{

  if (window.ActiveXObject)
    var XhrObj = new ActiveXObject("Microsoft.XMLHTTP") ;
  else
    var XhrObj = new XMLHttpRequest();
 
  if ( !XhrObj ) return false;
    	
	XhrObj.open("GET", page+"?"+data);

  XhrObj.onreadystatechange = function()
  {
    if (XhrObj.readyState == 4 && XhrObj.status == 200)
    {
      eval(XhrObj.responseText);
    }
  }    

	XhrObj.send(null);
	
	return true;
}

function usersession_set ( topdir, key, value )
{
	evalCommand ( topdir + "gateway.php", "module=usersession&set=" + escape(key) + "&value=" + escape(value) );
}

var quering=0;
var call=0;
function autocomplete(event,topdir, field,kind)
{
	if ( event != null )
	{
		if ( event.ctrlKey || event.keyCode == 8 || event.keyCode == 46 || event.keyCode == 13  ) return false; /* DEL SUPR ENTER*/
	
		if ( event.keyCode == 27 ) // ESC
		{
			autocomplete_stop(field);
			return false;
		}
	}
	
  var obj = document.getElementById(field);
    
  if ( !obj ) return false;
	
	if ( quering == 1 )
	{
		call=1;
		return;
	}
	
  if (window.ActiveXObject)
      var XhrObj = new ActiveXObject("Microsoft.XMLHTTP") ;
  else
      var XhrObj = new XMLHttpRequest();
   
  if ( !XhrObj )
    return false;

	XhrObj.open("GET", topdir + "gateway.php?module=complete&kind="+kind+"&pattern="+obj.value);

  XhrObj.onreadystatechange = function()
  {
    if ( XhrObj.readyState == 4 )
    {
      quering=0;
    if ( call == 1)
    {
      autocomplete(null,topdir,field,kind);
      call=0;	
    }
    else if ( XhrObj.status == 200)
        if ( pattern == obj.value )
        autocomplete_callback(topdir,XhrObj.responseXML,field,pattern,kind)       
    }
  }  
	quering = 1;
	XhrObj.send(null);
	return true;
}

function autocomplete_callback(topdir,res,field,pattern,kind)
{
	var obj = document.getElementById(field);
	var objlst = document.getElementById(field + "_area");
	var items = res.documentElement.getElementsByTagName('item'); 
	if ( items.length == 0 )
	{
		objlst.innerHTML = "(Aucun élément) " + pattern;
	}
	else
	{
		var suggest=items.item(0).firstChild.nodeValue;
		buf = "<ul class=\"smartlist_"+kind+"\">";
		var value = "";
		var id=0;
		var cnt=0;
		for (var i = 0; i < items.length; i++)
		{
			value=items.item(i).firstChild.nodeValue;
			if ( kind == 'pg' )
			{
				cnt=items.item(i).getAttribute("count");
				buf = buf + "<li><a href=\"#\" onclick=\"autocomplete_set('"+field+"','"+value+"'); return false;\">" + value + "</a>";
				if ( cnt == 1 )
					buf = buf + " ( une réponse )";
				else
					buf = buf + " ( " + cnt + " réponses )";
				buf = buf + "</li>";
			}
			else if ( kind == 'user' )
			{
				id=items.item(i).getAttribute("id");
				buf = buf + "<li><a href=\"#\" onclick=\"autocomplete_set('"+field+"','"+value+"'); return false;\"><img src=\"/images/matmatronch/"+id+".identity.jpg\" border=\"0\" alt=\"\" width=\"32\" /> " + value + "</a></li>";
			}
			else
				buf = buf + "<li><a href=\"#\" onclick=\"autocomplete_set('"+field+"','"+value+"'); return false;\">" + value + "</a></li>";
		}
		buf = buf + "</ul>";
		objlst.innerHTML = buf;
		if ( pattern.length <= suggest.length )
		{
			obj.value=suggest;
			setSelectionRange(obj,pattern.length,suggest.length);
		}
	}
	objlst.style.display = 'block';
}

function autocomplete_set ( field, value )
{
	var obj = document.getElementById(field);
	obj.value=value;
	autocomplete_stop(field);
}

function autocomplete_stop ( field )
{
	var objlst = document.getElementById(field + "_area");
	objlst.style.display = 'none';
}

function autocomplete_stop_delayed( field ) {
    setTimeout("autocomplete_stop('" + field + "')", 500);
}

var fsearch_display_query='';
var fsearch_sequence=0;
var fsearch_actual_sequence=0;

function fsearch_keyup(event,topdir)
{
	if ( event != null )
	{
		if ( event.ctrlKey || event.keyCode == 13 ) return false;
		if ( event.keyCode == 27 ) // ESC
		{
			fsearch_stop();
			return false;
		}
	}
	
  var obj = document.getElementById('fsearchpattern');
    
  if ( !obj ) return false;
    
  fsearch_sequence=fsearch_sequence+1;
    
	evalCommand( topdir + "gateway.php", "module=fsearch&fsearch_sequence="+fsearch_sequence+"&topdir="+topdir+"&pattern="+obj.value );
	
	return true;
}

function fsearch_stop ( )
{
	var obj = document.getElementById('fsearchres');
	obj.style.display = 'none';
	fsearch_display_query='';
}

function fsearch_stop_delayed( field ) {
    setTimeout("fsearch_stop()", 500);
}

function userselect_toggle(ref)
{
	var obj1 = document.getElementById(ref+"_fieldbox");
	var obj2 = document.getElementById(ref+"_static");
	var obj3 = document.getElementById(ref+"_currentuser");
	var obj4 = document.getElementById(ref+"_result");
	var obj5 = document.getElementById(ref+"_button");
	var obj6 = document.getElementById(ref+"_field");

	if ( obj1.style.display == 'none' ) {
		obj1.style.display = 'block';
		obj2.style.display = 'none';
		obj3.style.display = 'block';
		obj4.style.display = 'block';
		obj5.innerHTML="fermer";
		obj6.value="";
		obj6.focus();
	} else {
		obj1.style.display = 'none';
		obj2.style.display = 'block';
		obj3.style.display = 'none';
		obj4.style.display = 'none';
		obj5.innerHTML="changer";
	}
}

function userselect_set_user(topdir, ref,id,nom)
{
	var obj1 = document.getElementById(ref+"_fieldbox");
	var obj2 = document.getElementById(ref+"_static");
	var obj3 = document.getElementById(ref+"_currentuser");
	var obj4 = document.getElementById(ref+"_result");
	var obj5 = document.getElementById(ref+"_id");
	var obj6 = document.getElementById(ref+"_button");

	obj1.style.display = 'none';
	obj2.style.display = 'block';
	obj3.style.display = 'none';
	obj4.style.display = 'none';
	
	obj6.innerHTML="changer";

	obj5.value=id;
	obj2.innerHTML="<img src=\""+topdir+"images/icons/16/user.png\" class=\"icon\" alt=\"\" /> "+nom;
	obj4.innerHTML="";
	openInContents( ref + "_currentuser", topdir + "gateway.php", "module=userinfo&targettopdir=" + topdir + "&id_utilisateur=" + id );
	
}

function userselect_keyup(event,ref,topdir)
{
	if ( event != null )
		if ( event.ctrlKey || event.keyCode == 27 || event.keyCode == 13  )
			return false;
	
  var obj = document.getElementById(ref+'_field');
    	
  if ( !obj ) return false;
    
	openInContents( ref + "_result", topdir + "gateway.php", "module=userfield&topdir="+topdir+"&pattern="+obj.value+"&ref="+ref );
	
	return true;
}

/*
 * Entities : Fast Search Field (fsfield)
 * @see gateway.php
 */
var fsfield_current_sequence = new Array();
var fsfield_sequence = new Array();

function fsfield_init ( topdir, field )
{
  fsfield_current_sequence[field]=0;
  fsfield_sequence[field]=0;
}

function fsfield_toggle ( topdir, field )
{
	var obj1 = document.getElementById(field+"_fieldbox");
	var obj2 = document.getElementById(field+"_static");
	var obj4 = document.getElementById(field+"_result");
	var obj5 = document.getElementById(field+"_button");
	var obj6 = document.getElementById(field+"_field");

	if ( obj1.style.display == 'none' )
	{
		obj1.style.display = 'block';
		obj2.style.display = 'none';
		obj4.style.display = 'block';
		obj5.innerHTML="annuler";
		obj6.value="";
		obj6.focus();
	}
	else
	{
		obj1.style.display = 'none';
		obj2.style.display = 'block';
		obj4.style.display = 'none';
		obj5.innerHTML="changer";
	}
}

function fsfield_sel ( topdir, field, id, title, iconfile )
{
  // Bloque les reponses pas encore données par le serveur (évite que la boite re-aparaisse après avoir choisi l'élément)
	fsfield_current_sequence[field] = fsfield_sequence[field];
  
	var obj1 = document.getElementById(field+"_fieldbox");
	var obj2 = document.getElementById(field+"_static");
	var obj4 = document.getElementById(field+"_result");
	var obj5 = document.getElementById(field+"_id");
	var obj6 = document.getElementById(field+"_button");

	obj1.style.display = 'none';
	obj2.style.display = 'block';
	obj4.style.display = 'none';
	obj6.innerHTML="changer";

	obj5.value=id;
	obj2.innerHTML="<img src=\""+topdir+"images/icons/16/"+iconfile+"\" class=\"icon\" alt=\"\" /> "+title;
	obj4.innerHTML="";
}

function fsfield_keyup ( event, topdir, field, myclass )
{
	if ( event != null )
		if ( event.ctrlKey || event.keyCode == 27 || event.keyCode == 13  )
			return false;
			
  var obj = document.getElementById(field + '_field');

  if ( !obj ) return false;
    
  fsfield_sequence[field] = fsfield_sequence[field]+1;
    
	evalCommand( topdir + "gateway.php", 
  	"module=fsfield"+
  	"&topdir="+topdir+
  	"&pattern="+obj.value+
  	"&field="+field+
  	"&class="+myclass+
  	"&sequence="+fsfield_sequence[field] );
	
	return true;
}

/*
 * Entities : Tooltip 
 * @see gateway.php
 */
 
var tooltip_active='';
var tooltip_element = document.createElement("div");

tooltip_element.setAttribute('id','systooltip');
tooltip_element.style.position="absolute";

function findPos(obj)
{	var curleft = curtop = 0;	if (obj.offsetParent)
	{		curleft = obj.offsetLeft		curtop = obj.offsetTop		while (obj = obj.offsetParent)
		{			curleft += obj.offsetLeft			curtop += obj.offsetTop		}	}	return [curleft,curtop];}

function show_tooltip ( ref, topdir, myclass, id )
{
  document.body.appendChild(tooltip_element);

  
  tooltip_active = ref;
  setTimeout("go_tooltip('" + ref + "','" + topdir + "','" + myclass + "','" + id + "')", 1000);
}

function go_tooltip ( ref, topdir, myclass, id )
{
  if ( tooltip_active != ref )
    return;
    
  if (window.ActiveXObject)
    var XhrObj = new ActiveXObject("Microsoft.XMLHTTP") ;
  else
    var XhrObj = new XMLHttpRequest();
  
  if ( !XhrObj ) return false;

  XhrObj.open("GET", topdir+"gateway.php?module=entinfo&topdir="+topdir+"&class="+myclass+"&id="+id);

  XhrObj.onreadystatechange = function()
  {
    if (XhrObj.readyState == 4 && XhrObj.status == 200)
    {
      if ( tooltip_active == ref )
      {
        var elem = document.getElementById(ref);
        var pos = findPos(elem);
        tooltip_element.innerHTML = XhrObj.responseText ;
        tooltip_element.style.left=pos[0];
        tooltip_element.style.top=pos[1]+elem.offsetHeight+1;
        tooltip_element.style.display = 'block';
      }
    }
  }    

	XhrObj.send(null);
	return true;
}

function hide_tooltip ( ref )
{
  tooltip_active='';
  tooltip_element.style.display = 'none';
}

/*
 * Entities : Explorer Field
 *
 */

function exfield_toggle ( topdir, field, myclass )
{
	var obj2 = document.getElementById(field+"_static");
	var obj4 = document.getElementById(field+"_result");
	var obj5 = document.getElementById(field+"_button");
  var obj7 = document.getElementById(field+"_"+myclass+"_root");

	if ( obj4.style.display == 'none' )
	{
		obj2.style.display = 'none';
		obj4.style.display = 'block';
		obj5.innerHTML="annuler";
		obj7.innerHTML="";

    exfield_explore ( topdir, field, myclass, myclass, 'root' );
	}
	else
	{
		obj2.style.display = 'block';
		obj4.style.display = 'none';
		obj5.innerHTML="changer";
		obj7.innerHTML="";
	}
}

function exfield_explore ( topdir, field, myclass, eclass, eid )
{
  var obj = document.getElementById(field+"_"+eclass+"_"+eid);
  
  if ( obj.innerHTML != "" )
    obj.innerHTML = "";
  else
    openInContents( field+"_"+eclass+"_"+eid, topdir+"gateway.php", "module=exfield&topdir="+topdir+"&field="+field+"&class="+myclass+"&eclass="+eclass+"&eid="+eid);
}

function exfield_select ( topdir, field, myclass, id, title, iconfile )
{
  
	var obj2 = document.getElementById(field+"_static");
	var obj4 = document.getElementById(field+"_result");
	var obj5 = document.getElementById(field+"_id");
	var obj6 = document.getElementById(field+"_button");
  var obj7 = document.getElementById(field+"_"+myclass+"_root");
  
	obj2.style.display = 'block';
	obj4.style.display = 'none';
	obj6.innerHTML="changer";

	obj5.value=id;
	obj2.innerHTML="<img src=\""+topdir+"images/icons/16/"+iconfile+"\" class=\"icon\" alt=\"\" /> "+title;
	obj7.innerHTML="";
}

