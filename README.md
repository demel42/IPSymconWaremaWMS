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

## 2. Voraussetzungen

- IP-Symcon ab Version 6.0

## 3. Installation

### a. Installation des Moduls

Im [Module Store](https://www.symcon.de/service/dokumentation/komponenten/verwaltungskonsole/module-store/) ist das Modul unter dem Suchbegriff *Warema WMS* zu finden.<br>
Alternativ kann das Modul über [Module Control](https://www.symcon.de/service/dokumentation/modulreferenz/module-control/) unter Angabe der URL `https://github.com/demel42/IPSymconWaremaWMS.git` installiert werden.

### b. Einrichtung in IPS

#### WaremaWMSIO
#### WaremaWMSConfig

## 4. Funktionsreferenz

## 5. Konfiguration

### WaremaWMSConfig

#### Properties

| Eigenschaft               | Typ      | Standardwert | Beschreibung |
| :------------------------ | :------  | :----------- | :----------- |

#### Aktionen

| Bezeichnung                | Beschreibung |
| :------------------------- | :----------- |


### WaremaWMSDevice

#### Properties

| Eigenschaft               | Typ      | Standardwert | Beschreibung |
| :------------------------ | :------  | :----------- | :----------- |
| Instanz deaktivieren      | boolean  | false        | Instanz temporär deaktivieren |
|                           |          |              | |

#### Aktionen

| Bezeichnung                | Beschreibung |
| :------------------------- | :----------- |

### Test-Bereich

### Experten-Bereich

### Variablenprofile

Es werden folgende Variablenprofile angelegt:
* Boolean<br>

* Integer<br>

* Float<br>

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

- 0.9 @ 22.03.2022 17:34 (test)
  - Initiale Version
