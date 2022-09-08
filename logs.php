<?php
header("Cache-Control: no-cache, must-revalidate");
require "../../config.php";

use library\George as george;

$logs = $george->get_log();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="css/rocket.png">
    <link href="./css/style.css" rel="stylesheet">

    <title>Logs</title>
    <style>
        <?php @include_once "css/bootstrap.min.css"; ?>
    </style>
</head>

<body>
    <a class="btn btn-secondary" href="index.php">Back</a>
    <h1>Logs</h1>
    <div class="container">
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th scope="col">#Date</th>
                    <th scope="col">Main</th>
                    <th scope="col">Variation</th>
                    <th scope="col">Devices</th>
                    <th scope="col">IP</th>
                    <th scope="col">Status</th>
                    <th scope="col">Accès</th>
                </tr>
            </thead>
            <tbody>

                <?php
                foreach (array_reverse($logs) as $entry) {
                ?>
                    <tr>
                        <th scope="row"><?= $entry['Date'] ?></th>
                        <td class="text-primary"><?= empty($entry['Main']) ? "Pas d'ABTEST" :  $entry['Main'] ?></td>
                        <td><?= empty($entry['Variation']) ? "Pas d'ABTEST" :  $entry['Variation'] ?></td>
                        <td><?= $entry['Devices'] ?></td>
                        <td><?= $entry['IP'] ?></td>
                        <td><?php echo $entry['Status'] == 1 ? "<span class='text-success'>Succès</span>" : "<span class='text-danger'>Fail</span>"; ?></td>
                        <?php if (isset($entry['Access'])) {
                        ?>
                            <td><?= $entry['Access'] ?></td>
                        <?php
                        }
                        ?>
                    </tr>
                <?php
                }
                ?>

            </tbody>
        </table>
    </div>

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