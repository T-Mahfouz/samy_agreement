/* Loading
===============================*/
$(window).on("load", function () {
    "use strict";
    $(".lds-spinner").fadeOut(function () {
        $(this).parent().fadeOut();
        $("body").css({ "overflow-y": "visible" });
    });
});


/* Scripts
=================================*/
$(document).ready(function () {
    $(document).mousemove(function (e) {
        $(".cursor").eq(0).css({
            left: e.clientX,
            top: e.clientY,
        });
    });

    $("a, button").mouseenter(function () {
        $(".cursor").css({
            "background-color": "rgba(254, 209, 106, 0.3)",
            width: "40px",
            height: "40px",
        });
    });

    $("a, button").mouseleave(function () {
        $(".cursor").css({
            "background-color": "rgba(254, 209, 106, .9)",
            width: "20px",
            height: "20px",
        });
    });
    $("#menu_bar").click(function () {
        $(".header__menu").toggleClass("header__menu_show");
    });

    $('a[data-target="#contact"]').on("click", function () {
        $(".header__menu").removeClass("header__menu_show");
    });

    $(".tender_book_modal__form").on("submit", function (e) {
        e.preventDefault();
    });

    // Sort dropdown: update button label when selecting an option
    $(document).on("click", ".sort_dropdown__item", function () {
        var label = $(this).data("sort-label");
        if (label) {
            $(".sort_dropdown__label").text(label);
        }
    });

    $(document).on("click", ".description_expand__toggle", function () {
        var $expand = $(this).closest(".description_expand");
        var isExpanded = $expand.toggleClass("is-expanded").hasClass("is-expanded");

        $(this).attr("aria-expanded", isExpanded);
        $(this).attr("aria-label", isExpanded ? "عرض أقل" : "عرض المزيد");
    });

    $(".publish-date-tooltip").each(function () {
        $(this).tooltip({
            container: $(this),
            placement: "top",
            trigger: "hover focus",
            template:
                '<div class="tooltip publish-date-tooltip__popup" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
        });
    });

    function updateOfferAwardVisibility($row) {
        var isNoMatch = $row
            .find('input[name^="offer_exam_"][value="no_match"]')
            .is(":checked");
        var $awardChoice = $row.find(".offer_award__choice");

        if (isNoMatch) {
            $awardChoice.addClass("d-none");
            $row.find('input[name="offer_award"]').prop("checked", false);
        } else {
            $awardChoice.removeClass("d-none");
        }
    }

    $("#client_offers_form tbody tr").each(function () {
        updateOfferAwardVisibility($(this));
    });

    $("#client_offers_form").on(
        "change",
        'input[name^="offer_exam_"]',
        function () {
            updateOfferAwardVisibility($(this).closest("tr"));
        }
    );

});