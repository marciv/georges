<?php
require  "class.george.php";
if (isset($_GET['db']) && !empty($_GET['db'])) {
    try {
        $george = new george();
        $george->deleteData("database/" . $_GET['db']); //Suppression de l'ABTest
?>
        <script>
            window.location.href = "index.php"
        </script>
<?php
    } catch (\Throwable $th) {
        echo $th;
    }
}
