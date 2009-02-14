<?php

/* Copyright 2007
 * - Benjamin Collet - bcollet <at> oxynux <dot> org
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
 * along with this program; if not, write to the Free Sofware
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

$topdir = "./";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/mysql.inc.php");
require_once($topdir. "include/mysqlae.inc.php");

function error($apikey)
{
  if(!$GLOBALS["is_using_ssl"])
    return "httpsRequired";

  $db = new mysqlae ("rw");

  if(!$db->dbh)
    return "DatabaseUnavailable";

  $valid = new requete($db,
    "SELECT `key` ".
    "FROM `sso_api_keys` ".
    "WHERE `key` = '".mysql_real_escape_string($apikey)."'");

  if($valid->lines != 1)
    return "KeyNotValid";

  return "ok";
}

function testLogin($message)
{
  $simplexml = new SimpleXMLElement($message->str);
  $apikey = $simplexml->apikey[0];
  $login = $simplexml->login[0];
  $password = $simplexml->password[0];

  $error = error($apikey);

  if($error == "ok")
  {
    $site = new site();
    $site->user->load_by_email($login);

    if($site->user->is_valid())
    {
      if($site->user->is_password($password))
        $return = $site->user->id;
      else
        $return = 0;
    }
    else
    {
      $return = 0;
    }
  }
  else
  {
    $return = $error;
  }

  $response = <<<XML
<testLoginResponse>
  <result>$return</result>
</testLoginResponse>
XML;

  return $response;
}

function testAssoRole($message)
{
  $simplexml = new SimpleXMLElement($message->str);
  $apikey = $simplexml->apikey[0];
  $login = $simplexml->uid[0];
  $asso = $simplexml->aid[0];
  $role = $simplexml->role[0];

  $error = error($apikey);

  if($error == "ok")
  {
    $site = new site();
    $site->user->load_by_id($iud);

    if($site->user->is_asso_role($asso, $role))
      $return = 1;
    else
      $return = 0;
  }
  else
    $return = $error;

  $response = <<<XML
<testAssoRoleResponse>
  <result>$return</result>
</testAssoRoleResponse>
XML;

  return $response;
}

/* Ils utilisent pas l'inscription via site AE donc on commente */
/*function inscription($message)
{
  $simplexml = new SimpleXMLElement($message->str);
  $apikey = $simplexml->apikey[0];
  $utbm = $simplexml->utbm[0];
  $nom = $simplexml->nom[0];
  $prenom = $simplexml->prenom[0];
  $email = $simplexml->email[0];
  $password = $simplexml->password[0];
  $naissance = $simplexml->naissance[0];
  $droitimage = $simplexml->droitimage[0];
  $alias = $simplexml->alias[0];
  $sexe = $simplexml->sexe[0];

  $error = error($apikey);

  if($error == "ok")
  {
    $site = new site();
    $user = new utilisateur($site->db,$site->dbrw);

    if(!$email)
      $return = "MailMissing";
    elseif(!ereg("^([A-Za-z0-9\._-]+)@([A-Za-z0-9_-]+)\.([A-Za-z0-9\._-]*)$", $email))
      $return = "MailNotValid";
    elseif($user->load_by_email($email))
      $return = "AccountsExists";
    elseif(!$nom)
      $return = "NameMissing";
    elseif(!$prenom)
      $return = "LastnameMissing";
    elseif($utbm == 1 && !ereg("^([a-zA-Z0-9\.\-]+)@(utbm\.fr|assidu-utbm\.fr)$",$mail))
      $return = "NotUtbmMail";
    elseif(!$password)
      $return = "PasswordMissing";
    elseif($sexe != 1 && $sexe != 2)
      $return = "InvalidSex";
    elseif($alias && !preg_match("#^([a-z0-9][a-z0-9\-\._]+)$#i",$alias))
      $return = "InvalidAlias";
    elseif($alias && !$user->is_alias_avaible($alias))
      $return = "AliasExists";
    else
    {
      $user->create_user($nom, $prenom, $alias, $email, $password, $droitimage, $naissance, $sexe);
      $user->load_by_email($email);
      $return = $user->id;
    }
  }
  else
  {
    $return = $error;
  }

  $response = <<<XML
<inscriptionResponse>
<result>$return</result>
</inscriptionResponse>
XML;

  return $response;
}*/

/*$service = new WSService(array("operations" => array("testLogin", "inscription")));*/
$service = new WSService(array("operations" => array("testLogin", "testAssoRole")));
$service->reply();

?>
