/**
 * Copyright 2008
 * - Manuel Vonthron  <manuel DOT vonthron AT acadis DOT org>
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

var edt = {
  add_uv_seance: function(id, type, semestre, calling){
    url = 'uv.php?action=add_seance&mode=popup&id='+id+"&type="+type+"&semestre"+semestre+"&calling="+calling;
    win = window.open(url);
  },

  disp_freq_choice: function(elemid, freq, uvid, type){
    e = document.getElementById(elemid);
    if(!e)  return;

    if(freq != 2) 
      e.innerHTML = "";
    else
      e.innerHTML = "<select name=\"freq_"+uvid+"_"+type+"\">  \
                      <option value=\"A\">Semaine A</option> \
                      <option value=\"B\">Semaine B</option> \
                     </select>";
  },
  
  /* pour l instant juste une redirection */
  add: function(){
    document.location.href="edt.php?action=new" ;
  },
  
  select_uv: function(optionelt){
  }
}
