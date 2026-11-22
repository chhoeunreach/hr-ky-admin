@extends('layouts.master')

@section('title',__('index.permission_setting'))

@section('action',__('index.assign'))

@section('button')
    <a href="{{route('admin.roles.index')}}" class="btn btn-primary btn-sm"> <i class="link-icon" data-feather="arrow-left"></i> @lang('index.back') </a>
@endsection
@section('styles')

    <style>
        .mobile-role-preview{position:sticky;top:1rem;width:min(100%,390px);margin:0 auto;padding:9px;border:1px solid #cbd5e1;border-radius:34px;background:#0f172a;box-shadow:0 12px 35px #0f172a24}
        .mobile-role-screen{height:690px;overflow:hidden;position:relative;border-radius:25px;background:#f5f7fb;scrollbar-width:thin}
        .mobile-role-sidebar{display:none;position:absolute;inset:0 auto 0 0;z-index:5;width:78%;overflow:auto;padding:18px 12px;background:linear-gradient(160deg,#f3f7fd,#e2ebfa,#cdddf6);color:#0f172a;box-shadow:8px 0 28px #0f172a33}
        .mobile-role-screen.drawer-open .mobile-role-sidebar{display:block}
        .mobile-role-screen.drawer-open:after{content:"";position:absolute;inset:0;background:#0f172a66;z-index:4}
        .mobile-role-brand{padding:4px 10px 14px;font-size:19px;font-weight:900;letter-spacing:.04em;color:#0b5fff}
        .mobile-role-sidebar-label{padding:8px 10px;color:#64748b;font-size:10px;text-transform:uppercase;letter-spacing:.08em}
        .mobile-role-zone{display:flex;gap:6px;align-items:center;flex-wrap:wrap;min-height:32px;padding:4px 8px}
        .mobile-role-zone[data-zone="home_quick"]{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:5px;padding:0 12px 12px}
        .mobile-role-zone[data-zone="bottom"]{justify-content:space-around;flex-wrap:nowrap;border-top:1px solid #e2e8f0}
        .mobile-role-zone[data-zone="sidebar"]{display:grid;gap:4px;min-height:40px;padding:0}
        .mobile-role-zone[data-zone="top"]{justify-content:flex-end;padding:0 12px}
        .mobile-role-zone.manage-drop{outline:1px dashed #0b5fff88;border-radius:8px}
        .mobile-role-zone-label{display:none;color:#64748b;font-size:9px}
        .mobile-role-screen.manage-mode .mobile-role-zone-label{display:block}
        .mobile-role-side-item{padding:9px;border-radius:10px;color:#0f172a;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .mobile-role-side-item.active{background:#0b5fff1a;color:#0b5fff;font-weight:700}
        .mobile-role-body{height:100%;overflow:auto;padding-bottom:52px;background:#f5f7fb}
        .mobile-role-status{height:26px;padding:5px 16px;background:#fff;color:#0f172a;font-size:10px}
        .mobile-role-head{display:flex;align-items:center;gap:12px;padding:12px 14px;background:#0b5fff;color:white}
        .mobile-role-head strong{display:block;font-size:17px;color:white;flex:1}
        .mobile-role-head span{font-size:11px;color:#dbeafe}
        .mobile-role-greeting{margin:12px;padding:14px;border:1px solid #e2e8f0;border-radius:15px;background:#fff;color:#334155;font-size:12px;box-shadow:0 3px 12px #0f172a0c}
        .mobile-role-greeting strong{display:block;font-size:15px;color:#0f172a}
        .mobile-role-section{padding:0 12px 10px;color:#0f172a;font-size:13px;font-weight:700}
        .mobile-role-function{cursor:grab;position:relative;max-width:100%;padding:8px 24px 8px 10px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;color:#0f172a;font-size:10px;box-shadow:0 3px 10px #0f172a0c;user-select:none}
        .mobile-role-remove{position:absolute;top:2px;right:3px;width:20px;height:20px;padding:0;border:0;border-radius:50%;background:#fee2e2;color:#b91c1c;font-size:15px;line-height:18px;cursor:pointer}
        .mobile-role-remove:hover{background:#fecaca}
        .mobile-role-function:active{cursor:grabbing}
        .mobile-role-zone[data-zone="home_quick"] .mobile-role-function{min-height:66px;display:flex;align-items:center;justify-content:center;text-align:center;padding:7px 4px}
        .mobile-role-zone[data-zone="bottom"] .mobile-role-function{border:0;box-shadow:none;background:transparent;padding:6px 2px;text-align:center;color:#64748b}
        .mobile-role-zone[data-zone="sidebar"] .mobile-role-function{display:block;width:100%;border:0;background:#ffffff80;box-shadow:none}
        .mobile-role-zone[data-zone="top"] .mobile-role-function{border-radius:999px;padding:5px 8px}
        .mobile-role-function.dragging{opacity:.45}
        .mobile-role-empty{grid-column:1/-1;padding:12px 8px;text-align:center;color:#64748b;font-size:10px}
        .mobile-role-bottom{position:absolute;bottom:0;left:0;right:0;padding:5px 2px;background:#fff;color:#64748b;font-size:10px}
        .mobile-role-bottom .mobile-role-zone-label{padding:4px;text-align:center}
        .mobile-role-available{max-width:600px;margin:12px auto;padding:12px;border:1px dashed #94a3b8;border-radius:12px;background:#f8fafc}
        .mobile-role-available[hidden]{display:none}
        .mobile-role-available h6{margin:0 0 8px;font-size:12px}
        .mobile-role-available .mobile-role-zone{min-height:45px}
        .mobile-role-menu{width:28px;height:28px;border:0;border-radius:50%;background:#ffffff26;color:white;font-size:18px;line-height:1}
        .mobile-role-manage{display:block;margin:0 auto 10px}
        @media(max-width:991.98px){.mobile-role-preview{position:static;margin:0 auto 20px}}
        li.item{position: relative;}
        li.item label p.grant_leave {
            visibility: hidden;
            transition: all ease-in-out 0.3s;
            width:550px;
            background-color: #fcfcfc;
            border:1px solid #f23e6d;
            padding: 10px;
            position: absolute;
            top: -20px;
            z-index: 1;
            left: 100%;
            border-radius: 10px;
            line-height: 1.5;
        }

        li.item label:hover p.grant_leave {
            visibility: visible;
            transition: all ease-in-out 0.3s;
        }
    </style>
@endsection
@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.role.common.breadcrumb')

        <div class="card">
            <div class="card-header card-nav">
                <ul class="nav nav-tabs d-md-flex d-block text-center">
                    @foreach($allRoles as $key => $value)
                        <a class="nav-link my-md-0 my-1 d-inline-block {{$value->id == $role->id ? 'active': ''}}" href="{{route('admin.roles.permission',$value->id)}}">
                            <button class="btn btn-md btn-{{$value->id == $role->id ? 'primary':'secondary'}}">{{ucfirst($value->name)}} </button>
                        </a>
                    @endforeach
                </ul>
            </div>
            <div class="card-body card-nav-content">
                <div class="row g-4 mb-4 align-items-start">
                    <div class="col-12">
                        <div class="mobile-role-preview" aria-label="Live mobile permission preview">
                            <div class="mobile-role-screen" id="mobile-role-screen">
                                <aside class="mobile-role-sidebar">
                                    <div class="mobile-role-brand">Digital HR</div>
                                    <div class="mobile-role-sidebar-label">{{ $role->name }} · Sidebar</div>
                                    <div class="mobile-role-zone-label">Drop functions here for the side menu</div>
                                    <div class="mobile-role-zone" data-zone="sidebar" id="mobile-role-sidebar-list"></div>
                                </aside>
                                <main class="mobile-role-body">
                                    <div class="mobile-role-status">9:41 <span class="float-end">●●● ◉</span></div>
                                    <div class="mobile-role-head"><button type="button" class="mobile-role-menu" id="mobile-role-menu" aria-label="Open app menu">☰</button><strong>Home</strong><span>Original Mode</span></div>
                                    <div class="mobile-role-zone-label px-2">Top actions</div>
                                    <div class="mobile-role-zone" data-zone="top" id="mobile-role-top"></div>
                                    <div class="mobile-role-greeting"><strong>Good morning</strong>Attendance and work overview</div>
                                    <div class="mobile-role-card mx-3 mb-3" id="mobile-role-attendance-summary"><label><span>◷</span><span>Attendance</span></label><small>Check in · Check out · Today’s summary</small></div>
                                    <div class="mobile-role-section">Quick services</div>
                                    <div class="mobile-role-zone" data-zone="home_quick" id="mobile-role-preview-list" aria-live="polite"></div>
                                    <div id="mobile-role-activity" class="mx-3 mb-3">
                                        <div class="mobile-role-card mb-2"><label><span>▥</span><span>Weekly report</span></label><small>Attendance overview</small></div>
                                        <div class="mobile-role-card"><label><span>♙</span><span>My team</span></label><small>Team attendance</small></div>
                                    </div>
                                    <div class="mobile-role-bottom"><div class="mobile-role-zone-label">Bottom navigation · Drop here</div><div class="mobile-role-zone" data-zone="bottom" id="mobile-role-bottom"></div></div>
                                </main>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mobile-role-manage" id="mobile-role-manage">Manage visible functions</button>
                        <div class="mobile-role-available" id="mobile-role-available" hidden><h6>Available functions · drag into the phone</h6><div class="mobile-role-zone" data-zone="hidden" id="mobile-role-hidden"></div></div>
                        <p class="small text-muted text-center mt-2">Drag functions into Top actions, Sidebar, Bottom navigation, or Home quick menu. A function can appear in more than one area. Drag it back to Available to remove that placement.</p>
                    </div>
                    <div class="col-12">
                <form class="forms-sample" id="role-permission-form" action="{{route('admin.role.assign-permissions',$role->id)}}" method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.role.common.permission')
                </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('body').on('click', '.repair-preset-btn', function() {
                let role = $(this).data('role');
                let group = $(this).closest('.group-checkbox');
                let checkboxes = group.find('.module_checkbox');
                
                checkboxes.prop('checked', false);
                
                if (role === 'admin') {
                    checkboxes.prop('checked', true);
                } else if (role === 'manager') {
                    checkboxes.filter('#repair_price\\.view, #repair_price\\.view_cost, #repair_price\\.manage_prices, #repair_price\\.create, #repair_price\\.update').prop('checked', true);
                } else if (role === 'employee') {
                    checkboxes.filter('#repair_price\\.view').prop('checked', true);
                } else if (role === 'none') {
                    checkboxes.prop('checked', false);
                }
                checkboxes.trigger('change');
            });

            $(function() {
                $('.js-check-all').on('click', function() {
                    // Get the checked state of the "check all" checkbox itself
                    let isChecked = $(this).prop('checked');

                    // Apply this state to all child checkboxes
                    const checkboxes = $(this).closest('.group-checkbox').find('.module_checkbox');
                    checkboxes.prop('checked', isChecked).trigger('change');
                });
            });

            const mobileMenuOptions = @json($mobileMenuOptions);
            const mobileMenuPermissionOptions = @json($mobileMenuPermissionOptions);
            const mobileLayout = @json($mobileLayout);
            const zones = ['top', 'sidebar', 'bottom', 'home_quick', 'hidden'];
            const zoneIds = {top: 'mobile-role-top', sidebar: 'mobile-role-sidebar-list', bottom: 'mobile-role-bottom', home_quick: 'mobile-role-preview-list', hidden: 'mobile-role-hidden'};
            const mobileScreen = document.getElementById('mobile-role-screen');
            const menuButton = document.getElementById('mobile-role-menu');
            const manageButton = document.getElementById('mobile-role-manage');
            const availableBox = document.getElementById('mobile-role-available');
            const form = document.getElementById('role-permission-form');
            const placements = {};
            let manageMode = false;
            let draggedMenu = null;

            Object.keys(mobileMenuOptions).forEach((menu) => {
                placements[menu] = mobileLayout[menu]?.zones || ['sidebar'];
            });

            const cardMarkup = (menu, origin) => `<div class="mobile-role-function" draggable="${manageMode ? 'true' : 'false'}" data-menu="${$('<div>').text(menu).html()}" data-origin-zone="${origin}" title="${manageMode ? 'Drag to place this function' : ''}"><span>${$('<div>').text(mobileMenuOptions[menu]).html()}</span>${manageMode && origin !== 'available' ? `<button type="button" class="mobile-role-remove" data-remove-menu="${$('<div>').text(menu).html()}" data-remove-zone="${origin}" aria-label="Remove ${$('<div>').text(mobileMenuOptions[menu]).html()} from ${origin}">×</button>` : ''}</div>`;
            const renderMobilePreview = function() {
                zones.forEach((zone) => {
                    const container = document.getElementById(zoneIds[zone]);
                    container.innerHTML = '';
                    const items = zone === 'hidden'
                        ? Object.keys(mobileMenuOptions)
                        : Object.keys(mobileMenuOptions).filter((menu) => placements[menu].includes(zone));
                    items.sort((a, b) => (mobileLayout[a]?.positions?.[zone] ?? 0) - (mobileLayout[b]?.positions?.[zone] ?? 0));
                    items.forEach((menu) => container.insertAdjacentHTML('beforeend', cardMarkup(menu, zone === 'hidden' ? 'available' : zone)));
                    container.classList.toggle('manage-drop', manageMode);
                    if (manageMode && items.length === 0) {
                        container.insertAdjacentHTML('beforeend', `<span class="mobile-role-empty">Drop a function here</span>`);
                    }
                });
                document.getElementById('mobile-role-activity').hidden = !Object.keys(mobileMenuOptions).some((menu) => menu === 'attendance' && placements[menu].includes('home_quick'));
                document.getElementById('mobile-role-attendance-summary').hidden = !Object.keys(mobileMenuOptions).some((menu) => menu === 'attendance' && placements[menu].includes('home_quick'));
                manageButton.textContent = manageMode ? 'Show live preview' : 'Manage visible functions';
                manageButton.classList.toggle('btn-primary', manageMode);
                manageButton.classList.toggle('btn-outline-primary', !manageMode);
                mobileScreen.classList.toggle('manage-mode', manageMode);
                availableBox.hidden = !manageMode;
                form.querySelectorAll('input[name^="mobile_layout["]').forEach((input) => input.remove());
                Object.entries(placements).forEach(([menu, menuZones]) => {
                    const inputZones = menuZones.length ? menuZones : ['hidden'];
                    inputZones.forEach((zone) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `mobile_layout[${menu}][]`;
                        input.value = zone;
                        form.appendChild(input);
                    });
                });
                zones.filter((zone) => zone !== 'hidden').forEach((zone) => {
                    document.getElementById(zoneIds[zone]).querySelectorAll('[data-menu]').forEach((card) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `mobile_layout_order[${zone}][]`;
                        input.value = card.dataset.menu;
                        form.appendChild(input);
                    });
                });
            };
            const syncMobileMenuOptions = function() {
                const selectedPermissions = new Set(
                    Array.from(form.querySelectorAll('.module_checkbox:checked'))
                        .map((input) => input.dataset.permissionKey)
                        .filter(Boolean)
                );
                Object.entries(mobileMenuPermissionOptions).forEach(([menu, rule]) => {
                    const granted = selectedPermissions.has(rule.ability) ||
                        (!rule.explicit && rule.fallback_abilities.some((key) => selectedPermissions.has(key)));
                    if (granted && !Object.hasOwn(mobileMenuOptions, menu)) {
                        mobileMenuOptions[menu] = rule.name;
                        placements[menu] = mobileLayout[menu]?.zones || ['sidebar'];
                    } else if (!granted && Object.hasOwn(mobileMenuOptions, menu)) {
                        delete mobileMenuOptions[menu];
                        delete placements[menu];
                    }
                });
                renderMobilePreview();
            };
            form.addEventListener('change', (event) => {
                if (event.target.matches('.module_checkbox')) syncMobileMenuOptions();
            });
            manageButton.addEventListener('click', function() {
                manageMode = !manageMode;
                renderMobilePreview();
            });
            menuButton.addEventListener('click', function() {
                mobileScreen.classList.toggle('drawer-open');
            });
            mobileScreen.addEventListener('click', function(event) {
                if (mobileScreen.classList.contains('drawer-open') && event.target === mobileScreen) {
                    mobileScreen.classList.remove('drawer-open');
                }
            });
            $(document).on('click', '.mobile-role-side-item', function() {
                $('.mobile-role-side-item').removeClass('active');
                $(this).addClass('active');
                mobileScreen.classList.remove('drawer-open');
            });
            $(document).on('click', '.mobile-role-remove', function(event) {
                event.preventDefault();
                event.stopPropagation();
                const menu = $(this).data('remove-menu');
                const zone = $(this).data('remove-zone');
                placements[menu] = (placements[menu] || []).filter((item) => item !== zone);
                renderMobilePreview();
            });
            zones.forEach((zone) => {
                const container = document.getElementById(zoneIds[zone]);
                container.addEventListener('dragstart', function(event) {
                    const card = event.target.closest('[data-menu]');
                    if (!manageMode || !card) { event.preventDefault(); return; }
                    draggedMenu = card.dataset.menu;
                    card.classList.add('dragging');
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', draggedMenu);
                    event.dataTransfer.setData('application/x-mobile-zone', card.dataset.originZone || 'available');
                });
                container.addEventListener('dragend', function(event) {
                    event.target.closest('[data-menu]')?.classList.remove('dragging');
                });
                container.addEventListener('dragover', function(event) {
                    if (manageMode) event.preventDefault();
                });
                container.addEventListener('drop', function(event) {
                    if (!manageMode) return;
                    event.preventDefault();
                    const menu = event.dataTransfer.getData('text/plain') || draggedMenu;
                    if (!menu || !Object.hasOwn(mobileMenuOptions, menu)) return;
                    const sourceZone = event.dataTransfer.getData('application/x-mobile-zone');
                    if (zone === 'hidden') {
                        if (sourceZone && sourceZone !== 'available') {
                            placements[menu] = placements[menu].filter((item) => item !== sourceZone);
                        }
                    } else {
                        if (sourceZone && sourceZone !== 'available' && sourceZone !== zone) {
                            placements[menu] = placements[menu].filter((item) => item !== sourceZone);
                        }
                        if (!placements[menu].includes(zone)) placements[menu].push(zone);
                    }
                    const currentCards = document.getElementById(zoneIds[zone]).querySelectorAll('[data-menu]');
                    if (zone !== 'hidden') {
                        const nextPosition = Math.max(-1, ...Array.from(currentCards).map((card) => mobileLayout[card.dataset.menu]?.positions?.[zone] ?? -1)) + 1;
                        mobileLayout[menu] ??= {zones: [], positions: {}};
                        mobileLayout[menu].positions ??= {};
                        mobileLayout[menu].positions[zone] = nextPosition;
                    }
                    renderMobilePreview();
                });
            });
            syncMobileMenuOptions();

        });

    </script>
@endsection





