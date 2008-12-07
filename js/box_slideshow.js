/* Copyright 2008
 * - Simon Lopez < simon dot lopez at ayolo dot org >
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
 * @file
 * Fonctions pour les slideshow des "box".
 *
 * **RAPPEL** Ce fichier est sous licence GNU GPL. Vous pouvez le ré-utiliser
 * sur votre site internet, mais il doit rester sous licence GNU GPL même si
 * vous le modifiez. Si vous ré-utilisez des sources de gateway.php, ces sources
 * étant sous la même licence, elles devront aussi rester sous GNU GPL. 
 * Pour plus d'information : http://www.gnu.org/
 *
 * @author Simon Lopez
 */

/*
 * @ingroup js
 */

function start_slideshow(cts,start_frame, end_frame, delay) {
  setTimeout(switch_slides(cts,start_frame,start_frame,end_frame, delay), delay);
}

function switch_slides(cts,frame, start_frame, end_frame, delay) {
  var cts1 = document.getElementById(cts+frame);
  if (frame == end_frame)
    frame = start_frame;
  else
    frame = frame + 1;
  var cts2 = document.getElementById(cts+frame);
  cts1.style.display='none';
  cts1.style.display='block';
  setTimeout(switch_slides(cts,frame, start_frame, end_frame, delay), delay);
}

