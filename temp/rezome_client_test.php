<?php
  /* Premier test, on inscrit un utilisateur */
  $request = <<<XML
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
XML;

  $client = new WSClient(array("to" => "http://ae.utbm.fr/rezomesync.php"));
  $response = $client->request($request);

  echo "Service replied asking: '".$response->str."'\n";

?>
