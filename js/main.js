$(window).on('load', function () {
    $(".next").on("click", function () {
        section = $('section:visible');
        sectionId = section.attr('id');
        targetId = section.attr('target');
        $("#" + sectionId).fadeToggle(400, "swing",
            function () {
                $("#" + targetId).fadeToggle("slow");
            });
    });

    $(".toSection").on("click", function () {
        section = $('section:visible');
        sectionId = section.attr('id');
        targetId = $(this).attr('target');
        $("#" + sectionId).fadeToggle(400, "swing",
            function () {
                $("#" + targetId).fadeToggle("slow");
            });
    });

    $('.addVariation').on('click', function () {
        $('.more_variation').fadeIn();
        $('.addVariation').fadeOut();


    })


    //Refresh avec alert
    if (location.search != "") {
        setTimeout(function () {
            window.location.href = "index.php";
        }, 2000);
    }
    $("#formData").on("submit", function (e) {
        let checked = true;
        let inputs = $(e).find('input');

        //Second Variation 
        if ($('#variation_two').val() != "" && (first($('#variation_two').val()) != "/" || last($('#variation_two').val()) != "/")) {
            alert("URL Variation 2 must start and end with /");
            checked = false;
            $('#variation_two').css("background-color", "rgba(255, 1, 1, 0.049)");
        }

        //First Variation
        if (first($('#variation_one').val()) != "/" || last($('#variation_one').val()) != "/") {
            alert("URL Variation 1 must start and end with /");
            checked = false;
            $('#variation_one').css("background-color", "rgba(255, 1, 1, 0.049)");
        }
        if ($('#variation_one').val() == "") {
            checked = false;
            $('#variation_one').css("background-color", "rgba(255, 1, 1, 0.049)");
        }

        //Main
        if (first($('#url_conversion').val()) != "/" || last($('#url_conversion').val()) != "/") {
            alert("Main URL must start and end with /");
            checked = false;
            $('#url_conversion').css("background-color", "rgba(255, 1, 1, 0.049)");
        }

        if ($('#url_conversion').val() == "") {
            checked = false;
            $('#url_conversion').css("background-color", "rgba(255, 1, 1, 0.049)");
        }
        if ($('#taux_decouvert').val() > 0.25 || $('#taux_decouvert').val() == "") {
            alert("Discovery Rate must be less than 0.25 or not empty");
            checked = false;
            $('#taux_decouvert').css("background-color", "rgba(255, 1, 1, 0.049)");
        }

        if (checked) {
            return true;
        } else {
            e.preventDefault();
            setTimeout(function () {
                $('.message-contenu').text("");
            }, 4000);
            return false;
        }
    });

    function first(str) {
        first_part = str.substring(0, 1);
        return first_part;
    }

    function last(str) {
        last_part = str.substring(str.length - 1);
        return last_part;
    }
});