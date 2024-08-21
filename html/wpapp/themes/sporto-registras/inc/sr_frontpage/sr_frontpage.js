(function ($, window, document, undefined) {
    'use strict';

    const sr_map = {
        init: function () {
            const mapContainer = document.getElementById('sr-map');
            if (!mapContainer || !maplibregl) return;
            const map = new maplibregl.Map({
                container: 'sr-map',
                style: 'https://basemap.startupgov.lt/vector/styles/bright/style.json',
                center: sr_map_config.coordinates,
                zoom: sr_map_config.zoom
            });

            if (sr_map_config.add_layer === 'true') {
                map.on('load', () => {
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
                        type: 'circle',
                        source: 'registras',
                        filter: ['all', ['!has', 'cluster_id']],
                        paint: {
                            'circle-color': '#003D2B',
                            'circle-opacity': 1,
                            'circle-radius': 5,
                        },
                        'source-layer': 'sportsBases',
                    });

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
                });
            } else {
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