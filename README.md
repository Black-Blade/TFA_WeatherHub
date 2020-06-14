# IPS - TFA WeatherHub Modul

Mit diesem Modul könnt ihr relativ günstig Diverse Wetterinformationen in euer IPS System bekommen. Von der Firma TFA Dostmann gibt es diverse Wettersensoren (unter der Rubrik WeatherHub), welche über ein Gateway normalerweise die Daten in die TFA eigene Cloud senden. Über die Proxy Einstellungen könnt ihr die Daten direkt an IPS leiten, die dann von diesem Modul verarbeitet werden. Somit habt ihr alle von euren Sensoren verfügbaren Werte direkt im IPS zur Verfügung.

## Inhaltsverzeichnis

1. [Voraussetzungen](#1-voraussetzungen)
2. [Kompatieble Sensoren](#2-kompatieble-Sensoren)
3. [Vorbereitung des Gateways](#3-vorbereitung-des-Gateways)
4. [Installation des Moduls und des Gateways](#4-installation-des-Moduls-und-des-gateways)
5. [Installation der Sensoren](#5-installation-der-sensoren)
6. [Besonderheiten zu Sensoren](#6-besonderheiten-zu-sensoren)
7. [GUIDs und Datenaustausch](#7-GUIDs-und-Datenaustausch)
8. [Geholfen hat uns folgendes Projekt](#8-geholfen-hat-uns-folgendes-Projekt)
9. [Garantie](#9-garantie)


## 1. Voraussetzungen:
- Ihr benötigt das TFA Weatherhub Gateway 
- und mindestens ein passenden TFA Sensor
(Für die Erstanschaffung empfiehlt es sich ein Starterset zu kaufen) 
- IPS ab 5.1


## 2. Kompatieble Sensoren:

|TFA Artikel  |Beschreibung	                                            |Anmerkung      |ID      |Im Paket seit
|:-----------:|:-------------------------------------------------------:|:-------------:|:------:|:--------------:|
|30.3313.02	  |Profi-Temperatur-Sender mit wasserfestem Kabelfühler 	|getestet	    |ID01    |09.05.2020
|30.3300.02	  |Temperatur-Sender	                                    |getestet       |ID02    |09.05.2020
|30.3303.02	  |Thermo-Hygro-Sender	                                    |getestet       |ID03    |09.05.2020
|30.3304.02	  |Thermo-Hygro-Sender mit wasserfestem	Kablelfühler        |	            |ID03    |
|30.3305.02	  |Thermo-Hygro-Sender mit Wassermelder                     |getestet       |ID04    |09.05.2020
|30.3310.02	  |Thermo-Hygro-Sender mit Poolsensor                       |               |ID06    |
|30.3306.02	  |Regensensor                                              |getestet       |ID08    |09.05.2020
|30.3302.02	  |Thermo-Hygro-Sender mit Profi-Temperatur-Kabelfühler     |               |ID09    |
|30.3307.02	  |Windsensor                                               |getestet	    |ID0B    |09.05.2020
|30.3311.02	  |Türen- und Fensterkontakt-Sensor                         |getestet       |ID10    |09.05.2020
|30.3060.01   |KLIMA@HOME                                               |               |ID11    |
|             |Gateway(HTTP)                                            |getestet       |        |09.05.2020



- Siehe auch die Übersicht von TFA: https://www.tfa-dostmann.de/media/pdf/weatherhub-erweiterungen.pdf
- Weitere Infos zum TFA WeatherHub System: https://www.tfa-dostmann.de/themenwelten/smarthome/


## 3. Vorbereitung des Gateways
Das Gateway schließt ihr innerhalb eures Netzwerkes an Netzwerk und Strom an, wie in der original Anleitung beschrieben. Danach erhält das Gateway per DHCP eine IP Adresse. Ihr installiert die zugehörige App von TFA auf eurem Handy. Darin könnt ihr das Gateway unter Einstellungen nun selbst konfigurieren. Ggf. vergebt ihr eine feste IP, aber vor allem aktiviert ihr den Proxy Server und tragt die IP Adresse eures IPS-Servers ein. Ein Port könnt ihr frei wählen wenn noch frei z.B: 3778.

Die Sensoren müssen nicht konfiguriert werden, alle Daten der Sensoren in Reichweite werden vom Gateway empfangen und ihr erhaltet die Daten dann direkt im IPS.


## 4. Installation des Moduls und des Gateways

Im Store installiert ihr das Modul(Suche nach TFA).
Als erstes wird dann unter Splitter Instanzen die Instanz "TFAGATEWAY" installiert. Ihr geht mit der rechten Maustaste auf "Splitter Instanzen", dann Objekt hinzufügen - Instanz. Im Suchfilter "TFA" eingeben und dann TFAGATEWAY auswählen. Als erstes wird dann auch eine Schnittstelle (Server Socket) automatisch angelegt, welchen ihr aktiviert und die Portadresse des Gateways von oben z.B. 3778 eintragt. In der Konfiguration der Gatewayinstanz könnt ihr das auch ggf. nachträglich wie gewohnt ändern (Button "Schnittstelle Konfigurieren/Ändern").  Die Buttons in der Instanzkonfiguration sind erstmal nicht wichtig. Für Experten: unter FIREWALL" könnt ihr die IP Adresse des Gateways eingeben damit nur von einer bestimmten IP Adresse die Daten verarbeitet werden. Mit komma getrennt auch mehrere. Unter "DEBUGGER" könnt ihr Aktivieren dass Debug Meldungen ausgegeben werden, wenn es Probleme gibt. Mit dem Button "RESET" könnt ihr ein Skript anlegen lassen, welches das Gateway Resetten kann. Dazu das Skript einfach ausführen und der Reset des Gateways wird durchgeführt.


## 5. Installation der Sensoren

Zuerst legt ihr am besten in eurem Objektbaum eine neue Kategorie an, wo ihr die TFA Sensoren anlegt, aber da haltet ihr euch an eure gewohnte Struktur. Mit rechter Maustaste auf den Objektbaum und dann "Objekt hinzufügen - Instanz" auswählen. Im Suchfilter gebt ihr wieder "TFA" ein und alle möglichen Sensoren werden angezeigt. Anhand der ID erkennt ihr welchen Sensor ihr benötigt. Die ID sind die ersten beiden Stellen des QR Codes vom Sensor. In der Instanzkonfiguration ist nun die ID des Sensors einzutragen WICHTIG: ohne die ersten beiden Stellen vom QR-Code. Es müßten dann noch 10 Stellen sein, die einzutragen sind. Ihr legt für jeden weiteren Sensor eine eigene Instanz an.

Unter den verfügbaren Menüpunkten, abhängig vom Sensor, könnt ihr aktivieren, für welche Informationen die Variablen erstellt werden sollen. Somit habt ihr nur diese Informationen in eurem Objektbaum die Ihr auch wirklich benötigt. Also aktiviert am Anfang mal eher mehr Informationen, um zu sehen was es alles gibt. Unter "SENSOR" sind immer ein paar allgemeine Informationen zum Sensor vorhanden z.B. könnt ihr dann die Variable "battery low" überwachen um rechtzeitig die Batterie auszutauschen. Im Bereich "Fehlersuche" (Debugger) kann dann wieder aktiviert werden ob Debug Meldungen ausgegeben werden sollen. Alles was mit "... Vorherige" bezeichnet ist, bedeutet: Der TFA Sensor liefert in den meisten Fällen, wenn er Daten sendet auch seinen zuletzt gesendeten Wert oder Werte. Diesen Wert wertet das Modul auch aus und speichert diesen auch in einer Variable ab. Dies wird in der Regel nicht benötigt und kann deaktiviert bleiben.

Unter "CLOUD EINSTELLUNGEN" könnt ihr festlegen, ob der Empfangene Wert noch zusätzlich zu TFA in die Cloud gesendet werden soll. Damit habt ihr die Daten zusätzlich noch bei TFA und könnt die original TFA App verwenden. Als "Cloud Host_Adress" steht im noramlfall "www.data199.com" drin und bei "URL Parameter" "/gateway/put". Sollte TFA da mal was ändern, kann hier der Zielserver geändert werden. 

So, das wars schon, spätestens nach 10 Minuten, je nach Sensor, solltet ihr nun die ersten Daten bekommen. Zum testen ggf. die Batterien am Sensor kurz entfernen und wieder einsetzen, dann sendet der Sensor sofort die aktuellen Werte. 
Unter der Instanz erscheinen nun alle Variablen die Ihr in der Konfiguration der Instanz des Sensors aktiviert habt. Ab jetzt habt ihr alle Infos des Sensors im IPS. 

Je nach Sensor gibt es dann die verschiedenen Variablen mit den eigentlichen Werten des Sensors.
Um die Variablen in eurem Webfront darzustellen, legt ihr im Normalfall Links an, welche ihr dann entsprechend benennt. Aber auch die Variablen könnt ihr selbst umbenennen wie ihr wollt. Ihr aktiviert auch bei Bedarf die Archivierung selbst und gebt den Variablen ggf. andere Profile wie ihr möchtet, so dass es in eure eigene Struktur passt. Wenn Ihr in der Instanzkonfiguration eine Variable deaktiviert, wird diese nicht automatisch gelöscht. Wenn ihr sie nicht mehr möchtet, dann bitte selbst löschen.


## 6. Besonderheiten zu Sensoren

***- ID 0b - WIND SENSOR (30.3307.02)***

Bei einem Windsensor kann zusätzlich noch ein Offset eingegeben werden, falls ihr aus welchem Grund auch immer den Sensor nicht genau nach Vorgabe Richtung Süden ausrichten könnt, kann mit dem Offset der Wert entsprechend korrigiert werden. Auch in die Cloud wird dann der korrigierte Wert übertragen, falls ihr die Cloudübertragung  aktiviert habt.


***- ID 08 - RAIN SENSOR (30.3306.02)***

Der Regensensor enthält die Variable "Regen Zähler" also die Anzahl der Kippvorgänge. Zusätzlich wird die Variable "Regen Menge" erstellt und enthält die Regenmenge. Wichtig: In der Instanzkonfiguration hier immer beide Variablen zusammen aktivieren, eine alleine wird ggf. Probleme geben (wir konnten das aber leider technisch nicht anders lösen) Standardmäßig wird die Regenmenge in Millimeter angezeigt, das entspricht auch den Litern pro Quadratmeter. An dieser Variable solltet ihr in den Archiveinstellungen die Archivierung aktivieren und die Aggregation auf Zähler stellen. Wenn ihr diese Variable dann ins Webfront verlinkt, können alle Infos zum Regen abgerufen werden.


***- HTTPGATEWAY***

Das könnt ihr mal zum testen installieren. Dann werden Daten vom Gateway abgerufen und in Variablen gespeichert. Diese werden aber nicht unbedingt benötigt. Die gleichen Informationen erhaltet ihr auch wenn ich im Browser die Weboberfläche des Gateways mit dessen IP-Adresse aufruft. Angeben in der Konfiguration müsst ihr die IP-Adresse des Gateways. 


## 7. GUIDs und Datenaustausch
|UUID                                  |Beschreibung	                                            |
|:------------------------------------:|:----------------------------------------------------------:|
|E1738F13-0E82-7B77-A13C-7C0D814C8D51  |Daten vom Kind Modul zum Gateway (Wird noch nicht benutzt)  |
|7E53E668-20E9-7CDB-459C-B22E3B16D24F  |Daten werden an die Kinder übergeben                        |



## 8. Geholfen hat uns folgendes Projekt
https://github.com/sarnau/MMMMobileAlerts 


## 9. Garantie
Wir können natürlich trotz aller Sorgfalt keine Garantie für dieses Modul und dessen mögliche Fehlfunktionen übernehmen. Wenn ihr ein Problem feststellt, teilt uns dies bitte mit, wir versuchen dem dann nachzugehen. Da wir selbst nicht alle Sensoren von TFA haben, konnten wir nicht alles testen. Wenn ihr also einen solchen Sensor (siehe obere Liste) habt, teilt uns das gerne mit wenn es Probleme gibt. Aktiviert das Debugging und sendet uns möglichst viele Daten zum Sensor. Also vielen Dank schon mal für eure Mithilfe.

Dieses Modul ist nicht von der Firma TFA offiziell erstellt worden. Es ist ein rein Privates Projekt, wobei die Verwendung des Logos und die Veröffentlichung dieses Moduls mit TFA abgestimmt wurde.


Ansonsten wünschen wir viel Spaß mit dem Modul und euren Sensoren
