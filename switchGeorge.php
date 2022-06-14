<?php
require 'class.george.php';
if (isset($_GET['action'])) {
    /**
     * Change state of ABTEST
     * $_GET['db'] = nameDB
     */
    if ($_GET['action'] == "changeState") {
        $george = new george($_GET['db']); // On vérifie si une bdd avec le nom existe
        $george->changeStatus();
        return;
    }
    /**
     * Delete DB
     * $_GET['db'] = nameDB
     */
    if ($_GET['action'] == "delete") {
        $george = new george();
        $george->deleteData("database/" . $_GET['db']); //Suppression de l'ABTest
        return;
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
            array_push($urls_variation, ["uri" => parse_url($url['value'], PHP_URL_PATH), "name" => str_replace("/", "_", trim(parse_url($url['value'], PHP_URL_PATH), "/")),  "variation" =>  $url['value']]);
        }

        $george = new george($nameDB);
        $george->registerInDB(parse_url($url_conversion, PHP_URL_PATH), $discovery_rate, $urls_variation); //On crée une nouvelle BDD
        return;
    }
    /**
     * Add conversion 
     * $_POST['conversion_path'] = HTTP REFERER if exist
     * $_POST['path'] = URL LP
     */
    if ($_GET['action'] == "addConversion") {

        $http_referer =  $_POST['conversion_path']; //HTTP Referer if exists

        if (empty($http_referer) || $http_referer == "null") { //If null, http referer is not set and is main variation
            $variationName = $_POST['path']; // Main variation
        } else { //Else http referer is set and is another variation
            $variationName = $http_referer;
        }

        $variationName = str_replace("index.php", "", $variationName); //Rewrite variation name
        $variationName = trim(str_replace("/", "_", $variationName), "_"); //Rewrite variation name


        if ($variationName != "ref.php" || $variationName != "/") {
            $george = new george($variationName); // On vérifie si une bdd avec le nom existe
            $data = $george->get_data_custom_for_conversion($variationName);

            if (!empty($data)) {
                $george->save_conversion_custom(str_replace("index.php", "", $_POST['path']));
                $myfile = fopen("log.txt", "a") or die("Unable to open file!");
                $start = new \DateTime();
                $txt = "";
                $txt .= "START : " . $start->format("d/m/Y H:i:s") . "\n";
                $txt .= "Variation : " . $_POST['path'] . "\n";
                $txt .= "Main Variation : " . $_POST['conversion_path'] . "\n";
                $txt .= "TYPE DEVICE : " . $george->visit['device_type'] . "\n";
                $txt .= "IP : " . $george->visit['ip'] . "\n";
                $txt .= "CONVERSION SAVE : SUCCESSS\n";
                $end = new \DateTime();
                $txt .= "END : " . $end->format("d/m/Y H:i:s") . "\n";
                $txt .= "===================================\n";
                fwrite($myfile, $txt);
                fclose($myfile);
            } else {
                $myfile = fopen("log.txt", "a") or die("Unable to open file!");
                $txt = "";
                $start = new \DateTime();
                $txt .= "DATE : " . $start->format("d/m/Y H:i:s") . "\n";
                $txt .= "Variation : " . $_POST['path'] . "\n";
                $txt .= "TYPE DEVICE : " . $george->visit['device_type'] . "\n";
                $txt .= "CONVERSION SAVE : FAILED\n";
                $txt .= "===================================\n";
                fwrite($myfile, $txt);
                fclose($myfile);
            }
        }
        return;
    }
}
header('Location: index.php');
exit;
