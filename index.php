<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
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
    require './library/Mobile-Detect-2.8.25/Mobile_Detect.php';
    require './library/class.browser.php';
    require './library/FlatDB/flatdb.php';
    require "class.george.php";

    ?>
    <div class="main-george">
        <h1 class="mb-3 text-center">George</h1>

        <form id="formData">
            <div class="input-group mb-3">
                <span class="input-group-text">URL Principale</span>
                <input type="url" class="form-control" name="url_conversion" id="url_conversion" placeholder="/test/lan/08/">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text">Discovery Rate</span>
                <input type="number" class="form-control" name="taux_decouvert" id="taux_decouvert" placeholder="0.0" value="0.20">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text">URL Variation</span>
                <input type="url" class="form-control" name="url[]" placeholder="/test/lan/09/">
            </div>
            <div id="anotherInput" class="anotherInput"></div>

        </form>
        <div class="d-flex align-items-center justify-content-center">
            <button id="addInput" onclick="addInput()" class="btn btn-outline-info btn-rounded mr-3">+ Add variation</button>
            <button id="send" class="btn btn-outline-primary">Start AB Test</button>
        </div>
        <a class="text-center" target="_blank" id="See" href="#"></a>
        <p class="text-center alert"></p>
    </div>

    <div class="listDB">
        <?php
        $george = new george();
        echo $george->draw_allData(); // Affichage des bdd disponible
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
        $("#addInput").click(function() {
            $('.anotherInput').append(`
                <div class="input-group mb-3">
                    <span class="input-group-text">URL Variation</span>
                    <input type="url" class="form-control" name="url[]" placeholder="test/lan/XX/">
                </div>`);
        });


        $("#send").click(() => {
            var form_fields = $('[name="url[]"').serializeArray();
            let checked = true;

            form_fields.forEach(element => {
                if (element.value == "") {
                    checked = false;
                }
            });

            if ($('#url_conversion').val() == "" || $('#taux_decouvert').val() == "") {
                checked = false;
            }
            if (checked) {
                $.post("addABTest.php", {
                    url_conversion: $('#url_conversion').val(),
                    taux_decouvert: $('#taux_decouvert').val(),
                    url_variations: form_fields
                }).done(function(data) {
                    $('#See').attr('href', $('#url_conversion').val());
                    $('#See').text("See");
                    $('.alert').css("color", "green");
                    $('.alert').text("Success !");
                });
            } else {
                $('.alert').css("color", "orange");
                $('.alert').text("Veuillez remplir tous les champs !");
                setTimeout(function() {
                    $('.alert').text("");
                }, 4000)
            }
        })
    </script>
</body>

</html>