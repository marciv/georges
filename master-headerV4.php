<!-- Google Tag Manager -->
<script>
    (function(w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({
            'gtm.start': new Date().getTime(),
            event: 'gtm.js'
        });
        var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s),
            dl = l != 'dataLayer' ? '&l=' + l : '';
        j.async = true;
        j.src =
            'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-TL9GTG4');
</script>
<!-- End Google Tag Manager -->
<script>
    (function() {
        try {
            window.addEventListener("load", function() {
                dataLayer.push({
                    'event': 'afterLoadV2'
                });
                console.log('push evenet afterLoadV2');
            }, false);
        } catch (err) {}
    })();
</script>

<?php
require  ABSPATH . LIB . "/George/class.george.php";
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = str_replace('index.php', '', $request_uri);
$variationName = trim(str_replace("/", "_",  $request_uri), "_"); //Nom variation actuel 
$variableQuery =  parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ? "?" . parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) : "";   //Récupération des query
$george = new george($variationName); // On vérifie si une bdd avec le nom existe
$data = $george->get_data_custom();
if (isset($data['uri']) && ($data['uri'] == $request_uri  && !empty($data) || $data != false)) {
    //SI C'EST EN BDD ALORS ON LANCE LE SCRIPT
    // options
    $george->set_option(
        array(
            "discovery_rate" => $data['discovery_rate'],
            "default_view" => $data['default_view'],
        )
    );
    $george->add_variation(
        array(
            $variationName => array( //Name variation
                "lp" => "", //Link variation
            )
        )
    );
    foreach ($data['listVariation'] as $v) { //On parcours la liste des variations disponible 
        $george->add_variation(
            array(
                $v['name'] => array( //Name variation
                    "lp" => "https://" . $_SERVER['HTTP_HOST'] . $v['uri'] . $variableQuery, //Link variation
                )
            )
        );
    }
    $george->calculate(); // On ajoute à la variation actuel
    if ($variationName == $george->selected_view_name) {
        $george->render('lp');
    } else {
        echo '<script>window.location="' . $george->render("lp") . '"</script>';
    }
} else {
    try {
        $george->deleteData(ABSPATH . LIB . "/George/database/" . $variationName);
    } catch (\Throwable $th) {
    }
}

?>

<script>
    document.addEventListener('DOMContentLoaded', function(event) {
        document.addEventListener('form-sended', function(event) { //Event custom quand le form est envoyé et validé 
            var formData = new FormData();
            formData.append("path", window.location.pathname);
            var http_referer = <?php echo json_encode($_SERVER['HTTP_REFERER']); ?>;
            formData.append("conversion_path", http_referer);

            var xmlHttp = new XMLHttpRequest();
            xmlHttp.onreadystatechange = function() {
                if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
                    console.log(xmlHttp.responseText);
                }
            }
            var i = "../../../library/George/addConversion.php";
            xmlHttp.open("post", i)
            xmlHttp.send(formData);
        });
    });
</script>