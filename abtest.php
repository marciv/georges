<?php
require  "class.george.php";
$dbName = $_GET['dbName'];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>ABTest <?php echo $dbName; ?></title>
    <style>
        <?php @include_once "css/bootstrap.min.css"; ?>
    </style>

    <link href="./css/style_abtest.css" rel="stylesheet">
</head>

<body>
    <div class="mt-5 text-center d-flex justify-content-around align-items-center">
        <a class="btn btn-outline-secondary" href="index.php">Retour</a>
        <a class='btn btn-outline-danger' href='delDB.php?db=<?= $dbName ?>'>Delete</a>
    </div>
    <?php
    if (!empty($dbName)) {
        $george = new George($dbName);
    ?>
        <div class=" container">
            <?php
            echo $george->draw_abtest();
            ?>
        </div>
    <?php
    } else {
        echo "Aucune donnée disponible !";
    }
    ?>
    <script src="../js/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="../js/popper-1.12.9.min.js" crossorigin="anonymous"></script>
    <script src="../bs4/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js"></script>
    <script>
        WebFont.load({
            google: {
                families: ['Fira Sans', 'Nunito']
            }
        });
    </script>
</body>

</html>