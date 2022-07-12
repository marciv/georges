<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
if (isset($_GET['debug']) && $_GET['debug'] == true) {
    if (isset($_GET['http_referer'])) {
?>
        <code>
            <pre>
                <?php print_r($george->get_data_debug($_GET['http_referer'])); ?>
            </pre>
        </code>
    <?php
    } else {
    ?>
        <code>
            <pre>
                <?php print_r($george->get_data_debug()); ?>
            </pre>
        </code>
<?php
    }
}
?>
<script>
    $(document).ready(function() {
        if (jQuery) {
            // jQuery is loaded 
            let formSended = false;
            try {
                $('#pixel_crm_confirmation').on('load', function() {
                    addConversion();
                });
            } catch (error) {
                console.log(error);
            }

            try {
                $('form').on("submit", function(e) {
                    addConversion();
                })
            } catch (error) {
                console.log(error);
            }

            function addConversion() {
                if (!formSended) {
                    formSended = true;
                    let http_referer = "<?= isset($_GET['http_referer']) ? $_GET['http_referer'] : ""; ?>";
                    let status = <?= json_encode($george->status); ?>;
                    console.log(http_referer);
                    console.log(status);

                    if (http_referer == null || http_referer == undefined || http_referer == "") {
                        http_referer = null;
                    }

                    console.log(http_referer);


                    if (status != "1" || status != 1) {
                        $.post("../../../library/George/switchGeorge.php?action=addConversion", {
                            conversion_path: http_referer,
                            path: window.location.pathname
                        });
                    }
                    return;
                }
            }
        }
    });
</script>