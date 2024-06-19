(function ($, window, document, undefined) {
    'use strict';
    const sr_map = {
        init: function () {
            const mapContainer = document.getElementById('sr-map');
            if (!mapContainer || !maplibregl) return;
            const map = new maplibregl.Map({
                container: 'sr-map',
                style: 'https://basemap.startupgov.lt/vector/styles/bright/style.json',
                center: [23.8813, 55.1694],
                zoom: 7
            });
            const marker = new maplibregl.Marker()
                .setLngLat([23.8813, 55.1694])
                .addTo(map);
        }
    }
    document.addEventListener('DOMContentLoaded', () => {
        sr_map.init();
    });

}(jQuery, window, document));