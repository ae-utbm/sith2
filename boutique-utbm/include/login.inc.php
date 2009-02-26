<?php
/* Copyright 2007, 2008
 * - Julien Etelain <julien CHEZ pmad POINT net>
 * - Simon Lopez < simon dot lopez at ayolo dot org >
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

require_once($topdir."include/cts/board.inc.php");

/**
 * Affiche un formulaire de connexion et des liens pour l'inscription.
 *
 * Vous ne devriez pas avoir besoin de ce contents.
 * Utilisez site::allow_only_logged_users
 *
 * @author Julien Etelain
 * @ingroup display_cts
 * @see site::allow_only_logged_users
 */
class loginerror extends board
{

  function loginerror($section = "none")
  {
    global $wwwtopdir;

    $_SESSION['session_redirect'] = $_SERVER["REQUEST_URI"];

    $this->board("Merci de vous identifier","loginerror");

    $frm = new form("connect2","/connect.php",true,"POST","Vous etes un particulier");
    $frm->add_info("Compte unifié avec le site ae");
    $frm->add_hidden("mode","ae");
    $frm->add_select_field("domain","Connexion",array("utbm"=>"UTBM","assidu"=>"Assidu","id"=>"ID","autre"=>"Autre","alias"=>"Alias"), $section=="jobetu"?"autre":"utbm");
    $frm->add_text_field("username","Utilisateur","prenom.nom",true,27,true);
    $frm->add_password_field("password","Mot de passe","",true,27);
    $frm->add_checkbox ( "personnal_computer", "Me connecter automatiquement la prochaine fois", false );
    $frm->add_submit("connectbtn2","Se connecter");
    $this->add($frm,true);

    $frm = new form("connect2","/connect.php",true,"POST","Vous etes un service UTBM");
    $frm->add_hidden("mode","service");
    $frm->add_text_field("cf","Centre financier","centre financier",true,27,true);
    $frm->add_password_field("password","Mot de passe","",true,27);
    $frm->add_checkbox ( "personnal_computer", "Me connecter automatiquement la prochaine fois", false );
    $frm->add_submit("connectbtn2","Se connecter");
    $this->add($frm,true);

    $cts = new contents("Créer un compte");
    $cts->add_paragraph("Pour acceder à cette page vous devez posséder un compte.<br/>La création d'un compte nécessite que vous possédiez une addresse e-mail pour pouvoir l'activer.");
    $cts->add_paragraph("<a href=\"/newaccount.php\">Créer un compte</a>");
    $this->add($cts,true);
  }
}


?>
