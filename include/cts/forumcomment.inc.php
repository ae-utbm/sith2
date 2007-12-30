<?php
/* Copyright 2007
 * - Julien Etelain <julien CHEZ pmad POINT net>
 *
 * Ce fichier fait partie du site de l'Association des Etudiants de
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
 * @file
 */
require_once($topdir . "include/entities/forum.inc.php");
require_once($topdir . "include/entities/sujet.inc.php");
require_once($topdir . "include/entities/message.inc.php");
require_once($topdir . "include/entities/nouvelle.inc.php");
require_once($topdir . "include/entities/asso.inc.php");

/**
 * Classe permettant l'affichage et l'interaction avec le forum pour commenter
 * une nouvelle. 
 * L'objectif initial de ce contents est de permettre la creation de pages de type
 * "blog".
 */
class forumcomment extends contents
{
  
  function forumcomment ( $page, &$nouvelle, &$user )
  {
    global $topdir, $wwwtopdir;
    
    if ( strstr($page,"?"))
      $page = $page."&";
    else
      $page = $page."?";    
    
    $this->add_title(1,"Commentaires");

    if ( $_REQUEST["cmtaction"] == "new" )
      $this->new_comment($nouvelle,$user,$_REQUEST["cmttitre"],$_REQUEST["cmtmessage"]);
    elseif ( $_REQUEST["cmtaction"] == "delete" )
    $this->delete_comment($nouvelle,$user,$_REQUEST["id_message"]);

    if ( $user->is_valid() )
    {
      $grps = $user->get_groups_csv();
      $req = new requete($nouvelle->db,"SELECT frm_sujet.* ".
        "FROM frm_sujet ".
        "INNER JOIN frm_forum USING(`id_forum`) ".
        "WHERE ((droits_acces_forum & 0x1) OR " .
        "((droits_acces_forum & 0x10) AND id_groupe IN ($grps)) OR " .
        "(id_groupe_admin IN ($grps)) OR " .
        "((droits_acces_forum & 0x100) AND id_utilisateur='".$user->id."')) ".
        "AND (`frm_sujet`.`id_nouvelle`='" . mysql_escape_string($nouvelle->id) . "') ".
        "ORDER BY date_sujet");
    }
    else
      $req = new requete($nouvelle->db,"SELECT frm_sujet.* ".
        "FROM frm_sujet ".
        "INNER JOIN frm_forum USING(`id_forum`) ".
        "WHERE (droits_acces_forum & 0x1) ".
        "AND (`frm_sujet`.`id_nouvelle`='" . mysql_escape_string($nouvelle->id) . "') ".
        "ORDER BY date_sujet");
    
    if ( $req->lines < 1 )
    {
      $this->add_paragraph("Soyez le premier à réagir !");
      $this->comment_form($page,$nouvelle,$user);
      return;      
    }
    
    $npp=40;
    $start=0;
    $nb_messages=0;
    $sujets=array();
    
    while ( $row = $req->get_row()  )
    {    
      $sujet = new sujet($nouvelle->db,$nouvelle->dbrw);
      $sujet->_load($row);
      $nb_messages+=$sujet->nb_messages;
      $sujets[] = $sujet;  
    }
    
    $nbpages = ceil($nb_messages / $npp);
    

    
    if ( isset($_REQUEST["spage"]) )
    {
      $start = intval($_REQUEST["spage"])*$npp;
      if ( $start > $nb_messages )
      {
        $start = $nb_messages;
        $start -= $start%$npp;
      }
    }
    
    $entries=array();
    
    for( $n=0;$n<$nbpages;$n++)
      $entries[]=array($n, $page."spage=".$n,$n+1);
      
    $cts->add(new tabshead($entries, floor($start/$npp), "_top"));
    
    $this->buffer .= "<div id=\"comments\">";
    
    while ( $row = $req->get_row()  )
    {
      if ( $start < $sujet->nb_messages )
      {
        $spage = ceil($start/$npp);
        
        if ( $user->is_valid() )
          $last_read = $sujet->get_last_read_message ( $user->id );
        else
          $last_read = null;   
             
        $rows = $sujet->get_messages ( $user, $start, $npp );
        
        $firstunread=true;
        $n=0;
        
        foreach ( $rows as $row )
        {
          $t = strtotime($row['date_message']);
          
          if ( $user->is_valid() && 
          ( is_null($last_read) || $last_read < $row['id_message'] ) && 
          ( is_null($user->tout_lu_avant) || $t > $user->tout_lu_avant ) )
          {
            $this->buffer .= "<div class=\"comment nonlu\" id=\"msg".$row['id_message']."\">\n";
            if ( $firstunread )
            {
              $firstunread=false;  
              $this->buffer .= "<div id=\"firstunread\"></div>";
            }
          }
          else
          {
            if ( $n )
              $this->buffer .= "<div class=\"comment pair\" id=\"msg".$row['id_message']."\">\n";
            else
              $this->buffer .= "<div class=\"comment\" id=\"msg".$row['id_message']."\">\n";
            $n=($n+1)%2;
          }
    
          if ( $row['titre_message'] )
            $this->buffer .= "<h2 class=\"cmtt\">".htmlentities($row['titre_message'], ENT_NOQUOTES, "UTF-8")."</h2>\n";
          else
            $this->buffer .= "<h2 class=\"cmtt\">&nbsp;</h2>\n";     
    
          $this->buffer .= "<p class=\"date\">".human_date($t)."</p>\n";
    
          if (($user->is_valid() && $user->id == $row['id_utilisateur']) ||($this->is_admin($nouvelle,$user)))
          {
            $this->buffer .= "<p class=\"actions\">";
            $this->buffer .= "<a href=\"".$wwwtopdir.$page."cmtaction=delete&amp;id_message=".$row['id_message']."&amp;spage=$spage\">Supprimer</a>";           
            $this->buffer .= "</p>\n";   
          }
              
          $this->buffer .= "<div class=\"auteur\">\n";
          
          $this->buffer .= "<p class=\"funame\"><a href=\"".$wwwtopdir."user.php?id_utilisateur=".$row['id_utilisateur']."\">".htmlentities($row['alias_utl'],ENT_NOQUOTES,"UTF-8")."</a></p>\n";
          
          $img=null;
          if (file_exists($topdir."var/img/matmatronch/".$row['id_utilisateur'].".jpg"))
            $img = $wwwtopdir."var/img/matmatronch/".$row['id_utilisateur'].".jpg";
    
          if ( !is_null($img) )
            $this->buffer .= "<p class=\"fuimg\"><img src=\"".htmlentities($img,ENT_NOQUOTES,"UTF-8")."\" /></p>\n";
          
          $this->buffer .= "</div>\n";
          $this->buffer .= "<div class=\"forummessage\">\n";
          
          if ( $row['syntaxengine_message'] == "bbcode" )
            $this->buffer .= bbcode($row['contenu_message']);
            
          elseif ( $row['syntaxengine_message'] == "doku" )
            $this->buffer .= doku2xhtml($row['contenu_message']);
            
          elseif ( $row['syntaxengine_message'] == "plain" )
            $this->buffer .= "<pre>".htmlentities($row['contenu_message'],ENT_NOQUOTES,"UTF-8")."</pre>";
          
          else // text
            $this->buffer .= nl2br(htmlentities($row['contenu_message'],ENT_NOQUOTES,"UTF-8"));
            
          if ( !is_null($row['signature_utl']) )  
          {
            $this->buffer .= "<div class=\"signature\">\n";      
            $this->buffer .= doku2xhtml($row['signature_utl']);
            $this->buffer .= "</div>\n";      
          }
          
          $this->buffer .= "</div>\n";      
          $this->buffer .= "<div class=\"clearboth\"></div>\n";
          $this->buffer .= "</div>\n";
        }
        $this->buffer .= "</div>\n";
        
        $num = $start+$npp-1;
        if ( $num >= $sujet->nb_messages )
          $max_id_message = null;
        else
        {
          $req = new requete($nouvelle->db,"SELECT id_message FROM frm_message WHERE id_sujet='".mysql_real_escape_string($sujet->id)."' ORDER BY date_message LIMIT $num,1"); 
          list($max_id_message) = $req->get_row();    
        }
        $sujet->set_user_read ( $user->id, $max_id_message );      
      }
      $start -= $sujet->nb_messages;
    }
    $this->buffer .= "</div>";
    
    $cts->add(new tabshead($entries, floor($start/$npp), "_top"));
    
    $this->comment_form($page,$nouvelle,$user);
  }
  
  private function comment_form($page, &$nouvelle, &$user)
  {
    $this->add_title(2,"Nouveau commentaire");
    if ( !$user->is_valid() )
    {
      $this->login_form($page);
      return;
    }
    $frm = new form("commentfrm",$page."cmtaction=new",false,"POST");
  	$frm->add_text_field("cmttitre","Titre");
    $frm->add_text_area("cmtmessage", "Commentaire","",60,10);
  	$frm->add_submit("cmtactionnew","Ajouter");
    $this->add($frm);
  }
  
  private function login_form($page)
  {
    global $topdir, $wwwtopdir;
    
    $this->add_paragraph("Pour pouvoir ajouter un commentaire, vous devez avoir un compte sur le site de l'association des étudiants de l'utbm (ae.utbm.fr) et vous identifier.");
    
    $this->add_title(3,"Déjà inscrit ? Connectez vous");
    
    $_SESSION['session_redirect'] = $_SERVER["REQUEST_URI"];
    
    $frm = new form("connect2","/connect.php",true,"POST","Vous avez déjà un compte");
  	$frm->add_select_field("domain","Connexion",array("utbm"=>"UTBM","assidu"=>"Assidu","id"=>"ID","autre"=>"Autre","alias"=>"Alias"), $section=="jobetu"?"autre":"utbm");
  	$frm->add_text_field("username","Utilisateur","prenom.nom","",27);
  	$frm->add_password_field("password","Mot de passe","","",27);
  	$frm->add_checkbox ( "personnal_computer", "Me connecter automatiquement la prochaine fois", false );
  	$frm->add_submit("connectbtn2","Se connecter");
    $this->add($frm,true);	    
    
    $this->add_title(3,"Nouveau ? Inscrivez-vous !");
    
    if ( $topdir == $wwwtopdir && !preg_match('/pg2/', $_SERVER['SCRIPT_FILENAME']) )
      $this->add_paragraph("<a href=\"/newaccount.php\">Créer un compte</a>");    
    else
      $this->add_paragraph("<a href=\"/newaccount.php\" target=\"_blank\">Créer un compte sur ae.utbm.fr</a>");    
    
  }
  
  private function new_comment(&$nouvelle,&$user,&$sujet,$t,$m)
  {
    if ( !$user->is_valid() )
      return;
    
    $forum = new forum($nouvelle->db,$nouvelle->dbrw);
    $message = new message($nouvelle->db,$nouvelle->dbrw);
    
    if ( $sujet->is_valid() )
    {
      $forum->load_by_id($sujet->id_forum);
      $message->create($forum, $sujet, $user->id, $t, $message->commit_replace($m,$user),"doku");      
    }
    else
    {
      $forum->load_by_id(3);
      if ( !is_null($nouvelle->id_asso) )
      {
        $req = new requete($nouvelle->db,"SELECT * FROM frm_forum WHERE id_asso='".mysql_escape_string($nouvelle->id_asso)."' AND categorie_forum=0");
        if ( $req->lines > 0 )
          $forum->_load($req->get_row());
      }
      
      $sujet->create ( $forum, $user->id, "Commentaires de ".$nouvelle->titre, "",
        SUJET_NORMAL,null,null,$nouvelle->id,null,null );
      $message->create($forum, $sujet, $user->id, $t, $message->commit_replace($m,$user),"doku");      
    }
    
    $this->add_paragraph("Votre commentaire a été ajouté.");
  }
  
  
  private function delete_comment(&$nouvelle,&$user,$id_message)
  {
    if ( !$user->is_valid() )
      return;    
    $forum = new forum($nouvelle->db,$nouvelle->dbrw);
    $sujet = new sujet($nouvelle->db,$nouvelle->dbrw);
    $message = new message($nouvelle->db,$nouvelle->dbrw);
    
    $message->load_by_id($id_message);
    if ( !$message->is_valid() )
      return;
  
    if ((!$this->is_admin($nouvelle,$user))
        && ($message->id_utilisateur != $user->id))
      return;
  
    $sujet->load_by_id($message->id_sujet); 
    $forum->load_by_id($sujet->id_forum); 
  
    $message->delete($forum, $sujet);
  
  }
  
  function html_render ()
	{
		return "<div class=\"commentsbox\">".$this->buffer."</div>";
  }
  
  private function is_admin(&$nouvelle,&$user)
  {
		if ( $user->is_in_group("moderateur_forum") )
		  return true;	    
		  
		if ( $user->is_in_group("moderateur_site") )
		  return true;	
    
    if ( $nouvelle->id_utilisateur == $user->id) 
      return true;
      
    if ( !is_null($nouvelle->id_asso) && $user->is_asso_role($nouvelle->id_asso,ROLEASSO_MEMBREBUREAU) )
      return true;
    
    return false;
  }
  
}

?>