var glob_chart = undefined;
jQuery.fn.reverse = [].reverse;

$(document).ready(function(){
  var filter = ['data','data'];

  //LISTEN
  var callbacks = $.Callbacks();
  callbacks.add( updateTable );
  callbacks.add( updateChart );

  $('.filter').on('change', function(){
    $('.filter').each(function(i){
      filter[i] = $(this).find(":selected").attr('value');
    });
    callbacks.fire(filter);
  });
  
  //INIT
  $('div.chart').html('<canvas id="rona_chart" width="600" height="400"></canvas>');
  callbacks.fire(filter);
});

/**
 * Update the table
 * 
 * @param {Array} filter 
 */
function updateTable(filter){
  $('.covid-dashboard-table tr').each(function(){
    $('td', this).each(function(){
      
      if($(this).attr('data-date')){
        return;
      }

      filterCell(filter, this);
    });
  });
}

/**
 * Add the data attributes for a cell based on a filter.
 * 
 * @param {Array} filter 
 * @param {Object} cell
 */
function filterCell(filter, cell){
  var total = 0;

  for(var i = 0; i < cell.attributes.length; i++){
    const attribute = cell.attributes[i];

    if(attribute.name.indexOf(filter[0]) > -1 && attribute.name.indexOf(filter[1]) > -1){
      total += parseInt(attribute.nodeValue);
    }   
  }

  $(cell).text(total);
}

function updateChart(){
  var days = [];
  $('[data-date]').reverse().each(function(){
    days.push($(this).text());
  });


  var tested = [];
  $('.tested').reverse().each(function(index){
    let prev = index === 0 ? 0 : tested[index-1];
    tested.push(prev + parseInt($(this).text()));
  }); 
  
  var positive = [];
  $('.positive').reverse().each(function(index){
    let prev = index === 0 ? 0 : positive[index-1];
    positive.push(prev + parseInt($(this).text()));
  });

  var recovered = [];
  $('.recovered').reverse().each(function(index){
    let prev = index === 0 ? 0 : recovered[index-1];
    recovered.push(prev + parseInt($(this).text()));
  });
  
  /* json available... too many requests?
  $.getJSON('/covid-19/dashboard/data', function(data){});
  */
  

  if(glob_chart){
    glob_chart.data.datasets[0].data = tested;
    glob_chart.data.datasets[1].data = positive;
    glob_chart.data.datasets[2].data = recovered;
    
    glob_chart.update();

    return;
  }


  //Set global chart
  var ctx = document.getElementById('rona_chart').getContext('2d');
  glob_chart = new Chart(ctx, {
      type: 'line',
      data: {
          labels: days,
          datasets: [
          {
            label: 'Tested',
            data: tested,
            backgroundColor: 'rgba(92, 92, 92, 1)',
            borderColor: 'rgba(92, 92, 92, 1)',
            borderWidth: 2,
            fill: false,
        },{
              label: 'Positive',
              data: positive,
              backgroundColor: 'rgba(216, 59, 24, 1)',
              borderColor: 'rgba(216, 59, 24, 1)',
              borderWidth: 2,
              fill: false,
          },{
            label: 'Recovered',
            data: recovered,
            backgroundColor: 'rgba(40,167,69, 1)',
            borderColor: 'rgba(40,167,69, 1)',
            borderWidth: 2,
            fill: false,
        },]
      },
      options: {
          responsive: true,
          maintainAspectRatio: false,
          legend: {
            position: 'bottom',
          },
          scales: {
            xAxes: [{
              display: true,
              scaleLabel: {
                display: true,
                labelString: 'Date'
              }
            }],
            yAxes: [{
              display: true,
              ticks: {
                suggestedMin: 0,
                suggestedMax: 10,
                beginAtZero: true,
                maxTicksLimit: 14,
                stepSize: 1,
              },
              scaleLabel: {
                display: true,
                labelString: 'Count'
              }
            }]
          }
      }
  });

}