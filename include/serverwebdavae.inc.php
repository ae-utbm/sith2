<?
/**
 * @file
 */


require_once($topdir."include/mysql.inc.php");
require_once($topdir."include/mysqlae.inc.php");
require_once($topdir."include/lib/webdavserver.inc.php");

/**
 * Serveur WebDAV exploitant l'authentification du site de l'AE.
 */
class webdavserverae extends HTTP_WebDAV_Server
{
  var $db;
  var $dbrw;
  var $user;
  
  function webdavserverae ()
  {
    $this->HTTP_WebDAV_Server();
    
    $this->http_auth_realm = "Connexion site AE. Entrez votre adresse e-mail et votre mot de passe. Pour une connexion anonyme, precisez anonymous comme nom d'utilisateur (sans mot de passe).";
    $this->dav_powered_by = "AE-2.1.5";

    $this->db = new mysqlae ();
    $this->dbrw = new mysqlae ("rw");
		$this->user = new utilisateur( $this->db );
  }
  
  /**
    * check authentication
    *
    * @param string type Authentication type, e.g. "basic" or "digest"
    * @param string username Transmitted username
    * @param string passwort Transmitted password
    * @returns bool Authentication status
    */
  function checkAuth($type, $username, $password) 
  {
    if ( $type == "digest" ) // Digest not supported
      return false;
    
    if ( $username == "anonymous" )
      return true;
      
    $this->user->load_by_email($username);
    
    if ( !$this->user->is_valid() || !$this->user->is_password($password) )
      return false;
    
    return true;
  } 

}


?>