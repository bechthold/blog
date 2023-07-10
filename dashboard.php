<?php
#**********************************************************************************#


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

#************************************************************************************#


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


#**********************************************************************************#

            #******************************************#
            #********** INITIALIZE VARIABLES **********#
            #******************************************#


#**********************************************************************************#
?>

<!doctype html>

<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication - Registration</title>

    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/debug.css">

</head>

<body>

<!-- -------- PAGE HEADER START -------- -->

<!-- -------- PAGE HEADER END -------- -->

<h1>Dashboard</h1>


<!-- -------- USER MESSAGES START -------- -->

<!-- -------- USER MESSAGES END -------- -->

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
