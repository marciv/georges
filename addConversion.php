<?php
//Nothing now
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require  "class.george.php";

$variationName =  parse_url($_POST['conversion_path'], PHP_URL_PATH); //HTTP Referer

if (empty($variationName) || $variationName == "null") { //Si pas HTTP Referer alors on ajoute la conversion au path actuel
    $variationName = $_POST['path'];
}

$variationName = str_replace("index.php", "", $variationName);
$george = new george(trim(str_replace("/", "_", $variationName), "_")); // On vérifie si une bdd avec le nom existe

$data = $george->get_data_custom_for_conversion();


if (!empty($data)) {
    if ($variationName == str_replace("index.php", "", $_POST['path'])) { //Si pas HTTP Referer NULL 
        $george->save_conversion_custom("");
    } else { //Sinon on ajoute au path 
        $george->save_conversion_custom(str_replace("index.php", "", $_POST['path']));
    }
}
