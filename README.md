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
you need to install these dependencies before using george

- FlatDB => DB https://github.com/onyxhat/FlatDB
- Mobile-Detect-2.8.25 => Detection for mobile
- class.browser.php => Detection for browser

### File page_abtest.php

File for see data [nb_visit, coonversion, tx, etc...]
acces with parameter $\_GET['dbName']

### File switchGeorge.php

Router action function :

- changeState = change state for one abtest
- delete = delete database
- createDB = Create on ABTEST
- addConversion = add Conversion

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

add this to settings

// START GEORGE
use library\George as george;
try {

    $request_url = George::_getRequestUrl();
    $urlInformations = pathinfo($request_url);
    if((@$urlInformations['extension']=="php" || empty($urlInformations['extension'])) AND stripos($urlInformations['dirname'],'/lan')){
        $variationName = George::_getVariationNamefromUrl($request_url); //Nom variation actuel
        $george = new George($variationName); // On vérifie si une bdd avec le nom existe
        $george->start();

    }

} catch (Exception $e) {
echo 'erreur : ';
echo $e->getMessage();  
 // log system
}
