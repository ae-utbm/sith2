

var wiki_edit_locktime;
var wiki_edit_pagename;

var wiki_edit_modified_since_renew;

var wiki_edit_going_to_expire;

function wiki_modified ()
{
  wiki_edit_modified_since_renew=true;
  wiki_edit_modified=true;
  
  if ( wiki_edit_going_to_expire )
    wiki_timer();  
}

function wiki_timer()
{
  if ( wiki_edit_modified_since_renew == true )
  {
    wiki_edit_modified_since_renew=false;
    wiki_edit_going_to_expire=false;
    evalCommand("index.php","action=renew&name="+wiki_edit_pagename);
    setTimeout("wiki_timer()", wiki_edit_locktime*1000/2);
    return;
  }
  
  alert("Le verrouillage du document va expirer dans "+(wiki_edit_locktime/120)+" minutes, veuillez enregistrez vos modifications.");
  wiki_edit_going_to_expire=true;
  setTimeout("wiki_expired()", (wiki_edit_locktime-5)*1000/2);
}

function wiki_renewed()
{
  
}

function wiki_expired()
{
  if ( wiki_edit_going_to_expire == false ) 
    return;
  
  wiki_edit_going_to_expire=false;
  alert("Le verouillage du document a expiré, enregistrez dès que possible vos modifications, il est possible qu'une autre personne ai modifié le document.");
}

function wiki_lock_maintain ( topdir, locktime, pagename )
{
  wiki_edit_locktime = locktime;
  wiki_edit_pagename = pagename;
  wiki_edit_modified_since_renew=false;
  wiki_edit_going_to_expire=false;
  setTimeout("wiki_timer()", wiki_edit_locktime*1000/2);
  
  var obj = document.getElementById("textarea_revisewiki_contents");
  obj.onchange=wiki_modified;
  obj.onkeyup=wiki_modified;
  obj.onmouseup=wiki_modified;

}

function historyRadios(parent)
{
  var inputs = parent.getElementsByTagName('input');
  var radios = [];
  for (var i = 0; i < inputs.length; i++)
  {
    if (inputs[i].name == "diff" || inputs[i].name == "oldid")
    {
      radios[radios.length] = inputs[i];
    }
  }
  return radios;
}

function diffcheck()
{
  var dli = false; // li où le bouton rev_orig radio est coché
  var var oli = false; // li où le bouton radio rev_comp est coché
  var hf = document.getElementById('diff');
  if (!hf)
  {
    return true;
  }
  var lis = hf.getElementsByTagName('li');
  for (var i=0;i<lis.length;i++)
  {
    var inputs = historyRadios(lis[i]);
    if (inputs[1] && inputs[0])
    {
      if (inputs[1].checked || inputs[0].checked) // this row has a checked radio button
      {
        if (inputs[1].checked && inputs[0].checked && inputs[0].value == inputs[1].value)
	  return false;
	if (oli)
	{
	  if (inputs[1].checked)
	  {
	    oli.className = "selected";
	    return false;
	  }
	}
	else if (inputs[0].checked)
	  return false;
	if (inputs[0].checked)
	  dli = lis[i];
	if (!oli)
	  inputs[0].style.visibility = 'hidden';
	if (dli)
	  inputs[1].style.visibility = 'hidden';
	lis[i].className = "selected";
	oli = lis[i];
      }
      else // no radio is checked in this row
      {
        if (!oli)
	  inputs[0].style.visibility = 'hidden';
	else
	  inputs[0].style.visibility = 'visible';
	if (dli)
	  inputs[1].style.visibility = 'hidden';
	else
	  inputs[1].style.visibility = 'visible';
	lis[i].className = "";
      }
    }
  }
  return true;
}

function histrowinit()
{
  alert("je suis la");
  var hf = document.getElementById('diff');
  if (!hf)
    return;
  var lis = hf.getElementsByTagName('li');
  for (var i = 0; i < lis.length; i++)
  {
    var inputs = historyRadios(lis[i]);
    if (inputs[0] && inputs[1])
    {
      inputs[0].onclick = diffcheck;
      inputs[1].onclick = diffcheck;
    }
  }
  diffcheck();
}

function hookEvent(hookName, hookFunct)
{
  alert("hereuh");
  if (window.addEventListener)
    window.addEventListener(hookName, hookFunct, false);
  else if (window.attachEvent)
    window.attachEvent("on" + hookName, hookFunct);
}


hookEvent("load", histrowinit);
