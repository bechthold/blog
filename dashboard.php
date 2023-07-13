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

         #****************************************#
         #********** SECURE PAGE ACCESS **********#
         #****************************************#


         #********** PREPARE SESSION **********#
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
         /*
            SICHERHEIT: Um Session Hijacking und ähnliche Identitätsdiebstähle zu verhindern,
            wird die IP-Adresse des sich einloggenden Users beim Loginvorgang in die Session gespeichert.
            Hier wird die aufrufende IP-Adresse erneut ermittelt und mit der in der Session gespeicherten
            IP-Adresse abgeglichen.
            Eine IP-Adresse zu fälschen ist nahezu unmöglich. Wenn sich also ein Cookie-Dieb von einer
            anderen IP-Adresse als der beim Loginvorgang aktuellen aus einloggen will, wird ihm an dieser Stelle
            der Zugang verweigert und der Login muss erneut durchgeführt werden.

            Diese Maßnahme hilft auch gegen das 'zufällige' Erraten eines fremden Sessionnamens,
            da sich die in der Sessiondatei gespeicherte IP-Adresse von der aktuell die Seite
            aufrufenden IP-Adrese unterscheidet.
         */
         #********** NO VALID LOGIN **********#
         if( isset($_SESSION['ID']) === false OR $_SESSION['IPAddress'] !== $_SERVER['REMOTE_ADDR'] ) {
            // Fehlerfall (Seitenaufrufer ist nicht eingeloggt)
if(DEBUG)	echo "<p class='debug auth err'><b>Line " . __LINE__ . "</b>: Login konnte nicht validiert werden! <i>(" . basename(__FILE__) . ")</i></p>\n";
            #********** DENY PAGE ACCESS **********#
            // 1. Session löschen
            /*
               Da jeder unberechtigte Seitenaufruf eine neue leere Sessiondatei erzeugt,
               wird diese an dieser Stelle wieder gelöscht. So wird verhindert, dass
               der Server im Laufe der Zeit mit vielen unnötigen leeren Sessiondateien
               zugemüllt wird.
            */
            session_destroy();

            // Flag zur weiteren Verwendung setzen
            $loggedIn = false;

            // 2. User auf öffentliche Seite umleiten
            /*
               Die Funktion header() versendet sofort den HTTP-Header an den Client.
               Über den HTTP-Header können diverse Verhalten gesteuert werden, wie
               beispielsweise die automatische Weiterleitung auf eine andere Seite.

               Durch die Funktion header() wird ein String in den HTTP-Header geschrieben,
               der in diesem Fall den Befehl 'LOCATION:' sowie eine Zielseite für die
               Umleitung enthält.
            */
            header('LOCATION: index.php');

            // 3. Fallback, falls die Umleitung per HTTP-Header ausgehebelt werden sollte
            // Die Funktion 'exit()' beendet sofort die weitere Ausführung des Skripts
            exit();
         } else {
            // Erfolgsfall (Seitenaufrufer ist eingeloggt)
if(DEBUG)	echo "<p class='debug auth ok'><b>Line " . __LINE__ . "</b>: Login wurde erfolgreich validiert. <i>(" . basename(__FILE__) . ")</i></p>\n";

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

            $userID = $_SESSION['ID'];

            // Flag zur weiteren Verwendung setzen
            $loggedIn = true;
         }

// endregion regenerate session ID
#**********************************************************************************#
// region initialize variable

#******************************************#
#********** INITIALIZE VARIABLES **********#
#******************************************#

            $userFirstName                      = $_SESSION['userFirstName'];
            $userLastName                       = $_SESSION['userLastName'];

            $newArticleCategory                 = NULL;
            $newArticleTitle                    = NULL;
            $newArticlePictureAlignment         = NULL;
            $newArticlePicturePath              = NULL;
            $newArticleText                     = NULL;

            $newCategoryName                    = NULL;

            $errorNewArticleImageUpload         = NULL;
            $errorNewArticleText                = NULL;
            $errorNewArticleTitle               = NULL;

            $errorNewCategoryName               = NULL;

            $error				                  = NULL;
            $info				                     = NULL;
            $success			                     = NULL;

// endregion initialize variable
#**********************************************************************************#
// region system array $_SERVER


#*******************************************#
#********** SYSTEM ARRAY $_SERVER **********#
#*******************************************#

/*
if(DEBUG_V)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$arrayName <i>(" . basename(__FILE__) . ")</i>:<br>\n";
if(DEBUG_V)	print_r($_SERVER);
if(DEBUG_V)	echo "</pre>";
*/

// endregion system array $_SERVER
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

// endregion process URL parameters
#**********************************************************************************#
// region process new article form

            #**********************************************#
            #********** PROCESS NEW ARTICLE FORM **********#
            #**********************************************#

            #********** PREVIEW POST ARRAY **********#
/*
if(DEBUG_V)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_POST <i>(" . basename(__FILE__) . ")</i>:<br>\n";
if(DEBUG_V)	print_r($_POST);
if(DEBUG_V)	echo "</pre>";
*/
            #****************************************#

            // Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
            if (isset($_POST['newArticleForm']) === true) {
if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'New Article' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";

					// Schritt 2 FORM: Formulardaten auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Daten werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";

					$newArticleCategory         		= sanitizeString($_POST['newArticleCategory']);
					$newArticleTitle           		= sanitizeString($_POST['newArticleTitle']);
					$newArticlePictureAlignment     	= sanitizeString($_POST['newArticlePictureAlignment']);
					$newArticleText             		= sanitizeString($_POST['newArticleText']);

if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$newArticleCategory: $newArticleCategory <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$newArticleTitle: $newArticleTitle <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$newArticlePictureAlignment: $newArticlePictureAlignment <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$newArticleText: $newArticleText <i>(" . basename(__FILE__) . ")</i></p>\n";




					// Schritt 3 FORM: Feldvalidierung, Feldvorbelegung, Final Form Validation
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";

					$errorNewArticleTitle = validateInputString($newArticleTitle, mandatory: true, maxLength: INPUT_MAX_LENGTH_SHORT_TEXT);
					$errorNewArticleText = validateInputString($newArticleText, mandatory: true, maxLength: INPUT_MAX_LENGTH_LONG_TEXT);


				#********** FORM VALIDATION (FIELDS VALIDATION) **********#
				if($errorNewArticleTitle !== NULL OR $errorNewArticleText !== NULL) {
					// Fehlerfall
if(DEBUG)		echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Validation Part I. Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";

				} else {
					// Erfolgsfall
if(DEBUG)		echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Validation Part I. Das Formular ist formal fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>\n";

					#**********************************#
					#********** IMAGE UPLOAD **********#
					#**********************************#
					/*
						Da im Fall von fehlerhaften Formulareingaben kein verwaistes Bild auf
						den Server hochgeladen werden soll, findet der Bildupload erst NACH
						der finalen Formularvalidierung statt.
					*/

/*
if(DEBUG_V)		echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_FILES <i>(" . basename(__FILE__) . ")</i>:<br>\n";
if(DEBUG_V)		print_r($_FILES);
if(DEBUG_V)		echo "</pre>";
*/

						#********** CHECK IF IMAGE UPLOAD IS ACTIVE **********#
					if ($_FILES['newArticlePicture']['tmp_name'] === '') {
						// Image Upload inactive
if(DEBUG)			echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Image Upload inactive. <i>(" . basename(__FILE__) . ")</i></p>\n";
					} else {
						// Image Upload active
if(DEBUG)	    	echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Image Upload active. <i>(" . basename(__FILE__) . ")</i></p>\n";

						$validateImageUploadReturnArray = validateImageUpload($_FILES['newArticlePicture']['tmp_name']);

/*
if(DEBUG_V)			echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$validateImageUploadReturnArray <i>(" . basename(__FILE__) . ")</i>:<br>\n";
if(DEBUG_V)			print_r($validateImageUploadReturnArray);
if(DEBUG_V)			echo "</pre>";
*/

						#********** VALIDATE IMAGE UPLOAD **********#
						if ($validateImageUploadReturnArray['imageError'] !== NULL ){
							// Fehlerfall
							/*
								AUSNAHMEFEHLER in PHP: Wenn innerhalb eines Strings auf einen assoziativen Index
								zugegriffen wird, entfallen die Anführungszeichen für den Index.
							*/
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Bildupload: $validateImageUploadReturnArray[imageError] <i>(" . basename(__FILE__) . ")</i></p>\n";
							$errorNewArticleImageUpload = $validateImageUploadReturnArray['imageError'];

						} else {
                    // Erfolgsfall
                    /*
                        AUSNAHMEFEHLER in PHP: Wenn innerhalb eines Strings auf einen assoziativen Index
                        zugegriffen wird, entfallen die Anführungszeichen für den Index.
                    */
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Bild erfolgreich nach <i>'$validateImageUploadReturnArray[imagePath]'</i> auf den Server geladen. <i>(" . basename(__FILE__) . ")</i></p>\n";


                    #********** SAVE IMAGE PATH TO VARIABLE **********#
                    $newArticlePicturePath = $validateImageUploadReturnArray['imagePath'];
						} // VALIDATE IMAGE UPLOAD END
					} // IMAGE UPLOAD END
				
					#*****************************************************#


					#********** FINAL FORM NEW ARTICLE VALIDATION (AFTER IMAGE UPLOAD) **********#
					if( $errorNewArticleImageUpload !== NULL OR $errorNewArticleTitle !== NULL OR $errorNewArticleText !== NULL) {
						// Fehlerfall
if(DEBUG)			echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Validation Part II. Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";

					} else {
						// Erfolgsfall
if (DEBUG)  		echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular ist komplett fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>\n";

						// Schritt 4 FORM: Formulardaten weiterverarbeiten
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Formulardaten werden weiterverarbeitet... <i>(" . basename(__FILE__) . ")</i></p>\n";


						#********** CREATE NEW ARTICLE IN DB **********#
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Speichere New Article Daten in die DB... <i>(" . basename(__FILE__) . ")</i></p>\n";

						// Schritt 1 DB: DB-Verbindung herstellen
						$PDO = dbConnect(DB_NAME);

						// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
						$sql 		=  'INSERT INTO blogs 
										(blogHeadLine, blogImagePath, blogImageAlignment, blogContent, catID, userID)
										VALUES
										(:blogHeadLine,  :blogImagePath, :blogImageAlignment, :blogContent, :catID, :userID)';

						$params     = array(
										'blogHeadLine' => $newArticleTitle,
										'blogImagePath' => $newArticlePicturePath,
										'blogImageAlignment' => $newArticlePictureAlignment,
										'blogContent' => $newArticleText,
										'catID' => $newArticleCategory,
										'userID' => $userID
						);

						// Schritt 3 DB: Prepared Statements
						try {
							// Prepare: SQL-Statement vorbereiten
							$PDOStatement = $PDO->prepare($sql);

							// Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
							$PDOStatement->execute($params);

						} catch(PDOException $error) {
if(DEBUG) 				echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
							$error = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
						}

						// Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
						/*
							Bei schreibenden Operationen (INSERT/UPDATE/DELETE):
							Schreiberfolg prüfen anhand der Anzahl der betroffenen Datensätze (number of affected rows).
							Diese werden über die PDOStatement-Methode rowCount() ausgelesen.
							Der Rückgabewert von rowCount() ist ein Integer; wurden keine Daten verändert, wird 0 zurückgeliefert.
						*/
						$rowCount = $PDOStatement->rowCount();
if(DEBUG_V)			echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";

						if( $rowCount !== 1 ) {
							// Fehlerfall
							if(DEBUG)		echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Speichern der New Article Daten! <i>(" . basename(__FILE__) . ")</i></p>\n";

							// Fehlermeldung für User generieren
							$dbError = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';

						} else {
							// Erfolgsfall
							/*
								Bei einem INSERT die Last-Insert-ID nur nach geprüftem Schreiberfolg auslesen.
								Im Zweifelsfall wird hier sonst die zuletzt vergebene ID aus einem älteren
								Schreibvorgang zurückgeliefert.
							*/
							$newArticleID = $PDO->lastInsertID();
if (DEBUG) 				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Der Artikel erfolgreich unter ID $newArticleID gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";

							// DB-Verbindung schließen
if(DEBUG) 				echo "<p class='debug DB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
							unset($PDO);

							$success = 'Der beitrag wurde erfolgreich gespeichert';

							$newArticleTitle = NULL;
							$newArticleText = NULL;
						
						} // CREATE NEW ARTICLE IN DB END
						
					} //FORM VALIDATION (FIELDS VALIDATION) END
					
				} // FINAL FORM NEW ARTICLE VALIDATION (AFTER IMAGE UPLOAD) END	
				
			} // PROCESS NEW ARTICLE FORM END
	 
// endregion process new article form
#**********************************************************************************#
// region process new category form

               #***********************************************#
               #********** PROCESS NEW CATEGORY FORM **********#
               #***********************************************#

               #********** PREVIEW POST ARRAY **********#
/*
if(DEBUG_V)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_POST <i>(" . basename(__FILE__) . ")</i>:<br>\n";
if(DEBUG_V)	print_r($_POST);
if(DEBUG_V)	echo "</pre>";
*/
               #****************************************#

               // Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
               if( isset($_POST['newCategoryForm']) === true ) {
if(DEBUG)			echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'New Category' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";

						// Schritt 2 FORM: Formulardaten auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Daten werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";

						$newCategoryName 		= sanitizeString($_POST['newCategoryName']);

if(DEBUG_V)			echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$newCategoryName: $newCategoryName <i>(" . basename(__FILE__) . ")</i></p>\n";

						// Schritt 3 FORM: Feldvalidierung, Feldvorbelegung, Final Form Validation
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";

						$errorNewCategoryName = validateInputString($newCategoryName);

						if ($errorNewCategoryName !== NULL) {
							// Fehlerfall
if(DEBUG)		   	echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";
						} else {
							// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular ist formal fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>\n";


							#********** CHECK IF CATEGORY IS ALREADY EXISTED **********#
if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Prüfe, ob die Kategorie bereits existiert ist... <i>(" . basename(__FILE__) . ")</i></p>\n";

							// Schritt 1 DB: DB-Verbinsung herstellen
							$PDO = dbConnect(DB_NAME);


							// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
							$sql 		= 	'SELECT COUNT(catLabel) FROM categories
											WHERE catLabel = :catLabel';

							$params 	= 	array (
                                        'catLabel' => $newCategoryName
                                       );

							// Schritt 3 DB: Prepared Statements
							try {
                        // Schritt 2 DB: SQL-Statement vorbereiten
                        $PDOStatement = $PDO->prepare($sql);

                        // Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
                        $PDOStatement->execute($params);

							} catch(PDOException $error) {
if(DEBUG) 					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                        $dbError = 'Fehler beim Zugriff auf die Datenbank!';
							}

							// Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
							/*
								Bei SELECT COUNT(): Rückgabewert von COUNT() über $PDOStatement->fetchColumn() auslesen
							*/
							$count = $PDOStatement->fetchColumn();
if(DEBUG_V)				echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$count: $count <i>(" . basename(__FILE__) . ")</i></p>\n";

							// DB-Verbindung schließen
if(DEBUG)				echo "<p class='debug DB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
							unset($PDO);


							#********** FORM VALIDATION (DB VALIDATION) **********#
							if( $count !== 0 ) {
								// Fehlerfall
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Die Kategorie '$newCategoryName' ist bereits existiert! <i>(" . basename(__FILE__) . ")</i></p>\n";
								$errorNewCategoryName = 'Diese Kategorie ist bereits existiert!';

							} else {
								// Erfolgsfall
if (DEBUG) 					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Die Kategorie '$newCategoryName' ist nicht existiert. <i>(" . basename(__FILE__) . ")</i></p>\n";

								#********** CREATE NEW CATEGORY IN DB **********#
if(DEBUG)					echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Speichere New Category in die DB... <i>(" . basename(__FILE__) . ")</i></p>\n";

								// Schritt 1 DB: DB-Verbindung herstellen
								$PDO = dbConnect(DB_NAME);

								// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
								$sql        =  'INSERT INTO categories 
													(catLabel)
													VALUES
													(:catLabel)';

								$params     = array(
													'catLabel' => $newCategoryName
														);

								// Schritt 3 DB: Prepared Statements
								try {
									// Prepare: SQL-Statement vorbereiten
									$PDOStatement = $PDO->prepare($sql);

									// Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
									$PDOStatement->execute($params);

								} catch(PDOException $error) {
if(DEBUG) 						echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
									$error = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
								}

								// Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
								/*
									Bei schreibenden Operationen (INSERT/UPDATE/DELETE):
									Schreiberfolg prüfen anhand der Anzahl der betroffenen Datensätze (number of affected rows).
									Diese werden über die PDOStatement-Methode rowCount() ausgelesen.
									Der Rückgabewert von rowCount() ist ein Integer; wurden keine Daten verändert, wird 0 zurückgeliefert.
								*/
								$rowCount = $PDOStatement->rowCount();
if(DEBUG_V)					echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";

								if( $rowCount !== 1 ) {
									// Fehlerfall
if(DEBUG)		        		echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Speichern der New Category Daten! <i>(" . basename(__FILE__) . ")</i></p>\n";

									// Fehlermeldung für User generieren
									$dbError = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';

								} else {
									// Erfolgsfall
									/*
										Bei einem INSERT die Last-Insert-ID nur nach geprüftem Schreiberfolg auslesen.
										Im Zweifelsfall wird hier sonst die zuletzt vergebene ID aus einem älteren
										Schreibvorgang zurückgeliefert.
									*/
									$newArticleID = $PDO->lastInsertID();
if (DEBUG)              	echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Der Artikel erfolgreich unter ID $newArticleID gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";


									// DB-Verbindung schließen
if(DEBUG)               	echo "<p class='debug DB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
									unset($PDO);


									$success = "Die neue Kategorie mit dem Namen $newCategoryName wurde erfolgreich gespeichert";

									$newCategoryName = NULL;
								}
									
							} // FORM VALIDATION (DB VALIDATION) END
							
						} // CREATE NEW CATEGORY IN DB END
							
					} // PROCESS NEW CATEGORY FORM END
						
// endregion process new category form
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
   <title>Dashboard</title>

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
<header class="fright">

   <!-- -------- LINKS START -------- -->
   <?php if($loggedIn === true): ?>
      <p><a href="?action=logout">Logout</a></p>
      <p><a href="index.php"><< zum Frontend</a></p>

   <?php endif ?>
   <!-- -------- LINKS END -------- -->
</header>
<div class="clearer"></div>
<hr>

<h1>PHP-Project Blog - Dashboard</h1>
<p>Aktiver Benutzer: <?= $userFirstName ?> <?= $userLastName ?></p>

	<!-- -------- USER MESSAGES START -------- -->
	<?php if(isset($error)): ?>
	  <h4 class="error"><?php echo $error ?></h4>
	<?php elseif(isset($success)): ?>
	  <h4 class="success"><?php echo $success ?></h4>
	<?php elseif(isset($info)): ?>
	  <h4 class="info"><?php echo $info ?></h4>
	<?php endif ?>
	<!-- -------- USER MESSAGES END -------- -->

<!-- -------- PAGE HEADER END -------- -->

<main class="fleft">
   <h3>Neuen Blog-Eintrag verfassen</h3>

   <!-- -------- FORM BLOG-EINTRAG -------- -->
   <form action="" method="POST" enctype="multipart/form-data">

      <input type="hidden" name="newArticleForm">
      <br>
      <select style="width: 80%; height: 2em; margin-bottom: 2em;" name="newArticleCategory" id="newArticleCategory">
         <?php foreach($categories AS $category): ?>
            <option value="<?= $category['catID']?>" <?php if ($newArticleCategory == $category['catID']) echo 'selected' ?>><?= $category['catLabel'] ?></option>
         <?php endforeach ?>
      </select>

		<br>
		<span class="error"><?= $errorNewArticleTitle ?></span><br>
		<input type="text" name="newArticleTitle"  value="<?= $newArticleTitle ?>" placeholder="Uberschrift"><br>

		<div style="width: 80%; display: inline-block">
			<div style="display: inline-block">
				<p>Bild hochladen:</p>

				<!-- -------- INFOTEXT FOR IMAGE UPLOAD START -------- -->
				<p class="small">
					Erlaubt sind Bilder des Typs
					<?php $imageAllowedMimeTypes = implode(', ', array_keys(IMAGE_ALLOWED_MIME_TYPES)) ?>
					<?= strtoupper( str_replace( array('image/jpeg, ', 'image/'), '', $imageAllowedMimeTypes) ) ?>.
					<br>
					Die Bildbreite darf <?= IMAGE_MAX_WIDTH ?> Pixel nicht übersteigen.<br>
					Die Bildhöhe darf <?= IMAGE_MAX_HEIGHT ?> Pixel nicht übersteigen.<br>
					Die Dateigröße darf <?= IMAGE_MAX_SIZE/1024 ?>kB nicht übersteigen.
				</p>
				<!-- -------- INFOTEXT FOR IMAGE UPLOAD END -------- -->

				<span class="error"><?= $errorNewArticleImageUpload ?></span><br>
				<input type="file" name="newArticlePicture"><br>
			</div>

			<select name="newArticlePictureAlignment">
				<option value="left" <?php if( $newArticlePictureAlignment === 'left' ) echo 'selected'?>>align left</option>
				<option value="right" <?php if( $newArticlePictureAlignment === 'right' ) echo 'selected'?>>align right</option>
			</select>
		</div>

		<br>
		<span class="error"><?php echo $errorNewArticleText ?></span><br>
			<textarea name="newArticleText" placeholder="Text..."><?php echo $newArticleText ?></textarea>
			<div class="clearer"></div>
		<br>

		<input style="width: 80%" type="submit" value="Veröffentlichen">

   </form>


</main>

<aside class="fright">
   <h3>Neue Kategorie anlegen</h3>

   <form action="" method="POST">

      <input type="hidden" name="newCategoryForm">

      <span class="error"><?= $errorNewCategoryName ?></span><br>
      <input type="text" name="newCategoryName" value="<?= $newCategoryName ?>" placeholder="Name der Kategorie"><br>

      <input style="width: 80%" type="submit" value="Neue Kategorie anlegen">
   </form>

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
