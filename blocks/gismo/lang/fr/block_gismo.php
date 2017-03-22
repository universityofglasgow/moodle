<?php

/**
 * GISMO block FR translation file
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// block title
$string['pluginname'] = 'Gismo';
$string['gismo'] = 'Gismo';
$string['gismo_report_launch'] = 'Outil de reporting';

// capabilities
$string['gismo:trackuser'] = 'Gismo étudiant-e';
$string['gismo:trackteacher'] = 'Gismo professeur-e';

// help
$string['gismo_help'] = "<p>Gismo fonctionne sur les cours répondant aux exigences suivantes:</p><ul><li>il y a au moins un étudiant inscrit au cours</li><li>il y a au moins une instance de l'un des modules suivants :<ul><li>Ressources</li><li>Devoirs</li><li>Questionnaires</li></ul></li></ul>";

// General
$string['page_title'] = "Gismo - ";
$string['file'] = 'Fichier';
$string['options'] = 'Options';
$string['save'] = 'Exporter le graphique comme image';
$string['print'] = 'Imprimer';
$string['exit'] = 'Quitter';
$string['help'] = 'Aide';
$string['home'] = 'Accueil Gismo';
$string['close'] = 'Fermer';

$string['users'] = 'utilisateur-e-s'; //************
$string['teachers'] = 'professeur-e-s'; //************
// Students
$string['students'] = 'Étudiant-e-s';
$string['student_accesses'] = 'Accès par les étudiant-e-s';
$string['student_accesses_chart_title'] = 'Étudiant-e-s : accès par les étudiant-e-s';
$string['student_accesses_overview'] = 'Aperçu des accès';
$string['student_accesses_overview_chart_title'] = 'Étudiant-e-s : aperçu des accès';
$string['student_resources_overview'] = 'Aperçu des accès aux ressources';
$string['student_resources_overview_chart_title'] = 'Étudiant-e-s : aperçu des accès aux ressources';
$string['student_resources_details_chart_title'] = 'Étudiant-e-s : détails des accès aux ressources';

// Resources
$string['resources'] = 'Ressources';
$string['detail_resources'] = 'Détails des ressources';
$string['resources_students_overview'] = 'Aperçu des étudiant-e-s';
$string['resources_students_overview_chart_title'] = 'Ressources : aperçu des étudiant-e-s';
$string['resources_access_overview'] = 'Aperçu des accès';
$string['resources_access_overview_chart_title'] = 'Ressources : aperçu des accès';
$string['resources_access_detail_chart_title'] = 'Ressources : détails des accès par les étudiant-e-s'; //**************
// Activities
$string['activities'] = 'Activités';
$string['assignments'] = 'Devoirs';
$string['assignments_chart_title'] = 'Activités : aperçu des devoirs';
$string['assignments22'] = 'Devoirs 2.2';
$string['assignments22_chart_title'] = 'Activités : aperçu des devoirs 2.2';
$string['chats'] = 'Chats';

$string['chats_over_time'] = 'Chats à travers le temps'; //************

$string['chats_chart_title'] = 'Activités : aperçu des chats';
$string['chats_ud_chart_title'] = 'Activités : détails des contributions des étudiant-e-s aux chats';
$string['chats_over_time_chart_title'] = 'Activités : contributions aux chats à travers le temps';
$string['forums'] = 'Forums';

$string['forums_over_time'] = 'Forums à travers le temps'; //************

$string['forums_chart_title'] = 'Activités : aperçu des forums';
$string['forums_ud_chart_title'] = 'Activités : détails des contributions des étudiant-e-s aux forums';
$string['forums_over_time_chart_title'] = 'Activités : contributions aux forums à travers le temps';

$string['quizzes'] = 'Tests';
$string['quizzes_chart_title'] = 'Activités : aperçu des tests';

$string['wikis'] = 'Wikis';

$string['wikis_over_time'] = 'Wikis à travers le temps'; //************

$string['wikis_chart_title'] = 'Activités : aperçu des wikis';
$string['wikis_ud_chart_title'] = 'Activités : détails des contributions des étudiant-e-s aux wikis';
$string['wikis_over_time_chart_title'] = 'Activités : contributions aux wikis à travers le temps';

// Help
$string['help'] = 'Aide';
$string['help_docs'] = 'Bref apercu';
$string['tutorial'] = 'Tutorial';
$string['about'] = 'À propos de Gismo';

$string['date'] = 'Date';
$string['from'] = 'De';
$string['to'] = 'à';

$string['show'] = 'Afficher la liste'; //************
$string['list'] = 'Liste'; //************

$string['menu_hide'] = 'Cacher le menu'; //************
$string['menu_show'] = 'Afficher le menu'; //************
$string['detail_show'] = 'Afficher les détails'; //************

$string['items'] = 'ENTRÉES'; //************
$string['details'] = 'Détails'; //************
$string['info_title'] = 'GISMO - listes'; //************
$string['info_text'] = '<p>Pour personnaliser le graphique, vous pouvez sélectionner / déselectionner les articles dans les menus activés.</p>";
        message += "<p>Instructions</p>";
        message += "<ul style=\'list-style-position: inside;\'>";
        message += "<li>Case à cocher principale : sélectionner / déselectionner tous les articles de la liste.</li>";
        message += "<li>Clic d\'article : sélectionner / déselectionner l\'article cliqué.</li>";
        message += "<li>Alt+Clic d\'article : sélectionner uniquement l\'article cliqué.</li>";
        message += "<li><img src=\'images/eye.png\'> afficher les détails de l\'article</li>";
        message += "</ul>'; //************
// Errors
$string['err_course_not_set'] = 'L\'id du cours n\'est pas défini !';
$string['err_block_instance_id_not_set'] = 'L\'id d\'instance de bloc n\'est pas défini !';
$string['err_authentication'] = 'Vous n\'êtes pas authentifié-e. Il est possible que la session moodle ait expiré.<br /><br /><a href="">Login</a>';
$string['err_access_denied'] = 'Vous n\'êtes pas autorisé-e à effectuer cette action.';
$string['err_srv_data_not_set'] = 'Un ou plusieurs paramètres exigés est manquant !';
$string['err_missing_parameters'] = 'Un ou plusieurs paramètres exigés est manquant !';
$string['err_missing_course_students'] = 'Impossible d\'extraire les étudiant-e-s du cours !';
$string['gismo:view'] = "GISMO - Échec de l\'autorisation";


//OTHERS
$string['welcome'] = "Bienvenue dans GISMO v. 3.3";
$string['processing_wait'] = "Traitement des données, veuillez patienter !";

//Graphs labels
$string['accesses'] = "Accès";
$string['timeline'] = "Chronologie";
$string['actions_on'] = "Actions de ";
$string['nr_submissions'] = "Nombre de soumissions";



//OPTIONS
$string['option_intro'] = 'Cette section vous permet de personnaliser les options spécifiques de l\'application.';
$string['option_general_settings'] = 'Paramètres généraux';
$string['option_include_hidden_items'] = 'Inclure les articles cachés';
$string['option_chart_settings'] = 'Paramètres du graphique';
$string['option_base_color'] = 'Couleur de base';
$string['option_red'] = 'Rouge';
$string['option_green'] = 'Vert';
$string['option_blue'] = 'Bleu';
$string['option_axes_label_max_length'] = 'Longueurs max étiquette d\'axes (caractères)';
$string['option_axes_label_max_offset'] = 'Décalages max étiquette d\'axes (caractères)';
$string['option_number_of_colors'] = 'Nombres de couleurs (tableaux à matrice)';
$string['option_other_settings'] = 'Autres paramètres';
$string['option_window_resize_delay_seconds'] = 'Délai de redimensionnement de la fenêtre (secondes)';
$string['save'] = 'Enregistrer';
$string['cancel'] = 'Annuler';


$string['export_chart_as_image'] = 'GISMO - Exporter le graphique comme image';
$string['no_chart_at_the_moment'] = 'Il n\'existe pas de graphique pour le moment !';


$string['about_gismo'] = 'À propos de GISMO';
$string['intro_information_about_gismo'] = 'Les informations concernant cette version sont indiquées ci-dessous :';
$string['gismo_version'] = 'Version ';
$string['release_date'] = 'Date de publication ';
$string['authors'] = 'Auteurs ';
$string['contact_us'] = 'N\'hésitez pas à contacter les auteurs pour des questions ou pour signaler des erreurs à l\'adresse suivante : ';
$string['close'] = 'Fermer';
$string['confirm_exiting'] = 'Souhaitez vous vraiment quitter Gismo?';

//Completion
$string['completion'] = 'Achèvement';
$string['completion_quiz_menu'] = 'Tests';
$string['completion_quiz_chart_title'] = 'Achèvement tests';
$string['completion_assignment_menu'] = 'Devoirs';
$string['completion_assignment_chart_title'] = 'Achèvement devoirs';
$string['completion_assignment22_menu'] = 'Devoirs 2.2';
$string['completion_assignment22_chart_title'] = 'Achèvement devoirs 2.2';
$string['completion_resource_menu'] = 'Ressources';
$string['completion_resource_chart_title'] = 'Achèvement ressources';
$string['completion_forum_menu'] = 'Forums';
$string['completion_forum_chart_title'] = 'Achèvement forums';
$string['completion_wiki_menu'] = 'Wikis';
$string['completion_wiki_chart_title'] = 'Achèvement wikis';
$string['completion_chat_menu'] = 'Chats';
$string['completion_chat_chart_title'] = 'Achèvement chats';
$string['completion_completed_on_tooltip'] = 'Achevé le ';
$string['completion_completed_on_tooltip_months'] = "['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']";

//Added missing string 08.10.2013
$string['err_missing_data'] = 'Il est impossible de procéder à l&#39;analyse car il n&#39;y a aucune donnée disponible!';
$string['err_no_data'] = 'Pas de données';
$string['err_cannot_extract_data'] = 'Il est impossible d&#39;extraire des données du serveur!';
$string['err_unknown'] = 'Erreur inconnue!';

//Homepage text
$string['homepage_title'] = 'Bienvenus sur GISMO';
$string['homepage_processing_data_wait'] = 'Traitement de données , attendre svp!';
$string['homepage_processing_data'] = 'Traitement de données';
$string['homepage_text'] = 'GISMO est un système graphique interactif servant d\'outil de contrôle et de suivi d\'étudiant, celui-ci extrait les données de suivi à partir du système de gestion de cours Moodle. Il produit également des représentations graphiques très utiles qui peuvent être examinées par les enseignants et les étudiants afin d\'obtenir un aperçu général des activités d\'apprentissage.<br /> S\'il vous plaît sélectionner un des menus en haut de cette page pour commencer à utiliser GISMO.<br />Si vous souhaitez regarder le tutoriel il vous suffit de cliquer sur le menu "Aide" > "Tutorial".';

$string['hide_menu'] = 'Cacher menu';
$string['show_menu'] = 'Afficher menu';
$string['show_details'] = 'Afficher details';

$string['homepage_charts_preview_title'] = 'Aperçu des graphiques';
$string['homepage_chart_activities_assignments_overview'] = 'Activités: aperçu des devoirs';
$string['homepage_chart_resources_access_overview'] = 'Ressources: aperçu des accès';
$string['homepage_chart_resources_students_overview'] = 'Ressources: aperçu des étudiant-e-s';
$string['homepage_chart_students_access_overview_on_resources'] = 'Étudiant-e-s: aperçu des accès aux ressources';
$string['homepage_chart_students_access_overview'] = 'Étudiant-e-s: aperçu des accès';
$string['homepage_chart_students_accesses_by_students'] = 'Étudiant-e-s: accès par les étudiant-e-s';

//Added missing string 21.10.2013
$string['accesses_tooltip'] = 'accès';
?>
