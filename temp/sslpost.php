<?php
/** @file
 *
 * @brief Un exemple avantgardiste d'un parsing de tarés sur le
 * webmail de l'UTBM. Il n'est pas recherché ici une application
 * directe pratique, mais cela permet d'étendre un peu le champ de
 * compétences, et d'envisager d'aller récupérer sur des données en
 * ssl ailleurs.
 *
 * Ou pourquoi pas ne pas l'intégrer plus tard en tant que vraie boite
 * du site ? Notons toutefois que le passage des identifiants se fait
 * pour l'instant "en clair" via une méthode GET (appel certes via
 * javascript), sans doute pas la plus élégante ...
 *
 * Enfin, ca prend un peu de temps, mais bon.
 *
 */

/* Copyright 2007
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
 * along with this program; if not, write to the Free Sofware
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */


if ($_GET['boiboitemail'] == 1)
{
  echo get_infos_from_ouaibe_mail($_GET['implogin'],
                                 $_GET['imppasswd']);
  exit();
}

$topdir = "../";


require_once($topdir. "include/site.inc.php");
require_once($topdir . "include/entities/asso.inc.php");
require_once($topdir . "include/entities/news.inc.php");

$site = new site ();

$site->start_page("accueil","Bienvenue");

$site->add_contents(new contents("Coincoin", "plouf !"));


$boiboitepasinfo =
"
<script language=\"javascript\">
function sbmt_infos(obj)
{
  var plouf = document.getElementById('implogin');
  var coin  = document.getElementById('imppasswd');
  var blouh = document.getElementById('sbox_mails');

  blouh.innerHTML = '<b>Chargement en cours ...</b>';

  openInContents('sbox_mails', './sslpost.php', 'implogin='+plouf.value + '&imppasswd='+coin.value+'&boiboitemail=1&');

}
</script>

<form>
  <input type=\"hidden\" name=\"postedboiboite\" value=\"1\" />
  Login :<input id=\"implogin\" type=\"text\" name=\"implogin\" value=\"\" />
  Passw :<input id=\"imppasswd\" type=\"password\" name=\"imppassw\" value=\"\" />
  <input type=\"button\" name=\"post\" value=\"hop !\" onclick=\"javascript:sbmt_infos(this);\" />
</form>
";


$site->add_box("mails",
              new contents("Mes 10 derniers mails",
                           $boiboitepasinfo));

$site->set_side_boxes("right",array("anniv", "mails"),"accueil_c_right");


$site->end_page();


function send_https($host,
                   $page,
                   $type,
                   $query = null,
                   $cttype = "application/x-www-form-urlencoded")
{

  $fp = fsockopen("ssl://".$host, 443);

  if ($fp)
    {

      $plouf = "";

      /* building full headers query */
      $out = $type . " /" . $page." HTTP/1.1\r\n";
      $out .= "Host: ".$host."\r\n".
       "Content-type: ".$cttype. "\r\n".
       "User-Agent: Mozilla 4.0\r\n";

      if ($query != null)
       {
         $out .= "Content-length: ".strlen($query). "\r\n";
       }

      $out .= "\r\nConnection: Close\r\n\r\n$query";

      /* send it to the server */
      fwrite($fp, $out);

      /* wait for answer */
      while (!feof($fp))
       {
         $plouf .= fgets($fp, 128);
       }
      fclose($fp);
    }

  return $plouf;
}


function get_infos_from_ouaibe_mail($login, $password)
{
  /* first, get a REDIRECT answer from host to generate our horde id */
  $rs = send_https("webmail.utbm.fr", "login.php", "GET");
  preg_match("/Location: (.*)\r\n/", $rs, $matches);

  $newaddr = $matches[1];

  preg_match("/https:\/\/([^\/]*)\/(.*)/", $newaddr, $matches);

  $newhost = $matches[1];
  $newaddr = $matches[2];

  /* get login page */
  $newpage =  send_https($newhost, $newaddr, "GET");


  preg_match("/<form action=\"(.*)\" method=\"post\" name=\"implogin\">/", $newpage,  $postaddr);

  $postaddr = $postaddr[1];
  if ($postaddr[0] == "/")
    $postaddr = substr($postaddr, 1);

  preg_match("/<input type=\"hidden\" name=\"url\" value=\"(.*)\" \/>/",
            $newpage,
            $url);

  $url = $url[1];


  $query = "actionID=105&url=" . $url . "&mailbox=INBOX&imapuser=" .
    $login . "&pass=" . $password . "&server=cyrus&folders=&new_lang=fr_FR\r\n\r\n";


  /* Let's go and log on ! */
  $newpage =  send_https($newhost, $postaddr, "POST", $query);

  preg_match("/Location: (.*)\r\n/", $newpage, $matches);
  $newaddr = $matches[1];

  preg_match("/https:\/\/([^\/]*)\/(.*)/", $newaddr, $matches);

  $newhost = $matches[1];
  $newaddr = $matches[2];

  preg_match("/Horde=(.*)&module/", $newaddr, $horde);

  $hordeid = $horde[1];

  $newaddr = "horde/imp/mailbox.php?Horde=".$hordeid."&sortby=0&sortdir=1";

  $newpage =  send_https($newhost, $newaddr, "GET");


  $newpage = str_replace(array("<b>", "</b>"), array("", ""), $newpage);

  preg_match_all("/<td><a href=\"\/horde\/imp\/message.php?.*>(.*)<\/a>/", $newpage, $subj);
  preg_match_all("/<td nowrap=\"nowrap\"><a href=\"\/horde\/imp\/message.php?.*>(.*)<\/a>/", $newpage, $from);

  $subj = $subj[1];
  $from = $from[1];

  $str = "<h1>Mes mails récents</h1>\n";
  $str .= "<ul>\n";


  for ($i = 0; $i < 5; $i++)
    {
      $str .= "<li><b>". utf8_encode($subj[$i]) . "</b>, de " . utf8_encode($from[$i]) . "</li>\n";
    }
  $str .= "</ul>";
  return $str;
}


?>
