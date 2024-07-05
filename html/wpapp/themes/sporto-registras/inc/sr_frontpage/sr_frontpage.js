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

            const el = document.createElement('div');
            el.className = 'marker';
            el.style.backgroundImage = `url(${sr_map_config.pin.url})`;
            el.style.width = `${sr_map_config.pin.size[0]}px`;
            el.style.height = `${sr_map_config.pin.size[1]}px`;

            new maplibregl.Marker({element: el})
                .setLngLat(sr_map_config.coordinates)
                .addTo(map);
        }
    }
    document.addEventListener('DOMContentLoaded', () => {
        sr_map.init();
    });

}(jQuery, window, document));