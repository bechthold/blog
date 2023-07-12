<?php
#**********************************************************************************#
    // region page configuration

            #****************************************#
            #********** PAGE CONFIGURATION **********#
            #****************************************#

            /*
                include(Pfad zur Datei): Bei Fehler wird das Skript weiter ausgeführt. Problem mit doppelter Einbindung derselben Datei
                require(Pfad zur Datei): Bei Fehler wird das Skript gestoppt. Problem mit doppelter Einbindung derselben Datei
                include_once(Pfad zur Datei): Bei Fehler wird das Skript weiter ausgeführt. Kein Problem mit doppelter Einbindung derselben Datei
                require_once(Pfad zur Datei): Bei Fehler wird das Skript gestoppt. Kein Problem mit doppelter Einbindung derselben Datei
            */
            require_once('./include/config.inc.php');
            require_once('./include/form.inc.php');
            require_once('./include/db.inc.php');
            require_once('./include/dateTime.inc.php');


    // endregion page configuration
#**********************************************************************************#
    // region output buffering

    #**************************************#
    #********** OUTPUT BUFFERING **********#
    #**************************************#

    /*
        Output Buffering erstellt auf dem Server einen Speicherbereich, in dem Frontend-Ausgaben
        gespeichert (und nicht sofort im Frontend ausgegeben) werden, bis der Buffer-Inhalt
        explizit gesendet werden soll.

        Hat man beispielsweise Probleme mit der Fehlermeldung
        "Warning: Cannot modify header information - headers already sent by
        (output started at /some/file.php:12) in /some/file.php on line 23",
        hilft ein Buffering des Header-Versands. Hiermit wird der Header solange nicht gesendet, bis das PHP-Skript
        eine explizite Anweisung dazu findet, bspw. ob_end_flush() ODER automatisch am Ende des Skripts.

        Diese Funktion ob_start() aktiviert die Ausgabepufferung. Während die Ausgabepufferung aktiv ist,
        werden Skriptausgaben (inklusive der Headerinformationen) nicht direkt an den Client
        weitergegeben, sondern in einem internen Puffer gesammelt.
    */
    if( ob_start() === false ) {
        // Fehlerfall
if(DEBUG)		echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Starten des Output Bufferings! <i>(" . basename(__FILE__) . ")</i></p>\r\n";

    } else {
    // Erfolgsfall
if(DEBUG)		echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Output Buffering erfolgreich gestartet. <i>(" . basename(__FILE__) . ")</i></p>\r\n";
    }
    // endregion output buffering
#**********************************************************************************#
    // region regenerate session ID

    #*******************************************#
    #********** REGENERATE SESSION ID **********#
    #*******************************************#


    #********** PREPARE SESSION **********#
    // Der Sessionname sollte unique sein (beispielsweise aus dem Domainnamen der Webseite (ohne www., .com, .de etc.) bestehen)
    /*
        Für die Fortsetzung der Session muss hier der gleiche Name ausgewählt werden,
        wie beim Login-Vorgang, damit die Seite weiß, welches Cookie sie vom Client auslesen soll
    */
    session_name(SESSION_NAME);

    #********** START/CONTINUE SESSION **********#
    /*
        Der Befehl session_start() liest zunächst ein Cookie aus dem Browser des Clients aus,
        das dem Namen des im ersten Schritts gesetzten Sessionnamens entspricht. Existiert
        dieses Cookie, wird aus ihm der Name der zugehörigen Sessiondatei ausgelesen und geprüft,
        ob diese auf dem Server existiert. Ist beides der Fall, wird die bestehende Session fortgesetzt.

        Existieren Cookie oder Sessiondatei nicht, wird an dieser Stelle eine neue Session
        gestartet: Der Browser erhält ein frisches Cookie mit dem oben gesetzten Namen, und auf dem Server
        wird eine neue, leere Sessiondatei erstellt, deren Dateinamen in das Cookie geschrieben wird.
    */
    session_start();

/*
if(DEBUG_V)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_SESSION <i>(" . basename(__FILE__) . ")</i>:<br>\n";
if(DEBUG_V)	print_r($_SESSION);
if(DEBUG_V)	echo "</pre>";
*/

    #*******************************************#
    #********** CHECK FOR VALID LOGIN **********#
    #*******************************************#

    /*
        Ohne erfolgten Login ist das SESSION-Array an dieser Stelle leer.
        Bei erfolgtem Login beinhaltet das SESSION-Array an dieser Stelle
        den beim Login-Vorgang vergebenen Index 'ID', dessen Existenz an
        dieser Stelle geprüft wird.
    */
            #********** NO VALID LOGIN **********#
            if(isset($_SESSION['ID']) === false OR $_SESSION['IPAddress'] !== $_SERVER['REMOTE_ADDR']) {
// Fehlerfall (Seitenaufrufer ist nicht eingeloggt)
if(DEBUG)	    echo "<p class='debug auth hint'><b>Line " . __LINE__ . "</b>: Seitenaufrufer ist nicht eingeloggt. <i>(" . basename(__FILE__) . ")</i></p>\n";

                #********** DELETE EMPTY SESSION **********#
                /*
                    Da jeder Seitenaufruf ohne Login eine neue leere Sessiondatei erzeugt,
                    wird diese an dieser Stelle wieder gelöscht. So wird verhindert, dass
                    der Server im Laufe der Zeit mit vielen unnötigen leeren Sessiondateien
                    zugemüllt wird.
                */
                session_destroy();

                // Flag zur weiteren Verwendung setzen
                $loggedIn = false;

            #********** VALID LOGIN **********#
            } else {
// Erfolgsfall (Seitenaufrufer ist eingeloggt)
if(DEBUG)		echo "<p class='debug auth ok'><b>Line " . __LINE__ . "</b>: Seitenaufrufer ist eingeloggt. <i>(" . basename(__FILE__) . ")</i></p>\n";

                /*
                    SICHERHEIT: Um Cookiediebstahl oder Session Hijacking vorzubeugen, wird nach erfolgreicher
                    Authentifizierung eine neue Session-ID vergeben. Ein Hacker, der zuvor ein Cookie mit einer
                    gültigen Session-ID erbeutet hat, kann dieses nun nicht mehr benutzen.
                    Die Session-ID muss bei jedem erfolgreichem Login und bei jedem Logout erneuert werden, um
                    einen effektiven Schutz zu bieten.

                    Um die alte Session mit der alten (abgelaufenen) ID gleich zu löschen und eine neue Session
                    mit einer neuen ID zu generieren, muss session_regenerate_id() den optionalen Parameter
                    delete_old_session=true erhalten.
                */
                session_regenerate_id(true);

                $userID     = $_SESSION['ID'];

                // Flag zur weiteren Verwendung setzen
                $loggedIn = true;
            }

    // endregion regenerate session ID
#**********************************************************************************#
    // region initialize variable

    #******************************************#
    #********** INITIALIZE VARIABLES **********#
    #******************************************#

        $errorLogin = NULL;



    // endregion initialize variable
#**********************************************************************************#
    // region system array $_SERVER


    #*******************************************#
    #********** SYSTEM ARRAY $_SERVER **********#
    #*******************************************#
/*
if(DEBUG_V)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_SERVER <i>(" . basename(__FILE__) . ")</i>:<br>\n";
if(DEBUG_V)	print_r($_SERVER);
if(DEBUG_V)	echo "</pre>";
*/


    // endregion system array $_SERVER
#**********************************************************************************#
    // region process form login

    #****************************************#
    #********** PROCESS FORM LOGIN **********#
    #****************************************#

    #********** PREVIEW POST ARRAY **********#
/*
if(DEBUG_V)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_POST <i>(" . basename(__FILE__) . ")</i>:<br>\n";
if(DEBUG_V)	print_r($_POST);
if(DEBUG_V)	echo "</pre>";
*/
    #****************************************#

            // Schritt 1 FORM: Prüfen, ob Formular gesendet wurde
            if (isset($_POST['formLogin']) === true) {
if(DEBUG)	echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'Login' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";

            // Schritt 2 FORM: Formulardaten auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Daten werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";

                $userEmailForm  = sanitizeString($_POST['userEmailForm']);
                $passwordForm   = sanitizeString($_POST['passwordForm']);
if(DEBUG_V)	echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$userEmailForm: $userEmailForm <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)	echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$passwordForm: $passwordForm <i>(" . basename(__FILE__) . ")</i></p>\n";

            // Schritt 3 FORM: Feldvalidierung, Feldvorbelegung, Final Form Validation
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";

                $errorUserEmail     = validateEmail($userEmailForm);
                $errorPassword       = validateInputString($passwordForm, minLength: 4);
                if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$errorUserEmail: $errorUserEmail <i>(" . basename(__FILE__) . ")</i></p>\n";
                if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$errorPassword: $errorPassword <i>(" . basename(__FILE__) . ")</i></p>\n";

                #********** FINAL FORM VALIDATION **********#
                if($errorUserEmail !== NULL OR $errorPassword !== NULL) {
                    // Fehlerfall
if(DEBUG)			echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";

                    // NEUTRALE Fehlermeldung für User
                    $errorLogin = 'Loginname oder Passwort falsch!';

                    // Bei einem Login-Formular werden keine Feldvorbelegungen vorgenommen.
                } else {
                    // Erfolgsfall
if(DEBUG)			echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular ist formal fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>\n";

// Schritt 4 FORM: Formulardaten weiterverarbeiten


                    #*****************************************#
                    #********** VALIDATE LOGIN DATA **********#
                    #*****************************************#

                    #********** FETCH USER DATA FROM DATABASE BY EMAIL **********#
                    // Schritt 1 DB: DB-Verbindung herstellen
                    $PDO = dbConnect(DB_NAME);

                    // Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
                    $sql        = 'SELECT userID, userPassword, userFirstName, userLastName FROM users 
                                    WHERE userEmail = :userEmail';

                    $params     = array('userEmail' => $userEmailForm);

                    // Schritt 3 DB: Prepared Statements
                    try {
                        // Prepare: SQL-Statement vorbereiten
                        $PDOStatement = $PDO->prepare($sql);

                        // Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
                        $PDOStatement->execute($params);
                    } catch (PDOException $error) {
if(DEBUG) 				echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                        $dbError = 'Fehler beim Zugriff auf die Datenbank!';
                    }

                    // Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
                    /*
                        Bei lesenden Operationen wie SELECT und SELECT COUNT:
                        Abholen der Datensätze bzw. auslesen des Ergebnisses
                    */
                    $row = $PDOStatement->fetch($PDO::FETCH_ASSOC);

if(DEBUG_V)			echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$row <i>(" . basename(__FILE__) . ")</i>:<br>\n";
if(DEBUG_V)			print_r($row);
if(DEBUG_V)			echo "</pre>";

                    // DB-Verbindung schließen
if(DEBUG)			echo "<p class='debug DB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";

                    unset($PDO);

                    #********** 1. VALIDATE EMAIL **********#
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Validiere Email-Adresse... <i>(" . basename(__FILE__) . ")</i></p>\n";

                    /*
                        In $row ist nur dann ein Datensatz enthalten, wenn der Datensatz im Feld userRegHash
                        einen gültigen regHash beinhaltet UND der Timestamp im Feld userRegTimeStamp NICHT älter
                        als 24 Stunden ist.
                        Alle Datensätze, bei denen der userRegtimeStamp älter als 24 Stunden ist, würden über
                        einen sog. Cronjob alle 24 Stunden aus der DB gelöscht werden.
                    */
                    /*
                        Wenn ein passender Datensatz gefunden wurde, liefert $PDOStatement->fetch() an dieser
                        Stelle ein eindimensionales Array mit den ausgelesenen Datenfeldwerten zurück.
                        Wenn KEIN passender Datensatz gefunden wurde, enthält $row an dieser Stelle false.
                    */
                    if ($row === false) {
                        //Fehlerfall
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Die Email-Adresse '$userEmailForm' wurde nicht in der DB gefunden! <i>(" . basename(__FILE__) . ")</i></p>\n";

                        // NEUTRALE Fehlermeldung für User
                        $errorLogin = 'Loginname oder Passwort falsch!';

                    } else {
                        // Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Die Email-Adresse '$userEmailForm' wurde in der DB gefunden. <i>(" . basename(__FILE__) . ")</i></p>\n";


							#********** 2. VALIDATE PASSWORD **********#
if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Validiere Passwort... <i>(" . basename(__FILE__) . ")</i></p>\n";

                            /*
                                Die Funktion password_verify() vergleicht einen String mit einem mittels
                                password_hash() verschlüsseltem Passwort. Die Rückgabewerte sind true oder false.
                            */
                            if (password_verify($passwordForm, $row['userPassword']) === false) {
                                // Fehlerfall
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Passwort aus dem Formular stimmt nicht mit dem Passwort aus der DB überein! <i>(" . basename(__FILE__) . ")</i></p>\n";

                                // NEUTRALE Fehlermeldung für User
                                $errorLogin = 'Loginname oder Passwort falsch!';

                            } else {
                                // Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Passwort aus dem Formular stimmt mit dem Passwort aus der DB überein. <i>(" . basename(__FILE__) . ")</i></p>\n";

                            #********** 3. PROCESS LOGIN **********#
if(DEBUG)					echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Login wird durchgeführt... <i>(" . basename(__FILE__) . ")</i></p>\n";

                            #********** PREPARE SESSION **********#
                            // -- Der Sessionname wurde bereits am Anfang der Seite gesetzt --
                            // Der Sessionname sollte unique sein (beispielsweise aus dem Domainnamen der Webseite (ohne www., .com, .de etc.) bestehen)
                            // session_name('authentication');


                            #********** START SESSION **********#
                            /*
                                Schlägt das Starten der Session fehl, gibt session_start() false zurück
                                Keine Session = Kein Login
                            */
                            if (session_start() === false) {
                                // Fehlerfall
if(DEBUG)					    echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Starten der Session! <i>(" . basename(__FILE__) . ")</i></p>\n";

                            } else {
                                // Erfolgsfall
                                if (DEBUG) echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Session erfolgreich gestartet. <i>(" . basename(__FILE__) . ")</i></p>\n";

                                #********** SAVE USER DATA INTO SESSION FILE **********#
                                $_SESSION['ID'] = $row['userID'];
                                $_SESSION['IPAddress'] = $_SERVER['REMOTE_ADDR'];
                                $_SESSION['userFirstName'] = $row['userFirstName'];
                                $_SESSION['userLastName'] = $row['userLastName'];

                                if (DEBUG_V) echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_SESSION <i>(" . basename(__FILE__) . ")</i>:<br>\n";
                                if (DEBUG_V) print_r($_SESSION);
                                if (DEBUG_V) echo "</pre>";

                                #********** REDIRECT TO INTERNAL PAGE **********#
                                header('LOCATION: dashboard.php');
                            } // 3. PROCESS LOGIN END
                    } // 2. VALIDATE PASSWORD END
                } // 1. VALIDATE EMAIL END
            } // FINAL FORM VALIDATION END
        } // PROCESS FORM LOGIN END
    // endregion process form login
#**********************************************************************************#
    // region process URL parameters

    #********************************************#
    #********** PROCESS URL PARAMETERS **********#
    #********************************************#

    #********** PREVIEW GET ARRAY **********#
/*
if(DEBUG_V)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_GET <i>(" . basename(__FILE__) . ")</i>:<br>\n";
if(DEBUG_V)	print_r($_GET);
if(DEBUG_V)	echo "</pre>";
*/

    #****************************************#

            // Schritt 1 URL: Prüfen, ob Parameter übergeben wurde
            if (isset($_GET['action']) === true) {
if(DEBUG)	echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: URL-Parameter 'action' wurde übergeben. <i>(" . basename(__FILE__) . ")</i></p>\n";

            // Schritt 2 URL: Parameterwert auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Parameterwert wird ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";

                $action = sanitizeString($_GET['action']);
if(DEBUG_V)	echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$action: $action <i>(" . basename(__FILE__) . ")</i></p>\n";

                // Schritt 3 URL: Je nach erlaubtem(!) Parameterwert verzweigen

                #********** LOGOUT **********#
                if ($action === 'logout') {
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Logout wird durchgeführt... <i>(" . basename(__FILE__) . ")</i></p>\n";

                    // Schritt 4 URL: Parameterwert weiterverarbeiten (in jedem Zweig individuell)

						#********** PROCESS LOGOUT **********#
						// 1. Session löschen
                        session_destroy();

                        // 2. User auf öffentliche Seite umleiten
                        header('LOCATION: index.php');

                        // 3. Fallback, falls die Umleitung per HTTP-Header ausgehebelt werden sollte
                        exit();
                } // BRANCHING END
            } // PROCESS URL PARAMETERS END

            // Schritt 1 URL: Prüfen, ob Parameter übergeben wurde
            if (isset($_GET['category']) === true) {
if(DEBUG)	echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: URL-Parameter 'category' wurde übergeben. <i>(" . basename(__FILE__) . ")</i></p>\n";

            // Schritt 2 URL: Parameterwert auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Parameterwert wird ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";

            $categoryUrlParameter = sanitizeString($_GET['category']);
if(DEBUG_V)	echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$category: $categoryUrlParameter <i>(" . basename(__FILE__) . ")</i></p>\n";

} // PROCESS URL PARAMETERS END

    // endregion process URL parameters
#**********************************************************************************#
    //region fetch articles from DB

    #******************************************************#
    #********** PROCESS FETCH CATEGORIES FROM DB **********#
    #******************************************************#

if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese Articles aus DB aus... <i>(" . basename(__FILE__) . ")</i></p>\n";


            // Schritt 1 DB: DB-Verbindung herstellen
            $PDO = dbConnect(DB_NAME);

            // Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
            $sql            = 'SELECT  blogHeadline, 
                                       blogImagePath, 
                                       blogImageAlignment, 
                                       blogContent, 
                                       blogDate,
                                       catID,
                                       catLabel, 
                                       userFirstName, 
                                       userLastName 
                                FROM blogs 
                                INNER JOIN categories USING (catID)
                                INNER JOIN users USING (userID)';

            if (isset($categoryUrlParameter)) {
                $sql .= 'WHERE catID = :catID';
if(DEBUG)	    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese Articles aus DB aus. Die Einträge werden bei der Kategorie $categoryUrlParameter gefiltert <i>(" . basename(__FILE__) . ")</i></p>\n";
                $params         = array('catID' => $categoryUrlParameter);
            } else {
                $sql .= 'ORDER BY blogDate DESC';
if(DEBUG)	    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese Articles aus DB aus. Alle Einträge anzeigen<i>(" . basename(__FILE__) . ")</i></p>\n";
                $params         = array();
            }


            // Schritt 3 DB: Prepared Statements
            try {
                $PDOStatement = $PDO->prepare($sql);

                // Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
                $PDOStatement->execute($params);

            } catch(PDOException $error) {
if(DEBUG) 	echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                $dbError = 'Fehler beim Zugriff auf die Datenbank!';
            }

            // Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
            /*
                Bei lesenden Operationen wie SELECT und SELECT COUNT:
                Abholen der Datensätze bzw. auslesen des Ergebnisses
            */
            $articles = $PDOStatement->fetchAll(PDO::FETCH_ASSOC);

/*
if(DEBUG_V)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$articles <i>(" . basename(__FILE__) . ")</i>:<br>\n";
if(DEBUG_V)	print_r($articles);
if(DEBUG_V)	echo "</pre>";
*/

            // DB-Verbindung schließen
            if(DEBUG)	echo "<p class='debug DB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";

            unset($PDO);



    //endregion fetch articles from DB
#**********************************************************************************#
    // region fetch categories from DB

#******************************************************#
#********** PROCESS FETCH CATEGORIES FROM DB **********#
#******************************************************#

if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese Categories aus DB aus... <i>(" . basename(__FILE__) . ")</i></p>\n";

            // Schritt 1 DB: DB-Verbindung herstellen
            $PDO = dbConnect(DB_NAME);

            // Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
            $sql            = 'SELECT catID, catLabel FROM categories';

            $params         = array();

            // Schritt 3 DB: Prepared Statements
            try {
                $PDOStatement = $PDO->prepare($sql);

                // Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
                $PDOStatement->execute($params);

            } catch(PDOException $error) {
if(DEBUG) 	echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                $dbError = 'Fehler beim Zugriff auf die Datenbank!';
            }

            // Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
            /*
                Bei lesenden Operationen wie SELECT und SELECT COUNT:
                Abholen der Datensätze bzw. auslesen des Ergebnisses
            */
            $categories = $PDOStatement->fetchAll(PDO::FETCH_ASSOC);

            /*
            if(DEBUG_V)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$categories <i>(" . basename(__FILE__) . ")</i>:<br>\n";
            if(DEBUG_V)	print_r($categories);
            if(DEBUG_V)	echo "</pre>";
            */

            // DB-Verbindung schließen
if(DEBUG)	echo "<p class='debug DB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";

            unset($PDO);

    // endregion fetch categories from DB
#**********************************************************************************#
?>


<!doctype html>

<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blogs</title>

    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/debug.css">

    <style>
        main {
            width: 50%;
        }
        aside {
            width: 50%;
        }
    </style>

</head>

<body>

<!-- -------- PAGE HEADER START -------- -->
    <br>
    <header class="fright loginheader">

        <!-- -------- LOGIN FORM START -------- -->
        <?php if($loggedIn === false): ?>
        <form action="<?= $_SERVER['SCRIPT_NAME']?>" method="POST">
            <input type="hidden" name="formLogin">
            <fieldset>
                <legend>Login</legend>
                <span class="error"><?= $errorLogin?></span><br>
                <input class="short" type="text" name="userEmailForm" placeholder="Email-Adresse">
                <input class="short" type="password" name="passwordForm" placeholder="Passwort">
                <input class="short" type="submit" value="Anmelden">
            </fieldset>
        </form>
        <!-- -------- LOGIN FORM END -------- -->

        <?php else: ?>
            <p><a href="?action=logout">Logout</a></p>
            <p><a href="dashboard.php">Zum Dashboard >></a></p>
        <?php endif ?>
    </header>
    <div class="clearer"></div>

    <hr>
    <!-- -------- PAGE HEADER END -------- -->

    <main class="fleft">
        <h1>PHP-Project Blog</h1>
        <p><a href="<?php echo $_SERVER['SCRIPT_NAME']?>">Alle Einträge anzeigen</a></p>


        <!-- -------- USER MESSAGES START -------- -->
        <?php if(isset($error)): ?>
            <h4 class="error"><?php echo $error ?></h4>
        <?php elseif(isset($success)): ?>
            <h4 class="success"><?php echo $success ?></h4>
        <?php elseif(isset($info)): ?>
            <h4 class="info"><?php echo $info ?></h4>
        <?php endif ?>
        <!-- -------- USER MESSAGES END -------- -->

        <?php foreach ($articles AS $article): ?>
            <div class="article">
                    <br>
                    <p>Kategorie: <?= $article['catLabel'] ?></p>
                    <p><?= $article['blogHeadline'] ?></p>
                    <p><?= $article['userFirstName'] ?> <?= $article['userLastName'] ?> schrieb am <?= date('d.m.Y', strtotime($article['blogDate'])) ?> um <?= date('H:m', strtotime($article['blogDate']))  ?> Uhr:</p>
                <div>
                    <?php if($article['blogImagePath'] !== NULL): ?>
                        <?php if($article['blogImageAlignment'] === 'left'): ?>
                            <img class="picture fleft" src="<?= $article['blogImagePath'] ?>" alt="picture">
                        <?php elseif ($article['blogImageAlignment'] === 'right'): ?>
                        <img class="picture fright" src="<?= $article['blogImagePath'] ?>" alt="picture">
                        <?php endif ?>
                    <?php endif ?>

                    <p><?= $article['blogContent'] ?></p>
                    <br>
                    <hr>
                    <br>
                </div>
            </div>

        <?php endforeach ?>

    </main>

    <aside class="fright">
        <h1>Categories</h1>
        <div class="categories">
            <?php foreach ($categories AS $category): ?>
                <p><a href="?category=<?= $category['catID'] ?>"><?= $category['catLabel'] ?></a></p>
                <br>
            <?php endforeach ?>
        </div>
    </aside>

    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

</body>

</html>
