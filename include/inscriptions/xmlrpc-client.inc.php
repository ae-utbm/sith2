<?php

require_once('xmlrpc.inc');

class ClientHelper
{
  var $auth;
  
  function ClientHelper ($login, $password) {
    
    /** Notre authentification auprès de l'API XML-RPC */
    $this->auth = array(new xmlrpcval($login, 'string'),
                	new xmlrpcval($password, 'string'));

  }

  /** Sends an XML-RPC request.
   *
   * @param string The method name.
   * @param array An optional array of parameters.
   */
  function sendRequest ($method, $args = 0)
  {
    if (empty($args)) {
      $args = $this->auth;
    } else {
      $args = array_merge($this->auth, $args);
    }

    $f = new xmlrpcmsg($method, $args);

    $connexion = new xmlrpc_client(API_URI, API_HOST, API_PORT, API_PROTOCOL);
    $connexion->setRequestCompression('gzip');
    $connexion->setSSLVerifyPeer(FALSE);
    $connexion->setDebug(0);

    $r = &$connexion->send($f);
    return $r;
  }

  /** Retrieve the full users list.
   */
  function getList ()
  {
    $r = $this->sendRequest ('getList');

    if (!$r->faultCode())
      {
        $v   = $r->value();
	$max = $v->arraysize();

        $ret = array();
        for ($i=0; $i < $max; $i++)
          {
            $rec    = $v->arraymem($i);
            $id     = $rec->structmem("id");
            $nom    = $rec->structmem("nom");
            $prenom = $rec->structmem("prenom");
            $email  = $rec->structmem("email");
            
            $ret[] = array("id"     => $id->getval(),
                           "nom"    => $nom->getval(),
                           "prenom" => $prenom->getval(),
                           "email"  => $email->getval());
          }
        return $ret;
      }

    return array("error_code" => $r->faultCode(),
                 "reason"     => $r->faultString());
  }

  /** Retrieve detailled info on a user.
   *
   * @param int The user ID in the temp db.
   */
  function getById ($id)
  {
    $r = $this->sendRequest ('getById', array(new xmlrpcval($id, 'int')));

    if (!$r->faultCode())
      {
        $v = $r->value();

        $nom     = $v->structmem("nom");
        $prenom  = $v->structmem("prenom");
        $email   = $v->structmem("email");
        $sexe    = $v->structmem("sexe");
        $branche = $v->structmem("branche");
        $niveau  = $v->structmem("niveau");
        $bday    = $v->structmem("date_naissance");

        return array("nom"     => $nom->getval(),
                     "prenom"  => $prenom->getval(),
                     "email"   => $email->getval(),
                     "sexe"    => $sexe->getval(),
                     "branche" => $branche->getval(),
                     "niveau"  => $niveau->getval(),
                     "date_naissance" => $bday->getval());
      }

    return array("error_code" => $r->faultCode(),
                 "reason"     => $r->faultString());
  }

  /** Add or update a user's information.
   *
   */
  function addUser ($nom, $prenom, $email, $sexe,
                    $branche, $niveau, $date_naissance)
  {
    $r= $this->sendRequest ('addUser', array(php_xmlrpc_encode(strtoupper($nom)),
                                                    php_xmlrpc_encode(ucwords(strtolower($prenom))),
                                                    php_xmlrpc_encode($email),
                                                    php_xmlrpc_encode($sexe),
                                                    php_xmlrpc_encode($branche),
                                                    php_xmlrpc_encode($niveau),
                                                    php_xmlrpc_encode($date_naissance)));

    if (!$r->faultCode())
      return TRUE;

    return array("error_code" => $r->faultCode(),
                 "reason"     => $r->faultString());
  }
}

?>
