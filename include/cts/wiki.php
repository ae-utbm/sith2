<?php

/** @file
 *
 *
 */
/* Copyright 2005,2006
 * - Julien Etelain < julien at pmad dot net >
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
 
/**
 * @defgroup display Affichage 
 */ 
 
/**
 * @defgroup display_cts Contents 
 * @ingroup display
 */ 
 

/** Conteneur de diffwiki
 * @ingroup display_cts
 */
class diffwiki extends stdcontents
{
  var $action;
  var $name;

  /** Initialise un formulaire
   * @param $name      Nom du formulaire
   * @param $action    Fichier sur le quel le formulaire sera envoyé
   * @param $row       Un table de "diff"
   * @param $method    Méthode à utiliser pour envoyer les données (post ou get)
   * @param $title     Titre du formulaire (facultatif)
   */
  function diffwiki ( $name, $action, $rows, $method = "post", $title = false )
  {
    $this->name = $name;
    $this->title = $title;
    if(!is_array($rows)||empty($rows))
      return;
    $this->buffer ="<form action=\"$action\" method=\"".strtolower($method)."\"";
    $this->buffer.=" name=\"frm_".$this->name."\" id=\"frm_".$this->name."\" >";
    $this->buffer.="<div class=\"form\">\n";
    $this->buffer .= "<div class=\"formrow\">";
    $this->buffer .= "<div class=\"formfield\">";
    $this->buffer .= "<input type=\"submit\" id=\"submit_t\" name=\"submit\" value=\"Voir les différences\" class=\"isubmit\" />";
    $this->buffer .= "</div></div>\n";
    $this->buffer.="<div class=\"formrow\">\n";
    $this->buffer.="<ul class=\"diff\" id=\"$name\">\n";
    $i=0;
    foreach ( $rows as $row )
    {
      $this->buffer.="<li>";
      $this->buffer.="<input type=\"radio\" name=\"rev_comp\" class=\"radiobox\" value=\"".$row['value']."\" id=\"__rev_comp_".$row['value']."\"";
      if($i==0)
        $this->buffer.=" style=\"visibility:hidden\" checked=\"checked\"";
      if($i==1)
        $this->buffer.=" checked=\"checked\"";
      $this->buffer.=" />&nbsp;";
      $this->buffer.="<input type=\"radio\" name=\"rev_orig\" class=\"radiobox\" value=\"".$row['value']."\" id=\"__rev_orig_".$row['value']."\"";
      if($i==0)
        $this->buffer.=" checked=\"checked\"";
      $this->buffer.=" />&nbsp;";
      $this->buffer.=$row['desc'];
      $this->buffer.="</li>\n";
      $i++;
    }
    $this->buffer.= "</ul>\n";
    $this->buffer.= "</div>\n";
    $this->buffer .= "<div class=\"formrow\">";
    $this->buffer .= "<div class=\"formfield\">";
    $this->buffer .= "<input type=\"submit\" id=\"submit_b\" name=\"submit\" value=\"Voir les différences\" class=\"isubmit\" />";
    $this->buffer .= "</div></div>\n";
    $this->buffer.= "<div class=\"clearboth\"></div>\n";
    $this->buffer.= "</div>\n";
    $this->buffer.= "</form>\n";
  }
}
class diff extends stdcontents
{
  function diff($revs)
  {
  }
}

?>
