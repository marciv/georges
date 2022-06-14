# George

## Installation

First, create a folder here name "George":

```
C:\\Wamp\www\1root\library\
```

Create Folder "database" in George folder

Then in the LP:

include scriptGeorge.php in your LP

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

### File AddConversion.php

Script for save a conversion to a variation URL

### File abtest.php

File for see data [nb_visit, coonversion, tx, etc...]
acces with parameter $\_GET['dbName']

### File changeStatus.php

Script for change status PAUSE OR STARTED of abtest

### File AddConversion.php

Script for save a conversion to a variation URL

### File delDB.php

Script for delete a DB and delete files and folder with a name

### File index.php

Interface User for George

### File scriptGeorge.php

Contains Script for add conversion to LP

## Running the tests

- Start : Go to the url of the root of the folder

```
https://nomdomaine.fr/library/George/
```

### ADD ABTest

Fill in URL inputs without adding parameters

```
URL Principale => https://www.je-renove.net/pho/lan/06/

Discovery Rate => 0.20

URL variation => https://www.je-renove.net/pho/lan/16/
```

Click in the Button "Start AB Test"
