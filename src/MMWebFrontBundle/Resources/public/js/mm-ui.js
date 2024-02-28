/*
 * Copyright (c) 2017. Mealmatch GmbH
 * Author: Wizard <wizard@mealmatch.de> 
 */

/**
 * Mealmatch UI
 */
const mmUI = (function () {

    // mealTypes
    const mealTypes = ['ProMeal', 'HomeMeal'];

    return {

        showTWBSDialog: function (title, message) {
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_DANGER,
                title: title,
                message: message
            });
        },

        bindShowHideMoreElemten: function(shortenText) {
            $(document).on('click', '.post_wrap > p.message', function() {

                var that = $(this);
                var thisParent = that.closest('.post_wrap');

                if (that.hasClass('more-times')) {

                    if (shortenText) {
                        thisParent.find('.ellipseText').addClass('invisible');
                    }

                    thisParent.find('.moreShown').slideDown();

                    that.toggleClass('more-times', 'less-times').html(showLess);

                    if (!shortenText) {
                        $('html, body').animate({
                            scrollTop: $(".scrollTo").offset().top - 65
                        }, 1000);
                    }


                } else {

                    if (shortenText) {
                        thisParent.find('.ellipseText').removeClass('invisible');
                    }

                    $('html, body').animate({
                        scrollTop: $(".post_wrap").offset().top - 65
                    }, 1000);

                    thisParent.find('.moreShown').slideUp();

                    that.toggleClass('more-times', 'less-times').html(showMore);

                }
            });
        },
        showHideMore: function() {
            let $ShowHideMore = $('.post_wrap');

            var showLess = 'Weniger anzeigen <i class="fa fa-caret-up mm-green"></i>';
            var showMore = 'Mehr anzeigen <i class="fa fa-caret-down mm-green"></i>';
            var shortenText = false;

            // Length of chars
            var chars = 200;

            $ShowHideMore.each(function() {
                // Count child-elements
                var times = $(this).children('.child');
                // Save first-childs content
                var content = $ShowHideMore.children(':nth-of-type(1)').html();
                // more than 1 child
                if (times.length > 1) {
                    $ShowHideMore.children(':nth-of-type(2)').addClass('scrollTo');

                    // first child-content < chars
                    if(content.length < chars) {
                        // hide all childs after the second child
                        $ShowHideMore.children(':nth-of-type(n+3)').addClass('moreShown').hide();
                    }
                    // first child-content > chars
                    else{
                        // hide content after the first child
                        $ShowHideMore.children(':nth-of-type(n+2)').addClass('moreShown').hide();
                    }

                    if (times.length > 1){
                        $(this).find('p.message').addClass('more-times').html(showMore);
                    }

                    // only 1 child: shorten text if necessary
                } else{
                    // shorten content
                    var c = content.substr(0, chars);
                    // content - shorten
                    var h = content.substr(chars, content.length - chars - 4);
                    var html = c + '<span class="ellipseText">...</span><span class="moreShown" style="display:none">' + h + '</span></p>';
                    shortenText = true;

                    $ShowHideMore.children(':nth-of-type(1)').html(html);
                    $(this).find('p.message').addClass('more-times').html(showMore);
                }
            });
            return shortenText;
        }
    }
})();

$('#mm-addEventStart-datetimepicker').datetimepicker({
        format: "D.MM.Y H:mm",
        locale: 'de',
        stepping: 15,
        collapse: true,
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    }
);
$('#mm-addEventEnd-datetimepicker').datetimepicker({
        format: "D.MM.Y H:mm",
        locale: 'de',
        stepping: 15,
        collapse: true,
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    }
);

/** mm date time picker */
$('#mm-search-datetimepicker').datetimepicker({
        format: "D.MM.Y H:mm",
        locale: 'de',
        stepping: 15,
        collapse: true,
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    }
);
$('#mm-search-datetimepicker-mod').datetimepicker({
        format: "D.MM.Y H:mm",
        locale: 'de',
        stepping: 5,
        collapse: true,
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    }
);
$('#mm-search-datetimepicker-max').datetimepicker({
        format: "D.MM.Y H:mm",
        locale: 'de',
        stepping: 5,
        collapse: true,
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    }
);

$('ul.mm-navbar-xs-user-dropdown').on('click', function(event){
    event.stopPropagation();
});

/** FlashBag "Alerts" */
if (false === $('#mmTypeOfAlert').hasClass('alert-danger')) {
window.setTimeout(function() {
    $(".mm-alert").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove();
    });
}, 5000)}

/** PopOver */
$(function () {
    $('[data-toggle="popover"]').popover()
});
$('#mm-becomehost-popover').popover({
    trigger: "hover focus",
    delay: {show: 100, hide: 1400},
    placement: 'bottom',
    html: true,
    caret: true,
    content: $("#mm-becomehost-popover-html").html(),
    container: 'body'
});


var $input = $('#not-used-yet');
$input.typeahead(
    {
        source: [
            {id: "someId1", name: "Display name 1"},
            {id: "someId2", name: "Display name 2"}
        ],
        autoSelect: true
    }
);
$input.change(function() {
    var current = $input.typeahead("getActive");
    if (current) {
        // Some item from your model is active!
        if (current.name == $input.val()) {
            // This means the exact match is found. Use toLowerCase() if you want case insensitive match.
        } else {
            // This means it is only a partial match, you can either add a new item
            // or take the active if you don't want new items
        }
    } else {
        // Nothing is active so it is a new value (or maybe empty value)
    }
});
