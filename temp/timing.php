<?
$topdir="../";
$timing["include"] -= microtime(true);
include($topdir. "include/site.inc.php");

if( !preg_match('/^\/var\/www\/ae\/www\//', $_SERVER['SCRIPT_FILENAME']))
  $GLOBALS["taiste"] = true;
else
  $GLOBALS["taiste"] = false;
$timing["include"] += microtime(true);


class requete2 {
  
  var $result=null;
  var $errno=0;
  var $errmsg=null;
  var $lines=-1;

  function requete2 ( &$base, $req_sql )
  {
    global $timing;
    $timing["mysql"] -= $st = microtime(true);
    if(!$base->dbh) 
    {
      $this->errmsg = "Non connecté";
      if( $GLOBALS["taiste"] )
        echo "<p>NON MAIS CA VA PAS ! c'est un \$site->db et pas un \$this->db (ou inversement)</p>\n";
      return;
    }
    $this->result = mysql_query($req_sql, $base->dbh);
    $timing["mysql"] += $fn = microtime(true);
    $timing["mysql.counter"]++;
    if ( $fn-$st > 0.001 )
    $timing["req"][] = array($fn-$st,$req_sql);
    
    if ( ($this->errno = mysql_errno($base->dbh)) != 0)
    {
      $this->errmsg = mysql_error($base->dbh);
      if( $GLOBALS["taiste"] )
        echo "<p>Erreur lors du traitement de votre demande : ".$this->errmsg."</p>\n";
      $this->lines = -1;
      return;
    }

    $this->result = $res;
    if(strncasecmp($req_sql, "SELECT",6) == 0)
      $this->lines =  mysql_num_rows ($this->result);
    else
      $this->lines =  mysql_affected_rows ();
  }

  /**
   * Recupère ligne par ligne le résultat de la requête
   * @return un tableau associatif, ou null s'il n'ya plus aucune ligne.
   */
  function get_row () {
	  if(!empty($this->result))
      return mysql_fetch_array($this->result);
		else
		  return null;
  }
  
  /**
   * Retroune à la première ligne du résultat de la requête (si applicable)
   */
  function go_first ()
  {
  	if ($this->lines > 0 )
  	mysql_data_seek($this->result, 0);
  }
  
  /**
   * Détermine si la requête s'est déroulée avec succès
   * @return true si la requête a été éxécuté avec succès, false sinon
   */
  function is_success()
  {
    return $this->errno == 0;
  }
  
}



$site = new site ();

$timing["method0"] -= microtime(true);
for($i=0;$i<10000;$i++)
{
  new requete($site->db,"SELECT 1");
}
$timing["method0"] += microtime(true);
$timing["method1"] -= microtime(true);
for($i=0;$i<10000;$i++)
{
  new requete2($site->db,"SELECT 1");
}
$timing["method1"] += microtime(true);

echo "<pre>";
print_r($timing);
echo "</pre>";

?>