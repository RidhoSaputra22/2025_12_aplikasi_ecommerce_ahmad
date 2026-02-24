<div x-data="{
        progress: @js($progress),
        eta: @js($eta),
        isArrived: @js($isArrived),
        distanceRemainingKm: @js($distanceRemainingKm),
        totalDistanceKm: @js($totalDistanceKm),
        originName: @js($origin['name']),
        destinationName: @js($destination['name']),
        formatEta(v) {
            if (!v) return '-';
            try { return new Date(v).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }); }
            catch(e) { return '-'; }
        }
    }" data-map-config="{{ json_encode([
        'shipmentId'         => $shipmentId,
        'origin'             => $origin,
        'destination'        => $destination,
        'currentPosition'    => $currentPosition,
        'progress'           => $progress,
        'heading'            => $heading,
        'shippedAt'          => $shippedAt,
        'speedMultiplier'    => (float) config('shipping.speed_multiplier', 1),
        'travelDurationSecs' => (int) (config('shipping.travel_duration_hours', 6) * 3600),
        'totalDistanceKm'    => $totalDistanceKm,
    ]) }}" class="relative rounded-xl overflow-hidden border border-gray-200">
    {{-- Map Container --}}
    <div id="ship-map-{{ $shipmentId }}" class="w-full" style="height: 420px; z-index: 1;"></div>

    {{-- Info Overlay --}}
    <div class="absolute bottom-3 left-3 right-3 z-[1000]">
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-lg p-4 border border-gray-100">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex-1 min-w-[200px]">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-semibold text-gray-600">Progres Perjalanan</span>
                        <span class="text-xs font-bold" :class="isArrived ? 'text-green-600' : 'text-blue-600'"
                            x-text="Math.round(progress * 100) + '%'"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                        <div class="h-2.5 rounded-full transition-all duration-1000"
                            :class="isArrived ? 'bg-green-500' : 'bg-blue-600'"
                            :style="'width:' + Math.round(progress * 100) + '%'"></div>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-600">
                    <div class="text-center" x-show="!isArrived">
                        <p class="font-bold text-gray-800 text-sm" x-text="distanceRemainingKm.toFixed(1) + ' km'"></p>
                        <p>Sisa Jarak</p>
                    </div>
                    <div class="text-center" x-show="!isArrived && eta">
                        <p class="font-bold text-gray-800 text-sm" x-text="formatEta(eta)"></p>
                        <p>Perkiraan Tiba</p>
                    </div>
                    <div x-show="isArrived" class="flex items-center gap-1.5 text-green-600 font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Kapal Telah Tiba!</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-3 pt-2.5 border-t border-gray-100 text-xs text-gray-500">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block shrink-0"></span>
                <span x-text="originName"></span>
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block shrink-0"></span>
                <span x-text="destinationName"></span>
                <span class="ml-auto text-gray-400" x-text="totalDistanceKm.toFixed(1) + ' km'"></span>
            </div>
        </div>
    </div>

    {{-- Live indicator --}}
    <div class="absolute top-3 right-3 z-[1000]" x-show="!isArrived">
        <div
            class="flex items-center gap-1.5 bg-white/90 backdrop-blur-sm rounded-full px-3 py-1.5 shadow-md border border-gray-100">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
            </span>
            <span class="text-xs font-semibold text-gray-700">LIVE</span>
        </div>
    </div>

    @script
    <script>
    (function() {
        var rootEl = $el;
        var cfg = JSON.parse(rootEl.dataset.mapConfig);
        var mapId = 'ship-map-' + cfg.shipmentId;

        function loadLeaflet(cb) {
            if (window.L) {
                cb();
                return;
            }
            var css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(css);

            var st = document.createElement('style');
            st.textContent =
                '.leaflet-popup-content-wrapper{border-radius:12px!important;box-shadow:0 4px 12px rgba(0,0,0,.15)!important}';
            document.head.appendChild(st);

            var js = document.createElement('script');
            js.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            js.onload = cb;
            document.head.appendChild(js);
        }

        function shipIcon(heading) {
            return L.divIcon({
                html: '<div style="transform:rotate(' + (heading - 90) + 'deg)">' +
                    '<svg width="44" height="44" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">' +
                    '<g transform="translate(32,32)">' +
                    '<path d="M-8,12 L-12,4 L-12,-8 L-6,-14 L0,-16 L6,-14 L12,-8 L12,4 L8,12 Z" fill="#1e40af" stroke="#1e3a8a" stroke-width="1"/>' +
                    '<rect x="-6" y="-8" width="12" height="14" rx="2" fill="#3b82f6"/>' +
                    '<rect x="-4" y="-5" width="8" height="6" rx="1" fill="#60a5fa"/>' +
                    '<path d="M-3,-14 L0,-18 L3,-14" fill="#2563eb" stroke="#1e40af" stroke-width="0.5"/>' +
                    '<path d="M-5,12 Q0,16 5,12" fill="none" stroke="white" stroke-width="1.5" opacity="0.6"/>' +
                    '</g></svg></div>',
                className: '',
                iconSize: [44, 44],
                iconAnchor: [22, 22]
            });
        }

        function pinIcon(color) {
            return L.divIcon({
                html: '<div style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;background:' +
                    color +
                    ';border-radius:50%;border:2px solid white;box-shadow:0 2px 8px rgba(0,0,0,.3)">' +
                    '<svg width="16" height="16" fill="none" stroke="white" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>' +
                    '</svg></div>',
                className: '',
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
        }

        function popup(title, color, sub) {
            return '<div style="text-align:center;padding:4px"><strong style="color:' + color + '">' + title +
                '</strong><br>' +
                '<span style="font-size:11px;color:#6b7280">' + sub + '</span></div>';
        }

        loadLeaflet(function() {
            var mapEl = document.getElementById(mapId);
            if (!mapEl || mapEl._leaflet_id) return;

            var map = L.map(mapEl, { zoomControl: true, scrollWheelZoom: true });
            L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/Ocean/World_Ocean_Base/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri', maxZoom: 13
                }).addTo(map);

            var o = cfg.origin, d = cfg.destination, c = cfg.currentPosition;
            map.fitBounds(L.latLngBounds([o.lat, o.lng], [d.lat, d.lng]).pad(0.15));

            L.marker([o.lat, o.lng], { icon: pinIcon('#22c55e') })
                .addTo(map).bindPopup(popup(o.name, '#15803d', 'Titik Keberangkatan'));
            L.marker([d.lat, d.lng], { icon: pinIcon('#ef4444') })
                .addTo(map).bindPopup(popup(d.name, '#dc2626', 'Tujuan'));
            L.polyline([[o.lat, o.lng], [d.lat, d.lng]], {
                color: '#6366f1', weight: 2.5, opacity: 0.5, dashArray: '10,10'
            }).addTo(map);

            var trail = L.polyline([[o.lat, o.lng], [c.lat, c.lng]], {
                color: '#6366f1', weight: 3, opacity: 0.8
            }).addTo(map);
            var ship = L.marker([c.lat, c.lng], {
                icon: shipIcon(cfg.heading), zIndexOffset: 1000
            }).addTo(map);

            // --- Client-side real-time interpolation ---
            // Replicates PHP ShipmentTrackingService formula every second.
            // shippedAtMs: epoch ms of when the ship departed.
            var shippedAtMs = cfg.shippedAt ? new Date(cfg.shippedAt).getTime() : null;
            var totalMs     = cfg.travelDurationSecs * 1000;
            var multiplier  = cfg.speedMultiplier;

            function calcInterpolated() {
                if (!shippedAtMs || totalMs <= 0) return null;
                var elapsed  = (Date.now() - shippedAtMs) * multiplier;
                if (elapsed < 0) elapsed = 0;
                var progress = Math.min(elapsed / totalMs, 1.0);
                var lat = o.lat + (d.lat - o.lat) * progress;
                var lng = o.lng + (d.lng - o.lng) * progress;
                // heading: atan2 toward destination
                var dLng   = (d.lng - lng) * Math.PI / 180;
                var lat1   = lat * Math.PI / 180;
                var lat2   = d.lat * Math.PI / 180;
                var y      = Math.sin(dLng) * Math.cos(lat2);
                var x      = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLng);
                var heading = (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
                var remaining = (1 - progress) * cfg.totalDistanceKm;
                return { lat: lat, lng: lng, progress: progress, heading: heading,
                         remaining: remaining, isArrived: progress >= 1.0 };
            }

            var ticker = setInterval(function() {
                var pos = calcInterpolated();
                if (!pos) return;

                ship.setLatLng([pos.lat, pos.lng]);
                if (Math.abs(pos.heading - cfg.heading) > 0.5) {
                    cfg.heading = pos.heading;
                    ship.setIcon(shipIcon(pos.heading));
                }
                trail.setLatLngs([[o.lat, o.lng], [pos.lat, pos.lng]]);

                var alpine = Alpine.$data(rootEl);
                if (alpine) {
                    alpine.progress            = pos.progress;
                    alpine.distanceRemainingKm = pos.remaining;
                    alpine.isArrived           = pos.isArrived;
                }

                if (pos.isArrived) clearInterval(ticker);
            }, 1000);

            // On WebSocket event: re-anchor shippedAt from server truth
            if (window.Echo) {
                window.Echo.channel('shipment.' + cfg.shipmentId)
                    .listen('.ShipPositionUpdated', function(data) {
                        // Re-sync: recalculate shippedAt equivalent from server progress
                        if (data.progress < 1.0) {
                            var serverElapsedMs = data.progress * totalMs;
                            shippedAtMs = Date.now() - serverElapsedMs / multiplier;
                        }
                        var alpine = Alpine.$data(rootEl);
                        if (alpine) {
                            alpine.eta       = data.eta;
                            alpine.isArrived = data.is_arrived;
                        }
                    });
            }
        });
    })();
    </script>
    @endscript
</div>
