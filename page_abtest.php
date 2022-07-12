<?php
header("Cache-Control: no-cache, must-revalidate");
require "../../config.php";
use library\George as george;
$dbName = $_GET['dbName'];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="css/rocket.png">

    <title>ABTest <?php echo $dbName; ?></title>
    <style>
        <?php @include_once "css/bootstrap.min.css"; ?>
    </style>

    <link href="./css/style_abtest.css" rel="stylesheet">
</head>

<body>


    <div class="chart" id="myChart"></div>


    <div class="container">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $dbName; ?></li>
            </ol>
        </nav>
    </div>
    <?php
    if (!empty($dbName)) {
        $george = new George($dbName);
    ?>
        <div class="container">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.8.0/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.0.0/chartjs-plugin-datalabels.min.js"></script>
    <script>
        let dbData = <?= json_encode($george->get_data_by_abtest()) ?>;

        let SetName = [];
        let txConversionSetCount = [];
        let conversionSetCount = [];
        let visitDesktopSetCount = [];
        let visitMobileSetCount = [];
        let visitTabletSetCount = [];




        dbData.forEach(element => {
            SetName.push(element.uri);
            txConversionSetCount.push(element.tx_conversion);
            conversionSetCount.push(element.nb_conversion);
            visitDesktopSetCount.push(element.nb_visit_desktop);
            visitMobileSetCount.push(element.nb_visit_mobile);
            visitTabletSetCount.push(element.nb_visit_tablet);
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
                        label: 'Visite Mobile',
                        data: visitMobileSetCount,
                        backgroundColor: [

                            'rgba(153, 102, 255, 0.2)'

                        ],
                        borderColor: [
                            'rgb(153, 102, 255)'
                        ],
                        borderWidth: 1,

                    }, {
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
                    indexAxis: 'x',
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
                    indexAxis: 'x',

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