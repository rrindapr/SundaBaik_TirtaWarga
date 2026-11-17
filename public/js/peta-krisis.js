// PBI-16: peta dasar (Leaflet + OpenStreetMap).
// PBI-17: titik lokasi laporan. PBI-18 (heatmap) bisa memakai window.TirtaPeta.
(function () {
    'use strict';

    var el = document.getElementById('petaKrisis');
    var fallback = document.getElementById('petaFallback');
    if (!el) return;

    function tampilkanFallback(alasan) {
        if (alasan) console.error('[Peta Krisis]', alasan);
        if (fallback) fallback.hidden = false;
    }

    // PBI-17: warna titik menurut tingkat keparahan
    var WARNA = { Kritis: '#e5484d', Sedang: '#f5a623', Rendah: '#4c9a5f' };

    // Popup dibuat dengan DOM (textContent) agar alamat dari pengguna tidak bisa menyisipkan HTML.
    function buatBaris(label, nilai) {
        var p = document.createElement('div');
        p.className = 'peta-popup-row';
        var l = document.createElement('span');
        l.textContent = label;
        var v = document.createElement('b');
        v.textContent = nilai;
        p.appendChild(l);
        p.appendChild(v);
        return p;
    }

    function buatPopup(t) {
        var box = document.createElement('div');
        box.className = 'peta-popup';

        var judul = document.createElement('strong');
        judul.textContent = t.kode;
        box.appendChild(judul);

        var alamat = document.createElement('p');
        alamat.textContent = t.alamat;
        box.appendChild(alamat);

        box.appendChild(buatBaris('Terdampak', t.kk + ' KK'));
        box.appendChild(buatBaris('Keparahan', t.tingkat));
        box.appendChild(buatBaris('Status', t.ditetapkan ? 'Ditetapkan' : t.status));

        if (window.PETA_URL_LAPORAN) {
            var a = document.createElement('a');
            a.href = window.PETA_URL_LAPORAN;
            a.textContent = 'Buka Laporan Masuk';
            box.appendChild(a);
        }
        return box;
    }

    try {
        if (typeof L === 'undefined') {
            tampilkanFallback('Leaflet (L) tidak ter-load. Cek tag script Leaflet di view.');
            return;
        }

        var cfg = JSON.parse(document.getElementById('petaConfig').textContent);
        var titik = [];
        try {
            titik = JSON.parse(document.getElementById('petaTitik').textContent) || [];
        } catch (e) {
            console.error('[Peta Krisis] data titik tidak valid', e);
        }

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

        // PBI-17: layer titik lokasi laporan
        var layerTitik = L.featureGroup();
        titik.forEach(function (t) {
            var marker = L.circleMarker([t.lat, t.lng], {
                radius: 10,
                color: t.ditetapkan ? '#1d4fa3' : '#ffffff',
                weight: t.ditetapkan ? 4 : 2,
                fillColor: WARNA[t.tingkat] || WARNA.Sedang,
                fillOpacity: 0.9
            });
            marker.bindPopup(buatPopup(t));
            marker.bindTooltip(t.kode);
            marker.addTo(layerTitik);
        });
        layerTitik.addTo(map);

       var layerControl = L.control.layers(
            { 'Peta': peta, 'Satelit': satelit },
            { 'Titik laporan': layerTitik },
            { position: 'topright' }
        ).addTo(map);
        L.control.scale({ imperial: false }).addTo(map);

        // Arahkan tampilan awal ke seluruh titik; tanpa titik, tetap di pusat Bandung
        function tampilanAwal() {
            if (titik.length > 0) {
                map.fitBounds(layerTitik.getBounds().pad(0.2), { maxZoom: 15 });
            } else {
                map.setView(cfg.center, cfg.zoom);
            }
        }
        tampilanAwal();

        var ResetControl = L.Control.extend({
            options: { position: 'topleft' },
            onAdd: function () {
                var btn = L.DomUtil.create('button', 'peta-reset');
                btn.type = 'button';
                btn.textContent = 'Reset tampilan';
                L.DomEvent.disableClickPropagation(btn);
                L.DomEvent.on(btn, 'click', tampilanAwal);
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

        setTimeout(function () { map.invalidateSize(); }, 200);
        window.addEventListener('load', function () { map.invalidateSize(); });

        window.TirtaPeta = {
            map: map,
            config: cfg,
            data: titik,
           layers: { peta: peta, satelit: satelit, titik: layerTitik },
            control: layerControl // PBI-18: dipakai peta-heatmap.js untuk menambah checkbox layer
        };
    } catch (e) {
        tampilkanFallback(e);
    }
})();