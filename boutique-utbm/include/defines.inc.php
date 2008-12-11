<?php

/** @file
 *
 * @brief définitions générales du systeme de comptoirs
 *        actions spécials à effectuer (paramétrables)
 *        lors d'achats d'articles (ajout, update, suppression
 *        dans la base ...)
 */

/* Copyright 2005
 * - Julien Etelain <julien CHEZ pmad POINT net>
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
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
 * @addtogroup comptoirs
 * @{
 */

/* Action de vente produit */

/* Ignore le stock */
define("ACTION_VSIMPLE",0);

/* Prend en compte le stock */
define("ACTION_VSTOCKLIM",1);

$ActionsProduits = array (
  ACTION_VSIMPLE => "Vente simple (pas de limitation de stock)",
  ACTION_VSTOCKLIM => "Vente avec limitation par le stock",
);

define("PAIE_CHEQUE",0);
define("PAIE_ESPECS",1);

$TypesPaiements = array (
  PAIE_CHEQUE => "Chèque",
  PAIE_ESPECS => "Espèces"
);

$TypesPaiementsFull = array (
  PAIE_CHEQUE => "Chèque",
  PAIE_ESPECS => "Espèces"
);

$Banques = array( 0 => "--",
       1 => "Société Générale",
       2 => "Banque Populaire",
       3 => "BNP",
       4 => "Caisse d'Epargne",
       5 => "CIC",
       6 => "Crédit Agricole",
       7 => "Crédit Mutuel",
       8 => "Crédit Lyonnais",
       9 => "La Poste",
       100 => "Autre");

define("ETAT_FACT_A_RETIRER",          0x04);
define("ETAT_FACT_A_RETIRER_PARTIEL",  0x08);


?>
