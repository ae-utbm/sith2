<?
/** @file
 *
 * @brief Page d'informations sur les UVs
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

$topdir = "../";

include($topdir. "include/site.inc.php");

require_once ($topdir . "include/entities/edt.inc.php");
require_once ($topdir . "include/cts/edt_img.inc.php");


$site = new site();

$edt = new edt($site->db, $site->dbrw);

$site->start_page("services", "Information sur les UVs");


function post($host,$query,$others='')
{

   $path=explode('/',$host);
   $host=$path[0];
   unset($path[0]);
   $path='/'.(implode('/',$path));
   $post="POST $path HTTP/1.1\r\nHost: $host\r\nContent-type: application/x-www-form-urlencoded${others}".
     "\r\nUser-Agent: Mozilla 4.0\r\nContent-length: ".strlen($query)."\r\nConnection: close\r\n\r\n$query";
   $h=fsockopen($host,80);
   fwrite($h,$post);
   for($a=0,$r='';!$a;){
       $b=fread($h,8192);
       $r.=$b;
       $a=(($b=='')?1:0);
   }
  fclose($h);
  return $r;
}

function get_be_infos($uv)
{
  $s = post("www.bankexam.fr/rechercher", "query=".$uv." UTBM");
  
  preg_match_all("/<a class=\"agoogle\" href=\"(.*)\">.*<\/a>/", $s, $truc);

  $link = $truc[1][0];

  $s = file_get_contents('http://www.bankexam.fr'. $link);

  preg_match_all("/<a class=\"agoogle\" href=\"(.*)\" title/",
		 $s, $truc);

  foreach ($truc[1] as $annales)
    $ret[] =  "<a href=\"http://www.bankexam.fr".$annales."\">".basename($annales)."</a>";

  preg_match_all("/\?page=([0-9]*)\"/", $s, $pages);

  $numpage = $pages[1][count($pages[1]) - 1];

  if ($numpage > 0)
    {
      for ($i = 2; $i <= $numpage; $i++)
	{
	  $s = file_get_contents('http://www.bankexam.fr'. $link."?page=".$i);
	  preg_match_all("/<a class=\"agoogle\" href=\"(.*)\" title/",
			 $s, $truc);

	  foreach ($truc[1] as $annales)
	    $ret[] = "<a href=\"http://www.bankexam.fr".$annales."\">".basename($annales) . "</a>";

	}
    }
  return $ret;
}

$query = $_REQUEST['q'];

$cts = new contents("Information sur les UVs","");
if ($query)
{
  $tab = get_be_infos($query);
  $itl = new itemlist("Annales chez Bankexam - $query", false, $tab);
  $cts->add_paragraph("<h4>Annales chez Bankexam - $query</h4>");
  $cts->add($itl);
}


$site->add_contents($cts);

$site->end_page();

?>
