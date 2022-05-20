<?php
//Nothing now
require  "class.george.php";

$myfile = fopen("log.txt", "a") or die("Unable to open file!");

$start = new \DateTime();
$txt .= "START : " . $start->format("d/m/Y H:i:s") . "\n";
$txt .= "Variation : " . $_POST['path'] . "\n";
$txt .= "HTTP REFERER : " . $_POST['conversion_path'] . "\n";




$variationName =  parse_url($_POST['conversion_path'], PHP_URL_PATH); //HTTP Referer

$txt .= "Variation Name AVANT modif : " . $variationName . "\n";


if (empty($variationName) || $variationName == "null") { //Si pas HTTP Referer alors on ajoute la conversion au path actuel
    $variationName = $_POST['path'];
}

$variationName = str_replace("index.php", "", $variationName);

$txt .= "Variation Name APRES modif : " . trim(str_replace("/", "_", $variationName), "_") . "\n";


if (trim(str_replace("/", "_", $variationName), "_") != "ref.php" || trim(str_replace("/", "_", $variationName), "_") != "/") {
    $george = new george(trim(str_replace("/", "_", $variationName), "_")); // On vérifie si une bdd avec le nom existe
    $data = $george->get_data_custom_for_conversion(trim(str_replace("/", "_", $variationName), "_"));

    $txt .= "DATA : " . $data . "\n";
    $txt .= "PATH => " . $_POST['path'] . "\n";

    if (!empty($data)) {
        $george->save_conversion_custom(str_replace("index.php", "", $_POST['path']));

        $txt .= "CONVERSION SAVE : SUCCESSS\n";
    } else {
        $txt .= "CONVERSION SAVE : FAILED\n";
    }
} else {
    $txt .= "DETECTION SPAM BOT\n";
}

$txt .= "TYPE DEVICE : " . $george->visit['device_type'] . "\n";
$txt .= "IP : " . $george->visit['ip'] . "\n";

$end = new \DateTime();
$txt .= "END : " . $end->format("d/m/Y H:i:s") . "\n";
$txt .= "===================================\n";


fwrite($myfile, $txt);
fclose($myfile);
