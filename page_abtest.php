<?php
require  "class.george.php";
$dbName = $_GET['dbName'];
if (isset($_GET['path'])) {
    $path = $_GET['path'];
} else {
    $path = "";
}

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
            echo $george->draw_abtest($path);
            ?>


        </div>
        <div class="container card mx-auto">
            <canvas id="donut" width="100" height="100"></canvas>
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
        let dbName = <?= json_encode($dbName); ?>;
        let dbData = <?= json_encode($george->get_data_custom_for_conversion($dbName)) ?>;

        let Variation = [];

        dbData[0].listVariation.forEach(element => {
            Variation.push({
                name: element.variation,
                count: 0
            });
        });

        Variation.push({
            name: dbData[0].uri,
            count: dbData[0].nb_conversion
        });

        getData();


        /**
         * Get data json
         *
         * @return void
         */
        async function getData() {
            let data = await fetch("database/" + dbName + "/data_set/jsonDataConversion.json");
            let dataJson = await data.json();
            // console.log(dataJson);
            dataJson.forEach(point => {
                if (point.http_referer != "" && point.http_referer != null) { //Variation
                    Variation.forEach(variation => {
                        if (point.path.includes(variation.name)) {
                            variation.count++;
                        }
                    });
                } else {

                }
            });

            Variation.forEach(variation => {
                console.log(variation.name + " : " + variation.count);
            });

            console.log(Variation);
            draw();
        }
        /**
         * Draw Chart
         *
         * @return void
         */
        function draw() {
            let dataSetName = [];
            let dataSetCount = [];


            Variation.forEach(element => {
                dataSetName.push(element.name);
                dataSetCount.push(element.count);
            })

            Chart.register(ChartDataLabels);

            const donutEl = document.getElementById("donut").getContext("2d");
            const data = [4, 9, 5, 2];

            const pieChart = new Chart(donutEl, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: dataSetCount,
                        backgroundColor: ['rgb(255, 99, 132)', 'rgb(255, 159, 64)', 'rgb(255, 205, 86)', 'rgb(75, 192, 192)', 'rgb(54, 162, 235)', ],
                    }],
                    labels: dataSetName
                },
                options: {
                    plugins: {
                        datalabels: {
                            formatter: (value) => {
                                return value;
                            }
                        }
                    }
                }
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