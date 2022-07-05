<?php
header("Cache-Control: no-cache, must-revalidate");
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
        let visitSetCount = [];
        let conversionSetCount = [];


        dbData.forEach(element => {
            SetName.push(element.uri);
            txConversionSetCount.push(element.tx_conversion);
            visitSetCount.push(element.nb_visit);
            conversionSetCount.push(element.nb_conversion);
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

            const donutEl = document.getElementById("donut_tx_conversion").getContext("2d");

            const pieChart = new Chart(donutEl, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: txConversionSetCount,
                        backgroundColor: ['rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 206, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(153, 102, 255)',
                        ]
                    }],
                    labels: SetName
                },
                options: {
                    plugins: {
                        datalabels: {
                            formatter: (value) => {
                                return value + "%";
                            }
                        },
                    }
                }
            })

            const donutConversion = document.getElementById("donut_conversion").getContext("2d");

            const pieChart2 = new Chart(donutConversion, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: conversionSetCount,
                        backgroundColor: ['rgb(225, 131, 148)',
                            'rgb(177, 214, 239)',
                            'rgb(167, 180, 189)',
                            'rgb(182, 230, 163)',
                            'rgb(79, 112, 208)',
                        ]
                    }],
                    labels: SetName
                },
            })

            const donutVisit = document.getElementById("donut_visit").getContext("2d");

            const pieChart3 = new Chart(donutVisit, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: visitSetCount,
                        backgroundColor: ['rgb(75, 192, 192)',
                            'rgb(153, 102, 255)',
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 206, 86)',
                        ]
                    }],
                    labels: SetName
                },
            })
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