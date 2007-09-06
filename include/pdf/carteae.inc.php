<?php
/* Copyright 2006
 * - Julien Etelian <julien CHEZ pmad POINT net>
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

require_once($topdir . "include/lib/barcodefpdf.inc.php");
class pdfcarteae extends FPDF
{
	var $img_front;
	var $img_back;
	var $width;
	var $height;
	var $xmargin;
	var $ymargin;
	var $pos;
	var $npp;
	var $npl;
	var $tmpFiles = array();


        function Image($file,$x,$y,$w=0,$h=0,$type='',$link='', $isMask=false, $maskImg=0)
        {
          //Put an image on the page
          if(!isset($this->images[$file]))
          {
            //First use of image, get info
            if($type=='')
            {
              $pos=strrpos($file,'.');
              if(!$pos)
                $this->Error('Image file has no extension and no type was specified: '.$file);
              $type=substr($file,$pos+1);
            }
            $type=strtolower($type);
            $mqr=get_magic_quotes_runtime();
            set_magic_quotes_runtime(0);
            if($type=='jpg' || $type=='jpeg')
              $info=$this->_parsejpg($file);
            elseif($type=='png'){
              $info=$this->_parsepng($file);
              if ($info=='alpha') return $this->ImagePngWithAlpha($file,$x,$y,$w,$h,$link);
            }
            else
            {
              //Allow for additional formats
              $mtd='_parse'.$type;
              if(!method_exists($this,$mtd))
                $this->Error('Unsupported image type: '.$type);
              $info=$this->$mtd($file);
            }
            set_magic_quotes_runtime($mqr);
        
            if ($isMask){
              $info['cs']="DeviceGray"; // try to force grayscale (instead of indexed)
            }
            $info['i']=count($this->images)+1;
            if ($maskImg>0) $info['masked'] = $maskImg;###
            $this->images[$file]=$info;
          }
          else
            $info=$this->images[$file];
          //Automatic width and height calculation if needed
          if($w==0 && $h==0)
          {
            //Put image at 72 dpi
            $w=$info['w']/$this->k;
            $h=$info['h']/$this->k;
          }
          if($w==0)
            $w=$h*$info['w']/$info['h'];
          if($h==0)
            $h=$w*$info['h']/$info['w'];
        
        if ($isMask) $x = $this->fwPt + 10; // embed hidden, ouside the canvas  
        $this->_out(sprintf('q %.2f 0 0 %.2f %.2f %.2f cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']));
        if($link)
          $this->Link($x,$y,$w,$h,$link);
        
        return $info['i'];
      }

      // needs GD 2.x extension
      // pixel-wise operation, not very fast
     function ImagePngWithAlpha($file,$x,$y,$w=0,$h=0,$link='')
{
    $tmp_alpha = tempnam('.', 'mska');
    $this->tmpFiles[] = $tmp_alpha;
    $tmp_plain = tempnam('.', 'mskp');
    $this->tmpFiles[] = $tmp_plain;
    
    list($wpx, $hpx) = getimagesize($file);
    $img = imagecreatefrompng($file);
    $alpha_img = imagecreate( $wpx, $hpx );
    
    // generate gray scale pallete
    for($c=0;$c<256;$c++) ImageColorAllocate($alpha_img, $c, $c, $c);
    
    // extract alpha channel
    $xpx=0;
    while ($xpx<$wpx){
        $ypx = 0;
        while ($ypx<$hpx){
            $color_index = imagecolorat($img, $xpx, $ypx);
            $col = imagecolorsforindex($img, $color_index);
            imagesetpixel($alpha_img, $xpx, $ypx, $this->_gamma( (127-$col['alpha'])*255/127)  );
        ++$ypx;
        }
        ++$xpx;
    }

    imagepng($alpha_img, $tmp_alpha);
    imagedestroy($alpha_img);
    
    // extract image without alpha channel
    $plain_img = imagecreatetruecolor ( $wpx, $hpx );
    imagecopy ($plain_img, $img, 0, 0, 0, 0, $wpx, $hpx );
    imagepng($plain_img, $tmp_plain);
    imagedestroy($plain_img);
    
    //first embed mask image (w, h, x, will be ignored)
    $maskImg = $this->Image($tmp_alpha, 0,0,0,0, 'PNG', '', true);
    
    //embed image, masked with previously embedded mask
    $this->Image($tmp_plain,$x,$y,$w,$h,'PNG',$link, false, $maskImg);
}

function Close()
{
    parent::Close();
    // clean up tmp files
    foreach($this->tmpFiles as $tmp) @unlink($tmp);
}

/*******************************************************************************
*                                                                              *
*                               Private methods                                *
*                                                                              *
*******************************************************************************/
function _putimages()
{
    $filter=($this->compress) ? '/Filter /FlateDecode ' : '';
    reset($this->images);
    while(list($file,$info)=each($this->images))
    {
        $this->_newobj();
        $this->images[$file]['n']=$this->n;
        $this->_out('<</Type /XObject');
        $this->_out('/Subtype /Image');
        $this->_out('/Width '.$info['w']);
        $this->_out('/Height '.$info['h']);
        
        if (isset($info["masked"])) $this->_out('/SMask '.($this->n-1).' 0 R'); ###
        
        if($info['cs']=='Indexed')
            $this->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]');
        else
        {
            $this->_out('/ColorSpace /'.$info['cs']);
            if($info['cs']=='DeviceCMYK')
                $this->_out('/Decode [1 0 1 0 1 0 1 0]');
        }
        $this->_out('/BitsPerComponent '.$info['bpc']);
        if(isset($info['f']))
            $this->_out('/Filter /'.$info['f']);
        if(isset($info['parms']))
            $this->_out($info['parms']);
        if(isset($info['trns']) && is_array($info['trns']))
        {
            $trns='';
            for($i=0;$i<count($info['trns']);$i++)
                $trns.=$info['trns'][$i].' '.$info['trns'][$i].' ';
            $this->_out('/Mask ['.$trns.']');
        }
        $this->_out('/Length '.strlen($info['data']).'>>');
        $this->_putstream($info['data']);
        unset($this->images[$file]['data']);
        $this->_out('endobj');
        //Palette
        if($info['cs']=='Indexed')
        {
            $this->_newobj();
            $pal=($this->compress) ? gzcompress($info['pal']) : $info['pal'];
            $this->_out('<<'.$filter.'/Length '.strlen($pal).'>>');
            $this->_putstream($pal);
            $this->_out('endobj');
        }
    }
}

// GD seems to use a different gamma, this method is used to correct it again
function _gamma($v){
    return pow ($v/255, 2.2) * 255;
}

// this method overwriing the original version is only needed to make the Image method support PNGs with alpha channels.
// if you only use the ImagePngWithAlpha method for such PNGs, you can remove it from this script.
function _parsepng($file)
{
    //Extract info from a PNG file
    $f=@fopen($file,'rb');
    if(!$f)
        $this->Error('Can\'t open image file: '.$file);
    //Check signature
    if(@fread($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
        $this->Error('Not a PNG file: '.$file);
    //Read header chunk
    fread($f,4);
    if(@fread($f,4)!='IHDR')
        $this->Error('Incorrect PNG file: '.$file);
    $w=$this->_freadint($f);
    $h=$this->_freadint($f);
    $bpc=ord(@fread($f,1));
    if($bpc>8)
        $this->Error('16-bit depth not supported: '.$file);
    $ct=ord(@fread($f,1));
    if($ct==0)
        $colspace='DeviceGray';
    elseif($ct==2)
        $colspace='DeviceRGB';
    elseif($ct==3)
        $colspace='Indexed';
    else {
        fclose($f);      // the only changes are
        return 'alpha';  // made in those 2 lines
    }
    if(ord(@fread($f,1))!=0)
        $this->Error('Unknown compression method: '.$file);
    if(ord(@fread($f,1))!=0)
        $this->Error('Unknown filter method: '.$file);
    if(ord(@fread($f,1))!=0)
        $this->Error('Interlacing not supported: '.$file);
    @fread($f,4);
    $parms='/DecodeParms <</Predictor 15 /Colors '.($ct==2 ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w.'>>';
    //Scan chunks looking for palette, transparency and image data
    $pal='';
    $trns='';
    $data='';
    do
    {
        $n=$this->_freadint($f);
        $type=@fread($f,4);
        if($type=='PLTE')
        {
            //Read palette
            $pal=@fread($f,$n);
            @fread($f,4);
        }
        elseif($type=='tRNS')
        {
            //Read transparency info
            $t=@fread($f,$n);
            if($ct==0)
                $trns=array(ord(substr($t,1,1)));
            elseif($ct==2)
                $trns=array(ord(substr($t,1,1)),ord(substr($t,3,1)),ord(substr($t,5,1)));
            else
            {
                $pos=strpos($t,chr(0));
                if($pos!==false)
                    $trns=array($pos);
            }
            @fread($f,4);
        }
        elseif($type=='IDAT')
        {
            //Read image data block
            $data.=@fread($f,$n);
            @fread($f,4);
        }
        elseif($type=='IEND')
            break;
        else
            @fread($f,$n+4);
    }
    while($n);
    if($colspace=='Indexed' && empty($pal))
        $this->Error('Missing palette in '.$file);
    @fclose($f);
    return array('w'=>$w,'h'=>$h,'cs'=>$colspace,'bpc'=>$bpc,'f'=>'FlateDecode','parms'=>$parms,'pal'=>$pal,'trns'=>$trns,'data'=>$data);
}


	function pdfcarteae()
	{
		global $topdir;
		
		
		$this->FPDF();
		
		$this->width = 80; // Largeur d'une carte
		$this->height = 50; // Hauteur d'une carte
		$this->xmargin = 25; // Marge X
		$this->ymargin = 25; // Marge Y
		$this->npp = 10; // Nombre par page
		$this->npl=2; // Nombre par ligne
		
		$this->img_front = $topdir."images/carteae/front-2007.png";
		$this->img_back = $topdir."images/carteae/back-2006.png";
		
		/* ATTENTION 
		 * - l'égalité suivante doit être respectée :
		 * 	210=$this->npl*$this->width+2*$this->xmargin
		 * 	sinon les cartes seront mal aligné entre le recto et le verso
		 * 
		 * - l'égalité suivante doit être respectée :
		 * 	290<$this->height*$this->npp/$this->npl+$this->ymargin
		 *  sinon les cartes ne tiendrons pas dans la page
		 */ 
		

		$this->pos = array (
			"photo" => array ("x"=>5,"y"=>5,"w"=>27.8,"h"=>36,8),
			"cbar" => array ("x"=>10.6,"y"=>3.8,"w"=>67,"h"=>25),
			"front" => 
				array (
					"nom" => array ("x"=>51,"y"=>21,"w"=>27,"h"=>4.5),
					"prenom" => array ("x"=>51,"y"=>25.5,"w"=>27,"h"=>4.5),
					"surnom" => array ("x"=>51,"y"=>30,"w"=>27,"h"=>4.5),
					"semestres" => array ("x"=>51,"y"=>34.5,"w"=>27,"h"=>4.5)
				)
			);	
		
		$this->SetAutoPageBreak(false);
		
		
	}
	
	function render_front ( $x, $y, $infos )
	{
		global $topdir;

		//$this->Image($this->img_front,$x,$y,$this->width,$this->height);

		$src = "../var/img/matmatronch/".$infos['id'].".identity.jpg";

		$this->Image($src,
				$x+$this->pos['photo']['x'],
				$y+$this->pos['photo']['y'],
				$this->pos['photo']['w'],
				$this->pos['photo']['h']);

                $this->Image($this->img_front,$x,$y,$this->width,$this->height);

		$this->SetFont('Arial','',8);
		
		foreach ( $this->pos['front'] as $name => $pos )
		{
			$this->SetXY($x+$pos['x'],$y+$pos['y']);
			$this->Cell($pos['w'],$pos['h'],utf8_decode($infos[$name]));
		}
	}
	
	function render_back ( $x, $y, $infos )
	{
		$this->Image($this->img_back,$x,$y,$this->width,$this->height);

		$cbar = new PDF_C128AObject($this->pos['cbar']['w'], $this->pos['cbar']['h'],  
						BCS_ALIGN_CENTER | BCS_DRAW_TEXT, 
						$infos['cbar'], 
						&$this, 
						$x+$this->pos['cbar']['x'], 
						$y+$this->pos['cbar']['y']
						);
						
		$cbar->DrawObject(0.25);
	
	}
	
	function render_page ( $users )
	{
		$n = count($users);
		
		$this->AddPage();
		for($i=0;$i<$n;$i++)
		{
			$x = $this->xmargin+($i % $this->npl)*$this->width;
			$y = $this->ymargin+intval($i/$this->npl)*$this->height;
			
			$this->render_front($x,$y,$users[$i]);
		}
		
		$this->AddPage();
		for($i=0;$i<$n;$i++)
		{
			$x = $this->xmargin+($this->npl-1-($i % $this->npl))*$this->width;
			$y = $this->ymargin+intval($i/$this->npl)*$this->height;
			
			$this->render_back($x,$y,$users[$i]);
		}
	}
	
	function semestre ( $time )
	{
		$d = date("d",$time);
		$m = date("m",$time);
		
		if ( $m <= 2 )
			return "A".sprintf("%02d",(date("y",$time)-1));	
			
		if ( $m > 8 )
			return "A".date("y",$time);	
			
		return "P".date("y",$time);
	}
	
	
	function render ( $req )
	{
		global $topdir;
		$i=0;
		$users = array();
		
		$acc=utf8_decode('ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËéèêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ');
    		$noacc='AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn';
    		
		while ( $row= $req->get_row())
		{

			$users[$i]["id"] = $row["id_utilisateur"];
			$users[$i]["nom"] = $row["nom_utl"];
			$users[$i]["prenom"] = $row["prenom_utl"];
			$users[$i]["surnom"] = $row["surnom_utbm"];
			$fsem = $this->semestre(strtotime($row["date_fin_cotis"]));
			$sem = $this->semestre(time());
			
			if ( $fsem != $sem )
				$users[$i]["semestres"] = $sem."-".$fsem;
			else
				$users[$i]["semestres"] = $fsem;

			$users[$i]["cbar"] = $row["id_carte_ae"]." ".substr($row["prenom_utl"],0,6).".".substr($row["nom_utl"],0,6);
			$users[$i]["cbar"] = utf8_encode(strtr(utf8_decode($users[$i]["cbar"]), $acc, $noacc));
			$users[$i]["cbar"] = strtoupper(str_replace("-", "", $users[$i]["cbar"]));
			
			$i++;
			
			if ( $i == $this->npp )
			{
				$this->render_page($users);
				
				$users = array();
				$i = 0;	
			}

		}
			
		if ( $i != 0 )
			$this->render_page($users);

	}
	
}




?>
