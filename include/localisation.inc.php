<?php



// Force le passage en HTTPS, pour éviter le risque d'interception des données
if ( $_SERVER["REMOTE_ADDR"] != "127.0.1.1" )
{
  //echo "FAILED";
  //print_r($_SERVER);
	header("Location: https://ae.utbm.fr".$_SERVER["REQUEST_URI"]);
	exit();	
}

//$GLOBALS["localisation_key"]="testusage-testusage";
$GLOBALS["localisation_pv"]="AE2//testusage/";

function set_localisation ( $id_salle )
{

	$data = array("id_salle"=>$id_salle,"ip"=>$_SERVER["HTTP_X_FORWARDED_FOR"]);

	$data["check"]=md5($GLOBALS["localisation_pv"].$data["id_salle"].$data["ip"]);

	$data = serialize($data);

	/*$td = mcrypt_module_open(MCRYPT_TripleDES, "", MCRYPT_MODE_ECB, "");
	$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
	mcrypt_generic_init($td, $GLOBALS["localisation_key"], $iv);
	$data = mcrypt_generic($td, $data);
	mcrypt_generic_end($td);*/
	
	setcookie ("AE2_LOCALISATION", $data, time() + 31536000, "/", $_SERVER['HTTP_HOST'], true);
}

function get_localisation ( )
{
	if ( isset($_COOKIE["AE2_LOCALISATION"]) )
	{
		/*$td = mcrypt_module_open(MCRYPT_TripleDES, "", MCRYPT_MODE_ECB, "");
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $GLOBALS["localisation_key"], $iv);
		$data = mdecrypt_generic($td, $_COOKIE["AE2_LOCALISATION"]);
		mcrypt_generic_end($td);
		
		$data = unserialize($data);*/
		
		$data = unserialize($_COOKIE["AE2_LOCALISATION"]);
		
		if ( $data["check"] != md5($GLOBALS["localisation_pv"].$data["id_salle"].$data["ip"]) )
			return null;
			
		/* En raison du changement intenpestif des IPS des PCs, on 	oublie cette vérification pour le moment
		 * jusqu'a obtenir des baux dhcp fixess
		if ( $data["ip"] != $_SERVER["HTTP_X_FORWARDED_FOR"])
			return null;*/
			
		return $data["id_salle"];
	}
	
	return null;
}


?>
