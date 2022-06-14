<?php
require 'class.george.php';
$george = new george($_GET['db']); // On vérifie si une bdd avec le nom existe
$george->changeStatus();
header('Location: index.php');
