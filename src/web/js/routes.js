var routes = [
  // Index page
  {
    path: '/',
    url: './index.html',
    name: 'home',
  },
  // About page
  {
    path: '/aboutus/',
    url: './pages/about.html',
    name: 'about',
  },,
  // dasboard page
  {
    path: '/dashboard/',
    url: './pages/dashboard.html',
    name: 'about',
  },
  // Right Panel pages
  {
    path: '/panel-right-1/',
    content: '\
      <div class="page">\
        <div class="navbar">\
          <div class="navbar-inner sliding">\
            <div class="left">\
              <a href="#" class="link back">\
                <i class="icon icon-back"></i>\
                <span class="ios-only">Back</span>\
              </a>\
            </div>\
            <div class="title" i18n="language">Language</div>\
          </div>\
        </div>\
        <div class="page-content">\
          <div class="block">\
            <p>This is a right panel page 1</p>\
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quo saepe aspernatur inventore dolorum voluptates consequatur tempore ipsum! Quia, incidunt, aliquam sit veritatis nisi aliquid porro similique ipsa mollitia eaque ex!</p>\
          </div>\
        </div>\
      </div>\
    ',
  },
  {
    path: '/panel-right-2/',
    content: '\
      <div class="page">\
        <div class="navbar">\
          <div class="navbar-inner sliding">\
            <div class="left">\
              <a href="#" class="link back">\
                <i class="icon icon-back"></i>\
                <span class="ios-only">Back</span>\
              </a>\
            </div>\
            <div class="title">Panel Page 2</div>\
          </div>\
        </div>\
        <div class="page-content">\
          <div class="block">\
            <p>This is a right panel page 2</p>\
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quo saepe aspernatur inventore dolorum voluptates consequatur tempore ipsum! Quia, incidunt, aliquam sit veritatis nisi aliquid porro similique ipsa mollitia eaque ex!</p>\
          </div>\
        </div>\
      </div>\
    ',
  },
  {
    path: '/language/',
    content: '\
      <div class="page">\
        <div class="navbar">\
          <div class="navbar-inner sliding">\
            <div class="left">\
              <a href="#" class="link back">\
                <i class="icon icon-back"></i>\
                <span class="ios-only">Back</span>\
              </a>\
            </div>\
            <div class="title" i18n="language">Language</div>\
          </div>\
        </div>\
        <div class="page-content">\
          <div class="block">\
            <div class="list links-list">\
                  <ul>\
                      <li ><a href="javascript:;" data-lang="en" onclick="selectLang(this)" class="panel-close link"><svg class="icon" style="width: 1em; height: 1em;vertical-align: middle;fill: currentColor;overflow: hidden;" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="664"><path d="M1012.532736 412.5696a467.0976 467.0976 0 0 0-20.224-73.216h-38.1952l30.5664-19.6096a486.912 486.912 0 0 0-44.8-85.1968l-161.0752 105.3696h-119.0912l241.9712-157.7984c-7.1168-8.192-14.2336-16.384-21.8624-24.576l-227.7376 148.5312V20.48a450.2528 450.2528 0 0 0-61.184-13.1072v405.248h421.632zM431.975936 7.3216c-20.736 3.2768-41.472 7.68-61.1328 13.1072v288.9216L140.903936 159.744c-21.8624 22.9376-40.96 47.5136-58.4192 73.728l162.7136 105.984H126.721536L50.791936 289.6896a575.488 575.488 0 0 0-14.1824 32.256l26.7264 17.408h-33.28c-8.7552 23.552-15.3088 48.128-20.224 73.216h422.144V7.3216z m158.4128 1009.8176c20.736-3.2768 40.96-7.68 61.1328-13.1072v-280.1664l224.4608 146.3808c22.9376-23.5008 43.7248-49.152 62.2592-76.4928l-167.1168-108.6464h119.0912l80.2816 52.4288c4.352-9.3184 8.704-18.5856 12.544-27.8528l-37.6832-24.576h46.9504c8.7552-23.5008 15.3088-48.0768 20.224-73.216h-422.144v405.248zM9.831936 611.8912c4.9152 25.1392 11.4688 49.7152 20.224 73.216h42.0352l-33.8432 21.8112c12.544 30.0544 27.2896 58.4704 44.7488 85.1968l164.9664-107.5712h119.04l-244.6848 159.488c7.1168 8.192 14.2336 16.384 21.8624 24.0128l226.0992-147.456v282.88c19.6608 5.4784 40.448 10.3936 61.184 13.1072v-405.1968l-421.632 0.512z" fill="#FFFFFF" p-id="665"></path><path d="M983.041536 709.12c3.2768-8.192 6.5536-16.384 9.2672-24.576h-46.9504l37.6832 24.576z m-331.52 294.912a506.624 506.624 0 0 0 224.4608-133.7856l-224.4608-146.3808v280.1664z m340.7872-664.064a288.0512 288.0512 0 0 0-7.6288-19.712l-30.5664 19.6608h38.1952zM144.743936 867.9936a510.1568 510.1568 0 0 0 226.0992 135.424v-282.88l-226.0992 147.456zM370.843136 20.4288a515.9936 515.9936 0 0 0-229.9392 139.264l229.888 149.6576V20.4288z m508.416 137.1136A512.6144 512.6144 0 0 0 651.521536 20.4288v285.696l227.7376-148.5824zM36.609536 322.4576c-2.2016 5.9904-4.4032 11.4688-6.5536 17.4592h33.28c0-0.512-26.7264-17.4592-26.7264-17.4592zM30.055936 684.544c2.7136 7.68 5.4272 14.7456 8.704 21.8624l33.8944-21.8624H30.055936z" fill="#002377" p-id="666"></path><path d="M590.337536 1017.1392V611.84h422.1952c6.5536-32.256 9.8304-65.536 9.8304-99.3792 0-33.8944-3.2768-67.1744-9.8304-99.4304h-422.144V7.3216a496.5888 496.5888 0 0 0-75.9296-5.9904h-7.1168c-25.6512 0-51.3024 2.1504-75.8784 5.9904v405.248H9.831936A498.8928 498.8928 0 0 0 0.001536 511.9488c0 33.8432 3.2768 67.1744 9.8304 99.3792h422.144v405.248c24.576 3.84 50.2784 5.9904 75.9296 5.9904h6.5536a436.224 436.224 0 0 0 75.9296-5.4272" fill="#C2121F" p-id="667"></path><path d="M247.963136 684.544L82.945536 791.552c12.032 18.0736 25.1392 35.5328 39.3216 51.9168l244.6848-159.488-119.04 0.5632zM245.761536 339.968L82.433536 233.472a525.824 525.824 0 0 0-32.2048 56.7808l75.9296 49.664H245.761536z m532.992 0l161.6896-105.472c-12.032-17.9712-24.576-35.4816-38.7584-52.3776l-241.408 157.7984h118.528z m-8.192 344.576l167.168 108.6976c11.9808-18.0224 22.9376-37.1712 32.2048-56.2688l-80.2816-52.4288h-119.04z" fill="#C2121F" p-id="668"></path></svg>English</a></li>\
                      <li ><a href="javascript:;" data-lang="ar" onclick="selectLang(this)" class="panel-close link">العربية</a></li>\
                      <li ><a href="javascript:;" data-lang="de" onclick="selectLang(this)" class="panel-close link">Deutsch</a></li>\
                      <li ><a href="javascript:;" data-lang="es" onclick="selectLang(this)" class="panel-close link">Español</a></li>\
                      <li ><a href="javascript:;" data-lang="fr" onclick="selectLang(this)" class="panel-close link">Français</a></li>\
                      <li ><a href="javascript:;" data-lang="it" onclick="selectLang(this)" class="panel-close link">Italiano</a></li>\
                      <li ><a href="javascript:;" data-lang="ko" onclick="selectLang(this)" class="panel-close link">한국어</a></li>\
                      <li ><a href="javascript:;" data-lang="ja" onclick="selectLang(this)" class="panel-close link">日本語</a></li>\
                      <li ><a href="javascript:;" data-lang="pt" onclick="selectLang(this)" class="panel-close link">Português</a></li>\
                      <li ><a href="javascript:;" data-lang="ru" onclick="selectLang(this)" class="panel-close link">Pусский</a></li>\
                      <li ><a href="javascript:;" data-lang="th" onclick="selectLang(this)" class="panel-close link">ภาษาไทย</a></li>\
                      <li ><a href="javascript:;" data-lang="zhs" onclick="selectLang(this)" class="panel-close link">简体中文</a></li>\
                      <li ><a href="javascript:;" data-lang="zht" onclick="selectLang(this)" class="panel-close link">繁體中文</a></li>\
                  </ul>\
              </div>\
          </div>\
        </div>\
      </div>\
    ',
  },

  // pages
  {
    path: '/welcome/',
    url: './pages/welcome.html',
  },
  {
    path: '/verification/',
    url: './pages/verification.html',
  },
  {
    path: '/register/',
    url: './pages/register.html',
  },
  {
    path: '/projects/',
    url: './pages/project-list.html',
  },
  {
    path: '/projectdetail/',
    url: './pages/project-detail.html',
  },
  {
    path: '/profile/',
    url: './pages/profile.html',
  },
    
    
  // Components
  {
    path: '/component/',
    url: './pages/component.html',
  },
  {
    path: '/accordion/',
    url: './pages/accordion.html',
  },
  {
    path: '/action-sheet/',
    componentUrl: './pages/action-sheet.html',
  },
  {
    path: '/autocomplete/',
    componentUrl: './pages/autocomplete.html',
  },
  {
    path: '/badge/',
    componentUrl: './pages/badge.html',
  },
  {
    path: '/buttons/',
    url: './pages/buttons.html',
  },
  {
    path: '/calendar/',
    componentUrl: './pages/calendar.html',
  },
  {
    path: '/calendar-page/',
    componentUrl: './pages/calendar-page.html',
  },
  {
    path: '/cards/',
    url: './pages/cards.html',
  },
  {
    path: '/checkbox/',
    url: './pages/checkbox.html',
  },
  {
    path: '/chips/',
    componentUrl: './pages/chips.html',
  },
  {
    path: '/contacts-list/',
    url: './pages/contacts-list.html',
  },
  {
    path: '/content-block/',
    url: './pages/content-block.html',
  },
  {
    path: '/data-table/',
    componentUrl: './pages/data-table.html',
  },
  {
    path: '/dialog/',
    componentUrl: './pages/dialog.html',
  },
  {
    path: '/elevation/',
    url: './pages/elevation.html',
  },
  {
    path: '/fab/',
    url: './pages/fab.html',
  },
  {
    path: '/fab-morph/',
    url: './pages/fab-morph.html',
  },
  {
    path: '/form-storage/',
    url: './pages/form-storage.html',
  },
  {
    path: '/gauge/',
    componentUrl: './pages/gauge.html',
  },
  {
    path: '/grid/',
    url: './pages/grid.html',
  },
  {
    path: '/icons/',
    componentUrl: './pages/icons.html',
  },
  {
    path: '/infinite-scroll/',
    componentUrl: './pages/infinite-scroll.html',
  },
  {
    path: '/inputs/',
    url: './pages/inputs.html',
  },
  {
    path: '/lazy-load/',
    url: './pages/lazy-load.html',
  },
  {
    path: '/list/',
    url: './pages/list.html',
  },
  {
    path: '/list-index/',
    componentUrl: './pages/list-index.html',
  },
  {
    path: '/login-screen/',
    componentUrl: './pages/login-screen.html',
  },
  {
    path: '/login-screen-page/',
    componentUrl: './pages/login-screen-page.html',
  },
  {
    path: '/messages/',
    componentUrl: './pages/messages.html',
  },
  {
    path: '/navbar/',
    url: './pages/navbar.html',
  },
  {
    path: '/navbar-hide-scroll/',
    url: './pages/navbar-hide-scroll.html',
  },
  {
    path: '/notifications/',
    componentUrl: './pages/notifications.html',
  },
  {
    path: '/panel/',
    url: './pages/panel.html',
  },
  {
    path: '/photo-browser/',
    componentUrl: './pages/photo-browser.html',
  },
  {
    path: '/picker/',
    componentUrl: './pages/picker.html',
  },
  {
    path: '/popup/',
    componentUrl: './pages/popup.html',
  },
  {
    path: '/popover/',
    url: './pages/popover.html',
  },
  {
    path: '/preloader/',
    componentUrl: './pages/preloader.html',
  },
  {
    path: '/progressbar/',
    componentUrl: './pages/progressbar.html',
  },
  {
    path: '/pull-to-refresh/',
    componentUrl: './pages/pull-to-refresh.html',
  },
  {
    path: '/radio/',
    url: './pages/radio.html',
  },
  {
    path: '/range/',
    componentUrl: './pages/range.html',
  },
  {
    path: '/searchbar/',
    url: './pages/searchbar.html',
  },
  {
    path: '/searchbar-expandable/',
    url: './pages/searchbar-expandable.html',
  },
  {
    path: '/sheet-modal/',
    componentUrl: './pages/sheet-modal.html',
  },
  {
    path: '/smart-select/',
    url: './pages/smart-select.html',
  },
  {
    path: '/sortable/',
    url: './pages/sortable.html',
  },
  {
    path: '/statusbar/',
    componentUrl: './pages/statusbar.html',
  },
  {
    path: '/stepper/',
    componentUrl: './pages/stepper.html',
  },
  {
    path: '/subnavbar/',
    url: './pages/subnavbar.html',
  },
  {
    path: '/subnavbar-title/',
    url: './pages/subnavbar-title.html',
  },
  {
    path: '/swiper/',
    url: './pages/swiper.html',
    routes: [
      {
        path: 'swiper-horizontal/',
        url: './pages/swiper-horizontal.html',
      },
      {
        path: 'swiper-vertical/',
        url: './pages/swiper-vertical.html',
      },
      {
        path: 'swiper-space-between/',
        url: './pages/swiper-space-between.html',
      },
      {
        path: 'swiper-multiple/',
        url: './pages/swiper-multiple.html',
      },
      {
        path: 'swiper-nested/',
        url: './pages/swiper-nested.html',
      },
      {
        path: 'swiper-loop/',
        url: './pages/swiper-loop.html',
      },
      {
        path: 'swiper-3d-cube/',
        url: './pages/swiper-3d-cube.html',
      },
      {
        path: 'swiper-3d-coverflow/',
        url: './pages/swiper-3d-coverflow.html',
      },
      {
        path: 'swiper-3d-flip/',
        url: './pages/swiper-3d-flip.html',
      },
      {
        path: 'swiper-fade/',
        url: './pages/swiper-fade.html',
      },
      {
        path: 'swiper-scrollbar/',
        url: './pages/swiper-scrollbar.html',
      },
      {
        path: 'swiper-gallery/',
        componentUrl: './pages/swiper-gallery.html',
      },
      {
        path: 'swiper-custom-controls/',
        url: './pages/swiper-custom-controls.html',
      },
      {
        path: 'swiper-parallax/',
        url: './pages/swiper-parallax.html',
      },
      {
        path: 'swiper-lazy/',
        url: './pages/swiper-lazy.html',
      },
      {
        path: 'swiper-pagination-progress/',
        url: './pages/swiper-pagination-progress.html',
      },
      {
        path: 'swiper-pagination-fraction/',
        url: './pages/swiper-pagination-fraction.html',
      },
      {
        path: 'swiper-zoom/',
        url: './pages/swiper-zoom.html',
      },
    ],
  },
  {
    path: '/swipeout/',
    componentUrl: './pages/swipeout.html',
  },
  {
    path: '/tabs/',
    url: './pages/tabs.html',
  },
  {
    path: '/tabs-static/',
    url: './pages/tabs-static.html',
  },
  {
    path: '/tabs-animated/',
    url: './pages/tabs-animated.html',
  },
  {
    path: '/tabs-swipeable/',
    url: './pages/tabs-swipeable.html',
  },
  {
    path: '/tabs-routable/',
    url: './pages/tabs-routable.html',
    tabs: [
      {
        path: '/',
        id: 'tab1',
        content: ' \
        <div class="block"> \
          <p>Tab 1 content</p> \
          <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ullam enim quia molestiae facilis laudantium voluptates obcaecati officia cum, sit libero commodi. Ratione illo suscipit temporibus sequi iure ad laboriosam accusamus?</p> \
          <p>Saepe explicabo voluptas ducimus provident, doloremque quo totam molestias! Suscipit blanditiis eaque exercitationem praesentium reprehenderit, fuga accusamus possimus sed, sint facilis ratione quod, qui dignissimos voluptas! Aliquam rerum consequuntur deleniti.</p> \
          <p>Totam reprehenderit amet commodi ipsum nam provident doloremque possimus odio itaque, est animi culpa modi consequatur reiciendis corporis libero laudantium sed eveniet unde delectus a maiores nihil dolores? Natus, perferendis.</p> \
        </div> \
        ',
      },
      {
        path: '/tab2/',
        id: 'tab2',
        content: '\
        <div class="block"> \
          <p>Tab 2 content</p> \
          <p>Suscipit, facere quasi atque totam. Repudiandae facilis at optio atque, rem nam, natus ratione cum enim voluptatem suscipit veniam! Repellat, est debitis. Modi nam mollitia explicabo, unde aliquid impedit! Adipisci!</p> \
          <p>Deserunt adipisci tempora asperiores, quo, nisi ex delectus vitae consectetur iste fugiat iusto dolorem autem. Itaque, ipsa voluptas, a assumenda rem, dolorum porro accusantium, officiis veniam nostrum cum cumque impedit.</p> \
          <p>Laborum illum ipsa voluptatibus possimus nesciunt ex consequatur rem, natus ad praesentium rerum libero consectetur temporibus cupiditate atque aspernatur, eaque provident eligendi quaerat ea soluta doloremque. Iure fugit, minima facere.</p> \
        </div> \
        ',
      },
      {
        path: '/tab3/',
        id: 'tab3',
        content: '\
        <div class="block"> \
          <p>Tab 3 content</p> \
          <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ullam enim quia molestiae facilis laudantium voluptates obcaecati officia cum, sit libero commodi. Ratione illo suscipit temporibus sequi iure ad laboriosam accusamus?</p> \
          <p>Deserunt adipisci tempora asperiores, quo, nisi ex delectus vitae consectetur iste fugiat iusto dolorem autem. Itaque, ipsa voluptas, a assumenda rem, dolorum porro accusantium, officiis veniam nostrum cum cumque impedit.</p> \
          <p>Laborum illum ipsa voluptatibus possimus nesciunt ex consequatur rem, natus ad praesentium rerum libero consectetur temporibus cupiditate atque aspernatur, eaque provident eligendi quaerat ea soluta doloremque. Iure fugit, minima facere.</p> \
        </div> \
        ',
      },
    ],
  },
  {
    path: '/toast/',
    componentUrl: './pages/toast.html',
  },
  {
    path: '/toggle/',
    url: './pages/toggle.html',
  },
  {
    path: '/toolbar-tabbar/',
    componentUrl: './pages/toolbar-tabbar.html',
    routes: [
      {
        path: 'tabbar/',
        componentUrl: './pages/tabbar.html',
      },
      {
        path: 'tabbar-labels/',
        componentUrl: './pages/tabbar-labels.html',
      },
      {
        path: 'tabbar-scrollable/',
        componentUrl: './pages/tabbar-scrollable.html',
      },
      {
        path: 'toolbar-hide-scroll/',
        url: './pages/toolbar-hide-scroll.html',
      },
    ],
  },
  {
    path: '/tooltip/',
    componentUrl: './pages/tooltip.html',
  },
  {
    path: '/timeline/',
    url: './pages/timeline.html',
  },
  {
    path: '/timeline-vertical/',
    url: './pages/timeline-vertical.html',
  },
  {
    path: '/timeline-horizontal/',
    url: './pages/timeline-horizontal.html',
  },
  {
    path: '/timeline-horizontal-calendar/',
    url: './pages/timeline-horizontal-calendar.html',
  },
  {
    path: '/virtual-list/',
    componentUrl: './pages/virtual-list.html',
  },
  {
    path: '/virtual-list-vdom/',
    componentUrl: './pages/virtual-list-vdom.html',
  },

  // Color Themes
  {
    path: '/color-themes/',
    componentUrl: './pages/color-themes.html',
  },

  // Page Loaders
  {
    path: '/page-loader-template7/:user/:userId/:posts/:postId/',
    templateUrl: './pages/page-loader-template7.html',
    // additional context
    options: {
      context: {
        foo: 'bar',
      },
    },
  },
  {
    path: '/page-loader-component/:user/:userId/:posts/:postId/',
    componentUrl: './pages/page-loader-component.html',
    // additional context
    options: {
      context: {
        foo: 'bar',
      },
    },
  },

  // Default route (404 page). MUST BE THE LAST
  {
    path: '(.*)',
    url: './pages/404.html',
  },
];
