@extends('layouts.master')

@section('title',__('index.permission_setting'))

@section('action',__('index.assign'))

@section('button')
    <a href="{{route('admin.roles.index')}}" class="btn btn-primary btn-sm"> <i class="link-icon" data-feather="arrow-left"></i> @lang('index.back') </a>
@endsection

@section('styles')
    <style>
        /* Mobile Studio Preview Container */
        .mobile-role-preview{position:sticky;top:1rem;width:min(100%,400px);margin:0 auto;padding:12px;border:1px solid #cbd5e1;border-radius:40px;background:#0f172a;box-shadow:0 20px 50px rgba(15,23,42,0.22)}
        .mobile-role-screen{height:720px;overflow:hidden;position:relative;border-radius:30px;background:#f0f4f9;scrollbar-width:thin;transition:background 0.3s ease, color 0.3s ease}
        
        /* Realistic Sidebar Drawer */
        .mobile-role-sidebar {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 80%;
            z-index: 30;
            background: #ffffff;
            overflow-y: auto;
            box-shadow: 10px 0 35px rgba(15,23,42,0.3);
            transform: translateX(-100%);
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .mobile-role-screen.drawer-open .mobile-role-sidebar {
            transform: translateX(0);
        }
        .mobile-role-backdrop {
            display: none;
            position: absolute;
            inset: 0;
            background: rgba(15,23,42,0.55);
            z-index: 25;
            backdrop-filter: blur(2px);
            transition: opacity 0.28s ease;
        }
        .mobile-role-screen.drawer-open .mobile-role-backdrop {
            display: block;
        }
        .mobile-role-screen.theme-night .mobile-role-sidebar {
            background: #0f172a;
            color: #f8fafc;
            border-right: 1px solid #1e293b;
        }
        .mobile-role-screen.theme-night .mobile-role-sidebar .bg-white {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .mobile-role-screen.theme-night .mobile-role-sidebar .text-dark {
            color: #f8fafc !important;
        }
        
        /* Day Mode & Night Mode Styles */
        .mobile-role-screen.theme-day {
            background: #f1f5f9;
            color: #0f172a;
        }
        .mobile-role-screen.theme-night {
            background: #090d16;
            color: #f8fafc;
        }
        .mobile-role-screen.theme-night .mobile-app-header {
            background: #090d16;
            color: #f8fafc;
        }
        .mobile-role-screen.theme-night .day-card {
            background: #131c2e !important;
            border-color: #1e293b !important;
            color: #f8fafc !important;
        }
        .mobile-role-screen.theme-night .text-muted {
            color: #94a3b8 !important;
        }
        .mobile-role-screen.theme-night .mobile-role-bottom {
            background: #0f172a;
            border-top-color: #1e293b;
            color: #cbd5e1;
        }
        .mobile-role-screen.theme-night .bottom-nav-pill-active {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6) !important;
            color: #fff !important;
        }
        .mobile-role-screen.theme-night .attendance-orb-pulse {
            background: radial-gradient(circle, #2563eb 0%, #1d4ed8 70%, transparent 100%) !important;
        }
        
        /* Mobile App Header (Matches Day Mode screenshot) */
        .mobile-app-header {
            padding: 10px 14px 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f1f5f9;
        }
        .header-profile-box {
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .header-avatar-wrap {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #3b82f6;
            font-size: 20px;
        }
        .header-online-dot {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 10px;
            height: 10px;
            background: #22c55e;
            border: 2px solid #fff;
            border-radius: 50%;
        }
        .header-action-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #1e293b;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.12);
        }
        
        /* Welcome Angkor Banner */
        .angkor-banner-card {
            margin: 6px 12px 10px;
            padding: 14px 16px;
            border-radius: 18px;
            background: linear-gradient(135deg, #0a1e42 0%, #15397d 60%, #1e40af 100%);
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(10,30,66,0.25);
            position: relative;
            overflow: hidden;
        }
        .angkor-banner-card::after {
            content: "";
            position: absolute;
            right: -10px;
            bottom: -15px;
            width: 130px;
            height: 100px;
            background: radial-gradient(circle, rgba(251,191,36,0.2) 0%, transparent 70%);
            pointer-events: none;
        }
        
        /* Attendance Glowing Orb */
        .attendance-orb-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 12px 0 6px;
            position: relative;
        }
        .attendance-orb-outer {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: rgba(37,99,235,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed rgba(37,99,235,0.25);
            animation: pulse-ring 3s infinite;
        }
        .attendance-orb-inner {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #3b82f6, #1d4ed8 75%, #1e3a8a 100%);
            box-shadow: 0 10px 25px rgba(37,99,235,0.45), inset 0 2px 6px rgba(255,255,255,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 38px;
        }
        
        /* Floating Bottom Nav in Day Mode */
        .mobile-role-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 8px 10px 10px;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            border-radius: 24px 24px 0 0;
            box-shadow: 0 -4px 20px rgba(15,23,42,0.06);
            z-index: 10;
        }
        .bottom-nav-pill-active {
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
            color: #ffffff !important;
            border-radius: 20px;
            padding: 6px 14px;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(15,23,42,0.25);
        }
        
        /* Mobile Preview Zones */
        .mobile-role-zone{display:flex;gap:6px;align-items:center;flex-wrap:wrap;min-height:32px;padding:4px 8px}
        .mobile-role-zone[data-zone="home_quick"]{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;padding:0 12px 12px}
        .mobile-role-zone[data-zone="bottom"]{justify-content:space-around;flex-wrap:nowrap;border:none}
        .mobile-role-zone[data-zone="sidebar"]{display:grid;gap:6px;min-height:40px;padding:0}
        .mobile-role-zone[data-zone="top"]{justify-content:flex-end;gap:5px;padding:0}
        .mobile-role-zone.manage-drop{outline:2px dashed #0b5fff;border-radius:10px;background:#0b5fff08}
        .mobile-role-zone-label{display:none;color:#64748b;font-size:9px;font-weight:600}
        .mobile-role-screen.manage-mode .mobile-role-zone-label{display:block}
        
        .mobile-role-side-item{padding:8px 10px;border-radius:8px;color:#0f172a;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .mobile-role-side-item.active{background:#0b5fff1a;color:#0b5fff;font-weight:700}
        
        .mobile-role-body{height:100%;overflow-y:auto;overflow-x:hidden;padding-bottom:70px}
        .mobile-role-status{height:22px;padding:3px 16px;background:transparent;color:#0f172a;font-size:10px;display:flex;justify-content:space-between;align-items:center;font-weight:600}
        .mobile-role-screen.theme-night .mobile-role-status{color:#cbd5e1}
        .mobile-role-section{padding:0 12px 8px;color:#0f172a;font-size:12px;font-weight:700}
        
        .mobile-role-function{cursor:grab;position:relative;max-width:100%;padding:8px 24px 8px 10px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;color:#0f172a;font-size:11px;box-shadow:0 2px 6px #0f172a08;user-select:none;transition:all 0.15s ease}
        .mobile-role-screen.theme-night .mobile-role-function{background:#131c2e;border-color:#1e293b;color:#f8fafc}
        .mobile-role-remove{position:absolute;top:2px;right:3px;width:18px;height:18px;padding:0;border:0;border-radius:50%;background:#fee2e2;color:#b91c1c;font-size:13px;line-height:16px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center}
        .mobile-role-remove:hover{background:#fca5a5}
        .mobile-role-function:active{cursor:grabbing}
        .mobile-role-zone[data-zone="home_quick"] .mobile-role-function{min-height:64px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:8px 4px;font-size:10px}
        .mobile-role-zone[data-zone="bottom"] .mobile-role-function{border:0;box-shadow:none;background:transparent;padding:4px 2px;text-align:center;color:#64748b;font-size:10px}
        .mobile-role-zone[data-zone="sidebar"] .mobile-role-function{display:block;width:100%;border:1px solid #e2e8f0;background:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.03)}
        .mobile-role-zone[data-zone="top"] .mobile-role-function{border-radius:50%;width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;background:#1e293b;color:#fff;border:none}
        .mobile-role-function.dragging{opacity:.45}
        .mobile-role-empty{grid-column:1/-1;padding:12px 8px;text-align:center;color:#94a3b8;font-size:11px}
        
        .preview-tab-btn{cursor:pointer;border-radius:6px;padding:4px 10px;font-size:11px;font-weight:600;border:none;transition:all .2s}
        .preview-tab-btn.active{background:#ffffff;color:#0b5fff;box-shadow:0 2px 6px rgba(0,0,0,0.1)}
        .preview-tab-btn:not(.active){background:transparent;color:#ffffffcc}
        .preview-tab-btn:hover{color:#fff}
        
        /* Studio Zone Manager Cards (Left Column) */
        .studio-zone-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 18px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(15,23,42,0.04);
            transition: all 0.2s;
        }
        .studio-zone-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 14px rgba(15,23,42,0.07);
        }
        .studio-zone-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .studio-item-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
            margin: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
        }
        .studio-item-badge:hover {
            transform: translateY(-1px);
            border-color: #94a3b8;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
        }
        .studio-item-badge .btn-reorder-item {
            background: #e2e8f0;
            color: #475569;
            border: none;
            border-radius: 4px;
            width: 19px;
            height: 19px;
            line-height: 17px;
            font-size: 9px;
            cursor: pointer;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }
        .studio-item-badge .btn-reorder-item:hover {
            background: #cbd5e1;
            color: #0f172a;
            transform: scale(1.08);
        }
        .studio-item-badge .btn-remove-item {
            background: #fee2e2;
            color: #ef4444;
            border: none;
            border-radius: 50%;
            width: 19px;
            height: 19px;
            line-height: 17px;
            font-size: 13px;
            cursor: pointer;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 2px;
            transition: background 0.15s;
        }
        .studio-item-badge .btn-remove-item:hover {
            background: #fca5a5;
            color: #b91c1c;
        }
        .studio-empty-placeholder {
            width: 100%;
            padding: 18px;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            background: #f8fafc;
        }
        
        /* Modal Add Menu Styling */
        .modal-menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 12px;
            max-height: 480px;
            overflow-y: auto;
            padding: 4px;
        }
        .modal-menu-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            transition: all 0.2s;
        }
        .modal-menu-card:hover {
            border-color: #3b82f6;
            background: #f8faff;
            box-shadow: 0 3px 10px rgba(59,130,246,0.08);
        }
        .modal-menu-card.is-locked {
            background: #f8fafc;
            border-color: #e2e8f0;
            opacity: 0.85;
        }
        .modal-menu-card.is-locked:hover {
            border-color: #f59e0b;
            background: #fffbeb;
            opacity: 1;
        }
        
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

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header card-nav bg-white border-bottom">
                <ul class="nav nav-tabs d-md-flex d-block text-center border-0">
                    @foreach($allRoles as $key => $value)
                        <a class="nav-link my-md-0 my-1 d-inline-block border-0 {{$value->id == $role->id ? 'active': ''}}" href="{{route('admin.roles.permission',$value->id)}}">
                            <button class="btn btn-md btn-{{$value->id == $role->id ? 'primary':'outline-secondary'}} fw-semibold shadow-sm">{{ucfirst($value->name)}} </button>
                        </a>
                    @endforeach
                </ul>
            </div>
            
            <div class="card-body p-4">
                <!-- Studio Header Banner -->
                <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 16px; color: #fff;">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary px-3 py-1" style="font-size: 13px;">📱 Mobile Workplace & Layout Studio</span>
                                    <span class="badge bg-light text-dark fw-bold px-2 py-1" style="font-size: 12px;">តួនាទី: {{ ucfirst($role->name) }}</span>
                                </div>
                                <h4 class="text-white fw-bold mb-1">ស្ទូឌីយោរៀបចំ Layout និងម៉ឺនុយទូរស័ព្ទ</h4>
                                <p class="text-light mb-0" style="opacity: 0.85; font-size: 13px;">
                                    កំណត់ទីតាំងម៉ឺនុយ (Bottom Bar, Home Grid, More Page) តាមសិទ្ធិអនុញ្ញាតរបស់ Role និងបន្ថែម (+) ឬដកចេញ (-) បានភ្លាមៗ
                                </p>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <div class="bg-dark bg-opacity-50 border border-secondary border-opacity-25 rounded-3 px-3 py-2 text-center me-2">
                                    <small class="text-white-50 d-block" style="font-size: 11px;">សិទ្ធិអនុញ្ញាតប្រើប្រាស់</small>
                                    <span class="fw-bold text-success fs-6" id="studio-allowed-count">0 ម៉ឺនុយ</span>
                                </div>
                                <!-- 1-Click Role Presets -->
                                <div class="btn-group shadow-sm" role="group">
                                    <button type="button" class="btn btn-outline-light btn-sm studio-preset-btn" data-preset="manager" title="កំណត់ប្លង់សម្រាប់ Manager">
                                        👔 Manager Preset
                                    </button>
                                    <button type="button" class="btn btn-outline-light btn-sm studio-preset-btn" data-preset="technician" title="កំណត់ប្លង់សម្រាប់ Technician">
                                        🔧 Technician
                                    </button>
                                    <button type="button" class="btn btn-outline-light btn-sm studio-preset-btn" data-preset="staff" title="កំណត់ប្លង់សម្រាប់បុគ្គលិកទូទៅ">
                                        👤 Staff
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm studio-preset-btn" data-preset="default" title="ត្រឡប់ទៅលំនាំដើម">
                                        ↺ Reset
                                    </button>
                                </div>
                                <!-- Clone Layout from Another Role -->
                                <div class="dropdown d-inline-block shadow-sm">
                                    <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" id="dropdownCloneLayout" data-bs-toggle="dropdown" aria-expanded="false">
                                        📋 ចម្លងប្លង់ពី Role ផ្សេង
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="dropdownCloneLayout">
                                        @if(isset($allRolesLayouts) && count($allRolesLayouts) > 0)
                                            @foreach($allRolesLayouts as $otherRoleId => $otherRoleData)
                                                <li>
                                                    <a class="dropdown-item btn-clone-role py-2" href="javascript:void(0)" data-role-id="{{ $otherRoleId }}" data-role-name="{{ $otherRoleData['name'] }}">
                                                        ចម្លងពី: <strong class="text-primary">{{ $otherRoleData['name'] }}</strong>
                                                    </a>
                                                </li>
                                            @endforeach
                                        @else
                                            <li><span class="dropdown-item text-muted">គ្មាន Role ផ្សេងសម្រាប់ចម្លងទេ</span></li>
                                        @endif
                                    </ul>
                                </div>
                                <!-- Initial Landing Tab Selector -->
                                <div class="d-flex align-items-center gap-1 bg-dark bg-opacity-50 border border-secondary border-opacity-25 rounded-3 px-2 py-1 shadow-sm">
                                    <small class="text-white-50" style="font-size: 11px;">📌 ទំព័រដើមពេលបើក:</small>
                                    <select id="select-initial-tab" class="form-select form-select-sm py-0 border-0 bg-dark text-white fw-semibold" style="width: auto; font-size: 11px;">
                                        <option value="home">🏠 Home (ផ្ទាំងដើម)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2-Column Studio: Left Section Manager & Right Phone Preview -->
                <div class="row g-4 align-items-start">
                    
                    <!-- Left Column (col-lg-7): Visual Layout Section Manager -->
                    <div class="col-lg-7">
                        
                        <!-- Zone 1: Bottom Navigation Bar -->
                        <div class="studio-zone-card">
                            <div class="studio-zone-header">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                        <span class="me-1">📱</span> របារម៉ឺនុយខាងក្រោម (Bottom Navigation Bar)
                                        <span id="bottom-bar-count-badge" class="badge bg-light text-primary border ms-2" style="font-size:11px;">0 ម៉ឺនុយ</span>
                                    </h6>
                                    <small class="text-muted" style="font-size: 11px;">បង្ហាញនៅបាតក្រោមនៃអេក្រង់ទូរស័ព្ទ (បានណែនាំពី ៣ ទៅ ៥ ម៉ឺនុយ)</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary btn-open-add-modal" data-zone="bottom">
                                    <i class="link-icon" data-feather="plus"></i> + បន្ថែមម៉ឺនុយ (Add Tab)
                                </button>
                            </div>
                            <div class="studio-zone-items d-flex flex-wrap align-items-center" id="studio-items-bottom" data-zone="bottom">
                                <!-- Rendered dynamically by JS -->
                            </div>
                        </div>

                        <!-- Zone 2: Home Quick Services Grid -->
                        <div class="studio-zone-card">
                            <div class="studio-zone-header">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <span class="me-1">⚡</span> ម៉ឺនុយរហ័សលើផ្ទាំងដើម (Home Quick Services Grid)
                                    </h6>
                                    <small class="text-muted" style="font-size: 11px;">កាតសេវាកម្មរហ័សនៅលើទំព័រដើម (Quick Services)</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-success btn-open-add-modal" data-zone="home_quick">
                                    <i class="link-icon" data-feather="plus"></i> + បន្ថែមសេវាកម្ម (Add Shortcut)
                                </button>
                            </div>
                            <div class="studio-zone-items d-flex flex-wrap align-items-center" id="studio-items-home_quick" data-zone="home_quick">
                                <!-- Rendered dynamically by JS -->
                            </div>
                        </div>

                        <!-- Zone 3: More Page & Management -->
                        <div class="studio-zone-card">
                            <div class="studio-zone-header">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <span class="me-1">📄</span> ទំព័រផ្សេងៗ និងការគ្រប់គ្រង (More Page & Management)
                                    </h6>
                                    <small class="text-muted" style="font-size: 11px;">ម៉ឺនុយបង្ហាញក្នុងទំព័រ More និងតំបន់គ្រប់គ្រង Office Desk</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary btn-open-add-modal" data-zone="sidebar">
                                    <i class="link-icon" data-feather="plus"></i> + បន្ថែមម៉ឺនុយ (Add Menu)
                                </button>
                            </div>
                            <div class="studio-zone-items d-flex flex-wrap align-items-center" id="studio-items-sidebar" data-zone="sidebar">
                                <!-- Rendered dynamically by JS -->
                            </div>
                        </div>

                        <!-- Zone 4: Top Action Bar -->
                        <div class="studio-zone-card">
                            <div class="studio-zone-header">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <span class="me-1">🔔</span> របារមុខងារខាងលើ (Top Action Bar)
                                    </h6>
                                    <small class="text-muted" style="font-size: 11px;">ប៊ូតុងរហ័សនៅផ្នែកក្បាលទំព័រខាងលើ (Top Header Actions)</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-open-add-modal" data-zone="top">
                                    <i class="link-icon" data-feather="plus"></i> + បន្ថែមប៊ូតុង (Add Action)
                                </button>
                            </div>
                            <div class="studio-zone-items d-flex flex-wrap align-items-center" id="studio-items-top" data-zone="top">
                                <!-- Rendered dynamically by JS -->
                            </div>
                        </div>

                        <!-- Big Studio Save Button -->
                        <div class="mt-4">
                            <button type="button" class="btn btn-primary btn-lg w-100 shadow py-3 fw-bold fs-6" id="btn-save-studio">
                                💾 រក្សាទុកប្លង់ទូរស័ព្ទ និងសិទ្ធិទាំងអស់ (Save Layout & Permissions)
                            </button>
                            <p class="text-muted small text-center mt-2">
                                រាល់ការកែប្រែក្នុង Studio នឹងត្រូវបានរក្សាទុកទៅក្នុងប្រព័ន្ធ និងធ្វើសមកាលកម្មភ្លាមៗទៅកាន់ Mobile App។
                            </p>
                        </div>
                    </div>

                    <!-- Right Column (col-lg-5): Interactive Live Phone Preview -->
                    <div class="col-lg-5">
                        <div class="mobile-role-preview" aria-label="Live mobile permission preview">
                            
                            <!-- Phone Top Mode & View Switchers (3-Way: Home, Sidebar Drawer, More Page) -->
                            <div class="d-flex align-items-center justify-content-between p-2 mb-2 bg-dark rounded-3 border border-secondary border-opacity-25">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-sm btn-primary active preview-tab-btn py-1 px-2" id="preview-tab-home">🏠 ផ្ទាំងដើម</button>
                                    <button type="button" class="btn btn-sm btn-outline-light preview-tab-btn py-1 px-2" id="preview-tab-drawer">☰ Sidebar</button>
                                    <button type="button" class="btn btn-sm btn-outline-light preview-tab-btn py-1 px-2" id="preview-tab-more">📄 More (ផ្សេងៗ)</button>
                                </div>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-sm btn-light active theme-toggle-btn py-1 px-2 fw-semibold" id="btn-theme-day" data-theme="day" title="ម៉ូដពេលថ្ងៃ (Day Mode)">☀️ Day</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary theme-toggle-btn py-1 px-2" id="btn-theme-night" data-theme="night" title="ម៉ូដពេលយប់ (Night Mode)">🌙 Night</button>
                                </div>
                            </div>

                            <div class="mobile-role-screen theme-day" id="mobile-role-screen">
                                <!-- Realistic Mobile Sidebar Drawer (DashboardDrawer) -->
                                <aside class="mobile-role-sidebar" id="mobile-role-sidebar">
                                    <div class="d-flex align-items-center justify-content-between p-3 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fs-5">🏢</span>
                                            <div>
                                                <strong class="d-block text-dark fw-bold" style="font-size: 12px; line-height: 1.2;">Phnom Penh Branch</strong>
                                                <small class="text-muted" style="font-size: 9px;">Digital HR · Payment QR</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light border p-0 rounded-circle d-flex align-items-center justify-content-center" id="btn-close-sidebar" style="width: 24px; height: 24px; font-size: 13px;">×</button>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <small class="text-uppercase fw-bold text-primary" style="font-size: 10px; letter-spacing: 0.05em;">⭐ Services & Role Menus</small>
                                            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 8px;">Sidebar Zone</span>
                                        </div>
                                        <div class="mobile-role-zone" data-zone="sidebar" id="mobile-role-sidebar-list-drawer" style="display:grid;gap:5px;min-height:50px;"></div>

                                        <hr class="my-3 opacity-25">

                                        <small class="text-uppercase fw-bold text-muted d-block mb-2" style="font-size: 10px; letter-spacing: 0.05em;">🛠️ Quick Tools</small>
                                        <div class="d-grid gap-1" style="font-size: 11px;">
                                            <div class="p-2 rounded-3 bg-white border d-flex align-items-center gap-2"><span class="text-primary">🌐</span> <span>Social Feed</span></div>
                                            <div class="p-2 rounded-3 bg-white border d-flex align-items-center gap-2"><span class="text-success">🧮</span> <span>Loan Calculator</span></div>
                                            <div class="p-2 rounded-3 bg-white border d-flex align-items-center gap-2"><span class="text-info">🪪</span> <span>ID Card Preview</span></div>
                                            <div class="p-2 rounded-3 bg-white border d-flex align-items-center gap-2"><span class="text-secondary">🔗</span> <span>Company Links</span></div>
                                        </div>
                                    </div>
                                </aside>
                                <!-- Backdrop overlay for Drawer -->
                                <div class="mobile-role-backdrop" id="mobile-role-backdrop"></div>

                                <main class="mobile-role-body">
                                    <!-- Status Bar -->
                                    <div class="mobile-role-status">
                                        <span>07:50 <small class="opacity-75 ms-1">💬 📍</small></span>
                                        <div class="rounded-pill bg-dark bg-opacity-75" style="width: 50px; height: 10px;"></div>
                                        <span>●●● 5G 100% 🔋</span>
                                    </div>

                                    <!-- Home View -->
                                    <div id="preview-view-home">
                                        <!-- Real Header (Hamburger + Avatar + Name + Top Actions) -->
                                        <div class="mobile-app-header">
                                            <div class="header-profile-box">
                                                <!-- Hamburger Button to trigger Side Drawer -->
                                                <button type="button" class="btn btn-sm p-0 me-1 d-flex align-items-center justify-content-center shadow-xs border header-hamburger-btn" id="btn-hamburger" title="បើក Sidebar Drawer" style="width: 34px; height: 34px; border-radius: 50%; background: #ffffff; color: #1e293b; font-size: 15px;">
                                                    ☰
                                                </button>
                                                <div class="header-avatar-wrap">
                                                    <span>👤</span>
                                                    <span class="header-online-dot"></span>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 10px; line-height: 1;">Hello There 👋</small>
                                                    <strong class="d-block text-dark fw-bold" style="font-size: 13px; line-height: 1.2;">{{ auth()->user()->name ?? 'ឈឿន រាជ' }}</strong>
                                                    <small class="text-muted" style="font-size: 9px;">{{ auth()->user()->employee_id ?? 'KY0021' }} · {{ ucfirst($role->name) }}</small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <!-- Dynamic Top Actions Zone -->
                                                <div class="mobile-role-zone" data-zone="top" id="mobile-role-top"></div>
                                            </div>
                                        </div>

                                        <!-- Welcome Banner (Angkor Wat Theme with Carousel Dots) -->
                                        <div class="angkor-banner-card">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div>
                                                    <strong class="d-block" style="font-size: 14px; letter-spacing: 0.02em;">សូមស្វាគមន៍!</strong>
                                                    <small style="font-size: 11px; opacity: 0.85;">Good Morning!</small>
                                                </div>
                                                <span class="badge bg-white bg-opacity-20 text-white border-0" style="font-size: 9px;">☀️ ថ្ងៃនេះ</span>
                                            </div>
                                            <div class="d-flex align-items-baseline gap-1 my-1">
                                                <span class="fs-4 fw-bold font-monospace">07:50</span>
                                                <small class="opacity-75" style="font-size: 11px;">:48</small>
                                            </div>
                                            <small class="d-block opacity-75 mb-1" style="font-size: 10px;">📅 Tuesday, November 24, 2026</small>
                                            <p class="mb-2" style="font-size: 9.5px; opacity: 0.9; line-height: 1.35; border-top: 1px dashed rgba(255,255,255,0.25); padding-top: 6px;">
                                                "ការងារជាក្រុម នាំមកនូវលទ្ធផលល្អ សាមគ្គីភាពនាំឱ្យមានជ័យជំនះ"
                                            </p>
                                            <!-- Carousel Slideshow Dots -->
                                            <div class="d-flex align-items-center justify-content-center gap-1">
                                                <span class="rounded-pill bg-white" style="width: 14px; height: 3.5px;"></span>
                                                <span class="rounded-circle bg-white opacity-50" style="width: 4px; height: 4px;"></span>
                                                <span class="rounded-circle bg-white opacity-50" style="width: 4px; height: 4px;"></span>
                                                <span class="rounded-circle bg-white opacity-50" style="width: 4px; height: 4px;"></span>
                                                <span class="rounded-circle bg-white opacity-50" style="width: 4px; height: 4px;"></span>
                                            </div>
                                        </div>

                                        <!-- Summary Statistics Row (Late & Highlights) -->
                                        <div class="row g-2 px-3 mb-2">
                                            <div class="col-6">
                                                <div class="day-card p-2 bg-white rounded-3 border shadow-xs" style="font-size: 10px;">
                                                    <div class="d-flex justify-content-between text-muted mb-1">
                                                        <span>📈 <strong>Late</strong></span>
                                                        <small class="text-primary">Details →</small>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span class="text-muted">Total: <strong class="text-dark">0</strong></span>
                                                        <span class="text-muted">Reduc: <strong class="text-dark">$0.00</strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="day-card p-2 bg-white rounded-3 border shadow-xs" style="font-size: 10px;">
                                                    <div class="d-flex justify-content-between text-muted mb-1">
                                                        <span>✨ <strong>Highlights</strong></span>
                                                        <small class="text-primary">View →</small>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span class="text-success fw-bold">Bonus: $20.00</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Quick Services Section -->
                                        <div class="px-3 pt-1 pb-1 d-flex align-items-center justify-content-between">
                                            <span class="fw-bold text-dark" style="font-size: 12px;">⚡ សេវាកម្មរហ័ស (Quick Services)</span>
                                            <small class="text-muted" style="font-size: 10px;">រៀបចំតាម Role</small>
                                        </div>
                                        <div class="mobile-role-zone" data-zone="home_quick" id="mobile-role-preview-list" aria-live="polite"></div>

                                        <!-- Real Attendance Orb with QR Code and Floating Action Button & Pill -->
                                        <div class="attendance-orb-container position-relative" id="mobile-role-activity">
                                            <!-- Floating Out-of-Office Pill & Action Plus -->
                                            <div class="position-absolute end-0 top-0 me-3 mt-1 d-flex flex-column align-items-end gap-2" style="z-index: 5;">
                                                <div class="d-inline-flex align-items-center gap-1 px-2 py-1 bg-white border rounded-pill shadow-xs" style="font-size: 10px; color: #0284c7;">
                                                    <span>📍</span>
                                                    <span class="fw-bold">ចេញក្រៅ</span>
                                                </div>
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; font-size: 20px; cursor: pointer;">
                                                    +
                                                </div>
                                            </div>

                                            <div class="attendance-orb-outer">
                                                <div class="attendance-orb-inner shadow">
                                                    <span>📷</span>
                                                </div>
                                            </div>
                                            <strong class="mt-2 text-dark d-block" style="font-size: 13px;">Check In | Check Out</strong>
                                            <small class="text-muted" style="font-size: 10px;">Scan QR / NFC for Attendance</small>
                                        </div>
                                    </div>

                                    <!-- More Page View -->
                                    <div id="preview-view-more" style="display:none; padding:12px 14px;">
                                        <div class="day-card mb-3 p-3 bg-white rounded-3 border shadow-sm">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2" style="font-size: 18px;">👤</div>
                                                <div>
                                                    <strong class="d-block text-dark" style="font-size: 13px;">{{ auth()->user()->name ?? 'ឈឿន រាជ' }}</strong>
                                                    <small class="text-muted" style="font-size: 10px;">{{ ucfirst($role->name) }} Account · Settings</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mobile-role-section text-primary px-0 mb-2 d-flex align-items-center justify-content-between" style="font-size: 12px;">
                                            <span>⭐ ការគ្រប់គ្រង (Management / More Page)</span>
                                            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 9px;">Active Zone</span>
                                        </div>
                                        <div class="mobile-role-zone" data-zone="sidebar" id="mobile-role-sidebar-list" style="min-height:100px;background:#fff;border:1px dashed #cbd5e1;border-radius:14px;padding:8px;display:grid;gap:6px;"></div>
                                        
                                        <div class="mt-3 text-muted px-1" style="font-size: 10px;">
                                            <span>📂 Office Desk, Company Documents & Finance</span>
                                        </div>
                                    </div>

                                    <!-- Bottom Navigation Bar (Visible in both views) -->
                                    <div class="mobile-role-bottom">
                                        <div class="d-flex align-items-center justify-content-around">
                                            <!-- Fixed Home Tab (Active Pill) -->
                                            <div class="text-center" style="cursor: pointer;" id="preview-nav-home">
                                                <div class="bottom-nav-pill-active d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                                    <span>🏠</span>
                                                    <span>Home</span>
                                                </div>
                                            </div>
                                            <!-- Dynamic Placed Tabs -->
                                            <div class="mobile-role-zone d-flex align-items-center justify-content-around flex-grow-1 p-0" data-zone="bottom" id="mobile-role-bottom"></div>
                                            <!-- Fixed More Tab -->
                                            <div class="text-center px-1" style="color: #64748b; font-size: 10px; cursor: pointer;" id="preview-nav-more">
                                                <span class="d-block" style="font-size: 14px;">⋯</span>
                                                <span>More</span>
                                            </div>
                                        </div>
                                    </div>
                                </main>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="mobile-role-manage">
                                🔄 របៀបអូសទម្លាក់ (Toggle Drag & Drop)
                            </button>
                        </div>
                        <div class="mobile-role-available" id="mobile-role-available" hidden><h6>Available functions · drag into the phone</h6><div class="mobile-role-zone" data-zone="hidden" id="mobile-role-hidden"></div></div>
                    </div>
                </div>

                <!-- Detailed Permissions Matrix (Collapsible Card) -->
                <div class="card mt-4 border shadow-sm">
                    <div class="card-header bg-light d-flex align-items-center justify-content-between" id="headingPermissions" data-bs-toggle="collapse" data-bs-target="#collapsePermissions" aria-expanded="false" style="cursor: pointer;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="link-icon text-primary" data-feather="shield"></i>
                            <h5 class="mb-0 fw-bold">តារាងកំណត់សិទ្ធិលម្អិតតាមផ្នែក (Detailed Permissions Matrix)</h5>
                            <span class="badge bg-secondary ms-2">ចុចដើម្បីពង្រីក / បង្រួម</span>
                        </div>
                        <span class="text-muted"><i class="link-icon" data-feather="chevron-down"></i></span>
                    </div>
                    <div id="collapsePermissions" class="collapse">
                        <div class="card-body p-4">
                            <form class="forms-sample" id="role-permission-form" action="{{route('admin.role.assign-permissions',$role->id)}}" method="post">
                                @method('PUT')
                                @csrf
                                @include('admin.role.common.permission')
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Smart Add-Menu Picker Modal -->
        <div class="modal fade" id="modalAddMenu" tabindex="-1" aria-labelledby="modalAddMenuLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-light border-bottom">
                        <h5 class="modal-title fw-bold" id="modalAddMenuLabel">
                            ➕ បន្ថែមម៉ឺនុយទៅកាន់: <span id="modal-target-zone-name" class="text-primary"></span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3">
                        <div class="mb-3">
                            <input type="text" id="modal-menu-search" class="form-control" placeholder="🔍 វាយឈ្មោះម៉ឺនុយដើម្បីស្វែងរក (Search menu)...">
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary active modal-filter-btn" data-filter="all">ទាំងអស់ (All)</button>
                            <button type="button" class="btn btn-sm btn-outline-success modal-filter-btn" data-filter="allowed">🟢 បានអនុញ្ញាត (Allowed)</button>
                            <button type="button" class="btn btn-sm btn-outline-warning modal-filter-btn" data-filter="locked">🔒 ត្រូវការសិទ្ធិ (Needs Permission)</button>
                        </div>
                        <div class="modal-menu-grid" id="modal-menu-list">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បិទ (Close)</button>
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
                $('body').on('click', '.js-check-all', function() {
                    let isChecked = $(this).prop('checked');
                    const card = $(this).closest('.group-checkbox, .group-checkbox-card');
                    const checkboxes = card.find('.module_checkbox');
                    checkboxes.prop('checked', isChecked).trigger('change');
                });

                // Tab-level master check
                $('body').on('change', '.tab-master-check', function() {
                    const targetTabId = $(this).data('target-tab');
                    const isChecked = $(this).is(':checked');
                    const tabPane = $('#' + targetTabId);
                    tabPane.find('.module_checkbox').prop('checked', isChecked);
                    tabPane.find('.js-check-all').prop('checked', isChecked);
                    tabPane.find('.module_checkbox').trigger('change');
                });

                // Quick Search Filter for permissions
                $('#permissionSearchFilter').on('input', function() {
                    const query = $(this).val().toLowerCase().trim();
                    $('.permission-group-col').each(function() {
                        const groupName = ($(this).data('group-name') || '').toLowerCase();
                        const groupText = $(this).text().toLowerCase();
                        if (!query || groupName.includes(query) || groupText.includes(query)) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                });

                // 1-Click Role Presets Handler
                const showPresetFeedback = function(message) {
                    const fb = $('#presetFeedback');
                    fb.text(message).css('opacity', 1);
                    setTimeout(() => fb.css('opacity', 0), 3500);
                };

                const applyRolePreset = function(presetType) {
                    const allCheckboxes = $(form).find('.module_checkbox');
                    const allCheckAlls = $(form).find('.js-check-all');
                    
                    if (presetType === 'clear_all') {
                        allCheckboxes.prop('checked', false);
                        allCheckAlls.prop('checked', false);
                        $('.tab-master-check').prop('checked', false);
                        allCheckboxes.trigger('change');
                        showPresetFeedback('🧹 បានដោះសិទ្ធិទាំងអស់ចេញរួចរាល់ (All Cleared)');
                        return;
                    }

                    if (presetType === 'super_admin') {
                        allCheckboxes.prop('checked', true);
                        allCheckAlls.prop('checked', true);
                        $('.tab-master-check').prop('checked', true);
                        allCheckboxes.trigger('change');
                        showPresetFeedback('👑 បានកំណត់សិទ្ធិពេញលេញ Super Admin រួចរាល់');
                        return;
                    }

                    // Reset first
                    allCheckboxes.prop('checked', false);
                    allCheckAlls.prop('checked', false);
                    $('.tab-master-check').prop('checked', false);

                    if (presetType === 'hr_manager') {
                        allCheckboxes.filter('[data-permission-key="access_admin_panel"]').prop('checked', true);
                        const hrWebGroups = [
                            'company', 'branch', 'department', 'post', 'employee', 'attendance', 'leave',
                            'shift management', 'support', 'tada', 'dashboard', 'mobile notification',
                            'attendance method', 'payroll management', 'payroll setting', 'advance salary',
                            'employee salary', 'time leave', 'award management', 'tax report',
                            'event management', 'training management', 'leave approval', 'resignation management',
                            'termination management', 'warning', 'complaint', 'promotion', 'transfer',
                            'holiday', 'notice', 'team meeting', 'content management', 'daily records',
                            'store phone number', 'app links & accounts', 'company documents', 'payroll queue', 'repair price'
                        ];

                        allCheckboxes.each(function() {
                            const platform = $(this).data('platform');
                            const groupName = ($(this).data('group-name') || '').toLowerCase();
                            const permKey = $(this).data('permission-key') || '';

                            if (platform === 'web' && hrWebGroups.includes(groupName)) {
                                $(this).prop('checked', true);
                            }
                            if (platform === 'mobile') {
                                if (permKey.startsWith('mobile.menu.') || permKey.includes('list') || permKey.includes('view')) {
                                    $(this).prop('checked', true);
                                }
                            }
                        });
                        showPresetFeedback('🏢 បានកំណត់សិទ្ធិ HR Manager រួចរាល់');
                    } else if (presetType === 'branch_supervisor') {
                        allCheckboxes.filter('[data-permission-key="access_admin_panel"]').prop('checked', true);
                        const branchWebGroups = [
                            'attendance', 'leave', 'leave approval', 'shift management', 'daily records',
                            'team meeting', 'support', 'store phone number', 'dashboard', 'repair price'
                        ];
                        const branchMobileKeys = [
                            'mobile.menu.attendance', 'mobile.menu.leave-request', 'mobile.menu.leave-calendar',
                            'mobile.menu.time-leave', 'mobile.menu.team-sheet', 'mobile.menu.meeting',
                            'mobile.menu.meeting-attendance', 'mobile.menu.store-phone-numbers',
                            'mobile.menu.company-documents', 'repair_price.view', 'mobile.menu.support',
                            'mobile.menu.chat', 'mobile.menu.social-feed', 'mobile.menu.notice',
                            'mobile.menu.profile'
                        ];
                        allCheckboxes.each(function() {
                            const platform = $(this).data('platform');
                            const groupName = ($(this).data('group-name') || '').toLowerCase();
                            const permKey = $(this).data('permission-key') || '';

                            if (platform === 'web' && branchWebGroups.includes(groupName)) {
                                $(this).prop('checked', true);
                            }
                            if (platform === 'mobile' && (branchMobileKeys.includes(permKey) || permKey.includes('team_sheet') || permKey.includes('meeting'))) {
                                $(this).prop('checked', true);
                            }
                        });
                        showPresetFeedback('📍 បានកំណត់សិទ្ធិ Branch Supervisor រួចរាល់');
                    } else if (presetType === 'employee_mobile') {
                        // General Employee: Mobile only, Web all unchecked
                        const employeeMobileKeys = [
                            'mobile.menu.attendance', 'mobile.menu.leave-request', 'mobile.menu.time-leave',
                            'mobile.menu.advance-salary', 'mobile.menu.id-card', 'mobile.menu.profile',
                            'mobile.menu.company-rules', 'mobile.menu.holiday', 'mobile.menu.notice',
                            'mobile.menu.social-feed', 'mobile.menu.chat', 'mobile.menu.support',
                            'mobile.menu.links', 'mobile.menu.quotes', 'mobile.menu.dark-mode',
                            'mobile.menu.change-password', 'mobile.menu.store-phone-numbers',
                            'employee_check_in', 'employee_check_out', 'attendance_check_in', 'attendance_check_out'
                        ];
                        allCheckboxes.each(function() {
                            const platform = $(this).data('platform');
                            const permKey = $(this).data('permission-key') || '';
                            if (platform === 'mobile') {
                                if (employeeMobileKeys.includes(permKey)) {
                                    $(this).prop('checked', true);
                                }
                            }
                        });
                        showPresetFeedback('👤 បានកំណត់សិទ្ធិ General Employee (Mobile Only) រួចរាល់');
                    } else if (presetType === 'payroll_counter') {
                        allCheckboxes.filter('[data-permission-key="access_admin_panel"]').prop('checked', true);
                        const queueKeys = [
                            'view_payroll_queue', 'manage_payroll_queue', 'call_payroll_queue', 'disburse_payroll_queue',
                            'mobile.menu.payroll-queue', 'mobile.menu.attendance', 'mobile.menu.profile'
                        ];
                        allCheckboxes.each(function() {
                            const permKey = $(this).data('permission-key') || '';
                            const groupName = ($(this).data('group-name') || '').toLowerCase();
                            if (queueKeys.includes(permKey) || groupName === 'payroll queue') {
                                $(this).prop('checked', true);
                            }
                        });
                        showPresetFeedback('🎫 បានកំណត់សិទ្ធិ Payroll Queue Counter រួចរាល់');
                    }

                    // Update group checkAll states
                    $('.group-checkbox-card').each(function() {
                        const groupCheckboxes = $(this).find('.module_checkbox');
                        const total = groupCheckboxes.length;
                        const checked = groupCheckboxes.filter(':checked').length;
                        $(this).find('.js-check-all').prop('checked', total > 0 && total === checked);
                    });

                    allCheckboxes.trigger('change');
                };

                $(document).on('click', '.preset-btn', function() {
                    const preset = $(this).data('preset');
                    if (preset) {
                        applyRolePreset(preset);
                    }
                });
            });

            // Metadata & State
            const mobileMenuOptions = @json($mobileMenuOptions);
            const mobileMenuPermissionOptions = @json($mobileMenuPermissionOptions);
            const initialMobileLayout = @json($mobileLayout);
            const allRolesLayouts = @json($allRolesLayouts ?? []);
            let currentInitialTab = @json($initialTab ?? 'home');
            let mobileLayout = JSON.parse(JSON.stringify(initialMobileLayout));
            const zones = ['top', 'sidebar', 'bottom', 'home_quick', 'hidden'];
            const zoneIds = {top: 'mobile-role-top', sidebar: 'mobile-role-sidebar-list', bottom: 'mobile-role-bottom', home_quick: 'mobile-role-preview-list', hidden: 'mobile-role-hidden'};
            const studioZoneIds = {top: 'studio-items-top', sidebar: 'studio-items-sidebar', bottom: 'studio-items-bottom', home_quick: 'studio-items-home_quick'};
            const zoneDisplayNames = {
                'bottom': 'របារម៉ឺនុយបាតក្រោម (Bottom Navigation Bar)',
                'home_quick': 'ម៉ឺនុយរហ័សលើផ្ទាំងដើម (Home Quick Services Grid)',
                'sidebar': 'ទំព័រផ្សេងៗ និងការគ្រប់គ្រង (More Page & Management)',
                'top': 'របារមុខងារខាងលើ (Top Action Bar)'
            };
            const menuIcons = {
                'dashboard': '📊',
                'attendance': '🕒',
                'leave': '🏖️',
                'notice': '📢',
                'holiday': '🗓️',
                'support': '💬',
                'assets': '💼',
                'company-documents': '📁',
                'team-sheet': '👥',
                'repair': '🔧',
                'nfc': '📱',
                'qr': '📷',
                'project': '🚀',
                'task': '✅',
                'morescreen': '⋯'
            };

            const mobileScreen = document.getElementById('mobile-role-screen');
            const manageButton = document.getElementById('mobile-role-manage');
            const availableBox = document.getElementById('mobile-role-available');
            const form = document.getElementById('role-permission-form');
            const placements = {};
            const zoneOrders = { bottom: [], home_quick: [], sidebar: [], top: [] };
            let manageMode = false;
            let draggedMenu = null;
            let activeModalZone = 'bottom';

            Object.keys(mobileMenuOptions).forEach((menu) => {
                placements[menu] = mobileLayout[menu]?.zones || ['sidebar'];
            });

            // Initialize zoneOrders based on initial positions
            ['bottom', 'home_quick', 'sidebar', 'top'].forEach((zone) => {
                zoneOrders[zone] = Object.keys(mobileMenuOptions).filter((menu) => (placements[menu] || []).includes(zone));
                zoneOrders[zone].sort((a, b) => (mobileLayout[a]?.positions?.[zone] ?? 0) - (mobileLayout[b]?.positions?.[zone] ?? 0));
            });

            const syncPositionsFromZoneOrders = function() {
                ['bottom', 'home_quick', 'sidebar', 'top'].forEach((zone) => {
                    (zoneOrders[zone] || []).forEach((menu, pos) => {
                        mobileLayout[menu] = mobileLayout[menu] || { zones: [], positions: {} };
                        mobileLayout[menu].positions = mobileLayout[menu].positions || {};
                        mobileLayout[menu].positions[zone] = pos;
                    });
                });
            };

            const getMenuDisplayName = function(menu) {
                if (mobileMenuOptions[menu]) return mobileMenuOptions[menu];
                if (mobileMenuPermissionOptions[menu]) return mobileMenuPermissionOptions[menu].name;
                return menu.charAt(0).toUpperCase() + menu.slice(1).replace('-', ' ');
            };

            const updateAllowedCount = function() {
                const count = Object.keys(mobileMenuOptions).length;
                $('#studio-allowed-count').text(`${count} ម៉ឺនុយបានអនុញ្ញាត`);
            };

            // Render Section Badges in Studio (Left Column)
            const renderStudioZones = function() {
                ['bottom', 'home_quick', 'sidebar', 'top'].forEach((zone) => {
                    const container = document.getElementById(studioZoneIds[zone]);
                    if (!container) return;
                    container.innerHTML = '';
                    
                    // Filter zone items to only allowed and placed
                    zoneOrders[zone] = (zoneOrders[zone] || []).filter((menu) => Object.hasOwn(mobileMenuOptions, menu) && (placements[menu] || []).includes(zone));
                    // Check for any placed menu not yet in zoneOrders
                    Object.keys(mobileMenuOptions).forEach((menu) => {
                        if ((placements[menu] || []).includes(zone) && !zoneOrders[zone].includes(menu)) {
                            zoneOrders[zone].push(menu);
                        }
                    });

                    const items = zoneOrders[zone];

                    if (items.length === 0) {
                        container.innerHTML = `<div class="studio-empty-placeholder">មិនទាន់មានម៉ឺនុយក្នុងផ្នែកនេះនៅឡើយទេ សូមចុច [+ បន្ថែម]</div>`;
                    } else {
                        items.forEach((menu, index) => {
                            const icon = menuIcons[menu] || '📌';
                            const name = getMenuDisplayName(menu);
                            const badge = `
                                <div class="studio-item-badge" data-menu="${menu}" data-zone="${zone}">
                                    <span>${icon}</span>
                                    <span>${name}</span>
                                    <div class="d-inline-flex align-items-center gap-1 ms-1">
                                        <button type="button" class="btn-reorder-item btn-reorder-prev" data-menu="${menu}" data-zone="${zone}" title="រំកិលទៅមុខ/ឆ្វេង">◀</button>
                                        <button type="button" class="btn-reorder-item btn-reorder-next" data-menu="${menu}" data-zone="${zone}" title="រំកិលទៅក្រោយ/ស្តាំ">▶</button>
                                        <button type="button" class="btn-remove-item" data-remove-menu="${menu}" data-remove-zone="${zone}" title="ដកចេញ">×</button>
                                    </div>
                                </div>`;
                            container.insertAdjacentHTML('beforeend', badge);
                        });
                    }
                });

                // Update Bottom Bar guidance count
                const bottomCount = (zoneOrders['bottom'] || []).length;
                const bottomBadge = $('#bottom-bar-count-badge');
                if (bottomCount > 5) {
                    bottomBadge.attr('class', 'badge bg-warning text-dark border ms-2').html(`⚠️ ${bottomCount} ម៉ឺនុយ (ណែនាំ ៣ ទៅ ៥)`);
                } else {
                    bottomBadge.attr('class', 'badge bg-light text-primary border ms-2').text(`${bottomCount} ម៉ឺនុយ`);
                }
            };

            // Card Markup for Phone Mockup
            const cardMarkup = (menu, origin) => {
                const icon = menuIcons[menu] || '📌';
                const name = getMenuDisplayName(menu);
                return `<div class="mobile-role-function" draggable="${manageMode ? 'true' : 'false'}" data-menu="${$('<div>').text(menu).html()}" data-origin-zone="${origin}" title="${manageMode ? 'Drag to place this function' : ''}">
                    <span class="d-block mb-1" style="font-size:13px;">${icon}</span>
                    <span class="fw-semibold">${$('<div>').text(name).html()}</span>
                    ${manageMode && origin !== 'available' ? `<button type="button" class="mobile-role-remove" data-remove-menu="${$('<div>').text(menu).html()}" data-remove-zone="${origin}" aria-label="Remove">×</button>` : ''}
                </div>`;
            };

            // Render Phone Preview & Form Inputs
            const renderMobilePreview = function() {
                syncPositionsFromZoneOrders();

                zones.forEach((zone) => {
                    const container = document.getElementById(zoneIds[zone]);
                    if (!container) return;
                    container.innerHTML = '';
                    const items = zone === 'hidden'
                        ? Object.keys(mobileMenuOptions)
                        : (zoneOrders[zone] || Object.keys(mobileMenuOptions).filter((menu) => (placements[menu] || []).includes(zone)));
                    items.forEach((menu) => container.insertAdjacentHTML('beforeend', cardMarkup(menu, zone === 'hidden' ? 'available' : zone)));
                    container.classList.toggle('manage-drop', manageMode);
                    if (manageMode && items.length === 0) {
                        container.insertAdjacentHTML('beforeend', `<span class="mobile-role-empty">Drop a function here</span>`);
                    }
                    if (zone === 'sidebar') {
                        const drawer = document.getElementById('mobile-role-sidebar-list-drawer');
                        if (drawer) drawer.innerHTML = container.innerHTML;
                    }
                });

                document.getElementById('mobile-role-activity').hidden = !Object.keys(mobileMenuOptions).some((menu) => menu === 'attendance' && (placements[menu] || []).includes('home_quick'));
                document.getElementById('mobile-role-attendance-summary').hidden = !Object.keys(mobileMenuOptions).some((menu) => menu === 'attendance' && (placements[menu] || []).includes('home_quick'));

                manageButton.textContent = manageMode ? '✓ រួចរាល់ (Exit Drag Mode)' : '🔄 របៀបអូសទម្លាក់ (Toggle Drag & Drop)';
                manageButton.classList.toggle('btn-primary', manageMode);
                manageButton.classList.toggle('btn-outline-secondary', !manageMode);
                mobileScreen.classList.toggle('manage-mode', manageMode);
                availableBox.hidden = !manageMode;

                // Sync hidden inputs in form
                form.querySelectorAll('input[name^="mobile_layout["]').forEach((input) => input.remove());
                form.querySelectorAll('input[name^="mobile_layout_order["]').forEach((input) => input.remove());

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

                ['bottom', 'home_quick', 'sidebar', 'top'].forEach((zone) => {
                    (zoneOrders[zone] || []).forEach((menu) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `mobile_layout_order[${zone}][]`;
                        input.value = menu;
                        form.appendChild(input);
                    });
                });

                // Sync Initial Landing Tab dropdown & hidden input
                const initialSelect = $('#select-initial-tab');
                if (initialSelect.length) {
                    initialSelect.empty();
                    initialSelect.append('<option value="home">🏠 Home (ផ្ទាំងដើម)</option>');
                    (zoneOrders['bottom'] || []).forEach((m) => {
                        const name = getMenuDisplayName(m);
                        const icon = menuIcons[m] || '📌';
                        initialSelect.append(`<option value="${m}">${icon} ${name}</option>`);
                    });
                    initialSelect.val(currentInitialTab);
                    if (!initialSelect.val()) {
                        initialSelect.val('home');
                        currentInitialTab = 'home';
                    }
                }
                form.querySelectorAll('input[name="mobile_initial_tab"]').forEach((input) => input.remove());
                const initInput = document.createElement('input');
                initInput.type = 'hidden';
                initInput.name = 'mobile_initial_tab';
                initInput.value = currentInitialTab;
                form.appendChild(initInput);

                updateAllowedCount();
                renderStudioZones();
                const countBadge = document.getElementById('selectedPermissionsCount');
                if (countBadge) {
                    countBadge.textContent = form.querySelectorAll('.module_checkbox:checked').length;
                }
            };

            const syncMobileMenuOptions = function() {
                const selectedPermissions = new Set(
                    Array.from(form.querySelectorAll('.module_checkbox:checked'))
                        .map((input) => input.dataset.permissionKey)
                        .filter(Boolean)
                );
                Object.entries(mobileMenuPermissionOptions).forEach(([menu, rule]) => {
                    const granted = selectedPermissions.has(rule.ability) ||
                        (rule.fallback_abilities || []).some((key) => selectedPermissions.has(key));
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

            // Reorder Item Click Handlers
            $(document).on('click', '.btn-reorder-prev', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const menu = $(this).data('menu');
                const zone = $(this).data('zone');
                const list = zoneOrders[zone] || [];
                const idx = list.indexOf(menu);
                if (idx > 0) {
                    const temp = list[idx - 1];
                    list[idx - 1] = menu;
                    list[idx] = temp;
                    renderMobilePreview();

                }
            });

            $(document).on('click', '.btn-reorder-next', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const menu = $(this).data('menu');
                const zone = $(this).data('zone');
                const list = zoneOrders[zone] || [];
                const idx = list.indexOf(menu);
                if (idx >= 0 && idx < list.length - 1) {
                    const temp = list[idx + 1];
                    list[idx + 1] = menu;
                    list[idx] = temp;
                    renderMobilePreview();

                }
            });

            // Remove Button Click Handlers
            $(document).on('click', '.btn-remove-item, .mobile-role-remove', function(event) {
                event.preventDefault();
                event.stopPropagation();
                const menu = $(this).data('remove-menu');
                const zone = $(this).data('remove-zone');
                if (menu && zone && placements[menu]) {
                    placements[menu] = placements[menu].filter((item) => item !== zone);
                    if (zoneOrders[zone]) {
                        zoneOrders[zone] = zoneOrders[zone].filter((item) => item !== menu);
                    }
                    renderMobilePreview();

                }
            });

            // Modal: Open & Populate
            $('.btn-open-add-modal').on('click', function() {
                activeModalZone = $(this).data('zone');
                $('#modal-target-zone-name').text(zoneDisplayNames[activeModalZone] || activeModalZone);
                $('#modal-menu-search').val('');
                $('.modal-filter-btn').removeClass('active');
                $('.modal-filter-btn[data-filter="all"]').addClass('active');
                populateModalMenuList('all', '');
                $('#modalAddMenu').modal('show');
            });

            const populateModalMenuList = function(filter, search) {
                const container = $('#modal-menu-list');
                container.empty();
                search = (search || '').toLowerCase().trim();

                Object.keys(mobileMenuPermissionOptions).forEach((menu) => {
                    const info = mobileMenuPermissionOptions[menu];
                    const name = info.name || menu;
                    const icon = menuIcons[menu] || '📌';
                    const isAllowed = Object.hasOwn(mobileMenuOptions, menu);
                    const isPlacedInCurrentZone = (placements[menu] || []).includes(activeModalZone);

                    // Filters
                    if (filter === 'allowed' && !isAllowed) return;
                    if (filter === 'locked' && isAllowed) return;
                    if (search && !name.toLowerCase().includes(search) && !menu.toLowerCase().includes(search)) return;

                    let actionHtml = '';
                    if (isPlacedInCurrentZone) {
                        actionHtml = `<span class="badge bg-secondary py-2 px-2" style="font-size:11px;">✓ បានដាក់រួចហើយ</span>`;
                    } else if (isAllowed) {
                        actionHtml = `<button type="button" class="btn btn-sm btn-primary btn-modal-add" data-menu="${menu}">+ ជ្រើសរើស (Add)</button>`;
                    } else {
                        actionHtml = `<button type="button" class="btn btn-sm btn-outline-warning btn-modal-grant-add" data-menu="${menu}" data-ability="${info.ability}">🔑 បើកសិទ្ធិ & បន្ថែម</button>`;
                    }

                    const card = `
                        <div class="modal-menu-card ${isAllowed ? '' : 'is-locked'}" data-menu="${menu}">
                            <div class="d-flex align-items-center gap-2">
                                <span style="font-size: 20px;">${icon}</span>
                                <div>
                                    <strong class="d-block text-dark" style="font-size: 13px;">${name}</strong>
                                    <small class="${isAllowed ? 'text-success' : 'text-warning'} fw-semibold" style="font-size: 11px;">
                                        ${isAllowed ? '🟢 បានអនុញ្ញាត (Allowed)' : '🔒 ត្រូវការសិទ្ធិ (Needs Permission)'}
                                    </small>
                                </div>
                            </div>
                            <div>
                                ${actionHtml}
                            </div>
                        </div>`;
                    container.append(card);
                });

                if (container.children().length === 0) {
                    container.html('<div class="col-12 text-center text-muted py-4">រកមិនឃើញម៉ឺនុយត្រូវនឹងការស្វែងរកនេះទេ</div>');
                }
            };

            // Modal Search & Filter
            $('#modal-menu-search').on('input', function() {
                const search = $(this).val();
                const filter = $('.modal-filter-btn.active').data('filter') || 'all';
                populateModalMenuList(filter, search);
            });

            $('.modal-filter-btn').on('click', function() {
                $('.modal-filter-btn').removeClass('active');
                $(this).addClass('active');
                const filter = $(this).data('filter');
                const search = $('#modal-menu-search').val();
                populateModalMenuList(filter, search);
            });

            // Modal: Add Menu Action
            $(document).on('click', '.btn-modal-add', function() {
                const menu = $(this).data('menu');
                if (!menu || !Object.hasOwn(mobileMenuOptions, menu)) return;
                placements[menu] = placements[menu] || [];
                if (!placements[menu].includes(activeModalZone)) {
                    placements[menu].push(activeModalZone);
                }
                if (!zoneOrders[activeModalZone].includes(menu)) {
                    zoneOrders[activeModalZone].push(menu);
                }
                if (activeModalZone === 'sidebar') {
                    $('#preview-tab-more').trigger('click');
                } else if (activeModalZone === 'home_quick') {
                    $('#preview-tab-home').trigger('click');
                }
                renderMobilePreview();

                $('#modalAddMenu').modal('hide');
            });

            // Modal: Grant Permission & Add Action
            $(document).on('click', '.btn-modal-grant-add', function() {
                const menu = $(this).data('menu');
                const ability = $(this).data('ability');
                const info = mobileMenuPermissionOptions[menu];
                if (!info) return;

                let checkedAny = false;
                const abilitiesToTry = [ability, ...(info.fallback_abilities || [])];
                for (let key of abilitiesToTry) {
                    const escapedKey = key.replace(/\./g, '\\.');
                    const checkbox = $(`#role-permission-form #${escapedKey}, #role-permission-form [data-permission-key="${key}"]`);
                    if (checkbox.length) {
                        checkbox.prop('checked', true);
                        checkedAny = true;
                        break;
                    }
                }

                if (checkedAny) {
                    syncMobileMenuOptions();
                    placements[menu] = placements[menu] || [];
                    if (!placements[menu].includes(activeModalZone)) {
                        placements[menu].push(activeModalZone);
                    }
                    if (!zoneOrders[activeModalZone].includes(menu)) {
                        zoneOrders[activeModalZone].push(menu);
                    }
                    if (activeModalZone === 'sidebar') {
                        $('#preview-tab-more').trigger('click');
                    } else if (activeModalZone === 'home_quick') {
                        $('#preview-tab-home').trigger('click');
                    }
                    renderMobilePreview();

                    $('#modalAddMenu').modal('hide');
                } else {
                    alert('សូមបើកផ្ទាំងសិទ្ធិលម្អិត (Detailed Permissions Matrix) ដើម្បីផ្តល់សិទ្ធិដោយផ្ទាល់');
                }
            });

            // Presets Implementation
            $('.studio-preset-btn').on('click', function() {
                const preset = $(this).data('preset');
                if (preset === 'manager') {
                    const managerTarget = {
                        'dashboard': ['bottom'],
                        'assets': ['bottom', 'sidebar'],
                        'company-documents': ['bottom', 'sidebar'],
                        'team-sheet': ['bottom', 'sidebar'],
                        'morescreen': ['bottom'],
                        'attendance': ['home_quick'],
                        'leave': ['home_quick', 'sidebar'],
                        'notice': ['home_quick', 'top'],
                        'repair': ['sidebar'],
                        'support': ['home_quick', 'sidebar'],
                        'nfc': ['sidebar'],
                        'qr': ['sidebar']
                    };
                    Object.keys(placements).forEach((k) => placements[k] = []);
                    Object.entries(managerTarget).forEach(([k, z]) => {
                        if (Object.hasOwn(mobileMenuOptions, k)) placements[k] = z;
                    });
                } else if (preset === 'technician') {
                    const techTarget = {
                        'dashboard': ['bottom'],
                        'repair': ['bottom', 'home_quick', 'sidebar'],
                        'morescreen': ['bottom'],
                        'attendance': ['home_quick'],
                        'support': ['home_quick'],
                        'assets': ['sidebar'],
                        'notice': ['sidebar'],
                        'leave': ['sidebar'],
                        'holiday': ['sidebar']
                    };
                    Object.keys(placements).forEach((k) => placements[k] = []);
                    Object.entries(techTarget).forEach(([k, z]) => {
                        if (Object.hasOwn(mobileMenuOptions, k)) placements[k] = z;
                    });
                } else if (preset === 'staff') {
                    const staffTarget = {
                        'dashboard': ['bottom'],
                        'attendance': ['bottom', 'home_quick'],
                        'leave': ['bottom', 'home_quick'],
                        'morescreen': ['bottom'],
                        'holiday': ['home_quick', 'sidebar'],
                        'notice': ['home_quick', 'sidebar', 'top'],
                        'company-documents': ['sidebar'],
                        'support': ['sidebar']
                    };
                    Object.keys(placements).forEach((k) => placements[k] = []);
                    Object.entries(staffTarget).forEach(([k, z]) => {
                        if (Object.hasOwn(mobileMenuOptions, k)) placements[k] = z;
                    });
                } else if (preset === 'default') {
                    mobileLayout = JSON.parse(JSON.stringify(initialMobileLayout));
                    Object.keys(mobileMenuOptions).forEach((menu) => {
                        placements[menu] = mobileLayout[menu]?.zones || ['sidebar'];
                    });
                }

                // Rebuild zoneOrders based on the selected preset
                ['bottom', 'home_quick', 'sidebar', 'top'].forEach((z) => {
                    zoneOrders[z] = Object.keys(mobileMenuOptions).filter((m) => (placements[m] || []).includes(z));
                });
                renderMobilePreview();

            });

            // Clone Layout from Another Role
            $(document).on('click', '.btn-clone-role', function(e) {
                e.preventDefault();
                const roleId = $(this).data('role-id');
                const roleName = $(this).data('role-name');
                const roleData = allRolesLayouts[roleId];
                if (!roleData || !roleData.layout) return;
                if (!confirm(`តើអ្នកពិតជាចង់ចម្លងប្លង់ពី Role "${roleName}" មកជំនួសប្លង់បច្ចុប្បន្នមែនទេ?`)) return;

                const targetLayout = roleData.layout;
                Object.keys(placements).forEach((k) => placements[k] = []);
                Object.entries(targetLayout).forEach(([menu, config]) => {
                    if (Object.hasOwn(mobileMenuOptions, menu)) {
                        placements[menu] = [...(config.zones || [])];
                    }
                });
                ['bottom', 'home_quick', 'sidebar', 'top'].forEach((z) => {
                    zoneOrders[z] = Object.keys(mobileMenuOptions).filter((m) => (placements[m] || []).includes(z));
                    zoneOrders[z].sort((a, b) => (targetLayout[a]?.positions?.[z] ?? 0) - (targetLayout[b]?.positions?.[z] ?? 0));
                });
                syncPositionsFromZoneOrders();
                renderMobilePreview();

            });

            // Save Studio Button submits form
            $('#btn-save-studio').on('click', function(e) {
                e.preventDefault();
                $('#role-permission-form').submit();
            });

            // View Switcher Buttons (Home, Sidebar Drawer, More Page)
            $('#preview-tab-home').on('click', function() {
                $('.preview-tab-btn').removeClass('active btn-primary').addClass('btn-outline-light');
                $(this).addClass('active btn-primary').removeClass('btn-outline-light');
                $('#mobile-role-screen').removeClass('drawer-open');
                $('#preview-view-home').show();
                $('#preview-view-more').hide();
            });
            $('#preview-tab-drawer').on('click', function() {
                $('.preview-tab-btn').removeClass('active btn-primary').addClass('btn-outline-light');
                $(this).addClass('active btn-primary').removeClass('btn-outline-light');
                $('#mobile-role-screen').addClass('drawer-open');
            });
            $('#preview-tab-more').on('click', function() {
                $('.preview-tab-btn').removeClass('active btn-primary').addClass('btn-outline-light');
                $(this).addClass('active btn-primary').removeClass('btn-outline-light');
                $('#mobile-role-screen').removeClass('drawer-open');
                $('#preview-view-home').hide();
                $('#preview-view-more').show();
            });

            // Hamburger & Drawer Slide-in Controls
            $('#btn-hamburger').on('click', function(e) {
                e.preventDefault();
                $('#mobile-role-screen').toggleClass('drawer-open');
                if ($('#mobile-role-screen').hasClass('drawer-open')) {
                    $('.preview-tab-btn').removeClass('active btn-primary').addClass('btn-outline-light');
                    $('#preview-tab-drawer').addClass('active btn-primary').removeClass('btn-outline-light');
                }
            });
            $('#btn-close-sidebar, #mobile-role-backdrop').on('click', function() {
                $('#mobile-role-screen').removeClass('drawer-open');
                $('.preview-tab-btn').removeClass('active btn-primary').addClass('btn-outline-light');
                if ($('#preview-view-more').is(':visible')) {
                    $('#preview-tab-more').addClass('active btn-primary').removeClass('btn-outline-light');
                } else {
                    $('#preview-tab-home').addClass('active btn-primary').removeClass('btn-outline-light');
                }
            });

            // Bottom Nav Simulation Clicks
            $('#preview-nav-home').on('click', function() {
                $('#preview-tab-home').trigger('click');
            });
            $('#preview-nav-more').on('click', function() {
                $('#preview-tab-more').trigger('click');
            });

            // Theme Mode Switcher Buttons (Day Mode vs Night Mode)
            $('#btn-theme-day').on('click', function() {
                $('.theme-toggle-btn').removeClass('active btn-light btn-dark').addClass('btn-outline-secondary');
                $(this).addClass('active btn-light fw-bold').removeClass('btn-outline-secondary');
                $('#mobile-role-screen').removeClass('theme-night').addClass('theme-day');
            });
            $('#btn-theme-night').on('click', function() {
                $('.theme-toggle-btn').removeClass('active btn-light btn-dark fw-bold').addClass('btn-outline-secondary');
                $(this).addClass('active btn-dark fw-bold').removeClass('btn-outline-secondary');
                $('#mobile-role-screen').removeClass('theme-day').addClass('theme-night');
            });

            // Initial Landing Tab Selector Change
            $('#select-initial-tab').on('change', function() {
                currentInitialTab = $(this).val();
                form.querySelectorAll('input[name="mobile_initial_tab"]').forEach((input) => input.remove());
                const initInput = document.createElement('input');
                initInput.type = 'hidden';
                initInput.name = 'mobile_initial_tab';
                initInput.value = currentInitialTab;
                form.appendChild(initInput);
            });

            // Drag and drop events for phone mockup
            zones.forEach((zone) => {
                const container = document.getElementById(zoneIds[zone]);
                if (!container) return;
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
                            if (zoneOrders[sourceZone]) {
                                zoneOrders[sourceZone] = zoneOrders[sourceZone].filter((item) => item !== menu);
                            }
                        }
                    } else {
                        if (sourceZone && sourceZone !== 'available' && sourceZone !== zone) {
                            placements[menu] = placements[menu].filter((item) => item !== sourceZone);
                            if (zoneOrders[sourceZone]) {
                                zoneOrders[sourceZone] = zoneOrders[sourceZone].filter((item) => item !== menu);
                            }
                        }
                        if (!placements[menu].includes(zone)) placements[menu].push(zone);
                        if (zoneOrders[zone] && !zoneOrders[zone].includes(menu)) zoneOrders[zone].push(menu);
                    }
                    renderMobilePreview();

                });
            });

            // Initial render
            syncMobileMenuOptions();

        });
    </script>
@endsection
