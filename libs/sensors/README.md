# Sensor-Bausteine

Jede Datei in diesem Ordner beschreibt **einen** TFA-Sensortyp: welche Bytes eines
Paketes wie ausgewertet werden und in welche Variablen das Ergebnis wandert.

Ein neuer Sensor braucht **nur eine solche Datei** — kein PHP, keine neue GUID,
kein neues Modulverzeichnis. Sobald die Datei hier liegt, taucht die Sensor-ID
im Konfigurator auf und Instanzen lassen sich daraus anlegen.

Dateiname: die Typ-ID in Kleinbuchstaben, z. B. `0b.json`. Dateien, die mit `_`
beginnen, werden übersprungen — `_template.json` ist die Kopiervorlage.

## Aufbau

| Feld | Bedeutung |
|---|---|
| `typ` | Die ersten **zwei** Stellen des QR-Codes auf dem Sensor, hex, klein. |
| `article` | TFA-Artikelnummer, erscheint im Konfigurator. |
| `name` | Klartextname, erscheint im Konfigurator. |
| `state` | `getestet` / `beta` / `ungetestet` / `kein modul`. |
| `guid` | GUID des Moduls, das der Konfigurator anlegt. Leer = nur gelistet, nicht anlegbar. |
| `frame.header` | Erwartetes Byte 0 des Paketes, dezimal (z. B. `225` für `0xe1`). |
| `frame.length` | Erwartete Nutzdatenlänge = Byte 5 minus 12. Maximal 51. |
| `options.windoffset` | `true` schaltet das Offset-Feld für Windrichtung frei (nur ID 0b). |
| `groups` | Die eigentlichen Bausteine, siehe unten. |

### Eine Gruppe

Eine Gruppe schneidet ein Stück aus den Nutzdaten, schickt es durch einen Dekoder
und verteilt dessen Ergebnis auf Variablen. Im Konfigurationsformular der Instanz
wird jede Gruppe ein eigener Knopf, unter dem der Nutzer die Variablen einzeln
an- und abwählt.

```json
{
    "name": "temperature",          // Überschrift im Formular, via locale.json übersetzt
    "decoder": "dec_temperature",   // siehe Tabelle unten
    "offset": 2,                    // ab welchem Byte der Nutzdaten
    "length": 2,                    // wie viele Bytes
    "variables": [ ... ]
}
```

### Eine Variable

```json
{
    "ident": "temperature",      // technischer Name, vom Nutzer NICHT änderbar — nie doppelt vergeben
    "name": "temperature",       // Anzeigename, englisch, Übersetzung in locale.json
    "type": "float",             // boolean | integer | float | string
    "profile": "~Temperature",   // IPS-Variablenprofil, leer = keines
    "position": 2,               // Sortierung im Objektbaum
    "field": "temperature"       // welches Feld aus dem Dekoder-Ergebnis
}
```

`field` muss eines der Felder sein, die der gewählte Dekoder zurückgibt.

## Verfügbare Dekoder

| Dekoder | Bytes | Liefert die Felder |
|---|:---:|---|
| `dec_sensor_data` | 2 | `battery`, `heartbeat`, `counter`, `update` |
| `dec_sensor_data_wind` | 2 | `battery`, `heartbeat`, `counter`, `update` |
| `dec_sensor_data_dir` | 2 | `winddirection`, `windspeed`, `gustspeed`, `lasttransmit` |
| `dec_temperature` | 2 | `temperature`, `up05`, `down05`, `overflow`, `error` |
| `dec_humidity` | 2 | `humidity`, `average`, `id`, `up05`, `down05` |
| `dec_humidity_decimalplace` | 2 | `humidity` |
| `dec_airQuality` | 2 | `ppm`, `overflow` |
| `dec_wetness` | 1 | `wet`, `dry` |
| `dec_doorwindows` | 1 | `sensor`, `time`, `uint` |
| `dec_temperature_pos_rain` | 2 | `pos`, `temp`, `overflow`, `error`, `uint` |
| `dec_counter_rain` | 2 | `counter`, `rainnew` |
| `dec_event_rain` | 2 | `time`, `uint` |

Alle Dekoder liefern zusätzlich diverse `unknownbitXX`-Felder — die sind zum
Erforschen unbekannter Sensoren gedacht und gehören nicht in ein fertiges Modul.

## Vorgehen bei einem neuen Sensor

1. `_template.json` auf `<typ>.json` kopieren, `typ`, `article` und `name` setzen.
2. Im TFAGATEWAY das Debugging einschalten und den Sensor senden lassen
   (Batterie kurz entnehmen). Im Debug stehen `Package Header`, `Package Length`
   und der komplette Frame als Hex.
3. `frame.header` und `frame.length` aus dem Debug übernehmen.
4. Die Bytes gruppenweise durchgehen. Byte 0..1 der Nutzdaten sind bei fast allen
   Sensoren `dec_sensor_data`; danach folgen die eigentlichen Messwerte, meist in
   2-Byte-Schritten, oft gefolgt von den zuletzt gesendeten Werten in derselben
   Reihenfolge.
5. Den Hexstring im TFAGATEWAY unter *testing* als `SDATA` eintragen und
   `testdata()` auslösen — damit lässt sich der Baustein ohne den echten Sensor
   durchspielen.
6. Anzeigenamen in der `locale.json` des Moduls übersetzen.

Fehler im Baustein (unbekannter Dekoder, Gruppe hinter der Paketlänge, doppelter
`ident`, falscher `type`) werden geprüft und im Klartext gemeldet, statt still
zu scheitern.

## Sonderfälle

- **Regen (ID 08):** `dec_counter_rain` liest die vorherigen Werte zurück. Die
  Variablen `raincounter` und `rainfall` müssen immer gemeinsam aktiviert werden.
- **Wind (ID 0b):** `options.windoffset` schaltet das Offsetfeld frei. Der Offset
  wird auch in den Rohdaten korrigiert, die an die TFA-Cloud weitergehen.
