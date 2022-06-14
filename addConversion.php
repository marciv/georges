<?php
//Nothing now
require  "class.george.php";

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
        $txt .= "Variation : " . $_POST['path'] . "\n";
        $txt .= "Main Variation : " . $_POST['conversion_path'] . "\n";
        $txt .= "TYPE DEVICE : " . $george->visit['device_type'] . "\n";
        $txt .= "CONVERSION SAVE : FAILED\n";
        $txt .= "===================================\n";
        fwrite($myfile, $txt);
        fclose($myfile);
    }
}
