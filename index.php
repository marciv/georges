<?php
header("Cache-Control: no-cache, must-revalidate");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="css/rocket.png">
    <title>George</title>

    <style>
        <?php @include_once "css/bootstrap.min.css"; ?>
    </style>

    <link href="./css/style.css" rel="stylesheet">
</head>

<body>
    <?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require "../../config.php";

    use library\George as george;

    if (isset($_GET['success']) && isset($_GET['message'])) {
        if ($_GET['success'] == "true") {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <span class="message-contenu"><strong>Succès ! </strong>' . @$_GET['message'] . '</span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="message-contenu"><strong>Erreur ! </strong> ' . @$_GET['message'] . '</span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>';
        }
    }
    ?>
    <form method="POST" action="switchGeorge.php?action=generateABTEST">
        <button type="submit">Generate ABTEST test</button>
    </form>
    <div class="main-george">
        <h1 class="mb-3 text-center">George</h1>

        <form id="formData" method="POST" action="switchGeorge.php?action=createDB">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <span class="toSection" target="step-one">Start</span>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="toSection" target="step-two">Settings</span>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="toSection" target="step-three">Variations</span>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="toSection" target="step-for">Filters</span>
                    </li>
                </ol>
            </nav>
            <section id="step-one" target="step-two">
                <div class="form-group">
                    <label for="nameABtest">Name ABTest</label>
                    <input type="text" class="form-control" name="nameABtest" id="nameABtest" placeholder="">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" name="description" id="description"></textarea>
                </div>
                <div class="d-flex align-items-center justify-content-center">
                    <span class="next btn btn-outline-primary">Suivant</span>
                </div>
            </section>
            <section id="step-two" target="step-three" style="display:none">
                <div class="form-group">
                    <label for="urlPrincipal">Main URL</label>
                    <input type="text" class="form-control" name="url_conversion" id="url_conversion" placeholder="/test/lan/08/">
                    <small id="urlPrincipal" class="form-text text-muted">Main url must start and end with "/".</small>
                </div>
                <div class="form-group">
                    <label for="DiscoveryRate">Discovery rate</label>
                    <input type="number" class="form-control" name="taux_decouvert" id="taux_decouvert" placeholder="0.0" value="0.20" min="0.01" step="0.01" max="0.25">
                </div>
                <div class="d-flex align-items-center justify-content-center">
                    <span class="next btn btn-outline-primary">Suivant</span>
                </div>
            </section>
            <section id="step-three" target="step-for" style="display:none">

                <h5>Variation n°1</h5>
                <div class="form-group">
                    <label for="name_variation_one">Name</label>
                    <input type="text" class="form-control" name="name_variation_one" id="name_variation_one">
                </div>
                <div class="form-group">
                    <label for="variation_one">Url </label>
                    <input type="text" class="form-control" name="variation_one" id="variation_one" placeholder="/test/lan/XX/">
                    <small class="form-text text-muted">Variation url must start and end with "/".</small>
                </div>

                <div class="more_variation" style="display:none">
                    <h5>Variation n°2</h5>
                    <div class="form-group">
                        <label for="name_variation_two">Name</label>
                        <input type="text" class="form-control" name="name_variation_two" id="name_variation_two">
                    </div>
                    <div class="form-group">
                        <label for="variation_two">Url </label>
                        <input type="text" class="form-control" name="variation_two" id="variation_two" placeholder="/test/lan/XX/">
                        <small class="form-text text-muted">Variation url must start and end with "/".</small>
                    </div>
                </div>
                <small class="form-text text-muted">*if you want to add another variation, do it once the abtest is created</small>

                <div class="mt-2 d-flex align-items-center justify-content-center">
                    <span class="addVariation btn btn-outline-secondary">Add Variation</span>
                    <span class="next btn btn-outline-primary">Suivant</span>
                </div>
            </section>

            <section id="step-for" style="display:none">
                <div class="form-group">
                    <label for="inputState">Devices</label>
                    <select class="form-control" name="device_type" id="device_type">
                        <option value="0" selected>Devices...</option>
                        <option value="computer">Computer</option>
                        <option value="mobile">Mobile</option>
                        <option value="tablet">Tablet</option>
                    </select>
                </div>
                <!-- 
                <select class="form-select" name="browser" id="browser">
                    <option value="0" selected>Browser</option>
                    <option value="1">One</option>
                    <option value="2">Two</option>
                    <option value="3">Three</option>
                </select> -->

                <div class="form-group">
                    <label for="utm_source">Utm_source</label>
                    <input type="text" class="form-control" name="utm_source" placeholder="laisser vide si null" id="utm_source">
                </div>

                <div class="form-group">
                    <label for="utm_content">Utm_content</label>
                    <input type="text" class="form-control" name="utm_content" placeholder="laisser vide si null" id="utm_content">
                </div>

                <div class="form-group">
                    <label for="utm_campaign">Utm_campaign</label>
                    <input type="text" class="form-control" name="utm_campaign" placeholder="laisser vide si null" id="utm_campaign">
                </div>

                <div class="form-group">
                    <label for="utm_term">Utm_term</label>
                    <input type="text" class="form-control" name="utm_term" placeholder="laisser vide si null" id="utm_term">
                </div>
                <div class="d-flex align-items-center justify-content-center">
                    <button type="submit" class="btn btn-outline-primary">Start AB Test</button>
                </div>
            </section>
        </form>

        <p class="text-center message-contenu"></p>
    </div>

    <div class="container">
        <ul class="nav nav-pills justify-content-left mb-3 w-100" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="ml-3 nav-link btn btn-outline-primary active" id="pills-play-tab" data-toggle="pill" href="#pills-play" role="tab" aria-controls="pills-play" aria-selected="true">En cours</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="ml-3 nav-link btn btn-outline-secondary" id="pills-pause-tab" data-toggle="pill" href="#pills-pause" role="tab" aria-controls="pills-pause" aria-selected="false">En pause</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="ml-3 nav-link btn btn-outline-warning" id="pills-archived-tab" data-toggle="pill" href="#pills-archived" role="tab" aria-controls="pills-archived" aria-selected="false">Archivé</a>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="pills-tabContent">
        <?php
        $george = new george();
        echo $george->draw_allData(); // Affichage des bdd disponible

        echo '<div class="tab-pane fade " id="pills-archived" role="tabpanel" aria-labelledby="pills-archived-tab"><div class="listDB">';
        echo $george->draw_allData("archived"); // Affichage des bdd archivées
        echo '</div></div>';
        ?>
    </div>



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
    <script src="js/main.js"></script>
</body>

</html>