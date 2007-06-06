<?php

require_once("../include/mysql.inc.php");
require_once("../include/mysqlae.inc.php");
require_once("../include/entities/cotisation.inc.php");

require_once("/tmp/admin-cotis-data.php");

Header("Content-Type: text/html; charset=UTF-8");

echo "<pre>\n";

$dbro = new mysqlae();
$dbrw = new mysqlae("rw");

foreach ($data as $name) {
  $sql = new requete($dbro, "SELECT `id_utilisateur`,`nom_utl`,`prenom_utl` FROM `utilisateurs` WHERE `nom_utl` = '" .
    mysql_real_escape_string($name) . "'");

  echo "\n<b>$name</b>\n";
  if ($sql->lines == 1) {
    $c = new cotisation($dbro, $dbrw);
    $r = $sql->get_row();
    $c->load_lastest_by_user($r['id_utilisateur']);

    if ($c->id < 0) {
      echo " <font color=red>&gt;&gt;&gt;</font> No cotisation found!\n";
    } else {
      echo " &gt;&gt;&gt; ID: #" . $c->id . "\n";
      echo " &gt;&gt;&gt; Fin cotiz: " . date("Y-m-d", $c->date_fin_cotis) . "\n";
    }

    if (($c->id > 0 && (date("Y-m-d", $c->date_fin_cotis) != "2007-08-15")) ||
        ($c->id < 0)) {
      echo " <font color=green>-&gt;&gt;</font> Adding ... ";

      if ($c->add($r['id_utilisateur'], strtotime("2007-08-15"), 4, 2800)) {
        echo " done.\n";
      } else {
        echo " <font color=red>error</font>.\n";
      }
    } elseif (date("Y-m-d", $c->date_fin_cotis) == "2007-08-15") {
      echo " &gt;&gt;&gt; Already subscribed until 2007-08-15.\n";
    } else { " <font color=red>&gt;&gt;&gt;</font> Load error ?\n"; }
  } elseif ($sql->lines > 1) {
    echo " <font color=red>&gt;&gt;&gt;</font> Multiple matches :\n";
    while ($row = $sql->get_row()) {
      $c = new cotisation($dbro, $dbrw);
      $c->load_lastest_by_user($row['id_utilisateur']);

      echo "  &gt; " . $row['prenom_utl'] . " (<a href=\"/user.php?id_utilisateur=" .
        $row['id_utilisateur'] . "\">#" . $row['id_utilisateur'] . "</a>).";
        
      if ($c->id < 0) {
        echo "\tNo cotisation found.\n";
      } else {
        echo "\tFin cotiz: " . date("Y-m-d", $c->date_fin_cotis) . "\n";
      }
    }
  } else {
    echo " <font color=red>&gt;&gt;&gt;</font> Not found.\n";
  }

}

echo "</pre>\n";

?>
