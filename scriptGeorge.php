<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = str_replace('index.php', '', $request_uri);
$variationName = trim(str_replace("/", "_",  $request_uri), "_"); //Nom variation actuel 
$variableQuery =  parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ? "?" . parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) : "";   //Récupération des query
$george = new george($variationName); // On vérifie si une bdd avec le nom existe
$data = $george->get_data_custom();

var_dump($data);

if (isset($data['uri']) && ($data['uri'] == $request_uri  && !empty($data) || $data != false) && $data['status'] != 1) {
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

    if (empty($variableQuery)) {
        $http_referer = "?http_referer=" . $variationName;
    } else {
        $http_referer = "&http_referer=" . $variationName;
    }

    foreach ($data['listVariation'] as $v) { //On parcours la liste des variations disponible 
        $george->add_variation(
            array(
                $v['name'] => array( //Name variation
                    "lp" => $v['uri'] . $variableQuery . $http_referer, //Link variation
                )
            )
        );
    }
    $george->calculate(); // On ajoute à la variation actuel
    if ($variationName == $george->selected_view_name) {
        $george->render('lp');
    } else {
        header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $george->render("lp"));
        exit;
        // echo '<script>window.location="https://"+window.location.host+"' . $george->render("lp") . '"</script>';
    }
}
?>

<script>
    window.addEventListener('load', (event) => {
        document.addEventListener('form-sended', function(event) { //Event custom quand le form est envoyé et validé 

            console.log('FORM SENDED');

            var formData = new FormData();
            formData.append("path", window.location.pathname);
            var http_referer = <?php echo json_encode($_GET['http_referer']); ?>;
            var status = <?php echo $data['status']; ?>;

            console.log(http_referer);
            if (http_referer == null || http_referer == undefined || http_referer == "") {
                http_referer = null;
            }

            formData.append("conversion_path", http_referer);


            var xmlHttp = new XMLHttpRequest();
            xmlHttp.onreadystatechange = function() {
                if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
                    console.log(xmlHttp.responseText);
                }
            }
            var i = "../../../library/George/addConversion.php";
            xmlHttp.open("post", i)

            if (status != "1") {
                xmlHttp.send(formData);
            }
        });
    });
</script>