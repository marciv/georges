<?php include(ABSPATH . LIB . "/George/scriptGeorge.php");
?>
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