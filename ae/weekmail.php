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
        $headers ='From: "AE"<ae@utbm.fr>'."\n"; 
        $headers.='Reply-To: ae@utbm.fr'."\n";
	$headers.='Return-Path: <ae@utbm.fr>'."\n";
	//$headers.='Bcc:ae.com@utbm.fr,ae@utbm.fr'."\n";
        $headers.='Content-Transfer-Encoding: 8bit';
	$headers.='MIME-Version: 1.0'."\n";
	$frontiere = '-----=' . md5(uniqid(mt_rand()));
	$headers.='Content-Type: multipart/alternative; boundary="'.$frontiere.'"';
	$message ='This is a multi-part message in MIME format.'."\n\n";
        $message.='--'.$frontiere.'--'."\n";
	$message.='Content-Type: text/plain; charset="utf8"'."\n";
	$message.='Content-Transfer-Encoding: 8bit'."\n\n";
	$message.='Pour visionner ce weekmail rendez vous à l\'adresse suivante :'."\n";
	$message.='http://ae.utbm.fr/weekmail.php?id='.$id."\n\n";
	$message.= '--'.$frontiere.'--'."\n";
	$message.= 'Content-Type: text/html; charset="utf8"'."\n";
	$message.='Content-Transfer-Encoding: 8bit'."\n\n";
	$message.='<html>';
	$message.='<head>';
	$message.='<title>[weekmail] '.$title.'</title>';
	$message.='<link rel="stylesheet" type="text/css" href="http://ae.utbm.fr/css/weekmail.css" />';
	$message.='</head>';
	$message.='<body>';
	$message.=doku2xhtml($text,false,true);
	$message.='</body>';
	$message.='</html>'."\n\n";
	$message.= '--'.$frontiere.'--'."\n";
	//if(mail('etudiants@utbm.fr','[weekmail] '.$title.'</title>',$message,$headers))
        if(mail('ae.info@utbm.fr','[weekmail] '.$title.'</title>',$message,$headers))
	{
	  $sql='UPDATE weekmail SET statut=1, date="'.date("Y-m-d H:i:s").'" WHERE id='.intval($_REQUEST['id']);
	  $req = new requete($site->db,$sql);
	}
      }
    }
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
