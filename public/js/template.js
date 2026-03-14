(function($) {
  'use strict';
  $(function() {
    var body = $('body');
    var sidebar = $('.sidebar');

    //Add active class to nav-link based on url dynamically
    function addActiveClass(element) {
      if (current === "") {
        if (element.attr('href').indexOf("index.html") !== -1) {
          element.parents('.nav-item').last().addClass('active');
          if (element.parents('.sub-menu').length) {
            element.closest('.collapse').addClass('show');
            element.addClass('active');
          }
        }
      } else {
        if (element.attr('href').indexOf(current) !== -1) {
          element.parents('.nav-item').last().addClass('active');
          if (element.parents('.sub-menu').length) {
            element.closest('.collapse').addClass('show');
            element.addClass('active');
          }
          if (element.parents('.submenu-item').length) {
            element.addClass('active');
          }
        }
      }
    }

    var current = location.pathname.split("/").slice(-1)[0].replace(/^\/|\/$/g, '');
    $('.nav li a', sidebar).each(function() {
      addActiveClass($(this));
    });

    // Removed: the "close other submenus on open" handler was immediately closing
    // the Admin dropdown due to Bootstrap event timing. Since there is only one
    // collapsible sidebar menu, closing others is unnecessary.

    // Minimize sidebar toggle
    $('[data-toggle="minimize"]').on("click", function() {
      body.toggleClass('sidebar-icon-only');
    });

    // Checkbox and radios
    $(".form-check label,.form-radio label").append('<i class="input-helper"></i>');
  });

  // Focus input when clicking on search icon
  $('#navbar-search-icon').click(function() {
    $("#navbar-search-input").focus();
  });

})(jQuery);
