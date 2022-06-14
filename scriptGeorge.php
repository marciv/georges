<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
if (isset($_GET['debug']) && $_GET['debug'] == true) {
    var_dump($george->get_data_custom());
}
?>
<script>
    window.addEventListener('load', (event) => {
        let submitBtn = document.getElementById('final-submit');
        submitBtn.addEventListener('click', function(evt) {
            console.log('FORM SENDED');

            var formData = new FormData();
            formData.append("path", window.location.pathname);
            var http_referer = "<?= isset($_GET['http_referer']) ? $_GET['http_referer'] : ""; ?>";
            var status = <?= json_encode($george->status); ?>

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

            if (status != "1" || status != 1) {
                xmlHttp.send(formData);
            }
        });
    });
</script>