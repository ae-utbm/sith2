<?php

if ( $_REQUEST["action"] == "logclient" && count($site->comptoir->operateurs))
{
	$client = new utilisateur($site->db,$site->dbrw);

	if ( $_REQUEST["code_bar_carte"] )
	  $client->load_by_carteae($_REQUEST["code_bar_carte"]);
	else
	  $client->load_by_email($_REQUEST["adresse_mail_log"]);

	if ( $client->id == -2 )
	{
		$Erreur = "REFUSE. Carte à saisir.";
		$MajorError="Carte perdue/volée.";
	}
	elseif ( $client->id < 1 )
      $Erreur = "Client inconnu";
    elseif ( !$client->ae )
	  $Erreur = "Cotisation AE non renouvelée";
    elseif ( $client->is_in_group("cpt_bloque") )
	  $Erreur = "Compte bloqué : prendre contact avec un responsable. Ceci est probablement du à une dette au BDF.";	  
	else
	  $site->comptoir->ouvre_pannier($client,$_REQUEST["prix_barman"] == true);

}
/*
	En pleine vente... 
*/
else if ( $_REQUEST["action"] == "vente" && count($site->comptoir->operateurs) )
{

	if ( !strcasecmp($_REQUEST["code_barre"],"FIN") )
	{
		if ( $site->comptoir->mode == "book" )
		{
			if ( count($site->comptoir->panier) )
			{
				$emp = new emprunt ( $site->db, $site->dbrw );
				$endtime = time()+(8*24*60*60);

				$emp->add_emprunt ( $site->comptoir->client->id, null, null, time(), $endtime );
				foreach ( $site->comptoir->panier as $objet )
					$emp->add_object($objet->id);
					
				$op = first($site->comptoir->operateurs);
				$emp->retrait (  $op->id, 0, 0, "" );
	
				$rapport_contents = new contents("Pret juqu'au ".date("d/M/Y H:i",$endtime)." (maximum)");
				$rapport_contents->add_paragraph("Pret de matériel n°".$emp->id."");
			}
			$site->comptoir->vider_pour_vente();
		}
		else
		{	
			list($client,$vendus,$nvendus) = $site->comptoir->vendre_panier();
			if ( $client )
			{
				$client->refresh_solde();
				$rapport = true;
			}
		}
	}
	elseif ( !strcasecmp($_REQUEST["code_barre"],"ANN") )
		$site->comptoir->annule_dernier_produit();

	elseif ( !strcasecmp($_REQUEST["code_barre"],"ANC") )
		$site->comptoir->annule_pannier();

	elseif ( $site->comptoir->mode == "book" )
	{
		$emp = new emprunt ( $site->db, $site->dbrw );
		$bk = new livre($site->db);
		$bk->load_by_cbar( $_REQUEST["code_barre"]);
		
		if ( $bk->id > 0 )
		{
		if ( $bk->id_salle != $site->comptoir->id_salle )
			$Erreur = "Livre/BD venant d'un autre lieu !!";
		else
		{
			$emp->load_by_objet($bk->id);
			if ( $emp->id > 0 )
			{
				$emp->back_objet($bk->id);
				$message = "Livre/BD marquée comme restituée";
			}
			else
				$site->comptoir->ajout_pannier($bk);
		}
		}
	}
	else
	{
		$num = 1;
		$ok = true;
		$cbar = $_REQUEST["code_barre"];
		$produit = new produit($site->db);

		if ( ereg("^([0-9]*)x(.*)$",$cbar,$regs) )
		{
			$num = intval($regs[1]);
			$cbar = $regs[2];
		}

		$produit->charge_par_code_barre($cbar);

		if ( $produit->id > 0 )
		{
			for ( $i = 0 ; $i<$num ; $i++ )
				$ok = $ok && $site->comptoir->ajout_pannier($produit);
				
			if ( !$ok )
				$Erreur = "Solde ou stock insuffisant";	
		}
		else
		{
			$emp = new emprunt ( $site->db, $site->dbrw );
			$bk = new livre($site->db);
			$bk->load_by_cbar($cbar);
			
			if ( $bk->id > 0 )
			{
			if ( $bk->id_salle != $site->comptoir->id_salle )
				$Erreur = "Livre/BD venant d'un autre lieu !!";
			else
			{
				$site->comptoir->switch_to_special_mode("book");
				$emp->load_by_objet($bk->id);
				if ( $emp->id > 0 )
				{
					$emp->back_objet($bk->id);
					$message = "Livre/BD marqué comme restituée";
				}
				else
					$site->comptoir->ajout_pannier($bk);
			}
			}
		}
		
	
	}

}
/*
	On nous demande de recharger un compte
*/
else if ( $_REQUEST["action"] == "recharge" && count($site->comptoir->operateurs))
{

	$client = new utilisateur($site->db,$site->dbrw);
	$asso = new assocpt($site->db);

	$asso->load_by_id(1); /*AE*/

	$client->load_by_id($_REQUEST["id_utilisateur"]);
	$montant = intval($_REQUEST["montant_centimes"]);
	$id_banque = intval($_REQUEST["id_banque"]);
	$id_typepaie = intval($_REQUEST["id_typepaie"]);

	if ( $client->id < 1 )
    $RechargementErreur = "Etudiant inconnu";
  elseif ( !$GLOBALS["svalid_call"] )
    $RechargementErreur = "Ignoré";
  elseif ( !$client->ae )
	  $RechargementErreur = "Cotisation AE non renouvelée";  
  elseif ( $client->is_in_group("cpt_bloque") )
    $RechargementErreur = "Compte bloqué : prendre contact avec un responsable. Ceci est probablement du à une dette au BDF.";	      
  elseif ( $asso->id < 1 )
    $RechargementErreur = "Erreur interne";
  elseif ( $id_typepaie == PAIE_CHEQUE && $id_banque == 0 )
    $RechargementErreur = "Veuillez préciser la banque";
    
  /* Bienvenue dans un monde de restrictions... :S On va esperer que ça va limiter les erreurs */
  elseif ( $id_typepaie == PAIE_CHEQUE && $montant > 10000  )
    $RechargementErreur = "Montant du chèque trop important : 100 Euros maximum (et 5 Euros minimum) par chèque.";
  elseif ( $id_typepaie == PAIE_CHEQUE && $montant < 500  )
    $RechargementErreur = "Montant du chèque trop faible : 5 Euros minimum (et 100 Euros maximum) par chèque.";
    
  elseif ( $id_typepaie == PAIE_ESPECS && $montant > 50000  )
    $RechargementErreur = "Montant en espèces trop important : 50 Euros maximum (et 2 Euros minimum) par espèces.";
  elseif ( $id_typepaie == PAIE_ESPECS && $montant < 200  )
    $RechargementErreur = "Montant en espèces trop faible : 2 Euros minimum (et 50 Euros maximum) par espèces.";
    
  elseif ( $id_typepaie == PAIE_ESPECS && ($montant%10) != 0  )
    $RechargementErreur = "Montant en espèces invalide : pièces de 1 cts, 2 cts et 5 cts non acceptés."; 
      
	else
	{
	  if ( $id_typepaie == PAIE_ESPECS )
		  $id_banque = 0;

		$site->comptoir->recharger_compte($client,$id_typepaie,$id_banque,$montant,$asso);
	  $rapportrecharge = true;
	  $client->refresh_solde();
	}

}
else if ( $_REQUEST["page"] == "confirmrech" && count($site->comptoir->operateurs) )
{
	$client = new utilisateur($site->db);

	if ( $_REQUEST["code_bar_carte"] )
	  $client->load_by_carteae($_REQUEST["code_bar_carte"]);
	else
	  $client->load_by_email($_REQUEST["adresse_mail_rech"]);

	$montant = $_REQUEST["montant"];
	$id_banque = intval($_REQUEST["id_banque"]);
	$id_typepaie = intval($_REQUEST["id_typepaie"]);

	if ( $client->id == -2 )
	{
		$RechargementErreur = "REFUSE. Carte à saisir.";
		$MajorError="Carte perdue/volée.";
	}
	elseif ( $client->id < 1 )
    $RechargementErreur = "Etudiant inconnu";
  else if ( !$client->ae )
	  $RechargementErreur = "Cotisation AE non renouvelée";  
  else if ( $client->is_in_group("cpt_bloque") )
	  $RechargementErreur = "Compte bloqué : prendre contact avec un responsable. Ceci est probablement du à une dette au BDF.";	 
  else if ( $id_typepaie == PAIE_CHEQUE && $id_banque == 0 )
    $RechargementErreur = "Veuillez préciser la banque";
    
  /* Bienvenue dans un monde de restrictions... :S On va esperer que ça va limiter les erreurs */
  elseif ( $id_typepaie == PAIE_CHEQUE && $montant > 10000  )
    $RechargementErreur = "Montant du chèque trop important : 100 Euros maximum (et 5 Euros minimum) par chèque.";
  elseif ( $id_typepaie == PAIE_CHEQUE && $montant < 500  )
    $RechargementErreur = "Montant du chèque trop faible : 5 Euros minimum (et 100 Euros maximum) par chèque.";
    
  elseif ( $id_typepaie == PAIE_ESPECS && $montant > 50000  )
    $RechargementErreur = "Montant en espèces trop important : 50 Euros maximum (et 2 Euros minimum) par espèces.";
  elseif ( $id_typepaie == PAIE_ESPECS && $montant < 200  )
    $RechargementErreur = "Montant en espèces trop faible : 2 Euros minimum (et 50 Euros maximum) par espèces.";
    
  elseif ( $id_typepaie == PAIE_ESPECS && ($montant%10) != 0  )
    $RechargementErreur = "Montant en espèces invalide : pièces de 1 cts, 2 cts et 5 cts non acceptés."; 
    
    
	if ( $RechargementErreur )
		unset($_REQUEST["page"]);

}

// Page
$site->start_page("services","Comptoir: ".$site->comptoir->nom);
$site->add_css("css/comptoirs.css");


$cts = new contents($site->comptoir->nom);

if ( count($site->comptoir->operateurs) == 0 )
{
	$cts->add_paragraph("En attente de la connexion d'un barman");
}
else if ( $_REQUEST["page"] == "confirmrech" )
{
	$cts->add(new userinfo($client,true,true,false,false,true,true));
	
	$lst = new itemlist(false,"inforech");
	$lst->add("Mode de paiement : <b>".$TypesPaiements[$id_typepaie]."</b>") ;
	if ( $id_typepaie == PAIE_CHEQUE )
		$lst->add("Banque : <b>".$Banques[$id_banque]."</b>");
	$lst->add("Montant : <b>".($montant/100)." Euros</b>");
	$cts->add($lst);
  
	$frm = new form ("recharge","?id_comptoir=".$site->comptoir->id);
	$frm->allow_only_one_usage();
	$frm->add_hidden("action","recharge");
	$frm->add_hidden("id_utilisateur",$client->id);
	$frm->add_hidden("montant_centimes",$montant);
	$frm->add_hidden("id_banque",$id_banque);
	$frm->add_hidden("id_typepaie",$id_typepaie);
	$frm->add_submit("valid","valider");
	$cts->add($frm);
	
	$cts->add_paragraph("<a href=\"?id_comptoir=".$site->comptoir->id."\">Annuler</a>","annule");
	$cts->puts("<div class=\"clearboth\"></div>\n");
}
else if ( $site->comptoir->client->id > 0 )
{
	$cts->add(new userinfo($site->comptoir->client,true,true,false,false,true,true));
	
	if ( $message )
		$cts->add_paragraph($message,"linfo");	
	
	$frm = new form ("vente","?id_comptoir=".$site->comptoir->id);
	$frm->add_hidden("action","vente");
	if ( $Erreur )
		$frm->error($Erreur);	
		
	if ( $site->comptoir->prix_barman )
		$frm->add_info("<b>Prix barman</b>");
		
	$frm->add_text_field("code_barre","Code barre");
	$frm->add_submit("valid","valider");
	$frm->set_focus("code_barre");
	$cts->add($frm);
	
	if ( count($site->comptoir->panier))
	{
		$lst = new itemlist("Panier","panier");
		
		if ( $site->comptoir->mode == "book" )
		{
			$serie = new serie($site->db);
			

			foreach ($site->comptoir->panier as $bk)
			{
				if ( $bk->id_serie )
				{
					$serie->load_by_id($bk->id_serie);
					$bk->nom = $serie->nom." ".$bk->num_livre." : ".$bk->nom;
				}	
				$lst->add($bk->nom);
			}
		}
		else
		{
			
			foreach ($site->comptoir->panier as $vp)
			{
				$panier[$vp->produit->id][0]++;
				$panier[$vp->produit->id][1] = $vp;
			}
			$total=0;
			foreach ( $panier as $info )
			{
				list($nb,$vp) = $info;	
				$prix = $vp->produit->obtenir_prix($site->comptoir->prix_barman);
				$lst->add($vp->produit->nom.($nb>1?" x ".$nb:"")." : ".($prix*$nb/100)." E");
				$total += $prix*$nb;
			}
			$lst->add("Total: ".($total/100)."Euros","total");
		}
		
		$cts->add($lst);
	}
	$cts->puts("<div class=\"clearboth\"></div>\n");
}
else
{	
	if ( $MajorError )
		$cts->add(new contents("Erreur","<p class=\"majorerror\">".$MajorError."</p>"),true);
	
	if ( $rapport_contents )
		$cts->add($rapport_contents,true);
	elseif ( $rapport )
	{
		$recu = new itemlist("Recu","recu");
		$recu->add("Client : ".$client->nom." ".$client->prenom.", Nouveau solde ".($client->montant_compte/100)." Euros");
		$cts->add($recu,true);
	}
	elseif ( $rapportrecharge )
	{
		$recu = new itemlist("Recu","recu");
		$recu->add("Client : ".$client->nom." ".$client->prenom.", Nouveau solde ".($client->montant_compte/100)." Euros");
		$cts->add($recu,true);
	}	
	
	$frm = new form ("logclient","?id_comptoir=".$site->comptoir->id,true,"POST","Vente");
	$frm->add_hidden("action","logclient");
	if ( $Erreur )
		$frm->error($Erreur);	
	$frm->add_text_field("code_bar_carte","Carte AE");
	$frm->add_user_email_field("adresse_mail_log","Adresse email");
	$frm->add_checkbox("prix_barman","Prix barman (si possible)",true);
	$frm->add_submit("valid","valider");
	$frm->set_focus("code_bar_carte");	
	$cts->add($frm,true);
	
	$frm = new form ("confirmrech","?id_comptoir=".$site->comptoir->id,true,"POST","Rechargement");
	$frm->add_hidden("page","confirmrech");
	if ( $RechargementErreur )
		$frm->error($RechargementErreur);	
	$frm->add_price_field("montant","Montant");
  
  foreach ($TypesPaiements as $key => $item )
	{
    $sfrm = new form("id_typepaie",null,null,null,"Paiement par $item");
		if ( $key == PAIE_CHEQUE )
		  $sfrm->add_select_field("id_banque","Banque",$Banques);
		if ( $key == PAIE_ESPECS )
		  $check = TRUE;
		else
		  $check = FALSE ;
	  $frm->add($sfrm,false,true, $check ,$key ,false,true);
  }
	/*$frm->add_radiobox_field("id_typepaie","Mode de paiement",$TypesPaiements,PAIE_ESPECS,-1);
	$frm->add_select_field("id_banque","Banque",$Banques);*/
	$frm->add_text_field("code_bar_carte","Carte AE");
	$frm->add_user_email_field("adresse_mail_rech","Adresse email");
	$frm->add_submit("valid","valider");
	$cts->add($frm,true);
}

$site->add_contents($cts);

$site->end_page();


?>
