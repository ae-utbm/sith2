<?php
/* Copyright 2011
 * - Jérémie Laval < jeremie dot laval at gmail dot com >
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

$topdir="../";
require_once($topdir."include/site.inc.php");
require_once($topdir."include/mysql.inc.php");
require_once($topdir."include/entities/eticket.inc.php");
require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/pdf/eticket.inc.php");

$site = new site();

$site->allow_only_logged_users("matmatronch");

if ( isset($_REQUEST['id_utilisateur']) )
{
  $user = new utilisateur($site->db,$site->dbrw);
  $user->load_by_id($_REQUEST["id_utilisateur"]);

  if ( !$user->is_valid() )
    $site->error_not_found("matmatronch");

  if ( !($user->id==$site->user->id || $site->user->is_in_group("gestion_ae")) )
    $site->error_forbidden("matmatronch","private");
}
else
  $user = &$site->user;

if (!isset ($_REQUEST['id_ticket']) || !isset($_REQUEST['id_produit']) || !isset($_REQUEST['id_facture']))
    $site->error_not_found ('none', '/user/compteae.php');

$id_ticket = intval ($_REQUEST['id_ticket']);
$id_produit = intval ($_REQUEST['id_produit']);
$id_facture = intval ($_REQUEST['id_facture']);

$eticket = new eticket ($site->db, $site->dbrw);
if (!$eticket->load_by_id ($id_ticket) || $eticket->id_produit != $id_produit)
    $site->error_not_found ('none', '/user/compteae.php');

$file = new dfile($site->db, $site->dbrw);
$file->load_by_id ($eticket->banner);

$req = new requete ($site->db, 'SELECT quantite FROM cpt_vendu WHERE id_facture='.$id_facture.' AND id_produit='.$id_produit.' LIMIT 1');
$row = $req->get_row();
$quantite = intval($row['quantite']);

$code = $user->id . ' ' . $id_produit . ' ' . $quantite;
$hash = $eticket->compute_hash_for($code);
$code .= ' ' . $hash;

$eticket_pdf = new eticket_pdf ($file->get_real_filename ());
$eticket_pdf->renderize (array ('prenom' => $user->prenom,
                                'nom' => $user->nom,
                                'nickname' => $user->surnom,
                                'avatar' => $user->get_preview ()), $code);
$eticket_pdf->Output ('eticket_'.$id_ticket.'.pdf', 'D');

?>
