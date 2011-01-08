<?php
/* Copyright 2009
 * - Simon Lopez < simon dot lopez at ayolo dot org >
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
require_once($topdir. "include/entities/weekmail.inc.php");
$site = new site ();
$weekmail = new weekmail($site->db);

if(
   (
    isset($_REQUEST['id_weekmail'])
    && $weekmail->load_by_id($_REQUEST['id_weekmail'])
    && $weekmail->is_sent()
   )
   || $weekmail->load_latest_sent()
  )
{
  header("Content-Type: text/html; charset=utf-8");
  echo str_replace('<html><body bgcolor="#333333" width="700px"><table bgcolor="#333333" width="700px">',
                   '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">'.
                   '<head>'.
                   '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'.
                   '<title>[weekmail] '.$weekmail->titre.'</title>'.
                   '</head>'.
                   '<body bgcolor="#333333"><table bgcolor="#333333" width="100%">',
                   $weekmail->rendu_html);
  exit();
}

$site->start_page ("accueil", "Weekmail");
$site->add_contents(new contents('Pas de weekmail.','Aucun weekmail n\'a été trouvé.'));
$site->end_page();

?>
