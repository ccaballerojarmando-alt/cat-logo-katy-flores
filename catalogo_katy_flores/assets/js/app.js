(function () {
    'use strict';
    var menuButton = document.querySelector('.menu-toggle');
    var mainNav = document.getElementById('main-nav');
    if (menuButton && mainNav) {
        menuButton.addEventListener('click', function () {
            var open = mainNav.classList.toggle('is-open');
            menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    var mainImage = document.getElementById('main-product-image');
    document.querySelectorAll('.thumbnail[data-image]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (mainImage) mainImage.src = button.getAttribute('data-image');
            document.querySelectorAll('.thumbnail').forEach(function (item) { item.classList.remove('is-active'); });
            button.classList.add('is-active');
        });
    });

    var sidebarButton = document.querySelector('.mobile-sidebar-toggle');
    var sidebar = document.querySelector('.admin-sidebar');
    if (sidebarButton && sidebar) {
        sidebarButton.addEventListener('click', function () { sidebar.classList.toggle('is-open'); });
        document.addEventListener('click', function (event) {
            if (window.innerWidth <= 720 && sidebar.classList.contains('is-open') && !sidebar.contains(event.target) && !sidebarButton.contains(event.target)) {
                sidebar.classList.remove('is-open');
            }
        });
    }
}());
