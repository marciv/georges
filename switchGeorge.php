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
        $parameters = $george->parameters; // On récupère les paramètres de la bdd

        if (empty($parameters)) {
            header('Location: index.php?success=false&message=Une erreur est survenue avec ' . $_GET['db']);
            exit;
        }

        if ($parameters['status'] == "1") {
            $parameters['status'] = "0";
        } else {
            $parameters['status'] = "1";
        }

        if ($george->updateAbTest($parameters)) {
            header('Location: index.php?success=true&message=Status de l\'ABTEST ' . $_GET['db'] . ' changé');
        } else {
            header('Location: index.php?success=false&message=Une erreur est survenue avec ' . $_GET['db']);
        }
        exit;
    }

    if ($_GET['action'] == "changeDiscoveryRate") {
        $george = new george($_GET['db']); // On vérifie si une bdd avec le nom existe
        $parameters = $george->parameters; // On récupère les paramètres de la bdd

        if (empty($parameters)) {
            header('Location: index.php?success=false&message=Une erreur est survenue avec ' . $_GET['db']);
            exit;
        }

        $parameters['discovery_rate'] = $_POST['discovery_rate'];

        if ($george->updateAbTest($parameters)) {
            header('Location: index.php?success=true&message=Discovery Rate de l\'ABTEST ' . $_GET['db'] . ' changé');
        } else {
            header('Location: index.php?success=false&message=Une erreur est survenue');
        }
        exit;
    }

    if ($_GET['action'] == "addVariationToAbtest") {
        $george = new george($_GET['db']); // On vérifie si une bdd avec le nom existe
        if ($george->addVariationToAbtest($_POST['variation'], $_POST['name_variation'])) {
            header('Location: index.php?success=true&message=Variation ' . $_POST['variation'] . ' ajoutée à l\'ABTEST');
        } else {
            header('Location: index.php?success=false&message=Une erreur est survenue');
        }
        exit;
    }

    if ($_GET['action'] == "editFilter") {
        $george = new george($_GET['db']); // On vérifie si une bdd avec le nom existe
        $parameters = $george->parameters; // On récupère les paramètres de la bdd

        $filters = ["device_type" =>  $_POST['device_type'], "utm_source" => $_POST['utm_source'], "utm_term" => $_POST['utm_term'], "utm_content" => $_POST['utm_content'], "utm_campaign" => $_POST['utm_campaign']];

        $parameters['filters'] = $filters;

        if ($george->updateAbTest($parameters)) {
            header('Location: index.php?success=true&message=Filtre de l\'ABTEST ' . $_GET['db'] . ' changé');
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
     * GENERATE DEV ABTEST 
     */
    if ($_GET['action'] == "generateABTEST") {
        $url_conversion = "/1root/test/lan/08/";
        $discovery_rate = 0.20;

        $searchDB = George::_getVariationNamefromUrl($url_conversion);

        $nameABtest = "ABTEST generated" ?? $searchDB;
        $description = "Abtest generate automactically";

        $filters = ["device_type" =>  "computer", "utm_source" => "ag3", "utm_term" => "", "utm_content" => "", "utm_campaign" => ""];
        $urls_variation = [["uri" => $url_conversion, "name" => "Main URL",  "variation" =>  $searchDB], ["uri" => "/1root/test/lan/09/", "name" => "First Variation",  "variation" =>  "1root_test_lan_09"]]; // Stockage des URLS 


        $george = new george($searchDB);
        if ($george->registerInDB($discovery_rate, $filters, $urls_variation, $nameABtest, $description)) { //On crée une nouvelle BDD
            header('Location: index.php?success=true&message=ABTEST créé avec succès');
        } else {
            header('Location: index.php?success=false&message=Erreur sur la création de l\'ABTEST');
        }
    }
    //FIN GENERATE DEV 
    /**
     * Create DB with POST
     * $_POST['name'] = nameDB
     * $_POST['discovery_rate'] = discovery_rate *
     * $_POST['device_type'] = device_type
     * $_POST['utm_source'] = utm_source
     * $_POST['utm_term'] = utm_term
     * $_POST['utm_content'] = utm_content
     * $_POST['utm_campaign'] = utm_campaign
     * $_POST['url_conversion'] = url_conversion *
     * $_POST['name_variation_one'] = name_variation
     * $_POST['variation_one'] = variation *
     * * $_POST['name_variation_two'] = name_variation
     * $_POST['variation_two'] = variation
     */

    if ($_GET['action'] == "createDB") {
        $url_conversion = $_POST['url_conversion'];
        $discovery_rate = $_POST['taux_decouvert'];
        $description = $_POST['description'];

        $searchDB = George::_getVariationNamefromUrl($url_conversion);
        $nameABtest = $_POST['nameABtest'] ?? $searchDB;

        $urls_variation = []; // Stockage des URLS 


        $filters = ["device_type" =>  $_POST['device_type'], "utm_source" => $_POST['utm_source'], "utm_term" => $_POST['utm_term'], "utm_content" => $_POST['utm_content'], "utm_campaign" => $_POST['utm_campaign']];

        //Main
        array_push($urls_variation, ["uri" => $url_conversion, "name" => $_POST['name_main_url'],  "variation" =>  $searchDB]);
        //First Variation
        $variation_one_replaced = George::_getVariationNamefromUrl($_POST['variation_one']);
        array_push($urls_variation, ["uri" => $_POST['variation_one'], "name" => $_POST['name_variation_one'] != "" ? $_POST['name_variation_one'] : $variation_one_replaced,  "variation" =>  $variation_one_replaced]);
        //Second Variation
        if (!empty($_POST['variation_two']) && $_POST['variation_two'] != "") {
            $variation_two_replaced = George::_getVariationNamefromUrl($_POST['variation_two']);
            array_push($urls_variation, ["uri" => $_POST['variation_two'], "name" => $_POST['name_variation_two'] != "" ? $_POST['name_variation_two'] : $variation_two_replaced,  "variation" =>  $variation_two_replaced]);
        }

        $george = new george($searchDB);
        if ($george->registerInDB($discovery_rate, $filters, $urls_variation, $nameABtest, $description)) { //On crée une nouvelle BDD
            header('Location: index.php?success=true&message=ABTEST créé avec succès');
        } else {
            header('Location: index.php?success=false&message=Erreur sur la création de l\'ABTEST, il doit déjà exister');
        }
    }
    /**
     * Add conversion 
     * $_POST['conversion_path'] = HTTP REFERER if exist
     * $_POST['path'] = URL LP
     */
    if ($_GET['action'] == "addConversion") {
        $testUrl = (!empty($_POST['referer'])) ? $_POST['referer'] : $_POST['path'];
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
