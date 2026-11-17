// PBI-16: peta dasar (Leaflet + OpenStreetMap).
// Objek peta disimpan di window.TirtaPeta untuk dipakai PBI-17 (titik) dan PBI-18 (heatmap).
(function () {
    'use strict';

    var el = document.getElementById('petaKrisis');
    var fallback = document.getElementById('petaFallback');
    if (!el) return;

    function tampilkanFallback(alasan) {
        if (alasan) console.error('[Peta Krisis]', alasan);
        if (fallback) fallback.hidden = false;
    }

    try {
        if (typeof L === 'undefined') {
            tampilkanFallback('Leaflet (L) tidak ter-load. Cek path vendor/leaflet/leaflet.js');
            return;
        }

        var cfg = JSON.parse(document.getElementById('petaConfig').textContent);

        var map = L.map(el, {
            center: cfg.center,
            zoom: cfg.zoom,
            minZoom: cfg.minZoom,
            maxZoom: cfg.maxZoom,
            maxBounds: cfg.bounds,
            maxBoundsViscosity: 0.8
        });

        var peta = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        });

        var satelit = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            { maxZoom: 19, attribution: 'Tiles &copy; Esri' }
        );

        peta.addTo(map);

        L.control.layers({ 'Peta': peta, 'Satelit': satelit }, null, { position: 'topright' }).addTo(map);
        L.control.scale({ imperial: false }).addTo(map);

        var ResetControl = L.Control.extend({
            options: { position: 'topleft' },
            onAdd: function () {
                var btn = L.DomUtil.create('button', 'peta-reset');
                btn.type = 'button';
                btn.textContent = 'Reset tampilan';
                L.DomEvent.disableClickPropagation(btn);
                L.DomEvent.on(btn, 'click', function () {
                    map.setView(cfg.center, cfg.zoom);
                });
                return btn;
            }
        });
        new ResetControl().addTo(map);

        var gagal = 0;
        function hitungGagal() {
            gagal += 1;
            if (gagal >= 6) tampilkanFallback('Tile peta gagal dimuat (koneksi/diblokir)');
        }
        peta.on('tileerror', hitungGagal);
        satelit.on('tileerror', hitungGagal);
        peta.on('tileload', function () {
            gagal = 0;
            if (fallback) fallback.hidden = true;
        });

        // pastikan ukuran peta dihitung ulang setelah layout selesai
        setTimeout(function () { map.invalidateSize(); }, 200);
        window.addEventListener('load', function () { map.invalidateSize(); });

        window.TirtaPeta = {
            map: map,
            config: cfg,
            layers: { peta: peta, satelit: satelit }
        };
    } catch (e) {
        tampilkanFallback(e);
    }
})();