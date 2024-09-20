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
    

    const sr_map = {
        getRepresentativeImageUrl: function(photos) {
            const representativePhoto = photos.find(photo => photo.representative === true);
            return representativePhoto ? representativePhoto.url : null;
        },
        init: function () {
            const mapContainer = document.getElementById('sr-map');
            if (!mapContainer || !maplibregl) return;
            
            const map = new maplibregl.Map({
                container: 'sr-map',
                style: sr_map_config.base_map_style,
                center: sr_map_config.coordinates,
                zoom: sr_map_config.zoom,
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
            
            if (sr_map_config.add_layer == 'true') {
                map.on('load', async() => {

                    const image = await map.loadImage(sr_map_config.pin.url);

                    map.addImage('ico', image.data, {
                        width: sr_map_config.pin.size[0],
                        height: sr_map_config.pin.size[1]
                    });

                    map.addSource('registras', {
                        type: 'vector',
                        tiles: [sr_map_config.base_map_url + '/{z}/{x}/{y}'],
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

                        fetch(`${sr_map_config.base_map_url}/?query[id]=${featureId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.rows && data.rows.length > 0) {
                                    const featureData = data.rows[0];
                                    
                                    // Sanitize title for the URL
                                    const sanitizedTitle = featureData.name.sanitizeTitle();

                                    let popupContent = '<div class="sr-popup">';

                                    // Add the first image if available
                                    if (featureData.photos.length > 0 && sr_map.getRepresentativeImageUrl(featureData.photos)!=null) {
                                        popupContent += `<img src="${sr_map.getRepresentativeImageUrl(featureData.photos)}" alt="${featureData.name}" />`;
                                    }

                                    popupContent += `<h3>${featureData.name}</h3>`;
                                    const municipality = featureData.address.municipality;
                                    const street = featureData.address.street;
                                    const city = featureData.address.city;
                                    const house = featureData.address.house;

                                    popupContent += `<div class="address-wrapper">${street.name} ${house.plot_or_building_number}, ${city.name}, ${municipality.name}</div>`;


                                    const arrow_right = '<svg class="place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>';

                                    // Add the link
                                    popupContent += `<a target="_blank" class="place__url" href="${sr_map_config.sport_base_url}${featureData.id}/${sanitizedTitle}/">${sr_map_config.i18n.more}${arrow_right}</a>`;

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
            }else {
                const el = document.createElement('div');
                el.className = 'marker';
                el.style.backgroundImage = `url(${sr_map_config.pin.url})`;
                el.style.width = `${sr_map_config.pin.size[0]}px`;
                el.style.height = `${sr_map_config.pin.size[1]}px`;

                new maplibregl.Marker({
                        element: el
                    })
                    .setLngLat(sr_map_config.coordinates)
                    .addTo(map);
            }
        }
    }
    document.addEventListener('DOMContentLoaded', () => {
        sr_map.init();
    });

}(jQuery, window, document));