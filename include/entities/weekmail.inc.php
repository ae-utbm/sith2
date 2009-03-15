<?php
/* Copyright 2009
 * - Simon Lopez <simon DOT lopez AT ayolo DOT org >
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

require_once($topdir.'include/entities/files.inc.php');
require_once($topdir.'include/entities/asso.inc.php');
/**
 * @ingroup stdentity
 * @author Simon Lopez
 *
 */
class weekmail extends stdentity
{

  protected $id           = null;
  protected $date         = null;
  protected $title        = null;
  protected $introduction = null;
  protected $conclusion   = null;
  protected $statut       = 0;
  protected $config       = array('styles'=>array(),'header'=>null,'footer'=>null);

  public function load_by_id($id)
  {
  }

  private function _load($row)
  {
  }

  public function send ( )
  {
    if( !$this->is_valid() || $this->is_sent() )
      return false;
    $buffer='';
    $this->_render($buffer);
    // on envoie
    // si on arrive à envoyer, on change le statut
    $this->set_statut('sent');
    $req = new update($site->dbrw,
                        'weekmail',
                        array('intro'=>'','conclusion'=>'','content'=>mysql_real_escape_string($content)),
                        array('id_weekmail'=>$this->id,'statut'=>'sent');
  }

  public function set_statut($statut)
  {
    if(in_array($statut,array('open','prep','sent')))
    {
      $req = new update($site->dbrw,
                        'weekmail',
                        array('statut'=>$statut),
                        array('id_weekmail'=>$this->id);
      return $req->is_success();
    }
    else
      return false;
  }

  private function _styles()
  {
    $styles['alternate']  = 'align="center" style="font-family:Verdana, Arial, Helvetica, sans-serif;font-size:12px;"';
    $styles['title']      = 'style="background-color:#D50056;padding-left:40px;color:#FFFFFF;font-weight:bold;font-size:16px;font-family:Verdana, Arial, Helvetica, sans-serif;" height="29px"';
    $styles['intro']      = '';
    $styles['to']         = '';
    $styles['toc_item']   = 'style="color:#FFFFFF;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:12px;"';
    $styles['news_title'] = 'style="color:#D50056;font-weight:bold;padding-left:20px;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:16px;"';
    $styles['news0']      = 'valign="top" style="background-color:#E3EFCD"';
    $styles['news1']      = 'valign="top" style="background-color:#FFFFFF"';
    $styles['news_cts']   = '';
    $styles['conclusion'] = '';
    $this->config['styles']=$styles;
  }

  public function set_config($id,$type,$value)
  {
    $_value=explode("\n",str_replace("\r",'',trim($value)));
    $value='';
    foreach($_value as $val)
      $value.=' '.trim($val);
    $req = new update($this->dbrw,
                      'weekmail_config',
                      array('value'=>mysql_real_escape_string($value)),
                      array('id'=>$id,'type'=>$type));
  }

  public function get_config()
  {
    $req = new requete($this->db,
                       'SELECT id,type,value FROM weekmail_config');
    while(list($id,$type,$value))
    {
      if($type=='img')
      {
        if($id=='header')
          $this->config['header']=intval($value);
        elseif($id=='footer')
          $this->config['footer']=intval($value);
      }
      else
        $this->config['styles'][$id]=$value;
    }
  }

  private function _render(&$buffer)
  {
    $this->get_config();
    $this->_styles();//a virer
    $file = new dfile($this->db);
    $asso = new asso($this->db);
    $buffer = '<table width="600" border="0" cellspacing="0" cellpadding="0" align="center">';
    $buffer.= '<tr><td '.$this->styles['alternate'].'>';
    $buffer.= 'Pour visualiser la newsletter <a target="_blank" href="http://ae.utbm.fr/weekmail.php?id='.
              $this->id.'">cliquez ici</a><br/><br/></td></tr>';
    // header
    if(   $file->load_by_id($this->config['header'])
       && $file->is_valid()
       && $file->modere
       && $file->is_right(new utilisateur($this->db),DROIT_LECTURE)
      )
      $buffer.='<tr><td><img src="http://ae.utbm.fr/d.php?id_file='.$file->id.'"></td></tr>';

    //titre
    $buffer.='<tr><td '.$this->config['styles']['title'].'>[Weekmail AE] '.$this->titre.'</td></tr>';
    //intro
    $buffer.='<tr><td '.$this->config['styles']['intro'].'>'.$this->introduction.'</td></tr>';

    //photo semaine ?

    //sommaire
    if(   $file->load_by_id($this->sommaire)
       && $file->is_valid()
       && $file->modere
       && $file->is_right(new utilisateur($this->db),DROIT_LECTURE)
      )
      $buffer.='<tr><td><img src="http://ae.utbm.fr/d.php?id_file='.$file->id.'"></td></tr>';
    else
      $buffer.='<tr><td '.$this->config['styles']['toc'].'>Sommaire</td></tr>';
    $buffer.='<tr><td '.$this->config['styles']['toc'].'><ul>';
    $req = new requete($this->db,
                       'SELECT id_asso'.
                       ', titre'.
                       ', content'.
                       ', date'.
                       ' FROM weekmail_news'.
                       ' WHERE id_weekmail=\''.$this->id.'\''.
                       ' ORDER BY order,id_weeknews ASC';
    while(list($id_asso,$titre,$content,$date)=$req->get_row())
      $buffer.='<li '.$this->config['styles']['item'].'><strong>'.$titre.'</strong><br/>'.date("Y-m-d",strtotime($date)).'</li>';
    $buffer.='</ul></td></tr>';

    //news
    $req->go_first();
    $n=0;
    $first=true;
    while(list($id_asso,$titre,$content,$date)=$req->get_row())
    {
      $n = ($n+1)%2;
      $buffer.='<tr><td '.$this->config['styles']['news'.$n].'>';
      if($first && $first=false)
        $buffer.='<hr style="background-color:#8C8C8C;height:1px;border: 0;"/>';

      $img = false;
      if(   $asso->load_by_id($id_asso)
         && $asso->is_valid())
      {
        $img = "/var/img/logos/".$row['nom_unix_asso'].".icon.png";
        if ( file_exists("/var/www/ae/www/var/img/logos/".$row['nom_unix_asso'].".icon.png") )
          $img = "http://ae.utbm.fr/var/img/logos/".$row['nom_unix_asso'].".icon.png";
      }
      if($img)
        $buffer.='<div '.$this->config['styles']['news_title'].'><img src="'.$img.'" border="0" /> '.$titre.'</div><br/>';
      else
        $buffer.='<div '.$this->config['styles']['news_title'].'>'.$titre.'</div><br/>';
      $buffer.='<div '.$this->config['styles']['news_cts'].'>'.$content.'</div>';
      $buffer.='</td></tr>';
    }

    //conclusion
    $buffer.='<tr><td '.$this->config['styles']['conclusion'].'>'.$this->conclusion.'</td></tr>';
    if(   $file->load_by_id($this->config['footer'])
       && $file->is_valid()
       && $file->modere
       && $file->is_right(new utilisateur($this->db),DROIT_LECTURE)
      )
      $buffer.='<tr><td><img src="http://ae.utbm.fr/d.php?id_file='.$file->id.'"></td></tr>';


    $buffer.= '</table>';
  }




  public function is_valid( )
  {
    return (is_int($id) && $id>0)?true:false;
  }

  public function is_sent ( )
  {
    return ($this->is_valid() && $this->statut==1)?true:false;
  }

  private function _css_parser( )
  {
  }
}


?>
