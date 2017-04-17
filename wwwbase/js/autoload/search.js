$(function() {

  function init() {

    $('.notRecommendedShowHide').click(function() {
      $('span.notRecommended').toggleClass("toggleOff");
      $(this).text($(this).text() == '(arată)' ? '(ascunde)' : '(arată)');
    });

    moveBanner();
  }

  function moveBanner() {
    var h = $(window).height();
    var pos = null;

    // Move the banner a few definitions down, but
    // * not lower than 2/3 of the window height;
    // * only if followed by more definitions.
    $('#resultsTab .defWrapper:not(:first)').slice(0,3).each(function() {
      var top = $(this).offset().top;
      if (top + 100 < 2 * h / 3) {
        pos = $(this);
      }
    });

    if (pos) {
      $('#definitionBanner').children().insertBefore(pos);
    } else {
      $('#definitionBanner').show();
    }
  }

  init();

});
