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

class add_uv_edt_box extends form
{
  public function __construct($uv, $sem=SEMESTER_NOW)
  {
    if( !($uv instanceof uv) )
      throw new Exception("Incorrect type");
    
    $this->form($uv->code, null, null, null, $uv->code." - ".$uv->intitule);
    
    $code = $uv->code;
    $this->buffer = "";    
    
    if(!$uv->extra_loaded)
      $uv->load_extra();
    $this->buffer .= "<p>Selon nos informations, les enseignements de cette UV
      sont composés de "
        .$uv->guide['c']."h de Cours, "
        .$uv->guide['td']."h de TD et "
        .$uv->guide['tp']."h de TP (*)</p>";
    
    $this->buffer .= $this->build_uv_choice($uv, $sem, GROUP_C);
    $this->buffer .= $this->build_uv_choice($uv, $sem, GROUP_TD);
    $this->buffer .= $this->build_uv_choice($uv, $sem, GROUP_TP);
    
    $this->buffer .= "<p><i>(*) Si certaines des informations concernant cette UV
      sont incorrectes (détails des séances...), vous pouvez les 
      <a href=\"#\">corriger ici.</a></i></p>";
  }
  
  private function build_uv_choice($uv, $sem, $type){
    global $_GROUP;
    
    if($uv->guide[ $_GROUP[$type]['short'] ]){
      $groups = $uv->get_groups($type, $sem);
      $divid = $uv->id."_".$type;
      $sel_id = "seance_".$uv->id."_".$_GROUP[$type]['short'];
      
      $buffer  = "<div class=\"formrow\">\n";
      $buffer .= "  <div class=\"formlabel\">".$_GROUP[$type]['long']." : </div>\n";
      $buffer .= "  <div class=\"formfield\">\n";
      $buffer .= "    <select name=\"$sel_id\" id=\"sel_id\">\n";
      $buffer .= "      <option value=\"none\">S&eacute;lectionnez votre s&eacute;ance</option>\n";
      foreach($groups as $group){
        $buffer .= "      <option value=\"".$group['id_groupe']."\" onclick=\"edt.disp_freq_choice('".$divid."', ".$group['freq'].", ".$uv->id.", ".$type.");\">"
                            .$_GROUP[$type]['long']." n°".$group['num_groupe']." du ".get_day($group['jour'])." de ".$group['debut']." &agrave; ".$group['fin']." en ".$group['salle']
                            ."</option>\n";
      }
      $buffer .= "      <option value=\"add\" onclick=\"edt.add_uv_seance(".$uv->id.", ".$type.", '".$sel_name."');\">Ajouter une s&eacute;ance manquante...</option>\n";
      $buffer .= "    </select>\n";
      $buffer .= "    <span id=\"".$divid."\"></span>\n";
      $buffer .= "  </div>\n";
      $buffer .= "</div>\n\n";
    }
    else
      $buffer = null;
      
    return $buffer;
  }
}

class add_edt_start_box extends stdcontents
{
  public function __construct($semestre=SEMESTER_NOW)
  {
    $this->title = "Ajoutez un nouvel emploi du temps   (Étape 1/2)";
    $this->buffer = "";
    
    $y = date('Y');
    $sem = array();
    for($i = $y-2; $i <= $y; $i++){
      $sem[] = array('val'=>'P'.$i, 'name'=>'Printemps '.$i);
      $sem[] = array('val'=>'A'.$i, 'name'=>'Automne '.$i);
    }
    sort_by_semester($sem, 'val');
    
    $this->buffer  = "<div class=\"formrow\">\n";
    $this->buffer .= "  <div class=\"formlabel\">Semestre concerné : </div>\n";
    $this->buffer .= "  <div class=\"formfield\">\n";
    $this->buffer .= "    <select name=\"semestre\">\n";
    foreach($sem as $s)
      $this->buffer .= "      <option value=\"".$s['val']."\">".$s['name']."</option>\n";
    $this->buffer .= "    </select>\n";
    $this->buffer .= "  </div>\n";
    $this->buffer .= "</div>\n\n";

    $this->buffer .= "UV disponibles : <br />";
    $this->build_uv_choice();
  }
  
  private function build_uv_choice(){
    global $site;
    $tab= array();
    foreach(uv::get_list($site->db) as $uv)
      $tab[] = array('value'=>$uv['id_uv'], 'title'=>$uv['code']." - ".$uv['intitule']);
      
    $this->add(new selectbox('uvlist', 'Choix des UV', $tab, 'edt.php', 'UV'));
  }
}

class add_seance_box extends stdcontents
{
  public function __construct($iduv, $type=null, $semestre=SEMESTER_NOW)
  {
    global $site;
    global $_GROUP;
    
    $uv = new uv($site->db, $site->dbrw);
    $uv->load_by_id($iduv);
    if(!$uv->is_valid())
      throw new Exception("Object not found : UV ".$iduv);
    
    $this->title = "Ajouter une séance de ".$uv->code;
    
    $frm = new form("seance_".$iduv, "");
    $frm->allow_only_one_usage();
    
    /* type de seance C/TD/TP (on vire THE) */
    $avail_type = array();
    foreach($_GROUP as $grp => $desc)
      if($grp != GROUP_THE)
        $avail_type[$grp] = $desc['long'];
    $frm->add_select_field("type", "Type", $avail_type, $type);
    if($type)
      $frm->add_info("Il y a déjà ".count($uv->get_groups($type, $semestre))." séances de ".$_GROUP[$type]['long']." enregistrées pour ".$semestre.".");
    
    /* semestre */
    $y = date('Y');
    $avail_sem = array();
    for($i = $y-2; $i <= $y; $i++){
      $avail_sem['P'.$i] = 'Printemps '.$i;
      $avail_sem['A'.$i] = 'Automne '.$i;
    }
    $frm->add_select_field("semestre", "Semestre", $avail_sem, $semestre);
    
    /* numéro du groupe */
    $frm->add_text_field("num", "N° du groupe", "", 4, false, true, "(Tel que figurant sur la feuille de l'UTBM)");
    
    /* jour */
    $avail_jour = array(
      1 => "Lundi",
      2 => "Mardi",
      3 => "Mercredi",
      4 => "Jeudi",
      5 => "Vendredi",
      6 => "Samedi",
      7 => "Dimanche ?!",
    );
    $frm->add_select_field("jour", "Jour", $avail_jour);
    
    /* heures */
    $min = array('00', '15', '30', '45'); 

    $subfrm = new subform("heures", "Heures");
    $subfrm->add_text_field("hdebut", "Début", "" ,false, 4);
    $subfrm->add_select_field("mdebut", ":", $min);
    $subfrm->add_text_field("hfin", "Fin", "" ,false, 4);
    $subfrm->add_select_field("mfin", ":", $min);
    $frm->add($subfrm, false, false, false, false, true);
    
    /**/
    $frm->add_time_field("heure", "test heure");
    /* salle */
    $frm->add_text_field("salle", "N° de la salle", "", 8);
    
    /* submit */
    $frm->add_submit("add", "Ajouter la séance");
    
    $this->buffer .= $frm->html_render();
  }
}

class uv_dept_table extends stdcontents
{
  public function __construct($uvlist)
  {
    $this->buffer = "";
    $this->buffer .= "<table class=\"uvlist\">\n";
    $this->buffer .= " <tr>\n";
    $i = 0;
    if(!empty($uvlist))
    foreach($uvlist as $uv)
    {
      $this->buffer .= "  <td><a href=\"./uv.php?id=".$uv['id_uv']."\">".$uv['code']."</a></td>\n";
      $i++;
      if($i == 15){
        $this->buffer .= "</tr><tr>\n"; 
        $i = 0; 
      }
    }
    $this->buffer .= "\n </tr>\n</table>\n";
  }
}
?>
