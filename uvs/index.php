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

$site->start_page("services", "Emploi du temps");

if (!$site->user->utbm)
{
  error_403("reservedutbm");
}
if (!$site->user->is_valid())
{
  error_403();
}


if (isset($_REQUEST['emptylist']))
{
  unset($_SESSION['edu_uv_subscr']);
  exit();
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
                                , `intitule_uv`
                                , `cours_uv`
                                , `td_uv`
                                , `tp_uv`
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
      $format_h = implode(" / ", $format_h);


      $_SESSION['edu_uv_subscr'][$uv] = $res['code_uv'] . ' - ' . $res['intitule_uv']. " - ".$format_h;
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


$edt = new edt($site->db, $site->dbrw);



$cts = new contents("Emploi du temps",
		    "Sur cette page, vous allez pouvoir ".
		    "créer votre emploi du temps.");

$cts->add_title(2, "Sélection des UVs");

$selectuv = new form("adduv", "edt.php?step=2", true, "post", "Sélection des  UVs");

$rq = new requete($site->db,
		  "SELECT 
                            `id_uv`
                            , `code_uv`
                            , `intitule_uv`
                   FROM
                            `edu_uv`");

if ($rq->lines > 0)
{
  while ($rs = $rq->get_row())
    $uvs[$rs['id_uv']] = $rs['code_uv'] . " - " . $rs['intitule_uv'];

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

</script>\n";
  
  $selectuv->puts($js);
  $selectuv->add_select_field('uv_sl', "UV", $uvs);
  $selectuv->add_button("adduv_existing", "Ajouter l'UV à la liste", "javascript:addUV(parent)");
  $selectuv->add_button("emptylist", "Réinitialiser la liste", "javascript:emptylistuv()");
}
$cts->add($selectuv);

$cts->add_title(2, "Ajout d'une UV");
$cts->add_paragraph("Au cas où une UV n'existerait pas encore en base, "
                     . "vous avez la possibilité de renseigner ses caractéristiques ici.");


$adduv = new form("adduv", "edt.php?step=2", true, "post", "Ajout d'une UV");

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
document.getElementsByName('cts3')[0].setStyle('display', 'none');
</script>");

$site->add_contents($cts);

$site->end_page();

?>
