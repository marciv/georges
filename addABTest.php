<?php

require "class.george.php";

$url_conversion = $_POST['url_conversion'];
$discovery_rate = $_POST['taux_decouvert'];
$urls_variation = []; // Stockage des URLS 

$nameDB = str_replace("/", "_", trim(parse_url($url_conversion, PHP_URL_PATH), "/"));

foreach ($_POST['url_variations'] as $url) {
    array_push($urls_variation, ["uri" => parse_url($url['value'], PHP_URL_PATH), "name" => str_replace("/", "_", trim(parse_url($url['value'], PHP_URL_PATH), "/")),  "variation" =>  $url['value']]);
}

$george = new george($nameDB);
$george->registerInDB(parse_url($url_conversion, PHP_URL_PATH), $discovery_rate, $urls_variation); //On crée une nouvelle BDD
