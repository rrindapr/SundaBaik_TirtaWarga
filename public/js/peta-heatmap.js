// PBI-18: layer heatmap jumlah warga (KK) terdampak di Peta Krisis.
// Bergantung pada peta-krisis.js (window.TirtaPeta) dan Leaflet (L). Tanpa plugin eksternal:
// heatmap digambar sendiri di <canvas>, jadi tidak ada file vendor baru yang perlu di-commit.
(function () {
    'use strict';

    var T = window.TirtaPeta;
    if (!T || !T.map || typeof L === 'undefined') return; // peta dasar gagal dimuat -> lewati

    // ---- Pengaturan tampilan ------------------------------------------------
    var GRADIENT = {
        0.2: '#2b83ba',  // sedikit terdampak
        0.4: '#abdda4',
        0.6: '#ffffbf',
        0.8: '#fdae61',
        1.0: '#d7191c'   // paling banyak terdampak
    };
    var OPACITY     = 0.75;
    var RADIUS_M    = 700;  // radius pengaruh satu titik (meter)
    var RADIUS_MIN  = 22;   // batas radius dalam piksel
    var RADIUS_MAX  = 80;
    var BOBOT_MIN   = 0.15; // titik kecil tetap terlihat

    // ---- Data: ambil dari titik laporan PBI-17 (lat, lng, kk) ---------------
    var titik = (T.data || []).filter(function (t) {
        return isFinite(t.lat) && isFinite(t.lng) && Number(t.kk) > 0;
    });
    var maxKk = titik.reduce(function (m, t) { return Math.max(m, Number(t.kk)); }, 0);

    // ---- Palet warna 256 tingkat --------------------------------------------
    function buatPalet() {
        var c = document.createElement('canvas');
        c.width = 1;
        c.height = 256;
        var ctx = c.getContext('2d');
        var g = ctx.createLinearGradient(0, 0, 0, 256);
        Object.keys(GRADIENT).forEach(function (k) { g.addColorStop(parseFloat(k), GRADIENT[k]); });
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, 1, 256);
        return ctx.getImageData(0, 0, 1, 256).data;
    }
    var palet = buatPalet();

    // ---- Layer canvas khusus -------------------------------------------------
    var HeatLayer = L.Layer.extend({
        initialize: function (points, max) {
            this._points = points;
            this._max = max;
            this._stampCache = {};
        },

        onAdd: function (map) {
            this._map = map;
            // pane sendiri (di bawah titik laporan, z-index 400 = overlayPane)
            var pane = map.getPane('heatPane') || map.createPane('heatPane');
            pane.style.zIndex = 390;
            pane.style.pointerEvents = 'none';

            this._canvas = L.DomUtil.create('canvas', 'peta-heat-canvas leaflet-zoom-hide');
            pane.appendChild(this._canvas);

            map.on('move zoomend resize', this._jadwalkan, this);
            this._redraw();
        },

        onRemove: function (map) {
            L.DomUtil.remove(this._canvas);
            map.off('move zoomend resize', this._jadwalkan, this);
            this._canvas = null;
        },

        _jadwalkan: function () {
            if (this._raf) return;
            var self = this;
            this._raf = requestAnimationFrame(function () {
                self._raf = null;
                self._redraw();
            });
        },

        _radiusPx: function () {
            var map = this._map;
            var lat = map.getCenter().lat;
            var mPerPx = 156543.03392 * Math.cos(lat * Math.PI / 180) / Math.pow(2, map.getZoom());
            return Math.max(RADIUS_MIN, Math.min(RADIUS_MAX, Math.round(RADIUS_M / mPerPx)));
        },

        // "cap" lingkaran abu-abu yang memudar ke tepi; ditumpuk lalu diwarnai
        _stamp: function (r) {
            if (this._stampCache[r]) return this._stampCache[r];
            var s = document.createElement('canvas');
            s.width = s.height = r * 2;
            var x = s.getContext('2d');
            var g = x.createRadialGradient(r, r, 0, r, r, r);
            g.addColorStop(0, 'rgba(0,0,0,1)');
            g.addColorStop(1, 'rgba(0,0,0,0)');
            x.fillStyle = g;
            x.fillRect(0, 0, r * 2, r * 2);
            this._stampCache[r] = s;
            return s;
        },

        _redraw: function () {
            if (!this._map || !this._canvas) return;
            var map = this._map;
            var size = map.getSize();
            var c = this._canvas;

            c.width = size.x;   // sekaligus mengosongkan canvas
            c.height = size.y;
            L.DomUtil.setPosition(c, map.containerPointToLayerPoint([0, 0]));

            if (!this._points.length || !this._max || size.x === 0 || size.y === 0) return;

            var ctx = c.getContext('2d');
            var r = this._radiusPx();
            var stamp = this._stamp(r);

            this._points.forEach(function (t) {
                var p = map.latLngToContainerPoint([t.lat, t.lng]);
                if (p.x < -r || p.y < -r || p.x > size.x + r || p.y > size.y + r) return;
                ctx.globalAlpha = Math.max(BOBOT_MIN, Number(t.kk) / this._max);
                ctx.drawImage(stamp, p.x - r, p.y - r);
            }, this);
            ctx.globalAlpha = 1;

            // warnai berdasarkan akumulasi alpha
            var img = ctx.getImageData(0, 0, c.width, c.height);
            var d = img.data;
            for (var i = 3; i < d.length; i += 4) {
                var a = d[i];
                if (!a) continue;
                var j = a * 4;
                d[i - 3] = palet[j];
                d[i - 2] = palet[j + 1];
                d[i - 1] = palet[j + 2];
                d[i] = Math.min(255, a * OPACITY * 1.4);
            }
            ctx.putImageData(img, 0, 0);
        }
    });

    var layerHeat = new HeatLayer(titik, maxKk);

    // ---- Legenda -------------------------------------------------------------
    var legend = document.getElementById('petaHeatLegend');
    var bar = document.getElementById('petaHeatBar');
    var maxEl = document.getElementById('petaHeatMax');

    if (bar) {
        var stops = Object.keys(GRADIENT).map(function (k) {
            return GRADIENT[k] + ' ' + Math.round(parseFloat(k) * 100) + '%';
        });
        bar.style.background = 'linear-gradient(to right, ' + stops.join(', ') + ')';
    }
    if (maxEl) {
        maxEl.textContent = maxKk > 0 ? maxKk + ' KK' : 'Belum ada data';
    }
    layerHeat.on('add', function () { if (legend) legend.hidden = false; });
    layerHeat.on('remove', function () { if (legend) legend.hidden = true; });

    // ---- Pasang ke peta + kontrol layer (checkbox) ---------------------------
    layerHeat.addTo(T.map);
    if (T.control) T.control.addOverlay(layerHeat, 'Heatmap warga terdampak');

    T.layers = T.layers || {};
    T.layers.heatmap = layerHeat;
    T.heatmap = { layer: layerHeat, max: maxKk, data: titik };
})();