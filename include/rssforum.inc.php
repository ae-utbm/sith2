<?
/* 
 * flux rss du forum
 *        
 * Copyright 2007
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr/
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

require_once($topdir."include/rss.inc.php");
require_once($topdir . "include/lib/dokusyntax.inc.php");
require_once($topdir . "include/lib/bbcode.inc.php");


class rssfeedforum extends rssfeed
{
  var $nb;
  var $db;

  function rssfeedforum(&$db, $nbmessage = 50)
  {
    $this->db = $db;
    if (intval($nbmessage) < 0)
      $nbmessage = 50;
    $this->nb = $nbmessage;

    $this->title = "Les " . $nbmessage . " messages du forum de l'AE";
    $this->pubUrl = "http://ae.utbm.fr/forum2/";
    $this->rssfeed();
  }

  function output_items()
  {
    $req = new requete ($this->db, "SELECT 
                                             `utilisateurs`.`alias_utl`
                                             , `frm_message`.`id_message`
                                             , `frm_message`.`id_sujet`
                                             , `frm_message`.`contenu_message`
                                             , `frm_message`.`date_message`
                                             , `frm_message`.`syntaxengine_message`
                                             , `frm_sujet`.`titre_sujet`
                                             , `frm_forum`.`titre_forum`
                                    FROM
                                             `frm_message`
                                    INNER JOIN
                                             `utilisateurs`
                                    ON
                                             `utilisateurs`.`id_utilisateur` = `frm_message`.`id_utilisateur`
                                    INNER JOIN
                                             `frm_sujet`
                                    ON
                                             `frm_sujet`.`id_sujet` = `frm_message`.`id_sujet`
                                    INNER JOIN
                                             `frm_forum`
                                    ON
                                             `frm_sujet`.`id_forum` = `frm_forum`.`id_forum`
                                    ORDER BY
                                             `frm_message`.`id_message`
                                    DESC
                                    LIMIT ".$this->nb);

    while ($res = $req->get_row())
      {
	echo "<item>\n";
	echo "\t<title>![CDATA[". $row["titre_sujet"] . ", par <b>".$row['alias_utl']."</b>]]</title>\n";
	echo "\t<link>".$this->pubUrl."/?id_message=".$row["id_message"]."#msg".$row['id_message']."</link>\n";
	
	if ($row['syntaxengine_message'] == 'doku')
	  $content = doku2xhtml($row['contenu_message']);

	elseif ($row['syntaxengine_message'] == 'bbcode')
	  $content = bbcode($row['contenu_message']);

	echo "\t<description><![CDATA[".$content."]]</description>\n";
	echo "\t<pubDate>".gmdate("D, j M Y G:i:s T",strtotime($row["date_message"]))."</pubDate>\n";
	echo "\t<guid>http://ae.utbm.fr/forum2/?id_sujet=".$row["id_sujet"]."</guid>\n";
	echo "</item>\n";	
	
      }

  }
}


?>