<?php
  /* Premier test, on inscrit un utilisateur */
/*  $request = <<<XML
<inscription>
<apikey></apikey>
<utbm>0</utbm>
<nom>Crétin</nom>
<prenom>Lapin</prenom>
<email>benc@oxynux.org</email>
<password>bleh</password>
<naissance>2005-04-02</naissance>
<droitimage>true</droitimage>
<alias>LapinCretin</alias>
<sexe>1</sexe>
</inscription>
XML;*/

  /* Deuxième test, on vérifie son mot de passe */
  $request= <<<XML
<testLogin>
<apikey></apikey>
<login>benc@oxynux.org</login>
<password>leh</password>
</testLogin>
XML;

  $client = new WSClient(array("to" => "https://ae.utbm.fr:443/rezomesync.php"));
  $response = $client->request($request);

  printf("<br/> Request = %s </br>", htmlspecialchars($client->getLastRequest()));
  printf("<br/> Response = %s </br>", htmlspecialchars($client->getLastResponse())); 


  echo "Service replied asking: '".$response->str."'\n";
  
  $simplexml = new SimpleXMLElement($response->str);
  $result = $simplexml->result[0];
  echo "<br />Return: ".$result."<br />";

?>
