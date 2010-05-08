<?php

/* Copyright 2010
 * - Mathieu Briand < briandmathieu AT hyprua DOT org >
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

define('FPDF_FONTPATH', $topdir . 'font/');

require_once($topdir . "include/lib/fpdf.inc.php");
require_once($topdir."include/entities/files.inc.php");

class pdfplanning_news extends FPDF
{

  var $xmargin;
  var $ymargin;

  var $positions;
  var $dimensions;

  function pdfplanning_news($db, $title, $days)
  {
    global $topdir;

    $this->FPDF("L");

    $this->xmargin = 15;
    $this->ymargin = 10;
    $this->espace = 8;
    $this->numdays = count($days);
    $this->db = $db;

    $larg = (297 - 2*$this->xmargin - ($this->numdays-1)*$this->espace)
          / $this->numdays;

    $this->positions = array('sem' => array(null,160));

    sort($days);
    $posx = $this->xmargin;
    foreach($days as $day)
    {
      if ($day != 'sem')
      {
        $this->positions[$day] = array($posx, 20);
        $posx += $larg + $this->espace;
      }
    }

    $this->dimensions = array(1 => array($larg,6),
                              2 => array($larg,6),
                              3 => array($larg,6),
                              4 => array($larg,6),
                              5 => array($larg,6),
                              6 => array($larg,6),
                              7 => array($larg,6),
                              'sem' => array(null,6));

    $this->colors = array ( 1 => array('r' => 255, 'g' => 0, 'b' => 0),
                            2 => array('r' => 255, 'g' => 255, 'b' => 0),
                            3 => array('r' => 0, 'g' => 255, 'b' => 0),
                            4 => array('r' => 0, 'g' => 255, 'b' => 255),
                            5 => array('r' => 0, 'g' => 0, 'b' => 255),
                            6 => array('r' => 255, 'g' => 0, 'b' => 255),
                            7 => array('r' => 255, 'g' => 127, 'b' => 127),
                            'sem' => array('r' => 127, 'g' => 127, 'b' => 255));

    $this->SetAutoPageBreak(false);

    $this->AddPage();

    $file = new dfile($this->db, $this->dbrw);
    $file->load_by_id(5418);
    $this->Image($file->get_real_filename(), $this->xmargin, $this->ymargin,
                297-2*$this->xmargin, 210-2*$this->ymargin, 'JPG');


    $this->SetFont('Arial','',24);
    $this->SetXY($this->xmargin, $this->ymargin);
    $this->Cell($this->w-($this->xmargin*2), $this->ymargin, utf8_decode($title), 0, 0, "C");

    $this->SetFont('Arial','',8);
  }

  function add_day($day, $textes)
  {
    if (! in_array($day, array(1, 2, 3, 4, 5, 6, 7, 'sem')))
      return;

    global $topdir;

    list($x, $y) = $this->positions[$day];
    list($w, $h) = $this->dimensions[$day];
    $colors = $this->colors[$day];

    if (($x == null) || ($w == null))
    {
      $max_w = 0;
      foreach($textes as $texte)
        $max_w = max($max_w, $this->GetStringWidth(utf8_decode($texte)));
      $w = $max_w + 10;
      $x = ($this->w - $max_w) / 2;
    }

    $this->SetXY($x, $y);
    $this->SetDrawColor($colors['r'], $colors['g'], $colors['b']);

    foreach($textes as $texte)
    {
      $this->Image($topdir."images/plannings/haut_".$day.".gif", null, null, $w);

      if ($texte[0] != '')
      {
        $this->SetFillColor(255);
        $this->MultiCell($w, $h, utf8_decode($texte[0]), 'LRTB', 'C', true);
      }

      $this->SetFillColor($colors['r'], $colors['g'], $colors['b']);
      $this->MultiCell($w, $h, utf8_decode($texte[1]), 'LRTB', '', true);
      $this->Image($topdir."images/plannings/bas_".$day.".gif", null, null, $w);
    }
  }

}
?>
