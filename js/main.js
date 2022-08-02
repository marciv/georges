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


    //Refresh avec alert
    if (location.search != "") {
        setTimeout(function () {
            window.location.href = "index.php";
        }, 2000);
    }

    function checkInput(e) {
        let checked = true;
        let url_variations = $('input[name="url_variations[]"]');

        url_variations.each(function () {
            if ($(this).val() == "") {
                checked = false;
            }

            if (first($(this).val()) != "/" || last($(this).val()) != "/") {
                alert("URL Variation must start and end with /");
                $(this).css("background-color", "rgba(253, 111, 111, 0.3)");
                checked = false;
            }

        });


        if (first($('#url_conversion').val()) != "/" || last($('#url_conversion').val()) != "/") {
            alert("URL Conversion must start and end with /");
            checked = false;
            $('#url_conversion').css("background-color", "rgba(253, 111, 111, 0.3)");
        }

        if ($('#url_conversion').val() == "" || $('#taux_decouvert').val() == "") {
            checked = false;
            $('#url_conversion').css("background-color", "rgba(253, 111, 111, 0.3)");
            $('#taux_decouvert').css("background-color", "rgba(253, 111, 111, 0.3)");
        }
        if ($('#taux_decouvert').val() > 0.25) {
            alert("Discovery Rate must be less than 0.25");
            checked = false;
            $('#taux_decouvert').css("background-color", "rgba(253, 111, 111, 0.3)");
        }

        if (checked) {
            return true;
        } else {
            event.preventDefault();
            setTimeout(function () {
                $('.message-contenu').text("");
            }, 4000);
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

    $("#addInput").click(function () {
        $('.anotherInput').append(`
        <div class="form-group">
            <label for="urlPrincipal">Variation url</label>
            <input type="text" class="form-control" name="url_variations[]" id="url_variations[]" placeholder="/test/lan/XX/">
            <small id="urlPrincipal" class="form-text text-muted">Variation url must start and end with "/".</small>
        </div>`);
    });
});