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

  function pdfplanning_news($db, $title)
  {
    global $topdir;

    $this->FPDF("L");

    $this->xmargin = 15;
    $this->ymargin = 10;
    $this->xmargin_b = 8;
    $this->ymargin_b = 5;
    $this->title_h = 20;
    $this->cell_h = 6;
    $this->espace = 4;
    $this->section_space = 10;
    $this->db = $db;
    $this->background_file = 5418;

    $this->colors = array ( 1 => array('r' => 255, 'g' => 0, 'b' => 0),
                            2 => array('r' => 255, 'g' => 255, 'b' => 0),
                            3 => array('r' => 0, 'g' => 255, 'b' => 0),
                            4 => array('r' => 0, 'g' => 255, 'b' => 255),
                            5 => array('r' => 0, 'g' => 0, 'b' => 255),
                            6 => array('r' => 255, 'g' => 0, 'b' => 255),
                            7 => array('r' => 255, 'g' => 127, 'b' => 127),
                            'sem' => array('r' => 127, 'g' => 127, 'b' => 255));

    $this->evenements = array();
    $this->reguliers = array();
    $this->semaine = array();

    $this->SetAutoPageBreak(false);
    $this->AddPage();

    $file = new dfile($this->db, $this->dbrw);
    $file->load_by_id($this->background_file);
    $this->Image($file->get_real_filename(), $this->xmargin, $this->ymargin,
                $this->w-$this->xmargin_b*2, $this->h-$this->ymargin_b*2,
                substr(strrchr($file->nom_fichier, '.'), 1));

    $this->SetFont('Arial','',24);
    $this->SetXY($this->xmargin, $this->ymargin);
    $this->Cell($this->w-($this->xmargin*2), $this->ymargin, utf8_decode($title), 0, 0, "C");

    $this->SetFont('Arial','',8);
  }

  function render()
  {
    $days = array_unique(array_merge(array_keys($this->evenements), array_keys($this->reguliers)));
    print_r($days);

    $numdays = count($days);

    $this->larg = ($this->w - 2*$this->xmargin - ($numdays-1)*$this->espace) / $numdays;

    $endpos = $self->render_days($this->reguliers, $this->ymargin + $this->title_h);
    $endpos = $self->render_days($this->evenements, $endpos + $this->section_space);
    $this->render_week($this->semaine, $endpos + $this->section_space);

  }

  function add_texte($day, $texte)
  {
    if ($texte[2] == 0)
      $this->semaine[] = $texte;
    elseif ($texte[2] == 1)
      $this->evenements[$day][] = $texte;
    elseif ($texte[2] == 2)
      $this->reguliers[$day][] = $texte;
  }

  function render_days($data, $ymargin)
  {
    global $topdir;
    $endpos = 0;

    $daynames = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi',
                      'Samedi', 'Dimanche');

    $x = $this->xmargin;
    $y = $ymargin;

    foreach($this->reguliers as $day => $textes)
    {
      $colors = $this->colors[$day];

      $this->SetXY($x, $y);
      $this->SetDrawColor($colors['r'], $colors['g'], $colors['b']);

      if (in_array($day, array(1, 2, 3, 4, 5, 6, 7)))
      {
        $this->Image($topdir."images/plannings/haut_".$day.".gif", null, null, $this->larg);
        $this->SetFillColor($colors['r'], $colors['g'], $colors['b']);
        $this->SetX($x);
        $this->MultiCell($w, $h, $daynames[$day], '1', 'C', true);
        $this->SetX($x);
        $this->Image($topdir."images/plannings/bas_".$day.".gif", null, null, $this->larg);
        $this->SetY($this->getY() + 3);
      }

      foreach($textes as $texte)
      {
        $this->SetX($x);
        $this->Image($topdir."images/plannings/haut_".$day.".gif", null, null, $this->larg);

        if ($texte[0] != '')
        {
          $this->SetFillColor(255);
          $this->SetX($x);
          $this->MultiCell($this->larg, $this->cell_h, utf8_decode($texte[0]), '1', 'C', true);
        }

        $this->SetFillColor($colors['r'], $colors['g'], $colors['b']);
        $this->SetX($x);
        $this->MultiCell($this->larg, $this->cell_h, utf8_decode($texte[1]), '1', 'C', true);
        $this->SetX($x);
        $this->Image($topdir."images/plannings/bas_".$day.".gif", null, null, $this->larg);
        $this->SetY($this->getY() + 3);
      }
      $x += $this->larg + $this->espace;
      $endpos = max($endpos + $this->getY());
    }
    return $endpos;
  }

  function render_week($data, $ymargin)
  {
    global $topdir;

    $y = $ymargin;

    $max_w = 0;
    foreach($this->semaine as $texte)
      $max_w = max($max_w, $this->GetStringWidth(utf8_decode($texte[1])));
    $w = $max_w + 10;
    $x = ($this->w - $max_w) / 2;

    foreach($this->semaine as $texte)
    {
      $this->SetX($x);
      $this->Image($topdir."images/plannings/haut_sem.gif", null, null, $w);

      if ($texte[0] != '')
      {
        $this->SetFillColor(255);
        $this->SetX($x);
        $this->MultiCell($w, $this->cell_h, utf8_decode($texte[0]), '1', 'C', true);
      }

      $this->SetFillColor($colors['r'], $colors['g'], $colors['b']);
      $this->SetX($x);
      $this->MultiCell($w, $this->cell_h, utf8_decode($texte[1]), '1', 'C', true);
      $this->SetX($x);
      $this->Image($topdir."images/plannings/bas_sem.gif", null, null, $w);
      $this->SetY($this->getY() + 3);
    }
    return $this->getY();
  }


}
?>
