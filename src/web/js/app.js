// Dom7
var $$ = Dom7;

// Theme
var theme = 'auto';
if (document.location.search.indexOf('theme=') >= 0) {
    theme = document.location.search.split('theme=')[1].split('&')[0];
}

// Init App
var app = new Framework7({
    id: 'com.xbitbay.app',
    name: "xbitbay",
    root: '#app',
    theme: theme,
    data: function () {
        return {
            user: {
                firstName: 'John',
                lastName: 'Doe',
            },
        };
    },
    methods: {
        helloWorld: function () {
            app.dialog.alert('Hello World!');
        },
    },
    routes: routes,
    // Video Intelligence
    // vi: {
    //     placementId: 'pltd4o7ibb9rc653x14',
    // }
});

/* show hide app loader */
app.preloader.show();
$(window).on('load', function () {
    app.preloader.hide();
})

/* page inside iframe just for demo app */
if (self !== top) {
    $('body').addClass('max-demo-frame')
}


theme = 'theme-light';
var colorTheme = 'color-theme-gray';


function myColorTheme() {
    $$('.layout-theme').on('click', function () {
        $('body').removeClass(theme);
        theme = $(this).attr('value');
        $('body').addClass($(this).attr('value'));
    });
    $$('.layout-color-theme').on('click', function () {
        $('body').removeClass(colorTheme);
        var colorvalue = 'color-theme-' + $(this).attr('value');
        colorTheme = colorvalue;

        $('body').addClass(colorTheme);
    });
}

$$(document).on('page:init', '.page[data-name="dashboard"]', function (e) {
    $(".dynamicsparkline").sparkline([5, 6, 7, 2, 0, 4, 2, 5, 6, 7, 2, 0, 4, 2, 4], {
        type: 'bar',
        height: '25',
        barSpacing: 2,
        barColor: '#a9d7fe',
        negBarColor: '#ef4055',
        zeroColor: '#ffffff'
    });
    myColorTheme();
});
$$(document).on('page:init', '.page[data-name="welcome"]', function (e) {
    $(".dynamicsparkline").sparkline([5, 6, 7, 2, 0, 4, 2, 5, 6, 7, 2, 0, 4, 2, 4], {
        type: 'bar',
        height: '25',
        barSpacing: 2,
        barColor: '#a9d7fe',
        negBarColor: '#ef4055',
        zeroColor: '#ffffff'
    });
    myColorTheme();
});

$$(document).on('page:init', '.page[data-name="project-list"]', function (e) {
    $(".dynamicsparkline").sparkline([5, 6, 7, 2, 0, 4, 2, 5, 6, 7, 2, 0, 4, 2, 4], {
        type: 'bar',
        height: '25',
        barSpacing: 2,
        barColor: '#a9d7fe',
        negBarColor: '#ef4055',
        zeroColor: '#ffffff'
    });
    myColorTheme();
});

$$(document).on('page:init', '.page[data-name="aboutus"]', function (e) {
    myColorTheme();
});
$$(document).on('page:init', '.page[data-name="component"]', function (e) {
    myColorTheme();
});
$$(document).on('page:init', '.page[data-name="profile"]', function (e) {
    $(".dynamicsparkline").sparkline([5, 6, 7, 2, 0, 4, 2, 5, 6, 7, 2, 0, 4, 2, 4], {
        type: 'bar',
        height: '25',
        barSpacing: 2,
        barColor: '#a9d7fe',
        negBarColor: '#ef4055',
        zeroColor: '#ffffff'
    });
    myColorTheme();
});

$$(document).on('page:init', '.page[data-name="project-detail"]', function (e) {
    $(".dynamicsparkline").sparkline([5, 6, 7, 2, 0, 4, 2, 5, 6, 7, 2, 0, 4, 2, 4], {
        type: 'bar',
        height: '25',
        barSpacing: 2,
        barColor: '#a9d7fe',
        negBarColor: '#ef4055',
        zeroColor: '#ffffff'
    });

    /* Google chart */
    google.charts.load('current', {
        'packages': ['corechart']
    });
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Year', 'Sales', 'Expenses'],
          ['2013', 1000, 400],
          ['2014', 1170, 460],
          ['2015', 660, 1120],
          ['2016', 1030, 540]
        ]);

        var options = {
            vAxis: {
                minValue: 0
            },
            legend: {
                position: 'top',
                maxLines: 3
            },
            chartArea: {
                left: 38,
                top: 10,
                bottom: 20,
                width: '85%'
            }
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    }

    myColorTheme();

});
