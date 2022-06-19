/**
 * Global data
 */
var d = {
  'tuition': {
    'pa': 587,
    'os': 839,
  },
  'fee': {
    'cap': 49,  //TODO: legacy. Unused.
    'tech': 17, //TODO: legacy. Unused.
    'hs': 6,    //TODO: legacy. Unused.
    'act': 11,  //TODO: legacy. Unused.
    'enrl': 140,
    'lab': 45,
    'int': 0,
  },
  'credits': [0,0,0],
  'books': [0,0,0],
  'tools': [0,0,0],
  'lab': [0,0,0],
  'housing': [0,0,0],
  'summerHousing': 0,//TODO: unused?
  'meal': [0,0,0],
  'totalLiving': 0,//TODO: unused?
  'selected_pgm': '',//E.g. Software Development (online) B.S.
  'major': '',//Major Code
  'online': undefined,
  'savedMealId': '',//TODO: unused?
  'multi': 587, //used to default to 587 this.tution.pa,
  'st_1': 0,//semester total
  'st_2': 0,//semester total
  'st_s': 0,//semester total
  'major_fee': 0,//major fee
  'aid': {
    'total': 0,
    'fall':{},
    'spring':{},
  },
  'difference': 0,
  'international': false,
  'verbose': false,
};

jQuery(document).ready( function($){
  /**
   * SLICK
   */
  $.getScript('/themes/penn_college/js/slick.min.js?v=1.1.1', function(){
    if(d.verbose){console.log('slick loaded.');}
    $('.slick-2').slick({
      slidesToScroll: 1,
      variableWidth: false,
      infinite: false,
      swipeToSlide: true,
      slidesToShow: 3,
      responsive: [
        {
          breakpoint: 1200,
          settings: {
            slidesToShow: 3,
          }
        },
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 2,
          }
        },
        {
          breakpoint: 800,
          settings: {
            slidesToShow: 1,
          }
        },
      ],
    }).on('init', function(slick){
      AOS.refresh();
    }).on('afterChange', function(event, slick, currentSlide){
      AOS.refresh();
    });	

    //Set timeout to always resize slick.
    //setInterval(function(){if(d.verbose)console.log('resize');$('.slick-2').slick('resize');},500);

  });

  //INIT
  clone_major_select();//Duplicate major select list and put into section_major

  //HIDE ALL
  $('[id*="section_"]:not(#section_intro):not(#section_major):not(#section_aid)').each(function(){
    hide_section($(this));
  });

  //Any control with data-form-update should interact with the form.
  $('[data-form-update]').each(function(){
    $(this).on('click', function(){
      if(d.verbose){console.log('[data-form-update] click detected.');}
      //Update form
      const args = $(this).attr('data-form-update').split(';');
      var selector = '';
      switch(args[0]){
        case 'radio':
          selector = 'input[name="'+args[1]+'"][value="'+args[2]+'"]';
          //$(selector).attr('checked','checked');//TODO: Doesn't trigger a change.
          $(selector).click();
          break;
        case 'select':
          selector = 'select[name="'+args[1]+'"] option[value="'+args[2]+'"]';
          //$(selector).attr('selected','selected');//TODO: Doesn't trigger a change.
          $(selector).click();
          break;
      }

      $('.btn[data-form-update*="'+args[1]+'"]').removeClass('active');
      $(this).addClass('active');
    });
  });

  /**
   * PRE LOAD SLATE DATA
   */
  //aid shouldn't change after page load?
  if($('#total-aid-slate').length){
    d.aid.total = parseInt($('#total-aid-slate').text());
  }

  //If #major, click the major,
  if($('#major').length){
    let m = $('#major').text();

    $('#gui_select_major select[name="gui_major_select"] option[value="'+m+'"]').prop('selected','selected');
    $('#gui_select_major select[name="gui_major_select"]').trigger('change');
  }

  //If slate #state, click yes
  if($('#state').length){
    let s = $('#state').text();
    if(s === 'PA'){
      $('input[name="residency_radio"][value="587"]').prop('checked','checked');
      $('#gui_select_recidency .btn:eq(0)').addClass('active');
      $('#gui_select_recidency .btn:eq(1)').removeClass('active');
    }else{
      $('input[name="residency_radio"][value="839"]').prop('checked','checked');
      $('#gui_select_recidency .btn:eq(0)').removeClass('active');
      $('#gui_select_recidency .btn:eq(1)').addClass('active');
    }

    $('input[name="residency_radio"]').trigger('change');
  }
  /**
   * END LOAD SLATE DATA
   */
	
});

/**
 * Creates all the form elements necessary to interact with the hidden Drupal form on the page.
 */
function clone_major_select(){
  $select = $('[name=major_select]').clone().attr('name','gui_major_select');
  $select.on('change', function(event){
    $('[name=major_select]').val($(this).val()).trigger('change');
  });
  $('#gui_select_major').html($select);
}

/**
 * Formats an int into money.
 * 
 * @param {*} x 
 */
function fmt(x) {
  return Math.round(x).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * 
 */
function spin(){
  $('.count').each(function () { 
    $(this).prop('counter',0).animate({	counter: $(this).text().replace(',','')	}, { duration: 500, easing: 'swing', step: function (c) {$(this).text(Math.ceil(c).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")); } });
  });
}

/**
 * Loads the global data object.
 */
function update_global_data(args){
  if(d.verbose){console.log(`---update_global_data()`);}
  
  //Load major specific variables.
  d.credits[0] = args.c1;
  d.credits[1] = args.c2;
  d.credits[2] = args.cs;
  d.books[0] = args.b1;
  d.books[1] = args.b2;
  d.books[2] = args.bs;
  d.tools[0] = args.tu;
  d.tools[1] = 0;
  d.tools[2] = 0;
  d.lab[0] = args.l1;
  d.lab[1] = args.l2;
  d.lab[2] = args.ls;
  d.major_fee = args.mf;

  //Get all form values.
  //Major name. eq(0) because this is cloned.
  d.selected_pgm = $("[data-major-select] option:selected").eq(0).text();

  //Major Code
  if (d.selected_pgm == "UD"){
    d.major_code = 'GS';
  } else {
    d.major_code = $("[data-major-select] option:selected").eq(0).val();
  }

  //PA Residency- if online it's in state tution else it's the value of the checked radio.
  if(d.selected_pgm.indexOf("online") > 0){
    //Online major. Always assume in-state tuition rate
    d.online = true;
    d.multi = d.tuition.pa;
  }else{
    //Not online major. Check form for residency selection 
    d.online = false;
    $radio = $("input[name=residency_radio]:checked");
    if(parseInt($radio.val()) === 587){//TODO: change 587 to yes or no?
      d.multi = d.tuition.pa;
    }else if(parseInt($radio.val()) === 839){
      d.multi = d.tuition.os;
    }
  }
  

  //International- Adds 500 fee.
  $radio = $("input[name=international_radio]:checked");
  if($radio.length){
    d.fee.int = parseInt($("input[name=international_radio]:checked").val());
     
  }

  //On campus housing selection
  //housing_on_select
  
  //Auto 8 week major else normal
  if(d.major_code == 'AH' || d.major_code == 'CG' || d.major_code == 'FA'){ 
    if(d.verbose){console.log('8-week major found.');}
    let value = $('input[name="living_radio"]:checked').val();
    if(d.verbose){console.log(value);}
    if(value == '0'){
      d.housing = [1375, 1375, 720];
    }else{
      d.housing = [0, 0, 0];
    }
  }else{
    //16-weel majors
    let value = $('input[name="housing_on_select"]:checked').val();
    if(d.verbose){
      console.log('16 week major');
      console.log(value);
    }
    d.housing[0] = (value) ? parseInt(value) : 0;
    d.housing[1] = (value) ? parseInt(value) : 0;
    d.housing[2] = 0;
  }

  //On campus meal plan
  $meal_housing_on_radio = $("input[name=meal_housing_on_radio]:checked");
  if(d.verbose){console.log('$meal_housing_on_radio.length= ' + $meal_housing_on_radio.length)}
  if($meal_housing_on_radio.length){
    let value = $meal_housing_on_radio.val();
    d.meal[0] = (value) ? parseInt(value) : 0; //Fall semester meal cost
    d.meal[1] = (value) ? parseInt(value) : 0; //Spring semester meal cost
    d.meal[2] = 0;//summer semester meal cost.
  }

  //8 week meal plan
  $meal_housing_on_eight_week = $("input[name=meal_housing_on_eight_week_radio]:checked");
  if(d.verbose){console.log('$meal_housing_on_eight_week.length= ' + $meal_housing_on_eight_week.length)}
  if($meal_housing_on_eight_week.length){
    let value = $meal_housing_on_eight_week.val();
    d.meal[0] = (value) ? parseInt(value) : 0; //Fall semester meal cost
    d.meal[1] = (value) ? parseInt(value) : 0; //Spring semester meal cost
    d.meal[2] = 0;//summer semester meal cost.
  }

  //off campus meal plan
  $meal_housing_off_radio = $("input[name=meal_housing_off_radio]:checked");
  if(d.verbose){console.log('$meal_housing_off_radio.length= ' + $meal_housing_off_radio.length)}
  if($meal_housing_off_radio.length){
    let value = $meal_housing_off_radio.val();
    d.meal[0] = (value) ? parseInt(value) : 0; //Fall semester meal cost
    d.meal[1] = (value) ? parseInt(value) : 0; //Spring semester meal cost
    d.meal[2] = 0;//summer semester meal cost.
  }

  //If no meal is selected, clear values
  //TODO: why is meal_housing_off_radio.length === 1 here when online major selected. 
  //something is happening that the form still has a :checked at this point? even though in 
  //gui it shows an unselected radio set. Unsure what to think about this. d.online 
  //d.online fixes this though.
  if(d.online){
    d.meal = [0, 0, 0];
  }

  //Aid
  if(d.aid.total > 0){
    $('#total-total-aid span.count').text(fmt(d.aid.total));
  }  
}

/**
 * UPDATE THE GUI BASED ON GLOBAL DATA OBJECT
 */
function update_gui(){
  if(d.verbose){
    console.log('---update_gui');
    console.log('data-')
    console.log(d);
  }
  
  //Selected Program
  $('.selected-program').text(d.selected_pgm);

  //Enrollment fee of 140 for everyone except international students.
  d.fee.enrl = d.fee.int > 0 ? 0 : 140;
  
  //Momentum incentive. **REMOVED 'Applied Health Studies' per Admissions 11/11/2020
  if(d.selected_pgm.indexOf('B.S.') > -1 && d.selected_pgm.indexOf('Applied Health Studies') == -1){
    $('#momentum-incentive-message').removeClass('d-none');
  }else{
    $('#momentum-incentive-message').addClass('d-none');
  }

  //Remove 'financial aid' mentions for Competency Credentials.
  if(d.major_code.substr(0,1) == '0'){
    $('.h-comp-cred').addClass('d-none');
  }else{
    $('.h-comp-cred').removeClass('d-none');
  }
  
  //Update wide-cards
  const multi = $('input[name="residency_radio"]:checked').val();
  if(multi == '587'){
    $('.resident-in-state-message').removeClass('d-none');
    $('.resident-out-of-state-message').addClass('d-none');
  }else if(multi == '839'){
    $('.resident-out-of-state-message').removeClass('d-none');
    $('.resident-in-state-message').addClass('d-none');
  }

  const inter = $('input[name="international_radio"]:checked').val();
  if(inter == '500'){
    $('.international-message').removeClass('d-none');
    $('.domestic-message').addClass('d-none');
  }else if(inter == '0'){
    $('.international-message').addClass('d-none');
    $('.domestic-message').removeClass('d-none');
  }else{
    $('.international-message').addClass('d-none');
    $('.domestic-message').addClass('d-none');
  }

  //TODO: this is going through DOM and not d.
  const living = $('input[name="living_radio"]:checked').val();
  if(living == '0'){
    $('.housing-on-campus-message').removeClass('d-none');
    $('.housing-off-campus-message').addClass('d-none');
  }else if(living == '1'){
    $('.housing-on-campus-message').addClass('d-none');
    $('.housing-off-campus-message').removeClass('d-none');
  }else{
    $('.housing-on-campus-message').addClass('d-none');
    $('.housing-off-campus-message').addClass('d-none');
  }
  
  //Add and Show detail totals
  $("#total-tuition").text(fmt((d.multi)*(d.credits[0]+d.credits[1]+d.credits[2])+((45)*d.lab[0] +d.fee.enrl + d.major_fee + d.fee.int)+((45)*d.lab[1] + d.major_fee )+((45)*d.lab[2] + d.major_fee )));
  $("#total-books").text(fmt(d.books[0] + d.books[1] + d.books[2]));
  //TODO: Pop-up if $ is > 600 "This accounts for most of the tools you will need throughout your entire program."
  //console.log(d.tools[0] + d.tools[1] + d.tools[2]);
  if(d.tools[0] + d.tools[1] + d.tools[2] > 600){
    $("#total-tools").text(fmt(d.tools[0] + d.tools[1] + d.tools[2]));//Enable
    $(".tools-high").removeClass("d-none").addClass("d-inline");
    $(".tools-low").addClass("d-none").removeClass("d-inline");
  }else if(d.tools[0] + d.tools[1] + d.tools[2] == 0){
    $("#total-tools").text(fmt(d.tools[0] + d.tools[1] + d.tools[2]));
    $(".tools-high").addClass("d-none");
    $(".tools-low").addClass("d-none");
  }else{
    //"Additional tools may be needed in later semesters."
    $("#total-tools").text(fmt(d.tools[0] + d.tools[1] + d.tools[2]));
    $(".tools-high").addClass("d-none").removeClass("d-inline");
    $(".tools-low").removeClass("d-none").addClass("d-inline");
  }
  
  //Semester detail tables
  $(".per_tuition").text(d.multi);

  $(".t-total-1").text(fmt(d.multi * d.credits[0]));
  
  //Fall semester total fees
  $(".book-total-1").text(fmt(d.books[0]));
  $(".tool-total-1").text(fmt(d.tools[0]));

  //Fall Semester total
  d.st_1 = d.books[0] + d.tools[0] + ((45*d.lab[0]) + d.fee.enrl + d.major_fee + d.fee.int)+((d.multi*d.credits[0])+d.housing[0]+d.meal[0]);
  $(".sem-total-1").text(fmt(d.st_1));

  //Fall semester additional fee details table
  $("#first_semester_fees_list").html('');//Clear out fees
  if(d.lab[0] > 0){
    $("#first_semester_fees_list").append(
      '<div class="list-group-item d-flex justify-content-between align-items-center">\
        <small>Lab Fee<br>\
        $'+d.fee.lab+' per hour × '+d.lab[0]+' hours\
        </small>\
        <span>$'+fmt(d.fee.lab * d.lab[0])+'</span>\
      </div>'
    );
  }
  if(d.major_fee > 0){
    $("#first_semester_fees_list").append(
      '<div class="list-group-item d-flex justify-content-between align-items-center">\
        <small>Major Fee</small>\
        <span>$'+fmt(d.major_fee)+'</span>\
      </div>'
    );
  }

  if(d.fee.int > 0){
    $("#first_semester_fees_list").append(
      '<div class="list-group-item d-flex justify-content-between align-items-center">\
        <small>International Student Matriculation Fee</small>\
        <span>$'+fmt(d.fee.int)+'</span>\
      </div>'
    );
  } else {
    $("#first_semester_fees_list").append(
      '<div class="list-group-item d-flex justify-content-between align-items-center">\
        <small>One-Time New Student Enrollment Fee</small>\
        <span>$'+fmt(d.fee.enrl)+'</span>\
      </div>'
    );
  }
  

  //Fall semester number of credits
  $(".credit-1").text(fmt(d.credits[0]));
  //Spring semester number of credits
  $(".credit-2").text(fmt(d.credits[1]));

  //Spring tuiition total 
  $(".t-total-2").text(fmt(d.multi*d.credits[1]));

  //Spring semester books
  $(".book-total-2").text(fmt(d.books[1]));
  //Spring semester tools
  $(".tool-total-2").text(fmt(d.tools[1]));

  //Spring semester total
  d.st_2 = d.books[1] + d.tools[1] + ((45)*d.lab[1] + d.major_fee) + (d.multi * d.credits[1]) + d.housing[1] + d.meal[1];
  $(".sem-total-2").text(fmt(d.st_2));

  
  //Spring semester fees table.
  $("#second_semester_fees_list").html('');
  if(d.lab[1] > 0){
    $("#second_semester_fees_list").append(
      '<div class="list-group-item d-flex justify-content-between align-items-center">\
        <small>Lab Fee<br>\
        $'+d.fee.lab+' per hour × '+d.lab[1]+' hours\
        </small>\
        <span>$'+fmt(d.fee.lab * d.lab[1])+'</span>\
      </div>'
    );
  }
  if(d.major_fee > 0){
    //$("#table_1 tbody").append('<tr><th scope="row">Major Fee</th><td  class="text-muted"></td><td>$'+fmt(d.major_fee)+'</td></tr>');
    $("#second_semester_fees_list").append(
      '<div class="list-group-item d-flex justify-content-between align-items-center">\
        <small>Major Fee</small>\
        <span>$'+fmt(d.major_fee)+'</span>\
      </div>'
    );
  }

  //Summer semester?
  if(d.credits[2] > 0){
    $("#semester-summer").removeClass('d-none');

    $(".credit-s").text(d.credits[2]);
    $(".t-total-s").text(fmt(d.multi*d.credits[2]));
    //$(".f-total-s").text((45)*d.lab[2] + d.major_fee); //Fees
    $(".book-total-s").text(d.books[2]);
    $(".tool-total-s").text(d.tools[2]);

    //Semester total
    d.st_s = d.books[2] + d.tools[2] + (45 * d.lab[2] + d.major_fee) + (d.multi * d.credits[2]) + d.housing[2];
    $(".sem-total-s").text(fmt(d.st_s));
    
   //Summer semester fees table.
    $("#summer_semester_fees_list").html('');
    if(d.lab[2] > 0){
      $("#summer_semester_fees_list").append(
        '<div class="list-group-item d-flex justify-content-between align-items-center">\
          <small>Lab Fee<br>\
          $'+d.fee.lab+' per hour × '+d.lab[2]+' hours\
          </small>\
          <span>$'+fmt(d.fee.lab * d.lab[2])+'</span>\
        </div>'
      );
    }

    if(d.major_fee > 0){
      $("#summer_semester_fees_list").append(
        '<div class="list-group-item d-flex justify-content-between align-items-center">\
          <small>Major Fee</small>\
          <span>$'+fmt(d.major_fee)+'</span>\
        </div>'
      );
    }    
  } else {
    //No sumer semester
    $("#semester-summer").addClass('d-none');
    d.st_s = 0;
  }

  //Show housing in details.
  $('#living_cost_details').html('');//Clear
  for(const i in d.housing){
    if(d.housing[i] > 0){
      var sem = 'Fall';
      if(i == 1){
        sem = 'Spring';
      }else if(i == 2){
        sem = "Summer";
      }

      if(sem === 'Fall'){
        $("#fall_living_cost_details").html(
          '<div class="list-group-item d-flex justify-content-between align-items-center">\
            <small>Housing</small>\
            <span>$'+fmt(d.housing[i])+'</span>\
          </div>'
        );
      }

      if(sem === 'Spring'){
        $("#spring_living_cost_details").html(
          '<div class="list-group-item d-flex justify-content-between align-items-center">\
            <small>Housing</small>\
            <span>$'+fmt(d.housing[i])+'</span>\
          </div>'
        );
      }

      if(sem === 'Summer'){
        $("#summer_living_cost_details").html(
          '<div class="list-group-item d-flex justify-content-between align-items-center">\
            <small>Housing</small>\
            <span>$'+fmt(d.housing[i])+'</span>\
          </div>'
        );
      }
    }
  }

  //Show meal plan in details
  for(const i in d.meal){
    if(d.meal[i] > 0){
      var sem = 'Fall';
      if(i == 1){
        sem = 'Spring';
      }else if(i == 2){
        sem = "Summer";
      }

      if(sem === 'Fall'){
        $("#fall_dining_cost_details").html(
          '<div class="list-group-item d-flex justify-content-between align-items-center">\
            <small>Dining</small>\
            <span>$'+fmt(d.meal[i])+'</span>\
          </div>'
        );
      }

      if(sem === 'Spring'){
        $("#spring_dining_cost_details").html(
          '<div class="list-group-item d-flex justify-content-between align-items-center">\
            <small>Dining</small>\
            <span>$'+fmt(d.meal[i])+'</span>\
          </div>'
        );
      }

      if(sem === 'Summer'){
        $("#summer_dining_cost_details").html(
          '<div class="list-group-item d-flex justify-content-between align-items-center">\
            <small>Dining</small>\
            <span>$'+fmt(d.meal[i])+'</span>\
          </div>'
        );
      }
    }
  }

  //Show Living total.
  let total_living = d.housing[0] + d.housing[1] + d.housing[2] + d.meal[0] + d.meal[1] + d.meal[2];
  $('.living-total').text(fmt(total_living));
  
  //Show total total
  let total_total = d.st_1 + d.st_2 + d.st_s;
  d.difference = d.st_1 + d.st_2 + d.st_s - d.aid.total;
  $('#total-total span').text(fmt(total_total));
  $('#total-total-difference span.count').text(fmt(d.difference));
}

/**
 * 
 * @param {Object} e jQ object of an element
 */
function show_section(e){
  if(d.verbose){console.log(`---show_section(${e.attr('id')})`);}
  $(e).slideDown(333).removeClass('hidden').addClass('showing'); 
  //$(window).trigger('resize');
  
  if($(e).find('.slick-2').length){
    if(jQuery().slick){
      $(e).find('.slick-2').slick('resize');
      $(e).find('.slick-2').slick('refresh');
    }
  }
}

/**
 * 
 * @param {Object} e jQ object of an element
 */
function hide_section(e){
  reset_section(e);
  $(e).slideUp(333).removeClass('showing').addClass('hidden');
}

/**
 * Removes a sections form selection and button active state.
 * 
 * @param {Object} e jQ obj 
 */
function reset_section(e){
  if(d.verbose){console.log(`---reset_section() ${e.attr('id')}`);}

  const str = e.find('[data-form-update]').attr('data-form-update');
  if(!str){
    return;
  }
  const args = str.split(';');
  $('input[name="'+args[1]+'"]').each(function(){
    $(this).prop('checked', false);
    $(this).prop('selected', false);
    $(this).removeAttr('checked');
  });
  
  e.find('.btn.active').removeClass('active');

  $(this).trigger('change');
}

(function($) {
  /**
   * This is called from the form controller on stateChanged.
   */
  $.fn.refresh_cost = function(args) {
    if(d.verbose){console.log('---refresh_academic_cost()');}
    
    //Load global data object with major specific items from the table and form results
    update_global_data(args.itemized_list);

    //Update the gui based global data object
    update_gui();
  };
  
  /**
   * Updates the visibility of each section. It's called from the CostFormController->stateChanged()
   */
  $.fn.refresh_section_visibility = function() {
    if(d.verbose){console.log('---refresh_section_visibility()');}

    const major_code = $('select[name="major_select"]').val();
    const eight_week_majors = ['AH','FA','CG'];
    const online_major = d.online;
    function handle_section_major(){
      if(major_code !== undefined){
        //Major is selected.
        if(d.verbose){console.log('d.online= ' + d.online);}
        if(eight_week_majors.includes(major_code)){
          if(d.verbose){console.log('8week');}
          //8 week
          //show_section($('#section_housing_on_eight_week'));
          //show_section($('#section_housing_on_eight_week_meal_choose'));
          show_section($('#section_residency_choose'));
          show_section($('#section_results'));
          show_section($('#section_details'));
        }else if(online_major){
          //No residence
          if(d.verbose){console.log('online major');}
          hide_section($('#section_residency_choose'));
          hide_section($('#section_international_choose'));
          show_section($('#section_results'));
          show_section($('#section_details'));
          //No meal or housing.
          hide_section($('#section_housing_choose'));
          hide_section($('#section_housing_on_choose'));
          hide_section($('#section_housing_on_meal_choose'));
          hide_section($('#section_housing_off_meal_choose'));
        }else{
          if(d.verbose){console.log('regular');}
          
          //show_section($('#section_results'));
          //show_section($('#section_details'));

          show_section($('#section_residency_choose'));
        }
      }else{
        //No form value
      }
    }
    handle_section_major();

    function handle_resident_section(){
      //Form value
      const form_value = $('input[name="residency_radio"]:checked').val();
      //console.log(`handle_resident_section(), form_value = ${form_value}`);

      //Show residency
      if(form_value !== undefined){
        //Show self
        show_section($('#section_residency_choose'));
        //Everyone has a chance to live off campus- except online?
        //show_section($('#section_housing_choose'));
        show_section($('#section_results'));
        show_section($('#section_details'));

        if(form_value === '587'){//587 in-state
          hide_section($('#section_international_choose'));
          show_section($('#section_housing_choose'));
        }else{
          show_section($('#section_international_choose'));
          show_section($('#section_housing_choose'));
        }
      }else{
        //No form value
      }
    }
    handle_resident_section();

    function handle_international_choose_section(){
      //Form value
      const form_value = $('input[name="international_radio"]:checked').val();//0 yes, 1 no.
      //console.log(`handle_international_choose_section(), form_value = ${form_value}`);

      //Show residency
      if(form_value !== undefined){
        //Show self
        show_section($('#section_international_choose'));
        show_section($('#section_housing_choose'));
      }else{
        //No form value, already hidden, dont show
      }
    }
    handle_international_choose_section();

    function handle_housing_choose_section(){
      //Form value
      const form_value = $('input[name="living_radio"]:checked').val();
      
      //console.log(`handle_housing_choose_section(), form_value = ${form_value}`);
      //Show residency
      if(form_value !== undefined){
        //Has value, show self
        show_section($('#section_housing_choose'));

        if(eight_week_majors.includes(major_code)){
          //8 week
          if(form_value === '0'){//0 is yes, 1 is no
            //Yes, on campus
            show_section($('#section_housing_on_eight_week'));
            show_section($('#section_housing_on_eight_week_meal_choose'));
            hide_section($('#section_housing_on_choose'));
            hide_section($('#section_housing_on_meal_choose'));
            hide_section($('#section_housing_off_meal_choose'));
          }else{
            //No, off campus
            hide_section($('#section_housing_on_eight_week'));
            hide_section($('#section_housing_on_eight_week_meal_choose'));
            hide_section($('#section_housing_on_choose'));
            hide_section($('#section_housing_on_meal_choose'));
            show_section($('#section_housing_off_meal_choose'));
          }
        }else{
          //regular
          if(form_value === '0'){//0 is yes, 1 is no
            //Yes, on campus
            show_section($('#section_housing_on_choose'));
            show_section($('#section_housing_on_meal_choose'));
            hide_section($('#section_housing_off_meal_choose'));
          }else{
            //No, off campus
            hide_section($('#section_housing_on_choose'));
            hide_section($('#section_housing_on_meal_choose'));
            show_section($('#section_housing_off_meal_choose'));
          }
        }
      }else{
        //No form value
      }
    }
    handle_housing_choose_section();
  };
})(jQuery);

