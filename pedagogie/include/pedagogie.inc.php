<?php
/**
 * Copyright 2008
 * - Manuel Vonthron  <manuel DOT vonthron AT acadis DOT org>
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

/**
 * les enumerations et constantes ci-dessous doivent respecter les
 * valeurs de leurs équivalents dans la BDD
 */

/* Resultat */
define("RESULT_A",      1);
define("RESULT_B",      2);
define("RESULT_C",      3);
define("RESULT_D",      4);
define("RESULT_E",      5);
define("RESULT_F",      6);
define("RESULT_FX",     7);
define("RESULT_ABS",    8);
define("RESULT_EQUIV",  9);


/* type de cours */
define("GROUP_C",  1);
define("GROUP_TD", 2);
define("GROUP_TP", 3);
define("GROUP_THE",4);

/* type d'UV */
define("UV_CS", 1);
define("UV_TM", 2);
define("UV_EC", 3);
define("UV_CG", 4);
define("UV_Ext",5);

/* type de cours */
define("SEMESTER_A",  1);
define("SEMESTER_B",  2);
define("SEMESTER_AB", 3);
define("SEMESTER_closed",4);


/* Etats */
define("STATE_VALID",   1);
define("STATE_PENDING", 2);
define("STATE_MODIFIED",3);

/* departements */
define("DPT_HUMA",  1);
define("DPT_TC",    2);
define("DPT_GI",    3);
define("DPT_GESC",  4);
define("DPT_IMAP",  5);
define("DPT_GMC",   6);
define("DPT_EDIM",  7);

$dpt_short = array(
  DPT_HUMA => "Humas",
  DPT_TC => "TC",
  DPT_GI => "GI",
  DPT_GESC => "GESC",
  DPT_IMAP => "IMAP",
  DPT_MC => "MC",
  DPT_EDIM => "EDIM"
  );

$dpt_long = array(
  DPT_HUMA => "Humanités",
  DPT_TC => "Tronc Commun",
  DPT_GI => "Informatique",
  DPT_GESC => "Génie Électrique et Systèmes de Commande",
  DPT_IMAP => "Ingénierie et Management de Process",
  DPT_MC => "Mécanique et Conception",
  DPT_EDIM => "Ergonomie, Design et Ingénierie Mécanique"
  );

/* definition du semestre actuel */
$m = date('n');
if($m > 7 || $m == 1) $s = 'A'; /* entre Aout et Janvier */
else  $s = 'P'                  /* entre Fevrier et Juillet */
define("SEMESTER_NOW", $s.date('Y'));

/**
 * Vérifie si le format de semestre est bien au format A2004
 * @param $value donnee a vérifiée
 * @return true/false suivant le résultat
 */
function check_semester_format($value){
  return preg_match('/^[A-Z]{2}[0-9]{2}$/');
}

/**
 * Tri d'un tableau par semestre A2005, P2008...
 * @param $data tableau a trier
 * @param $id_row indice de la colonne dans laquelle se trouve le semestre
 */
function sort_by_semester(&$data, $id_row){
  /* celui la il va etre coton */  
}

?>
