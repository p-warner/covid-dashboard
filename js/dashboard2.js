(function ($, Drupal, once) {
  Drupal.behaviors.CovidDashboardChart = {
    attach: function (context, settings) {
      once('CovidDashboardChartRender', 'body', context).forEach(function (element) {
        // Apply the myCustomBehaviour effect to the elements only once.
        console.log('wtf');
      });
    }
  };
})(jQuery, Drupal, once);
