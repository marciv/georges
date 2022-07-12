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
    <div class="main-george">
        <h1 class="mb-3 text-center">George</h1>

        <form id="formData" onsubmit="return checkInput();" method="POST" action="switchGeorge.php?action=createDB">
            <div class="form-group">
                <label for="urlPrincipal">Main URL</label>
                <input type="text" class="form-control" name="url_conversion" id="url_conversion" placeholder="/test/lan/08/">
                <small id="urlPrincipal" class="form-text text-muted">Main url must start and end with "/".</small>
            </div>
            <div class="form-group">
                <label for="DiscoveryRate">Discovery rate</label>
                <input type="number" class="form-control" name="taux_decouvert" id="taux_decouvert" placeholder="0.0" value="0.20" min="0.01" step="0.01" max="0.25">
            </div>

            <div class="form-group">
                <label for="urlPrincipal">Variation URL</label>
                <input type="text" class="form-control" name="url_variations[]" id="url_variations[]" placeholder="/test/lan/XX/">
                <small id="urlPrincipal" class="form-text text-muted">Variation url must start and end with "/".</small>
            </div>

            <div id="anotherInput" class="anotherInput"></div>
            <div class="d-flex align-items-center justify-content-center">
                <button type="button" id="addInput" class="btn btn-outline-info btn-rounded mr-3">+ Add variation</button>
                <button type="submit" class="btn btn-outline-primary">Start AB Test</button>
            </div>

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
    <script>
        //Refresh avec alert
        if (location.search != "") {
            setTimeout(function() {
                window.location.href = "index.php";
            }, 2000);
        }

        function checkInput(e) {
            let checked = true;
            let url_variations = $('input[name="url_variations[]"]');

            url_variations.each(function() {
                if ($(this).val() == "") {
                    checked = false;
                }

                if (first($(this).val()) != "/" || last($(this).val()) != "/") {
                    alert("URL Variation must start and end with /");
                    $(this).css("background-color", "rgba(253, 111, 111, 0.3)");
                    checked = false;
                }

            });


            if (first($('#url_conversion').val()) != "/" || last($('#url_conversion').val()) != "/") {
                alert("URL Conversion must start and end with /");
                checked = false;
                $('#url_conversion').css("background-color", "rgba(253, 111, 111, 0.3)");
            }

            if ($('#url_conversion').val() == "" || $('#taux_decouvert').val() == "") {
                checked = false;
                $('#url_conversion').css("background-color", "rgba(253, 111, 111, 0.3)");
                $('#taux_decouvert').css("background-color", "rgba(253, 111, 111, 0.3)");
            }
            if ($('#taux_decouvert').val() > 0.25) {
                alert("Discovery Rate must be less than 0.25");
                checked = false;
                $('#taux_decouvert').css("background-color", "rgba(253, 111, 111, 0.3)");
            }

            if (checked) {
                return true;
            } else {
                event.preventDefault();
                setTimeout(function() {
                    $('.message-contenu').text("");
                }, 4000);
                return false;
            }
        }

        function first(str) {
            first_part = str.substring(0, 1);
            return first_part;
        }

        function last(str) {
            last_part = str.substring(str.length - 1);
            return last_part;
        }

        $("#addInput").click(function() {
            $('.anotherInput').append(`
            <div class="form-group">
                <label for="urlPrincipal">Variation url</label>
                <input type="text" class="form-control" name="url_variations[]" id="url_variations[]" placeholder="/test/lan/XX/">
                <small id="urlPrincipal" class="form-text text-muted">Variation url must start and end with "/".</small>
            </div>`);
        });
    </script>
</body>

</html>