<?php
/**
 * GISMO block IT translation file
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// block title
$string['pluginname'] = 'Gismo';
$string['gismo'] = 'Gismo';
$string['gismo_report_launch'] = 'Reporting Tool';
$string['exportlogs_missing'] = 'Parametro exportlogs mancante';
$string['exportlogs_missingcourselogs'] = 'Il processo di analisi dei dati del corso gira ad orari prestabiliti, tipicamente di notte. I dati del corso saranno disponibili entro 24 ore';


// capabilities
$string['gismo:trackuser'] = 'Gismo Studenti';
$string['gismo:trackteacher'] = 'Gismo Docenti';

// help
$string['gismo_help'] = "<p>Gismo works on those courses that meet the following requirements:</p><ul><li>there is at least one student enrolled to the course</li><li>there is at least one instance of one of the following modules:<ul><li>Resources</li><li>Assignments</li><li>Quizzes</li></ul></li></ul>";

// General
$string['page_title'] = "Gismo - ";
$string['file'] = 'File';
$string['options'] = 'Opzioni';
$string['save'] = 'Esporta il grafico come immagine';
$string['print'] = 'Stampa';
$string['exit'] = 'Esci';
$string['help'] = 'Aiuto';
$string['home'] = 'Pagina principale';
$string['close'] = 'Chiudi';

$string['users'] = 'utenti'; //************
$string['teachers'] = 'docenti'; //************

// Students
$string['students'] = 'Studenti';
$string['student_accesses'] = 'Accessi degli Studenti';
$string['student_accesses_chart_title'] = 'Studenti: Accessi degli studenti';
$string['student_accesses_overview'] = 'Panoramica degli Accessi';
$string['student_accesses_overview_chart_title'] = 'Studenti: Panoramica degli Accessi';
$string['student_resources_overview'] = 'Panoramica degli Accessi alle Risorse';
$string['student_resources_overview_chart_title'] = 'Studenti: Panoramica degli Accessi alle Risorse';
$string['student_resources_details_chart_title'] = 'Studenti: Dettagli degli Accessi alle Risorse';

// Resources
$string['resources'] = 'Risorse';
$string['detail_resources'] = 'Dettaglio sulle Risorse';
$string['resources_students_overview'] = 'Panoramica degli Studenti';
$string['resources_students_overview_chart_title'] = 'Risorse: Panoramica degli Studenti';
$string['resources_access_overview'] = 'Panoramica degli Accessi';
$string['resources_access_overview_chart_title'] = 'Risorse: Panoramica degli Accessi';
$string['resources_access_detail_chart_title'] = 'Risorse: Dettaglio sulle Risorse per Studente'; //**************

// Activities
$string['activities'] = 'Attivit&agrave;';
$string['assignments'] = 'Compiti';
$string['assignments_chart_title'] = 'Attivit&agrave;: Panoramica dei Compiti';
$string['assignments22'] = 'Compiti 2.2';
$string['assignments22_chart_title'] = 'Attivit&agrave;: Panoramica dei Compiti 2.2';
$string['chats'] = 'Chats';
$string['chats_chart_title'] = 'Attivit&agrave;: Panoramica delle Chats';
$string['chats_ud_chart_title'] = 'Attivit&agrave;: Dettagli degli Studenti sulle Chats';
$string['chats_over_time_chart_title'] = 'Attivit&agrave;: Contributi alle Chats nel tempo';
$string['forums'] = 'Forum';

$string['forums_over_time'] = 'Forum nel tempo'; //************

$string['forums_chart_title'] = 'Attivit&agrave;: Panoramica dei Forum';
$string['forums_ud_chart_title'] = 'Attivit&agrave;: Dettagli degli Studenti sui Forum';
$string['forums_over_time_chart_title'] = 'Attivit&agrave;: Contributi ai Forum nel tempo';

$string['quizzes'] = 'Quiz';
$string['quizzes_chart_title'] = 'Attivit&agrave;: Panoramica dei Questionari';

$string['wikis'] = 'Wiki';

$string['wikis_over_time'] = 'Wiki nel tempo'; //************

$string['wikis_chart_title'] = 'Attivit&agrave;: Panoramica dei Wiki';
$string['wikis_ud_chart_title'] = 'Attivit&agrave;: Dettagli degli Studenti sui Wiki';
$string['wikis_over_time_chart_title'] = 'Attivit&agrave; : Contributi ai Wiki nel tempo';

// Help
$string['help'] = 'Aiuto';
$string['tutorial'] = 'Tutorial';
$string['help_docs'] = 'Breve panoramica';
$string['about'] = 'Su Gismo';

$string['date'] = 'Data';
$string['from'] = 'Da';
$string['to'] = 'A';

$string['show'] = 'Mostra lista : '; //************
$string['list'] = ''; //************

$string['menu_hide'] = 'Nascondi menu'; //************
$string['menu_show'] = 'Mostra menu'; //************
$string['detail_show'] = 'Mostra dettagli'; //************

$string['items'] = 'OGGETTI'; //************
$string['details'] = 'Dettagli'; //************
$string['info_title'] = 'GISMO - Liste'; //************
$string['info_text'] = '<p>Per personalizzare i grafici puoi selezionare/deselezionare degli oggetti dai menu attivi.</p>";
        message += "<p>Instruzioni</p>";
        message += "<ul style=\'list-style-position: inside;\'>";
        message += "<li>Checkbox principale: selezionare/deselezionare tutti gli oggetti.</li>";
        message += "<li>Pressione su un Oggetto: selezionare/deselezionare lo specifico oggetto.</li>";
        message += "<li>Pressione di Alt+Click su un Oggetto: selezionare soltanto lo specifico oggetto.</li>";
        message += "<li><img src=\'images/eye.png\'> mostra la lista degli oggetti.</li>";
        message += "</ul>'; //************

// Errors
$string['err_course_not_set'] = 'L\'identificativo del corso non e\' stato impostato!';
$string['err_block_instance_id_not_set'] = 'L\'identificativo dell\'istanza del blocco non e\' stata impostato!';
$string['err_authentication'] = 'Non sei autorizzato. E\' possibile che la sessione di Moodle sia scaduta.<br /><br /><a href="">Connettiti</a>';
$string['err_access_denied'] = 'Non sei autorizzato a svolgere questa azione.';
$string['err_srv_data_not_set'] = 'Uno o piu\' dei parametri richiesti non risulta impostato!';
$string['err_missing_parameters'] = 'Uno o piu\' dei parametri richiesti e\' mancante!';
$string['err_missing_course_students'] = 'Non riesco ad estrarre gli studenti del corso!';
$string['gismo:view'] = "GISMO - Autorizzazione fallita";


//OTHERS
$string['welcome'] = "Benvenuti a GISMO v. 3.3";
$string['processing_wait'] = "Calcolando i dati, per favore attendere!";

//Graphs labels

$string['accesses'] = "Accessi";
$string['timeline'] = "Barra dei Tempi";
$string['actions_on'] = "Azioni su ";
$string['nr_submissions'] = "Numero degli invii";



//OPTIONS
$string['option_intro'] = 'Questa sezione vi permette di configurare le opzioni specifiche dell\'applicazione.';
$string['option_general_settings'] = 'Impostazioni generali';
$string['option_include_hidden_items'] = 'Includere elementi nascosti';
$string['option_chart_settings'] = 'Settaggi dei grafici';
$string['option_base_color'] = 'Colore di base';
$string['option_red'] = 'Rosso';
$string['option_green'] = 'Verde';
$string['option_blue'] = 'Blu';
$string['option_axes_label_max_length'] = 'Max lunghezza etichetta assi (caratteri)';
$string['option_axes_label_max_offset'] = 'Max spostamento etichetta assi (caratteri)';
$string['option_number_of_colors'] = 'Numero di sfumature (grafici a matrice)';
$string['option_other_settings'] = 'Altre impostazioni';
$string['option_window_resize_delay_seconds'] = 'Ritardo del <i>resize</i> della finestra (secondi)';
$string['save'] = 'Salva';
$string['cancel'] = 'Annulla';


$string['export_chart_as_image'] = 'GISMO - Esporta grafico come immagine';
$string['no_chart_at_the_moment'] = 'Non ci sono grafici caricati al momento!';


$string['about_gismo'] = 'Informazioni su GISMO';
$string['intro_information_about_gismo'] = 'Informazioni circa la versione correntemente installata sono riportate di seguito:'; 
$string['gismo_version'] = 'Versione ';
$string['release_date'] = 'Data di rilascio ';
$string['authors'] = 'Autori ';
$string['contact_us']= 'Per domande o per segnalare errori, contattate pure gli autori ai seguenti indirizzi: ';
$string['close'] = 'Chiudi';
$string['confirm_exiting'] = 'Sei sicuro di voler uscire da Gismo?';

//Settings
$string['manualexportpassword'] = 'Password per l\'export dei dati manuale';
$string['manualexportpassworddesc'] = 'Con questo pametro impostiamo la password per lo script export_data.php, in modo tale che per essere eseguito sia necessario introdurre la password ed evitare azioni illecite, mediante il seguente URL:<br /><br />http://site.example.com/blocks/gismo/lib/gismo/server_side/export_data.php?password=something<br /><br />Se questo campo &egrave; vuoto, non &egrave; necessario nessuna password.';
$string['manualexportpassworderror'] = 'Password per l\'export dei dati per GISMO mancante o errata';
$string['export_data_limit_records'] = 'Limita il numero di record nelle query SQL per l\'analisi dei dati';
$string['export_data_limit_recordsdesc'] = 'Limita il numero di record nelle query SQL per l\'analisi dei dati nel processo di export di Gismo (GISMOdata_manager.php).<br /> Non modificare questo valore se non si sa bene cosa si sta facendo.';
$string['export_data_hours_from_last_run'] = 'Ritardo (in ore) prima della successiva esecuzione del processo di export dei dati';
$string['export_data_hours_from_last_rundesc'] = 'Il processo di analisi dei dati in Gismo viene eseguito dopo X ore dall\'esecuzione del processo precedente. Per evitare di sovraccaricare il server &egrave; meglio non inserire un valore troppo basso. Non modificare questo valore se non si sa bene cosa si sta facendo.';
$string['export_data_run_inf'] = 'Ora in cui pu&ograve; iniziare il processo di export dei dati di Gismo';
$string['export_data_run_infdesc'] = 'Esegue il processo di export di Gismo solo a partire da quest\'ora.<br /> Questa ora deve essere precedente a: export_data_run_sup.';
$string['export_data_run_sup'] = 'Ora in cui il processo di export di Gismo non viene più eseguito';
$string['export_data_run_supdesc'] = 'Non eseguire pi&ugrave; il processo di export di Gismo dopo quest\'ora.<br /> Questa ora deve essere successiva a export_data_run_inf.';
$string['exportlogs'] = 'Esportazione dei dati per gismo';
$string['exportlogsdesc'] = 'Esporta tutti i logs: questa opzione attiva l\'export dei dati per Gismo per tutti i corsi esistenti nella piattaforma. Questa impostazione genera un numero elevato di dati nelle tabelle Gismo del database, ma ha il vantaggio che i dati di Gismo sono immediatamente disponibili dopo aver inserito il blocco Gismo un corso.<br /> Esporta solo i corsi con blocco Gismo: attiva l\'export dei dati solo per i corsi che contengono gi&agrave; il blocco Gismo, con questa opzione i dati di Gismo saranno disponibili solo dopo che il processo di export verr&agrave; attivato, quindi pu&ograve; essere necessario attendere parecchie ore prima di vedere i dati di Gismo.';
$string['exportalllogs'] = 'Esporta tutti i logs';
$string['exportcourselogs'] = 'Esporta solo i corsi con blocco Gismo';
$string['debug_mode'] = 'Modalit&agrave; debug';
$string['debug_modedesc'] = 'Se abilitato, alcuni messaggi di debug verranno visualizzati durante il processo di export di Gismo.';
$string['debug_mode_true'] = 'Abilitato';
$string['debug_mode_false'] = 'Disabilitato';

//Completion
$string['completion'] = 'Completamento';
$string['completion_quiz_menu'] = 'Quiz';
$string['completion_quiz_chart_title'] = 'Completamento dei quiz';
$string['completion_assignment_menu'] = 'Compiti';
$string['completion_assignment_chart_title'] = 'Completamento dei compiti';
$string['completion_assignment22_menu'] = 'Compiti 2.2';
$string['completion_assignment22_chart_title'] = 'Completamento dei compiti 2.2';
$string['completion_resource_menu'] = 'Risorse';
$string['completion_resource_chart_title'] = 'Completamento delle risorse';
$string['completion_forum_menu'] = 'Forum';
$string['completion_forum_chart_title'] = 'Completamento dei forum';
$string['completion_wiki_menu'] = 'Wiki';
$string['completion_wiki_chart_title'] = 'Completamento dei wiki';
$string['completion_chat_menu'] = 'Chat';
$string['completion_chat_chart_title'] = 'Completamento delle chat';
$string['completion_completed_on_tooltip'] = 'Completato il ';
$string['completion_completed_on_tooltip_months'] = "['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic']";

//Added missing string 08.10.2013
$string['err_missing_data'] ='Non posso procedere con l&#39;analisi perch&eacute; non ci sono dati';
$string['err_no_data'] ='Non ci sono dati';
$string['err_cannot_extract_data'] ='Impossibile estrarre i dati dal server!';
$string['err_unknown'] ='Errore generico!';

//Homepage text
$string['homepage_title']='Benvenuti su GISMO';
$string['homepage_processing_data_wait']='Sto analizzando i dati, attendere ...';
$string['homepage_processing_data']='Analisi dei dati';
$string['homepage_text']='GISMO &egrave uno strumento di monitoraggio interattivo degli studenti, che si basa sui dati di traccimanto delle attivita del Course Management System Moodle. Esso genera anche delle utili rappresentazioni grafiche che possono essere analizzate dal docente del corso e dagli studenti per avere una visione generale delle attivit&agrave didattiche.<br />
Seleziona per favore una delle voci di menu dalla barra in alto a questa pagina per cominciare ad usare GISMO.<br />
Se desiderate vedere il tutorial, per favore scegliete la voce di menu "Aiuto"> "Tutorial".';

$string['hide_menu']='Nascondi menu';
$string['show_menu']='Mostra menu';
$string['show_details']='Mostra dettagli';

$string['homepage_charts_preview_title']='Anteprima grafici';
$string['homepage_chart_activities_assignments_overview']='Attivit&agrave;: panoramica compiti';
$string['homepage_chart_resources_access_overview']='Risorse: panoramica degli accessi';
$string['homepage_chart_resources_students_overview']='Risorse: panoramica degli studenti';
$string['homepage_chart_students_access_overview_on_resources']='Studenti: panoramica degli accessi alle risorse';
$string['homepage_chart_students_access_overview']='Studenti: panoramica accessi';
$string['homepage_chart_students_accesses_by_students']='Studenti: accessi per ogni studente';

//Added missing string 21.10.2013
$string['accesses_tooltip']='accessi';
?>
