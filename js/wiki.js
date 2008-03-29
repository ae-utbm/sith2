

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