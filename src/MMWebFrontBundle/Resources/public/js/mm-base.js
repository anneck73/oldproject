/*
 * Copyright (c) 2016. Mealmatch GmbH. All rights reserved!
 */

/**
 * Created by andre on 06.12.16.
 */
//
// /** MM Card Flip */
// function rotateCard(btn) {
//     var $card = $(btn).closest('.card-container');
//
//     if ($card.hasClass('hover')) {
//         $card.removeClass('hover');
//     } else {
//         $card.addClass('hover');
//     }
// }

//
// /** Google Analytics ... */
// (function (i, s, o, g, r, a, m) {
//     i['GoogleAnalyticsObject'] = r;
//     i[r] = i[r] || function () {
//             (i[r].q = i[r].q || []).push(arguments)
//         }, i[r].l = 1 * new Date();
//     a = s.createElement(o), m = s.getElementsByTagName(o)[0];
//     a.async = 1;
//     a.src = g;
//     m.parentNode.insertBefore(a, m)
// })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');



// Position einer Klasse / einer ID ermitteln (soa)
function toggleHomePro(cl){

    // Position Search Button -> Position Infotext
    var position = $("#mm-search-btn").position();
    $(cl).css({top: position.top - 7, left: position.left, position:'absolute'});

    // Toggle Classes
    $(cl).toggleClass("hidden");
    $("#mm-search-btn").toggleClass("invisible");
    if (cl == "#mm-option-home") 
        $("#mm-option-pro").toggleClass("invisible");
    else
        $("#mm-option-home").toggleClass("invisible");
}


// Public Profile: Map und Image-Slide nur bei entsprechendem Tab sichtbar schalten (soa)
var showPublicProfileFooter = {};
showPublicProfileFooter.lastSwitch = false;

showPublicProfileFooter.switch = function (target) {

    if (target != "none") {
        var targetId = "#" + target;
        $(targetId).toggleClass("hidden");
        /*if (!$(".mm-footer-lg").hasClass("no-margin")) {
            $(".mm-footer-lg").addClass("no-margin");
        }*/
    } else {
        targetId = target;
        //$(".mm-footer-lg").removeClass("no-margin");
    }

    if (showPublicProfileFooter.lastSwitch) {
        $(showPublicProfileFooter.lastSwitch).toggleClass("hidden");
    }

    showPublicProfileFooter.lastSwitch = targetId;

};




$(document).ready(function(){


    // SearchToggle front page
    // Home / Pro Info Startseite
    $("#mm-toggle-search-home").hover(function () {

        toggleHomePro("#mm-option-home");
        
    });

    $("#mm-toggle-search-pro").hover(function () {
        toggleHomePro("#mm-option-pro");
    });


    // Toggle Public Profile
    $("[data-trigger=showPublicProfileFooter]").on("click", function () {

        var $this = $(this);
        showPublicProfileFooter.switch($this.data("target"));

    });


    // ToDo: Unschoene Loesung, sollten wir generell anders angehen
    /*var searchResults = document.getElementById('search-results');
    if (searchResults) {
        if (!$(".mm-footer-lg").hasClass("no-margin")) {
            $(".mm-footer-lg").addClass("no-margin");
        }
    }*/

    

});