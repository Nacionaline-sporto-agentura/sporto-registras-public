(function ($, window, document, undefined) {
    'use strict';

    String.prototype.sanitizeTitle = function () {
        return this.toLowerCase()
            .replace(/ą/g, 'a')
            .replace(/č/g, 'c')
            .replace(/ę/g, 'e')
            .replace(/ė/g, 'e')
            .replace(/į/g, 'i')
            .replace(/š/g, 's')
            .replace(/ų/g, 'u')
            .replace(/ū/g, 'u')
            .replace(/ž/g, 'z')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    };

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
                    if (slide) { // Ensure slide is not undefined or null
                        slide.style.transition = '';
                        slide.style.transform = '';
                    }
                });
                if (this.track) { // Ensure this.track is not null
                    this.track.style.transition = '';
                    this.track.style.transform = '';
                }
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
                    return;
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
    const sr_base_map = {
        init: function() {
            if (!document.getElementById('sport-base__manager-map') || !maplibregl) return;
            const mapElement = document.querySelector('.sport-base__manager-map-wrapper');
            const lat = parseFloat(mapElement.getAttribute('data-lat'));
            const lng = parseFloat(mapElement.getAttribute('data-lng'));

            const map = new maplibregl.Map({
                container: 'sport-base__manager-map',
                style: objVars.base_map_style,
                center: [lng, lat],
                zoom: objVars.map.zoom,
                attributionControl: false
            });
            map.addControl(new maplibregl.FullscreenControl());
            map.addControl(new maplibregl.AttributionControl({
                compact: true
            }));

            map.on('load', function() {
                const attributionDetails = document.querySelector('.maplibregl-compact');
                if (attributionDetails) {
                    attributionDetails.removeAttribute('open'); 
                    attributionDetails.classList.remove('maplibregl-compact-show'); 
                }
            });
            const markerDiv = document.createElement('div');
            markerDiv.className = 'custom-marker';
            markerDiv.style.backgroundImage = `url(${objVars.map.ico})`;
            markerDiv.style.backgroundSize = 'contain';
            markerDiv.style.width = `${objVars.map.ico_width}px`;
            markerDiv.style.height = `${objVars.map.ico_height}px`;

            const marker = new maplibregl.Marker({
                    element: markerDiv
                })
                .setLngLat([lng, lat])
                .addTo(map);
        }
    }
    const sr_organization_map = {
        getRepresentativeImageUrl: function(photos) {
            const representativePhoto = photos.find(photo => photo.representative === true);
            return representativePhoto ? representativePhoto.url : null;
        },
        init:function(){
            if (!document.getElementById('sport-organization__manager-map') || !maplibregl) return;

            const map = new maplibregl.Map({
                container: 'sport-organization__manager-map',
                style: objVars.base_map_style, //'https://basemap.startupgov.lt/vector/styles/bright/style.json',
                center: [25.279652, 54.687157],
                zoom: objVars.map.zoom,
                attributionControl: false
            });
          
            map.addControl(new maplibregl.AttributionControl({
                compact: true
            }));

            map.on('load', function() {
                const attributionDetails = document.querySelector('.maplibregl-compact');
                if (attributionDetails) {
                    attributionDetails.removeAttribute('open'); 
                    attributionDetails.classList.remove('maplibregl-compact-show'); 
                }
            });

            if($('#sport-organization__manager-map').data('sportbasesids') && $('#sport-organization__manager-map').data('sportbasesids').length > 0) { 
                map.on('load', async() => {
                    const image = await map.loadImage(objVars.map.ico);

                    map.addImage('ico', image.data, {
                        width: objVars.map.ico_width,
                        height: objVars.map.ico_height
                    });

                    map.addControl(new maplibregl.FullscreenControl());

                    map.addSource('registras', {
                        type: 'vector',
                        tiles: [objVars.base_map_url + '/{z}/{x}/{y}'+'/?query={"id":{"$in":['+$('#sport-organization__manager-map').data('sportbasesids')+']}}'],
                    });

                    map.addLayer({
                        id: 'cluster-circle',
                        type: 'circle',
                        filter: ['all', ['has', 'cluster_id']],
                        paint: {
                            'circle-color': '#003D2B',
                            'circle-opacity': 0.3,
                            'circle-radius': 20,
                        },
                        source: 'registras',
                        'source-layer': 'sportsBases',
                    });

                    map.addLayer({
                        id: 'point',
                        type: 'symbol',
                        source: 'registras',
                        filter: ['all', ['!has', 'cluster_id']],
                        layout: {
                            'icon-image': 'ico',
                            'icon-size': 1
                        },
                        'source-layer': 'sportsBases',
                    }, 'cluster-circle');

                    map.addLayer({
                        id: 'cluster',
                        type: 'symbol',
                        source: 'registras',
                        'source-layer': 'sportsBases',
                        filter: ['all', ['has', 'cluster_id']],
                        layout: {
                            'text-field': "{point_count}",
                            'text-font': ['Noto Sans Regular'],
                            'text-size': 16,
                        },
                        paint: {
                            'text-color': '#000000'
                        },
                    });
                    map.on('click', 'cluster', async (e) => {
                        const features = map.queryRenderedFeatures(e.point, {
                            layers: ['cluster']
                        });
                
                        if (!features.length) return; // No features found
                
                        const clusterId = features[0].properties.cluster_id;
                
                        try {
                            const source = map.getSource('registras');
                            // get current zoom
                            const zoom = map.getZoom();
                            const zoomedIn = zoom + 1;
                            
                            map.easeTo({
                                center: features[0].geometry.coordinates,
                                zoom: zoomedIn
                            });
                        } catch (error) {
                            console.error('Error zooming into cluster:', error);
                        }
                    });
                    map.on('click', 'point', (e) => {
                        const coordinates = e.features[0].geometry.coordinates.slice();
                        const featureId = e.features[0].properties.id;

                        // Ensure the popup opens at the correct coordinates
                        while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
                            coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
                        }

                        // Fetch additional data for the feature
                        fetch(`${objVars.base_map_url}/?query[id]=${featureId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.rows && data.rows.length > 0) {
                                    const featureData = data.rows[0];
                                    
                                    // Sanitize title for the URL
                                    const sanitizedTitle = featureData.name.sanitizeTitle();

                                    let popupContent = '<div class="sr-popup">';

                                    // Add the first image if available
                                    if (featureData.photos.length > 0 && sr_organization_map.getRepresentativeImageUrl(featureData.photos)!=null) {
                                        popupContent += `<img src="${sr_organization_map.getRepresentativeImageUrl(featureData.photos)}" alt="${featureData.name}" />`;
                                    }

                                    popupContent += `<h3>${featureData.name}</h3>`;
                                    const municipality = featureData.address.municipality || '';
                                    const street = featureData.address.street || '';
                                    const city = featureData.address.city || '';
                                    const house = featureData.address.house || '';

                                    popupContent += `<div class="address-wrapper">${street.name} ${house.plot_or_building_number}, ${city.name}, ${municipality.name}</div>`;


                                    const arrow_right = '<svg class="place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>';

                                    // Add the link
                                    popupContent += `<a target="_blank" class="place__url" href="${objVars.sport_base_url}${featureData.id}/${sanitizedTitle}/">${objVars.i18n.more}${arrow_right}</a>`;

                                    popupContent += '</div>';
                                    // Create and display the popup
                                    new maplibregl.Popup({ offset: 25 })
                                        .setLngLat(coordinates)
                                        .setHTML(popupContent)
                                        .addTo(map);
                                } else {
                                    console.error('No data found for the feature.');
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching feature data:', error);
                            });
                        });

                    map.on('mouseenter', 'point', () => {
                        map.getCanvas().style.cursor = 'pointer';
                    });

                    map.on('mouseleave', 'point', () => {
                        map.getCanvas().style.cursor = '';
                    });

                    map.on('mouseenter', 'cluster', () => {
                        map.getCanvas().style.cursor = 'pointer';
                    });
                    map.on('mouseleave', 'cluster', () => {
                        map.getCanvas().style.cursor = '';
                    });
                    
                });
            }
        }
    };

    const sr_tags = {
        init() {
            document.querySelectorAll('.tags-wrapper').forEach(wrapper => {
                const tags = Array.from(wrapper.querySelectorAll('.tag'));
                const moreButton = wrapper.querySelector('.more-button');
                
                if (moreButton) {
                    moreButton.addEventListener('click', () => this.showAllTags(wrapper, tags, moreButton));
                }
                
                const adjustVisibility = () => this.adjustTagVisibility(wrapper, tags, moreButton);
                window.addEventListener('resize', adjustVisibility);
                adjustVisibility();
            });
        },
    
        showAllTags(wrapper, tags, moreButton) {
            tags.forEach(tag => { tag.style.display = 'inline-flex';tag.style.position  = 'relative'; });
            moreButton.style.display = 'none';
            wrapper.style.flexWrap = 'wrap';
        },
    
        adjustTagVisibility(wrapper, tags, moreButton) {
            console.log('adjusting visibility');
            const wrapperWidth = wrapper.offsetWidth;
            let currentWidth = 0;
            let isOverflowing = false;
    
            tags.forEach(tag => {
                tag.style.display = 'inline-flex';
                tag.style.position  = 'relative';
                currentWidth += tag.offsetWidth + 8;
    
                if (currentWidth + (moreButton?.offsetWidth || 0) > wrapperWidth) {
                    tag.style.display = 'none';
                    tag.style.position  = 'absolute';
                    isOverflowing = true;
                }
            });
    
            if (moreButton) {
                moreButton.style.display = isOverflowing ? 'inline-flex' : 'none';
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        sr_gallery.init();
        sr_tabs.init();
        sr_areas.init();
        sr_base_map.init();
        sr_organization_map.init();
        sr_tags.init();
    });

}(jQuery, window, document));