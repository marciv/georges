<?php

require "../../config.php";

use library\George as george;

if (isset($_GET['action'])) {
    /**
     * Change state of ABTEST
     * $_GET['db'] = nameDB
     */
    if ($_GET['action'] == "changeState") {
        $george = new george($_GET['db']); // On vérifie si une bdd avec le nom existe
        if ($george->changeStatus()) {
            header('Location: index.php?success=true&message=Status de l\'ABTEST changé');
        } else {
            header('Location: index.php?success=false&message=Une erreur est survenue');
        }
        exit;
    }

    if ($_GET['action'] == "changeDiscoveryRate") {
        $george = new george($_GET['db']); // On vérifie si une bdd avec le nom existe
        if ($george->changeDiscoveryRate($_POST['taux_decouvert'])) {
            header('Location: index.php?success=true&message=Discovery Rate de l\'ABTEST changé');
        } else {
            header('Location: index.php?success=false&message=Une erreur est survenue');
        }
        exit;
    }

    if ($_GET['action'] == "addVariationToAbtest") {
        $george = new george($_GET['db']); // On vérifie si une bdd avec le nom existe
        if ($george->addVariationToAbtest($_POST['variation'])) {
            header('Location: index.php?success=true&message=Variation ' . $_POST['variation'] . ' ajoutée à l\'ABTEST');
        } else {
            header('Location: index.php?success=false&message=Une erreur est survenue');
        }
        exit;
    }

    /**
     * Delete DB
     * $_GET['db'] = nameDB
     */
    if ($_GET['action'] == "delete") {
        $success = false;
        $george = new george();
        if ($_GET['archived'] == "true") {
            $george->deleteData("database/archived/" . $_GET['db']); //Suppression de l'ABTest
        } else {
            //Suppression de l'ABTest
            if ($george->deleteData("database/" . $_GET['db'])) {
                $success = true;
            }
        }
        if ($success) {
            header('Location: index.php?success=true&message=ABTEST supprimé avec succès');
        } else {
            header('Location: index.php?success=false&message=Erreur dans la suppression');
        }
        exit;
    }
    /**
     * Create ABTEST
     * $_POST['url_conversion'] = URL Principale
     * $_POST['taux_decouvert'] = taux_decouvert
     * $_POST['url_variations'] = All variations
     */
    if ($_GET['action'] == "createDB") {
        $url_conversion = $_POST['url_conversion'];
        $discovery_rate = $_POST['taux_decouvert'];
        $urls_variation = []; // Stockage des URLS 

        $nameDB = str_replace("/", "_", trim(parse_url($url_conversion, PHP_URL_PATH), "/"));

        foreach ($_POST['url_variations'] as $url) {
            array_push($urls_variation, ["uri" => parse_url($url, PHP_URL_PATH), "name" => str_replace("/", "_", trim(parse_url($url, PHP_URL_PATH), "/")),  "variation" =>  $url]);
        }

        $george = new george($nameDB);
        if ($george->registerInDB(parse_url($url_conversion, PHP_URL_PATH), $discovery_rate, $urls_variation)) { //On crée une nouvelle BDD
            header('Location: index.php?success=true&message=ABTEST créé avec succès');
        } else {
            header('Location: index.php?success=false&message=Erreur sur la création de l\'ABTEST');
        }
    }
    /**
     * Add conversion 
     * $_POST['conversion_path'] = HTTP REFERER if exist
     * $_POST['path'] = URL LP
     */
    if ($_GET['action'] == "addConversion") {

        
        $testUrl = (!empty($_POST['referer']))?$_POST['referer']:$_POST['path'];
        $variationName = George::_getVariationNamefromUrl($testUrl);        
        echo $variationName;

        if ($variationName != "ref.php" || $variationName != "/") {
            $george = new george($variationName); // On vérifie si une bdd avec le nom existe
            $data = @($george->get_data($variationName))[0];            
            $myfile = fopen("log.txt", "a") or die("Unable to open file!");
            $start = new \DateTime();
            $txt = "";
            if (!empty($data)) {
                $george->save_conversion(str_replace("index.php", "", $_POST['path']));
                $txt .= "START : " . $start->format("d/m/Y H:i:s") . "\n";
                $txt .= "Variation : " . $_POST['path'] . "\n";
                $txt .= "Main Variation : " . $testUrl . "\n";
                $txt .= "TYPE DEVICE : " . $george->visit['device_type'] . "\n";
                $txt .= "IP : " . $george->visit['ip'] . "\n";
                $txt .= "CONVERSION SAVE : SUCCESSS\n";
                $end = new \DateTime();
                $txt .= "END : " . $end->format("d/m/Y H:i:s") . "\n";
                $txt .= "===================================\n";
            } else {
                $txt .= "DATE : " . $start->format("d/m/Y H:i:s") . "\n";
                $txt .= "Variation : " . $_POST['path'] . "\n";
                $txt .= "TYPE DEVICE : " . $george->visit['device_type'] . "\n";
                $txt .= "CONVERSION SAVE : FAILED\n";
                $txt .= "===================================\n";
            }
            fwrite($myfile, $txt);
            fclose($myfile);
        }
        echo @$txt;        
    }

    /**
     * Archivage
     */
    if ($_GET['action'] == "setArchive") {
        $george = new george($_GET['db']); // On vérifie si une bdd avec le nom existe
        if ($george->setArchive()) {
            header('Location: index.php?success=true&message=Archivage réussi');
        } else {
            header('Location: index.php?success=false&message=Archivage échoué');
        }
        exit;
    }
}
