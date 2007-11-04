<?php
$topdir = "../";

require_once($topdir. "include/site.inc.php");

$site = new site();

define('FPDF_FONTPATH', $topdir . 'font/');
require_once($topdir . "include/lib/barcodefpdf.inc.php");


class etoiles extends FPDF
{
	
	var $width;
	var $height;
	var $xmargin;
	var $ymargin;
	var $pos;
	var $npp;
	var $npl;
	
	var $i;
	
	function etoiles()
	{
		global $topdir;
		
		$this->FPDF();

		$this->width = 18; // Largeur d'une carte
		$this->height = 18; // Hauteur d'une carte
		$this->xmargin = 20; // Marge X
		$this->ymargin = 20; // Marge Y
		$this->npp = 4; // Nombre par page
		$this->npl = 2; // Nombre par ligne
		$this->fontsize = 20; // Nombre par ligne
		$this->SetAutoPageBreak(false);
		
		$this->i = 0;
	
		
	}
	
	function add ( $name )
	{
		if ( $this->i % $this->npp == 0 )
		{
			$this->AddPage();
			$this->i = 0;
		}

		$x = ($this->i % $this->npl) * $this->width         + $this->xmargin;
		$y = intval ($this->i / $this->npl) * $this->height + $this->ymargin;
			
			
		$this->SetFont('Arial','',$this->fontsize);
		$this->SetXY($x,$y+(($this->height-$this->fontsize)/2));
		$this->Cell($this->width,$this->fontsize,utf8_decode($name),0,0,'C');

	  $this->Image("etoile.jpg",$x,$y,$this->width,$this->height);
			
		$this->i++;

	}
}

if ( isset($_REQUEST["data"]) )
{

  $etoiles = new etoiles();
  
  $lines= explode("\n",$_REQUEST["data"]);
  
  foreach ( $lines as $line )
  {
    $etoiles->add($line);
  }
  
  $etoiles->Output();

  exit();
}

$site->start_page("services","Etoiles");

$cts = new contents("Etoiles");


$frm = new form("process","etoiles_gala.php");
$frm->add_text_area("data","Données");
$frm->add_submit("valide","C'est parti");
$cts->add($frm);


$site->add_contents($cts);
$site->end_page();

?>