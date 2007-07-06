<?php

/*
 * test de la classe emploi du temps
 *
 */
$topdir = "../";

include($topdir. "include/site.inc.php");

require_once ($topdir . "include/entities/edt.inc.php");
require_once ($topdir . "include/cts/edt_img.inc.php");


$site = new site();

$edt = new edt($site->db, $site->dbrw);

$site->start_page("services", "Emploi du temps");

if (!$site->user->utbm)
{
  error_403("reservedutbm");
}
if (!$site->user->is_valid())
{
  error_403();
}
/** STEP 3 : En fonction des formats horaires, on pond un formulaire de renseignement sur les séances */
if ($_REQUEST['step'] == 3)
{

  $site->add_contents(new contents("DEBUG", print_r($_REQUEST, true)));

  $site->end_page();
  exit();
}


/** STEP 2 : En fonction des formats horaires, on pond un formulaire de renseignement sur les séances */

if ($_REQUEST['step'] == 2)
{
  $cts = new contents("Renseignement sur les séances", "");
  if (count($_SESSION['edu_uv_subscr']) == 0)
    $cts->add_paragraph("Vous n'avez pas sélectionné d'UV à l'étape 1. Merci de recommencer cette étape ".
			"avant de remplir les différentes informations sur les formats horaires");
  else
    {
      $lst = new itemlist("Liste des UVs", false, $_SESSION['edu_uv_subscr']);
      $cts->add($lst);

      $frm = new form('frm', 'index.php?step=3');

      $frm->puts(
"<script language=\"javascript\">
function togglesellist(obj, uv, type)
{
  sellist = document.getElementsByName('uv[' +uv+ '][' +type+ '][semaine]')[0];

  if (obj.selectedIndex == '1')
  {
    sellist.style.display = 'none';
  }
  else
  {
   s ellist.style.display = 'block';
  }
}




</script>\n");

      global $jour;

      /* horaires */
      for ($i = 0; $i < 24; $i++)
	{
	  $tmp = sprintf("%02d", $i);
	  $hours[$tmp] = $tmp; 
	}
	      
      for ($i = 0; $i < 60; $i++)
	{
	  $tmp = sprintf("%02d", $i);
	  $minut[$tmp] = $tmp;
	}


      foreach($_SESSION['edu_uv_subscr'] as $uv)
	{
	  $frm->puts("<h1>$uv</h1>");
	  $req = new requete($site->db, "SELECT `cours_uv`, `td_uv`, `tp_uv`, `id_uv` 
                                         FROM   `edu_uv` 
                                         WHERE `code_uv` = '".mysql_real_escape_string($uv) . "'");
 	  $rs = $req->get_row();
	  $c    = $rs['cours_uv'];
	  $td   = $rs['td_uv'];
	  $tp   = $rs['tp_uv'];
	  $iduv = $rs['id_uv'];

	  if (($c==0) && ($td == 0) && ($tp == 0))
	    $frm->puts("<b>UV hors emploi du temps. En conséquence, elle n'apparaitra pas sur l'Emploi du temps.</b>");

	  /* cours */
	  if ($c == 1)
	    {
	      $frm->puts("<h2>Cours</h2>");

	      $req = new requete($site->db, 
				"SELECT `id_uv_groupe`, `numero_grp`, `jour_grp`, `heure_debut_grp`, `heure_fin_grp`
                                 FROM `edu_uv_groupe`
                                 WHERE `id_uv` = $iduv AND `type_grp` = 'C'");

	      if ($req->lines <= 0)
		$frm->puts("<p>Aucun groupe de cours connu pour cette UV. Vous êtes donc amené à ".
			   "en renseigner les caractéristiques<br/></p>");
	      else
		{
		  $sccours = array(-1 => "--");
		  while ($rs = $req->get_row())
		    $sccours[$rs['id_uv_groupe']] = 'Cours N°'.$rs['numero_grp']." du ". 
		      $jour[$rs['jour_grp']] . " de ".$rs['heure_debut_grp']." à ".$rs['heure_fin_grp'];;
		  $frm->add_select_field($uv.'-C', 'Séances de cours connues', $sccours);
		}
	      add_seance_form($frm, $uv, 'C');      
	    }

	  /* td */
	  if ($td == 1)
	    {
	      $frm->puts("<h2>TD</h2>");

	      $req = new requete($site->db, 
				"SELECT `id_uv_groupe`, `numero_grp`,  `jour_grp`, `heure_debut_grp`, `heure_fin_grp`
                                 FROM `edu_uv_groupe`
                                 WHERE `id_uv` = $iduv AND `type_grp` = 'TD'");
	      if ($req->lines <= 0)
		$frm->puts("<p>Aucun groupe de TD connu pour cette UV. Vous êtes donc amené à ".
				    "en renseigner les caractéristiques<br/></p>");
	      else
		{
		  $sctd = array(-1 => "--");
		  while ($rs = $req->get_row())
		    $sctd[$rs['id_uv_groupe']] = 'TD N°'.$rs['numero_grp'] . " du ". 
		      $jour[$rs['jour_grp']] . " de ".$rs['heure_debut_grp']." à ".$rs['heure_fin_grp'];
		  $frm->add_select_field($uv.'-TD', 'Séances de TD connues', $sctd);

		}
	      add_seance_form($frm, $uv, 'TD');	      
	    }


	  /* tp */
	  if ($tp == 1)
	    {
	      $frm->puts("<h2>TP</h2>");
	      $req = new requete($site->db, 
				"SELECT `id_uv_groupe`, `numero_grp`,  `jour_grp`, `heure_debut_grp`, `heure_fin_grp`
                                 FROM `edu_uv_groupe`
                                 WHERE `id_uv` = $iduv AND `type_grp` = 'TP'");
	      if ($req->lines <= 0)
		$frm->puts("<p>Aucun groupe de TP connu pour cette UV. Vous êtes donc amené à ".
			       "en renseigner les caractéristiques<br/></p>");
	      else
		{
		  $sctp = array(-1 => "--");
		  while ($rs = $req->get_row())
		    $sctp[$rs['id_uv_groupe']] = 'TP N°'.$rs['numero_grp']. " du ". 
		      $jour[$rs['jour_grp']] . " de ".$rs['heure_debut_grp']." à ".$rs['heure_fin_grp'];
		  $frm->add_select_field($uv.'-TP', 'Séances de TP connues', $sctp);

		}
	      add_seance_form($frm, $uv, 'TP');
	
	    }
	  $frm->puts("<br/>");
	} // fin foreach
      $frm->add_submit("step2_sbmt", "Envoyer");
      $cts->add($frm);
    } // else



  $site->add_contents($cts);

  $site->end_page();
  exit();
}

/** fonction affichant un formulaire de saisie */
function add_seance_form($formcts, $uv, $type)
{
  $formcts->puts("<h3>Ajout d'une séance horaire</h3>");

  /* numéro groupe de TP */
  $formcts->add_text_field("uv[$uv][$type][numgrp]",
			   'Numéro de groupe',
			   '', false, 1);
  /* jour */
  global $jour;
  $formcts->add_select_field("uv[$uv][$type][jour]",
			     'jour',
			     $jour);
  
  /* horaires debut / fin */
  global $hours, $minut;
  $formcts->add_select_field("uv[$uv][$type][hdeb]",
			     'Heure de début', $hours);
  
  $formcts->add_select_field("uv[$uv][$type][mdeb]",
			     'Minutes de début', $minut);
  

  $formcts->add_select_field("uv[$uv][$type][hfin]",
			     'Heure de fin', $hours);
  
  $formcts->add_select_field("uv[$uv][$type][mfin]",
			     'Minutes de fin', $minut);
  
  $formcts->add_select_field("uv[$uv][$type][freq]",
			     'Fréquence',
			     array("0" => "--",
                                   "1" => "Hebdomadaire",
				   "2" => "Bimensuelle"),
			     false,
			     "",
			     false,
			     true,
			     "javascript:togglesellist(this, '".$uv."', '".$type."')");
  
  $formcts->add_select_field("uv[$uv][$type][semaine]",
			     'Semaine',
			     array("A" => "Semaine A",
				   "B" => "Semaine B"));
}



/*** STEP 1 : étape initiale, choix des uvs, ajout et modification d'un format horaire */


if (isset($_REQUEST['emptylist']))
{
  unset($_SESSION['edu_uv_subscr']);
  exit();
}

if (isset($_REQUEST['modform']))
{
  $uv = intval($_REQUEST['iduv']);

  if ($uv <= 0)
    exit();
  
  $rq = new requete($site->db,
		    "SELECT
                                `code_uv`
                                , `cours_uv`
                                , `td_uv`
                                , `tp_uv`
                     FROM
                                `edu_uv`
                     WHERE
                                `id_uv` = " . $uv);
      $res = $rq->get_row();

      ($res['cours_uv'] == 1) ?	$cours = true : $cours = false;
      ($res['td_uv'] == 1)    ? $td = true    : $td = false;
      ($res['tp_uv'] == 1)    ? $tp = true    : $tp = false;

  echo "<h1>Modification d'UV</h1>";
  echo "<p>A l'aide de ce formulaire, vous pouvez modifier le format horaire de l'UV ".$res['code_uv']."</p>";

  $moduv = new form("moduv", 
		    "index.php", 
		    false, 
		    "post", 
		    "Modification d'une UV");
  $moduv->add_hidden('modifyuv', 1);
  $moduv->add_hidden('mod_iduv', $uv);
  $moduv->add_checkbox('mod_cours', 'Cours', $cours);
  $moduv->add_checkbox('mod_td', 'TD', $td);
  $moduv->add_checkbox('mod_tp', 'TP', $tp);

  $moduv->add_submit('moduv_sbmt', 'Modifier le format');

  echo $moduv->html_render();

  exit();

}


/** ajout uv **/
if (isset($_REQUEST['adduv_sbmt']))
{
  $name = $_REQUEST['adduv_name'];
  $intl = $_REQUEST['adduv_intitule'];
  $c = $_REQUEST['adduv_c'] == 1 ? 1 : 0;
  $td = $_REQUEST['adduv_td'] == 1 ? 1 : 0;
  $tp = $_REQUEST['adduv_tp'] == 1 ? 1 : 0;

  $ret = $edt->create_uv($name, $intl, $c, $td, $tp);



  if ($ret >= 0)
    $creationuv = true;
  else
    $creationuv = false;
}

if (isset($_REQUEST['modifyuv']))
{

  ($_REQUEST['mod_cours'] == 1) ? $c = 1 : $c = 0;
  ($_REQUEST['mod_td']    == 1) ? $td = 1 : $td = 0;
  ($_REQUEST['mod_tp']    == 1) ? $tp = 1 : $tp = 0;

  $uv = intval($_REQUEST['mod_iduv']);

  $rq = new update($site->dbrw,
		   'edu_uv',
		   array ('cours_uv' => $c,
			  'td_uv' => $td,
			  'tp_uv' => $tp),
		   array ('id_uv' => $uv));

  if ($rq->lines  == 1)
    $retmod = true;
  else 
    $retmod = false;

}


/* l'utilisateur a demandé l'ajout d'une UV */
if (isset($_REQUEST['subscr']))
{
  $uv = $_REQUEST['subscr'];
  if (! array_key_exists($uv, $_SESSION['edu_uv_subscr']))
    {
      $rq = new requete($site->db,
			"SELECT 
                                `id_uv`
                                , `code_uv`
                         FROM
                                `edu_uv`
                         WHERE
                                `id_uv` = " . intval($uv));
      $res = $rq->get_row();

      if ($res['cours_uv'] == 1)
	$format_h[] = "Cours";
      if ($res['td_uv'] == 1)
	$format_h[] =  "TD";
      if ($res['tp_uv'] == 1)
	$format_h[] = "TP";

      if (count($format_h) == 0)
	$format_h = "HET";
      else
	$format_h = implode(" / ", $format_h);


      $_SESSION['edu_uv_subscr'][$uv] = $res['code_uv'];
    }

  exit();
}

if (isset($_REQUEST['refreshlistuv']))
{
  echo "<h1>Liste des UVs dans lesquelles vous êtes inscrit</h1>\n";

  if (is_array($_SESSION['edu_uv_subscr']))
    {

      echo "<ul>\n";

      foreach($_SESSION['edu_uv_subscr'] as $key => $value)
	{
	  echo "<li>".$value."</li>\n";
	}
      echo "</ul>\n";
    }
  else
    echo "<b>Vous n'avez pour l'instant selectionné aucune UV.</b>";


  exit();
}


/** real code begins here */





$cts = new contents("Emploi du temps",
		    "Sur cette page, vous allez pouvoir ".
		    "créer votre emploi du temps.");

if (isset($retmod))
{
  if ($retmod == true)
    {
      $cts->add_title(1, "Modification d'UV");
      $cts->add_paragraph("Le format horaire a été modifié avec succès.");
    }
  else
    {
      $cts->add_title(1, "Modification d'UV");
      $cts->add_paragraph("<b>Erreur lors de la modification du format horaire.</b>");
    }
}

if (isset($creationuv))
{
  if ($creationuv == true)
    {
      $cts->add_title(1, "Création d'UV");
      $cts->add_paragraph("L'UV a été créée avec succès.");
    }
  else
    {
      $cts->add_title(1, "Création d'UV");
      $cts->add_paragraph("<b>Erreur lors de la création de l'UV.</b>");
    }
}

$cts->add_title(2, "Sélection des UVs");

$selectuv = new form("adduv", "index.php", true, "post", "Sélection des  UVs");

$rq = new requete($site->db,
		  "SELECT 
                            `id_uv`
                            , `code_uv`
                            , `intitule_uv`
                            , `cours_uv`
                            , `td_uv`
                            , `tp_uv`
                   FROM
                            `edu_uv`
                   ORDER BY
                            `code_uv`");

if ($rq->lines > 0)
{
  while ($rs = $rq->get_row())
    {
      $format_h = array();
      if ($rs['cours_uv'] == 1)
	$format_h[] = "Cours";
      if ($rs['td_uv'] == 1)
	$format_h[] =  "TD";
      if ($rs['tp_uv'] == 1)
	$format_h[] = "TP";
      if (count($format_h) == 0)
	$format_h = "HET";
      else
	$format_h = implode(" / ", $format_h);

      $uvs[$rs['id_uv']] = $rs['code_uv'] . " - " . $rs['intitule_uv'] . " - " . $format_h;
    }

  /* javascript code begins here ! */

  $js = 
    "
<script language=\"javascript\">
function addUV(obj)
{
 selected = document.getElementsByName('uv_sl')[0];
 evalCommand('index.php', 'subscr=' + selected.value);
 openInContents('cts2', 'index.php', 'refreshlistuv');
}

function emptylistuv()
{
  evalCommand('index.php', 'emptylist');
  openInContents('cts2', 'index.php', 'refreshlistuv');
}

function modifyuv()
{
  mod_iduv  = document.getElementsByName('mod_iduv')[0].value;
  mod_cours = document.getElementsByName('magicform[boolean][mod_cours]')[0].checked;
  mod_td    = document.getElementsByName('magicform[boolean][mod_td]')[0].checked;
  mod_tp    = document.getElementsByName('magicform[boolean][mod_tp]')[0].checked;
  alert(mod_cours + mod_td + mod_tp);

  evalCommand('index.php', 'modifyuv=1&mod_cours='+mod_cours+'&mod_td='+mod_td+'&mod_tp='+mod_tp);
  openInContents('cts3','index.php' ,'modifyuv=1&iduv='+mod_iduv+'&mod_cours='+mod_cours+'&mod_td='+mod_td+'&mod_tp='+mod_tp);

}
function updatemodifpanel()
{
  selected = document.getElementsByName('uv_sl')[0].value;
  moduv = document.getElementById('cts3');
  openInContents('cts3', 'index.php', 'modform=1&iduv='+selected);
  moduv.style.display = 'block';

}
</script>\n";
  
  $selectuv->puts($js);
  $selectuv->add_select_field('uv_sl', "UV", $uvs);
  $selectuv->add_button("adduv_existing", "Ajouter l'UV à la liste", "javascript:addUV(parent)");
  $selectuv->add_button("emptylist", "Réinitialiser la liste", "javascript:emptylistuv()");
  $selectuv->add_button("reqmodiffmth", "Modifier le format horaire", "javascript:updatemodifpanel()");

}
$cts->add($selectuv);

$cts->add_paragraph("Une fois la liste des UVs suivies renseignées, vous pouvez passer à ".
"<a href=\"./index.php?step=2\">la deuxième étape</a>");

$cts->add_title(2, "Ajout d'une UV");
$cts->add_paragraph("Au cas où une UV n'existerait pas encore en base, "
                     . "vous avez la possibilité de renseigner ses caractéristiques ici.");


$adduv = new form("adduv", "index.php", true, "post", "Ajout d'une UV");

$adduv->add_text_field('adduv_name',
		       "Code de l'UV",
		       "", true);

$adduv->add_text_area('adduv_intitule',
		      "Intitulé de l'UV",
		      "");

$adduv->add_checkbox('adduv_c',
		      "Cours",
		      true);

$adduv->add_checkbox('adduv_td',
		      "TD",
		      true);

$adduv->add_checkbox('adduv_tp',
		      "TP",
		      true);


$adduv->add_submit('adduv_sbmt',
		   "Ajouter");

$cts->add($adduv);


$site->add_contents($cts);

$uvs = "";



if (is_array($_SESSION['edu_uv_subscr']))
    {

      $uvs .= "<ul>\n";

      foreach($_SESSION['edu_uv_subscr'] as $key => $value)
	{
	  $uvs .= "<li>".$value."</li>\n";
	}
      $uvs .= "</ul>\n";
    }

else
    $uvs .= "<b>Vous n'avez pour l'instant selectionné aucune UV.</b>";

$site->add_contents(new contents('Liste des UVs dans lesquelles vous êtes '.
                        'inscrit',$uvs));

$cts = new contents("Modification d'UV","");
$cts->puts("<script language=\"javascript\">
document.getElementById('cts3').style.display = 'none';
</script>");

$cts->add_title(2, "Modification d'UV");
$cts->add_paragraph("A l'aide de ce formulaire, vous pouvez ".
                    "modifier le format horaire d'une UV");
 
$moduv = new form("moduv", 
                  "index.php", 
                  true, 
                  "post", 
                  "Modification d'une UV");

$moduv->add_hidden('mod_iduv', -1);
$moduv->add_checkbox('mod_cours', 'Cours', true);
$moduv->add_checkbox('mod_td', 'TD', true);
$moduv->add_checkbox('mod_tp', 'TP', true);

$moduv->add_button('moduv', 'Modifier le format', 'javascript:modifyuv();');

$cts->add($moduv);


$site->add_contents($cts);


$site->end_page();

?>
