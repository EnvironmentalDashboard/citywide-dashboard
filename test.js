// <![CDATA[
//console.log(''); // Delete this when in production
// FUNCTIONS //

// Timer function based on setInterval()
// See http://stackoverflow.com/a/8126515/2624391
function Timer(fn, t) {
    var timerObj = setInterval(fn, t);
    this.stop = function () {
        if (timerObj) {
            clearInterval(timerObj);
            timerObj = null;
        }
        return this;
    }
    // start timer using current settings (if it's not already running)
    this.start = function () {
        if (!timerObj) {
            this.stop();
            timerObj = setInterval(fn, t);
        }
        return this;
    }
    // start with new interval, stop current interval
    this.reset = function (newT) {
        t = newT;
        return this.stop().start();
    }
}

var landing_messages = [{ "message": "Welcome to Oberlin's Environmental Dashboard.  Try exploring our webpage by scrolling over different parts of the picture below." }, { "message": "Welcome to the Bioregional Dashboard.  Explore current environmental conditions in Oberlin by clicking on different icons in the picture below.  \r\n" }, { "message": "Welcome to Oberlin's Bioregional Dashboard.  Did you know that less than 1% of the water on Earth is readily accessible to humans?  Click on the icons below to see how water and electricity flow through our city's environment.\r\n" }, { "message": "Welcome to Oberlin's Environmental Resource Dashboard.  Did you know that the Great Lakes hold 20% of the Earth's readily accessible freshwater?  Click on the play button below to learn more." }, { "message": "Welcome to Oberlin\u2019s Environmental Dashboard.  The Energy Squirrel and Wally the Walleye will tell you about current environmental conditions in Oberlin and actions you can take.  Choose a resource or press the play button to start." }, { "message": "Energy units can be confusing. Kilowatt-hour is to gallon as kilowatt is to gallon-per-minute." }, { "message": "Good day and welcome to Oberlin's Bioregional Dashboard! Click on the icons below to learn more about the existing environmental conditions in Oberlin." }];
var electricity_messages = [{ "message": "Fantastic! We are conserving a lot of energy." }, { "message": "Baseload power is the amount of electricity that needs to be constantly generated to satisfy the minimum demands of the community." }, { "message": "Space heating and air conditioning consume the most energy in homes. Together they equal almost half of the electricity used in homes.  Using a programmable thermostat can help reduce these costs." }, { "message": "A kilowatt-hour of electricity is enough energy to make three brews of coffee." }, { "message": "The top three consumers of electricity in Oberlin are Oberlin College, The Federal Aviation Administration, and Lorain County Joint Vocational School." }, { "message": "In Oberlin, the peak load hours of electricity use in the spring, summer, and fall are between the hours of 2PM and 6PM.  In the winter, the peak occurs between the hours of 6AM and 10AM.  " }, { "message": "Try hang drying your clothing instead of using a  dryer. Your dryer uses enough energy in one load to power a ceiling fan for 33 hours!" }, { "message": "Ohioans pay on average 8 cents per kill-watt hour." }, { "message": "Greenhouse gases trap heat in the Earth's atmosphere like a blanket. The most common human-produced greenhouse gases are carbon dioxide and methane." }]; var stream_messages = [{ "message": "Make sure you don't add too much fertilizer to your lawn or garden.  Excess fertilizers will run off during rain and wash into the Plum Creek and Black River creating havoc for organisms trying to survive in those habitats." }, { "message": "Lake Erie holds a volume of 119 cubic miles of water.  That's 129 trillions gallons--enough to provide freshwater for all businesses and residents of the United States for 315 days." }, { "message": "Turbidity is a measure of water clarity.  Soil eroding from fields and other particles in the water increase turbidity and can be unhealthy for stream life." }, { "message": "It's been super wet. Plum Creek is raging." }]; var water_messages = [{ "message": "Replacing shower heads with low-flow units saves water and money.  One old shower head replaced with a low-flow alternative could easily save you 1,825 gallons of water annually--enough water to fill almost 50 full bath tubs!" }, { "message": "Each day 1.8 million children die worldwide from lack of water or from diseases contracted from tainted drinking water." }, { "message": "Oy! We have been using way too much water." }, { "message": "One out of every six gallons of water that is pumped into water mains by U.S. utilities simply leaks away back into the ground." }, { "message": "The Oberlin reservoir on Parsons Road can hold 386,000,000 gallons of water--that's about a 15 month supply if no water is added." }, { "message": "The Oberlin Freshwater Treatment Plant cleans about 850,000 gallons of water each day.  That's 170 gallons for each resident." }, { "message": "Oberlin\u2019s drinking water is pumped from the west branch of the Black River into a reservoir and then carefully cleaned and filtered before it is delivered to your home." }]; var weather_messages = [{ "message": "Weather in the Oberlin area is influenced by our temperate latitude but also by the proximity of Lake Erie, which moderates temperature and adds moisture to the atmosphere." }, { "message": "Oberlin, Ohio annually averages about 36 inches of precipitation.  That includes rain, sleet, snow, and hail." }, { "message": "Weather is the prevailing conditions of the atmosphere over a short period of time, and climate is how the atmosphere \"behaves\" over relatively longer periods of time." }, { "message": "Clouds can trap heat radiating from the Earth's surface which can actually increase the local ground temperature.  Clear, cloudless nights typically make for cooler conditions." }, { "message": "Air temperature has decreased considerably during this time period" }];
// Set landing gauges
$('#gauge1').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/');
$('#gauge2').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/');
$('#gauge3').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/');
$('#gauge4').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/');

var i = 0;
var current_state = 'landing';

$('#message').text(landing_messages[i++]['message']);
var msgTimer = new Timer(function () {
    if (i === landing_messages.length || current_state !== 'landing') {
        msgTimer.stop();
    } else {
        $('#message').text(landing_messages[i++]['message']);
    }
}, 10000);
var time = 0;
setInterval(function () {
    time++;
}, 1000);

// Functions for each state //
// 

function electricity() {
    // ga('send', 'event', 'Electricity button', 'Click', '', time);
    time = 0;
    console.log('called electricity()');
    // Set gauge URLs
    $('#gauge1').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1021');
    $('#gauge2').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1020');
    $('#gauge3').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1019');
    $('#gauge4').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1018');
    // Set powerline highlight
    $('#powerlines_lit, #powerlines_lit_back').attr('display', 'visible');
    // Set button to active state
    $('#electricity_highlight').css('opacity', '1');
    $('#electricity_hover, #electricity_btn').css('opacity', '0');
    // House
    $('#stick_electricity').attr('display', 'visible');
    $('#house_inside').attr('display', 'visible');
    // Electricity nodes
    $('#sparkpaths, #sparkpaths_back').attr('display', 'visible');
    // Select charachter
    $('#squirrel').css('opacity', '1');
    var i1 = 0;
    $('#message').text(electricity_messages[i1++]['message']);
    var msgTimer1 = new Timer(function () {
        if (i1 === electricity_messages.length || current_state !== 'electricity') {
            msgTimer1.stop();
        } else {
            $('#message').text(electricity_messages[i1++]['message']);
        }
    }, 10000);
    i = 0;
}

function undo_electricity() {
    console.log('called undo_electricity()');
    // Remove power line highlight
    $('#powerlines_lit, #powerlines_lit_back').attr('display', 'none');
    // Reset button state
    $('#electricity_highlight').css('opacity', '0');
    $('#electricity_btn').css('opacity', '1');
    // House
    $('#stick_electricity').attr('display', 'none');
    // Electricity nodes
    $('#sparkpaths, #sparkpaths_back').attr('display', 'none');
    $('#house_inside').attr('display', 'none');
    $('#squirrel').css('opacity', '0');
    i1 = 0;
}

function water() {
    console.log('called water()');
    // Set gauge URLs
    $('#gauge1').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1025');
    $('#gauge2').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1024');
    $('#gauge3').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1023');
    $('#gauge4').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1022');
    // Set button to active state
    $('#water_highlight').css('opacity', '1');
    $('#water_btn, #water_hover').css('opacity', '0');
    // House
    $('#stick_water').attr('display', 'visible');
    $('#house_inside').attr('display', 'visible');
    // Print messages
    // Animation stuff
    $('#waterlines_clip').css('opacity', '1');
    $('#fish').css('opacity', '1');
    var i2 = 0;
    $('#message').text(water_messages[i2++]['message']);
    var msgTimer2 = new Timer(function () {
        if (i2 === water_messages.length || current_state !== 'water') {
            msgTimer2.stop();
        } else {
            $('#message').text(water_messages[i2++]['message']);
        }
    }, 10000);
}

function undo_water() {
    console.log('called undo_water()');
    // Reset button state
    $('#water_highlight').css('opacity', '0');
    $('#water_btn, #water_hover').css('opacity', '1');
    $('#waterlines_clip').css('opacity', '0');
    // House
    $('#stick_water').attr('display', 'none');
    $('#house_inside').attr('display', 'none');
    $('#fish').css('opacity', '0');
    i2 = 0;
}

function stream() {
    console.log('called stream()');
    // Set gauge URLs, set button states, print message to top of SVG
    $('#gauge1').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1029');
    $('#gauge2').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1028');
    $('#gauge3').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1027');
    $('#gauge4').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1026');
    $('#stream_highlight').css('opacity', '1');
    $('#stream_btn, #stream_hover').css('opacity', '0');
    // House
    $('#stick_stream').attr('display', 'visible');
    $('#house_inside').attr('display', 'visible');
    // Animation
    $('#flow_marks').css('opacity', '1');
    $('#fish').css('opacity', '1');
    var i3 = 0;
    $('#message').text(stream_messages[i3++]['message']);
    var msgTimer3 = new Timer(function () {
        if (i3 === stream_messages.length || current_state !== 'stream') {
            msgTimer3.stop();
        } else {
            $('#message').text(stream_messages[i3++]['message']);
        }
    }, 10000);
}

function undo_stream() {
    console.log('called undo_stream()');
    // Reset button state
    $('#stream_highlight').css('opacity', '0');
    $('#stream_btn, #stream_hover').css('opacity', '1');
    // House
    $('#stick_stream').attr('display', 'none');
    $('#house_inside').attr('display', 'none');
    // Animation
    $('#flow_marks').css('opacity', '0');
    $('#fish').css('opacity', '0');
    i3 = 0;
}

function weather() {
    console.log('called weather()');
    // Set gauge URLs, set button states, print message to top of SVG
    $('#gauge1').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1033');
    $('#gauge2').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1032');
    $('#gauge3').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1031');
    $('#gauge4').attr('xlink:href', 'https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/1030');
    $('#weather_highlight').css('opacity', '1');
    $('#weather_btn, #weather_hover').css('opacity', '0');
    // House
    $('#stick_weather').attr('display', 'visible');
    $('#house_inside').attr('display', 'visible');
    $('#squirrel').css('opacity', '1');
    var i4 = 0;
    $('#message').text(weather_messages[i4++]['message']);
    var msgTimer4 = new Timer(function () {
        if (i4 === weather_messages.length || current_state !== 'weather') {
            msgTimer4.stop();
        } else {
            $('#message').text(weather_messages[i4++]['message']);
        }
    }, 10000);
}

function undo_weather() {
    console.log('called undo_weather()');
    // Reset button state
    $('#weather_highlight').css('opacity', '0');
    $('#weather_btn, #weather_hover').css('opacity', '1');
    // House
    $('#stick_weather').attr('display', 'none');
    $('#house_inside').attr('display', 'none');
    $('#squirrel').css('opacity', '0');
    i4 = 0;
}

// ANIMATIONS //

// These animaions happen continuously
// Bird animation
function bird_animation() {
    var arr = [];
    var x = -100; // initially move left
    var y = 100; // initially move down
    for (var i = 0; i < 25; i++) {
        arr[i] = {
            x: x,
            y: y
        };
        var rand = Math.random();
        x -= (100 * rand * 2); // some random #
        if (rand > .9) {
            y -= 25; // move up a bit
        } else if (rand > .8) {
            // nothing
        } else if (rand > .7) {
            y += 25; // move down
        } else if (rand > .3) {
            y += 50;
        } else if (rand > .1) {
            y += 75;
        }
    }
    return arr;
}
TweenMax.to($('#bird1, #bird2, #bird3, #bird4'), 10, {
    bezier: {
        type: 'cubic',
        values: bird_animation(),
        autoRotate: false
    },
    scaleX: 1.3,
    scaleY: 1.3,
    ease: Power1.easeIn,
    repeat: -1,
    repeatDelay: 10
}); //, x:"-1800px", y:(Math.random()*500)+"px", ease: Power1.easeIn, repeat: -1, repeatDelay: 10});
// var c = 1;
// var n = 2;
var direction = 0;
var frame = 1;
setInterval(function () {
    // $('#bird' + c).css('opacity', '0');
    // $('#bird' + n).css('opacity', '1');
    // if (c == 3) {
    //   n = 0;
    // }
    // if (c == 4) {
    //   c = 0;
    //   n = 1;
    // }
    // c++;
    // n++;
    $('#bird' + (frame + 1)).css('opacity', '0');
    if (frame >= 3 || frame <= 0) {
        direction = !direction;
    }
    if (direction) {
        frame--;
    } else {
        frame++;
    }
    $('#bird' + (frame + 1)).css('opacity', '1');

}, 100);
// Rain animation
TweenMax.to($('#raindrop-0'), 1, {
    y: 1000,
    repeat: -1,
    delay: 9.9
});
TweenMax.to($('#raindrop-1'), 1, {
    y: 1000,
    repeat: -1,
    delay: 1.4
});
TweenMax.to($('#raindrop-2'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6
});
TweenMax.to($('#raindrop-3'), 1, {
    y: 1000,
    repeat: -1,
    delay: 2.1
});
TweenMax.to($('#raindrop-4'), 1, {
    y: 1000,
    repeat: -1,
    delay: 7.1
});
TweenMax.to($('#raindrop-5'), 1, {
    y: 1000,
    repeat: -1,
    delay: 5
});
TweenMax.to($('#raindrop-6'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6.7
});
TweenMax.to($('#raindrop-7'), 1, {
    y: 1000,
    repeat: -1,
    delay: 2.2
});
TweenMax.to($('#raindrop-8'), 1, {
    y: 1000,
    repeat: -1,
    delay: 8.8
});
TweenMax.to($('#raindrop-9'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6.7
});
TweenMax.to($('#raindrop-10'), 1, {
    y: 1000,
    repeat: -1,
    delay: 2.8
});
TweenMax.to($('#raindrop-11'), 1, {
    y: 1000,
    repeat: -1,
    delay: 5.6
});
TweenMax.to($('#raindrop-12'), 1, {
    y: 1000,
    repeat: -1,
    delay: 4
});
TweenMax.to($('#raindrop-13'), 1, {
    y: 1000,
    repeat: -1,
    delay: 4.1
});
TweenMax.to($('#raindrop-14'), 1, {
    y: 1000,
    repeat: -1,
    delay: 2.1
});
TweenMax.to($('#raindrop-15'), 1, {
    y: 1000,
    repeat: -1,
    delay: 10
});
TweenMax.to($('#raindrop-16'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6.3
});
TweenMax.to($('#raindrop-17'), 1, {
    y: 1000,
    repeat: -1,
    delay: 9.2
});
TweenMax.to($('#raindrop-18'), 1, {
    y: 1000,
    repeat: -1,
    delay: 9.6
});
TweenMax.to($('#raindrop-19'), 1, {
    y: 1000,
    repeat: -1,
    delay: 7.2
});
TweenMax.to($('#raindrop-20'), 1, {
    y: 1000,
    repeat: -1,
    delay: 1.8
});
TweenMax.to($('#raindrop-21'), 1, {
    y: 1000,
    repeat: -1,
    delay: 5.4
});
TweenMax.to($('#raindrop-22'), 1, {
    y: 1000,
    repeat: -1,
    delay: 1.1
});
TweenMax.to($('#raindrop-23'), 1, {
    y: 1000,
    repeat: -1,
    delay: 2.1
});
TweenMax.to($('#raindrop-24'), 1, {
    y: 1000,
    repeat: -1,
    delay: 3.3
});
TweenMax.to($('#raindrop-25'), 1, {
    y: 1000,
    repeat: -1,
    delay: 7.8
});
TweenMax.to($('#raindrop-26'), 1, {
    y: 1000,
    repeat: -1,
    delay: 9.4
});
TweenMax.to($('#raindrop-27'), 1, {
    y: 1000,
    repeat: -1,
    delay: 7.6
});
TweenMax.to($('#raindrop-28'), 1, {
    y: 1000,
    repeat: -1,
    delay: 0.4
});
TweenMax.to($('#raindrop-29'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6
});
TweenMax.to($('#raindrop-30'), 1, {
    y: 1000,
    repeat: -1,
    delay: 8.6
});
TweenMax.to($('#raindrop-31'), 1, {
    y: 1000,
    repeat: -1,
    delay: 5
});
TweenMax.to($('#raindrop-32'), 1, {
    y: 1000,
    repeat: -1,
    delay: 0.5
});
TweenMax.to($('#raindrop-33'), 1, {
    y: 1000,
    repeat: -1,
    delay: 5.2
});
TweenMax.to($('#raindrop-34'), 1, {
    y: 1000,
    repeat: -1,
    delay: 7.4
});
TweenMax.to($('#raindrop-35'), 1, {
    y: 1000,
    repeat: -1,
    delay: 5.9
});
TweenMax.to($('#raindrop-36'), 1, {
    y: 1000,
    repeat: -1,
    delay: 0.1
});
TweenMax.to($('#raindrop-37'), 1, {
    y: 1000,
    repeat: -1,
    delay: 3.2
});
TweenMax.to($('#raindrop-38'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6.2
});
TweenMax.to($('#raindrop-39'), 1, {
    y: 1000,
    repeat: -1,
    delay: 0.9
});
TweenMax.to($('#raindrop-40'), 1, {
    y: 1000,
    repeat: -1,
    delay: 4.6
});
TweenMax.to($('#raindrop-41'), 1, {
    y: 1000,
    repeat: -1,
    delay: 2.6
});
TweenMax.to($('#raindrop-42'), 1, {
    y: 1000,
    repeat: -1,
    delay: 1.7
});
TweenMax.to($('#raindrop-43'), 1, {
    y: 1000,
    repeat: -1,
    delay: 4.2
});
TweenMax.to($('#raindrop-44'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6.1
});
TweenMax.to($('#raindrop-45'), 1, {
    y: 1000,
    repeat: -1,
    delay: 0.9
});
TweenMax.to($('#raindrop-46'), 1, {
    y: 1000,
    repeat: -1,
    delay: 4.9
});
TweenMax.to($('#raindrop-47'), 1, {
    y: 1000,
    repeat: -1,
    delay: 9.7
});
TweenMax.to($('#raindrop-48'), 1, {
    y: 1000,
    repeat: -1,
    delay: 3.8
});
TweenMax.to($('#raindrop-49'), 1, {
    y: 1000,
    repeat: -1,
    delay: 0.7
});
TweenMax.to($('#raindrop-50'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6.2
});
TweenMax.to($('#raindrop-51'), 1, {
    y: 1000,
    repeat: -1,
    delay: 10
});
TweenMax.to($('#raindrop-52'), 1, {
    y: 1000,
    repeat: -1,
    delay: 2.7
});
TweenMax.to($('#raindrop-53'), 1, {
    y: 1000,
    repeat: -1,
    delay: 8.7
});
TweenMax.to($('#raindrop-54'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6.4
});
TweenMax.to($('#raindrop-55'), 1, {
    y: 1000,
    repeat: -1,
    delay: 2.8
});
TweenMax.to($('#raindrop-56'), 1, {
    y: 1000,
    repeat: -1,
    delay: 4.6
});
TweenMax.to($('#raindrop-57'), 1, {
    y: 1000,
    repeat: -1,
    delay: 2.5
});
TweenMax.to($('#raindrop-58'), 1, {
    y: 1000,
    repeat: -1,
    delay: 0.5
});
TweenMax.to($('#raindrop-59'), 1, {
    y: 1000,
    repeat: -1,
    delay: 4.5
});
TweenMax.to($('#raindrop-60'), 1, {
    y: 1000,
    repeat: -1,
    delay: 4.8
});
TweenMax.to($('#raindrop-61'), 1, {
    y: 1000,
    repeat: -1,
    delay: 8.9
});
TweenMax.to($('#raindrop-62'), 1, {
    y: 1000,
    repeat: -1,
    delay: 1.1
});
TweenMax.to($('#raindrop-63'), 1, {
    y: 1000,
    repeat: -1,
    delay: 9.5
});
TweenMax.to($('#raindrop-64'), 1, {
    y: 1000,
    repeat: -1,
    delay: 9
});
TweenMax.to($('#raindrop-65'), 1, {
    y: 1000,
    repeat: -1,
    delay: 9.7
});
TweenMax.to($('#raindrop-66'), 1, {
    y: 1000,
    repeat: -1,
    delay: 8.8
});
TweenMax.to($('#raindrop-67'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6.6
});
TweenMax.to($('#raindrop-68'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6
});
TweenMax.to($('#raindrop-69'), 1, {
    y: 1000,
    repeat: -1,
    delay: 3.2
});
TweenMax.to($('#raindrop-70'), 1, {
    y: 1000,
    repeat: -1,
    delay: 9.7
});
TweenMax.to($('#raindrop-71'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6.1
});
TweenMax.to($('#raindrop-72'), 1, {
    y: 1000,
    repeat: -1,
    delay: 8.6
});
TweenMax.to($('#raindrop-73'), 1, {
    y: 1000,
    repeat: -1,
    delay: 8.8
});
TweenMax.to($('#raindrop-74'), 1, {
    y: 1000,
    repeat: -1,
    delay: 4.5
});
TweenMax.to($('#raindrop-75'), 1, {
    y: 1000,
    repeat: -1,
    delay: 2.7
});
TweenMax.to($('#raindrop-76'), 1, {
    y: 1000,
    repeat: -1,
    delay: 2.7
});
TweenMax.to($('#raindrop-77'), 1, {
    y: 1000,
    repeat: -1,
    delay: 9.3
});
TweenMax.to($('#raindrop-78'), 1, {
    y: 1000,
    repeat: -1,
    delay: 3.6
});
TweenMax.to($('#raindrop-79'), 1, {
    y: 1000,
    repeat: -1,
    delay: 4.8
});
TweenMax.to($('#raindrop-80'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6.1
});
TweenMax.to($('#raindrop-81'), 1, {
    y: 1000,
    repeat: -1,
    delay: 2.2
});
TweenMax.to($('#raindrop-82'), 1, {
    y: 1000,
    repeat: -1,
    delay: 1.9
});
TweenMax.to($('#raindrop-83'), 1, {
    y: 1000,
    repeat: -1,
    delay: 7.6
});
TweenMax.to($('#raindrop-84'), 1, {
    y: 1000,
    repeat: -1,
    delay: 0.8
});
TweenMax.to($('#raindrop-85'), 1, {
    y: 1000,
    repeat: -1,
    delay: 3
});
TweenMax.to($('#raindrop-86'), 1, {
    y: 1000,
    repeat: -1,
    delay: 2.3
});
TweenMax.to($('#raindrop-87'), 1, {
    y: 1000,
    repeat: -1,
    delay: 0.6
});
TweenMax.to($('#raindrop-88'), 1, {
    y: 1000,
    repeat: -1,
    delay: 3.2
});
TweenMax.to($('#raindrop-89'), 1, {
    y: 1000,
    repeat: -1,
    delay: 9.6
});
TweenMax.to($('#raindrop-90'), 1, {
    y: 1000,
    repeat: -1,
    delay: 3.2
});
TweenMax.to($('#raindrop-91'), 1, {
    y: 1000,
    repeat: -1,
    delay: 0.1
});
TweenMax.to($('#raindrop-92'), 1, {
    y: 1000,
    repeat: -1,
    delay: 1.7
});
TweenMax.to($('#raindrop-93'), 1, {
    y: 1000,
    repeat: -1,
    delay: 0.5
});
TweenMax.to($('#raindrop-94'), 1, {
    y: 1000,
    repeat: -1,
    delay: 3.8
});
TweenMax.to($('#raindrop-95'), 1, {
    y: 1000,
    repeat: -1,
    delay: 9.2
});
TweenMax.to($('#raindrop-96'), 1, {
    y: 1000,
    repeat: -1,
    delay: 1.3
});
TweenMax.to($('#raindrop-97'), 1, {
    y: 1000,
    repeat: -1,
    delay: 6.8
});
TweenMax.to($('#raindrop-98'), 1, {
    y: 1000,
    repeat: -1,
    delay: 4.5
});
TweenMax.to($('#raindrop-99'), 1, {
    y: 1000,
    repeat: -1,
    delay: 5.9
});
// Ship animation
TweenMax.to($('#ship'), 60, {
    scaleX: 0.7,
    scaleY: 0.7,
    x: "1260px",
    y: "140px",
    ease: Power1.easeInOut,
    repeat: -1,
    repeatDelay: 1
});
// Wind turbine animation
TweenMax.to($('#blades'), 2.5, {
    rotation: 360,
    transformOrigin: "50% 60%",
    repeat: -1,
    ease: Power0.easeNone
});
// Smokestack image swapping
var counter = 0;
var smokestack = setInterval(function () {
    if (counter++ % 2 == 0) {
        $('#pipes').children().attr('xlink:href', 'img/smokestack/smokestack2.png');
    } else {
        $('#pipes').children().attr('xlink:href', 'img/smokestack/smokestack1.png');
    }
}, 1000);
// Waterlines animations (very messy but there doesnt seem to be any other way of doing this but by animating clip-path)
var tl1 = new TimelineMax({
    repeat: -1
});
var waterlines_clip1 = $('#waterline_clip1').children()[0];
tl1.to(waterlines_clip1, 0.25, {
    y: 80,
    ease: Power0.easeNone
})
    .to(waterlines_clip1, 0.25, {
        x: 50,
        ease: Power0.easeNone
    })
    .to(waterlines_clip1, 1.5, {
        y: 385,
        x: 240,
        ease: Power0.easeNone
    })
    .to(waterlines_clip1, 0.25, {
        y: 410,
        x: 210,
        ease: Power0.easeNone
    })
    .to(waterlines_clip1, 1, {
        y: 450,
        x: -100,
        ease: Power0.easeNone
    });
var tl2 = new TimelineMax({
    repeat: -1,
    repeatDelay: 2
});
var waterlines_clip2 = $('#waterline_clip1').children()[1];
tl2.to(waterlines_clip2, 1, {
    x: 230,
    y: 140,
    ease: Power0.easeNone
});
var tl3 = new TimelineMax({
    repeat: -1,
    repeatDelay: 2
});
var waterlines_clip3 = $('#waterline_clip1').children()[2];
tl3.to(waterlines_clip3, 1, {
    x: -230,
    y: 160,
    ease: Power0.easeNone
});
var tl4 = new TimelineMax({
    repeat: -1
});
var waterlines_clip4 = $('#waterline_clip2').children()[0];
tl4.to(waterlines_clip4, 1, {
    x: -330,
    y: 75,
    ease: Power0.easeNone
});
var tl5 = new TimelineMax({
    repeat: -1
});
var waterlines_clip5 = $('#waterline_clip3').children()[0];
tl5.to(waterlines_clip5, 1, {
    x: 170,
    y: 270,
    ease: Power0.easeNone
})
    .to(waterlines_clip5, 0.375, {
        x: 50,
        y: 310,
        ease: Power0.easeNone
    })
    .to(waterlines_clip5, 1.5, {
        x: -500,
        y: 340,
        ease: Power0.easeNone
    })
    .to(waterlines_clip5, 0.5, {
        x: -620,
        y: 230,
        ease: Power0.easeNone
    })
var tl6 = new TimelineMax({
    repeat: -1,
    repeatDelay: 2
});
var waterlines_clip6 = $('#waterline_clip3').children()[1];
tl6.to(waterlines_clip6, 0.5, {
    x: -300,
    ease: Power0.easeNone
});
var tl6 = new TimelineMax({
    repeat: -1,
    repeatDelay: 0.5
});
var waterlines_clip6 = $('#waterline_clip4').children()[0];
tl6.to(waterlines_clip6, 0.5, {
    x: -50,
    y: 75,
    ease: Power0.easeNone
})
    .to(waterlines_clip6, 0.75, {
        x: 100,
        y: 150,
        ease: Power0.easeNone
    })
    .to(waterlines_clip6, 0.5, {
        x: 0,
        y: 200,
        ease: Power0.easeNone
    });
// Flow marks animation
TweenMax.to($('#flow_marks_clip').children()[0], 3, {
    y: "-600px",
    ease: Power0.easeNone,
    repeat: -1
});
// Smoke animation
TweenMax.to($('#smoke > image'), 1, {
    y: "-60px",
    x: "20px",
    scaleX: 2,
    scaleY: 1.5,
    opacity: 0,
    ease: Power0.easeNone,
    repeat: -1,
    repeatDelay: 1.1
});
// Spark animations
TweenMax.to($("#sparkpaths").children()[0], 1.5, {
    bezier: [{
        x: 15,
        y: -25
    }, {
        x: 30,
        y: -50
    }],
    ease: Power0.easeNone,
    repeat: -1
});
TweenMax.to($("#sparkpaths").children()[1], 1.5, {
    bezier: [{
        x: -150,
        y: 40
    }, {
        x: -200,
        y: 20
    }],
    ease: Power0.easeNone,
    repeat: -1
});
TweenMax.to($("#sparkpaths").children()[2], 1.5, {
    bezier: [{
        x: 60,
        y: -70
    }, {
        x: 100,
        y: -140
    }],
    ease: Power0.easeNone,
    repeat: -1
});
TweenMax.to($("#sparkpaths_back").children()[0], 1.5, {
    bezier: [{
        x: -10,
        y: 50
    }, {
        x: -20,
        y: 70
    }],
    ease: Power0.easeNone,
    repeat: -1
});
TweenMax.to($("#sparkpaths_back").children()[1], 1.5, {
    bezier: [{
        x: 25,
        y: 50
    }, {
        x: 75,
        y: 100
    }],
    ease: Power0.easeNone,
    repeat: -1
});

// LANDSCAPE COMPONENTS //

// Display landscape component messages on click
$('#agriculture, #city, #college, #houses, #industry, #mountains, #park, #reservoir, #town, #water_tower, #water_treatment').click(function () {
    var id = '#' + $(this).attr('id');
    var box = id + '_message';
    var close = id + '_close';
    $(box).attr('display', 'visible');
    $(close).attr('display', 'visible');
});
$('#agriculture_close, #city_close, #college_close, #houses_close, #industry_close, #mountains_close, #park_close, #reservoir_close, #town_close, #water_tower_close, #water_treatment_close').click(function () {
    // alert(this);
    var close = '#' + $(this).attr('id');
    var box = close.slice(0, -6) + '_message';
    $(box).attr('display', 'none');
    $(close).attr('display', 'none');
});
// Hover on landscape components (most of which are in g#clickables)
$('#pipes, #house_inside, #agriculture, #city, #college, #houses, #industry, #mountains, #park, #reservoir, #town, #water_tower, #water_treatment').hover(
    function () {
        $(this).attr('filter', 'url(#landscape_components_filter)');
        var id = $(this).attr('id');
        if (id == 'pipes') {
            $("#industry").attr('filter', 'url(#landscape_components_filter)');
        } else if (id == 'industry') {
            $("#pipes").attr('filter', 'url(#landscape_components_filter)');
        }
    },
    function () {
        $(this).attr('filter', '');
        var id = $(this).attr('id');
        if (id == 'pipes') {
            $("#industry").attr('filter', '');
        } else if (id == 'industry') {
            $("#pipes").attr('filter', '');
        }
    }
);

// BUTTONS //

// Hover on #buttons swaps out image
$('#electricity, #water, #stream, #weather, #landing').hover(
    function () {
        var id = $(this).attr('id');
        if ($('#' + id + '_highlight').css('opacity') == 0) {
            $('#' + id + '_btn').css('opacity', '0');
            $('#' + id + '_hover').css('opacity', '1');
        }
    },
    function () {
        var id = $(this).attr('id');
        if ($('#' + id + '_highlight').css('opacity') == 0) {
            $('#' + $(this).attr('id') + '_btn').css('opacity', '1');
            $('#' + $(this).attr('id') + '_hover').css('opacity', '0');
        }
    }
);


var last_state = 'landing';

function nextState(nextStateValue = null) {
    // first undo everything & then apply the current state
    undo_weather();
    undo_electricity();
    undo_water();
    undo_stream();

    // store current state into last state
    last_state = current_state;

    // define specific state value
    if (nextStateValue) {
        current_state = nextStateValue;
    } else {
        // use cycle of states as per last_state
        switch (last_state) {
            case 'landing':
                current_state = 'electricity';
                break;
            case 'electricity':
                current_state = 'water';
                break;
            case 'water':
                current_state = 'stream';
                break;
            case 'stream':
                current_state = 'weather';
                break;
            case 'weather':
                current_state = 'electricity';
                break;
        }
    }

    // trigger specific callback function as per the current state
    switch (current_state) {
        case 'electricity':
            electricity();
            break;
        case 'water':
            water();
            break;
        case 'stream':
            stream();
            break;
        case 'weather':
            weather();
            break;
    }

}

// Click on #buttons sets the index and calls the function of the new state
$('#electricity, #water, #stream, #weather, #landing').click(function () {
    if (current_state !== 'landing') {
        window['undo_' + current_state]();
    }
    current_state = $(this).attr('id');
    window[current_state](); // Call clicked state
});
// Automatically play
var playTimer = new Timer(function () {
    nextState();
}, 30000);
playTimer.stop(); // The cycle is first paused by default
var playtext = $('#playtext');
var pausetext = $('#pausetext');
// Play button
$('#play').click(function () {
    if (pausetext.attr('display') === 'none') { // Currently paused
        playtext.attr('display', 'none');
        pausetext.attr('display', 'visible');
        nextState(); // Call once because setInterval doesnt fire immediatly
        playTimer.start();
        console.log('play');
    } else { // Currently playing
        playtext.attr('display', 'visible');
        pausetext.attr('display', 'none');
        playTimer.stop();
        console.log('pause');
    }
});


// refresh every 5 mins to get new data
setTimeout(function () {
    if (pausetext.attr('display') !== 'none') { // Don't reload if CWD is paused
        window.location.reload();
    }
}, 5 * 1000 * 60);
