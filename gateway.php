<?php
/* Copyright 2006-2007
 * - Julien Etelain < julien at pmad dot net >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License a
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

$topdir = "./";
require_once($topdir. "include/site.inc.php");

$site = new site();

if ( isset($_REQUEST['topdir']) && ($_REQUEST['topdir']=="./" || $_REQUEST['topdir'] =="../" || $_REQUEST['topdir'] =="./../") )
  $wwwtopdir = $_REQUEST['topdir'];

if ( $_REQUEST['module']=="fsearch" )
{
  header("Content-Type: text/javascript; charset=UTF-8");

  echo "if ( ".$_REQUEST['fsearch_sequence']." > fsearch_actual_sequence ) {\n";

  echo "fsearch_actual_sequence=".$_REQUEST['fsearch_sequence'].";\n";

  if ( $_REQUEST["pattern"] == "" )
  {
    echo "var content = document.getElementById('fsearchres');\n";
    echo "content.style.display = 'none';\n";
    exit();
  }

  $results="";
  require_once($topdir. "include/cts/fsearch.inc.php");
  $fsearch = new fsearch ( $site );
  // si la requete a été trop longue on ne l'affiche pas !
  echo "  if ( ".$_REQUEST['fsearch_sequence']." > fsearch_actual_sequence ) {\n";
  echo "    var content = document.getElementById('fsearchres');\n";
  echo "    content.style.zIndex = 100000;\n";
  echo "    content.style.display = 'block';\n";
  echo "    content.innerHTML ='".addslashes($fsearch->buffer)."';\n";
  echo "    fsearch_display_query='".addslashes($_REQUEST["pattern"])."';\n";
  echo "  }\n";
  echo "}\n";

}
elseif ( $_REQUEST['module']=="explorer" )
{
  header("Content-Type: text/html; charset=utf-8");

  require_once($topdir."include/entities/files.inc.php");
  require_once($topdir."include/entities/folder.inc.php");

  $folder = new dfolder($site->db);

  if ( !isset($_REQUEST["id_folder"]) || !$_REQUEST["id_folder"] )
    $folder->id = null;
  else
    $folder->load_by_id($_REQUEST["id_folder"]);

  $field = $_REQUEST["field"];

  if ( is_null($folder->id) )
    $sub1 = new requete($this->db,"SELECT `d_folder`.`id_folder`, ".
    "IF(`asso`.`id_asso` IS NULL,`d_folder`.`titre_folder`, `asso`.`nom_asso`) AS `titre_folder` ".
    "FROM `d_folder` ".
    "LEFT JOIN `asso` ON `asso`.`id_asso` = `d_folder`.`id_asso` ".
    "WHERE `d_folder`.`id_folder_parent` IS NULL ".
    "ORDER BY `asso`.`nom_asso`");
  else
    $sub1 = $folder->get_folders ( $site->user );

  $fd = new dfolder(null);
  while ( $row = $sub1->get_row() )
  {
    $fd->_load($row);
    echo "<li><a href=\"#\" onclick=\"zd_seldir('$field','".$fd->id."','$wwwtopdir'); return false;\"><img src=\"".$wwwtopdir."images/icons/16/folder.png\" alt=\"dossier\" /> ".htmlentities($fd->titre,ENT_COMPAT,"UTF-8")."</a><ul id=\"".$field."_".$fd->id."_cts\" style=\"display:none;\"></ul></li>";
  }

  if ( !is_null($folder->id) )
  {
    $sub2 = $folder->get_files ( $site->user);
    $fd = new dfile(null);
    while ( $row = $sub2->get_row() )
    {
      $fd->_load($row);
      $img = $wwwtopdir."images/icons/16/".$fd->get_icon_name();
      echo "<li><a href=\"#\" onclick=\"zd_selfile('$field','".$fd->id."','$wwwtopdir'); return false;\"><img src=\"$img\" alt=\"fichier\" /> ".htmlentities($fd->titre,ENT_COMPAT,"UTF-8")."</a></li>";
    }
  }
}
elseif ( $_REQUEST['module']=="usersession" )
{
  /**** NOTE IMPORTANTE ****
   * En raison de ce module, les valeurs de $_SESSION["usersession"] ne peuvent être
   * considéré comme "sûres"
   */

  if ( isset($_REQUEST["set"]) )
  {
    $_SESSION["usersession"][$_REQUEST["set"]]   = $_REQUEST["value"];


    if ( $site->user->is_valid() ) // mémorise le usersession
      $site->user->set_param("usersession",$_SESSION["usersession"]);


    //echo "alert('".$_REQUEST["set"]."=".$_REQUEST["set"]."');";
  }

  exit();
}
elseif ( $_REQUEST['module']=="userfield" )
{
  if ( !$site->user->is_valid() && !count($_SESSION["Comptoirs"])) exit();

  $pattern = mysql_real_escape_string($_REQUEST["pattern"]);

  $pattern = ereg_replace("(e|é|è|ê|ë|É|È|Ê|Ë)","(e|é|è|ê|ë|É|È|Ê|Ë)",$pattern);
  $pattern = ereg_replace("(a|à|â|ä|À|Â|Ä)","(a|à|â|ä|À|Â|Ä)",$pattern);
  $pattern = ereg_replace("(i|ï|î|Ï|Î)","(i|ï|î|Ï|Î)",$pattern);
  $pattern = ereg_replace("(c|ç|Ç)","(c|ç|Ç)",$pattern);
  $pattern = ereg_replace("(o|O|ò|Ò|ô|Ô)","(o|O|ò|Ò|ô|Ô)",$pattern);
  $pattern = ereg_replace("(u|ù|ü|û|Ü|Û|Ù)","(u|ù|ü|û|Ü|Û|Ù)",$pattern);
  $pattern = ereg_replace("(n|ñ|Ñ)","(n|ñ|Ñ)",$pattern);

  $req = new requete($site->db,
    "SELECT `id_utilisateur`,CONCAT(`prenom_utl`,' ',`nom_utl`) " .
    "FROM `utilisateurs` " .
    "WHERE CONCAT(`prenom_utl`,' ',`nom_utl`) REGEXP '^".$pattern."' " .
    "UNION SELECT `id_utilisateur`,CONCAT(`nom_utl`,' ',`prenom_utl`) " .
    "FROM `utilisateurs` " .
    "WHERE CONCAT(`nom_utl`,' ',`prenom_utl`) REGEXP '^".$pattern."' " .
    "UNION SELECT `utilisateurs`.`id_utilisateur`,CONCAT(`surnom_utbm`,' (',`prenom_utl`,' ',`nom_utl`,')') " .
    "FROM `utl_etu_utbm` " .
    "INNER JOIN `utilisateurs` ON `utl_etu_utbm`.`id_utilisateur` = `utilisateurs`.`id_utilisateur` " .
    "WHERE `surnom_utbm`!='' AND `surnom_utbm` REGEXP '^".$pattern."' " .
    "ORDER BY 2 LIMIT 10");

  if ( !$req || $req->errno != 0) // Si l'expression régulière envoyée par l'utilisateur est invalide, on évite l'erreur mysql
  {
    echo "<ul>\n";
    echo "<li>Recherche invalide.</li>\n";
    echo "</ul>\n";
    echo "<div class=\"clearboth\"></div>";
    exit();
  }


  echo "<ul>\n";

  while ( list($id,$email) = $req->get_row() )
  {
    echo "<li><div class=\"imguser\"><img src=\"";

    if (file_exists($topdir."var/img/matmatronch/".$id.".identity.jpg"))
      echo $wwwtopdir."var/img/matmatronch/".$id.".identity.jpg";
    elseif (file_exists($topdir."var/img/matmatronch/".$id.".jpg"))
      echo $wwwtopdir."var/img/matmatronch/".$id.".jpg";
    else
      echo $wwwtopdir."var/img/matmatronch/na.gif";

    echo "\" /></div><a href=\"#\" onclick=\"userselect_set_user('$wwwtopdir','".$_REQUEST["ref"]."',$id,'".addslashes(htmlspecialchars($email))."'); return false;\">".htmlspecialchars($email)."</a></li>\n";
  }
  echo "</ul>\n";
  echo "<div class=\"clearboth\"></div>";
  exit();
}
elseif ( $_REQUEST['module']=="userinfo" )
{
  if ( !$site->user->is_valid() && !count($_SESSION["Comptoirs"])) exit();

  $user = new utilisateur($site->db,$site->dbrw);
  $user->load_by_id($_REQUEST["id_utilisateur"]);
  if ( $user->id < 0 )
    $user = &$site->user;

  if (file_exists($topdir."var/img/matmatronch/".$user->id.".identity.jpg"))
    echo "<img src=\"".$wwwtopdir."var/img/matmatronch/".$user->id.".jpg\" alt=\"\" />\n";
  else
    echo "<img src=\"".$wwwtopdir."var/img/matmatronch/na.gif"."\" alt=\"\" />\n";

  echo "<p class=\"nomprenom\">". $user->prenom . " " . $user->nom . "</p>";
  if ( $user->surnom )
    echo "<p class=\"surnom\">'' ". $user->surnom . " ''</p>";
  echo "<div class=\"clearboth\"></div>";
  exit();
}
elseif ( $_REQUEST['module']=="entinfo" )
{
  $class = $_REQUEST['class'];

  if ( class_exists($class) )
    $std = new $class($site->db);

  elseif ( isset($GLOBALS["entitiescatalog"][$class][5]) && $GLOBALS["entitiescatalog"][$class][5] )
  {
    include($topdir."include/entities/".$GLOBALS["entitiescatalog"][$class][5]);
    if ( class_exists($class) )
      $std = new $class($site->db);
  }

  if ($class=="utilisateur")
    $std->load_all_by_id($_REQUEST['id']);
  else
    $std->load_by_id($_REQUEST['id']);

  if ( !$std->is_valid() )
  {
    echo "?";
    exit();
  }

  if ( !$std->allow_user_consult($site->user) )
    exit();

  if ( $std->can_preview() )
    echo "<p class=\"stdpreview\"><img src=\"".$wwwtopdir.$std->get_preview()."\" alt=\"".htmlentities($std->get_display_name(),ENT_COMPAT,"UTF-8")."\" /></p>";

  echo "<p class=\"stdinfo\">".$std->get_html_extended_info()."</p>";
  echo "<div class=\"clearboth\"></div>";
  exit();

}
elseif ( $_REQUEST['module']=="entdesc" )
{
  $class = $_REQUEST['class'];

  if ( class_exists($class) )
    $std = new $class($site->db);

  elseif ( isset($GLOBALS["entitiescatalog"][$class][5]) && $GLOBALS["entitiescatalog"][$class][5] )
  {
    include($topdir."include/entities/".$GLOBALS["entitiescatalog"][$class][5]);
    if ( class_exists($class) )
      $std = new $class($site->db);
  }

  $std->load_by_id($_REQUEST['id']);

  if ( !$std->is_valid() )
  {
    echo "?";
    exit();
  }

  if ( !$std->allow_user_consult($site->user) )
    exit();

  echo htmlentities($std->get_description(),ENT_NOQUOTES,"UTF-8");

  exit();
}
elseif ( $_REQUEST['module']=="fsfield" )
{
  $class = $_REQUEST['class'];
  $field = $_REQUEST['field'];


  if ( !ereg("^([a-z0-9]*)$",$class) )
    exit();

  $std = null;

  if ( class_exists($class) )
    $std = new $class($site->db);

  elseif ( isset($GLOBALS["entitiescatalog"][$class][5]) && $GLOBALS["entitiescatalog"][$class][5] )
  {
    include($topdir."include/entities/".$GLOBALS["entitiescatalog"][$class][5]);
    if ( class_exists($class) )
      $std = new $class($site->db);
  }

  if ( is_null($std) )
    exit();

  if ( !$std->can_fsearch() )
    exit();

  if ( !$std->allow_user_consult($site->user) )
    exit();

  if ( $_REQUEST['pattern'] != "" )
  {
    $conds=array();
    if(isset($_REQUEST['conds']) && !empty($_REQUEST['conds']) && is_array($_REQUEST['conds']))
      $conds=$_REQUEST['conds'];
    $res = $std->fsearch ( $_REQUEST['pattern'], 6 , $conds);
    if ( !is_null($res) )
    {
      $buffer = "<ul class=\"fsfield_list\">";
      foreach ( $res as $id => $name )
      {
        $buffer .= "<li>";

        $std->id = $id;
        if ( $std->can_preview() )
        {
          $img = $std->get_preview();
          if ( !is_null($img) )
            $buffer .= "<div class=\"imguser\"><img src=\"".$wwwtopdir.$img."\" /></div>";
        }

        $buffer .= "<a href=\"#\" onclick=\"fsfield_sel('$wwwtopdir','$field',$id,'".addslashes(htmlspecialchars($name))."','".$GLOBALS["entitiescatalog"][$class][2]."'); return false;\">";
        $buffer .= htmlspecialchars($name);
        $buffer .= "</a>";
        $buffer .= "</li>";
      }
      $buffer .=  "</ul>";
      $buffer .=  "<div class=\"clearboth\"></div>";
    }
    else
      $buffer="<p class=\"error\">Requête invalide</p>";
  }
  else
    $buffer="";

  echo "if ( ".$_REQUEST['sequence']." > fsfield_current_sequence['".$field."'] )\n{\n";
  echo "  fsfield_current_sequence['".$field."']=".$_REQUEST['sequence'].";\n";
  echo "  var content = document.getElementById('".$field."_result');\n";
  echo "  content.style.zIndex = 100000;\n";
  echo "  content.style.display = 'block';\n";
  echo "  content.innerHTML ='".addslashes($buffer)."';\n";
  echo "}\n";

  exit();
}
elseif ( $_REQUEST['module']=="exfield" )
{
  $class = $_REQUEST['class'];
  $field = $_REQUEST['field'];
  $eclass = $_REQUEST['eclass'];

  if ( !ereg("^([a-z0-9]*)$",$class) || !ereg("^([a-z0-9]*)$",$class) )
    exit();

  $std = null;

  if ( class_exists($eclass) )
    $std = new $eclass($site->db);

  elseif ( isset($GLOBALS["entitiescatalog"][$eclass][5]) && $GLOBALS["entitiescatalog"][$eclass][5] )
  {
    include($topdir."include/entities/".$GLOBALS["entitiescatalog"][$eclass][5]);
    if ( class_exists($eclass) )
      $std = new $eclass($site->db);
  }

  if ( is_null($std) )
    exit();

  if ( $_REQUEST['eid'] == "root" )
  {
    $std = $std->get_root_element();
    if ( is_null($std) )
      exit();
  }
  else
    $std->load_by_id($_REQUEST['eid']);

  if ( !$std->is_valid() )
    exit();

  if ( !$std->allow_user_consult($site->user) )
    exit();

  $childs = $std->get_childs($site->user);

  if ( is_null($childs) || count($childs) == 0 )
    exit();

  foreach ( $childs as $child )
  {
    $name = $child->get_display_name();

    echo "<li>";

    echo "<a href=\"#\" onclick=\"";
    if ( get_class($child) == $class )
      echo "exfield_select('$wwwtopdir','$field','$class','".$child->id."','".addslashes(htmlspecialchars($name))."','".$GLOBALS["entitiescatalog"][$class][2]."');";
    else
      echo "exfield_explore('$wwwtopdir','$field','$class','".get_class($child)."','".$child->id."');";
    echo "return false;\">";

    echo "<img src=\"".$wwwtopdir."images/icons/16/".$GLOBALS["entitiescatalog"][get_class($child)][2]."\" alt=\"\" />";
    echo htmlspecialchars($name);
    echo "</a>";

    echo "<ul id=\"".$field."_".get_class($child)."_".$child->id."\"></ul>";

    echo "</li>";
  }


  exit();
}
elseif( $_REQUEST['module']=="tinycal" )
{
  $cal = new tinycalendar($site->db);
  $cal->set_target($_REQUEST['target']);
  $cal->set_type($_REQUEST['type']);
  $cal->set_ext_topdir($_REQUEST['topdir']);
  echo $cal->html_render();
  exit();
}

if ( $_REQUEST['class'] == "calendar" )
{
  if(isset($_REQUEST['subclass']) && !empty($_REQUEST['subclass']))
    $subclass=$_REQUEST['subclass'];
  else
    $subclass='';
  if(isset($_REQUEST['id_box']) && !empty($_REQUEST['id_box']))
    $cts = new calendar($site->db,null,$subclass,$_REQUEST['id_box']);
  else
    $cts = new calendar($site->db);
}
else
  $cts = new contents();

echo $cts->html_render();



?>
