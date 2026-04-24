@extends('layouts.master')

@section('title', 'Live Staff Map')

@section('nav-head', 'Live Staff Map')

@section('action', 'Live Map')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .live-map-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 16px;
            min-height: 72vh;
        }

        #staffLiveMap {
            min-height: 72vh;
            border-radius: 8px;
            overflow: hidden;
            background: #eef2f6;
        }

        .live-map-panel {
            min-height: 72vh;
            max-height: 72vh;
            overflow: hidden;
        }

        .live-map-list {
            max-height: calc(72vh - 164px);
            overflow-y: auto;
        }

        .staff-location-item {
            border-bottom: 1px solid rgba(0, 0, 0, .08);
            cursor: pointer;
            transition: background-color .15s ease;
        }

        .staff-location-item:hover,
        .staff-location-item.active {
            background: rgba(15, 118, 110, .08);
        }

        .staff-location-avatar {
            width: 42px;
            height: 42px;
            object-fit: cover;
            flex: 0 0 42px;
        }

        .live-map-empty {
            min-height: 220px;
        }

        .live-map-marker {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .28);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
        }

        @media (max-width: 991.98px) {
            .live-map-shell {
                grid-template-columns: 1fr;
            }

            #staffLiveMap,
            .live-map-panel {
                min-height: 55vh;
                max-height: none;
            }

            .live-map-list {
                max-height: 360px;
            }
        }
    </style>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.employees.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Live Map Filter</h6>
            </div>
            <div class="card-body pb-0">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option value="">All Branches</option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterData['branch_id']) && $filterData['branch_id'] == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="department_id" id="department_id">
                            <option value="">All Departments</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="employee_id" id="employee_id">
                            <option value="">All Staff</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 d-md-flex">
                        <button type="button" class="btn btn-block btn-success me-md-2 me-0 mb-md-4 mb-2" id="applyLiveMapFilter">
                            {{ __('index.filter') }}
                        </button>
                        <button type="button" class="btn btn-block btn-primary me-md-2 me-0 mb-4" id="resetLiveMapFilter">
                            {{ __('index.reset') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="live-map-shell">
            <div class="card mb-0">
                <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div>
                        <h6 class="card-title mb-1">All Staff Realtime Location</h6>
                        <small class="text-muted">Shows staff currently logged into the app and refreshes every 15 seconds from their latest location records.</small>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="refreshLiveMap">
                        <i class="link-icon" data-feather="refresh-cw"></i>
                        Refresh
                    </button>
                </div>
                <div class="card-body p-0">
                    <div id="staffLiveMap"></div>
                </div>
            </div>

            <div class="card live-map-panel mb-0">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Staff Online Map</h6>
                        <span class="badge bg-primary" id="staffLocationCount">0</span>
                    </div>
                    <input type="search" class="form-control" id="staffLocationSearch" placeholder="Search staff, branch, department">
                    <small class="text-muted d-block mt-2" id="liveMapUpdatedAt">Loading latest locations...</small>
                </div>
                <div class="card-body p-0">
                    <div class="live-map-list" id="staffLocationList"></div>
                    <div class="live-map-empty d-none align-items-center justify-content-center text-center p-4" id="staffLocationEmpty">
                        <div>
                            <i class="link-icon text-muted mb-2" data-feather="map-pin"></i>
                            <p class="mb-0 fw-bold">No live locations found</p>
                            <small class="text-muted">Logged-in staff will appear after the app sends their location data.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const locationsUrl = @json(route('admin.live-map.locations'));
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const initialBranchId = @json($filterData['branch_id'] ?? null);
        const initialDepartmentId = @json($filterData['department_id'] ?? null);
        const initialEmployeeId = @json($filterData['employee_id'] ?? null);
        const fallbackCenter = [11.5564, 104.9282];
        const hasLeaflet = typeof L !== 'undefined';
        const map = hasLeaflet ? L.map('staffLiveMap', {zoomControl: true}).setView(fallbackCenter, 12) : null;
        const markers = new Map();
        let allLocations = [];
        let activeEmployeeId = null;

        if (hasLeaflet) {
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
        } else {
            document.getElementById('staffLiveMap').innerHTML = `
                <div class="live-map-empty d-flex align-items-center justify-content-center text-center p-4 h-100">
                    <div>
                        <i class="link-icon text-muted mb-2" data-feather="map"></i>
                        <p class="mb-1 fw-bold">Map library did not load</p>
                        <small class="text-muted">Staff GPS data will still show in the list. Check internet/CDN access for Leaflet.</small>
                    </div>
                </div>
            `;
        }

        $('#department_id').select2();
        $('#employee_id').select2();
        $('#branch_id').select2();

        async function loadDepartments(branchId, selectedDepartmentId = null) {
            $('#department_id').empty().append('<option value="">All Departments</option>');

            if (!branchId) {
                return;
            }

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${branchId}`,
                });

                (response.data || []).forEach(department => {
                    const selected = String(department.id) === String(selectedDepartmentId) ? 'selected' : '';
                    $('#department_id').append(`<option value="${department.id}" ${selected}>${escapeHtml(department.dept_name)}</option>`);
                });
            } catch (error) {
                $('#department_id').append('<option disabled>Error loading departments</option>');
            }
        }

        async function loadEmployees({branchId = null, departmentId = null, selectedEmployeeId = null} = {}) {
            $('#employee_id').empty().append('<option value="">All Staff</option>');

            if (!branchId && !departmentId) {
                return;
            }

            const url = departmentId
                ? `{{ url('admin/employees/get-all-employees') }}/${departmentId}`
                : `{{ url('admin/employees/get-branch-employee') }}/${branchId}`;

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                });
                const data = await response.json();
                const users = data.data || data.employee || [];

                users.forEach(user => {
                    const selected = String(user.id) === String(selectedEmployeeId) ? 'selected' : '';
                    $('#employee_id').append(`<option value="${user.id}" ${selected}>${escapeHtml(user.name)}</option>`);
                });
            } catch (error) {
                $('#employee_id').append('<option disabled>Error loading staff</option>');
            }
        }

        function selectedFilters() {
            return {
                branch_id: isAdmin ? ($('#branch_id').val() || '') : (defaultBranchId || ''),
                department_id: $('#department_id').val() || '',
                employee_id: $('#employee_id').val() || ''
            };
        }

        function buildLocationsUrl() {
            const params = new URLSearchParams();
            Object.entries(selectedFilters()).forEach(([key, value]) => {
                if (value) {
                    params.append(key, value);
                }
            });

            return params.toString() ? `${locationsUrl}?${params.toString()}` : locationsUrl;
        }

        function initials(name) {
            return (name || '?')
                .split(' ')
                .filter(Boolean)
                .slice(0, 2)
                .map(part => part[0])
                .join('')
                .toUpperCase();
        }

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character]));
        }

        function markerIcon(location) {
            if (!hasLeaflet) {
                return null;
            }

            return L.divIcon({
                className: '',
                html: `<div class="live-map-marker">${initials(location.name)}</div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17],
                popupAnchor: [0, -18]
            });
        }

        function popupHtml(location) {
            const meta = [location.department, location.branch].filter(Boolean).join(' / ');
            return `
                <div class="d-flex gap-2 align-items-center">
                    <img src="${location.avatar}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover" alt="">
                    <div>
                        <strong>${escapeHtml(location.name)}</strong>
                        <div class="text-muted small">${escapeHtml(meta || 'No department')}</div>
                    </div>
                </div>
                <div class="small mt-2">Last seen ${escapeHtml(location.last_seen_human || '-')}</div>
                <a class="small" target="_blank" rel="noopener" href="${location.map_url}">Open in Google Maps</a>
            `;
        }

        function renderMap(locations) {
            if (!map) {
                return;
            }

            const mappableLocations = locations.filter(location => location.has_location);
            const visibleIds = new Set(mappableLocations.map(location => String(location.employee_id)));

            markers.forEach((marker, employeeId) => {
                if (!visibleIds.has(String(employeeId))) {
                    map.removeLayer(marker);
                    markers.delete(employeeId);
                }
            });

            const bounds = [];
            mappableLocations.forEach(location => {
                const position = [location.latitude, location.longitude];
                bounds.push(position);

                if (markers.has(location.employee_id)) {
                    markers.get(location.employee_id)
                        .setLatLng(position)
                        .setIcon(markerIcon(location))
                        .setPopupContent(popupHtml(location));
                    return;
                }

                const marker = L.marker(position, {icon: markerIcon(location)})
                    .addTo(map)
                    .bindPopup(popupHtml(location));
                markers.set(location.employee_id, marker);
            });

            if (bounds.length === 1) {
                map.setView(bounds[0], 15);
            } else if (bounds.length > 1) {
                map.fitBounds(bounds, {padding: [40, 40], maxZoom: 16});
            }
        }

        function staffItem(location) {
            const meta = [location.department, location.branch].filter(Boolean).join(' / ');
            const activeClass = String(activeEmployeeId) === String(location.employee_id) ? ' active' : '';
            const locationText = location.has_location
                ? `${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)}`
                : 'Waiting for GPS location from app';
            const badgeClass = location.has_location ? 'bg-light text-dark' : 'bg-warning text-dark';

            return `
                <div class="staff-location-item p-3${activeClass}" data-employee-id="${location.employee_id}">
                    <div class="d-flex gap-3">
                        <img src="${location.avatar}" class="rounded-circle staff-location-avatar" alt="">
                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <strong class="text-truncate">${escapeHtml(location.name)}</strong>
                                <span class="badge ${badgeClass}">${escapeHtml(location.last_seen_human || '-')}</span>
                            </div>
                            <div class="text-muted small text-truncate">${escapeHtml(meta || 'No department')}</div>
                            <div class="text-muted small">${escapeHtml(locationText)}</div>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderList() {
            const search = document.getElementById('staffLocationSearch').value.toLowerCase().trim();
            const filtered = allLocations.filter(location => {
                return [location.name, location.branch, location.department, location.email, location.phone]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase()
                    .includes(search);
            });

            document.getElementById('staffLocationList').innerHTML = filtered.map(staffItem).join('');
            document.getElementById('staffLocationCount').textContent = filtered.length;
            document.getElementById('staffLocationEmpty').classList.toggle('d-none', filtered.length !== 0);
            document.getElementById('staffLocationEmpty').classList.toggle('d-flex', filtered.length === 0);
            renderMap(filtered);
        }

        async function loadLocations() {
            try {
                const response = await fetch(buildLocationsUrl(), {
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    credentials: 'same-origin'
                });
                const payload = await response.json();

                if (!payload.success) {
                    throw new Error(payload.message || 'Unable to load live employee locations.');
                }

                allLocations = payload.locations || [];
                document.getElementById('liveMapUpdatedAt').textContent = `Updated ${new Date(payload.updated_at).toLocaleString()}`;
                renderList();
            } catch (error) {
                document.getElementById('liveMapUpdatedAt').textContent = error.message;
            }
        }

        document.getElementById('staffLocationList').addEventListener('click', function (event) {
            const item = event.target.closest('.staff-location-item');
            if (!item) {
                return;
            }

            activeEmployeeId = item.dataset.employeeId;
            const marker = markers.get(Number(activeEmployeeId)) || markers.get(activeEmployeeId);
            if (marker && map) {
                map.setView(marker.getLatLng(), 16);
                marker.openPopup();
            } else {
                document.getElementById('liveMapUpdatedAt').textContent = hasLeaflet
                    ? 'Selected staff is online, but the app has not sent GPS yet.'
                    : 'Map library did not load, but staff GPS data is listed here.';
            }

            document.querySelectorAll('.staff-location-item').forEach(listItem => {
                listItem.classList.toggle('active', listItem.dataset.employeeId === activeEmployeeId);
            });
        });

        document.getElementById('staffLocationSearch').addEventListener('input', renderList);
        document.getElementById('refreshLiveMap').addEventListener('click', loadLocations);
        document.getElementById('applyLiveMapFilter').addEventListener('click', loadLocations);
        document.getElementById('resetLiveMapFilter').addEventListener('click', async function () {
            if (isAdmin) {
                $('#branch_id').val('').trigger('change.select2');
            }

            $('#department_id').empty().append('<option value="">All Departments</option>').trigger('change.select2');
            $('#employee_id').empty().append('<option value="">All Staff</option>').trigger('change.select2');

            if (!isAdmin && defaultBranchId) {
                await loadDepartments(defaultBranchId);
                await loadEmployees({branchId: defaultBranchId});
            }

            loadLocations();
        });

        $('#branch_id').on('change', async function () {
            const branchId = $(this).val();
            await loadDepartments(branchId);
            await loadEmployees({branchId});
            loadLocations();
        });

        $('#department_id').on('change', async function () {
            const branchId = selectedFilters().branch_id;
            const departmentId = $(this).val();
            await loadEmployees({branchId, departmentId});
            loadLocations();
        });

        $('#employee_id').on('change', loadLocations);

        async function initializeFilters() {
            const branchId = initialBranchId || defaultBranchId || $('#branch_id').val();

            if (branchId) {
                if (isAdmin) {
                    $('#branch_id').val(branchId).trigger('change.select2');
                }

                await loadDepartments(branchId, initialDepartmentId);
                await loadEmployees({
                    branchId,
                    departmentId: initialDepartmentId,
                    selectedEmployeeId: initialEmployeeId
                });
            }

            loadLocations();
        }

        initializeFilters();
        setInterval(loadLocations, 15000);
    </script>
@endsection
