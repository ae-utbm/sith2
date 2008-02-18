<?php
  /* Premier test, on inscrit un utilisateur */
  $request = <<<XML
<inscription>
<apikey>ML2ee7549d391c2fcd451758ed6cb6dcaa22d8ff80</apikey>
<utbm>false</utbm>
<nom>Crétin</nom>
<prenom>Lapin</prenom>
<email>benc@oxynux.org</email>
<password>bleh</password>
<naissance>2005-04-02</naissance>
<droitimage>true</droitimage>
<alias>LapinCretin</alias>
<sexe>1</sexe>
</inscription>
XML;
  
  $client = new WSClient(array("to" => "http://ae.utbm.fr/rezomesync.php"));
  $response = $client->request($request);

  printf("<br/> Request = %s </br>", htmlspecialchars($client->getLastRequest()));
  printf("<br/> Response = %s </br>", htmlspecialchars($client->getLastResponse())); 


  echo "Service replied asking: '".$response->str."'\n";

?>
