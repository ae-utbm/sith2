<?php

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

$edt = new edt($site->db, $site->dbrw);


if ($_REQUEST['render'] == 1)
{
  isset($_REQUEST['id']) ? $id = intval($_REQUEST['id']) : $id = $site->user->id;
  $edt->load($id);

  $edtimg = new edt_img("",  $edt->edt_arr);
  $edtimg->generate ();
  exit();

}

?>

