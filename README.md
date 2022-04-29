[![IPS-Version](https://img.shields.io/badge/Symcon_Version-6.0+-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Code](https://img.shields.io/badge/Code-PHP-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Installation](#3-installation)
4. [Funktionsreferenz](#4-funktionsreferenz)
5. [Konfiguration](#5-konfiguration)
6. [Anhang](#6-anhang)
7. [Versions-Historie](#7-versions-historie)

## 1. Funktionsumfang

Steuerung der Warema WMS-Komponenten, unterstützt wird zur Zeit:<br>
- Markise, Markise mit Windsensor

Weitere WMS-Komponenten sind grundsätzlich auch ansteuerbar, mangels Verfügbarkeit jedoch noch nicht ausprogrammiert. Bei Bedarf bitte an den Autor wenden.

## 2. Voraussetzungen

- IP-Symcon ab Version 6.0
- Warema WebControl (nicht Warema WebControl Pro via Warema Cloud)
eingelernt im WMS

Es ist möglich, das eine WebControl Pro im Hausnetz genauso wie ein WebControl funktioniert, würde ich aber nicht vonb ausgehen.

## 3. Installation

### a. Installation des Moduls

Im [Module Store](https://www.symcon.de/service/dokumentation/komponenten/verwaltungskonsole/module-store/) ist das Modul unter dem Suchbegriff *Warema WMS* zu finden.<br>
Alternativ kann das Modul über [Module Control](https://www.symcon.de/service/dokumentation/modulreferenz/module-control/) unter Angabe der URL `https://github.com/demel42/IPSymconWaremaWMS.git` installiert werden.

### b. Einrichtung in IPS

#### Warema WMS IO
In IP-Symcon nun unterhalb von _I/O Instanzen_ die Funktion _Instanz hinzufügen_ auswählen und als Hersteller _Warema_ angeben.
In der IO-Instanz muss nur der Hostname/die IP-Adresse des WebControl angegeben werden.
Mittels _Zugriff prüfen_ kann getestet werden (Hinweis: dauert ein paar Sekunden)

#### Warema WMS Config
In IP-Symcon nun unterhalb von _Konfigurator Instanzen_ die Funktion _Instanz hinzufügen_ auswählen und als Hersteller _Warema_ angeben.
In dem Konfigurator werden nun alle eingerichteten Räume/Kanäle aufgelistet; eine Anlage der Geräte-Instanz kann entsprechend erfolgen

#### Warema WMS Device
Die Geräte-Instanz wird über dem Konfigurator angelegt. In der _Basis-Konfiguration_ ist Raum/Kanal sowie der Produkt-Typ eingetragen. Achtung: die Warema-Komponenten werden via Raum+Kanal angesprochen, bei Änderung der Zuordnung muss das ggfs. nachgeführt werden.

## 4. Funktionsreferenz

alle Funktionen sind über _RequestAction_ der jew. Variablen ansteuerbar

## 5. Konfiguration

### Warema WMS IO

#### Properties

| Eigenschaft               | Typ      | Standardwert | Beschreibung |
| :------------------------ | :------  | :----------- | :----------- |
| Instanz deaktivieren      | boolean  | false        | Instanz temporär deaktivieren |
|                           |          |              | |
| WMS-Schnittstelle         | integer  | 0            | 0=WebControl |
| Hostname of WebControl    | string   |              | Hostname / ƢP-Adresse des WebControl |


#### Aktionen

| Bezeichnung                | Beschreibung |
| :------------------------- | :----------- |
| Zugriff prüfen             | Auflistung der Konfiguration |

### WaremaWMSConfig

#### Properties

| Eigenschaft               | Typ      | Standardwert | Beschreibung |
| :------------------------ | :------  | :----------- | :----------- |
| Kategorie                 | integer  | 0            | Kategorie zu Anlage von Instanzen |

### WaremaWMSDevice

#### Properties

| Eigenschaft               | Typ      | Standardwert | Beschreibung |
| :------------------------ | :------  | :----------- | :----------- |
| Instanz deaktivieren      | boolean  | false        | Instanz temporär deaktivieren |
|                           |          |              | |
| Raum-ID                   | integer  |              | Raum-Index |
| Kanal-ID                  | integer  |              | Kanal-Index |
| Produkt                   | integer  |              | Produkt-Typ |
|                           |          |              | |
| Aktualisierungsintervall  | integer  | 15           | Intervall in Sekunden |

#### Aktionen

| Bezeichnung                | Beschreibung |
| :------------------------- | :----------- |
| Status aktualisieren       | Abfragen des Status (z.B. Position, Aktivität) |

### Variablenprofile

Es werden folgende Variablenprofile angelegt:
* Integer<br>
WaremaWMS.Activity,
WaremaWMS.ControlAwning,
WaremaWMS.ControlBlind,
WaremaWMS.Position,
WaremaWMS.State

## 6. Anhang

GUIDs
- Modul: `{275F8086-2BAE-0114-374B-B871E0564EAB}`
- Instanzen:
  - WaremaWMSIO: `{6A9BBD57-8473-682D-4ABF-009AE8584B2B}`
  - WaremaWMSConfig: `{657F43A7-9122-0568-5E0C-3301A6DFFAF5}`
  - WaremaWMSDevice: `{6A9BBD57-8473-682D-4ABF-009AE8584B2B}`
- Nachrichten:
  - `{A8C43E67-9C5C-8A22-1F46-69EC56138C81}`: an WaremaWMSIO
  - `{B78E405B-23E3-10A5-4B26-F24277883F96}`: an WaremaWMSConfig, WaremaWMSDevice

## 7. Versions-Historie

- 1.2.2 @ 29.04.2022 14:20
  - Schreibfehler im Variableprofil 'WaremaWMS.State'

- 1.2.1 @ 27.04.2022 17:10
  - Korrektur: self::$IS_DEACTIVATED wieder IS_INACTIVE
  - interne Änderungen (Translate überlagert, locale.json in mehㄦere translate.json gesplittet)

- 1.2 @ 24.04.2022 15:34
  - Übersetzung vervollständigt
  - Implememtierung einer Update-Logik
  - diverse interne Änderungen

- 1.1.2 @ 16.04.2022 12:02
  - potentieller Namenskonflikt behoben (trait CommonStubs)
  - Aktualisierung von submodule CommonStubs

- 1.1.1 @ 08.04.2022 16:04
  - Konfigurator zeigt nun auch Instanzen an, die nicht mehr zu den vorhandenen Geräten passen

- 1.1 @ 08.04.2022 10:12
  - Korrektur bei der Berechnung der Postion(en)

- 1.0 @ 26.03.2022 17:14
  - Initiale Version
