-- Base de données: `ae2`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `ae_carte`
-- 

CREATE TABLE `ae_carte` (
  `id_carte_ae` int(11) NOT NULL auto_increment,
  `id_cotisation` int(11) NOT NULL default '0',
  `etat_vie_carte_ae` int(32) NOT NULL default '0',
  `date_expiration` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`id_carte_ae`),
  KEY `fk_ae_carte_ae_cotisations` (`id_cotisation`)
) ENGINE=MyISAM AUTO_INCREMENT=3629 DEFAULT CHARSET=latin1 PACK_KEYS=0 COMMENT='carte ae';

-- --------------------------------------------------------

-- 
-- Structure de la table `ae_cotisations`
-- 

CREATE TABLE `ae_cotisations` (
  `id_cotisation` int(11) NOT NULL auto_increment,
  `id_utilisateur` int(11) NOT NULL default '0',
  `date_cotis` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_fin_cotis` date NOT NULL default '0000-00-00',
  `a_pris_cadeau` tinyint(1) NOT NULL default '0',
  `a_pris_carte` tinyint(1) NOT NULL default '0',
  `mode_paiement_cotis` tinyint(1) NOT NULL default '0',
  `prix_paye_cotis` int(32) NOT NULL default '0',
  PRIMARY KEY  (`id_cotisation`),
  KEY `fk_ae_cotisations_utilisateurs` (`id_utilisateur`)
) ENGINE=MyISAM AUTO_INCREMENT=4248 DEFAULT CHARSET=latin1 COMMENT='historisation des cotisations';

-- --------------------------------------------------------

-- 
-- Structure de la table `asso`
-- 

CREATE TABLE `asso` (
  `id_asso` int(11) NOT NULL auto_increment,
  `id_asso_parent` int(11) default NULL,
  `nom_asso` varchar(128) NOT NULL default '',
  `nom_unix_asso` varchar(128) NOT NULL default '',
  `adresse_postale` text,
  `email_asso` varchar(128) default NULL,
  `siteweb_asso` varchar(128) default NULL,
  `login_email` varchar(64) default NULL,
  `passwd_email` varchar(64) default NULL,
  PRIMARY KEY  (`id_asso`),
  KEY `fk_asso_asso` (`id_asso_parent`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `asso_membre`
-- 

CREATE TABLE `asso_membre` (
  `id_asso` int(11) NOT NULL default '0',
  `id_utilisateur` int(11) NOT NULL default '0',
  `date_debut` date NOT NULL default '0000-00-00',
  `date_fin` date default NULL,
  `role` int(2) NOT NULL default '0',
  `desc_role` varchar(128) default NULL,
  PRIMARY KEY  (`id_asso`,`id_utilisateur`,`date_debut`),
  KEY `fk_asso_membre_utilisateurs` (`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `bk_auteur`
-- 

CREATE TABLE `bk_auteur` (
  `id_auteur` int(11) NOT NULL auto_increment,
  `nom_auteur` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id_auteur`)
) ENGINE=MyISAM AUTO_INCREMENT=471 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `bk_book`
-- 

CREATE TABLE `bk_book` (
  `id_objet` int(11) NOT NULL default '0',
  `id_editeur` int(11) NOT NULL default '0',
  `id_serie` int(11) default NULL,
  `num_livre` int(11) default NULL,
  `isbn_livre` varchar(13) default NULL,
  PRIMARY KEY  (`id_objet`),
  KEY `id_editeur` (`id_editeur`),
  KEY `id_serie` (`id_serie`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `bk_editeur`
-- 

CREATE TABLE `bk_editeur` (
  `id_editeur` int(11) NOT NULL auto_increment,
  `nom_editeur` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id_editeur`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `bk_livre_auteur`
-- 

CREATE TABLE `bk_livre_auteur` (
  `id_objet` int(11) NOT NULL default '0',
  `id_auteur` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_objet`,`id_auteur`),
  KEY `id_objet` (`id_objet`),
  KEY `id_auteur` (`id_auteur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `bk_serie`
-- 

CREATE TABLE `bk_serie` (
  `id_serie` int(11) NOT NULL auto_increment,
  `nom_serie` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id_serie`)
) ENGINE=MyISAM AUTO_INCREMENT=170 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `commentaire_entreprise`
-- 

CREATE TABLE `commentaire_entreprise` (
  `id_com_ent` int(11) NOT NULL auto_increment,
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_ent` int(11) NOT NULL default '0',
  `id_contact` int(11) default NULL,
  `date_com_ent` date NOT NULL default '0000-00-00',
  `commentaire_ent` text NOT NULL,
  PRIMARY KEY  (`id_com_ent`),
  KEY `fk_commentaire_contact_entreprise_utilisateurs` (`id_utilisateur`),
  KEY `fk_commentaire_contact_entreprise_contact_entreprise` (`id_contact`),
  KEY `id_ent` (`id_ent`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `contact_entreprise`
-- 

CREATE TABLE `contact_entreprise` (
  `id_contact` int(11) NOT NULL auto_increment,
  `id_ent` int(11) NOT NULL default '0',
  `nom_contact` varchar(128) NOT NULL default '',
  `telephone_contact` varchar(32) NOT NULL default '',
  `service_contact` varchar(128) NOT NULL default '',
  `email_contact` varchar(128) default NULL,
  `fax_contact` varchar(32) default NULL,
  PRIMARY KEY  (`id_contact`),
  KEY `fk_contact_entreprise_entreprise` (`id_ent`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpg_campagne`
-- 

CREATE TABLE `cpg_campagne` (
  `id_campagne` int(11) NOT NULL auto_increment,
  `nom_campagne` varchar(255) NOT NULL,
  `description_campagne` text NOT NULL,
  `id_groupe` int(11) NOT NULL,
  `date_debut_campagne` date default NULL,
  `date_fin_campagne` date default NULL,
  PRIMARY KEY  (`id_campagne`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpg_participe`
-- 

CREATE TABLE `cpg_participe` (
  `id_campagne` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `date_participation` date NOT NULL,
  PRIMARY KEY  (`id_campagne`,`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpg_question`
-- 

CREATE TABLE `cpg_question` (
  `id_question` int(11) NOT NULL auto_increment,
  `id_campagne` int(11) NOT NULL,
  `nom_question` varchar(255) NOT NULL,
  `description_question` text NOT NULL,
  `type_question` enum('list','text','radio','checkbox') default 'text',
  `reponses_question` text,
  `limites_reponses_question` int(2) NOT NULL,
  PRIMARY KEY  (`id_question`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 PACK_KEYS=1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpg_reponse`
-- 

CREATE TABLE `cpg_reponse` (
  `id_campagne` int(11) NOT NULL,
  `id_question` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `valeur_reponse` text NOT NULL,
  PRIMARY KEY  (`id_campagne`,`id_question`,`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpt_association`
-- 

CREATE TABLE `cpt_association` (
  `id_assocpt` int(2) NOT NULL auto_increment,
  `montant_ventes_asso` int(10) NOT NULL default '0',
  `montant_rechargements_asso` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id_assocpt`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpt_comptoir`
-- 

CREATE TABLE `cpt_comptoir` (
  `id_comptoir` int(2) NOT NULL auto_increment,
  `id_groupe` int(11) NOT NULL default '0',
  `id_assocpt` int(2) NOT NULL default '0',
  `id_groupe_vendeur` int(11) NOT NULL default '0',
  `nom_cpt` text NOT NULL,
  `type_cpt` tinyint(1) NOT NULL default '0',
  `id_salle` int(11) default NULL,
  PRIMARY KEY  (`id_comptoir`),
  KEY `fk_cpt_comptoir_groupe` (`id_groupe`),
  KEY `fk_cpt_comptoir_cpt_association` (`id_assocpt`),
  KEY `fk_cpt_comptoir_groupe1` (`id_groupe_vendeur`),
  KEY `id_salle` (`id_salle`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpt_debitfacture`
-- 

CREATE TABLE `cpt_debitfacture` (
  `id_facture` int(11) NOT NULL auto_increment,
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_comptoir` int(2) NOT NULL default '0',
  `id_utilisateur_client` int(11) NOT NULL default '0',
  `date_facture` datetime NOT NULL default '0000-00-00 00:00:00',
  `mode_paiement` char(2) NOT NULL default '',
  `montant_facture` int(12) NOT NULL default '0',
  `transacid` int(8) default NULL,
  `etat_facture` tinyint(2) default NULL,
  PRIMARY KEY  (`id_facture`),
  KEY `fk_cpt_debitfacture_utilisateurs` (`id_utilisateur`),
  KEY `fk_cpt_debitfacture_cpt_comptoir` (`id_comptoir`),
  KEY `fk_cpt_debitfacture_utilisateurs1` (`id_utilisateur_client`)
) ENGINE=MyISAM AUTO_INCREMENT=62873 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpt_mise_en_vente`
-- 

CREATE TABLE `cpt_mise_en_vente` (
  `id_produit` int(11) NOT NULL default '0',
  `id_comptoir` int(2) NOT NULL default '0',
  `stock_local_prod` int(4) NOT NULL default '0',
  `date_mise_en_vente` datetime default NULL,
  PRIMARY KEY  (`id_produit`,`id_comptoir`),
  KEY `fk_cpt_mise_en_vente_cpt_comptoir` (`id_comptoir`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpt_produits`
-- 

CREATE TABLE `cpt_produits` (
  `id_produit` int(11) NOT NULL auto_increment,
  `id_typeprod` int(11) NOT NULL default '0',
  `id_assocpt` int(2) NOT NULL default '0',
  `nom_prod` varchar(64) default NULL,
  `prix_vente_barman_prod` int(7) NOT NULL default '0',
  `prix_vente_prod` int(7) NOT NULL default '0',
  `prix_vente_prod_cotisant` int(7) NOT NULL default '0',
  `prix_achat_prod` int(7) NOT NULL default '0',
  `meta_action_prod` varchar(32) default NULL,
  `action_prod` int(1) default NULL,
  `cbarre_prod` varchar(16) default NULL,
  `stock_global_prod` int(4) NOT NULL default '0',
  `prod_archive` binary(1) NOT NULL default '\0',
  `url_logo_prod` text NOT NULL,
  `description_prod` text NOT NULL,
  `frais_port_prod` int(10) default NULL,
  `postable_prod` tinyint(1) default NULL,
  `a_retirer_prod` tinyint(1) default NULL,
  `description_longue_prod` text,
  `id_file` int(11) default NULL,
  `id_groupe` int(11) default NULL,
  `date_fin_produit` datetime default NULL,
  `id_produit_parent` int(11) default NULL,
  PRIMARY KEY  (`id_produit`),
  KEY `fk_cpt_produits_cpt_type_produit` (`id_typeprod`),
  KEY `fk_cpt_produits_cpt_association` (`id_assocpt`),
  KEY `id_file` (`id_file`),
  KEY `cbarre_prod` (`cbarre_prod`)
) ENGINE=MyISAM AUTO_INCREMENT=267 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpt_rechargements`
-- 

CREATE TABLE `cpt_rechargements` (
  `id_rechargement` int(11) NOT NULL auto_increment,
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_comptoir` int(2) NOT NULL default '0',
  `id_utilisateur_operateur` int(11) NOT NULL default '0',
  `id_assocpt` int(2) NOT NULL default '0',
  `montant_rech` int(7) NOT NULL default '0',
  `type_paiement_rech` int(1) NOT NULL default '0',
  `banque_rech` int(3) NOT NULL default '0',
  `date_rech` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id_rechargement`),
  KEY `fk_cpt_rechargements_utilisateurs` (`id_utilisateur`),
  KEY `fk_cpt_rechargements_cpt_comptoir` (`id_comptoir`),
  KEY `fk_cpt_rechargements_utilisateurs1` (`id_utilisateur_operateur`),
  KEY `fk_cpt_rechargements_cpt_association` (`id_assocpt`)
) ENGINE=MyISAM AUTO_INCREMENT=10629 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpt_tracking`
-- 

CREATE TABLE `cpt_tracking` (
  `id_utilisateur` int(11) NOT NULL,
  `id_comptoir` int(11) NOT NULL,
  `logged_time` datetime NOT NULL,
  `activity_time` datetime NOT NULL,
  `closed_time` datetime default NULL,
  PRIMARY KEY  (`id_utilisateur`,`id_comptoir`,`logged_time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpt_type_produit`
-- 

CREATE TABLE `cpt_type_produit` (
  `id_typeprod` int(11) NOT NULL auto_increment,
  `id_assocpt` int(2) NOT NULL default '0',
  `nom_typeprod` text NOT NULL,
  `action_typeprod` text NOT NULL,
  `url_logo_typeprod` varchar(128) NOT NULL default '',
  `description_typeprod` text NOT NULL,
  `id_file` int(11) default NULL,
  PRIMARY KEY  (`id_typeprod`),
  KEY `fk_cpt_type_produit_cpt_association` (`id_assocpt`),
  KEY `id_file` (`id_file`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpt_vendu`
-- 

CREATE TABLE `cpt_vendu` (
  `id_facture` int(11) NOT NULL default '0',
  `id_produit` int(11) NOT NULL default '0',
  `id_assocpt` int(2) NOT NULL default '0',
  `quantite` int(12) NOT NULL default '0',
  `prix_unit` int(12) NOT NULL default '0',
  `a_retirer_vente` tinyint(1) default NULL,
  `a_expedier_vente` tinyint(1) default NULL,
  PRIMARY KEY  (`id_facture`,`id_produit`,`id_assocpt`,`prix_unit`),
  KEY `fk_cpt_vendu_cpt_produits` (`id_produit`),
  KEY `fk_cpt_vendu_cpt_association` (`id_assocpt`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpt_vendu_cotisant`
-- 

CREATE TABLE `cpt_vendu_cotisant` (
  `id_facture` int(11) NOT NULL default '0',
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_produit` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_utilisateur`,`id_produit`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpt_verrou`
-- 

CREATE TABLE `cpt_verrou` (
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_produit` int(11) NOT NULL default '0',
  `id_comptoir` int(2) NOT NULL default '0',
  `prix_cotisant` int(1) NOT NULL default '0',
  `quantite` char(32) NOT NULL default '',
  `date_res` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id_utilisateur`,`id_produit`,`id_comptoir`),
  KEY `fk_cpt_verrou_cpt_produits` (`id_produit`),
  KEY `fk_cpt_verrou_cpt_comptoir` (`id_comptoir`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpt_verrou_cotisant`
-- 

CREATE TABLE `cpt_verrou_cotisant` (
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_produit` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_utilisateur`,`id_produit`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpta_budget`
-- 

CREATE TABLE `cpta_budget` (
  `id_budget` int(11) NOT NULL auto_increment,
  `id_classeur` int(11) NOT NULL default '0',
  `nom_budget` varchar(128) NOT NULL default '',
  `total_budget` int(32) NOT NULL default '0',
  `date_budget` datetime NOT NULL default '0000-00-00 00:00:00',
  `valide_budget` tinyint(1) default NULL,
  `projets_budget` text,
  `termine_budget` enum('0','1') default '0',
  PRIMARY KEY  (`id_budget`),
  KEY `fk_cpta_budget_cpta_classeur` (`id_classeur`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpta_classeur`
-- 

CREATE TABLE `cpta_classeur` (
  `id_classeur` int(11) NOT NULL auto_increment,
  `id_cptasso` int(11) NOT NULL default '0',
  `date_debut_classeur` date NOT NULL default '0000-00-00',
  `date_fin_classeur` date NOT NULL default '0000-00-00',
  `nom_classeur` varchar(128) NOT NULL default '',
  `ferme` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id_classeur`),
  KEY `fk_cpta_classeur_compte_asso` (`id_cptasso`)
) ENGINE=MyISAM AUTO_INCREMENT=203 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpta_cpasso`
-- 

CREATE TABLE `cpta_cpasso` (
  `id_cptasso` int(11) NOT NULL auto_increment,
  `id_asso` int(11) NOT NULL default '0',
  `id_cptbc` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_cptasso`),
  KEY `fk_compte_asso_asso` (`id_asso`),
  KEY `fk_compte_asso_cpta_cpbancaire` (`id_cptbc`)
) ENGINE=MyISAM AUTO_INCREMENT=64 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpta_cpbancaire`
-- 

CREATE TABLE `cpta_cpbancaire` (
  `id_cptbc` int(11) NOT NULL auto_increment,
  `nom_cptbc` varchar(128) NOT NULL default '',
  `solde_cptbc` int(11) default NULL,
  `date_releve_cptbc` date default NULL,
  `num_cptbc` varchar(25) default NULL,
  PRIMARY KEY  (`id_cptbc`),
  KEY `num_cptbc` (`num_cptbc`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpta_cpbancaire_lignes`
-- 

CREATE TABLE `cpta_cpbancaire_lignes` (
  `id_cptbc` int(11) NOT NULL,
  `num_ligne_cptbc` int(11) NOT NULL auto_increment,
  `date_ligne_cptbc` date NOT NULL,
  `date_valeur_ligne_cptbc` date NOT NULL,
  `libelle_ligne_cptbc` varchar(60) default NULL,
  `commentaire_ligne_cptbc` varchar(256) default NULL,
  `montant_ligne_cptbc` int(11) NOT NULL,
  `devise_ligne_cptbc` enum('EUR') NOT NULL default 'EUR',
  `libbanc_ligne_cptbc` varchar(30) NOT NULL,
  PRIMARY KEY  (`id_cptbc`,`num_ligne_cptbc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpta_facture`
-- 

CREATE TABLE `cpta_facture` (
  `id_efact` int(11) NOT NULL auto_increment,
  `id_classeur` int(11) NOT NULL,
  `nom_facture` varchar(128) NOT NULL,
  `adresse_facture` text NOT NULL,
  `date_facture` date NOT NULL,
  `titre_facture` varchar(128) NOT NULL,
  `montant_facture` int(11) NOT NULL,
  `id_op` int(11) default NULL,
  PRIMARY KEY  (`id_efact`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpta_facture_ligne`
-- 

CREATE TABLE `cpta_facture_ligne` (
  `num_ligne_efact` int(11) NOT NULL auto_increment,
  `id_efact` int(11) NOT NULL,
  `prix_unit_ligne_efact` int(11) NOT NULL,
  `quantite_ligne_efact` int(11) NOT NULL,
  `designation_ligne_efact` varchar(64) NOT NULL,
  PRIMARY KEY  (`num_ligne_efact`,`id_efact`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpta_libelle`
-- 

CREATE TABLE `cpta_libelle` (
  `id_libelle` int(11) NOT NULL auto_increment,
  `id_asso` int(11) NOT NULL default '0',
  `nom_libelle` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id_libelle`),
  KEY `id_asso` (`id_asso`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpta_ligne_budget`
-- 

CREATE TABLE `cpta_ligne_budget` (
  `id_budget` int(11) NOT NULL default '0',
  `num_lignebudget` int(11) NOT NULL auto_increment,
  `id_opclb` int(11) NOT NULL default '0',
  `description_ligne` varchar(128) NOT NULL default '',
  `montant_ligne` int(32) NOT NULL default '0',
  PRIMARY KEY  (`id_budget`,`num_lignebudget`),
  KEY `fk_cpta_ligne_budget_cpta_op_clb` (`id_opclb`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpta_op_clb`
-- 

CREATE TABLE `cpta_op_clb` (
  `id_opclb` int(11) NOT NULL auto_increment,
  `id_asso` int(11) default NULL,
  `id_opstd` int(11) default NULL,
  `libelle_opclb` varchar(128) NOT NULL default '',
  `type_mouvement` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id_opclb`),
  KEY `fk_cpta_op_clb_asso` (`id_asso`),
  KEY `fk_cpta_op_clb_cpta_op_plcptl` (`id_opstd`)
) ENGINE=MyISAM AUTO_INCREMENT=1065 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpta_op_plcptl`
-- 

CREATE TABLE `cpta_op_plcptl` (
  `id_opstd` int(11) NOT NULL auto_increment,
  `code_plan` varchar(8) NOT NULL default '',
  `libelle_plan` varchar(128) NOT NULL default '',
  `type_mouvement` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id_opstd`)
) ENGINE=MyISAM AUTO_INCREMENT=516 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpta_operation`
-- 

CREATE TABLE `cpta_operation` (
  `id_op` int(11) NOT NULL auto_increment,
  `id_opclb` int(11) default NULL,
  `id_cptasso` int(11) default NULL,
  `id_opstd` int(11) default NULL,
  `id_utilisateur` int(11) default NULL,
  `id_asso` int(11) default NULL,
  `id_op_liee` int(11) default NULL,
  `id_ent` int(11) default NULL,
  `id_classeur` int(11) NOT NULL default '0',
  `num_op` int(32) NOT NULL default '0',
  `montant_op` int(32) NOT NULL default '0',
  `date_op` date NOT NULL default '0000-00-00',
  `commentaire_op` varchar(128) NOT NULL default '',
  `op_effctue` tinyint(1) NOT NULL default '0',
  `mode_op` tinyint(4) default NULL,
  `num_cheque_op` varchar(32) default NULL,
  `id_libelle` int(11) default NULL,
  PRIMARY KEY  (`id_op`),
  KEY `fk_cpta_operation_cpta_op_clb` (`id_opclb`),
  KEY `fk_cpta_operation_cpta_op_plcptl` (`id_opstd`),
  KEY `fk_cpta_operation_utilisateurs` (`id_utilisateur`),
  KEY `fk_cpta_operation_asso` (`id_asso`),
  KEY `fk_cpta_operation_cpta_operation` (`id_op_liee`),
  KEY `fk_cpta_operation_entreprise` (`id_ent`),
  KEY `fk_cpta_operation_cpta_classeur` (`id_classeur`),
  KEY `fk_cpta_operation_cpta_cpasso` (`id_cptasso`),
  KEY `id_libelle` (`id_libelle`)
) ENGINE=MyISAM AUTO_INCREMENT=4714 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `cpta_operation_cpblg`
-- 

CREATE TABLE `cpta_operation_cpblg` (
  `id_op` int(11) NOT NULL,
  `id_cptbc` int(11) NOT NULL,
  `num_ligne_cptbc` int(11) NOT NULL,
  PRIMARY KEY  (`id_op`,`id_cptbc`,`num_ligne_cptbc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `d_file`
-- 

CREATE TABLE `d_file` (
  `id_file` int(11) NOT NULL auto_increment,
  `nom_fichier_file` varchar(96) NOT NULL default '',
  `titre_file` varchar(96) default NULL,
  `id_folder` int(11) NOT NULL default '0',
  `description_file` text,
  `date_ajout_file` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_asso` int(11) default NULL,
  `nb_telechargement_file` int(11) NOT NULL default '0',
  `mime_type_file` varchar(64) NOT NULL default '',
  `taille_file` int(11) NOT NULL default '0',
  `id_utilisateur` int(11) default NULL,
  `id_groupe` int(11) NOT NULL default '0',
  `id_groupe_admin` int(11) NOT NULL default '0',
  `droits_acces_file` int(11) NOT NULL default '0',
  `modere_file` smallint(1) NOT NULL default '0',
  PRIMARY KEY  (`id_file`)
) ENGINE=MyISAM AUTO_INCREMENT=942 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `d_folder`
-- 

CREATE TABLE `d_folder` (
  `id_folder` int(11) NOT NULL auto_increment,
  `titre_folder` varchar(96) default NULL,
  `id_folder_parent` int(11) default NULL,
  `description_folder` text,
  `date_ajout_folder` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_asso` int(11) default NULL,
  `id_utilisateur` int(11) default NULL,
  `id_groupe` int(11) NOT NULL default '0',
  `id_groupe_admin` int(11) NOT NULL default '0',
  `droits_acces_folder` int(11) NOT NULL default '0',
  `modere_folder` smallint(1) NOT NULL default '0',
  PRIMARY KEY  (`id_folder`)
) ENGINE=MyISAM AUTO_INCREMENT=346 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `edu_uv`
-- 

CREATE TABLE `edu_uv` (
  `id_uv` int(11) NOT NULL,
  `code_uv` char(4) NOT NULL,
  `intitule_uv` varchar(128) default NULL,
  PRIMARY KEY  (`id_uv`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `edu_uv_groupe`
-- 

CREATE TABLE `edu_uv_groupe` (
  `id_uv_groupe` int(11) NOT NULL,
  `id_uv` int(11) NOT NULL,
  `type_grp` enum('C','TD','TP') NOT NULL,
  `numero_grp` int(1) default NULL,
  `heure_debut_grp` int(4) NOT NULL,
  `heure_fin_grp` int(4) NOT NULL,
  `jour_grp` int(1) NOT NULL,
  `frequence_grp` int(1) NOT NULL,
  `semestre_grp` varchar(5) NOT NULL,
  `salle_grp` varchar(4) NOT NULL,
  `id_lieu` int(11) default NULL,
  PRIMARY KEY  (`id_uv_groupe`),
  KEY `id_uv` (`id_uv`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `edu_uv_groupe_etudiant`
-- 

CREATE TABLE `edu_uv_groupe_etudiant` (
  `id_uv_groupe` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `semaine_etu_grp` enum('AB','A','B') NOT NULL default 'AB',
  PRIMARY KEY  (`id_uv_groupe`,`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `entreprise`
-- 

CREATE TABLE `entreprise` (
  `id_ent` int(11) NOT NULL auto_increment,
  `nom_entreprise` varchar(128) NOT NULL default '',
  `rue_entreprise` varchar(128) default NULL,
  `ville_entreprise` varchar(128) default NULL,
  `cpostal_entreprise` varchar(16) default NULL,
  `pays_entreprise` varchar(64) default NULL,
  `telephone_entreprise` varchar(32) default NULL,
  `email_entreprise` varchar(128) default NULL,
  `fax_entreprise` varchar(32) default NULL,
  `id_ville` int(11) default NULL,
  `siteweb_entreprise` varchar(128) NOT NULL,
  PRIMARY KEY  (`id_ent`),
  KEY `id_ville` (`id_ville`)
) ENGINE=MyISAM AUTO_INCREMENT=560 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `entreprise_secteur`
-- 

CREATE TABLE `entreprise_secteur` (
  `id_ent` int(11) NOT NULL,
  `id_secteur` int(11) NOT NULL,
  PRIMARY KEY  (`id_ent`,`id_secteur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `fax_fbx`
-- 

CREATE TABLE `fax_fbx` (
  `id_fax` int(11) NOT NULL auto_increment,
  `idfree_fax` int(11) NOT NULL,
  `idtfree_fax` varchar(16) NOT NULL,
  `numdest_fax` varchar(32) NOT NULL,
  `filename_fax` varchar(256) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_asso` int(11) NOT NULL,
  `date_fax` datetime NOT NULL,
  PRIMARY KEY  (`id_fax`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='Table des instances de fax envoyé via la Freebox de l''AE ';

-- --------------------------------------------------------

-- 
-- Structure de la table `fimu_inscr`
-- 

CREATE TABLE `fimu_inscr` (
  `id_inscr` int(5) NOT NULL auto_increment,
  `id_utilisateur` int(11) NOT NULL default '0',
  `disp_24` enum('0','1') default NULL,
  `disp_25` enum('0','1') default NULL,
  `disp_26` enum('0','1') default NULL,
  `disp_27` enum('0','1') default NULL,
  `disp_28` enum('0','1') default NULL,
  `disp_29` enum('0','1') default NULL,
  `choix1_choix` enum('pilote','regisseur','accueil','signaletic') NOT NULL default 'pilote',
  `choix1_com` text,
  `choix2_choix` enum('pilote','regisseur','accueil','signaletic') NOT NULL default 'pilote',
  `choix2_com` text,
  `lang1_lang` tinytext,
  `lang1_lvl` tinytext,
  `lang1_com` text,
  `lang2_lang` tinytext,
  `lang2_lvl` tinytext,
  `lang2_com` text,
  `lang3_lang` tinytext,
  `lang3_lvl` tinytext,
  `lang3_com` text,
  `permis` enum('O','N') NOT NULL default 'N',
  `voiture` enum('O','N') NOT NULL default 'N',
  `afps` enum('O','N') NOT NULL default 'N',
  `afps_com` tinytext,
  `poste_preced` text,
  `remarques` text,
  PRIMARY KEY  (`id_inscr`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `frm_forum`
-- 

CREATE TABLE `frm_forum` (
  `id_forum` int(11) NOT NULL auto_increment,
  `titre_forum` varchar(64) NOT NULL,
  `description_forum` text,
  `categorie_forum` enum('0','1') NOT NULL default '0',
  `id_forum_parent` int(11) default NULL,
  `id_asso` int(11) default NULL,
  `id_sujet_dernier` int(11) default NULL,
  `nb_sujets_forum` int(11) NOT NULL default '0',
  `id_utilisateur` int(11) default NULL,
  `id_groupe` int(11) NOT NULL,
  `id_groupe_admin` int(11) NOT NULL,
  `droits_acces_forum` int(11) NOT NULL,
  `ordre_forum` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_forum`),
  KEY `id_groupe_admin` (`id_groupe_admin`),
  KEY `id_groupe` (`id_groupe`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_asso` (`id_asso`),
  KEY `id_forum_parent` (`id_forum_parent`)
) ENGINE=MyISAM AUTO_INCREMENT=159 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `frm_message`
-- 

CREATE TABLE `frm_message` (
  `id_message` int(11) NOT NULL auto_increment,
  `id_utilisateur` int(11) default NULL,
  `id_sujet` int(11) NOT NULL,
  `titre_message` varchar(128) default NULL,
  `contenu_message` text NOT NULL,
  `date_message` datetime NOT NULL,
  `syntaxengine_message` varchar(8) NOT NULL,
  `id_utilisateur_moderateur` int(11) default NULL,
  PRIMARY KEY  (`id_message`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_sujet` (`id_sujet`),
  KEY `id_utilisateur_moderateur` (`id_utilisateur_moderateur`),
  FULLTEXT KEY `contenu_message` (`contenu_message`)
) ENGINE=MyISAM AUTO_INCREMENT=96286 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `frm_sujet`
-- 

CREATE TABLE `frm_sujet` (
  `id_sujet` int(11) NOT NULL auto_increment,
  `id_utilisateur` int(11) default NULL,
  `id_forum` int(11) NOT NULL,
  `titre_sujet` varchar(92) NOT NULL,
  `soustitre_sujet` varchar(128) default NULL,
  `type_sujet` tinyint(4) NOT NULL,
  `icon_sujet` varchar(32) default NULL,
  `date_sujet` datetime NOT NULL,
  `id_message_dernier` int(11) default NULL,
  `nb_messages_sujet` int(11) NOT NULL default '0',
  `date_fin_annonce_sujet` datetime default NULL,
  `id_utilisateur_moderateur` int(11) default NULL,
  `id_nouvelle` int(11) default NULL,
  `id_catph` int(11) default NULL,
  `id_sondage` int(11) default NULL,
  PRIMARY KEY  (`id_sujet`),
  KEY `id_sondage` (`id_sondage`),
  KEY `id_catph` (`id_catph`),
  KEY `id_nouvelle` (`id_nouvelle`),
  KEY `id_utilisateur_moderateur` (`id_utilisateur_moderateur`),
  KEY `id_message_dernier` (`id_message_dernier`),
  KEY `id_forum` (`id_forum`),
  KEY `id_utilisateur` (`id_utilisateur`),
  FULLTEXT KEY `titre_sujet` (`titre_sujet`),
  FULLTEXT KEY `soustitre_sujet` (`soustitre_sujet`)
) ENGINE=MyISAM AUTO_INCREMENT=6173 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `frm_sujet_utilisateur`
-- 

CREATE TABLE `frm_sujet_utilisateur` (
  `id_sujet` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_message_dernier_lu` int(11) NOT NULL,
  PRIMARY KEY  (`id_sujet`,`id_utilisateur`),
  KEY `id_message_dernier_lu` (`id_message_dernier_lu`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `groupe`
-- 

CREATE TABLE `groupe` (
  `id_groupe` int(11) NOT NULL auto_increment,
  `nom_groupe` varchar(40) NOT NULL default '',
  `description_groupe` text NOT NULL,
  PRIMARY KEY  (`id_groupe`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `inscriptions`
-- 

CREATE TABLE `inscriptions` (
  `id` smallint(4) NOT NULL auto_increment,
  `nom` varchar(255) NOT NULL default '',
  `prenom` varchar(255) NOT NULL default '',
  `sexe` enum('1','2') character set latin1 default NULL,
  `email` varchar(255) character set latin1 NOT NULL default '',
  `branche` enum('TC','GI','GMC','GESC','IMAP','Autre','Administration','Enseignant','Master') default NULL,
  `niveau` tinyint(2) default NULL,
  `date_naissance` date default NULL,
  `AE` tinyint(1) default '0',
  `INTEG` tinyint(1) default '0',
  `BDS` tinyint(1) default '0',
  `MMT` tinyint(1) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3104 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `inv_emprunt`
-- 

CREATE TABLE `inv_emprunt` (
  `id_emprunt` int(11) NOT NULL auto_increment,
  `id_utilisateur` int(11) default NULL,
  `id_asso` int(11) default NULL,
  `id_utilisateur_op` int(11) default NULL,
  `date_demande_emp` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_prise_emp` datetime default NULL,
  `date_retour_emp` datetime default NULL,
  `date_debut_emp` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_fin_emp` datetime NOT NULL default '0000-00-00 00:00:00',
  `caution_emp` int(32) default NULL,
  `prix_paye_emp` int(11) default NULL,
  `emprunteur_ext` varchar(128) default NULL,
  `notes_emprunt` text,
  `etat_emprunt` int(2) NOT NULL default '0',
  PRIMARY KEY  (`id_emprunt`),
  KEY `fk_inv_emprunt_utilisateurs` (`id_utilisateur`),
  KEY `fk_inv_emprunt_asso` (`id_asso`),
  KEY `fk_inv_emprunt_utilisateurs1` (`id_utilisateur_op`)
) ENGINE=MyISAM AUTO_INCREMENT=453 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `inv_emprunt_objet`
-- 

CREATE TABLE `inv_emprunt_objet` (
  `id_objet` int(11) NOT NULL default '0',
  `id_emprunt` int(11) NOT NULL default '0',
  `retour_effectif_emp` datetime default NULL,
  PRIMARY KEY  (`id_objet`,`id_emprunt`),
  KEY `fk_inv_emprunt_objet_inv_emprunt` (`id_emprunt`),
  KEY `stop_doublons` (`id_objet`,`retour_effectif_emp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `inv_objet`
-- 

CREATE TABLE `inv_objet` (
  `id_objet` int(11) NOT NULL auto_increment,
  `id_asso` int(11) NOT NULL default '0',
  `id_salle` int(11) NOT NULL default '0',
  `id_op` int(11) default NULL,
  `id_asso_prop` int(11) NOT NULL default '0',
  `id_objtype` int(11) NOT NULL default '0',
  `nom_objet` varchar(128) default NULL,
  `num_objet` int(32) NOT NULL default '0',
  `cbar_objet` varchar(64) NOT NULL default '',
  `num_serie` varchar(128) NOT NULL default '',
  `date_achat` date NOT NULL default '0000-00-00',
  `prix_objet` int(32) NOT NULL default '0',
  `caution_objet` int(11) NOT NULL default '0',
  `prix_emprunt_objet` int(11) NOT NULL default '0',
  `objet_empruntable` tinyint(1) NOT NULL default '0',
  `en_etat` tinyint(1) NOT NULL default '0',
  `archive_objet` tinyint(1) NOT NULL default '0',
  `notes_objet` text,
  PRIMARY KEY  (`id_objet`),
  KEY `fk_inv_objet_asso` (`id_asso`),
  KEY `fk_inv_objet_sl_salle` (`id_salle`),
  KEY `fk_inv_objet_cpta_operation` (`id_op`),
  KEY `fk_inv_objet_asso1` (`id_asso_prop`),
  KEY `fk_inv_objet_inv_type_objets` (`id_objtype`)
) ENGINE=MyISAM AUTO_INCREMENT=1534 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `inv_objet_evenement`
-- 

CREATE TABLE `inv_objet_evenement` (
  `id_objeven` int(11) NOT NULL auto_increment,
  `id_objet` int(11) NOT NULL default '0',
  `id_emprunt` int(11) default NULL,
  `id_utilisateur` int(11) NOT NULL default '0',
  `type_objeven` int(32) NOT NULL default '0',
  `date_even` datetime NOT NULL default '0000-00-00 00:00:00',
  `notes_even` text,
  PRIMARY KEY  (`id_objeven`),
  KEY `fk_inv_objet_evenement_inv_objet` (`id_objet`),
  KEY `fk_inv_objet_evenement_inv_emprunt` (`id_emprunt`),
  KEY `fk_inv_objet_evenement_utilisateurs` (`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `inv_type_objets`
-- 

CREATE TABLE `inv_type_objets` (
  `id_objtype` int(11) NOT NULL auto_increment,
  `nom_objtype` varchar(128) NOT NULL default '',
  `prix_objtype` int(32) NOT NULL default '0',
  `caution_objtype` int(11) NOT NULL default '0',
  `prix_emprunt_objtype` int(11) NOT NULL default '0',
  `code_objtype` varchar(6) NOT NULL default '',
  `empruntable_objtype` tinyint(1) NOT NULL default '0',
  `notes_objtype` text,
  PRIMARY KEY  (`id_objtype`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `job_annonces`
-- 

CREATE TABLE `job_annonces` (
  `id_annonce` int(11) NOT NULL auto_increment,
  `id_client` int(11) NOT NULL,
  `id_select_etu` int(11) default NULL,
  `titre` text NOT NULL,
  `job_type` int(11) NOT NULL,
  `desc` text NOT NULL,
  `divers` text,
  `profil` text NOT NULL,
  `start_date` date NOT NULL,
  `duree` int(11) NOT NULL,
  `nb_postes` int(11) NOT NULL default '1',
  `indemnite` int(11) default NULL,
  `ville` text,
  `type_contrat` text,
  PRIMARY KEY  (`id_annonce`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='stockage des annonces AE Job Etu';

-- --------------------------------------------------------

-- 
-- Structure de la table `job_annonces_etu`
-- 

CREATE TABLE `job_annonces_etu` (
  `id_relation` int(11) NOT NULL auto_increment,
  `id_annonce` int(11) NOT NULL,
  `id_etu` int(11) NOT NULL,
  `relation` enum('apply','reject') NOT NULL,
  `comment` text,
  PRIMARY KEY  (`id_relation`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `job_types`
-- 

CREATE TABLE `job_types` (
  `id_type` int(11) NOT NULL default '0',
  `nom` text NOT NULL,
  PRIMARY KEY  (`id_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `job_types_etu`
-- 

CREATE TABLE `job_types_etu` (
  `id_type` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  PRIMARY KEY  (`id_type`,`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `loc_lieu`
-- 

CREATE TABLE `loc_lieu` (
  `id_lieu` int(11) NOT NULL auto_increment,
  `id_lieu_parent` int(11) default NULL,
  `id_ville` int(11) NOT NULL,
  `nom_lieu` varchar(64) NOT NULL,
  `lat_lieu` double default NULL,
  `long_lieu` double default NULL,
  `eloi_lieu` double default NULL,
  PRIMARY KEY  (`id_lieu`),
  KEY `id_lien_parent` (`id_lieu_parent`),
  KEY `id_ville` (`id_ville`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `loc_pays`
-- 

CREATE TABLE `loc_pays` (
  `id_pays` int(3) NOT NULL auto_increment,
  `nom_pays` varchar(32) NOT NULL,
  `indtel_pays` int(4) default NULL,
  PRIMARY KEY  (`id_pays`)
) ENGINE=MyISAM AUTO_INCREMENT=213 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `loc_ville`
-- 

CREATE TABLE `loc_ville` (
  `id_ville` int(9) NOT NULL auto_increment,
  `id_pays` int(3) default '0',
  `nom_ville` varchar(64) NOT NULL default '',
  `cpostal_ville` varchar(10) default NULL,
  `lat_ville` double NOT NULL default '0',
  `long_ville` double NOT NULL default '0',
  `eloi_ville` double NOT NULL default '0',
  PRIMARY KEY  (`id_ville`)
) ENGINE=MyISAM AUTO_INCREMENT=35250 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `mc_jeton`
-- 

CREATE TABLE `mc_jeton` (
  `id_jeton` int(11) NOT NULL auto_increment,
  `id_salle` int(11) NOT NULL default '0',
  `type_jeton` enum('laver','secher') NOT NULL default 'laver',
  `nom_jeton` varchar(8) NOT NULL default '',
  PRIMARY KEY  (`id_jeton`),
  KEY `id_salle` (`id_salle`),
  KEY `nom_jeton` (`nom_jeton`)
) ENGINE=MyISAM AUTO_INCREMENT=104 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `mc_jeton_utilisateur`
-- 

CREATE TABLE `mc_jeton_utilisateur` (
  `id_jeton` int(11) NOT NULL default '0',
  `id_utilisateur` int(11) NOT NULL default '0',
  `prise_jeton` datetime NOT NULL default '0000-00-00 00:00:00',
  `retour_jeton` datetime default NULL,
  PRIMARY KEY  (`id_jeton`,`id_utilisateur`,`prise_jeton`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `nvl_dates`
-- 

CREATE TABLE `nvl_dates` (
  `id_nouvelle` int(11) NOT NULL default '0',
  `id_dates_nvl` int(11) NOT NULL auto_increment,
  `id_salres` int(11) default NULL,
  `date_debut_eve` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_fin_eve` datetime NOT NULL default '0000-00-00 00:00:00',
  `titre_eve` varchar(128) default NULL,
  PRIMARY KEY  (`id_nouvelle`,`id_dates_nvl`),
  KEY `fk_nvl_dates_sl_reservation` (`id_salres`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `nvl_nouvelles`
-- 

CREATE TABLE `nvl_nouvelles` (
  `id_nouvelle` int(11) NOT NULL auto_increment,
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_asso` int(11) default NULL,
  `titre_nvl` varchar(128) NOT NULL default '',
  `resume_nvl` text,
  `contenu_nvl` text NOT NULL,
  `date_nvl` datetime NOT NULL default '0000-00-00 00:00:00',
  `modere_nvl` tinyint(1) NOT NULL default '0',
  `id_utilisateur_moderateur` int(11) default NULL,
  `type_nvl` tinyint(1) NOT NULL default '1',
  `asso_seule_nvl` enum('0','1') NOT NULL default '0',
  `id_lieu` int(11) default NULL,
  PRIMARY KEY  (`id_nouvelle`),
  KEY `fk_nvl_nouvelles_utilisateurs` (`id_utilisateur`),
  KEY `fk_nvl_nouvelles_asso` (`id_asso`)
) ENGINE=MyISAM AUTO_INCREMENT=558 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `pages`
-- 

CREATE TABLE `pages` (
  `id_page` int(2) NOT NULL auto_increment,
  `nom_page` varchar(64) NOT NULL default '',
  `id_groupe` int(11) NOT NULL default '0',
  `id_utilisateur` int(11) NOT NULL default '0',
  `texte_page` text NOT NULL,
  `date_page` datetime NOT NULL default '0000-00-00 00:00:00',
  `titre_page` text NOT NULL,
  `section_page` varchar(24) default NULL,
  `id_groupe_modal` int(11) NOT NULL default '0',
  `droits_acces_page` int(11) NOT NULL default '0',
  `modere_page` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id_page`),
  KEY `fk_pages_groupe` (`id_groupe`),
  KEY `fk_pages_utilisateurs` (`id_utilisateur`)
) ENGINE=MyISAM AUTO_INCREMENT=110 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `parrains`
-- 

CREATE TABLE `parrains` (
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_utilisateur_fillot` int(11) NOT NULL default '0',
  `id_photo` int(11) default NULL,
  PRIMARY KEY  (`id_utilisateur`,`id_utilisateur_fillot`),
  KEY `fk_parrains_utilisateurs1` (`id_utilisateur_fillot`),
  KEY `fk_parrains_sas_photos` (`id_photo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `pl_gap`
-- 

CREATE TABLE `pl_gap` (
  `id_gap` int(11) NOT NULL auto_increment,
  `id_planning` int(11) NOT NULL default '0',
  `start_gap` int(11) NOT NULL default '0',
  `end_gap` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_gap`,`id_planning`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `pl_gap_user`
-- 

CREATE TABLE `pl_gap_user` (
  `id_planning` int(11) NOT NULL default '0',
  `id_gap` int(11) NOT NULL default '0',
  `id_utilisateur` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_planning`,`id_gap`,`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `pl_planning`
-- 

CREATE TABLE `pl_planning` (
  `id_planning` int(11) NOT NULL auto_increment,
  `id_asso` int(11) NOT NULL default '0',
  `name_planning` varchar(64) NOT NULL default '',
  `user_per_gap` smallint(6) NOT NULL default '0',
  `start_date_planning` datetime default NULL,
  `end_date_planning` datetime default NULL,
  `weekly_planning` enum('1','0') NOT NULL default '0',
  PRIMARY KEY  (`id_planning`),
  KEY `id_asso` (`id_asso`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sas_cat_photos`
-- 

CREATE TABLE `sas_cat_photos` (
  `id_catph` int(11) NOT NULL auto_increment,
  `id_groupe` int(11) NOT NULL default '0',
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_groupe_admin` int(11) NOT NULL default '0',
  `id_catph_parent` int(11) default NULL,
  `id_photo` int(11) default NULL,
  `nom_catph` varchar(128) NOT NULL default '',
  `date_debut_catph` datetime default '0000-00-00 00:00:00',
  `date_fin_catph` datetime default '0000-00-00 00:00:00',
  `droits_acces_catph` int(32) NOT NULL default '0',
  `modere_catph` tinyint(1) NOT NULL default '0',
  `meta_id_asso_catph` int(11) default NULL,
  `meta_mode_catph` int(1) default NULL,
  `id_lieu` int(11) default NULL,
  PRIMARY KEY  (`id_catph`),
  KEY `fk_sas_cat_photos_groupe` (`id_groupe`),
  KEY `fk_sas_cat_photos_utilisateurs` (`id_utilisateur`),
  KEY `fk_sas_cat_photos_groupe1` (`id_groupe_admin`),
  KEY `fk_sas_cat_photos_sas_cat_photos` (`id_catph_parent`),
  KEY `id_lieu` (`id_lieu`)
) ENGINE=MyISAM AUTO_INCREMENT=610 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sas_palette`
-- 

CREATE TABLE `sas_palette` (
  `r` int(3) unsigned NOT NULL default '0',
  `g` int(3) unsigned NOT NULL default '0',
  `b` int(3) unsigned NOT NULL default '0',
  `idx` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`idx`),
  KEY `r` (`r`),
  KEY `g` (`g`),
  KEY `b` (`b`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sas_palette_photos`
-- 

CREATE TABLE `sas_palette_photos` (
  `idx` int(11) NOT NULL default '0',
  `id_photo` int(11) NOT NULL default '0',
  PRIMARY KEY  (`idx`,`id_photo`),
  UNIQUE KEY `id_photo` (`id_photo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sas_personnes_photos`
-- 

CREATE TABLE `sas_personnes_photos` (
  `id_photo` int(8) NOT NULL default '0',
  `id_utilisateur` int(8) NOT NULL default '0',
  `accord_phutl` enum('0','1') default '0',
  `modere_phutl` enum('0','1') default '0',
  PRIMARY KEY  (`id_photo`,`id_utilisateur`),
  KEY `fk_sas_personnes_photos_utilisateurs` (`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sas_photos`
-- 

CREATE TABLE `sas_photos` (
  `id_photo` int(11) NOT NULL auto_increment,
  `id_catph` int(11) NOT NULL default '0',
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_groupe` int(11) NOT NULL default '0',
  `id_groupe_admin` int(11) NOT NULL default '0',
  `id_utilisateur_photographe` int(11) default NULL,
  `date_prise_vue` datetime default '0000-00-00 00:00:00',
  `modere_ph` tinyint(1) NOT NULL default '0',
  `commentaire_ph` varchar(128) default NULL,
  `droits_acces_ph` int(16) NOT NULL default '0',
  `incomplet` tinyint(1) default NULL,
  `droits_acquis` tinyint(1) default NULL,
  `couleur_moyenne` int(11) default NULL,
  `classification` int(11) default NULL,
  `supprime_ph` tinyint(1) NOT NULL default '0',
  `meta_id_asso_ph` int(11) default NULL,
  `date_ajout_ph` datetime default NULL,
  `id_utilisateur_moderateur` int(11) default NULL,
  `type_media_ph` enum('0','1') NOT NULL default '0',
  `titre_ph` varchar(80) default NULL,
  `id_asso_photographe` int(11) default NULL,
  PRIMARY KEY  (`id_photo`),
  KEY `fk_sas_photos_sas_cat_photos` (`id_catph`),
  KEY `fk_sas_photos_utilisateurs` (`id_utilisateur`),
  KEY `fk_sas_photos_groupe` (`id_groupe`),
  KEY `fk_sas_photos_utilisateurs1` (`id_utilisateur_photographe`),
  KEY `id_asso_photographe` (`id_asso_photographe`)
) ENGINE=MyISAM AUTO_INCREMENT=47736 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sas_retrait`
-- 

CREATE TABLE `sas_retrait` (
  `id_retrait` int(11) NOT NULL auto_increment,
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_photo` int(11) NOT NULL default '0',
  `date_retrait` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id_retrait`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sdn_a_repondu`
-- 

CREATE TABLE `sdn_a_repondu` (
  `id_sondage` int(11) NOT NULL default '0',
  `id_utilisateur` int(11) NOT NULL default '0',
  `date_reponse` datetime default NULL,
  PRIMARY KEY  (`id_sondage`,`id_utilisateur`),
  KEY `fk_sdn_a_repondu_utilisateurs` (`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sdn_reponse`
-- 

CREATE TABLE `sdn_reponse` (
  `num_reponse` int(11) NOT NULL auto_increment,
  `id_sondage` int(11) NOT NULL default '0',
  `nom_reponse` varchar(128) default NULL,
  `nb_reponse` varchar(32) default NULL,
  PRIMARY KEY  (`num_reponse`),
  KEY `fk_sdn_reponse_sdn_sondage` (`id_sondage`)
) ENGINE=MyISAM AUTO_INCREMENT=105 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sdn_sondage`
-- 

CREATE TABLE `sdn_sondage` (
  `id_sondage` int(11) NOT NULL auto_increment,
  `question` varchar(128) default NULL,
  `total_reponses` varchar(32) default NULL,
  `date_sondage` date default NULL,
  `date_fin` date default NULL,
  PRIMARY KEY  (`id_sondage`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `secteur`
-- 

CREATE TABLE `secteur` (
  `id_secteur` int(11) NOT NULL auto_increment,
  `nom_secteur` varchar(64) NOT NULL,
  PRIMARY KEY  (`id_secteur`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `site_boites`
-- 

CREATE TABLE `site_boites` (
  `nom_boite` varchar(32) NOT NULL default '',
  `contenu_boite` text NOT NULL,
  `description_boite` text NOT NULL,
  PRIMARY KEY  (`nom_boite`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `site_parametres`
-- 

CREATE TABLE `site_parametres` (
  `nom_param` varchar(32) NOT NULL default '',
  `valeur_param` text NOT NULL,
  PRIMARY KEY  (`nom_param`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `site_sessions`
-- 

CREATE TABLE `site_sessions` (
  `id_session` char(32) NOT NULL default '',
  `id_utilisateur` int(11) NOT NULL default '0',
  `date_debut_sess` datetime NOT NULL default '0000-00-00 00:00:00',
  `derniere_visite` datetime NOT NULL default '0000-00-00 00:00:00',
  `connecte_sess` enum('1','0') NOT NULL default '1',
  `expire_sess` datetime default NULL,
  PRIMARY KEY  (`id_session`),
  KEY `fk_site_sessions_utilisateurs` (`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sl_association`
-- 

CREATE TABLE `sl_association` (
  `id_asso` int(11) NOT NULL default '0',
  `id_salle` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_asso`,`id_salle`),
  KEY `fk_sl_association_sl_salle` (`id_salle`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sl_batiment`
-- 

CREATE TABLE `sl_batiment` (
  `id_batiment` int(11) NOT NULL auto_increment,
  `id_site` int(11) NOT NULL default '0',
  `nom_bat` varchar(128) NOT NULL default '',
  `bat_fumeur` int(32) NOT NULL default '0',
  `convention_bat` tinyint(1) NOT NULL default '0',
  `notes_bat` text,
  PRIMARY KEY  (`id_batiment`),
  KEY `fk_sl_batiment_sl_site` (`id_site`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 PACK_KEYS=0;

-- --------------------------------------------------------

-- 
-- Structure de la table `sl_reservation`
-- 

CREATE TABLE `sl_reservation` (
  `id_salres` int(11) NOT NULL auto_increment,
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_utilisateur_op` int(11) default NULL,
  `id_salle` int(11) NOT NULL default '0',
  `id_asso` int(11) default NULL,
  `date_demande_res` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_debut_salres` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_fin_salres` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_accord_res` datetime default NULL,
  `description_salres` text NOT NULL,
  `convention_salres` tinyint(1) NOT NULL default '0',
  `etat_salres` int(32) NOT NULL default '0',
  `notes_salres` text,
  PRIMARY KEY  (`id_salres`),
  KEY `fk_sl_reservation_utilisateurs` (`id_utilisateur`),
  KEY `fk_sl_reservation_utilisateurs1` (`id_utilisateur_op`),
  KEY `fk_sl_reservation_sl_salle` (`id_salle`),
  KEY `fk_sl_reservation_asso` (`id_asso`)
) ENGINE=MyISAM AUTO_INCREMENT=1023 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sl_salle`
-- 

CREATE TABLE `sl_salle` (
  `id_salle` int(11) NOT NULL auto_increment,
  `id_batiment` int(11) NOT NULL default '0',
  `nom_salle` varchar(128) NOT NULL default '',
  `etage` int(32) NOT NULL default '0',
  `salle_fumeur` tinyint(1) NOT NULL default '0',
  `convention_salle` tinyint(1) NOT NULL default '0',
  `reservable` tinyint(1) NOT NULL default '0',
  `surface_salle` int(32) NOT NULL default '0',
  `tel_salle` varchar(32) NOT NULL default '',
  `notes_salle` text,
  PRIMARY KEY  (`id_salle`),
  KEY `fk_sl_salle_sl_batiment` (`id_batiment`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sl_site`
-- 

CREATE TABLE `sl_site` (
  `id_site` int(11) NOT NULL auto_increment,
  `nom_site` varchar(128) NOT NULL default '',
  `site_fumeur` tinyint(1) NOT NULL default '0',
  `convention_site` tinyint(1) NOT NULL default '0',
  `notes_site` text,
  `id_ville` int(11) default NULL,
  PRIMARY KEY  (`id_site`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sms_translations`
-- 

CREATE TABLE `sms_translations` (
  `sms` varchar(26) NOT NULL,
  `french` varchar(255) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `sso_api_keys`
-- 

CREATE TABLE `sso_api_keys` (
  `key` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `stats_browser`
-- 

CREATE TABLE `stats_browser` (
  `browser` varchar(20) NOT NULL default '',
  `visites` int(6) NOT NULL,
  PRIMARY KEY  (`browser`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `stats_os`
-- 

CREATE TABLE `stats_os` (
  `os` varchar(20) NOT NULL,
  `visites` int(6) NOT NULL,
  PRIMARY KEY  (`os`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `stats_page`
-- 

CREATE TABLE `stats_page` (
  `page` varchar(255) NOT NULL,
  `visites` int(6) NOT NULL,
  PRIMARY KEY  (`page`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `utilisateurs`
-- 

CREATE TABLE `utilisateurs` (
  `id_utilisateur` int(11) NOT NULL auto_increment,
  `nom_utl` varchar(64) NOT NULL default '',
  `prenom_utl` varchar(64) NOT NULL default '',
  `email_utl` varchar(128) NOT NULL default '',
  `pass_utl` varchar(34) default NULL,
  `hash_utl` varchar(32) NOT NULL default '',
  `sexe_utl` enum('1','2') default '1',
  `date_naissance_utl` date default NULL,
  `addresse_utl` varchar(128) default NULL,
  `ville_utl` varchar(64) default NULL,
  `cpostal_utl` varchar(16) default NULL,
  `pays_utl` varchar(64) default NULL,
  `tel_maison_utl` varchar(32) default NULL,
  `tel_portable_utl` varchar(32) default NULL,
  `alias_utl` varchar(128) default NULL,
  `utbm_utl` enum('0','1') default '0',
  `etudiant_utl` enum('0','1') default '0',
  `ancien_etudiant_utl` enum('0','1') default '0',
  `ae_utl` enum('0','1') default '0',
  `modere_utl` enum('0','1') default '0',
  `droit_image_utl` enum('0','1') default '0',
  `montant_compte` int(32) default NULL,
  `site_web` varchar(92) default NULL,
  `date_maj_utl` datetime default NULL,
  `derniere_visite_utl` datetime default NULL,
  `publique_utl` enum('0','1') NOT NULL default '1',
  `publique_mmtpapier_utl` enum('0','1') NOT NULL default '1',
  `tovalid_utl` enum('none','email','utbmemail','utbm') default 'none',
  `id_ville` int(11) default NULL,
  `id_pays` int(11) default NULL,
  `tout_lu_avant_utl` datetime default NULL,
  `signature_utl` text,
  PRIMARY KEY  (`id_utilisateur`),
  KEY `email_utl` (`email_utl`),
  KEY `nom_utl` (`nom_utl`,`prenom_utl`),
  KEY `alias_utl` (`alias_utl`),
  KEY `id_ville` (`id_ville`),
  KEY `id_pays` (`id_pays`)
) ENGINE=MyISAM AUTO_INCREMENT=3608 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `utl_etu`
-- 

CREATE TABLE `utl_etu` (
  `id_utilisateur` int(11) NOT NULL default '0',
  `citation` text,
  `adresse_parents` varchar(128) default NULL,
  `ville_parents` varchar(128) default NULL,
  `cpostal_parents` varchar(32) default NULL,
  `pays_parents` varchar(128) default NULL,
  `tel_parents` varchar(128) default NULL,
  `nom_ecole_etudiant` varchar(128) default NULL,
  `visites` int(6) default '0',
  `id_ville` int(11) default NULL,
  `id_pays` int(11) default NULL,
  PRIMARY KEY  (`id_utilisateur`),
  KEY `id_ville` (`id_ville`),
  KEY `id_pays` (`id_pays`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `utl_etu_utbm`
-- 

CREATE TABLE `utl_etu_utbm` (
  `id_utilisateur` int(11) NOT NULL default '0',
  `semestre_utbm` int(2) default NULL,
  `branche_utbm` varchar(6) default NULL,
  `filiere_utbm` varchar(6) default NULL,
  `surnom_utbm` varchar(128) default NULL,
  `email_utbm` varchar(128) default NULL,
  `promo_utbm` int(2) default NULL,
  `date_diplome_utbm` date default NULL,
  `role_utbm` varchar(3) default 'etu',
  `departement_utbm` varchar(4) default 'na',
  PRIMARY KEY  (`id_utilisateur`),
  KEY `email_utbm` (`email_utbm`),
  KEY `surnom_utbm` (`surnom_utbm`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `utl_groupe`
-- 

CREATE TABLE `utl_groupe` (
  `id_groupe` int(11) NOT NULL default '0',
  `id_utilisateur` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_groupe`,`id_utilisateur`),
  KEY `fk_utl_groupe_utilisateurs` (`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `utl_parametres`
-- 

CREATE TABLE `utl_parametres` (
  `id_utilisateur` int(11) NOT NULL default '0',
  `nom_param` varchar(32) NOT NULL default '',
  `valeur_param` text NOT NULL,
  PRIMARY KEY  (`id_utilisateur`,`nom_param`),
  KEY `nom_param` (`nom_param`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `vt_a_vote`
-- 

CREATE TABLE `vt_a_vote` (
  `id_election` int(11) NOT NULL default '0',
  `id_utilisateur` int(11) NOT NULL default '0',
  `date_vote` int(32) default NULL,
  PRIMARY KEY  (`id_election`,`id_utilisateur`),
  KEY `fk_vt_a_vote_utilisateurs` (`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `vt_candidat`
-- 

CREATE TABLE `vt_candidat` (
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_poste` int(11) NOT NULL default '0',
  `id_liste` int(11) default NULL,
  `nombre_voix` int(32) NOT NULL default '0',
  PRIMARY KEY  (`id_utilisateur`,`id_poste`),
  KEY `fk_vt_candidat_vt_postes` (`id_poste`),
  KEY `fk_vt_candidat_vt_liste_candidat` (`id_liste`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `vt_election`
-- 

CREATE TABLE `vt_election` (
  `id_election` int(11) NOT NULL auto_increment,
  `id_groupe` int(11) NOT NULL default '0',
  `date_debut` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_fin` datetime NOT NULL default '0000-00-00 00:00:00',
  `nom_elec` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id_election`),
  KEY `fk_vt_election_groupe` (`id_groupe`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `vt_liste_candidat`
-- 

CREATE TABLE `vt_liste_candidat` (
  `id_liste` int(11) NOT NULL auto_increment,
  `id_utilisateur` int(11) NOT NULL default '0',
  `id_election` int(11) NOT NULL default '0',
  `nom_liste` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id_liste`),
  KEY `fk_vt_liste_candidat_utilisateurs` (`id_utilisateur`),
  KEY `fk_vt_liste_candidat_vt_election` (`id_election`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `vt_postes`
-- 

CREATE TABLE `vt_postes` (
  `id_poste` int(11) NOT NULL auto_increment,
  `id_election` int(11) NOT NULL default '0',
  `nom_poste` varchar(128) NOT NULL default '',
  `description_poste` text NOT NULL,
  `votes_total` int(32) NOT NULL default '0',
  `votes_blancs` int(32) NOT NULL default '0',
  PRIMARY KEY  (`id_poste`),
  KEY `fk_vt_postes_vt_election` (`id_election`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=latin1;
