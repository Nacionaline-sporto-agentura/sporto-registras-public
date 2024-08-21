(function ($, window, document, undefined) {
    'use strict';

    var sr_gallery = {
        track: null,
        slides: [],
        nextButton: null,
        prevButton: null,
        dots: [],
        currentIndex: 0,
        updateButtons: function () {
            if (this.currentIndex === 0) {
                this.prevButton.classList.add('sport-base__photos_navigation__disabled');
            } else {
                this.prevButton.classList.remove('sport-base__photos_navigation__disabled');
            }

            if (this.currentIndex === this.slides.length - 1) {
                this.nextButton.classList.add('sport-base__photos_navigation__disabled');
            } else {
                this.nextButton.classList.remove('sport-base__photos_navigation__disabled');
            }
        },
        updateDots: function () {
            this.dots.forEach(dot => dot.classList.remove('sport-base__photos__pagination-bullet-active'));
            this.dots[this.currentIndex].classList.add('sport-base__photos__pagination-bullet-active');
        },
        removeAddedStyles: function() {
            if (window.innerWidth >= 768) {
                sr_gallery.slides.forEach(slide => {
                    slide.style.transition = '';
                    slide.style.transform = '';
                });
                this.track.style.transition = '';
                this.track.style.transform = '';
            }
        },
        init: function () {
            let that = this;
            this.track = document.querySelector('.sport-base__photos');

            if (this.track == null || this.track.length === 0 ) {
                console.warn('Sporto bazės galerijos elementų nerasta.');
                return;
            }

            this.slides = Array.from(this.track.children);
            this.nextButton = document.querySelector('.sport-base__photos_navigation__next');
            this.prevButton = document.querySelector('.sport-base__photos_navigation__prev');
            this.dots = Array.from(document.querySelector('.sport-base__photos__pagination').children);

            // Set up event listeners
            this.nextButton.addEventListener('click', () => {
                if (that.currentIndex < that.slides.length - 1) {
                    that.moveToSlide(that.currentIndex + 1);
                }
            });
            this.prevButton.addEventListener('click', () => {
                if (that.currentIndex > 0) {
                    that.moveToSlide(that.currentIndex - 1);
                }
            });
            this.dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    that.moveToSlide(index);
                });
            });

            // Swipe functionality
            let startX = 0;
            let endX = 0;
            this.track.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
            });

            this.track.addEventListener('touchend', (e) => {
                if (window.innerWidth >= 768) return false;
                endX = e.changedTouches[0].clientX;
                if (startX > endX + 50) {
                    // Swipe left
                    if (that.currentIndex < that.slides.length - 1) {
                        that.moveToSlide(that.currentIndex + 1);
                    }
                } else if (startX < endX - 50) {
                    // Swipe right
                    if (that.currentIndex > 0) {
                        that.moveToSlide(that.currentIndex - 1);
                    }
                }
            });

            this.updateButtons();
            this.updateDots();
            window.addEventListener('resize', this.removeAddedStyles);
        },
        moveToSlide: function (index) {
            this.track.style.transform = `translateX(-${index * 100}%)`;
            this.currentIndex = index;
            this.updateButtons();
            this.updateDots();
        }
    };

    var sr_tabs = {
        init: function() {
            this.tabs = document.querySelectorAll('.sport-base__tab');
            this.contents = document.querySelectorAll('.sport-base__tab-content');

            if (this.tabs.length === 0 || this.contents.length === 0) {
                console.warn('Sporto bazės tabuliacijos elementų nerasta.');
                return;
            }
    
            this.tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    this.deactivateAllTabs();
                    this.activateTab(tab);
                });
            });
    
            const initialActiveTab = document.querySelector('.sport-base__tab--active');
            if (initialActiveTab) {
                this.activateTab(initialActiveTab);
            }
        },
    
        deactivateAllTabs: function() {
            this.tabs.forEach(tab => tab.classList.remove('sport-base__tab--active'));
            this.contents.forEach(content => content.style.display = 'none');
        },
    
        activateTab: function(tab) {
            const tabName = tab.getAttribute('data-tab');
            const content = document.querySelector(`.sport-base__tab-content[data-tab="${tabName}"]`);
            tab.classList.add('sport-base__tab--active');
            content.style.display = 'block';
        }
    }

    var sr_areas = {
        init: function() {
            this.bindEvents();
        },
        bindEvents: function() {
            var moreButtons = document.querySelectorAll('.sport-base__space__more');
            if (moreButtons.length === 0) {
                console.warn('Sporto bazės erdvių elementų nerasta.');
                return;
            }
            moreButtons.forEach(function(button) {
                button.addEventListener('click', sr_areas.toggleDetails);
            });
            document.addEventListener('click', sr_areas.closeAllDetails);
            document.querySelectorAll('.sport-base__space').forEach(function(space) {
                space.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
            });
        },
        toggleDetails: function(event) {
            event.stopPropagation();
            var button = event.currentTarget;
            var wrapper = button.closest('.sport-base__space').querySelector('.sport-base__space_additionalValues_wrapper');
            var expandText = button.querySelector('.expand-text');
            var collapseText = button.querySelector('.collapse-text');
            var isVisible = wrapper.style.display === 'block';

            if (isVisible) {
                wrapper.style.display = 'none';
                expandText.style.display = 'inline';
                collapseText.style.display = 'none';
            } else {
                sr_areas.closeAllDetails();
                wrapper.style.display = 'block';
                expandText.style.display = 'none';
                collapseText.style.display = 'inline';
            }
        },
        closeAllDetails: function() {
            var wrappers = document.querySelectorAll('.sport-base__space_additionalValues_wrapper');
            var moreButtons = document.querySelectorAll('.sport-base__space__more');

            wrappers.forEach(function(wrapper) {
                wrapper.style.display = 'none';
            });
            moreButtons.forEach(function(button) {
                var expandText = button.querySelector('.expand-text');
                var collapseText = button.querySelector('.collapse-text');
                expandText.style.display = 'inline';
                collapseText.style.display = 'none';
            });
        }
    };
    const sr_manager_map = {
        init: function() {
            const mapContainer = document.getElementById('sport-base__manager-map');
            if(!mapContainer || !maplibregl) return;
            const map = new maplibregl.Map({
                container: 'sport-base__manager-map',
                style: 'https://basemap.startupgov.lt/vector/styles/bright/style.json',
                center: [23.8813, 55.1694],
                zoom: 8
            });
            const marker = new maplibregl.Marker()
                .setLngLat([23.8813, 55.1694])
                .addTo(map);
        }
    }
    document.addEventListener('DOMContentLoaded', () => {
        sr_gallery.init();
        sr_tabs.init();
        sr_areas.init();
        sr_manager_map.init();
    });

}(jQuery, window, document));