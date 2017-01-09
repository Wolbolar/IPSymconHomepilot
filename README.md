# IPSymconHomepilot
===

Modul für IP-Symcon ab Version 4.0 ermöglicht die Kommunikation mit dem Rademacher Homepilot.

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)  
2. [Voraussetzungen](#2-voraussetzungen)  
3. [Installation](#3-installation)  
4. [Funktionsreferenz](#4-funktionsreferenz)
5. [Konfiguration](#5-konfiguration)  
6. [Anhang](#6-anhang) 

## 1. Funktionsumfang

Das Modul liest die Konfiguration des Homepilot aus und erstellt automatisch in IP-Symcon die vorhandenen Instanzen mit Schaltmöglichkeit im Webfront.

[Homepilot Rademacher](https://homepilot.rademacher.de/ "Homepilot Rademacher")

## 2. Voraussetzungen

 - IPS 4.0
 - Rademacher Homepilot

## 3. Installation

### a. Laden des Moduls

 Wir wechseln zu IP-Symcon (min Ver. 4.0) und fügen unter Kerninstanzen über __*Modules*__ -> Hinzufügen das Modul hinzu mit der URL
```
git://github.com/....
```	

### b. Einrichtung in IPS

In IP-Symcon unter Splitter Instanzen wechseln. Hier eine neue Instanz mit _Rechter Mausklick->Objekt hinzufügen->Instanz hinzufügen_ oder _CTRL+1_ erzeugen und als Gerät __*Homepilot Splitter*__ wählen.
Es wird ein Splitter und der  Homepilot I/O angelegt. Jetzt erstellen wir im Objektbaum von IP-Symcon eine Kategorie unter der später die Homepilot Geräte angelegt werden sollen.
Nun unter _I/O Instanzen_ zum _**HomepilotIO**_ wechseln und mit Doppelklick öffnen. Unter _IP-Adresse_ ergänzen wir die IP Adresse des Homepilot und wählen im Formular auch die Kategorie aus die wir zuvor für
die Homepilot Geräte angelegt haben. Mit _Übernehmen_ bestätigen. Nun im unteren Teil des Formulars zunächst auf _Konfiguartion auslesen_ drücken. Wenn die Daten richtig ausgelesen wurden sollte nun die Variabale
_Homepilot Config_ unterhalb **HomepilotIO**_ mit Inhalt gefüllt worden sein. Im Anschluss daran auf _Setup Homepilot_ drücken, es werden nun die Geräte in IP-Symcon angelegt. An den Geräte Instanzen ist nichts zusätzlich zu konfigurieren,
diese solten nach dem Anlegen über den Webfront bedienbar sein.

## 4. Funktionsreferenz

### Homepilot
Ein Gerät wird mit der entsprechenden Funktion und Übergabe der InstanzID angesteuert.
 
Rollladen Hochfahren
```php
Homepilot_Up(integer $InstanceID)
```   
Parameter _$InstanceID_ __*ObjektID*__ der Homepilot Instanz

Rollladen Stop
```php
Homepilot_Stop(integer $InstanceID)
```   
Parameter _$InstanceID_ __*ObjektID*__ der Homepilot Instanz

Rollladen Runterfahren
```php
Homepilot_Down(integer $InstanceID)
```   
Parameter _$InstanceID_ __*ObjektID*__ der Homepilot Instanz

Rollladen Position 0 % anfahren
```php
Homepilot_Position0(integer $InstanceID)
```   
Parameter _$InstanceID_ __*ObjektID*__ der Homepilot Instanz

Rollladen Position 25 % anfahren
```php
Homepilot_Position25(integer $InstanceID)
```   
Parameter _$InstanceID_ __*ObjektID*__ der Homepilot Instanz

Rollladen Position 50 % anfahren
```php
Homepilot_Position50(integer $InstanceID)
```   
Parameter _$InstanceID_ __*ObjektID*__ der Homepilot Instanz

Rollladen Position 75 % anfahren
```php
Homepilot_Position75(integer $InstanceID)
```   
Parameter _$InstanceID_ __*ObjektID*__ der Homepilot Instanz

Rollladen Position 100 % anfahren
```php
Homepilot_Position100(integer $InstanceID)
```   
Parameter _$InstanceID_ __*ObjektID*__ der Homepilot Instanz

Rollladen Position X % anfahren
```php
Homepilot_Position(integer $InstanceID, integer $position)
```   
Parameter _$InstanceID_ __*ObjektID*__ der Homepilot Instanz
Parameter _$position_ Anzufahrende __*Position*__ des Rollladen


### Homepilot IO
Konfiguration auslesen
```php
HomepilotIO_getConfig(integer $InstanceID)
```   
Parameter _$InstanceID_ __*ObjektID*__ der Homepilot I/O Instanz

Holt die aktuelle Position vom Homepilot ab und schreibt diese in die Instanz Variable des Rollladens
```php
HomepilotIO_GetState(integer $InstanceID)
```   
Parameter _$InstanceID_ __*ObjektID*__ der Homepilot I/O Instanz
 

## 5. Konfiguration:

### Homepilot IO:

| Eigenschaft       | Typ     | Standardwert | Funktion                                                  |
| :---------------: | :-----: | :----------: | :-------------------------------------------------------: |
| Host              | string  | 		     | IP Adresse des Homepilot                                  |
| ImportCategoryID  | integer | 		     | ObjektId der Importkategorie für die Homepilot Geräte     |
| username          | string  | 		     | username                                                  |
| password          | string  |              | password                                                  |



### Homepilot:  

| Eigenschaft      | Typ     | Standardwert| Funktion                                                    |
| :--------------: | :-----: | :---------: | :---------------------------------------------------------: |
| Name             | string  |             | Name des Geräts                                             |
| DeviceID         | integer |             | Device ID des Geräts                                        |
| ProductName      | string  |             | Produktbezeichnung                                          |
| Version          | string  |             | Version des Geräts                                          |
| UID              | string  |             | UID des Geräts                                              |





## 6. Anhang

###  a. GUIDs und Datenaustausch:

#### Homepilot IO:

GUID: `{D40B39AA-3966-41F1-A7E1-ABFF538DE0CE}` 


#### Homepilot:

GUID: `{19E9190A-F772-4589-8655-5FB219F6C418}` 


