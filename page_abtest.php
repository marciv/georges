<?php
header("Cache-Control: no-cache, must-revalidate");
require "../../config.php";

use library\George as george;

$dbName = $_GET['dbName'];
if (!empty($dbName)) {
    $george = new George($dbName);
    $data = $george->dataDB;
    $parameters = $george->parameters;
    $state = $parameters['status'] == 0 ? "En cours" : "En pause";
    $abtest = @$george->_array_msort($data, array('tx_conversion' => SORT_DESC, 'nb_visit' => SORT_DESC));

?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex,nofollow">
        <link rel="icon" href="css/rocket.png">

        <title>ABTest <?php echo $parameters['name']; ?></title>
        <style>
            <?php @include_once "css/bootstrap.min.css"; ?>
        </style>

        <link href="./css/style_abtest.css" rel="stylesheet">
    </head>

    <body>
        <div class="container">
            <nav style="--bs-breadcrumb-divider: >" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $parameters['name'];; ?></li>
                </ol>
            </nav>
        </div>
        <div class="container">
            <div class="headerCard">
                <div class="date_crea text-center">Date de création : <?= $parameters['date_time']; ?></div>
                <div class="discovery_rate text-center d-flex align-items-center justify-content-between">
                    <p><span class="text-info"><?= $state; ?></span> | Taux de découverte : <?= $parameters['discovery_rate'] * 100; ?>%</p>
                    <div class="dropdown">
                        <p class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</p>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item text-info" href="switchGeorge.php?action=changeState&db=<?= $data[0]['variation']; ?>">Pause/Play</a>
                            <a class="dropdown-item text-danger" href="switchGeorge.php?archived=false&action=delete&db=<?= $data[0]['variation']; ?>">/!\ Supprimer</a>
                            <hr />
                            <a type="button" data-toggle="modal" data-target="#updateDiscoveryRate" class="dropdown-item text-secondary">Edit Discovery Rate</a>
                            <a type="button" data-toggle="modal" data-target="#addVariation" class="dropdown-item text-secondary">Add Variation Rate</a>
                        </div>
                    </div>
                </div>
                <div class="description">
                    <?= $parameters['description'] ?>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-center flex-wrap">

                <?php
                $i = 0;
                foreach ($abtest as $key => $value) {
                ?>
                    <div class="card col-12 col-sm-6">
                        <h2 class="text-center"><?= $value['variation']; ?></h2>
                        <p>Nombre visiteur(s) : <b>D(<?= $value['nb_visit_desktop']; ?>)</b> | <b>M(<?= $value['nb_visit_mobile']; ?>)</b> | <b>T(<?= $value['nb_visit_tablet']; ?>)</b></p>
                        <div class="row justify-content-center align-items-center">
                            <div class="col-12 col-sm-6">
                                <h6 class="mt-5 text-center">Nombre de visiteur</h6>
                                <div class="roundedCardText mx-auto">
                                    <div><b><?= $value['nb_visit']; ?></b></div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <h6 class="mt-5 text-center">Convertion mobile</h6>
                                <div class="roundedCardText mx-auto">
                                    <div><b><?= @round(($value['nb_conversion_mobile'] / $value['nb_visit_mobile']) * 100, 1) ?>%</b></div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <h6 class="mt-5 text-center">Conversion tablette</h6>
                                <div class="roundedCardText mx-auto">
                                    <div><b><?= @round(($value['nb_conversion_tablet'] / $value['nb_visit_tablet']) * 100, 1) ?>%</b></div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <h6 class="mt-5 text-center">Conversion PC</h6>
                                <div class="roundedCardText mx-auto">
                                    <div><b><?= @round(($value['nb_conversion_desktop'] / $value['nb_visit_desktop']) * 100, 1) ?>%</b></div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <h6 class="mt-5 text-center">Conversion total</h6>
                                <div class="roundedCardText mx-auto">
                                    <div><b><?= $value['nb_conversion']; ?></b></div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <h6 class="mt-5 text-center">Taux de conversion</h6>
                                <?php if ($i == 0) { ?>
                                    <div class="roundedCardText text-white bg-primary mx-auto">
                                    <?php } else { ?>
                                        <div class="roundedCardText text-white bg-secondary mx-auto">
                                        <?php } ?>
                                        <div>
                                            <b><?= $value['tx_conversion']; ?>%</b>
                                        </div>
                                        </div>
                                    </div>

                            </div>
                        </div>
                    <?php
                    $i++;
                }
                    ?>
                    <div class="cardStat col-12">
                        <div class="container mx-auto">
                            <ul class="nav nav-pills justify-content-left mb-3 w-100" id="pills-tab" role="tablist">
                                <li class="nav-item mx-auto" role="presentation">
                                    <a class="ml-3 nav-link btn btn-outline-primary active" id="pills-tx-tab" data-toggle="pill" href="#pills-tx" role="tab" aria-controls="pills-tx" aria-selected="true">Conversion</a>
                                </li>
                                <li class="nav-item mx-auto" role="presentation">
                                    <a class="ml-3 nav-link btn btn-outline-primary" id="pills-visit-tab" data-toggle="pill" href="#pills-visit" role="tab" aria-controls="pills-visit" aria-selected="false">Visite</a>
                                </li>
                            </ul>
                        </div>
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active " id="pills-tx" role="tabpanel" aria-labelledby="pills-tx-tab">
                                <canvas id="donut_tx_conversion"></canvas>
                            </div>
                            <div class="tab-pane fade" id="pills-visit" role="tabpanel" aria-labelledby="pills-visit-tab">
                                <canvas id="donut_visit"></canvas>
                            </div>
                        </div>
                    </div>
                    </div>

            </div>



            <!-- MODAL UPDATE DISCOVERY RATE DATA -->
            <div class="modal fade" id="updateDiscoveryRate" tabindex="-1" role="dialog" aria-labelledby="updateDiscoveryRateTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateDiscoveryRateTitle">Modification Discovery Rate</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="post" action="switchGeorge.php?action=changeDiscoveryRate&db=<?= $data[0]['variation']; ?>">
                            <div class="modal-body">
                                <div class="input-group mb-3">
                                    <input type="number" class="form-control" name="discovery_rate" id="discovery_rate" min="0.01" step="0.01" max="0.25" value="<?= $parameters['discovery_rate'] ?>">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" onclick="return e.preventDefault();" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- MODAL ADD Variation DATA -->
            <div class="modal fade" id="addVariation" tabindex="-1" role="dialog" aria-labelledby="addVariationTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addVariationTitle">Ajouter une variation</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="post" onsubmit="return checkVariation();" action="switchGeorge.php?action=addVariationToAbtest&db=<?= $data[0]['variation']; ?>">
                            <div class="modal-body">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" name="variation" id="variation" placeholder="/test/lan/XX/">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" onclick="return e.preventDefault();" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php
    } else {
        echo "Aucune donnée disponible !";
    }
        ?>
        <script src="../js/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
        <script src="../js/popper-1.12.9.min.js" crossorigin="anonymous"></script>
        <script src="../bs4/js/bootstrap.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.8.0/chart.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.0.0/chartjs-plugin-datalabels.min.js"></script>
        <script>
            function checkVariation() {
                let checked = true;
                let url_variations = $('#variation').val();

                if (first($('#variation').val()) != "/" || last($('#variation').val()) != "/") {
                    alert("URL Conversion must start and end with /");
                    checked = false;
                    $('#variation').css("background-color", "rgba(253, 111, 111, 0.3)");
                }

                if (checked) {
                    return true;
                } else {
                    event.preventDefault();
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


            let dbData = <?= json_encode($george->get_data()) ?>;

            let SetName = [];
            let txConversionSetCount = [];
            let conversionSetCount = [];
            let visitDesktopSetCount = [];
            let visitMobileSetCount = [];
            let visitTabletSetCount = [];




            dbData.forEach(element => {
                if (element.uri != null) {
                    SetName.push(element.uri);
                    txConversionSetCount.push(element.tx_conversion);
                    conversionSetCount.push(element.nb_conversion);
                    visitDesktopSetCount.push(element.nb_visit_desktop);
                    visitMobileSetCount.push(element.nb_visit_mobile);
                    visitTabletSetCount.push(element.nb_visit_tablet);
                }
            });


            draw();
            /**
             * Draw Chart
             *
             * @return void
             */
            function draw() {
                Chart.register(ChartDataLabels);
                Chart.defaults.font.size = 16;

                drawTx();
                drawVisite();
            }

            function drawVisite() {
                const labels = SetName;
                const data = {
                    labels: labels,
                    datasets: [{
                            label: 'Visite PC',
                            data: visitDesktopSetCount,
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.2)'
                            ],
                            borderColor: [
                                'rgba(75, 192, 192)'

                            ],
                            borderWidth: 1
                        },
                        {
                            label: 'Visite Mobile',
                            data: visitMobileSetCount,
                            backgroundColor: [

                                'rgba(153, 102, 255, 0.2)'

                            ],
                            borderColor: [
                                'rgb(153, 102, 255)'
                            ],
                            borderWidth: 1,

                        },
                        {
                            label: 'Visite Tablette',
                            data: visitTabletSetCount,
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.2)'
                            ],
                            borderColor: [
                                'rgb(54, 162, 235)',
                            ],
                            borderWidth: 1,
                        }
                    ]

                };

                const config = {
                    type: 'bar',
                    data,
                    options: {
                        indexAxis: 'y',
                    }
                };
                const VisitChart = document.getElementById("donut_visit").getContext("2d");
                const BarChart = new Chart(VisitChart, config);
            }



            function drawTx() {
                const labels = SetName;
                const data = {
                    labels: labels,
                    datasets: [{
                            label: 'Nb conversion',
                            data: conversionSetCount,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.2)'
                            ],
                            borderColor: [
                                'rgb(255, 99, 132)',
                            ],
                            borderWidth: 1
                        },
                        {
                            label: 'Taux conversion',
                            data: txConversionSetCount,
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.2)',
                            ],
                            borderColor: [
                                'rgb(75, 192, 192)',
                            ],
                            borderWidth: 1,
                        }
                    ]

                };

                const config = {
                    type: 'bar',
                    data,
                    options: {
                        indexAxis: 'y',

                    }
                };
                const conversionChart = document.getElementById("donut_tx_conversion").getContext("2d");
                const pieChart = new Chart(conversionChart, config);
            }
        </script>
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