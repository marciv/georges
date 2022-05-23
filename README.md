# George

## Installation

First, create a folder here name "George":

```
C:\\Wamp\www\1root\library\
```

Past All file here without master-headerV4

Second, move master-headerV4 in the same folder to master-headerV2

```
C:\\Wamp\www\1root\
```

Then in the LP:

- Include master-headerV4(In Futur master-headerV2) in the index.php
- Include in the js/general.js where the form is sent a custom event see below

```
let event = new CustomEvent('form-sended', {
bubbles: true,
cancelable: false
});

document.dispatchEvent(event);
```

### Folder CSS

This Folder contains style.css for add styling to George

### Folder database

This folder will contains all database and stock data for George

The DB will be name in following this pattern `campaign_lan_numLP`

The databases are managed with the FlatDB library

### Folder library

This Folder contains all library require for George

- FlatDB => DB
- Mobile-Detect-2.8.25 => Detection for mobile
- class.browser.php => Detection for browser

### File addABTest.php

Script for create ABTest with one URL conversion, one Discovery Rate and variation URL

### File AddConversion.php

Script for save a conversion to a variation URL

### File delDB.php

Script for delete a DB and delete files and folder with a name

### File index.php

Interface User for George

### File master-headerV4.php

This file is the master-headerV4 for the LP and contain script for search if DB and data exist and launch the script for calculate visit and the conversion

## Running the tests

- Start : Go to the url of the root of the folder

```
https://nomdomaine.fr/library/George/
```

### ADD ABTest

Fill in URL inputs without adding parameters

```
URL conversion => https://www.je-renove.net/pho/lan/06/

Discovery Rate => 0.20

URL variation => https://www.je-renove.net/pho/lan/16/
```

Click in the Button "Start AB Test"
