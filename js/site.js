/* Copyright 2004-2006
 * - Maxime Pettazoni
 * - Pierre Mauduit
 * - Laurent Colnat
 * - Julien Etelain < julien at pmad dot net >
 *
 * Ce fichier fait partie du site de l'Association des Ãtudiants de
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

/**
 * Sets/unsets the pointer and marker in browse mode
 *
 * @param   string    the class name
 * @param	integer	  the current number of row
 * @param   string    the action calling this script (over, out or click)
 *
 * @return  boolean  whether pointer is set or not
 */
function setPointer(theClass, currentNum, theAction, basename, the_form)
{
	var color;
	var obj = document.getElementById('ln['+currentNum+']');
	
	/* traitement out */
	if (theClass == 'ln0' && theAction == 'out')
		color = '#eff7ff';
	else if (theAction == 'out' && theClass == 'ln1')
		color = 'white';
	else if (theAction == 'click' && document.forms[the_form].elements[basename + currentNum + ']'].checked == true)
	{
		var do_check = document.forms[the_form].elements[basename + currentNum + ']'].checked;
		document.forms[the_form].elements[basename + currentNum + ']'].checked = !do_check;
		if (theClass == 'ln1')
			color = 'white';
		else if (theClass == 'ln0')
			color = '#eff7ff';
	}
	else if (theAction == 'click' && document.forms[the_form].elements[basename + currentNum + ']'].checked == false)
	{
		var do_check = document.forms[the_form].elements[basename + currentNum + ']'].checked;
		document.forms[the_form].elements[basename + currentNum + ']'].checked = !do_check;
		color = '#DFEAF2';
	}
	else if (theAction == 'over')
		color = '#ffcc2a';

    var currentColor = document.getElementById('ln['+currentNum+']').style.backgroundColor;

    if (currentColor.indexOf("rgb") >= 0)
    {
        var rgbStr = currentColor.slice(currentColor.indexOf('(') + 1,
                                     currentColor.indexOf(')'));
        var rgbValues = rgbStr.split(",");
        currentColor = "#";
        var hexChars = "0123456789ABCDEF";
        for (var i = 0; i < 3; i++)
        {
            var v = rgbValues[i].valueOf();
            currentColor += hexChars.charAt(v/16) + hexChars.charAt(v%16);
        }
    }

	if (currentColor != "#DFEAF2")
	{
		obj.style.background = color;
	}
	else if (currentColor == "#DFEAF2" && theAction == 'click')
	{
		obj.style.background = color;
	}

    return true;
} // end of the 'setPointer()' function


/**
 * Checks/unchecks all rows
 *
 * @param   string   the form name
 * @param   string   basename of the element
 * @param   integer  min element count
 * @param   integer  max element count
 *
 * @return  boolean  always true
 */
function setCheckboxesRange(the_form, basename, min, max)
{
	do_check = document.forms[the_form].elements[the_form + '_all'].checked;
    for (var i = min; i < max; i++) {
            document.forms[the_form].elements[basename + i + ']'].checked = do_check;
    }
	return true;

} // end of the 'setCheckboxesRange()' function


function fileListToggle (id)
{
  if (id.style.display == 'none')
	  id.style.display = 'block';
  else
	  id.style.display = 'none';
}

function tab(curr,dest)
{
    if ((curr.getAttribute) && (curr.value.length == curr.getAttribute("maxlength")))
	{
	    dest.focus();
	}
}

function on_off (id)
{
	var obj = document.getElementById(id);

	if ( obj.style.display == 'none' ) {
		obj.style.display = 'block';

	} else {
		obj.style.display = 'none';

	}
}

function on_off_icon (id,topdir)
{
	var obj = document.getElementById(id + '_contents');

	var img = document.getElementById(id + '_icon');

	if ( obj.style.display == 'none' ) {
		obj.style.display = 'block';
		img.src = topdir + 'images/fld.png';
	} else {
		obj.style.display = 'none';
		img.src = topdir + 'images/fll.png';
	}
}


function on_off_icon_store (id,topdir,key)
{
	var obj = document.getElementById(id + '_contents');
	var img = document.getElementById(id + '_icon');

	if ( obj.style.display == 'none' ) {
		obj.style.display = 'block';
		img.src = topdir + 'images/fld.png';
		usersession_set(topdir,key,1);
	} else {
		obj.style.display = 'none';
		img.src = topdir + 'images/fll.png';
		usersession_set(topdir,key,0);
	}
}

function on_off_options (name,val,oldval)
{
	var obj = document.getElementById(name + '_' + val + '_contents');
	obj.style.display = 'block';

	if ( oldval )
	{
		var oobj = document.getElementById(name + '_' + oldval + '_contents');
		oobj.style.display = 'none';
	}
}

var day;
var month;
var year;
var hour;
var minute;
var second;
var clock_set = 0;

/**
 * Opens calendar window.
 *
 * @param   string      calendar.php parameters
 * @param   string      form name
 * @param   string      field name
 */
function openCalendar(topdir, form, field, type) {
    window.open(topdir + "little_calendar.php?type=" + type, "calendar", "width=400,height=150,status=no");
		dateField = eval("document.getElementById('"+field+"')");

}

/**
 * Formats number to two digits.
 *
 * @param   int number to format.
 */
function formatNum2(i, valtype) {
    f = (i < 10 ? '0' : '') + i;
    if (valtype && valtype != '') {
        switch(valtype) {
            case 'month':
                f = (f > 12 ? 12 : f);
                break;

            case 'day':
                f = (f > 31 ? 31 : f);
                break;

            case 'hour':
                f = (f > 24 ? 24 : f);
                break;

            default:
            case 'second':
            case 'minute':
                f = (f > 59 ? 59 : f);
                break;
        }
    }

    return f;
}

/**
 * Formats number to four digits.
 *
 * @param   int number to format.
 */
function formatNum4(i) {
    return (i < 1000 ? i < 100 ? i < 10 ? '000' : '00' : '0' : '') + i;
}

/**
 * Initializes calendar window.
 */
function initCalendar() {
    if (!year && !month && !day) {
        /* Called for first time */
        if (window.opener.dateField.value) {
            value = window.opener.dateField.value;
                date        = value.split("/");
                day         = parseInt(date[0],10);
                month       = parseInt(date[1],10) - 1;
                year        = parseInt(date[2],10);
        }
        if (isNaN(year) || isNaN(month) || isNaN(day) || day == 0) {
            dt      = new Date();
            year    = dt.getFullYear();
            month   = dt.getMonth();
            day     = dt.getDate();
        }

    } else {
        /* Moving in calendar */
        if (month > 11) {
            month = 0;
            year++;
        }
        if (month < 0) {
            month = 11;
            year--;
        }
    }

    if (document.getElementById) {
        cnt = document.getElementById("calendar_data");
    } else if (document.all) {
        cnt = document.all["calendar_data"];
    }

    cnt.innerHTML = "";

    str = ""

    //heading table
    str += '<table class="calendar"><tr><th width="50%">';
    str += '<form method="NONE" onsubmit="return 0">';
    str += '<a href="javascript:month--; initCalendar();">&laquo;</a> ';
    str += '<select id="select_month" name="monthsel" onchange="month = parseInt(document.getElementById(\'select_month\').value); initCalendar();">';
    for (i =0; i < 12; i++) {
        if (i == month) selected = ' selected="selected"';
        else selected = '';
        str += '<option value="' + i + '" ' + selected + '>' + month_names[i] + '</option>';
    }
    str += '</select>';
    str += ' <a href="javascript:month++; initCalendar();">&raquo;</a>';
    str += '</form>';
    str += '</th><th width="50%">';
    str += '<form method="NONE" onsubmit="return 0">';
    str += '<a href="javascript:year--; initCalendar();">&laquo;</a> ';
    str += '<select id="select_year" name="yearsel" onchange="year = parseInt(document.getElementById(\'select_year\').value); initCalendar();">';
    for (i = year - 25; i < year + 25; i++) {
        if (i == year) selected = ' selected="selected"';
        else selected = '';
        str += '<option value="' + i + '" ' + selected + '>' + i + '</option>';
    }
    str += '</select>';
    str += ' <a href="javascript:year++; initCalendar();">&raquo;</a>';
    str += '</form>';
    str += '</th></tr></table>';

    str += '<table class="calendar"><tr>';
    for (i = 0; i < 7; i++) {
        str += "<th>" + day_names[i] + "</th>";
    }
    str += "</tr>";

    var firstDay = new Date(year, month, 1).getDay();
    var lastDay = new Date(year, month + 1, 0).getDate();

    str += "<tr>";

    dayInWeek = 0;
    for (i = 0; i < firstDay; i++) {
        str += "<td>&nbsp;</td>";
        dayInWeek++;
    }
    for (i = 1; i <= lastDay; i++) {
        if (dayInWeek == 7) {
            str += "</tr><tr>";
            dayInWeek = 0;
        }

        dispmonth = 1 + month;

        actVal = formatNum2(i, 'day') + "/" + formatNum2(dispmonth, 'month') + "/" + formatNum4(year);

        if (i == day) {
            style = ' class="selected"';
        } else {
            style = '';
        }
        str += "<td" + style + "><a href=\"javascript:returnDate('" + actVal + "');\">" + i + "</a></td>"
        dayInWeek++;
    }
    for (i = dayInWeek; i < 7; i++) {
        str += "<td>&nbsp;</td>";
    }

    str += "</tr></table>";

    cnt.innerHTML = str;

}

/**
 * Returns date from calendar.
 *
 * @param   string     date text
 */
function returnDate(d) {
    txt = d;
    if ( fieldType == 'datetime' )
    window.opener.dateField.value = txt + " 08:00";
    else
    window.opener.dateField.value = txt;
    window.close();
}

function openMatmatronch(topdir, id, width, height) {
    if (width == "")
	  width = 280;
    if (height == "")
	  height = 390;

    path = topdir + "matmatronch/show_img.php?id=" + id;
    

    window.open(path,
                "Photo Mat'Matronch",
                "width="+width+",height="+height);
}

function errorMsg()
{
alert("Netscape 6 or Mozilla is needed to install a sherlock plugin");
}

function addEngine(topdir,name,ext,cat,type)
{
if ((typeof window.sidebar == "object") && (typeof
window.sidebar.addSearchEngine == "function"))
{
window.sidebar.addSearchEngine(
"http://"+topdir+name+".src",
"http://"+topdir+name+"."+ext,
name,
cat );
}
else
{
errorMsg();
}
}


function show_obj_top(obj)
{
	
	var content = document.getElementById(obj);
	
	content.style.display = 'block';
	content.style.zIndex = 10000000;
	
	if ( document.all )	// replacons l'élément histoire d'éviter un bug d'IE6
	{
		var target = document.getElementById("left");
		var parent = content.parentNode;
		parent.removeChild(content);
		target.insertBefore(content, document.getElementById("sbox_calendrier").nextSibling);
	}	
}

function hide_obj(obj)
{
	var content = document.getElementById(obj);
	content.style.display = 'none';	
}

function switchphoto (dest,src)
{
	var img = document.getElementById(dest);

  if ( img )
    img.src = src;

}

function toggle(id_tglnum,id)
{

      var toHide = null;
      var imgToChange = null;

      toHide = document.getElementById("tgl"+id_tglnum+"_"+id);
      imgToChange = document.getElementById("tgl"+id_tglnum+"_img"+id);
 
      toHidezeClass = toHide.getAttribute('class');
      imgToChangezeClass = imgToChange.getAttribute('class');
     
      if (toHide == null)
      {
        alert("objects to hide not found !");
        return null;
      }
      if (imgToChange == null)
      {  
        alert("image to change not found !");
        return null;
      }

      if (!toHidezeClass)
      {
	toHide.setAttribute('class', 'tgloff');
      //  toHide.class = "tgloff";
      }


      toHidezeClass = toHide.getAttribute('class');
      imgToChangezeClass = imgToChange.getAttribute('class');


      if (toHidezeClass == "tglon")
      {
	toHide.setAttribute('class', 'tgloff');
         //toHide.class = "tgloff";
        toHide.style.display = "none";
	imgToChange.src = "/images/fll.png";
         
      }
      else
      {
	toHide.setAttribute('class', 'tglon');
	 //toHide.class = "tglon";       
        toHide.style.display = "inline";
        imgToChange.src = "/images/fld.png";
      }
}

function insert_tags(txtarea, lft, rgt, sample_text) 
{
  sample_text = typeof(sample_text) != 'undefined' ? sample_text : 'votre tAExte'; /* pas de passage d'arguments par dÃ©faut en JS alors on fait autrement */

  if (lft == '[[' && rgt == ']]') /* balises d'URL */
    {
      var _url = prompt("Entrez l'URL:","http://");

      if (_url != "" && _url != "http://") {
	lft="[[" + _url + "|";
	rgt="]]";
	insert_tags(txtarea, lft, rgt);
      }
      else
	insert_tags(txtarea, lft, " "+rgt); /* vieux truandage pour passer outre le test */

      return;
    }
  else if (document.all) /* IE */
    {
      _selection = document.selection.createRange().text;
      if (_selection == "")
        _selection = sample_text;

      document.selection.createRange().text = lft + _selection + rgt;
    }
  else if (document.getElementById) /* Firefox... */
    {		
      var _length = txtarea.textLength;
      var _start = txtarea.selectionStart;
      var _end = txtarea.selectionEnd;
      if (_end==1 || _end==2) 
	_end = _length;
      var s1 = (txtarea.value).substring(0,_start);
      var s2 = (txtarea.value).substring(_start, _end)
      var s3 = (txtarea.value).substring(_end, _length);
     
      if(s2 == "")
        s2 = sample_text;   

      txtarea.value = s1 + lft + s2 + rgt + s3;

    }
}

function setSelectionRange(input, selectionStart, selectionEnd)
{
  if (input.createTextRange)
  {
    var range = input.createTextRange();
    range.collapse(true);
    range.moveEnd('character', selectionEnd);
    range.moveStart('character', selectionStart);
    range.select();
  }
  else if (input.setSelectionRange)
  {
    input.focus();
    input.setSelectionRange(selectionStart, selectionEnd);
  } 
  else
  {
  	input.selectionStart = selectionStart;
  	input.selectionEnd = selectionEnd;
  }
}

function insert_tags2(objid, lft, rgt, deftext) 
{
  var obj = document.getElementById(objid);
  
  if ( !obj )
    return;
  
  if ( document.selection )
  {
    oldlen = obj.value.length;
    
    
    obj.focus();
    range = document.selection.createRange();
    if ( range.text == "")
    {
      len=deftext.length;
      range.text = lft + deftext + rgt;
    }
    else
    {
      len=range.text.length;
      range.text = lft + range.text + rgt;
    }
    range.select();
    
    range = document.selection.createRange();
    if ( window.opera && rgt.substring(rgt.length-1) == "\n" )
    {
      range.moveStart('character', -rgt.length-len+1);
      range.moveEnd('character', -rgt.length+1);      
    }
    else
    {
      range.moveStart('character', -rgt.length-len);
      range.moveEnd('character', -rgt.length);      
    }
    range.select();
  }
  else if ( obj.selectionStart != null )
  {		
    obj.focus();
    var start = obj.selectionStart;
    var end = obj.selectionEnd;
    
    var s1 = obj.value.substring(0,start);
    var s2 = obj.value.substring(start, end)
    var s3 = obj.value.substring(end);
     
    if(s2 == "")
      s2 = deftext;   

    obj.value = s1 + lft + s2 + rgt + s3;

    setSelectionRange(obj,start+lft.length,start+lft.length+s2.length);
  }
}


function popUpStream(topdir)
{
  window.open(topdir+"stream.php?get=popup", "stream", "width=300,height=350,status=no,scrollbars=yes,resizable=yes");
  return false;  
}

var onSelectedFile;
var onSelectedFileFieldName;

function onSelectedWikiFile ( id, titre  )
{
  insert_tags2(onSelectedFileFieldName, "[[", "]]", "dfile://"+id);
}

function onSelectedWikiImage ( id, titre  )
{
  insert_tags2(onSelectedFileFieldName, "{{", "}}", "dfile://"+id);
}

function _selectFile ( topdir,context="" )
{
  window.open(topdir+"explorer.php?"+context, "fileselector", "width=750,height=500,status=no,scrollbars=yes,resizable=yes");
}

function selectWikiImage(topdir,field,context="")
{
  onSelectedFileFieldName = field;
  onSelectedFile = onSelectedWikiImage;
  _selectFile(topdir,context);
}

function selectWikiFile(topdir,field,context="")
{
  onSelectedFileFieldName = field;
  onSelectedFile = onSelectedWikiFile;
  _selectFile(topdir,context);
}

var listFileTopDir;
var listFileField;

function onSelectedListFile ( id, titre )
{
  var contener = document.getElementById("_files_"+listFileField+"_items");
  var values = document.getElementById("_files_"+listFileField+"_ids");
  
  //Visuel
  
  var elem = document.createElement("div");
  var buffer = "";
  
  elem.setAttribute("id","_files_"+listFileField+"_"+id);
  elem.setAttribute("class","slsitem");
  
  elem.innerHTML= "<a href=\""+listFileTopDir+"/dfile.php?id_file="+id+"\"><img src=\""+listFileTopDir+"images/icons/16/file.png\" /> "+titre+"</a> <a onclick=\"removeListFile('"+listFileTopDir+"','"+listFileField+"',"+id+"); return false;\"><img src=\""+listFileTopDir+"images/actions/delete.png\" /></a>";
  
  contener.insert(elem);
  
  // Données
  if ( values.value == "" )
    values.value = id;
  else
    values.value = values.value + "," + id;
}


function removeListFile(topdir,field,id)
{
  var element = document.getElementById("_files_"+field+"_"+id);
  var values = document.getElementById("_files_"+field+"_ids");
  
  // Visuel
	var contener = element.parentNode;
	parent.removeChild(element);
  
  // Données
  var ids = values.value.split(",");
}

function selectListFile(topdir,field,context)
{
  listFileTopDir=topdir;
  listFileField=field;
  onSelectedFile = onSelectedListFile;
  _selectFile(topdir,context);
}
