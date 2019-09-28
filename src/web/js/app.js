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
    i18n_init();
});

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


/******************************************** language ********************************************/
/**
 * internationalization
 * language file name: i18n_xx.json, xx is language shorten name
 * this plugin cannot parse json like {"xx":{"xxx" :"xxx"}}结构，直接写入{"xx.xxx":"xxx"}
 * .dropdown-item的属性：data-lang="xx", 控制选择该语言为xx
 * https://github.com/T-baby/jquery.i18n
*/
var storeLangName = 'defaultLang';

/*language initial*/
function i18n_init() {
  let defaultLang = localStorage.getItem(storeLangName) ? localStorage.getItem(storeLangName) : getBrowserLang();
  $("[i18n]").i18n({
      defaultLang: defaultLang,
      filePath: "../i18n/",
      filePrefix: "",
      fileSuffix: "",
      forever: true,
      callback: function() {}
  });
}

/* get client browser language*/
function getBrowserLang() {
    let lng = navigator.language;
    if(lng.substr(0, 2) === 'zh'){
        lng = lng.toLowerCase() === 'zh-cn' ? 'zhs' : 'zht'
    }else {
        lng = lng.substr(0, 2)
    }
    return lng
}

function showMenu (i) {
  let defaultLang = localStorage.getItem(storeLangName) ? localStorage.getItem(storeLangName) : getBrowserLang();
  $(i).find('.dropdown-menu').toggleClass('show');
  $('.dropdown-item[data-lang="' + defaultLang + '"]').addClass('lang_active')
}

/* button to switch language*/
function selectLang (i) {
  $(i).addClass('lang_active').siblings().removeClass('lang_active');
  localStorage.setItem('defaultLang',$(i).attr('data-lang'));
  $("[i18n]").i18n({
      defaultLang: $(i).attr('data-lang'),
  });
}

$$(document).on('page:init', function (e) {
    i18n_init();
    myColorTheme();
});

/******************************************** language end ********************************************/

$$(document).on('page:init', '.page[data-name="dashboard"]', function (e) {
    $(".dynamicsparkline").sparkline([5, 6, 7, 2, 0, 4, 2, 5, 6, 7, 2, 0, 4, 2, 4], {
        type: 'bar',
        height: '25',
        barSpacing: 2,
        barColor: '#a9d7fe',
        negBarColor: '#ef4055',
        zeroColor: '#ffffff'
    });

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
    // myColorTheme();
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
});
