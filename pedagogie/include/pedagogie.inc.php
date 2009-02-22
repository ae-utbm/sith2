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
 * @todo remplacer toutes ces constates par une vraie class enum
 */

/* Resultat 
 * @var RESULT_ */
define("RESULT_A",      1);
define("RESULT_B",      2);
define("RESULT_C",      3);
define("RESULT_D",      4);
define("RESULT_E",      5);
define("RESULT_F",      6);
define("RESULT_FX",     7);
define("RESULT_ABS",    8);
define("RESULT_EQUIV",  9);


/* type de cours 
 * @var GROUP_ */
define("GROUP_C",  1);
define("GROUP_TD", 2);
define("GROUP_TP", 3);
define("GROUP_THE",4);

/* type d'UV 
 * @var TYPE_ */
define("TYPE_CS", 1);
define("TYPE_TM", 2);
define("TYPE_EC", 3);
define("TYPE_CG", 4);
define("TYPE_Ext",5);

/* semestres d ouverture (ou pas)
 * @var SEMESTER_ */
define("SEMESTER_A",  1);
define("SEMESTER_P",  2);
define("SEMESTER_AP", 3);
define("SEMESTER_closed",4);


/* Etats 
 * @var STATE_  */
define("STATE_VALID",   1);
define("STATE_PENDING", 2);
define("STATE_MODIFIED",3);

/* Types de cursus
 * @var CURSUS_  */
define("CURSUS_FILIERE",1);
define("CURSUS_MINEUR", 2);
define("CURSUS_AUTRE",  3);

/* departements 
 * @var DPT_  */
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

/* definition du semestre actuel 
 * @var SEMESTRE_NOW  */
$m = date('n');
if($m > 7 || $m == 1) $s = 'A'; /* entre Aout et Janvier */
else  $s = 'P';                 /* entre Fevrier et Juillet */
define("SEMESTER_NOW", $s.date('Y'));

/**
 * Vérifie si le format de semestre est bien au format A2004
 * @param $value donnee a vérifier
 * @return true/false suivant le resultat
 */
function check_semester_format(&$value){
  $value = strtoupper($value);
  return preg_match('/^[AP][0-9]{4}$/', $value);
}

/**
 * LO45 et cie
 */
function check_uv_format(&$value){
  $value = strtoupper($value);
  return preg_match('/^[A-Z]{2}[0-9]{2}$/', $value);
}

/**
 * Tri d'un tableau par semestre A2005, P2008...
 * @param $data tableau a trier
 * @param $id_row indice de la colonne dans laquelle se trouve le semestre
 */
function sort_by_semester(&$data, $id_row){
  $GLOBALS['id_row_sort'] = $id_row;
  usort($data, '__semester_comp');
}

/**
 * comparaisons entre deux lignes pour le tri par semestre
 * appelee par usort
 * @see sort_by_semester
 */
function __semester_comp($row1, $row2){
  global $id_row_sort;
  preg_match("/^([AP])([0-9]{4})$/", $row1[$id_row_sort], $s1);
  preg_match("/^([AP])([0-9]{4})$/", $row2[$id_row_sort], $s2);

  /* comparaisons sur les annees, puis si egalite sur les A/P */
  if($s1[2] < $s2[2])
    return -1;
  else if ($s1[2] > $s2[2])
    return 1;
  else{
    /* si < : A pour 1 et P pour 2 */
    if($s1[1] < $s2[1])
      return 1;
     else 
      return -1;
  }
}

?>
