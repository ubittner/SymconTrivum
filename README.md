# SymconTrivum

[![Version](https://img.shields.io/badge/Symcon_Version-5.0>-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Modul_Version-1.04-blue.svg)
![Version](https://img.shields.io/badge/Modul_Build-1-blue.svg)
![Version](https://img.shields.io/badge/Code-PHP-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![StyleCI](https://github.styleci.io/repos/164321794/shield?branch=master&style=flat)](https://github.styleci.io/repos/164321794)

Dies ist ein Gemeinschaftsprojekt von Normen Thiel und Ulrich Bittner und integriert [Trivum FlexLine SoundSysteme](https://www.trivum.de/products/flexline/) in [IP-Symcon](https://www.symcon.de).

Für dieses Modul besteht kein Anspruch auf Fehlerfreiheit, Weiterentwicklung, sonstige Unterstützung oder Support.

Bevor das Modul installiert wird, sollte unbedingt ein Backup von IP-Symcon durchgeführt werden.

Der Entwickler haftet nicht für eventuell auftretende Datenverluste.

Der Nutzer stimmt den o.a. Bedingungen, sowie den Lizenzbedingungen ausdrücklich zu.

## Dokumentation

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanz in IP-Symcon](#4-einrichten-der-instanz-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)
8. [GUIDs](#8-guids)
9. [Changelog](#9-changelog)
10. [Lizenz](#10-lizenz)
11. [Autor](#11-autor)


### 1. Funktionsumfang

- Ein- / Ausschalten einer Zone (Ausgang)
- Auswahl eines Favoriten (Audioquelle) zur Wiedergabe
- Veränderung der Lautstärke für die Zone
- Multiroom, Gruppierung von verschiedenen Zonen

### 2. Voraussetzungen

 - IP-Symcon ab Version 5.0, Web-Console

### 3. Software-Installation

Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Entwickler.

Bei privater Nutzung:

Nachfolgend wird die Installation des Moduls anhand der neuen Web-Console ab der Version 5.0 beschrieben. Die Verwendung der (legacy) Verwaltungskonsole wird vom Entwickler nicht mehr berücksichtigt.

Folgende Instanzen stehen dann in IP-Symcon zur Verfügung:

- FlexLine

#### 3a. Modul hinzufügen

Im Objektbaum von IP-Symcon die Kern Instanzen aufrufen. 

Danach die [Modulverwaltung](https://www.symcon.de/service/dokumentation/modulreferenz/module-control/) aufrufen.

Sie sehen nun die bereits installierten Module.

Fügen Sie über das `+` Symbol (unten rechts) ein neues Modul hinzu.

Wählen Sie als URL:

`https://github.com/ubittner/SymconTrivum.git`  

Anschließend klicken Sie auf `OK`, um das Symcon Trivum FlexLine Modul zu installieren.

#### 3b. Instanz hinzufügen

Klicken Sie in der Objektbaumansicht unten links auf das `+` Symbol. 

Wählen Sie anschließend `Instanz` aus. 

Geben Sie im Schnellfiler das Wort "FlexLine" ein oder wählen den Hersteller "Trivum" aus. 
Wählen Sie aus der Ihnen angezeigten Liste "FlexLine" aus und klicken Sie anschließend auf `OK`, um die Instanz zu installieren.

### 4. Einrichten der Instanz in IP-Symcon

#### FlexLine
Geben Sie die IP-Adresse und den Timeout des Trivum Gerätes an. Legen Sie anschließend fest, welche Zone Sie steuern möchten (Ausgang).
Fügen Sie die Favoriten hinzu, die Sie als Quellen verwenden möchten. Unter Multiroom können Sie weitere Zonen zur Gruppierung hinzufügen. 

#### Konfiguration:

#### FlexLine:

| Name                                  | Beschreibung                                                                                                      |
| :-----------------------------------: | :---------------------------------------------------------------------------------------------------------------: |
| (0) Instanzinformationen              | Zeigt Informationen zur Modulversion an                                                                           |
| (1) Allgemeine Einstellungen          |                                                                                                                   |
| IP-Adresse                            | IP-Adresse des Trivum SoundSystems                                                                                |
| Timeout ms                            | Netzwerk-Timeout in Millisekunden (ms)                                                                            |
| (2) Ausgang (Zone)                    |                                                                                                                   |
| Zonen ID                              | ID der Output Zone (0-n)                                                                                          |
| Zonenbezeichnung                      | Bezeichnung der Zone                                                                                              | 
| (3) Eingänge (Favoritenliste)         | Liste der Audioquellen, welche als Favoriten im Trivum SoundSystem vorhanden sind                                 |
| Position                              | Position zur Sortierung der Reihenfolge                                                                           |
| Favorit                               | Nummer des Favoriten (1-n)                                                                                        |
| Bezeichnung                           | Bezeichnung des Favoriten                                                                                         |
| Volume                                | Standard-Lautstärke (-1 = keine Standardlautstärke verwenden)                                                     |
| (4) Multiroom                         |                                                                                                                   |
| Position                              | Position zur Sortierung der Reihenfolge                                                                           |
| Zonen ID                              | ID des Zonemitglieds, welche hinzugefügt werden soll                                                              |
| Bezeichnung                           | Bezeichnung des Zonenmitglieds                                                                                    |
| Instanz ID                            | Instanz ID des Zonenmitglieds                                                                                     |

__Schaltfläche__:

| Name                                  | Beschreibung                                                                                                      |
| :-----------------------------------: | :---------------------------------------------------------------------------------------------------------------: |
| Button "Favoriten anzeigen"           | Zeigt die Favoriten an, sofern kein Popup-Blocker aktiviert wurde                                                 |
| Button "Setup aufrufen"               | Ruft den Setup-Modus des Trivum Multiroom Audio Systems auf, sofern kein Popup-Blocker aktiviert wurde            |
| Button "Bedienoberfläche aufrufen"    | Ruft die Bedienoberfläche des Trivum Multiroom Audio Systems auf, sofern kein Popup-Blocker aktiviert wurde       |

Geben Sie die erforderlichen Daten an. 

Wenn Sie die Daten eingetragen haben, erscheint unten im Instanzeditor eine Meldung `Die Instanz hat noch ungespeicherte Änderungen`. Klicken Sie auf den Button `Änderungen übernehmen`, um die Konfigurationsdaten zu übernehmen und zu speichern.

Mit dem Button `Favoriten auslesen` werden die Favoriten des Trivum SoundSystems ausgelesen und mit der Instanz verknüpft. Sie finden die vorhandenen Favoriten in der Favoritenliste.

Sie können den Vorgang für weitere Zonen wiederholen.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Variablen:

|Name               | Typ       | Beschreibung                                                                          |
| :---------------: | :------:  | :-----------------------------------------------------------------------------------: |
| ZonePower         | Boolean   | Schaltet die Zone ein, bzw. aus                                                       |
| AudioSources      | Integer   | Zeigt die vorhandenen Audioquellen an, welche über die Favoritenliste angelegt wurden |
| VolumeSlider      | String    | Regelt die Lautstärke der Audioquelle für die Zone                                    |
| ZoneMembers       | Integer   | Multiroom, verfügbare Zonen zur Gruppierung                                           |
| Group             | String    | Informationen zur Gruppe                                                              |
| GroupType         | String    | Informationen zum Gruppentyp                                                          |


##### Profile:

Nachfolgende Profile werden automatisch angelegt und sofern die Instanz gelöscht wird auch wieder automatisch gelöscht.

| Name                           | Typ       | Beschreibung                              |
| :----------------------------: | :-------: | :---------------------------------------: |
| UBTFL.InstanzID.ZonePower      | Bool      | Zone Aus / An                             |
| UBTFL.InstanzID.AudioSources   | Integer   | Enthält die Audioquellen                  |
| UBTFL.InstanzID.AudioFavorites | Integer   | Enthält die Favoriten                     |
| UBTFL.InstanzID.VolumeSlider   | Integer   | Lautstärke An                             |
| UBTFL.InstanzID.ZoneMembers    | Integer   | Enthält die Zonenmitglieder               |

### 6. WebFront

Über das WebFront kann die Zone ein-, bzw. ausgeschaltet werden.  
Ebenfalls kann die Audioquelle ausgewählt werden.  
Die Lautstärke der Audioquelle für die Zone kann über den Schieberegler eingestellt werden.  
Die Auswhl einer weiteren Zone zur Gruppierung ist möglich.

### 7. PHP-Befehlsreferenz

Präfix des Moduls `UBTFL` (Ulrich Bittner Trivum Flex Line)

`UBTFL_ShowSystemConfiguration(integer $InstanzID)`

Ruft die Systemkonfiguration des Trivum SoundSystems auf.

`UBTFL_SetupSystem(integer $InstanzID)`

Ruft das Setup des Trivum SoundSystems auf, um es zu konfigurieren.
Ein im Browser vorhandener Popup-Blocker muss ggfs. deaktiviert werden.

`UBTFL_ShowWebUI(integer $InstanzID)`

Ruft die Bedienoberfläche des Trivum SoundSystems auf, um es zu bedienen.
Ein im Browser vorhandener Popup-Blocker muss ggfs. deaktiviert werden.

`UBTFL_GetTrivumData(integer $InstanzID)`

Liest den Namen der Zone und die angelegten Favoriten aus dem Trivum SoundSystem aus und verknüpft Sie mit der Instanz.
Über den Instanzeditor können innerhalb der Favoritenliste nachträglich noch Änderungen vorgenommen werden.

`UBTFL_ToggleZonePower(integer $InstanzID, bool $Status)` 

Schaltet die Zone mit dem Wert `true` ein und mit dem Wert `false` aus.

`UBTFL_SelectFavorite(integer $InstanzID, integer $Favoritennummer)`

Spielt die unter der Favoritennummer gespeicherte Audioquelle für die Zone ab.
 
`UBTFL_SetZoneVolume(integer InstanzID, integer $Lautstärke)`

Ändert die Lautstärke der Zone für die ausgewählte Audioquelle um den angegebenen Wert.

`UBTFL_SelectZoneMember(integer InstanzID, integer $SlaveZonenInstanzID)`

Fügt eine Slave Zone der Master Zone hinzu.

`UBTFL_GetGroupStatus(integer InstanzID)`

Zeigt den Gruppenstatus an.

### 8. GUIDs

__Modul GUIDs__:

| Name              | GUID                                      | Bezeichnung   |
| :----------------:| :----------------------------------------:| :------------:|
| Bibliothek        | {2BF5E234-467B-40D5-A156-C0FA9A728352}    | Library GUID  |
| FlexLine          | {CFAA5028-F205-4FE6-B86C-4F5E1EDD4CCD}    | Module GUID   |

### 9. Changelog

| Version       | Datum         | Beschreibung                      |
| :-----------: | :-----------: |---------------------------------: |
| 1.04-2        | 19.04.2019    | Anpassungen an Firmware-Update    |
| 1.04-1        | 06.01.2019    | Anpassungen an IP-Symcon 5.0      |
| 1.03          | 16.09.2018    | Gruppensteuerung hinzugefügt      |
| 1.02          | 27.08.2018    | Verbesserungen                    |
| 1.01          | 08.05.2018    | Verbesserungen                    |
| 1.00          | 30.03.2018    | Modulerstellung                   |

### 10. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)

### 11. Autor

Ulrich Bittner
