<?php
#******************************************************************************#

				
				#*************************************#
				#********** SANITIZE STRING **********#
				#*************************************#
				
				/**
				*
				*	Ersetzt potentiell gefährliche Steuerzeichen durch HTML-Entities
				*	Entfernt vor und nach einem String Whitespaces
				*
				*	@param	String	$value		Der zu bereinigende String
				*
				*	@return	String|NULL				Der bereinigte String | NULL wenn $value kein String ist
				*
				*/
				function sanitizeString($value) {
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug sanitizeString'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$value') <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					if( is_string($value) === true ) {
						/*
							SCHUTZ GEGEN EINSCHLEUSUNG UNERWÜNSCHTEN CODES:
							Damit so etwas nicht passiert: <script>alert("HACK!")</script>
							muss der empfangene String ZWINGEND entschärft werden!
							htmlspecialchars() wandelt potentiell gefährliche Steuerzeichen wie
							< > " & in HTML-Code um (&lt; &gt; &quot; &amp;).
							
							Der Parameter ENT_QUOTES wandelt zusätzlich einfache ' in &apos; um.
							Der Parameter ENT_HTML5 sorgt dafür, dass der generierte HTML-Code HTML5-konform ist.
							
							Der 1. optionale Parameter regelt die zugrundeliegende Zeichencodierung 
							(NULL=Zeichencodierung wird vom Webserver übernommen)
							
							Der 2. optionale Parameter bestimmt die Zeichenkodierung
							
							Der 3. optionale Parameter regelt, ob bereits vorhandene HTML-Entities erneut entschärft werden
							(false=keine doppelte Entschärfung)
						*/
						$value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, double_encode:false);
						
						/*
							trim() entfernt VOR und NACH einem String (aber nicht mitten drin) 
							sämtliche sog. Whitespaces (Leerzeichen, Tabs, Zeilenumbrüche)
						*/
						$value = trim($value);
					}					
					
					return $value;
					#********** LOCAL SCOPE END **********#
				}
				
				
				#*************************************************************#
				
				
				#*******************************************#
				#********** VALIDATE INPUT STRING **********#
				#*******************************************#
				
				function validateInputString($value, $mandatory=true, $minLength=INPUT_MIN_LENGTH, $maxLength=INPUT_MAX_LENGTH_SHORT_TEXT) {
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug validateInputString'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$value' [$minLength | $maxLength]) <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					#********** MANDATORY CHECK **********#
					/*
						Da ein zu prüfender String nicht zwangsläufig aus einem Formular,
						sondern beispielswesie auch aus einem JSON-Objekt stammen könnte, sollten
						hier auch NULL-Werte mit geprüft werden.
					*/
					if( $mandatory === true AND ($value === '' OR $value === NULL) ) {
						// Fehlerfall
						return 'Dies ist ein Pflichtfeld!';
					

					#********** MAXIMUM LENGTH CHECK **********#
					/*
						Da die Felder in der Datenbank oftmals eine Längenbegrenzung besitzen,
						die Datenbank aber bei Überschreiten dieser Grenze keine Fehlermeldung
						ausgibt, sondern alles, das über diese Grenze hinausgeht, stillschweigend 
						abschneidet, muss vorher eine Prüfung auf diese Maximallänge durchgeführt 
						werden. Nur so kann dem User auch eine entsprechende Fehlermeldung ausgegeben
						werden.
					*/
					/*
						mb_strlen() erwartet als Datentyp einen String. Wenn (später bei der OOP)
						jedoch ein anderer Datentyp wie Integer oder Float übergeben wird, wirft
						mb_strlen() einen Fehler. Da es ohnehin keinen Sinn maht, einen Zahlenwert
						auf seine Länge (Anzahl der Zeichen) zu prüfen, wird diese Prüfung nur für
						den Datentyp 'String' durchgeführt.
					*/
					} elseif( is_string($value) === true AND mb_strlen($value) > $maxLength ) {
						// Fehlerfall
						return "Darf maximal $maxLength Zeichen lang sein!";

					
					#********** MINIMUM LENGTH CHECK **********#
					/*
						Es gibt Sonderfälle, bei denen eine Mindestlänge für einen Userinput
						vorgegeben ist, beispielsweise bei der Erstellung von Passwörtern.
						Damit nicht-Pflichtfelder aber auch weiterhin leer sein dürfen, muss
						die Mindestlänge als Standardwert mit 0 vorbelegt sein.
						
						Bei einem optionalen Feldwert, der gleichzeitig eine Mindestlänge
						einhalten muss, darf die Prüfung keine Leersrtings validieren, da 
						diese nie die Mindestlänge erfüllen und somit der Wert nicht mehr 
						optional wäre.
					*/
					/*
						mb_strlen() erwartet als Datentyp einen String. Wenn (später bei der OOP)
						jedoch ein anderer Datentyp wie Integer oder Float übergeben wird, wirft
						mb_strlen() einen Fehler. Da es ohnehin keinen Sinn macht, einen Zahlenwert
						auf seine Länge (Anzahl der Zeichen) zu prüfen, wird diese Prüfung nur für
						den Datentyp 'String' durchgeführt.
					*/
					} elseif( is_string($value) === true AND $value !== '' AND mb_strlen($value) < $minLength ) {
						// Fehlerfall
						return "Muss mindestens $minLength Zeichen lang sein!";
						
						
					#********** INPUT STRING IS VALID **********#	
					} else {
						// Erfolgsfall
						return NULL;
					}
					#********** LOCAL SCOPE END **********#
				}
				
				
				#*************************************************************#
				
				
				#*******************************************#
				#********** VALIDATE EMAIL FORMAT **********#
				#*******************************************#
				
				function validateEmail($value) {
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug validateEmail'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$value') <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					#********** MANDATORY CHECK **********#
					$error = validateInputString($value);
					if( $error !== NULL ) {
						// Fehlerfall
						return $error;
						
						
					#********** VALIDATE EMAIL ADDRESS FORMAT **********#	
					} elseif( filter_var($value, FILTER_VALIDATE_EMAIL) ===  false ) {
						// Fehlerfall
						return 'Dies ist keine gültige Email-Adresse!';
					
					
					#********** EMAIL ADDRESS FORMAT IS VALID **********#	
					} else {
						// Erfolgsfall
						return NULL;
					}
					#********** LOCAL SCOPE END **********#
				}
				
				
				#*************************************************************#
				
				
				#**********************************#
				#********** IMAGE UPLOAD **********#
				#**********************************#				
				
				/**
				*
				*	Validiert ein auf den Server geladenes Bild, generiert einen unique Dateinamen
				*	sowie eine sichere Dateiendung und verschiebt das Bild in ein anzugebendes Zielverzeichnis.
				*	Validiert werden der aus dem Dateiheader ausgelesene MIME-Type, die aus dem Dateiheader
				*	ausgelesene Bildgröße in Pixeln sowie die auf Dateiebene ermittelte Dateigröße. 
				*	Der Dateiheader wird außerdem auf Plausibilität geprüft.
				*
				*	@param	String	$fileTemp															Der temporäre Pfad zum hochgeladenen Bild im Quarantäneverzeichnis
				*	@param	Integer	$imageMaxHeight			=IMAGE_MAX_HEIGHT					Die maximal erlaubte Bildhöhe in Pixeln
				*	@param	Integer	$imageMaxWidth				=IMAGE_MAX_WIDTH					Die maximal erlaubte Bildbreite in Pixeln				
				*	@param	Integer	$imageMinSize				=IMAGE_MIN_SIZE					Die minimal erlaubte Dateigröße in Bytes
				*	@param	Integer	$imageMaxSize				=IMAGE_MAX_SIZE					Die maximal erlaubte Dateigröße in Bytes
				*	@param	Array		$imageAllowedMimeTypes	=IMAGE_ALLOWED_MIME_TYPES		Whitelist der zulässigen MIME-Types mit den zugehörigen Dateiendungen
				*	@param	String	$imageUploadPath			=IMAGE_UPLOAD_PATH				Das Zielverzeichnis
				*
				*	@return	Array		{'imagePath'	=>	String|NULL, 								Bei Erfolg der Speicherpfad zur Datei im Zielverzeichnis | bei Fehler NULL
				*							 'imageError'	=>	String|NULL}								Bei Erfolg NULL | Bei Fehler Fehlermeldung
				*
				*/
				function validateImageUpload( $fileTemp,
														$imageMaxHeight 			= IMAGE_MAX_HEIGHT,
														$imageMaxWidth 			= IMAGE_MAX_WIDTH,
														$imageMinSize 				= IMAGE_MIN_SIZE,
														$imageMaxSize 				= IMAGE_MAX_SIZE,
														$imageAllowedMimeTypes 	= IMAGE_ALLOWED_MIME_TYPES,
														$imageUploadPath			= IMAGE_UPLOAD_PATH
													 )
				{
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug validateImageUpload'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$fileTemp') <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					#**************************************************************************#
					#********** 1. GATHER INFORMATION FOR IMAGE FILE VIA FILE HEADER **********#
					#**************************************************************************#
					
					/*
						Die Funktion getimagesize() liest den Dateiheader einern Bilddatei aus und 
						liefert bei gültigem MIME Type ('image/...') ein gemischtes Array zurück:
						
						[0] 				Bildbreite in PX 
						[1] 				Bildhöhe in PX 
						[3] 				Einen für das HTML <img>-Tag vorbereiteten String (width="480" height="532") 
						['bits']			Anzahl der Bits pro Kanal 
						['channels']	Anzahl der Farbkanäle (somit auch das Farbmodell: RGB=3, CMYK=4) 
						['mime'] 		MIME Type
						
						Bei ungültigem MIME Type (also nicht 'image/...') liefert getimagesize() false zurück
					*/
					$imageDataArray = @getImageSize($fileTemp);
/*					
if(DEBUG_F)		echo "<pre class='debug value validateImageUpload'><b>Line " . __LINE__ . "</b>: \$imageDataArray <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_F)		print_r($imageDataArray);					
if(DEBUG_F)		echo "</pre>";				
*/
					
					#********** CHECK FOR VALID MIME TYPE **********#
					if( $imageDataArray === false ) {
						// Fehlerfall (MIME TYPE IS NO VALID IMAGE TYPE)
						
						return array('imagePath' => NULL, 'imageError' => 'Dies ist keine Bilddatei!');
						
						
					#********** FETCH FILE INFOS **********#	
					} else {
						// Erfolgsfall (MIME TYPE IS A VALID IMAGE TYPE)
						
						$imageWidth 	= sanitizeString($imageDataArray[0]);			// image WIDTH via FILE HEADER
						$imageHeight 	= sanitizeString($imageDataArray[1]);			// image HEIGHT via FILE HEADER
						$imageMimeType	= sanitizeString($imageDataArray['mime']);	// image MIME TYPE via FILE HEADER
						$fileSize		= fileSize($fileTemp);								// file SIZE in bytes from server
						
if(DEBUG_F)			echo "<p class='debug value validateImageUpload'><b>Line " . __LINE__ . "</b>: \$imageWidth: $imageWidth px <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)			echo "<p class='debug value validateImageUpload'><b>Line " . __LINE__ . "</b>: \$imageHeight: $imageHeight px <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)			echo "<p class='debug value validateImageUpload'><b>Line " . __LINE__ . "</b>: \$imageMimeType: $imageMimeType <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)			echo "<p class='debug value validateImageUpload'><b>Line " . __LINE__ . "</b>: \$fileSize: " . round($fileSize/1024, 2) . "kB <i>(" . basename(__FILE__) . ")</i></p>\n";
						
					} // 1. GATHER INFORMATION FOR IMAGE FILE VIA FILE HEADER END
					#**************************************************************************#
					
					
					#*****************************************#
					#********** 2. IMAGE VALIDATION **********#
					#*****************************************#				
					
					#********** VALIDATE PLAUSIBILITY OF FILE HEADER **********#
					/*
						Diese Prüfung setzt darauf, dass ein maniplulierter Dateiheader nicht konsequent
						gefälscht wurde:
						Ein Hacker ändert den MimeType einer Textdatei mit Schadcode aud 'image/jpg', vergisst
						allerdings, zusätzlich weitere Einträge wie 'imageWidth' oder 'imageHeight' hinzuzufügen.
						
						Da wir den Datentyp eines im Dateiheader fehlenden Wertes nicht kennen (NULL, '', 0), 
						wird an dieser Stelle ausdrücklich nicht typsicher, sondern auf 'falsy' geprüft.
						Ein ! ('NOT') vor einem Wert oder einer Funktion negiert die Auswertung: Die Bedingung 
						ist erfüllt, wenn die Auswertung false ergibt.
					*/
					if( !$imageWidth OR !$imageHeight OR !$imageMimeType OR $fileSize < $imageMinSize ) {
						// 1. Fehlerfall: Verdächtiger Dateiheader
						return array('imagePath' => NULL, 'imageError' => 'Verdächtiger Dateiheader!');
					}					
					
					
					#********** VALIDATE ALLOWED MIME TYPES **********#
					// Whitelist mit erlaubten MIME TYPES
					// $imageAllowedMimeTypes = array('image/jpeg'=>'.jpg', 'image/jpg'=>'.jpg', 'image/gif'=>'.gif', 'image/png'=>'.png'); 
					
					/*
						Die Funktion in_array() prüft, ob eine übergebene Needle einem Wert (value) innerhalb 
						eines zu übergebenden Arrays entspricht.
						
						Die Funktion array_key_exists() prüft, ob eine übergebene Needle einem Index (key) innerhalb 
						eines zu übergebenden Arrays entspricht.
					*/
					if( array_key_exists($imageMimeType, $imageAllowedMimeTypes) === false ) {
						// 2. Fehlerfall: Unerlaubter Bildtyp
						return array('imagePath' => NULL, 'imageError' => 'Dies ist kein erlaubter Bildtyp!');
					}	
										
					
					#********** VALIDATE IMAGE WIDTH **********#
					if( $imageWidth > $imageMaxWidth ) {
						// 3. Fehlerfall: Bildbreite zu groß
						return array('imagePath' => NULL, 'imageError' => "Die Bildbreite darf maximal $imageMaxWidth Pixel betragen!");
					}
										
					
					#********** VALIDATE IMAGE HEIGHT **********#
					if( $imageHeight > $imageMaxHeight ) {
						// 4. Fehlerfall: Bildhöhe zu groß
						return array('imagePath' => NULL, 'imageError' => "Die Bildhöhe darf maximal $imageMaxHeight Pixel betragen!");
					}
										
					
					#********** VALIDATE FILE SIZE **********#
					if( $fileSize > $imageMaxSize ) {
						// 5. Fehlerfall: Dateigröße zu groß
						return array('imagePath' => NULL, 'imageError' => "Die Dateigröße darf maximal " . $imageMaxSize/1024 . "kB betragen!");
					
					} // 2. IMAGE VALIDATION END
					#**************************************************************************#
					
					
					#*************************************************************#
					#********** 3. PREPARE IMAGE FOR PERSISTANT STORAGE **********#
					#*************************************************************#					
					
					#********** GENERATE UNIQUE FILE NAME **********#
					/*
						Da der Dateiname selbst Schadcode in Form von ungültigen oder versteckten Zeichen,
						doppelte Dateiendungen (dateiname.exe.jpg) etc. beinhalten kann, darüberhinaus ohnehin 
						sämtliche, nicht in einer URL erlaubten Sonderzeichen und Umlaute entfernt werden müssten 
						sollte der Dateiname aus Sicherheitsgründen komplett neu generiert werden.
						
						Hierbei muss außerdem bedacht werden, dass die jeweils generierten Dateinamen unique
						sein müssen, damit die Dateien sich bei gleichem Dateinamen nicht gegenseitig überschreiben.
					*/
					/*
						- 	mt_rand() stellt die verbesserte Version der Funktion rand() dar und generiert 
							Zufallszahlen mit einer gleichmäßigeren Verteilung über das Wertesprektrum. Ohne zusätzliche
							Parameter werden Zahlenwerte zwischen 0 und dem höchstmöglichem von mt_rand() verarbeitbaren 
							Zahlenwert erzeugt.
						- 	str_shuffle() mischt die Zeichen eines übergebenen Strings zufällig durcheinander.
						- 	microtime() liefert einen Timestamp mit Millionstel Sekunden zurück (z.B. '0.57914300 163433596'),
							aus dem für eine URL-konforme Darstellung der Dezimaltrenner und das Leerzeichen entfernt werden.
					*/
					$fileName = mt_rand() . '_' . str_shuffle('abcdefghijklmnopqrstuvwxyz__--00112233445566778899') . str_replace( array('.', ' '), '', microtime() );
					
					
					#********** GENERATE FILE EXTENSION **********#
					/*
						Aus Sicherheitsgründen wird nicht die ursprüngliche Dateinamenerweiterung aus dem
						Dateinamen verwendet, sondern eine vorgenerierte Dateiendung aus dem Array der 
						erlaubten MIME Types.
						Die Dateiendung wird anhand des ausgelesenen MIME Types [key] ausgewählt.
					*/
					$fileExtension = $imageAllowedMimeTypes[$imageMimeType];

					
					#********** GENERATE FILE TARGET **********#
					/*
						Endgültigen Speicherpfad auf dem Server generieren:
						destinationPath/fileName.fileExtension
					*/
					$fileTarget = $imageUploadPath . $fileName . $fileExtension;
					
if(DEBUG_V)		echo "<p class='debug value hint validateImageUpload'><b>Line " . __LINE__ . "</b>: \$fileTarget: $fileTarget <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					// 3. PREPARE IMAGE FOR PERSISTANT STORAGE END
					#**************************************************************************#
					
					
					#********************************************************#
					#********** 4. MOVE IMAGE TO FINAL DESTINATION **********#
					#********************************************************#
					/*
						move_uploaded_file() verschiebt eine hochgeladene Datei an einen 
						neuen Speicherort und benennt die Datei um
					*/
					if( @move_uploaded_file($fileTemp, $fileTarget) === false ) {
						// 6. Fehlerfall: Bild konnte nicht verschoben werden
						return array('imagePath' => NULL, 'imageError' => 'Beim Speichern des Bildes ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.'); 
					
					} else {
						// Erfolgsfall
						return array('imagePath' => $fileTarget, 'imageError' => NULL); 

					} // 4. MOVE IMAGE TO FINAL DESTINATION END
					#**************************************************************************#	
					
					
					#********** LOCAL SCOPE END **********#
				}
				
				
				#*************************************************************#				
				

#******************************************************************************#