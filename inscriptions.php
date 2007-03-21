<?php

define("INSCR_TBL_NAME", "inscriptions");

if ($_SERVER['REQUEST_METHOD'] != 'POST' && $_GET['showsource'])
{
  highlight_file(__FILE__);
  die();
}

require_once("include/inscriptions/pass-database.inc.php");

require_once("include/inscriptions/xmlrpc.inc");
require_once("include/inscriptions/xmlrpcs.inc");
require_once("include/mysql.inc.php");
require_once("include/mysqlae.inc.php");

/** getById()
 *
 * Paramètres:
 * 0: Login
 * 1: Password
 * 2: ID
 */
function getById ($params)
{
  global $passwords;

  if ($params->getNumParams() < 3) {
    return new xmlrpcresp(NULL, 1, "Error: missing parameters, 3 needed");
  }

  $login = php_xmlrpc_decode($params->getParam(0));
  $pass  = php_xmlrpc_decode($params->getParam(1));
  $id    = php_xmlrpc_decode($params->getParam(2));

  if ($passwords[$login] != $pass)
    return new xmlrpcresp(NULL, 1, "Access denied");

  if (!isset($id) || empty($id) || ($id <= 0))
    return new xmlrpcresp(NULL, 1, "Invalid ID");

  $req = new requete(new mysqlae(),
                     "SELECT `nom`, `prenom`, `email`,`sexe`,`branche`,`niveau`,`date_naissance`,`AE`
                      FROM `" . INSCR_TBL_NAME . "` WHERE `id` = '" .
                     mysql_real_escape_string($id) . "'");
  if ($req->errno) {
      return new xmlrpcresp(NULL, 1, "Request error: " . $req->errmsg);
  }

  $res = $req->get_row();

  $ret = new xmlrpcval(array("nom"     => new xmlrpcval($res['nom']),
                             "prenom"  => new xmlrpcval($res['prenom']),
                             "email"   => new xmlrpcval($res['email']),
                             "sexe"    => new xmlrpcval($res['sexe']),
                             "branche" => new xmlrpcval($res['branche']),
                             "niveau"  => new xmlrpcval($res['niveau']),
                             "date_naissance" => new xmlrpcval($res['date_naissance'])),
                       'struct');

  return new xmlrpcresp($ret);
}

/** addUser()
 *
 * Paramètres :
 * 0: Login
 * 1: Password
 * 2: Nom
 * 3: Prénom
 * 4: Email UTBM
 * 5: Sexe (1->M, 2->F)
 * 6: Branche
 * 7: Niveau
 * 8: Date de naissance
 */
function addUser ($params)
{
  global $passwords;

  if ($params->getNumParams() < 6) {
    return new xmlrpcresp(NULL, 1, "Error: missing parameters, 5 needed at least");
  }

  $login = php_xmlrpc_decode($params->getParam(0));
  $pass  = php_xmlrpc_decode($params->getParam(1));

  if ($passwords[$login] != $pass)
    return new xmlrpcresp(NULL, 1, "Access denied");

  $nom    = utf8_encode(php_xmlrpc_decode($params->getParam(2)));
  $prenom = utf8_encode(php_xmlrpc_decode($params->getParam(3)));
  $email  = php_xmlrpc_decode($params->getParam(4));
  $sexe   = php_xmlrpc_decode($params->getParam(5));

  if (empty($nom) || empty($prenom) || empty($email) || empty($sexe) ||
      !isset($nom) || !isset($prenom) || !isset($email) || !isset($sexe)) {
    return new xmlrpcresp(NULL, 1, "Error: missing or empty mandatory parameters !");
  }

  $dept   = null;
  $lvl    = null;
  $bday   = null;

  if ($params->getNumParams() > 6) {
    $dept   = php_xmlrpc_decode($params->getParam(6));
    $lvl    = php_xmlrpc_decode($params->getParam(7));
    $_bday  = php_xmlrpc_decode($params->getParam(8));

    if (!empty($_bday) && preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $_bday)) {
        $bday = $_bday;
    } else {
        return new xmlrpcresp(NULL, 1, "Error: malformed birthday (expected YYYY-MM-DD)");
    }
  }

  if (!empty($dept) && !in_array($dept, array("GI", "GESC", "GM", "IMAP", "TC",
                                              "Administration", "Enseignant", "Autre"))) {
    return new xmlrpcresp(NULL, 1, "Error: wrong department");
  }

  $verif = new requete(new mysqlae(),
                       "SELECT * FROM `" . INSCR_TBL_NAME . "` WHERE `email` = '" .
                       mysql_real_escape_string($email) . "' LIMIT 1");

  if ($verif->lines == 1)
    {
      /* Si l'utilisateur existe déjà, on met à jour les champs vides
       * avec les nouvelles valeurs */
      $row = $verif->get_row();

      $update = array();
      if (empty($row['nom']))
        $update['nom'] = $nom;
      if (empty($row['prenom']))
        $update['prenom'] = $prenom;
      if (empty($row['sexe']))
        $update['sexe'] = $sexe;
       if (empty($row['branche']))
        $update['branche'] = $dept;
      if (empty($row['niveau']))
        $update['niveau'] = $lvl;
      if (empty($row['date_naissance']))
        $update['date_naissance'] = $bday;
      if ($row[strtoupper($login)] == 0)
        $update[strtoupper($login)] = 1;

      $req = new update(new mysqlae(rw), INSCR_TBL_NAME, $update, array("email" => $email));
      if ($req)
        return new xmlrpcresp(new xmlrpcval("Ok", 'string'));
      else
        return new xmlrpcresp(NULL, 1, "Error Update: " . $req->errmsg);
    }
  else
    {
      $req = new insert(new mysqlae(rw),
                        INSCR_TBL_NAME,
                        array("nom"     => $nom,
                              "prenom"  => $prenom,
                              "email"   => $email,
                              "sexe"    => $sexe,
                              "branche" => $dept,
                              "niveau"  => $lvl,
                              "date_naissance" => $bday,
                              "AE"     => ($login == "ae"),
                              "BDS"    => ($login == "bds"),
                              "INTEG"  => ($login == "integ"),
                              "MMT"    => ($login == "mmt")));
      if ($req)
        return new xmlrpcresp(new xmlrpcval("Ok", 'string'));
      else
        return new xmlrpcresp(NULL, 1, "Error Insert: " . $req->errmsg);
    }
}

/** getList()
 *
 * Paramètres:
 * 0: Login
 * 1: Password
 */
function getList ($params)
{
  global $passwords;

  if ($params->getNumParams() < 2) {
    return new xmlrpcresp(NULL, 1, "Error: missing parameters, 2 needed");
  }

  $login = php_xmlrpc_decode($params->getParam(0));
  $pass  = php_xmlrpc_decode($params->getParam(1));

  if ($passwords[$login] != $pass)
    return new xmlrpcresp(NULL, 1, "Access denied");

  $req = new requete(new mysqlae(),
                     "SELECT `id`, `nom`, `prenom`, `email` FROM `" . INSCR_TBL_NAME . "` ORDER BY `nom` ASC");
  $ret = array();
  while ($res = $req->get_row())
    {
      $ret[] = new xmlrpcval(array("id"     => new xmlrpcval($res['id']),
                                   "nom"    => new xmlrpcval($res['nom']),
                                   "prenom" => new xmlrpcval($res['prenom']),
                                   "email"  => new xmlrpcval($res['email'])),
                             'struct');
    }

  return new xmlrpcresp(new xmlrpcval($ret, 'array'));
}

/* Mise en place des méthodes */
$a = array("getList" => array("function" => "getList"),
           "getById" => array("function" => "getById"),
           "addUser" => array("function" => "addUser"));

$s = new xmlrpc_server($a, false);
$s->response_charset_encoding = "UTF-8";
$s->setdebug(0);
$s->compress_response = true;
$s->service();

?>
