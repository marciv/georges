<?php
//Nothing now
require  "class.george.php";

$variationName =  parse_url($_POST['conversion_path'], PHP_URL_PATH); //HTTP Referer
if (empty($variationName) || $variationName == "null") { //Si pas HTTP Referer alors on ajoute la conversion au path actuel
    $variationName = $_POST['path'];
}


$george = new george(trim(str_replace("/", "_", $variationName), "_")); // On vérifie si une bdd avec le nom existe

$data = $george->get_data_custom_for_conversion();

if (!empty($data)) {
    if ($variationName == $_POST['path']) { //Si pas HTTP Referer NULL 
        $george->save_conversion_custom("");
    } else { //Sinon on ajoute au path 
        $george->save_conversion_custom($_POST['path']);
    }
}
