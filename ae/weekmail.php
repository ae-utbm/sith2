<?php
/* Copyright 2008
 * - Simon Lopez < simon dot lopez at ayolo dot org >
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
$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
$site = new site ();
if (!$site->user->is_in_group ("moderateur_site"))
        $site->error_forbidden();

$site->start_page ("none", "Weekmail");

if($_REQUEST['action']=='send')
{
  if(isset($_REQUEST['id']))
  {
    $sql='SELECT * FROM weekmail WHERE id='.intval($_REQUEST['id']);
    $req = new requete($site->db,$sql);
    if($req->lines==1)
    {
      list($id,$date,$title,$content,$statut)=$req->get_row();
      if($statut!=1)
      {
        require_once($topdir. "include/lib/phpmailer.inc.php");
        $mail=new PHPMailer();
        $body='<html>'."\n";
        $body.='<head>'."\n";
        $body.='<title>[weekmail] '.$title.'</title>'."\n";
        $body.='<link rel="stylesheet" type="text/css" '.
        $body.='href="http://ae.utbm.fr/css/weekmail.css" />'."\n";
        $body.='</head>'."\n";
        $body.='<body>'."\n";
        $body.=doku2xhtml($text,false,true);
        $body.='</body>'."\n";
        $body.='</html>'."\n";
        echo $body;
        $mail->From='ae@utbm.fr';
        $mail->FromName='AE';
        $mail->Subject='[weekmail] '.$title;
        $mail->AltBody="Pour visionner le weekmail allez à l'adresse ".
                       "suivante :\nhttp://ae.utbm.fr/weekmail.php?id=".$id.
                       "\n\nCordialement,\nL'AE";
        $mail->MsgHTML($body);
        $mail->AddAddress("simon.lopez@ayolo.org");

        if($mail->Send())
        {
          $sql='UPDATE weekmail SET statut=1, date="'.date("Y-m-d H:i:s").'" WHERE id='.intval($_REQUEST['id']);
          //$req = new requete($site->dbrw,$sql);
        }
        else
          $site->add_contents(new error('Echec de l\'envoi','Le weekmail n\'a pas pu être envoyé ...'));
      }
      else
        $site->add_contents(new error('Echec de l\'envoi','Ce weekmail a déjà été envoyé'));
    }
    else
      $site->add_contents(new error('Weekmail inconnu !','Weekmail inconnu au bataillon ...'));
  }
}


if(isset($_REQUEST['id']))
{
  $sql='SELECT * FROM weekmail WHERE id='.intval($_REQUEST['id']);
  $req = new requete($site->db,$sql);
  if($req->lines==0)
    $cts=new error('Weekmail not found!','Weekmail inconnu au bataillon moussaillon');
  else
  {
    list($id,$date,$title,$content,$statut)=$req->get_row();
    $cts = new contents('[Weekmail] '.$title);
    if($statut==1)
      $statut='Envoyé';
    else
      $statut='En attente';
    list($annee, $mois, $jour) = explode("-", $date);
    $date=$jour."/".$mois."/".$annee;
    $cts->add_paragraph('Le '. $date .' ('.$statut.')');
    $cts->puts(doku2xhtml($content));
  }
}
elseif($_REQUEST['action']=='edit')
{
  $cts = new contents('[Weekmail] '.$title);
}
elseif($_REQUEST['action']=='add')
{
  $cts = new contents('Nouveau weekmail');
}
else
{
  $cts = new contents('Bleh');
}

$site->add_contents($cts);
$site->end_page ();

?>
