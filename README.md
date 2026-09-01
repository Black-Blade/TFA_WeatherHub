# IPS - TFA WeatherHub Modul (Version 2)

Mit diesem Modul könnt ihr relativ günstig diverse Wetterinformationen in euer IPS System bekommen. Von der Firma TFA Dostmann gibt es diverse Wettersensoren (unter der Rubrik WeatherHub), welche über ein Gateway normalerweise die Daten in die TFA eigene Cloud senden. Über die Proxy Einstellungen könnt ihr die Daten direkt an IPS leiten, die dann von diesem Modul verarbeitet werden. Somit habt ihr alle von euren Sensoren verfügbaren Werte direkt im IPS zur Verfügung.

> **Version 2 ist neu und noch nicht auf allen Sensoren im Dauerbetrieb gelaufen.** Sie ist eine eigenständige Bibliothek und lässt sich **neben** Version 1 installieren. Eure vorhandenen Instanzen bleiben davon unberührt und laufen weiter. Wer auf Nummer sicher gehen will, installiert V2 zusätzlich, legt sich einen Sensor testweise neu an und zieht erst dann um.

## Inhaltsverzeichnis

1. [Was in Version 2 anders ist](#1-was-in-version-2-anders-ist)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Kompatible Sensoren](#3-kompatible-sensoren)
4. [Vorbereitung des Gateways](#4-vorbereitung-des-gateways)
5. [Installation](#5-installation)
6. [Sensoren anlegen mit dem Konfigurator](#6-sensoren-anlegen-mit-dem-konfigurator)
7. [Umstieg von Version 1](#7-umstieg-von-version-1)
8. [Besonderheiten zu Sensoren](#8-besonderheiten-zu-sensoren)
9. [Einen neuen Sensor selbst bauen](#9-einen-neuen-sensor-selbst-bauen)
10. [Wenn ein Sensor nicht erkannt wird](#10-wenn-ein-sensor-nicht-erkannt-wird)
11. [Module und GUIDs](#11-module-und-guids)
12. [Geholfen hat uns folgendes Projekt](#12-geholfen-hat-uns-folgendes-projekt)
13. [Garantie](#13-garantie)
14. [Changelog](#14-changelog)

## 1. Was in Version 2 anders ist

**Ihr müsst keine Sensor-IDs mehr abtippen.** Der neue Konfigurator zeigt alle Sensoren, die euer Gateway empfängt, und legt sie auf Knopfdruck an — mit vorbelegter ID.

**Ein Sensormodul statt zwölf.** Früher gab es für jeden Sensortyp ein eigenes Modul. Jetzt gibt es ein Modul „TFA Sensor", dem der Typ mitgegeben wird. Wie die Bytes eines Sensors auszuwerten sind, steht in einer Beschreibungsdatei im Ordner `libs/sensors/`.

**Neue Sensoren ohne Programmierung.** Weil diese Beschreibung nur eine Datei ist, könnt ihr einen neuen Sensortyp mit dem mitgelieferten Baukasten selbst zusammenbauen. Er taucht dann sofort im Konfigurator auf. Wir freuen uns, wenn ihr uns solche Dateien schickt.

**Mitschnitt eingebaut.** Wenn etwas nicht funktioniert, schaltet ihr das Mitschnitt-Modul ein und könnt uns den Text direkt zuschicken.

**Batterieanzeige korrigiert.** In V1 wurde „ok" rot dargestellt. V2 bringt ein eigenes Profil mit: „Batterie in Ordnung" ist grün, „Batterie schwach" ist rot.

## 2. Voraussetzungen

- Das TFA WeatherHub Gateway
- mindestens ein passender TFA Sensor (für die Erstanschaffung empfiehlt sich ein Starterset)
- IPS ab 6.0

## 3. Kompatible Sensoren

|ID  |TFA Artikel            |Beschreibung                                             |Anmerkung  |Werte|
|:--:|:---------------------:|:-------------------------------------------------------:|:---------:|:---:|
|01  |30.3313.02             |Profi-Temperatur-Sender mit wasserfestem Kabelfühler      |getestet   |24|
|02  |30.3300.02             |Temperatur-Sender                                         |getestet   |14|
|03  |30.3303.02 / 30.3304.02|Thermo-Hygro-Sender, auch mit wasserfestem Kabelfühler    |getestet   |24|
|04  |30.3305.02             |Thermo-Hygro-Sender mit Wassermelder                      |getestet   |28|
|06  |30.3310.02             |Thermo-Hygro-Sender mit Poolsensor                        |ungetestet |34|
|07  |MA 10410               |Wetterstation MA10410                                     |beta       |44|
|08  |30.3306.02             |Regensensor                                               |getestet   |20|
|09  |30.3302.02             |Thermo-Hygro-Sender mit Profi-Temperatur-Kabelfühler      |kein Baustein|—|
|0B  |30.3307.02             |Windsensor                                                |getestet   |27|
|0E  |30.3312.02             |Profi Thermo-Hygro-Sender WEATHERHUB                      |ungetestet |22|
|10  |30.3311.02             |Türen- und Fensterkontakt-Sensor                          |getestet   |13|
|11  |30.3060.01             |KLIMA@HOME                                                |beta       |84|

„Werte" ist die Anzahl der Variablen, die dieser Sensor liefern kann. Ihr wählt selbst aus, welche davon angelegt werden.

Für **ID 09** liegt uns noch keine Beschreibung vor. Der Sensor wird im Konfigurator angezeigt, lässt sich aber nicht anlegen. Wenn ihr so einen habt: siehe [Abschnitt 10](#10-wenn-ein-sensor-nicht-erkannt-wird), wir bauen ihn dann gerne ein.

- Übersicht von TFA: https://www.tfa-dostmann.de/media/pdf/weatherhub-erweiterungen.pdf
- Weitere Infos zum TFA WeatherHub System: https://www.tfa-dostmann.de/themenwelten/smarthome/

## 4. Vorbereitung des Gateways

Das Gateway schließt ihr innerhalb eures Netzwerkes an Netzwerk und Strom an, wie in der Originalanleitung beschrieben. Danach erhält es per DHCP eine IP Adresse. Ihr installiert die zugehörige App von TFA auf eurem Handy. Darin könnt ihr das Gateway unter Einstellungen selbst konfigurieren. Ggf. vergebt ihr eine feste IP, aber vor allem aktiviert ihr den Proxy Server und tragt die IP Adresse eures IPS-Servers ein. Einen freien Port könnt ihr frei wählen, z.B. 3778.

Die Sensoren müssen nicht konfiguriert werden. Alle Daten der Sensoren in Reichweite werden vom Gateway empfangen und ihr erhaltet sie dann direkt im IPS.

Wichtig zu wissen: Wenn das Gateway euren IPS-Server gerade nicht erreicht, speichert es die Pakete zwischen und schickt sie später gesammelt nach. Es kommen also durchaus mehrere Messungen auf einmal an.

## 5. Installation

Im Store installiert ihr die Bibliothek (Suche nach TFA). Danach legt ihr **eine** Gateway-Instanz an:

Rechte Maustaste auf „Splitter Instanzen", dann Objekt hinzufügen – Instanz, im Suchfilter „TFA" eingeben und **TFAGATEWAY_V2** auswählen. Dabei wird automatisch eine Schnittstelle (Server Socket) angelegt, die ihr aktiviert und in die ihr die oben gewählte Portadresse eintragt, z.B. 3778. Nachträglich ändern könnt ihr das über den Button „Schnittstelle Konfigurieren/Ändern".

In der Gatewaykonfiguration gibt es außerdem:

- **FIREWALL** – hier könnt ihr die IP-Adresse eures Gateways eintragen, damit nur von dort Daten verarbeitet werden. Mehrere durch Komma getrennt.
- **DEBUGGER** – Debug-Meldungen einschalten, wenn es Probleme gibt.
- **RESET** – legt ein Skript an, mit dem sich das Gateway zurücksetzen lässt. Skript ausführen genügt.
- **testing** – hier könnt ihr ein Paket als Hex-Text eintragen und durchlaufen lassen, ohne dass ein Sensor senden muss.

## 6. Sensoren anlegen mit dem Konfigurator

Das ist der bequeme Weg und ersetzt das Anlegen von Hand.

Legt eine Instanz **TFACONFIGURATOR_V2** an (Objekt hinzufügen – Instanz, Suchfilter „TFA"). Sie hängt sich automatisch an euer Gateway. Ab jetzt sammelt sie jeden Sensor, von dem ein Paket hereinkommt.

Wartet ein paar Minuten, oder nehmt bei einem Sensor kurz die Batterien heraus und wieder hinein — dann sendet er sofort. Öffnet die Konfiguration des Konfigurators, und ihr seht eine Liste:

- **Sensoren mit grüner Anlegen-Schaltfläche** – einmal klicken und die Instanz ist da, die Sensor-ID ist schon eingetragen.
- **Sensoren, die schon eine Instanz haben** – werden nur angezeigt, damit ihr nichts doppelt anlegt.
- **Unbekannter Sensor** – die ID ist uns noch nicht bekannt. Siehe [Abschnitt 10](#10-wenn-ein-sensor-nicht-erkannt-wird).
- **Passt nicht zum Baustein** – die ID kennen wir, aber das Paket sieht anders aus als erwartet. Meldet euch bitte, dann ist entweder unsere Beschreibung falsch oder TFA hat etwas geändert.

Nach dem Anlegen öffnet ihr die Sensorinstanz und wählt aus, welche Werte ihr als Variablen haben wollt. Unter „SENSOR" stehen allgemeine Angaben wie der Batteriezustand. Alles, was mit „... Vorherige" bezeichnet ist, sind die zuletzt gesendeten Werte, die der Sensor mitschickt; die braucht man in der Regel nicht.

Wenn ihr eine Variable wieder abwählt, wird sie **nicht** automatisch gelöscht — was in eurem Objektbaum steht, gehört euch. Löscht sie bei Bedarf selbst.

Unter **CLOUD EINSTELLUNGEN** legt ihr fest, ob die empfangenen Werte zusätzlich an TFA weitergereicht werden. Damit könnt ihr die original TFA App weiter benutzen. Normalerweise steht dort „www.data199.com" und „/gateway/put".

## 7. Umstieg von Version 1

Version 2 ist eine eigene Bibliothek mit eigenen Kennungen. Beide lassen sich gleichzeitig installieren, und **V1 läuft unverändert weiter**. Es gibt keinen Zwang und keinen Stichtag.

Wenn ihr umziehen wollt:

1. V2 zusätzlich installieren.
2. Eine neue Gateway-Instanz für V2 anlegen. **Achtung:** Der Port kann nur von einer Schnittstelle belegt werden. Entweder ihr stellt das Gateway in der TFA App auf einen zweiten Port um, oder ihr deaktiviert für den Test die alte Schnittstelle.
3. Konfigurator anlegen und einen Sensor testweise neu anlegen.
4. Wenn alles passt, die restlichen Sensoren umstellen und die alten Instanzen löschen.

Die Variablen der neuen Instanzen sind neue Objekte. Eure Archivdaten hängen an den alten Variablen — wenn euch die Historie wichtig ist, hebt die alten Instanzen auf oder zieht die Daten vorher um.

## 8. Besonderheiten zu Sensoren

### ID 0B – Windsensor (30.3307.02)

Ihr könnt einen Offset eingeben, falls ihr den Sensor nicht genau nach Süden ausrichten könnt. Der korrigierte Wert wird auch an die Cloud übertragen, falls ihr die Cloudübertragung aktiviert habt.

### ID 08 – Regensensor (30.3306.02)

Der Regensensor liefert „Regen Zähler", also die Anzahl der Kippvorgänge, und daraus berechnet „Regen Menge". **Aktiviert immer beide Variablen zusammen** — die Berechnung braucht beide, sonst wird die Regengruppe übersprungen. Standardmäßig wird die Menge in Millimeter angezeigt, das entspricht Litern pro Quadratmeter. An dieser Variable solltet ihr in den Archiveinstellungen die Archivierung aktivieren und die Aggregation auf Zähler stellen.

### HTTP-Gateway

Das könnt ihr zusätzlich installieren. Es ruft die Weboberfläche eures Gateways ab und legt dessen Angaben in Variablen ab. Notwendig ist das nicht; dieselben Informationen seht ihr auch im Browser. Ist das Gateway nicht erreichbar, bricht die Abfrage nach drei Sekunden ab, damit IPS nicht hängen bleibt.

## 9. Einen neuen Sensor selbst bauen

Jeder Sensortyp wird durch **eine Datei** im Ordner `libs/sensors/` beschrieben: welche Bytes mit welcher Rechenvorschrift ausgewertet werden und in welche Variablen das Ergebnis wandert. Mehr braucht es nicht — kein PHP, keine neue Modulkennung.

Legt dazu eine Instanz **TFASENSORBUILDER_V2** an. Darin:

- **Aus Paket vorbelegen** – ihr fügt ein Paket eures Sensors als Hex-Text ein, Typ, Paketkopf und Länge werden automatisch übernommen.
- **Vorhandenen Baustein laden** – nehmt einen ähnlichen Sensor als Ausgangspunkt.
- **Prüfen** – meldet Fehler im Klartext, bevor irgendetwas geschrieben wird.
- **Baustein speichern** – danach erscheint die Sensor-ID sofort im Konfigurator und lässt sich anlegen.

Selbst gebaute Bausteine werden im Kernelverzeichnis abgelegt, nicht im Modulordner. Ein Update der Bibliothek löscht sie also nicht.

Das genaue Format, alle verfügbaren Rechenvorschriften und was sie liefern, steht in [libs/sensors/README.md](libs/sensors/README.md).

**Bitte schickt uns eure Bausteine.** Dann nehmen wir sie in die Bibliothek auf und alle haben etwas davon.

## 10. Wenn ein Sensor nicht erkannt wird

1. Legt eine Instanz **TFALOGGER_V2** an. Sie schneidet alles mit, was vom Gateway kommt.
2. Optional „nur unbekannte Sensoren" anhaken, dann bleibt der Mitschnitt übersichtlich.
3. Ein paar Messungen abwarten oder am Sensor kurz die Batterien wechseln.
4. Auf „Als Text herauskopieren" klicken und uns den Text schicken — zusammen mit der genauen TFA-Bezeichnung und Artikelnummer eures Sensors.

Alternativ zeigt euch der Konfigurator bei einem unbekannten Sensor unter „Unbekannter Sensor" direkt den letzten empfangenen Code zum Kopieren.

Habt ihr den Code von jemand anderem bekommen und gar keinen Sensor? Der Konfigurator hat unter **Bytes von Hand eingeben** ein Feld, in das ihr ihn einfügen könnt. Er wird dann genauso ausgewertet, als wäre er über euer Gateway gekommen.

## 11. Module und GUIDs

Der Namenszusatz `_V2` sorgt dafür, dass sich die Modulnamen und die davon abgeleiteten PHP-Funktionen nicht mit Version 1 überschneiden. Ein Aufruf lautet also z.B. `TFAGATEWAY_V2_testdata($id)`.

|Modul                    |Art          |GUID                                  |
|:------------------------|:------------|:-------------------------------------|
|TFAGATEWAY_V2            |Splitter     |{EADE1EC1-66C7-4E18-89FF-8AD98F499DB2}|
|TFASENSOR_V2             |Gerät        |{904322F3-ED27-4EDE-B235-393AFDF1FD5E}|
|TFACONFIGURATOR_V2       |Konfigurator |{7FF896C5-BA6A-404A-9C12-BB7A7F0B3B0E}|
|TFASENSORBUILDER_V2      |Gerät        |{0845C706-40CC-4E8D-B589-35A8267906D1}|
|TFALOGGER_V2             |Gerät        |{E2082BA9-F516-42AF-9BDE-0CE72106A4D2}|
|TFASENSORHTTPGATEWAY_V2  |Gerät        |{18AB6B98-6E85-401B-967F-14D1AE70B13B}|

Datenaustausch zwischen den Instanzen:

|GUID                                  |Beschreibung                          |
|:-------------------------------------|:-------------------------------------|
|{99C7FBD6-D83A-4E3C-B395-CB508547A2FC}|Daten vom Gateway an die Kindmodule   |
|{BA01C4BB-8696-4A6C-9B28-87BD068426A2}|Daten vom Kindmodul zum Gateway       |

Alle Kennungen sind gegenüber Version 1 neu, damit sich beide Bibliotheken nicht ins Gehege kommen.

## 12. Geholfen hat uns folgendes Projekt

https://github.com/sarnau/MMMMobileAlerts

## 13. Garantie

Wir können natürlich trotz aller Sorgfalt keine Garantie für dieses Modul und dessen mögliche Fehlfunktionen übernehmen. Wenn ihr ein Problem feststellt, teilt uns dies bitte mit, wir versuchen dem dann nachzugehen. Da wir selbst nicht alle Sensoren von TFA haben, konnten wir nicht alles testen. Wenn ihr also einen solchen Sensor habt, teilt uns das gerne mit, wenn es Probleme gibt. Nutzt dafür am besten das Mitschnitt-Modul aus [Abschnitt 10](#10-wenn-ein-sensor-nicht-erkannt-wird). Vielen Dank schon mal für eure Mithilfe.

## 14. Changelog

|Datum        |Version |Beschreibung|
|:-----------:|:------:|:-----------|
|01.09.2026 | 2.0 |Eigenständige Bibliothek neben Version 1.<br>Konfigurator, der Sensoren findet und anlegt.<br>Ein Sensormodul für alle Typen, beschrieben durch Dateien in `libs/sensors/`.<br>Baukasten für eigene Sensortypen.<br>Mitschnitt-Modul.<br>Batterieanzeige korrigiert: „ok" ist jetzt grün.|
|07.07.2026 | 1.8 |HTTP-Gateway gegen hängende Socket-Verbindungen abgesichert|
|06.04.2025 | 1.7 |Sensor ID-11 hinzugefügt|
|30.03.2025 | 1.6 |Sensor ID-07 hinzugefügt|
|16.08.2021 | 1.3 |Gateway(HTTP) kann nicht über CURL die Webseite auslesen, durch Socket-Aufruf ersetzt|
|26.06.2020 | 1.1 |Sensor 06 hinzugefügt (nicht getestet), diverse Rechtschreibfehler und Übersetzungen korrigiert|
|09.05.2020 | 1.0 |erste Veröffentlichung des Moduls|

Dieses Modul ist nicht von der Firma TFA offiziell erstellt worden. Es ist ein rein privates Projekt, wobei die Verwendung des Logos und die Veröffentlichung dieses Moduls mit TFA abgestimmt wurde.

Ansonsten wünschen wir viel Spaß mit dem Modul und euren Sensoren.
