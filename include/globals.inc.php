<?php

/** @file
 *
 * @brief Fonctions diverses et variées.
 *
 */

/* Copyright 2004
 * - Alexandre Belloni <alexandre POINT belloni CHEZ utbm POINT fr>
 * - Thomas Petazzoni <thomas POINT petazzoni CHEZ enix POINT org>
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
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

 /* Definit si le site est en mode inscriptions intensives avec usage de l'API commune AE/BDS/INTEG/MMT */

 define('XMLRPC_USE',true);

function error_403($reason="")
{
	global $topdir;
	header("Location: ".$topdir."403.php?reason=$reason");
	$_SESSION['session_redirect'] = $_SERVER["REQUEST_URI"];
	exit();	
}

/** La fonction de generation de sortie, tres simple pour le
  * moment. Elle pourra etre amelioree plus tard pour supporter
  * l'indentation du code source de sortie.
  *
  * @param string La chaine a sortir dans le HTML
  *
  * @param indent Increment du niveau d'indentation. Si egal a 1, le
  *               niveau d'indentation sera augmente, si egal a -1
  *               il sera diminue. Par defaut, il vaut 0.
  */
function output ($string, $indent = 0)
{
  echo $string;
}

/** Affiche un message d'erreur
  * @param text Le message d'erreur
  */
function aeerror ($text)
{
  output("<span class=\"error\">\n", 1);
  output($text);
  output("</span>", -1);
  die();
}

/** Convertit la date en une chaîne human readable
 *
 * @param start Date de début au format YYYY-MM-DD HH:MM:SS. Si aucune
 * date de fin n'est donnée, alors une chaîne du type "Mardi 4 mai à
 * 14h00" sera générée.
 *
 * @param end Date de fin (optionnelle). Si elle est donnée, une
 * chaîne du type "Du mardi 4 mai à 14h00 au mardi 5 mai à 18h00" sera
 * générée.
 *
 * @return Une chaîne de caractère humainement lisible de la date ou
 * de l'intervalle de date.
 */
function HumanReadableDate($start, $end="", $time = true)
{
  $start = split("[-: ]", $start);
  $timestamp = mktime($start[3], $start[4], $start[5], $start[1], $start[2], $start[0]);

  if($end != "")
    {
      $end   = split("[-: ]", $end);
      $timestampend = mktime($end[3], $end[4], $end[5], $end[1], $end[2], $end[0]);
    }

  if(setlocale(LC_TIME, "fr_FR.UTF-8") == false)
    aeerror("Erreur de configuration des locales");

  /* Est-ce qu'une date de fin est donnée ? */
  if($end == "")
    {
	  if ($time)
	    return strftime("%A %e %B &agrave; %Hh%M", $timestamp);
	  else
	    return strftime("%A %e %B", $timestamp);
    }
  else
    {
      /* Si les dates de début et de fin sont le même jour,
         on affiche un truc du style "lundi 4 mai de 14h00 à 18h00" */
      if($start[2] == $end[2] && $start[1] == $end[1] && $start[0] == $end[0])
	    return (strftime("%A %e %B de %Hh%M", $timestamp) . strftime(" &agrave; %Hh%M", $timestampend));

      /* Sinon, on affiche un truc du style "du lundi 4 mai à 14h00 au mardi 5 mai à 15h00" */
      else
	    return (strftime("du %A %e %B �%Hh%M", $timestamp) . strftime(" au %A %e %B �%Hh%M", $timestampend));
    }
}

/** Créé un lien d'email ANTI-SPAM
 * @param email Adresse email
 * @param text Texte du lien
 */
function GenerateEmailLink($email, $text, $class="")
{
  $patterns = array ("/@/");
  $replace = array ("&#64;");
  $email = preg_replace ($patterns, $replace, $email);
  $text  = preg_replace ($patterns, $replace, $text);

  if($class == "")
    {
      return "<a href=\"mailto:" . $email . "\">" . $text . "</a>";
    }
  else
    {
      return "<a class=\"" . $class . "\" href=\"mailto:" . $email . "\">" . $text . "</a>";
    }
}

/** Génère une liste de sélection pour un formulaire
 *
 * @param values Un tableau associatif (clé => valeur) donnant la
 * liste des éléments.
 *
 * @param current La clé de l'élement sélectionné
 *
 * @param name Le nom du champ dans le formulaire
 *
 * @return le code html genere
 */
function GenerateSelectList($values, $current, $name, $size=1)
{
  $buffer = ("<select name=\"" . $name . "\" size=\"" . $size . "\">\n");

  foreach ($values as $key => $val)
    {
      if($key != $current)
	{
	  $buffer .= ("<option value=\"" . $key . "\">" . $val . "</option>\n");
	}
      else
	{
	  $buffer .= ("<option value=\"" . $key . "\" selected>" . $val . "</option>\n");
	}
    }
  $buffer .=("</select>\n");
  return $buffer;
}

/** Génération de mot de passe
 * Cette fonction va générer une chaîne aléatoire de la longueur
 * spécifiée. C'est notamment utile pour générer des mots de passe.
 *
 * @param nameLength Longueur de la chaîne
 *
 * @return La chaîne aléatoire
 */
function genere_pass ($nameLength=12)
{
  $NameChars = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKMNLOP';
  $Vouel = 'aeiouAEIOU';
  $Name = "";

  for ($index = 1; $index <= $nameLength; $index++)
    {
      if ($index % 3 == 0)
	{
	  $randomNumber = rand(1,strlen($Vouel));
	  $Name .= substr($Vouel,$randomNumber-1,1);
	}
      else
	{
	  $randomNumber = rand(1,strlen($NameChars));
	  $Name .= substr($NameChars,$randomNumber-1,1);
	}
    }

  return $Name;
}
/** Conversion des dates
 *
 * @param date La date a convertir
 *
 * @return -1 si probleme, la date convertie sinon
 */
function jj_mm_aaaa__to__aaaa_mm_jj($date)
{
  //Controle sur la longueur
  //un peu féblard !
  if (strlen($date)==10) {
  $ret =
    //Année
    substr($date,6,4). "-".
    //Mois
    substr($date,3,2). "-".
    //Jour
    substr($date,0,2);
  return $ret;
  }
  else return -1;
}
/** Conversion des dates
 *
 *
 * @param date La date a convertir
 *
 * @return -1 si probleme, la date convertie sinon
 */
function aaaa_mm_jj__to__jj_mm_aaaa($date, $char = "/")
{
 if (strlen($date)==10) {
  $ret =
    //Jour
    substr($date,8,2). $char.
    //Mois
    substr($date,5,2). $char.
    //Année
      substr($date,0,4);
  return $ret;
  }
  else return -1;
}
/** Vérification de l'email
 *
 * @param email L'email à vérifier
 * @param type  0 = email du type "prenom.nom"
 *              1 = email utbm
 *              2 = email assidu
 *              3 = email du type "truc@chose"
 *
 * @return 0 si valide, -1 si invalide
 */
function CheckEmail($email, $type = 0)
{
  /* L'email est vide, c'est pas bon. */
  if (empty($email))
    return false;

  if ($type == 0)
    {
      if (ereg("^([a-z\-]+)\.([a-z0-9\-]+)$", $email))
	return true;
      else
	return false;
    }

  /* Email utbm: prenom.nom@utbm.fr */
  elseif ($type == 1)
    {
      if (ereg("^([a-z\-]+)\.([a-z0-9\-]+)@utbm\.fr$", $email))
	return true;
      else
	return false;
    }

  elseif ($type == 2)
    {
      if (ereg("^([a-z\-]+)\.([a-z0-9\-]+)@assidu-utbm\.fr$", $email) ||
	  ereg("^([a-z\-]+)\.([a-z0-9\-]+)-([0-9]{4})@assidu-utbm\.fr$", $email))
	return true;
      else
	return false;
    }

  elseif ($type == 3)
    {
      if (ereg("^([A-Za-z0-9\._-]+)@([A-Za-z0-9_-]+)\.([A-Za-z0-9\._-]*)$", $email))
	return true;
      else
	return false;
    }

  return false;
}
/** Référence à la page
 *
 * @return l'url pointant sur la page elle même
 */
function URLCourante()
{
  $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  $rep = explode('/', $url);

  if ($rep[1] == "siteae")
    $url = strstr(substr($_SERVER['SCRIPT_URL'], 1), '/');

  return $url;
}

/** Génération d'une date située n jours après (si n > 0)
 *                               (n jours avant si n < 0)
 *
 * @param date la date à prendre comme point de repère
 *
 * @param format_parm le format de la date passé en argument
 *                    timestamp ou date (Y-m-d)
 *
 * @param n le nombre de jours à évaluer.  (exemple : n = -1 renverra
 * la veille de $date n = 1 renverra le lendemain).
 *
 * @param format_ret le format de la date à renvoyer
 * si = date la fonction renverra au format "Y-m-d"
 * si = timestamp, la fonction renverra un timestamp
 *
 * @return la date voulue (format Y-m-d)
 */
function relative_date($date, $format_parm, $format_ret, $n)
{
  if ($format_parm == "date")
    {
      if ($format_ret == "date")
	return date("Y-m-d", strtotime($date) + 24 * 3600 * $n);
      if ($format_ret == "timestamp")
	return strtotime($date) + 24 * 3600 * $n;
    }
  if ($format_parm == "timestamp")
    {
      if ($format_ret == "timestamp")
	return $date + 24 * 3600 * $n;
      if ($format_ret == "date")
	return date("Y-m-d", $date + 24 * 3600 * $n);
    }
}

function close_session ()
{
  if(isset($_COOKIE['AE_SESS_ID']))
    {
      $base = new mysqlae("rw");

      $req = new delete($base, "ae_site_sessions",
			array("id" => $_COOKIE['AE_SESS_ID']));

      setcookie("AE_SESS_ID", "0", mktime(12,0,0,1, 1, 1990), "/", "ae.utbm.fr", 0);
      unset($_COOKIE['AE_SESS_ID']);
    }

  session_unset();
  session_destroy();
}

/* obtention de la revision actuelle du site (subversion) */
function get_rev ()
{
  return exec ("/usr/share/php5/exec/rev_info.sh");
}

/* compat. php5 */
/*function file_put_contents ($file, $datas)
{
  $fres = fopen ($file, "w");
  fwrite ($fres, $datas);
  fclose ($fres);

  return true;
}*/

/** Convertit un nom
  *
  * Cette fonction convertit un nom en majuscule, de mani�re �
  * uniformiser l'aspect des noms dans la base.
  *
  * @param nom Le nom � normaliser
  *
  * @result Le nom normalis�
  */
function convertir_nom($nom)
{
  return trim(mb_strtoupper($nom));
}

/** Convertit un pr�nom
  *
  * Cette fonction convertit un pr�nom en majuscule, de mani�re �
  * uniformiser l'aspect des pr�noms dans la base.
  *
  * @param nom Le pr�nom � normaliser
  *
  * @result Le pr�nom normalis�
  */

function do_prenom_stuff($frags)
{
	$i = 1;
	$string = ucfirst(strtolower(trim($frags[0])));
	while($frags[$i])
	{
		if(!strstr($frags[$i],"-"))
			$string .= "-" . ucfirst(strtolower(trim($frags[$i])));
		$i++;
	}
	return $string;
}

function convertir_prenom ($string)
{
	$string = trim($string);

	// cas de l'exemple pierre-emmanuel, et de celui pierre - emmanuel
	if (strstr($string,"-"))
	{
		$frags = explode("-",$string);
		$string = do_prenom_stuff($frags);
	}

	// cas de l'exemple pierre emmanuel
	else if (strstr($string," "))
	{
		$frags = explode(" ",$string);
		$string = do_prenom_stuff($frags);
	}

	// cas de l'exemple laurent
	if (!(strstr($string," ")) && !(strstr($string,"-")))
	{
		$string = ucfirst(strtolower($string));
	}
	return $string;
}

/** Retourne un des parametres de la requete
 *
 * @param name Le nom du parametre
 * @param default Une valeur par defaut si le parametre n'est pas trouve
 * @param array Le tableau de recherche, par defaut : $_REQUEST
 */
function GetRequestParam ($name, $default = null, $array = null)
{
  if (!$array)
    $array = $_REQUEST;

  if (array_key_exists($name, $array))
    return $array[$name];

  return $default;
}

/* Changement du séparateur pour &amp */
ini_set("arg_separator.output", "&amp;");
?>
