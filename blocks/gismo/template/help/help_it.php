<?php
/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// libraries & acl
require_once "common.php";
?>
<div id="inner">
    <b>Che cos'&egrave; GISMO?</b>
    <p>
        Sei il docente di uno o pi&ugrave; corsi a distanza, che gestisci con il CMS (Course Management System) Moodle. Forse i tuoi studenti sono a
        centinaia di chilometri di distanza, o forse non sono cos&igrave; lontani, ma li vedi in classe solo una o due volte a semestre.
        Essendo il docente del corso, sarai probabilmente interessato a sapere quel che succede ai tuoi studenti: stanno leggendo il materiale?
        Stanno frequentando lo spazio online regolarmente? Ci sono alcuni quiz o compiti che risultano particolarmente problematici? Le consegne sono eseguite in tempo? 
        <br>

        Se questo &egrave; il tuo caso, GISMO ti pu&ograve; essere d'aiuto. GISMO &egrave; uno strumento grafico interattivo che permette il monitoraggio degli 
        studenti e il tracciamento del sistema, estraendo i dati da Moodle. GISMO genera delle utili rappresentazioni grafiche che possono essere 
        analizzate dai docenti per esaminare i vari aspetti degli studenti a distanza. 

    </p>
    <p>
        <b>Come ottenere e installare il software</b>
    </p>
    <p>
        Per installare il software devi chiedere all'amministratore della tua piattaforma locale Moodle di aggiungere il blocco.
        <br>
        L'applicazione GISMO pu&ograve; essere scaricata dalla seguente pagina internet: http://sourceforge.net/projects/gismo/
        <br>
        Una volta che il tuo amministratore ha installato il software, sei pronto ad utilizzarlo.
    </p>
    <p>
        <b>Avviamento di GISMO</b>
    </p>
    <p>
        GISMO appare come qualsiasi altro blocco di Moodle. Per aggiungere GISMO al tuo corso dovrai innanzitutto cliccare su &quot;Attiva Modifica&quot;. 
        Questo blocco sar&agrave; visibile solo ai docenti del corso. Per avviare l'applicazione, dovrai cliccare sul link &quot;GISMO&quot; che appare all'interno del blocco GISMO.
    </p>
    <p>
        <b>Rappresentazioni grafiche</b>
    </p>
    <p>
        In questa sezione viene descritta ogni rappresentazione grafica che pu&ograve; essere generata con GISMO. Le visualizzazioni grafiche possono essere attivate cliccando sulle voci del menu. Esistono tre principali categorie:
    </p>
    <p>
        <em>&#8729; Studenti
            <br>
            &#8729; Risorse
            <br>
            &#8729; Attivit&agrave;
        </em>
    </p>
    <p>
        Per ogni categoria c'&egrave; una voce specifica nella barra del men&ugrave;;. Ciascuna di esse verr&agrave; illustrata nelle sezioni seguenti.
    </p>
    <p>
        <b>Pagina di benvenuto</b>
    </p>
    <p>
        <img src="images/help/gismo_main.png" width="500" height="330">
        <br>
        <em>[Pagina di benvenuto]</em>
    </p>
    <p>
        La figura<em> [Pagina di benvenuto] </em>
        rappresenta la pagina di benvenuto di GISMO. Come puoi vedere, ci sono tre diverse aree (pannelli) nell'interfaccia dell'utente:
    </p>
    <ul style="list-style-position: inside;">
        <li>
            Pannello-grafici: i grafici vengono tracciati in questo pannello. 
        </li>
        <li>
            Pannello-lista: contiene la lista degli studenti, le risorse, i quiz e i compiti del corso monitorato. Per ogni lista, il docente pu&ograve; selezionare/deselezionare i dati da visualizzare. 
        </li>
        <li>
            Pannello-tempo: tramite questo pannello il docente pu&ograve; effettuare la selezione in termini di tempo, limitando il grafico per uno specifico intervallo di date.
        </li>
    </ul>
    <p>
        <b>Studenti: accessi degli studenti</b>
    </p>
    <p>
        <img src="images/help/students_accesses_by_students.png" width="500" height="331">
        <br>
        <em>[Studenti: accessi degli studenti]</em>
    </p>
    <p>
        La figura <em>[Studenti: accessi degli studenti]</em> 
        illustra un grafico sugli accessi degli studenti al corso. Una semplice matrice formata dal nome degli studenti (sull'asse Y) e le date del corso (sull'asse X) &egrave; utilizzata per raffigurare gli accessi al corso. Un segno corrispondente rappresenta almeno un accesso al corso da parte dello studente per la data selezionata.  
    </p>
    <p>
        <b>Studenti: panoramica degli accessi</b>
    </p>
    <p>
        <img src="images/help/students_accesses_overview.png" width="500" height="331">
        <br>
        <em>[Studenti: panoramica degli accessi]</em>
    </p>
    <p>
        La figura <em>[Studenti: panoramica degli accessi]</em> 
        mostra un istogramma con il numero globale di visite al corso effettuate dagli studenti per ogni data. 
    </p>
    <p>
        Grazie ai due grafici precedenti, il docente ha, in un solo colpo d'occhio, una panoramica degli accessi al corso effettuati dagli studenti. Inoltre, potr&agrave; identificare chiaramente modelli e tendenze e avere informazioni sulla presenza al corso di uno studente specifico.
    </p>
    <p>
        <b>Studenti: Panoramica degli accessi alle risorse</b>
    </p>
    <p>
        <img src="images/help/students_accesses_overview_on_resources.png" width="500" height="325">
        <br>
        <em>[Studenti: Panoramica degli accessi alle risorse]</em>
    </p>
    <p>
        L'immagine nella figura <em>[Studenti: Panoramica degli accessi alle risorse]</em>
        rappresenta il numero globale degli accessi effettuati dagli studenti (asse X) a tutte le risorse del corso (asse Y).
        <br />
        Cliccando sull'&quot;icona occhio&quot; nel men&ugrave;; di sinistra l'utente potr&agrave; vedere i dettagli per uno studente specifico. Questo genera la seguente rappresentazione.
    </p>
    <p>
        <b>Studenti: Dettagli degli studenti sulle risorse</b>
    </p>
    <p>
        <img src="images/help/student_details_on_resources.png" width="500" height="331">
        <br>
        <em>[Studenti: Dettagli degli studenti sulle risorse]</em>
    </p>
    <p>
        La figura <em>[Studenti: Dettagli degli studenti sulle risorse]</em>
        illustra una panoramica degli accessi di uno studente alle risorse del corso. Le date sono rappresentate sull'asse X e le risorse sull'asse Y.  
    </p>
    <p>
        <b>Risorse: panoramica degli studenti</b>
    </p>
    <p>
        <img src="images/help/resources_students_overview.png" width="500" height="331">
        <br>
        <em>[Risorse: panoramica degli studenti]</em>
    </p>
    <p>
        I docenti potrebbero anche essere interessati ad avere i dettagli su quali risorse sono state utilizzate dagli studenti e quando. Una specifica rappresentazione grafica &egrave; stata concepita per fornire questa informazione.
        La figura <em>[Risorse: panoramica degli studenti]</em>
        raffigura i nomi degli studenti sull'asse Y e i nomi delle risorse sull'asse X. Un segno indica se lo studente ha acceduto alla risorsa e il colore del segno varier&agrave; dal rosso-chiaro al rosso-scuro a seconda del numero di volte in cui lo studente ha acceduto alla risorsa.  
    </p>
    <p>
        <b>Risorse: Panoramica degli accessi</b>
    </p>
    <p>
        <img src="images/help/resources_accesses_overview.png" width="500" height="325">
        <br>
        <em>[Risorse: Panoramica degli accessi]</em>
    </p>
    <p>
        L'immagine nella figura <em>[Risorse: Panoramica degli accessi]</em>
        illustra il numero globale degli accessi effettuati dagli studenti ad ogni risorsa del corso (asse X). Ogni barra dell'istogramma rappresenta una specifica risorsa del corso. 
        <br />
        Cliccando sull'&quot;icona occhio&quot; nel menu di sinistra, l'utente potr&agrave; vedere i dettagli per una specifica risorsa. Questo genera la seguente rappresentazione.
    </p>
    <p>
        <b>Risorse: dettagli delle risorse per gli studenti</b>
    </p>
    <p>
        <img src="images/help/resources_resource_details_on_students.png" width="500" height="325">
        <br>
        <em>[Risorse: dettagli delle risorse per gli studenti]</em>
    </p>
    <p>
        La figura <em>[Risorse: dettagli delle risorse per gli studenti]</em>
        presenta una panoramica degli accessi degli studenti a una particolare risorsa. Le date sono rappresentate sull'asse X e gli studenti sull'asse Y.
    </p>
    <p>
        <b>Attivit&agrave;: Panoramica su quiz e compiti</b>
    </p>
    <p>
        <img src="images/help/activities_assignments.png" width="500" height="331">
        <br>
        <em>[Attivit&agrave;: Panoramica sui compiti]</em>
    </p>
    <p>
        Il grafico nella figura <em>[Attivit&agrave;: Panoramica sui compiti]</em>
        indica visivamente i voti ricevuti dagli studenti per i compiti ed i quiz. Sull'asse X ci sono tutti i compiti (o i quiz, nel caso del grafico dedicato ai quiz) e i segni indicano le consegne degli studenti. Un quadratino vuoto significa che la consegna non &egrave; stata valutata, mentre il quadratino colorato indica il voto: un voto basso sar&agrave; illustrato con un colore chiaro mentre un colore scuro indicher&agrave; un voto alto. 
    </p>
</div>