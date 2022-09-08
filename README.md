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

### File page_abtest.php

File for see data [nb_visit, coonversion, tx, etc...]
acces with parameter $\_GET['dbName']

### File switchGeorge.php

Router action function :

- changeState = change state for one abtest
- changeDiscoveryRate = change discovery rate for one abtest
- addVariationToAbtest = add variation to one abtest
- editFilter = edit filter for one abtest
- generateABTEST = Generate ABTEST for developpment with filter and variation
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

Fill all input with (*) and click on "Start AB Test"

### Add this to settings

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
