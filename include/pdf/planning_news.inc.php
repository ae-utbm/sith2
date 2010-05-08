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

class pdfplanning_news extends FPDF
{

  var $xmargin;
  var $ymargin;

  var $positions;
  var $dimensions;

  function pdfplanning_news($title)
  {
    global $topdir;

    $this->FPDF("L");

    $this->xmargin = 15;
    $this->ymargin = 10;

    $this->positions = array( 1 => array(15,20),
                              2 => array(54,20),
                              3 => array(93,20),
                              4 => array(132,20),
                              5 => array(171,20),
                              6 => array(210,20),
                              7 => array(249,20),
                              'sem' => array(null,160));

    $this->dimensions = array(1 => array(30,6),
                              2 => array(30,6),
                              3 => array(30,6),
                              4 => array(30,6),
                              5 => array(30,6),
                              6 => array(30,6),
                              7 => array(30,6),
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

    $this->SetFont('Arial','',24);
    $this->SetXY($this->xmargin, $this->ymargin);
    $this->Cell($this->w-($this->xmargin*2), $this->ymargin, utf8_decode($title), 0, 0, "C");

    $this->SetFont('Arial','',8);
  }

  function add_day($day, $textes)
  {
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
    $this->SetFillColor($colors['r'], $colors['g'], $colors['b']);
    $this->SetDrawColor($colors['r'], $colors['g'], $colors['b']);
    $this->MultiCell($w, $h, utf8_decode(implode("\n", $textes)), 'TB', '', true);
  }

}
?>
