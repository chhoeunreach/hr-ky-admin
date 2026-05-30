@extends('layouts.master')

@section('title', __('index.attendance'))

@section('action', __('index.employee_attendance_lists'))


@section('main-content')

    <section class="content">
        <style>
            .page-content {
                padding-top: 0.4rem !important;
            }

            .content {
                margin-top: 0 !important;
            }

            .attendance-page-loader {
                position: fixed;
                inset: 0;
                z-index: 2050;
                display: none;
                align-items: center;
                justify-content: center;
                background: rgba(248, 250, 252, 0.82);
                backdrop-filter: blur(8px);
            }

            .attendance-page-loader.is-visible {
                display: flex;
            }

            .attendance-loader-panel {
                min-width: 220px;
                padding: 22px 24px;
                border-radius: 8px;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                box-shadow: 0 22px 60px rgba(15, 23, 42, 0.18);
                text-align: center;
            }

            .attendance-loader-spinner {
                width: 34px;
                height: 34px;
                margin: 0 auto 12px;
                border-radius: 50%;
                border: 3px solid #dbeafe;
                border-top-color: #2563eb;
                animation: attendanceLoaderSpin 0.8s linear infinite;
            }

            .attendance-loader-title {
                color: #0f172a;
                font-weight: 700;
                line-height: 1.2;
            }

            .attendance-loader-text {
                margin-top: 5px;
                color: #64748b;
                font-size: 0.86rem;
            }

            @keyframes attendanceLoaderSpin {
                to {
                    transform: rotate(360deg);
                }
            }

            .attendance-chat-modal .modal-dialog {
                max-width: 760px;
            }

            .attendance-chat-modal .modal-content {
                border: 0;
                border-radius: 26px;
                overflow: hidden;
                box-shadow: 0 28px 70px rgba(15, 23, 42, 0.24);
            }

            .attendance-chat-shell {
                background:
                    radial-gradient(circle at top left, rgba(96, 165, 250, 0.14), transparent 28%),
                    radial-gradient(circle at top right, rgba(168, 85, 247, 0.10), transparent 24%),
                    linear-gradient(180deg, #f8fbff 0%, #ffffff 32%);
            }

            .attendance-chat-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                padding: 22px 24px 20px;
                background: rgba(255, 255, 255, 0.92);
                backdrop-filter: blur(16px);
                border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            }

            .attendance-chat-person {
                display: flex;
                align-items: center;
                gap: 14px;
                min-width: 0;
            }

            .attendance-chat-avatar-wrap {
                position: relative;
                flex-shrink: 0;
            }

            .attendance-chat-avatar {
                width: 56px;
                height: 56px;
                border-radius: 50%;
                object-fit: cover;
                border: 3px solid #ffffff;
                box-shadow: 0 10px 26px rgba(59, 130, 246, 0.16);
            }

            .attendance-chat-status {
                position: absolute;
                right: 2px;
                bottom: 2px;
                width: 14px;
                height: 14px;
                border-radius: 50%;
                background: #cbd5e1;
                border: 2px solid #ffffff;
            }

            .attendance-chat-status.online {
                background: #22c55e;
            }

            .attendance-chat-person h5 {
                margin: 0;
                color: #111827;
                font-size: 1.35rem;
                font-weight: 700;
                letter-spacing: -0.02em;
            }

            .attendance-chat-person p {
                margin: 2px 0 0;
                color: #64748b;
                font-size: 0.94rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .attendance-chat-actions {
                display: flex;
                align-items: center;
                gap: 10px;
                color: #a855f7;
                font-size: 1.1rem;
            }

            .attendance-chat-actions button,
            .attendance-chat-actions span {
                width: 42px;
                height: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: rgba(250, 245, 255, 0.92);
                border: 1px solid rgba(233, 213, 255, 0.75);
                color: #a855f7;
                box-shadow: 0 12px 22px rgba(168, 85, 247, 0.10);
                transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
            }

            .attendance-chat-actions button:hover,
            .attendance-chat-actions span:hover {
                transform: translateY(-1px);
                background: #ffffff;
                box-shadow: 0 16px 28px rgba(168, 85, 247, 0.16);
            }

            .attendance-chat-body {
                padding: 0;
                background:
                    linear-gradient(180deg, rgba(255, 255, 255, 0.92) 0%, rgba(248, 251, 255, 0.96) 100%),
                    linear-gradient(90deg, rgba(59, 130, 246, 0.03) 0, rgba(59, 130, 246, 0.03) 1px, transparent 1px, transparent 32px),
                    linear-gradient(rgba(148, 163, 184, 0.03) 0, rgba(148, 163, 184, 0.03) 1px, transparent 1px, transparent 32px);
                background-size: auto, 32px 32px, 32px 32px;
            }

            .attendance-chat-thread {
                height: 56vh;
                min-height: 420px;
                max-height: 680px;
                overflow-y: auto;
                padding: 26px 26px 18px;
                display: flex;
                flex-direction: column;
                gap: 14px;
            }

            .attendance-chat-thread .chat-bubble-row {
                display: flex;
                width: 100%;
                margin-bottom: 0;
            }

            .attendance-chat-thread .chat-bubble-row.outgoing {
                justify-content: flex-end;
            }

            .attendance-chat-thread .chat-bubble {
                display: inline-block;
                width: fit-content;
                max-width: min(72%, 460px);
                min-width: 0;
                padding: 14px 16px 12px;
                border-radius: 24px 24px 24px 10px;
                background: rgba(255, 255, 255, 0.96);
                border: 1px solid rgba(226, 232, 240, 0.95);
                box-shadow: 0 18px 34px rgba(15, 23, 42, 0.07);
                overflow-wrap: anywhere;
                word-break: break-word;
                color: #0f172a;
            }

            .attendance-chat-thread .chat-bubble.outgoing {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                border-color: rgba(37, 99, 235, 0.85);
                color: #ffffff;
                border-radius: 24px 24px 10px 24px;
                box-shadow: 0 20px 34px rgba(37, 99, 235, 0.22);
            }

            .attendance-chat-thread .chat-bubble-meta {
                margin-top: 8px;
                font-size: 0.74rem;
                font-weight: 500;
                color: #64748b;
            }

            .attendance-chat-thread .chat-bubble.outgoing .chat-bubble-meta {
                color: rgba(255, 255, 255, 0.84);
            }

            .attendance-chat-thread .chat-bubble-image {
                border-radius: 20px;
            }

            .attendance-chat-thread .chat-bubble > div,
            .attendance-chat-thread .chat-bubble > a,
            .attendance-chat-thread .chat-bubble > audio,
            .attendance-chat-thread .chat-bubble > img {
                max-width: 100%;
            }

            .attendance-chat-thread .chat-empty {
                margin: auto;
                max-width: 320px;
                padding: 22px 20px;
                text-align: center;
                border-radius: 22px;
                background: rgba(255, 255, 255, 0.88);
                border: 1px solid rgba(226, 232, 240, 0.95);
                color: #64748b;
                box-shadow: 0 18px 34px rgba(15, 23, 42, 0.06);
            }

            .attendance-chat-footer {
                border-top: 1px solid #edf1f7;
                background: rgba(255, 255, 255, 0.94);
                padding: 16px 18px 18px;
                backdrop-filter: blur(16px);
            }

            .attendance-chat-preview {
                display: none;
                position: relative;
                width: 142px;
                height: 142px;
                margin-bottom: 14px;
                border-radius: 28px;
                background: #f4f7fb;
                box-shadow: inset 0 0 0 1px #e5edf7;
                overflow: hidden;
            }

            .attendance-chat-preview.is-visible {
                display: block;
            }

            .attendance-chat-preview img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            .attendance-chat-preview-remove {
                position: absolute;
                top: 8px;
                right: 8px;
                width: 38px;
                height: 38px;
                border-radius: 50%;
                border: 0;
                background: rgba(255, 255, 255, 0.96);
                color: #111827;
                box-shadow: 0 10px 25px rgba(15, 23, 42, 0.14);
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .attendance-chat-form {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .attendance-chat-attach {
                width: 44px;
                height: 44px;
                border-radius: 50%;
                background: #eff6ff;
                color: #2563eb;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                flex-shrink: 0;
                margin-bottom: 0;
            }

            .attendance-chat-attach input {
                display: none;
            }

            .attendance-chat-input {
                flex: 1;
                border: 1px solid rgba(226, 232, 240, 0.95);
                background: linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
                border-radius: 999px;
                min-height: 48px;
                padding: 0 18px;
                color: #0f172a;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
            }

            .attendance-chat-input:focus {
                outline: none;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.14);
                background: #ffffff;
            }

            .attendance-chat-send {
                border: 0;
                min-width: 104px;
                height: 46px;
                border-radius: 999px;
                background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
                color: #ffffff;
                font-weight: 600;
                box-shadow: 0 14px 24px rgba(37, 99, 235, 0.25);
            }

            .attendance-chat-status-text {
                margin-top: 12px;
                color: #64748b;
                font-size: 0.88rem;
                padding-left: 6px;
            }

            .attendance-filter-card .card-header,
            .attendance-day-card .card-header {
                padding: 0.72rem 1.25rem;
                border-bottom: 1px solid #edf2f7;
            }

            .attendance-day-card {
                position: relative;
                overflow: hidden;
            }

            .attendance-day-card .card-body {
                position: relative;
            }

            .attendance-results-reload {
                position: absolute;
                inset: 0;
                z-index: 20;
                display: none;
                padding: 24px;
                background: rgba(248, 250, 252, 0.72);
                backdrop-filter: blur(3px);
            }

            .attendance-day-card.is-refreshing .attendance-results-reload {
                display: block;
            }

            .attendance-reload-panel {
                position: sticky;
                top: calc(50vh - 90px);
                width: min(360px, 92%);
                margin: 80px auto;
                padding: 18px 20px;
                border: 1px solid #dbe5f3;
                border-radius: 8px;
                background: rgba(255, 255, 255, 0.96);
                box-shadow: 0 18px 44px rgba(15, 23, 42, 0.16);
            }

            .attendance-reload-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                margin-bottom: 12px;
                color: #172033;
                font-weight: 800;
            }

            .attendance-reload-percent {
                color: #2563eb;
                font-variant-numeric: tabular-nums;
            }

            .attendance-reload-track {
                height: 9px;
                overflow: hidden;
                border-radius: 999px;
                background: #e8eef7;
            }

            .attendance-reload-bar {
                width: 0%;
                height: 100%;
                border-radius: inherit;
                background: linear-gradient(90deg, #38bdf8 0%, #2563eb 100%);
                box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
                transition: width 0.2s ease;
            }

            .attendance-reload-text {
                margin-top: 9px;
                color: #64748b;
                font-size: 0.84rem;
            }

            .attendance-filter-card .card-title,
            .attendance-day-card .card-title {
                font-size: 0.92rem;
                font-weight: 700;
                letter-spacing: 0.01em;
                line-height: 1.1;
            }

            .attendance-filter-form {
                padding: 0.9rem 1.25rem 1rem;
            }

            .attendance-filter-grid {
                display: grid;
                grid-template-columns: minmax(180px, 240px) minmax(180px, 220px) minmax(220px, 1fr) auto;
                gap: 16px;
                align-items: end;
            }

            .attendance-filter-field {
                margin-bottom: 0;
            }

            .attendance-filter-field .form-control,
            .attendance-filter-field .form-select,
            .attendance-filter-actions .btn {
                min-height: 40px;
                border-radius: 14px;
            }

            .attendance-filter-field .form-control,
            .attendance-filter-field .form-select {
                border-color: #d9e2ef;
                box-shadow: none;
            }

            .attendance-filter-field .form-control:focus,
            .attendance-filter-field .form-select:focus {
                border-color: #93c5fd;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
            }

            .attendance-filter-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                justify-content: flex-end;
            }

            .attendance-filter-actions .btn {
                min-width: 108px;
                padding-inline: 16px;
                margin: 0;
            }

            .attendance-table-toolbar {
                display: grid;
                grid-template-columns: auto 1fr auto;
                align-items: center;
                gap: 12px;
                margin-bottom: 8px;
            }

            .attendance-toolbar-left {
                display: flex;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
            }

            .attendance-entry-control {
                display: flex;
                align-items: center;
                gap: 10px;
                color: #111827;
                font-weight: 500;
            }

            .attendance-entry-select {
                min-width: 104px;
                min-height: 38px;
                padding-top: 0.45rem;
                padding-bottom: 0.45rem;
                border-radius: 14px;
            }

            .attendance-toolbar-actions {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }

            .attendance-toolbar-actions .btn,
            .attendance-filter-card .card-header .btn {
                min-height: 38px;
                padding: 0.5rem 0.9rem;
                border-radius: 14px;
            }

            .attendance-table-search-wrap {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 8px;
            }

            .attendance-table-reset {
                min-height: 38px;
                border-radius: 14px;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                white-space: nowrap;
            }

            .attendance-table-search {
                width: min(100%, 250px);
                border: 1px solid #d7dfeb;
                border-radius: 14px;
                min-height: 38px;
                padding: 0 12px;
                color: #111827;
                background: #f8fbff;
                box-shadow: none;
            }

            .attendance-table-search:focus {
                outline: none;
                border-color: #93c5fd;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
            }

            .attendance-username-cell {
                position: relative;
            }

            .attendance-username-wrap {
                position: relative;
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                gap: 4px;
            }

            .attendance-username-summary {
                display: none;
                position: absolute;
                top: calc(100% + 6px);
                left: 50%;
                transform: translateX(-50%);
                min-width: 220px;
                padding: 10px;
                border: 1px solid rgba(226, 232, 240, 0.95);
                border-radius: 8px;
                background: #ffffff;
                color: #111827;
                font-size: 0.76rem;
                line-height: 1.35;
                text-align: left;
                box-shadow: 0 16px 36px rgba(15, 23, 42, 0.18);
                z-index: 8;
            }

            .attendance-summary-popover-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 7px 8px;
                border-radius: 6px;
                font-weight: 600;
            }

            .attendance-summary-popover-row.is-clickable {
                width: 100%;
                border: 0;
                color: inherit;
                font: inherit;
                text-decoration: none;
                cursor: pointer;
                text-align: left;
                transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease;
            }

            .attendance-summary-popover-row.is-clickable:hover,
            .attendance-summary-popover-row.is-clickable:focus {
                transform: translateY(-1px);
                box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
                filter: brightness(0.99);
                outline: none;
            }

            .attendance-summary-popover-row.is-empty {
                opacity: 0.62;
                cursor: default;
            }

            .attendance-leave-detail-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .attendance-leave-detail-card {
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 14px;
                background: #ffffff;
            }

            .attendance-leave-detail-head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 10px;
            }

            .attendance-leave-detail-title {
                margin: 0;
                color: #0f172a;
                font-size: 0.98rem;
                font-weight: 700;
            }

            .attendance-leave-detail-date {
                margin: 3px 0 0;
                color: #64748b;
                font-size: 0.84rem;
            }

            .attendance-leave-detail-status {
                flex-shrink: 0;
                border-radius: 999px;
                padding: 4px 10px;
                background: #eff6ff;
                color: #1d4ed8;
                font-size: 0.75rem;
                font-weight: 700;
            }

            .attendance-leave-detail-meta {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 10px;
            }

            .attendance-leave-detail-meta div {
                min-width: 0;
            }

            .attendance-leave-detail-meta span {
                display: block;
                color: #64748b;
                font-size: 0.74rem;
                font-weight: 700;
                text-transform: uppercase;
            }

            .attendance-leave-detail-meta p {
                margin: 3px 0 0;
                color: #111827;
                font-size: 0.86rem;
                overflow-wrap: anywhere;
            }

            .attendance-leave-detail-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 14px;
            }

            .attendance-leave-detail-empty {
                margin: 0;
                padding: 22px 16px;
                border: 1px dashed #cbd5e1;
                border-radius: 8px;
                color: #64748b;
                text-align: center;
            }

            @media (max-width: 575.98px) {
                .attendance-leave-detail-meta {
                    grid-template-columns: 1fr;
                }
            }

            .attendance-summary-popover-row + .attendance-summary-popover-row {
                margin-top: 6px;
            }

            .attendance-summary-popover-row span:first-child {
                color: #334155;
            }

            .attendance-summary-popover-row strong {
                min-width: 30px;
                padding: 3px 8px;
                border-radius: 999px;
                text-align: center;
                font-size: 0.78rem;
            }

            .attendance-summary-popover-row.is-day-off {
                background: #f0fdf4;
            }

            .attendance-summary-popover-row.is-day-off strong {
                background: #bbf7d0;
                color: #166534;
            }

            .attendance-summary-popover-row.is-leave {
                background: #eff6ff;
            }

            .attendance-summary-popover-row.is-leave strong {
                background: #bfdbfe;
                color: #1d4ed8;
            }

            .attendance-summary-popover-row.is-time-leave {
                background: #f0fdfa;
            }

            .attendance-summary-popover-row.is-time-leave strong {
                background: #99f6e4;
                color: #0f766e;
            }

            .attendance-summary-popover-row.is-pending {
                background: #fff7ed;
            }

            .attendance-summary-popover-row.is-pending strong {
                background: #fed7aa;
                color: #c2410c;
            }

            .attendance-day-row:hover .attendance-username-summary {
                display: block;
            }

            .attendance-status-stack {
                position: relative;
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                gap: 0;
                min-width: 128px;
            }

            .attendance-status-actions {
                min-width: 0;
            }

            .attendance-status-stack .quickApproveLeaveTrigger,
            .attendance-status-stack .quickApproveTimeLeaveTrigger {
                position: absolute;
                left: 50%;
                z-index: 7;
                opacity: 0;
                visibility: hidden;
                white-space: nowrap;
                box-shadow: 0 8px 18px rgba(15, 23, 42, 0.16);
                transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease, box-shadow 0.18s ease;
            }

            .attendance-status-stack .quickApproveLeaveTrigger {
                bottom: 0;
                transform: translate(-50%, 118%) scale(0.98);
            }

            .attendance-status-stack .quickApproveTimeLeaveTrigger {
                top: 0;
                transform: translate(-50%, -118%) scale(0.98);
            }

            .attendance-day-row:hover .attendance-status-stack .quickApproveLeaveTrigger,
            .attendance-day-row:hover .attendance-status-stack .quickApproveTimeLeaveTrigger,
            .attendance-status-stack .quickApproveLeaveTrigger:focus,
            .attendance-status-stack .quickApproveTimeLeaveTrigger:focus {
                opacity: 1;
                visibility: visible;
            }

            .attendance-day-row:hover .attendance-status-stack .quickApproveLeaveTrigger,
            .attendance-status-stack .quickApproveLeaveTrigger:focus {
                transform: translate(-50%, 118%) scale(1);
            }

            .attendance-day-row:hover .attendance-status-stack .quickApproveTimeLeaveTrigger,
            .attendance-status-stack .quickApproveTimeLeaveTrigger:focus {
                transform: translate(-50%, -118%) scale(1);
            }

            .attendance-profile-wrap {
                position: relative;
                min-height: 42px;
            }

            .attendance-time-in-cell {
                position: relative;
            }

            .attendance-time-in-wrap {
                position: relative;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 30px;
                min-width: 72px;
            }

            .attendance-multi-time-stack {
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                gap: 6px;
                min-width: 76px;
            }

            .attendance-multi-time-stack .checkLocation,
            .attendance-multi-time-empty {
                min-width: 68px;
            }

            .attendance-multi-time-empty {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 26px;
                color: #94a3b8;
                font-weight: 700;
            }

            .attendance-status-pill {
                min-width: 23px;
                height: 20px;
                padding: 0 6px;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #ffffff;
                font-size: 9px;
                font-weight: 900;
                line-height: 1;
                box-shadow: 0 2px 6px rgba(15, 23, 42, 0.16);
            }

            .attendance-status-pill.is-late {
                background: #f97316;
            }

            .attendance-status-pill.is-early {
                background: #0ea5e9;
            }

            .attendance-status-pill.is-danger {
                background: #dc2626;
            }

            .attendance-time-cell,
            .attendance-multi-time-item {
                position: relative;
                overflow: visible;
            }

            .attendance-multi-time-item {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .attendance-overlay-badge {
                position: absolute;
                top: -7px;
                right: -9px;
                z-index: 8;
                pointer-events: auto;
            }

            .attendance-overlay-badge .attendance-status-pill {
                min-width: 21px;
                height: 19px;
                padding: 0 5px;
                border: 2px solid #ffffff;
                box-shadow: 0 3px 8px rgba(15, 23, 42, 0.22);
            }

            .attendance-profile-chat-badge {
                position: static;
                z-index: 7;
                display: inline-flex;
                align-items: center;
                gap: 5px;
                white-space: nowrap;
                margin-left: 0.35rem;
                box-shadow: 0 8px 18px rgba(15, 23, 42, 0.14);
                transform: none;
                transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
            }

            .attendance-profile-chat-badge .attendance-chat-unread-count {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 18px;
                height: 18px;
                padding: 0 5px;
                border-radius: 999px;
                background: #ef232a;
                color: #ffffff;
                font-size: 10px;
                font-weight: 800;
                line-height: 1;
            }

            .attendance-profile-chat-badge .attendance-chat-unread-count.is-empty {
                display: none;
            }

            .attendance-leave-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 4px;
                max-width: 100%;
                min-height: 28px;
                padding: 5px 7px;
                border-radius: 999px;
                border: 1px solid transparent;
                font-weight: 700;
                font-size: 0.74rem;
                line-height: 1.2;
                white-space: nowrap;
                box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08);
            }

            .attendance-leave-pill-label {
                max-width: 130px;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .attendance-leave-pill-status {
                padding: 2px 5px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.72);
                font-size: 0.67rem;
                text-transform: uppercase;
            }

            .attendance-leave-pill.is-day-off {
                border-color: #86efac;
                background: #dcfce7;
                color: #166534;
            }

            .attendance-leave-pill.is-leave,
            .attendance-leave-pill.is-approved {
                border-color: #93c5fd;
                background: #dbeafe;
                color: #1d4ed8;
            }

            .attendance-leave-pill.is-pending {
                border-color: #fdba74;
                background: #ffedd5;
                color: #c2410c;
            }

            .attendance-leave-pill.is-rejected,
            .attendance-leave-pill.is-cancelled {
                border-color: #fecaca;
                background: #fee2e2;
                color: #b91c1c;
            }

            .attendance-leave-pill.is-time-leave {
                border-color: #a5b4fc;
                background: #eef2ff;
                color: #4338ca;
            }

            .attendance-leave-pill.is-time-leave.is-pending {
                border-color: #fdba74;
                background: #ffedd5;
                color: #c2410c;
            }

            .attendance-leave-content {
                position: relative;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                max-width: 100%;
            }

            .attendance-status-stack.has-attendance-status .attendance-leave-content {
                margin-top: 6px;
            }

            .attendance-leave-content .attendanceLeaveRequestUpdate,
            .attendance-leave-content .attendanceTimeLeaveRequestUpdate {
                min-width: 0;
                max-width: 100%;
            }

            .attendance-leave-content .attendance-leave-pill {
                max-width: 100%;
            }

            .attendance-leave-content .attendance-leave-pill-label {
                max-width: 74px;
            }

            .attendance-summary-footer {
                display: grid;
                grid-template-columns: repeat(10, minmax(0, 1fr));
                gap: 8px;
                margin-top: 14px;
                padding: 10px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                background: #f8fafc;
            }

            .attendance-summary-item {
                position: relative;
                display: flex;
                align-items: center;
                gap: 8px;
                min-height: 66px;
                padding: 9px 8px 9px 10px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                background: #ffffff;
                text-align: left;
                cursor: pointer;
                overflow: hidden;
                transition: border-color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
            }

            .attendance-summary-item::before {
                content: "";
                position: absolute;
                inset: 0 auto 0 0;
                width: 4px;
                background: var(--summary-accent, #2563eb);
                opacity: 0.85;
            }

            .attendance-summary-item:hover {
                border-color: color-mix(in srgb, var(--summary-accent, #2563eb) 32%, #cbd5e1);
                background: #ffffff;
                box-shadow: 0 12px 24px rgba(15, 23, 42, 0.10);
                transform: translateY(-1px);
            }

            .attendance-summary-item.is-active {
                border-color: color-mix(in srgb, var(--summary-accent, #2563eb) 45%, #cbd5e1);
                background: color-mix(in srgb, var(--summary-accent, #2563eb) 9%, #ffffff);
                box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12);
            }

            .attendance-summary-icon {
                width: 34px;
                height: 34px;
                border-radius: 8px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 34px;
                background: color-mix(in srgb, var(--summary-accent, #2563eb) 12%, #ffffff);
                color: var(--summary-accent, #2563eb);
            }

            .attendance-summary-icon i,
            .attendance-summary-icon svg {
                width: 17px;
                height: 17px;
                stroke-width: 2.2;
            }

            .attendance-summary-copy {
                min-width: 0;
            }

            .attendance-summary-item strong {
                display: block;
                color: #0f172a;
                font-size: 1.12rem;
                line-height: 1.1;
                font-weight: 800;
                font-variant-numeric: tabular-nums;
            }

            .attendance-summary-item span {
                display: block;
                margin-top: 5px;
                color: #64748b;
                font-size: 0.64rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0;
                line-height: 1.2;
            }

            .attendance-summary-item.is-total {
                --summary-accent: #2563eb;
            }

            .attendance-summary-item.is-check-in,
            .attendance-summary-item.is-check-out {
                --summary-accent: #16a34a;
            }

            .attendance-summary-item.is-missing {
                --summary-accent: #dc2626;
            }

            .attendance-summary-item.is-day-off {
                --summary-accent: #0d9488;
            }

            .attendance-summary-item.is-leave {
                --summary-accent: #7c3aed;
            }

            .attendance-summary-item.is-time-leave {
                --summary-accent: #0891b2;
            }

            .attendance-summary-item.is-request {
                --summary-accent: #ea580c;
            }

            @media (max-width: 1399.98px) {
                .attendance-summary-footer {
                    grid-template-columns: repeat(5, minmax(0, 1fr));
                }
            }

            @media (max-width: 991.98px) {
                .attendance-summary-footer {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
            }

            .attendance-scroll-shortcuts {
                position: fixed;
                right: 22px;
                bottom: 24px;
                display: flex;
                flex-direction: column;
                gap: 10px;
                z-index: 1040;
            }

            .attendance-scroll-shortcut {
                width: 44px;
                height: 44px;
                border: 1px solid #d6e1f0;
                border-radius: 14px;
                background: rgba(255, 255, 255, 0.94);
                color: #3b4d73;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
            }

            .attendance-scroll-shortcut:hover {
                transform: translateY(-1px);
                border-color: #9db4da;
                box-shadow: 0 14px 28px rgba(15, 23, 42, 0.16);
            }

            .attendance-scroll-shortcut.is-hidden {
                display: none;
            }

            @media (max-width: 767.98px) {
                .attendance-chat-modal .modal-dialog {
                    max-width: 100%;
                    margin: 0;
                }

                .attendance-chat-modal .modal-content {
                    min-height: 100vh;
                    border-radius: 0;
                }

                .attendance-chat-thread .chat-bubble {
                    max-width: 88%;
                }

                .attendance-chat-actions {
                    display: none;
                }

                .attendance-filter-form {
                    padding: 1rem;
                }

                .attendance-filter-grid {
                    grid-template-columns: 1fr;
                }

                .attendance-filter-actions {
                    justify-content: stretch;
                }

                .attendance-filter-actions .btn {
                    width: 100%;
                }

                .attendance-table-toolbar {
                    grid-template-columns: 1fr;
                    align-items: stretch;
                }

                .attendance-entry-control {
                    width: 100%;
                    justify-content: space-between;
                }

                .attendance-entry-select {
                    min-width: 0;
                    flex: 1;
                }

                .attendance-toolbar-actions,
                .attendance-table-search-wrap {
                    justify-content: flex-start;
                }

                .attendance-table-search {
                    width: 100%;
                }

                .attendance-table-reset {
                    flex: 0 0 auto;
                }

                .attendance-status-stack .quickApproveLeaveTrigger,
                .attendance-status-stack .quickApproveTimeLeaveTrigger {
                    position: static;
                    opacity: 1;
                    visibility: visible;
                    transform: none;
                    margin-top: 0.35rem;
                }

                .attendance-profile-chat-badge {
                    position: static;
                    opacity: 1;
                    visibility: visible;
                    transform: none;
                    margin-top: 0.35rem;
                }

                .attendance-username-summary {
                    position: static;
                    display: block;
                    transform: none;
                    margin-top: 6px;
                }

                .attendance-summary-footer {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .attendance-scroll-shortcuts {
                    right: 14px;
                    bottom: 14px;
                }
            }
        </style>
        <?php
        if($isBsEnabled){
            $currentDate = \App\Helpers\AppHelper::getCurrentDateInBS();

        }else{
            $currentDate = \App\Helpers\AppHelper::getCurrentDateInYmdFormat();
        }

        $hasAttendanceFilters = filled($filterParameter['branch_id'] ?? null)
            || filled($filterParameter['department_id'] ?? null)
            || (($filterParameter['attendance_date'] ?? null) !== $currentDate);
        ?>

        @include('admin.section.flash_message')

        <div class="attendance-page-loader" id="attendancePageLoader" aria-hidden="true">
            <div class="attendance-loader-panel" role="status" aria-live="polite">
                <div class="attendance-loader-spinner"></div>
                <div class="attendance-loader-title">Loading attendance</div>
                <div class="attendance-loader-text" id="attendancePageLoaderText">Please wait...</div>
            </div>
        </div>

        @include('admin.attendance.common.breadcrumb')
        <div class="card mb-4 attendance-filter-card">
            <div class="card-header d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#attendanceFilterCollapse"
                            aria-expanded="{{ $hasAttendanceFilters ? 'true' : 'false' }}"
                            aria-controls="attendanceFilterCollapse">
                        <i class="link-icon" data-feather="filter"></i>
                        {{ __('index.filter') }}
                    </button>
                    <h6 class="card-title mb-0">{{ __('index.attendance_filter') }}</h6>
                </div>
            </div>
            <div id="attendanceFilterCollapse" class="collapse{{ $hasAttendanceFilters ? ' show' : '' }}">
            <form class="forms-sample attendance-filter-form" action="{{ route('admin.attendances.index') }}" method="get">

                <div class="attendance-filter-grid">

                    <div class="attendance-filter-field">
                        <input id="attendance_date"
                               name="attendance_date"
                               value="{{ $filterParameter['attendance_date'] }}"
                               @if($isBsEnabled)
                                   class="form-control dayAttendance"
                               type="text"
                               placeholder="{{ __('index.date_placeholder_bs') }}"
                               @else
                                   class="form-control"
                               type="date"
                            @endif
                        />
                    </div>
                    @if(!isset(auth()->user()->branch_id))
                    <div class="attendance-filter-field">
                        <select class="form-select form-select-lg" name="branch_id" id="branch_id">
                            <option value="" {{ !isset($filterParameter['branch_id']) ? 'selected' : '' }}>{{ __('index.select_branch') }}</option>
                            @foreach($branch as $key =>  $value)
                                <option value="{{ $value->id }}" {{ (isset($filterParameter['branch_id']) && $value->id == $filterParameter['branch_id']) ? 'selected' : '' }}>
                                    {{ ucfirst($value->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @endif
                    <div class="attendance-filter-field">
                        <select class="form-select " name="department_id" id="department_id">
                            <option selected disabled>{{ __('index.select_department') }}</option>
                        </select>
                    </div>
                    <div class="attendance-filter-actions">
                            <button type="submit" class="btn btn-success">{{ __('index.filter') }}</button>
                            <a class="btn btn-primary me-0" href="{{ route('admin.attendances.index') }}">{{ __('index.reset') }}</a>
                    </div>

                </div>
            </form>
            </div>
        </div>

        @php
            $groupedAttendance = $attendanceDetail->groupBy('user_id');
            $isDayOffType = static function ($type) {
                $typeName = strtolower(trim((string) $type));
                return str_contains($typeName, 'day off');
            };
            $hasCheckIn = static function ($attendanceRows) {
                return $attendanceRows->contains(fn ($row) => !empty($row->check_in_at) || !empty($row->night_checkin));
            };
            $hasCheckOut = static function ($attendanceRows) {
                return $attendanceRows->contains(fn ($row) => !empty($row->check_out_at) || !empty($row->night_checkout));
            };
            $statusBadge = static function (string $code, string $label, string $class, ?string $title = null): string {
                $title = $title ?: $label;

                return '<span class="attendance-status-pill '.$class.'" title="'.e($title).'">'.e($code).'</span>';
            };
            $attendanceBadgeMeta = static function ($attendance): array {
                $checkIn = $attendance->check_in_at ?: $attendance->night_checkin;
                $checkOut = $attendance->check_out_at ?: $attendance->night_checkout;
                $shiftOpening = $attendance->attendance_office_opening_time ?: $attendance->office_opening_time;
                $shiftClosing = $attendance->attendance_office_closing_time ?: $attendance->office_closing_time;
                $checkoutBefore = $attendance->checkout_before;
                $manualLateGraceMinutes = 16;
                $earlyCheckoutEnabled = (int) ($attendance->is_early_check_out ?? 0) === 1;
                $attendanceRejected = $attendance->attendance_status !== null
                    && $attendance->attendance_status != \App\Models\Attendance::ATTENDANCE_APPROVED;
                $attendanceLate = false;
                $attendanceEarlyCheckout = false;
                $allowedCheckInLabel = null;
                $allowedCheckOutLabel = null;

                if ($checkIn && $shiftOpening) {
                    $allowedCheckIn = \Carbon\Carbon::parse($shiftOpening)->addMinutes($manualLateGraceMinutes);
                    $allowedCheckInLabel = $allowedCheckIn->format('H:i');
                    $attendanceLate = \Carbon\Carbon::parse($checkIn)->gt($allowedCheckIn);
                }

                if ($checkOut && $shiftClosing && $earlyCheckoutEnabled) {
                    $allowedCheckOut = \Carbon\Carbon::parse($shiftClosing);
                    if ($checkoutBefore !== null) {
                        $allowedCheckOut = $allowedCheckOut->subMinutes((int) $checkoutBefore);
                    }
                    $allowedCheckOutLabel = $allowedCheckOut->format('H:i');
                    $attendanceEarlyCheckout = \Carbon\Carbon::parse($checkOut)->lt($allowedCheckOut);
                }

                $attendanceStatusTitle = trim(
                    ($checkIn ? 'In '.$checkIn : '').
                    ($checkOut ? ' Out '.$checkOut : '')
                );

                return [
                    'late' => $attendanceLate && !$attendanceRejected,
                    'early' => $attendanceEarlyCheckout && !$attendanceRejected,
                    'no_checkout' => $checkIn && !$checkOut,
                    'late_title' => trim(($attendanceStatusTitle ?: 'Late') . ($allowedCheckInLabel ? ' | Allowed ' . $allowedCheckInLabel : '')),
                    'early_title' => trim(($attendanceStatusTitle ?: 'Early Checkout') . ($allowedCheckOutLabel ? ' | Allowed ' . $allowedCheckOutLabel : '')),
                ];
            };

        @endphp

        <div id="attendanceResultsBlock" class="card attendance-day-card">
            <div class="card-header">
                <div class="attendance-table-toolbar mb-0">
                    <div class="attendance-toolbar-left">
                        <div class="attendance-entry-control">
                            <span>Show</span>
                            <select id="attendanceEntries" class="form-control attendance-entry-select">
                                <option value="25" {{ (string) $perPage === '25' ? 'selected' : '' }}>25</option>
                                <option value="50" {{ (string) $perPage === '50' ? 'selected' : '' }}>50</option>
                                <option value="100" {{ (string) $perPage === '100' ? 'selected' : '' }}>100</option>
                                <option value="200" {{ (string) $perPage === '200' ? 'selected' : '' }}>200</option>
                                <option value="all" {{ $perPage === 'all' ? 'selected' : '' }}>All</option>
                            </select>
                            <span>entries</span>
                        </div>
                        <h6 class="card-title mb-0">{{ __('index.attendance_of_the_day') }}</h6>
                    </div>
                    <div class="attendance-toolbar-actions">
                        @can('attendance_csv_export')
                            <button type="button"
                                    id="download-daywise-attendance-excel"
                                    data-href="{{ route('admin.attendances.index') }}"
                                    class="btn btn-outline-secondary btn-sm">Export
                            </button>
                        @endcan
                    </div>
                    <div class="attendance-table-search-wrap">
                        <button type="button"
                                id="attendanceResetFilters"
                                class="btn btn-outline-secondary btn-sm attendance-table-reset"
                                title="{{ __('index.reset') }}">
                            <i class="link-icon" data-feather="rotate-ccw"></i>
                            {{ __('index.reset') }}
                        </button>
                        <input type="text"
                               id="attendanceDaySearch"
                               class="attendance-table-search"
                               value="{{ $filterParameter['search'] ?? '' }}"
                               placeholder="Search ...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="attendance-results-reload" aria-hidden="true">
                    <div class="attendance-reload-panel" role="status" aria-live="polite">
                        <div class="attendance-reload-header">
                            <span>Reloading attendance</span>
                            <span class="attendance-reload-percent">0%</span>
                        </div>
                        <div class="attendance-reload-track">
                            <div class="attendance-reload-bar"></div>
                        </div>
                        <div class="attendance-reload-text">Refreshing table data...</div>
                    </div>
                </div>
                <div class="table-responsive">

                        <table id="dataTableExample" class="table">
                            <thead>
                            <tr>
                                @can('attendance_show')
                                    <th></th>
                                @endcan
                                <th class="text-center">{{ __('index.username') }}</th>
                                <th>{{ __('index.employee_name') }}</th>
                                    @if($multipleAttendance > 1)
                                        <th class="text-center">{{ __('index.total_worked_hours') }}</th>
                                        <th class="text-center">{{ __('index.check_in_at') }}</th>
                                        <th class="text-center">{{ __('index.check_out_at') }}</th>
                                    @else
                                        <th class="text-center">Time In</th>
                                        <th class="text-center">{{ __('index.check_in_at') }}</th>
                                        <th class="text-center">Time Out</th>
                                        <th class="text-center">{{ __('index.check_out_at') }}</th>
                                        <th class="text-center">{{ __('index.worked_hour') }}</th>
                                    @endif
                                <th class="text-center">{{ __('index.attendance_status') }}</th>
                                @canany(['attendance_create', 'attendance_update', 'attendance_delete'])
                                    <th class="text-center">{{ __('index.action') }}</th>
                                @endcanany
                            </tr>
                            </thead>
                            <tbody>
                                @php
                                $changeColor = [
                                    0 => 'danger',
                                    1 => 'success',
                                ]
                               @endphp
                                @php
                                    $selectedAttendanceDate = $isBsEnabled
                                        ? \App\Helpers\AppHelper::dateInYmdFormatNepToEng($filterParameter['attendance_date'])
                                        : $filterParameter['attendance_date'];
                                @endphp

                                @forelse($groupedAttendance as $userId => $userAttendances)

                                    @php
                                        $firstAttendance = $userAttendances->first();
                                        $totalWorkedMinutes = $userAttendances->sum('worked_hour');
                                        $lastAttendance = $userAttendances->last();

                                        $hours = floor($totalWorkedMinutes / 60);
                                        $minutes = $totalWorkedMinutes % 60;

                                        $workedHours = '';
                                        if ($hours > 0) {
                                            $workedHours .= $hours . ' h ';
                                        }
                                        if ($minutes > 0) {
                                            $workedHours .= $minutes . ' m';
                                        }
                                        $workedHours = trim($workedHours);

                                        $multipleEntries = $userAttendances->count();

                                        $nightShift = ($firstAttendance->user_shift_type ?? $firstAttendance->shift) === \App\Enum\ShiftTypeEnum::night->value;
                                        $canAddAttendanceForSelectedDate = $filterParameter['attendance_date'] != $currentDate
                                            && !$firstAttendance->attendance_id
                                            && !$firstAttendance->leave_request_id;
                                        $quickChatTitle = 'Quick chat with ' . ucfirst($firstAttendance->user_name);
                                        $unreadChatCount = (int) ($firstAttendance->unread_chat_count ?? 0);
                                        $approvedDayOffDays = (int) ($firstAttendance->approved_day_off_days ?? 0);
                                        $approvedLeaveDays = (int) ($firstAttendance->approved_leave_days ?? 0);
                                        $pendingLeaveDays = (int) ($firstAttendance->pending_leave_days ?? 0);
                                        $approvedTimeLeaveDays = (int) ($firstAttendance->approved_time_leave_days ?? 0);
                                        $pendingTimeLeaveDays = (int) ($firstAttendance->pending_time_leave_days ?? 0);
                                        $attendanceDetailDateParts = \App\Helpers\AppHelper::getDayMonthYearFromDate(
                                            $filterParameter['date_in_bs'] ? $filterParameter['attendance_date'] : $selectedAttendanceDate
                                        );
                                        $attendanceDetailYear = (int) $attendanceDetailDateParts['year'];
                                        $attendanceDetailMonth = (int) $attendanceDetailDateParts['month'];
                                        $attendanceLeaveDetailUrl = route('admin.attendances.leave-details');
                                        $attendanceLeaveDetailBaseData = [
                                            'data-url' => $attendanceLeaveDetailUrl,
                                            'data-user-id' => $userId,
                                            'data-employee-name' => $firstAttendance->user_name ?: $firstAttendance->username,
                                            'data-year' => $attendanceDetailYear,
                                            'data-month' => $attendanceDetailMonth,
                                            'data-date-in-bs' => $filterParameter['date_in_bs'] ? '1' : '0',
                                        ];

                                    @endphp

                                    @php
                                        $rowHasCheckIn = $hasCheckIn($userAttendances);
                                        $rowHasCheckOut = $hasCheckOut($userAttendances);
                                        $rowIsApprovedLeave = $firstAttendance?->leave_request_id && $firstAttendance?->leave_request_status === 'approved';
                                        $rowIsPendingLeaveRequest = $firstAttendance?->leave_request_id && $firstAttendance?->leave_request_status === 'pending';
                                        $rowIsPendingTimeLeaveRequest = $firstAttendance?->time_leave_id && $firstAttendance?->time_leave_status === 'pending';
                                        $rowIsDayOff = $rowIsApprovedLeave && $isDayOffType($firstAttendance?->leave_request_type);
                                        $rowIsLeave = $rowIsApprovedLeave && !$isDayOffType($firstAttendance?->leave_request_type);
                                        $rowIsNotYetCheckIn = !$rowHasCheckIn && !$firstAttendance?->leave_request_id;
                                        $rowCanQuickLeave = $firstAttendance?->attendance_status !== \App\Models\Attendance::ATTENDANCE_APPROVED;
                                        $rowHasAnyRequest = (bool) ($firstAttendance?->leave_request_id || $firstAttendance?->time_leave_id);
                                        $rowShowAttendanceStatus = !is_null($firstAttendance->attendance_status) || !$rowHasAnyRequest;
                                        $firstAttendanceBadgeMeta = $attendanceBadgeMeta($firstAttendance);
                                    @endphp

                                    <tr class="attendance-day-row"
                                        data-summary-total_employee="1"
                                        data-summary-total_check_in="{{ $rowHasCheckIn ? '1' : '0' }}"
                                        data-summary-total_not_yet_check_in="{{ $rowIsNotYetCheckIn ? '1' : '0' }}"
                                        data-summary-total_check_out="{{ $rowHasCheckOut ? '1' : '0' }}"
                                        data-summary-total_not_yet_check_out="{{ ($rowHasCheckIn && !$rowHasCheckOut) ? '1' : '0' }}"
                                        data-summary-total_day_off="{{ $rowIsDayOff ? '1' : '0' }}"
                                        data-summary-total_leave="{{ $rowIsLeave ? '1' : '0' }}"
                                        data-summary-total_time_leave="{{ ($firstAttendance?->time_leave_id && $firstAttendance?->time_leave_status === 'approved') ? '1' : '0' }}"
                                        data-summary-total_leave_request="{{ $rowIsPendingLeaveRequest ? '1' : '0' }}"
                                        data-summary-total_time_leave_request="{{ $rowIsPendingTimeLeaveRequest ? '1' : '0' }}">
                                    @can('attendance_show')
                                        <td>
                                            <ul class="text-center list-unstyled mb-0">
                                                <li class="me-2">
                                                    <a href="{{ route('admin.attendances.show', $userId) }}"
                                                       title="{{ __('index.show_detail') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </td>
                                    @endcan

                                    <td class="text-center attendance-username-cell">
                                        <div class="attendance-username-wrap">
                                            <span>{{ $firstAttendance->username ?: 'N/A' }}</span>
                                            <div class="attendance-username-summary">
                                                @if($approvedDayOffDays > 0)
                                                    <button type="button"
                                                            class="attendance-summary-popover-row is-day-off is-clickable attendance-leave-detail-trigger"
                                                            title="{{ __('index.show_detail') }}"
                                                            data-category="day_off"
                                                            data-label="{{ __('index.total_day_off') }}"
                                                            @foreach($attendanceLeaveDetailBaseData as $attribute => $value) {{ $attribute }}="{{ $value }}" @endforeach>
                                                        <span>{{ __('index.total_day_off') }}</span>
                                                        <strong>{{ number_format($approvedDayOffDays) }}</strong>
                                                    </button>
                                                @else
                                                    <div class="attendance-summary-popover-row is-day-off is-empty">
                                                        <span>{{ __('index.total_day_off') }}</span>
                                                        <strong>{{ number_format($approvedDayOffDays) }}</strong>
                                                    </div>
                                                @endif
                                                @if($approvedLeaveDays > 0)
                                                    <button type="button"
                                                            class="attendance-summary-popover-row is-leave is-clickable attendance-leave-detail-trigger"
                                                            title="{{ __('index.show_detail') }}"
                                                            data-category="leave"
                                                            data-label="{{ __('index.leave') }}"
                                                            @foreach($attendanceLeaveDetailBaseData as $attribute => $value) {{ $attribute }}="{{ $value }}" @endforeach>
                                                        <span>{{ __('index.leave') }}</span>
                                                        <strong>{{ number_format($approvedLeaveDays) }}</strong>
                                                    </button>
                                                @else
                                                    <div class="attendance-summary-popover-row is-leave is-empty">
                                                        <span>{{ __('index.leave') }}</span>
                                                        <strong>{{ number_format($approvedLeaveDays) }}</strong>
                                                    </div>
                                                @endif
                                                @if($pendingLeaveDays > 0)
                                                    <button type="button"
                                                            class="attendance-summary-popover-row is-pending is-clickable attendance-leave-detail-trigger"
                                                            title="{{ __('index.show_detail') }}"
                                                            data-category="pending_leave"
                                                            data-label="{{ __('index.pending_leave_requests') }}"
                                                            @foreach($attendanceLeaveDetailBaseData as $attribute => $value) {{ $attribute }}="{{ $value }}" @endforeach>
                                                        <span>{{ __('index.pending_leave_requests') }}</span>
                                                        <strong>{{ number_format($pendingLeaveDays) }}</strong>
                                                    </button>
                                                @else
                                                    <div class="attendance-summary-popover-row is-pending is-empty">
                                                        <span>{{ __('index.pending_leave_requests') }}</span>
                                                        <strong>{{ number_format($pendingLeaveDays) }}</strong>
                                                    </div>
                                                @endif
                                                @if($approvedTimeLeaveDays > 0)
                                                    <button type="button"
                                                            class="attendance-summary-popover-row is-time-leave is-clickable attendance-leave-detail-trigger"
                                                            title="{{ __('index.show_detail') }}"
                                                            data-category="time_leave"
                                                            data-label="{{ __('index.time_leave') }}"
                                                            @foreach($attendanceLeaveDetailBaseData as $attribute => $value) {{ $attribute }}="{{ $value }}" @endforeach>
                                                        <span>{{ __('index.time_leave') }}</span>
                                                        <strong>{{ number_format($approvedTimeLeaveDays) }}</strong>
                                                    </button>
                                                @else
                                                    <div class="attendance-summary-popover-row is-time-leave is-empty">
                                                        <span>{{ __('index.time_leave') }}</span>
                                                        <strong>{{ number_format($approvedTimeLeaveDays) }}</strong>
                                                    </div>
                                                @endif
                                                @if($pendingTimeLeaveDays > 0)
                                                    <button type="button"
                                                            class="attendance-summary-popover-row is-pending is-clickable attendance-leave-detail-trigger"
                                                            title="{{ __('index.show_detail') }}"
                                                            data-category="pending_time_leave"
                                                            data-label="{{ __('index.time_leave_request') }}"
                                                            @foreach($attendanceLeaveDetailBaseData as $attribute => $value) {{ $attribute }}="{{ $value }}" @endforeach>
                                                        <span>{{ __('index.time_leave_request') }}</span>
                                                        <strong>{{ number_format($pendingTimeLeaveDays) }}</strong>
                                                    </button>
                                                @else
                                                    <div class="attendance-summary-popover-row is-pending is-empty">
                                                        <span>{{ __('index.time_leave_request') }}</span>
                                                        <strong>{{ number_format($pendingTimeLeaveDays) }}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        @php
                                            $profileImage = $firstAttendance->avatar
                                                ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $firstAttendance->avatar)
                                                : asset('assets/images/img.png');
                                            $profileTitle = ucfirst($firstAttendance->user_name)
                                                . ' | ' . __('index.branch_name') . ': ' . ($firstAttendance->branch_name ? ucfirst($firstAttendance->branch_name) : 'N/A')
                                                . ' | ' . __('index.department') . ': ' . ($firstAttendance->department_name ? ucfirst($firstAttendance->department_name) : 'N/A');
                                        @endphp
                                        <div class="d-flex align-items-center gap-2 attendance-profile-wrap">
                                            <a href="#"
                                               class="showProfilePhoto"
                                               data-src="{{ $profileImage }}"
                                               data-name="{{ ucfirst($firstAttendance->user_name) }}"
                                               title="{{ $profileTitle }}">
                                                <img src="{{ $profileImage }}"
                                                 alt="{{ ucfirst($firstAttendance->user_name) }}"
                                                 class="rounded-circle"
                                                 style="width: 42px; height: 42px; object-fit: cover;">
                                            </a>
                                            <div>
                                                <div class="fw-semibold">{{ ucfirst($firstAttendance->user_name) }}</div>
                                                <small class="text-muted">{{ $firstAttendance->phone ?: 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    @if($nightShift && $multipleAttendance <= 1)
                                            <td class="text-center attendance-time-in-cell">
                                                <div class="attendance-time-in-wrap">
                                                    <span>{{ $firstAttendance->office_opening_time ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->office_opening_time) : 'N/A' }}</span>
                                                    @can('view_employee_chat')
                                                        <a href="#"
                                                           class="btn btn-outline-primary btn-xs attendance-profile-chat-badge openAttendanceChat"
                                                           data-employee-id="{{ $firstAttendance->user_id }}"
                                                           data-employee-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                           data-employee-avatar="{{ $profileImage }}"
                                                           data-employee-subtitle="{{ $firstAttendance->department_name ? ucfirst($firstAttendance->department_name) : ($firstAttendance->phone ?: 'Employee') }}"
                                                           data-employee-online="{{ (int) ($firstAttendance->online_status ?? 0) === \App\Models\User::ONLINE ? '1' : '0' }}"
                                                           title="{{ $quickChatTitle }}">
                                                             <i class="link-icon" data-feather="message-circle"></i>
                                                             Quick Chat
                                                             <span class="attendance-chat-unread-count {{ $unreadChatCount > 0 ? '' : 'is-empty' }}">{{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}</span>
                                                         </a>
                                                     @endcan
                                                </div>
                                            </td>
                                            @if(isset($firstAttendance->night_checkin))
                                                <td class="text-center attendance-time-cell">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="{{ $firstAttendance->check_in_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($firstAttendance->check_in_type).' '.__('index.checkin') }}"
                                                      data-bs-toggle="modal"
                                                      data-href="{{ 'https://maps.google.com/maps?q='.$firstAttendance->check_in_latitude.','.$firstAttendance->check_in_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                      data-bs-target="{{ '#addslider' }}"
                                                >
                                                    {{  \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $firstAttendance->night_checkin) ?? '' }}
                                                </span>
                                                    @if($firstAttendanceBadgeMeta['late'])
                                                        <span class="attendance-overlay-badge">{!! $statusBadge('L', 'Late', 'is-late', $firstAttendanceBadgeMeta['late_title']) !!}</span>
                                                    @endif
                                                </td>
                                            @else
                                                <td class="text-center attendance-time-cell"></td>
                                            @endif
                                            <td class="text-center">
                                                {{ $firstAttendance->office_closing_time ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->office_closing_time) : 'N/A' }}
                                            </td>

                                            @if( isset($firstAttendance->night_checkout))
                                                <td class="text-center attendance-time-cell">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="{{ $firstAttendance->check_out_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($firstAttendance->check_out_type).' '.__('index.checkout') }}"
                                                      data-bs-toggle="modal"
                                                      data-href="{{  'https://maps.google.com/maps?q='.$firstAttendance->check_out_latitude.','.$firstAttendance->check_out_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                      data-bs-target="{{  '#addslider' }}"
                                                >
                                                   {{  \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $firstAttendance->night_checkout)  ??  '' }}
                                                </span>
                                                    @if($firstAttendanceBadgeMeta['early'])
                                                        <span class="attendance-overlay-badge">{!! $statusBadge('E', 'Early Checkout', 'is-early', $firstAttendanceBadgeMeta['early_title']) !!}</span>
                                                    @endif
                                                </td>
                                            @else
                                                <td class="text-center attendance-time-cell">
                                                    @if($firstAttendanceBadgeMeta['no_checkout'])
                                                        <span class="attendance-overlay-badge">{!! $statusBadge('NC', 'No Checkout', 'is-danger', 'No Checkout') !!}</span>
                                                    @endif
                                                </td>
                                            @endif

                                            <td class="text-center">
                                                {{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($firstAttendance->worked_hour) }}
                                            </td>
                                    @elseif($multipleAttendance > 1)
                                        <td class="text-center">
                                            {{ $workedHours }}
                                        </td>
                                        <td class="text-center">
                                            <div class="attendance-multi-time-stack">
                                                @foreach($userAttendances as $attendanceEntry)
                                                    @php
                                                        $multiCheckIn = $attendanceEntry->check_in_at ?: $attendanceEntry->night_checkin;
                                                        $multiCheckInIsNight = empty($attendanceEntry->check_in_at) && !empty($attendanceEntry->night_checkin);
                                                        $attendanceEntryBadgeMeta = $attendanceBadgeMeta($attendanceEntry);
                                                    @endphp
                                                    <span class="attendance-multi-time-item">
                                                        @if($multiCheckIn)
                                                            <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                  title="{{ $attendanceEntry->check_in_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($attendanceEntry->check_in_type).' '.__('index.checkin') }}"
                                                                  data-bs-toggle="modal"
                                                                  data-href="{{ 'https://maps.google.com/maps?q='.$attendanceEntry->check_in_latitude.','.$attendanceEntry->check_in_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                                  data-bs-target="{{ '#addslider' }}">
                                                                {{ $multiCheckInIsNight
                                                                    ? \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $multiCheckIn)
                                                                    : \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $multiCheckIn) }}
                                                            </span>
                                                            @if($attendanceEntryBadgeMeta['late'])
                                                                <span class="attendance-overlay-badge">{!! $statusBadge('L', 'Late', 'is-late', $attendanceEntryBadgeMeta['late_title']) !!}</span>
                                                            @endif
                                                        @else
                                                            <span class="attendance-multi-time-empty">-</span>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="attendance-multi-time-stack">
                                                @foreach($userAttendances as $attendanceEntry)
                                                    @php
                                                        $multiCheckOut = $attendanceEntry->check_out_at ?: $attendanceEntry->night_checkout;
                                                        $multiCheckOutIsNight = empty($attendanceEntry->check_out_at) && !empty($attendanceEntry->night_checkout);
                                                        $attendanceEntryBadgeMeta = $attendanceBadgeMeta($attendanceEntry);
                                                    @endphp
                                                    <span class="attendance-multi-time-item">
                                                        @if($multiCheckOut)
                                                            <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                  title="{{ $attendanceEntry->check_out_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($attendanceEntry->check_out_type).' '.__('index.checkout') }}"
                                                                  data-bs-toggle="modal"
                                                                  data-href="{{ 'https://maps.google.com/maps?q='.$attendanceEntry->check_out_latitude.','.$attendanceEntry->check_out_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                                  data-bs-target="{{ '#addslider' }}">
                                                                {{ $multiCheckOutIsNight
                                                                    ? \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $multiCheckOut)
                                                                    : \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $multiCheckOut) }}
                                                            </span>
                                                            @if($attendanceEntryBadgeMeta['early'])
                                                                <span class="attendance-overlay-badge">{!! $statusBadge('E', 'Early Checkout', 'is-early', $attendanceEntryBadgeMeta['early_title']) !!}</span>
                                                            @endif
                                                        @else
                                                            <span class="attendance-multi-time-empty">-</span>
                                                            @if($attendanceEntryBadgeMeta['no_checkout'])
                                                                <span class="attendance-overlay-badge">{!! $statusBadge('NC', 'No Checkout', 'is-danger', 'No Checkout') !!}</span>
                                                            @endif
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                    @else
                                        <td class="text-center attendance-time-in-cell">
                                            <div class="attendance-time-in-wrap">
                                                <span>{{ $firstAttendance->office_opening_time ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->office_opening_time) : 'N/A' }}</span>
                                                @can('view_employee_chat')
                                                    <a href="#"
                                                       class="btn btn-outline-primary btn-xs attendance-profile-chat-badge openAttendanceChat"
                                                       data-employee-id="{{ $firstAttendance->user_id }}"
                                                       data-employee-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                       data-employee-avatar="{{ $profileImage }}"
                                                       data-employee-subtitle="{{ $firstAttendance->department_name ? ucfirst($firstAttendance->department_name) : ($firstAttendance->phone ?: 'Employee') }}"
                                                       data-employee-online="{{ (int) ($firstAttendance->online_status ?? 0) === \App\Models\User::ONLINE ? '1' : '0' }}"
                                                       title="{{ $quickChatTitle }}">
                                                         <i class="link-icon" data-feather="message-circle"></i>
                                                         Quick Chat
                                                         <span class="attendance-chat-unread-count {{ $unreadChatCount > 0 ? '' : 'is-empty' }}">{{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}</span>
                                                     </a>
                                                 @endcan
                                            </div>
                                        </td>
                                        @if(isset($firstAttendance->check_in_at))
                                            <td class="text-center attendance-time-cell">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="{{ $firstAttendance->check_in_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($firstAttendance->check_in_type).' '.__('index.checkin') }}"
                                                      data-bs-toggle="modal"
                                                      data-href="{{ 'https://maps.google.com/maps?q='.$firstAttendance->check_in_latitude.','.$firstAttendance->check_in_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                      data-bs-target="{{ '#addslider' }}"
                                                >
                                                    {{  \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->check_in_at) ?? '' }}
                                                </span>
                                                @if($firstAttendanceBadgeMeta['late'])
                                                    <span class="attendance-overlay-badge">{!! $statusBadge('L', 'Late', 'is-late', $firstAttendanceBadgeMeta['late_title']) !!}</span>
                                                @endif
                                            </td>
                                        @else
                                            <td class="text-center attendance-time-cell"></td>
                                        @endif
                                        <td class="text-center">
                                            {{ $firstAttendance->office_closing_time ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->office_closing_time) : 'N/A' }}
                                        </td>

                                        @if(isset($firstAttendance->check_out_at) )
                                            <td class="text-center attendance-time-cell">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="{{ $firstAttendance->check_out_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($firstAttendance->check_out_type).' '.__('index.checkout') }}"
                                                      data-bs-toggle="modal"
                                                      data-href="{{  'https://maps.google.com/maps?q='.$firstAttendance->check_out_latitude.','.$firstAttendance->check_out_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                      data-bs-target="{{  '#addslider' }}"
                                                >
                                                   {{ \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->check_out_at) ??  '' }}
                                                </span>
                                                @if($firstAttendanceBadgeMeta['early'])
                                                    <span class="attendance-overlay-badge">{!! $statusBadge('E', 'Early Checkout', 'is-early', $firstAttendanceBadgeMeta['early_title']) !!}</span>
                                                @endif
                                            </td>
                                        @else
                                            <td class="text-center attendance-time-cell">
                                                @if($firstAttendanceBadgeMeta['no_checkout'])
                                                    <span class="attendance-overlay-badge">{!! $statusBadge('NC', 'No Checkout', 'is-danger', 'No Checkout') !!}</span>
                                                @endif
                                            </td>
                                        @endif

                                        <td class="text-center">
                                            {{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($firstAttendance->worked_hour) }}
                                        </td>
                                    @endif

                                    <td class="text-center">
                                        <div class="attendance-status-stack{{ $rowShowAttendanceStatus ? ' has-attendance-status' : '' }}">
                                            @if($rowShowAttendanceStatus)
                                            <div>
                                                @if(!is_null($firstAttendance->attendance_status))
                                                    <a class="btn btn-{{ $changeColor[$firstAttendance->attendance_status] }} btn-xs"
                                                       title="{{ $firstAttendance->attendance_status == \App\Models\Attendance::ATTENDANCE_APPROVED ? __('index.approved') : __('index.rejected') }}">
                                                        {{ $firstAttendance->attendance_status == \App\Models\Attendance::ATTENDANCE_APPROVED ? __('index.approved') : __('index.rejected') }}
                                                    </a>
                                                @elseif(!$rowHasAnyRequest)
                                                    <span class="btn btn-light btn-xs disabled">
                                                        {{ __('index.pending') }}
                                                    </span>
                                                @endif
                                            </div>
                                            @endif
                                        <div class="d-inline-flex flex-column align-items-center gap-2 attendance-status-actions">
                                            @if(!$firstAttendance->leave_request_id && !$firstAttendance->time_leave_id)
                                                @can('create_time_leave_request')
                                                    <a href="#"
                                                       class="btn btn-outline-info btn-xs quickApproveTimeLeaveTrigger"
                                                       data-user-id="{{ $firstAttendance->user_id }}"
                                                       data-user-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                       data-attendance-date="{{ $selectedAttendanceDate }}"
                                                       data-display-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $selectedAttendanceDate) }}">
                                                        Quick Time Leave
                                                    </a>
                                                @endcan
                                            @endif

                                            @if($firstAttendance->leave_request_id || $firstAttendance->time_leave_id)
                                                @if($firstAttendance->leave_request_id)
                                                    <div class="attendance-leave-content">
                                                        @canany(['update_leave_request','access_admin_leave'])
                                                            <a href="#"
                                                               class="attendanceLeaveRequestUpdate"
                                                               data-href="{{ route('admin.leave-request.update-status', $firstAttendance->leave_request_id) }}"
                                                               data-status="{{ $firstAttendance->leave_request_status }}"
                                                               data-remark="{{ $firstAttendance->leave_request_admin_remark }}"
                                                               data-reason="{{ strip_tags((string) $firstAttendance->leave_request_reason) }}"
                                                               data-id="{{ $firstAttendance->leave_request_id }}">
                                                                <span class="attendance-leave-pill {{ $rowIsDayOff ? 'is-day-off' : ($rowIsLeave ? 'is-leave' : 'is-' . $firstAttendance->leave_request_status) }}"
                                                                      title="{{ \App\Helpers\AppHelper::convertLeaveDateFormat($firstAttendance->leave_request_from) }} - {{ \App\Helpers\AppHelper::convertLeaveDateFormat($firstAttendance->leave_request_to) }}">
                                                                    <span class="attendance-leave-pill-label">{{ $firstAttendance->leave_request_type ? ucfirst($firstAttendance->leave_request_type) : __('index.leave_request') }}</span>
                                                                    <span class="attendance-leave-pill-status">{{ ucfirst($firstAttendance->leave_request_status) }}</span>
                                                                </span>
                                                            </a>
                                                        @else
                                                            <span class="attendance-leave-pill {{ $rowIsDayOff ? 'is-day-off' : ($rowIsLeave ? 'is-leave' : 'is-' . $firstAttendance->leave_request_status) }}"
                                                                  title="{{ \App\Helpers\AppHelper::convertLeaveDateFormat($firstAttendance->leave_request_from) }} - {{ \App\Helpers\AppHelper::convertLeaveDateFormat($firstAttendance->leave_request_to) }}">
                                                                <span class="attendance-leave-pill-label">{{ $firstAttendance->leave_request_type ? ucfirst($firstAttendance->leave_request_type) : __('index.leave_request') }}</span>
                                                                <span class="attendance-leave-pill-status">{{ ucfirst($firstAttendance->leave_request_status) }}</span>
                                                            </span>
                                                        @endcanany
                                                    </div>
                                                @endif

                                                @if(!$firstAttendance->leave_request_id && $firstAttendance->time_leave_id)
                                                    <div class="attendance-leave-content">
                                                        @if(auth('admin')->check() || \Illuminate\Support\Facades\Gate::allows('update_time_leave'))
                                                            <a href="#"
                                                               class="attendanceTimeLeaveRequestUpdate"
                                                               data-href="{{ route('admin.time-leave-request.update-status', $firstAttendance->time_leave_id) }}"
                                                               data-status="{{ $firstAttendance->time_leave_status }}"
                                                               data-remark="{{ $firstAttendance->time_leave_admin_remark }}"
                                                               data-reason="{{ strip_tags((string) $firstAttendance->time_leave_reason) }}"
                                                               data-id="{{ $firstAttendance->time_leave_id }}"
                                                               data-label="{{ __('index.time_leave_request') }} {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($firstAttendance->time_leave_start_time) }} - {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($firstAttendance->time_leave_end_time) }}">
                                                                <span class="attendance-leave-pill is-time-leave {{ $firstAttendance->time_leave_status === 'pending' ? 'is-pending' : '' }}"
                                                                      title="{{ \App\Helpers\AppHelper::timeLeaverequestDate($firstAttendance->time_leave_date) }} {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($firstAttendance->time_leave_start_time) }} - {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($firstAttendance->time_leave_end_time) }}">
                                                                    <span class="attendance-leave-pill-label">{{ __('index.time_leave_request') }}</span>
                                                                    <span class="attendance-leave-pill-status">
                                                                        {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($firstAttendance->time_leave_start_time) }}
                                                                        -
                                                                        {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($firstAttendance->time_leave_end_time) }}
                                                                    </span>
                                                                </span>
                                                            </a>
                                                        @else
                                                            <span class="attendance-leave-pill is-time-leave {{ $firstAttendance->time_leave_status === 'pending' ? 'is-pending' : '' }}"
                                                                  title="{{ \App\Helpers\AppHelper::timeLeaverequestDate($firstAttendance->time_leave_date) }} {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($firstAttendance->time_leave_start_time) }} - {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($firstAttendance->time_leave_end_time) }}">
                                                                <span class="attendance-leave-pill-label">{{ __('index.time_leave_request') }}</span>
                                                                <span class="attendance-leave-pill-status">
                                                                    {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($firstAttendance->time_leave_start_time) }}
                                                                    -
                                                                    {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($firstAttendance->time_leave_end_time) }}
                                                                </span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif

                                            @else
                                                @if($rowCanQuickLeave)
                                                    @can('quick_leave')
                                                    <a href="#"
                                                       class="btn btn-outline-primary btn-xs quickApproveLeaveTrigger"
                                                       data-user-id="{{ $firstAttendance->user_id }}"
                                                       data-user-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                       data-attendance-date="{{ $selectedAttendanceDate }}"
                                                       data-display-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $selectedAttendanceDate) }}"
                                                       data-fetch-url="{{ route('admin.leaves.employee-data', $firstAttendance->user_id) }}">
                                                        Quick Leave
                                                    </a>
                                                    @endcan
                                                @endif
                                            @endif
                                        </div>
                                        </div>
                                    </td>

                                    @canany(['attendance_create','attendance_update','attendance_delete'])
                                        @if($nightShift && $filterParameter['attendance_date'] ==  $currentDate)

                                            <td class="text-center">
                                                <ul class="d-flex text-center list-unstyled mb-0 justify-content-center align-items-center">
                                                    @php
                                                        $nightAttendance = \App\Helpers\AttendanceHelper::checkNightShiftCheckOut($userId);

                                                    @endphp
                                                    @if($nightAttendance == 'checkout')
                                                        @can('attendance_update')
                                                            <li class="me-2">
                                                                <a href="{{ route('admin.employees.check-out', [$firstAttendance->company_id, $firstAttendance->user_id]) }}"
                                                                   id="checkOut"
                                                                   data-href=""
                                                                   data-id="">
                                                                    <button class="btn btn-danger btn-xs">{{ __('index.check_out') }}</button>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    @elseif($nightAttendance == 'checkin')
                                                        @can('attendance_create')
                                                            <li class="me-2">
                                                                <a href="{{ route('admin.employees.check-in', [$firstAttendance->company_id, $firstAttendance->user_id]) }}"
                                                                   id="checkIn"
                                                                   data-href=""
                                                                   data-id="">
                                                                    <button class="btn btn-success btn-xs">{{ __('index.check_in') }}</button>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    @else

                                                    @endif

                                                    @if($firstAttendance->attendance_id)
                                                        @can('attendance_update')
                                                            <li class="me-2">
                                                                <a href=""
                                                                   class="editNightAttendance"
                                                                   data-href="{{ route('admin.night_attendances.update', $firstAttendance->attendance_id) }}"
                                                                   data-in="{{ $firstAttendance->night_checkin }}"
                                                                   data-out="{{ $firstAttendance->night_checkout ?? null  }}"
                                                                   data-remark="{{ $firstAttendance->edit_remark }}"
                                                                   data-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $firstAttendance->attendance_date) }}"
                                                                   data-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                                   title="{{ __('index.edit_attendance_time') }}"
                                                                >
                                                                    <i class="link-icon"
                                                                       data-feather="edit"></i>
                                                                </a>
                                                            </li>
                                                        @endcan

                                                        @can('attendance_delete')
                                                            <li class="me-2">
                                                                <a class="deleteAttendance" href="{{ route('admin.attendance.delete', $firstAttendance->attendance_id) }}">
                                                                    <i class="link-icon"  data-feather="delete"></i>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                        @if($attendanceNote)
                                                            <li class="me-2">
                                                                <a href="#"
                                                                   class="noteLink"
                                                                   data-checkout_note="{{ $firstAttendance->check_out_note }}"
                                                                   data-checkin_note="{{ $firstAttendance->check_in_note }}">
                                                                    Note
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endif
                                                </ul>
                                            </td>
                                        @elseif($multipleAttendance > 1)
                                            <td class="text-center">
                                                <ul class="d-flex text-center list-unstyled mb-0 justify-content-center align-items-center">
                                                    @if($filterParameter['attendance_date'] == $currentDate && ($multipleEntries < $multipleAttendance || ($lastAttendance->check_in_at && !$lastAttendance->check_out_at)))

                                                        @if((!$firstAttendance->check_in_at && !$firstAttendance->check_out_at) || ($lastAttendance->check_in_at && $lastAttendance->check_out_at))
                                                            @can('attendance_create')
                                                                <li class="me-2">
                                                                    <a href="{{ route('admin.employees.check-in', [$firstAttendance->company_id, $firstAttendance->user_id]) }}"
                                                                       id="checkIn"
                                                                       data-href=""
                                                                       data-id="">
                                                                        <button
                                                                            class="btn btn-success btn-xs">{{ __('index.check_in') }}</button>
                                                                    </a>
                                                                </li>
                                                            @endcan
                                                        @elseif(($firstAttendance->check_in_at && !$firstAttendance->check_out_at) || ($lastAttendance->check_in_at && !$lastAttendance->check_out_at))
                                                            @can('attendance_update')
                                                                <li class="me-2">
                                                                    <a href="{{ route('admin.employees.check-out', [$firstAttendance->company_id, $firstAttendance->user_id]) }}"
                                                                       id="checkOut"
                                                                       data-href=""
                                                                       data-id="">
                                                                        <button
                                                                            class="btn btn-danger btn-xs">{{ __('index.check_out') }}</button>
                                                                    </a>
                                                                </li>
                                                            @endcan
                                                        @endif

                                                    @endif
                                                    @if($canAddAttendanceForSelectedDate)
                                                        @can('attendance_create')
                                                            <li class="me-2">
                                                                <a href=""
                                                                   class="addEmployeeAttendance"
                                                                   data-href="{{ route('admin.attendances.store') }}"
                                                                   data-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                                   data-date="{{ $filterParameter['attendance_date'] }}"
                                                                   data-cdate="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $filterParameter['attendance_date']) }}"
                                                                   data-user_id="{{ $firstAttendance->user_id }}"
                                                                   title="{{ __('index.add_attendance_time') }}">
                                                                    <i class="link-icon" data-feather="plus-circle"></i>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    @endif
                                                    @if($attendanceNote)
                                                        <li class="me-2">
                                                            <a href="#"
                                                               class="noteLink"
                                                               data-checkout_note="{{ $firstAttendance->check_out_note }}"
                                                               data-checkin_note="{{ $firstAttendance->check_in_note }}">
                                                                Note
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </td>
                                        @else
                                            <td class="text-center">
                                                <ul class="d-flex text-center list-unstyled mb-0 justify-content-center align-items-center">
                                                    @if($filterParameter['attendance_date'] ==  $currentDate)
                                                            @if(!$firstAttendance->check_in_at)
                                                                @can('attendance_create')
                                                                    <li class="me-2">
                                                                        <a href="{{ route('admin.employees.check-in', [$firstAttendance->company_id, $firstAttendance->user_id]) }}"
                                                                           id="checkIn"
                                                                           data-href=""
                                                                           data-id="">
                                                                            <button class="btn btn-success btn-xs">{{ __('index.check_in') }}</button>
                                                                        </a>
                                                                    </li>
                                                                @endcan
                                                            @endif


                                                            @if($firstAttendance->check_in_at && !$firstAttendance->check_out_at)
                                                                @can('attendance_update')
                                                                    <li class="me-2">
                                                                        <a href="{{ route('admin.employees.check-out', [$firstAttendance->company_id, $firstAttendance->user_id]) }}"
                                                                           id="checkOut"
                                                                           data-href=""
                                                                           data-id="">
                                                                            <button class="btn btn-danger btn-xs">{{ __('index.check_out') }}</button>
                                                                        </a>
                                                                    </li>
                                                                @endcan
                                                            @endif
                                                    @endif

                                                    @if($firstAttendance->attendance_id)
                                                        @can('attendance_update')
                                                            <li class="me-2">
                                                                <a href=""
                                                                   class="editAttendance"
                                                                   data-href="{{ route('admin.attendances.update', $firstAttendance->attendance_id) }}"
                                                                   data-in="{{ date('H:i', strtotime($firstAttendance->check_in_at)) }}"
                                                                   data-out="{{ $firstAttendance->check_out_at ? date('H:i', strtotime($firstAttendance->check_out_at)) : null }}"
                                                                   data-remark="{{ $firstAttendance->edit_remark }}"
                                                                   data-date="{{ $filterParameter['attendance_date'] }}"
                                                                   data-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                                   title="{{ __('index.edit_attendance_time') }}"
                                                                >
                                                                    <i class="link-icon"
                                                                       data-feather="edit"></i>
                                                                </a>
                                                            </li>
                                                        @endcan

                                                        @can('attendance_delete')
                                                            <li class="me-2">
                                                                <a class="deleteAttendance" href="{{ route('admin.attendance.delete', $firstAttendance->attendance_id) }}">
                                                                    <i class="link-icon"  data-feather="delete"></i>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                            @if($attendanceNote)
                                                                <li class="me-2">
                                                                    <a href="#"
                                                                       class="noteLink"
                                                                       data-checkout_note="{{ $firstAttendance->check_out_note }}"
                                                                       data-checkin_note="{{ $firstAttendance->check_in_note }}">
                                                                        Note
                                                                    </a>
                                                                </li>
                                                            @endif
                                                    @endif

                                                    @if($canAddAttendanceForSelectedDate)
                                                        @can('attendance_create')
                                                            <li class="me-2">
                                                                <a href=""
                                                                   class="addEmployeeAttendance"
                                                                   data-href="{{ route('admin.attendances.store') }}"
                                                                   data-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                                   data-date="{{ $filterParameter['attendance_date'] }}"
                                                                   data-cdate="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $filterParameter['attendance_date']) }}"
                                                                   data-user_id="{{ $firstAttendance->user_id }}"
                                                                   title="{{ __('index.add_attendance_time') }}">
                                                                    <i class="link-icon" data-feather="plus-circle"></i>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    @endif

                                                </ul>
                                            </td>
                                        @endif
                                    @endcanany

                                </tr>

                                @empty
                                    <tr>
                                        <td colspan="100%">
                                            <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="attendance-summary-footer">
                            <div class="attendance-summary-item is-total {{ empty($filterParameter['status_filter']) || $filterParameter['status_filter'] === 'total_employee' ? 'is-active' : '' }}" data-summary-filter="total_employee" role="button" tabindex="0">
                                <div class="attendance-summary-icon"><i data-feather="users"></i></div>
                                <div class="attendance-summary-copy">
                                    <strong>{{ number_format($attendanceSummary['total_employee']) }}</strong>
                                    <span>{{ __('index.total_employee') }}</span>
                                </div>
                            </div>
                            <div class="attendance-summary-item is-check-in {{ ($filterParameter['status_filter'] ?? '') === 'total_check_in' ? 'is-active' : '' }}" data-summary-filter="total_check_in" role="button" tabindex="0">
                                <div class="attendance-summary-icon"><i data-feather="log-in"></i></div>
                                <div class="attendance-summary-copy">
                                    <strong>{{ number_format($attendanceSummary['total_check_in']) }}</strong>
                                    <span>{{ __('index.total_check_in') }}</span>
                                </div>
                            </div>
                            <div class="attendance-summary-item is-missing {{ ($filterParameter['status_filter'] ?? '') === 'total_not_yet_check_in' ? 'is-active' : '' }}" data-summary-filter="total_not_yet_check_in" role="button" tabindex="0">
                                <div class="attendance-summary-icon"><i data-feather="clock"></i></div>
                                <div class="attendance-summary-copy">
                                    <strong>{{ number_format($attendanceSummary['total_not_yet_check_in']) }}</strong>
                                    <span>{{ __('index.not_yet_check_in') }}</span>
                                </div>
                            </div>
                            <div class="attendance-summary-item is-check-out {{ ($filterParameter['status_filter'] ?? '') === 'total_check_out' ? 'is-active' : '' }}" data-summary-filter="total_check_out" role="button" tabindex="0">
                                <div class="attendance-summary-icon"><i data-feather="log-out"></i></div>
                                <div class="attendance-summary-copy">
                                    <strong>{{ number_format($attendanceSummary['total_check_out']) }}</strong>
                                    <span>{{ __('index.total_check_out') }}</span>
                                </div>
                            </div>
                            <div class="attendance-summary-item is-missing {{ ($filterParameter['status_filter'] ?? '') === 'total_not_yet_check_out' ? 'is-active' : '' }}" data-summary-filter="total_not_yet_check_out" role="button" tabindex="0">
                                <div class="attendance-summary-icon"><i data-feather="alert-circle"></i></div>
                                <div class="attendance-summary-copy">
                                    <strong>{{ number_format($attendanceSummary['total_not_yet_check_out']) }}</strong>
                                    <span>{{ __('index.not_yet_check_out') }}</span>
                                </div>
                            </div>
                            <div class="attendance-summary-item is-day-off {{ ($filterParameter['status_filter'] ?? '') === 'total_day_off' ? 'is-active' : '' }}" data-summary-filter="total_day_off" role="button" tabindex="0">
                                <div class="attendance-summary-icon"><i data-feather="coffee"></i></div>
                                <div class="attendance-summary-copy">
                                    <strong>{{ number_format($attendanceSummary['total_day_off']) }}</strong>
                                    <span>{{ __('index.total_day_off') }}</span>
                                </div>
                            </div>
                            <div class="attendance-summary-item is-leave {{ ($filterParameter['status_filter'] ?? '') === 'total_leave' ? 'is-active' : '' }}" data-summary-filter="total_leave" role="button" tabindex="0">
                                <div class="attendance-summary-icon"><i data-feather="calendar"></i></div>
                                <div class="attendance-summary-copy">
                                    <strong>{{ number_format($attendanceSummary['total_leave']) }}</strong>
                                    <span>{{ __('index.leave') }}</span>
                                </div>
                            </div>
                            <div class="attendance-summary-item is-time-leave {{ ($filterParameter['status_filter'] ?? '') === 'total_time_leave' ? 'is-active' : '' }}" data-summary-filter="total_time_leave" role="button" tabindex="0">
                                <div class="attendance-summary-icon"><i data-feather="watch"></i></div>
                                <div class="attendance-summary-copy">
                                    <strong>{{ number_format($attendanceSummary['total_time_leave']) }}</strong>
                                    <span>{{ __('index.time_leave') }}</span>
                                </div>
                            </div>
                            <div class="attendance-summary-item is-request {{ ($filterParameter['status_filter'] ?? '') === 'total_leave_request' ? 'is-active' : '' }}" data-summary-filter="total_leave_request" role="button" tabindex="0">
                                <div class="attendance-summary-icon"><i data-feather="file-text"></i></div>
                                <div class="attendance-summary-copy">
                                    <strong>{{ number_format($attendanceSummary['total_leave_request']) }}</strong>
                                    <span>{{ __('index.leave_request') }}</span>
                                </div>
                            </div>
                            <div class="attendance-summary-item is-request {{ ($filterParameter['status_filter'] ?? '') === 'total_time_leave_request' ? 'is-active' : '' }}" data-summary-filter="total_time_leave_request" role="button" tabindex="0">
                                <div class="attendance-summary-icon"><i data-feather="clipboard"></i></div>
                                <div class="attendance-summary-copy">
                                    <strong>{{ number_format($attendanceSummary['total_time_leave_request']) }}</strong>
                                    <span>{{ __('index.time_leave_request') }}</span>
                                </div>
                            </div>
                        </div>
                        @if(isset($attendancePaginator) && $attendancePaginator->hasPages())
                            <div class="mt-3">
                                {{ $attendancePaginator->links() }}
                            </div>
                        @endif

                </div>
            </div>
        </div>


        <div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <iframe id="iframeModalWindow" class="attendancelocation" height="500px" width="100%" src="" name="iframe_modal"></iframe>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.attendance.common.edit-attendance-form')
        @include('admin.attendance.common.create-attendance-form')
        @include('admin.attendance.common.edit-night-attendance-form')

        <div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-labelledby="profilePhotoModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="profilePhotoModalTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="profilePhotoPreview" src="" alt="profile" class="img-fluid rounded" style="max-height: 70vh; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="attendanceLeaveRequestModal" tabindex="-1" aria-labelledby="attendanceLeaveRequestModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-12 mb-2 pb-2 border-bottom">
                                    <label class="form-label fw-bold">{{ __('index.referred_by') }}</label>
                                    <p class="form-control border-0 p-0 fst-italic" style="height:inherit" id="attendanceLeaveReferredBy"></p>
                                </div>
                                <div class="col-lg-12 mb-2 pb-2 border-bottom">
                                    <label class="form-label fw-bold">{{ __('index.leave_reason') }}</label>
                                    <p class="form-control border-0 p-0 fst-italic" style="height:inherit" id="attendanceLeaveDescription"></p>
                                </div>
                                <div class="col-lg-12">
                                    <label class="form-label fw-bold">{{ __('index.admin_remark') }}</label>
                                    <p class="form-control border-0 p-0 fst-italic" style="height:inherit" id="attendanceLeaveAdminRemark"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="attendanceLeaveDetailModal" tabindex="-1" aria-labelledby="attendanceLeaveDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="attendanceLeaveDetailModalLabel">{{ __('index.leave_request_section') }}</h5>
                            <div class="text-muted small" id="attendanceLeaveDetailModalSubtitle"></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="attendanceLeaveDetailModalBody" class="attendance-leave-detail-list">
                            <p class="attendance-leave-detail-empty">Loading detail...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="attendanceLeaveStatusUpdate" tabindex="-1" aria-labelledby="attendanceLeaveStatusUpdate" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h5 class="modal-title" id="attendanceLeaveStatusUpdateTitle">{{ __('index.leave_request_section') }}</h5>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <form class="forms-sample" id="attendanceUpdateLeaveStatus" action="" method="post">
                                @csrf
                                @method('put')
                                <input type="hidden" name="redirect_back" value="1">
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        <label class="form-label">{{ __('index.leave_reason') }}</label>
                                        <div class="form-control bg-light" style="height: auto; min-height: 44px;" id="attendanceLeaveStatusReason">N/A</div>
                                    </div>

                                    <label for="attendanceLeaveStatus" class="form-label">{{ __('index.status') }} </label>
                                    <div class="col-lg-12 mb-3">
                                        <select class="form-select" id="attendanceLeaveStatus" name="status">
                                            <option value="{{ \App\Enum\LeaveStatusEnum::approved->value }}">{{ __('index.approve') }}</option>
                                            <option value="{{ \App\Enum\LeaveStatusEnum::rejected->value }}">{{ __('index.reject') }}</option>
                                        </select>
                                    </div>

                                    <label for="attendanceLeaveRemark" class="form-label">{{ __('index.admin_remark') }}</label>
                                    <div class="col-lg-12 mb-3">
                                        <textarea class="form-select" id="attendanceLeaveRemark" minlength="10" name="admin_remark" rows="3"></textarea>
                                    </div>
                                </div>

                                <div id="attendancePreviousApprovers" class="mb-3"></div>

                                <div class="text-start">
                                    <button type="submit" class="btn btn-primary btn-xs">{{ __('index.submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="attendanceQuickLeaveModal" tabindex="-1" aria-labelledby="attendanceQuickLeaveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="attendanceQuickLeaveModalLabel">Quick Leave</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('admin.attendances.quick-approved-leave') }}" method="post" id="attendanceQuickLeaveForm">
                            @csrf
                            <input type="hidden" name="user_id" id="attendanceQuickLeaveUserId">
                            <input type="hidden" name="attendance_date" id="attendanceQuickLeaveDate">

                            <div class="mb-3">
                                <label for="attendanceQuickLeaveType" class="form-label">Leave Type</label>
                                <select class="form-select" name="leave_type_id" id="attendanceQuickLeaveType" required>
                                    <option value="">Select leave type</option>
                                </select>
                                <small class="text-muted d-block mt-2" id="attendanceQuickLeaveHelpText">
                                    This will create an already approved leave for the selected attendance day.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="attendanceQuickLeaveReason" class="form-label">{{ __('index.leave_reason') }}</label>
                                <textarea class="form-control" name="reasons" id="attendanceQuickLeaveReason" rows="3" placeholder="Optional note"></textarea>
                            </div>

                            <div class="text-start">
                                <button type="submit" class="btn btn-primary btn-sm" id="attendanceQuickLeaveSubmit">
                                    Save as Approved Leave
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="attendanceQuickTimeLeaveModal" tabindex="-1" aria-labelledby="attendanceQuickTimeLeaveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="attendanceQuickTimeLeaveModalLabel">Quick Time Leave</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('admin.attendances.quick-approved-time-leave') }}" method="post" id="attendanceQuickTimeLeaveForm">
                            @csrf
                            <input type="hidden" name="user_id" id="attendanceQuickTimeLeaveUserId">
                            <input type="hidden" name="attendance_date" id="attendanceQuickTimeLeaveDate">

                            <div class="mb-3">
                                <label for="attendanceQuickTimeLeaveFrom" class="form-label">{{ __('index.from') }}</label>
                                <input type="time" class="form-control" name="leave_from" id="attendanceQuickTimeLeaveFrom" required>
                            </div>

                            <div class="mb-3">
                                <label for="attendanceQuickTimeLeaveTo" class="form-label">{{ __('index.to') }}</label>
                                <input type="time" class="form-control" name="leave_to" id="attendanceQuickTimeLeaveTo" required>
                                <small class="text-muted d-block mt-2" id="attendanceQuickTimeLeaveHelpText">
                                    This will create an already approved time leave for the selected attendance day.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="attendanceQuickTimeLeaveReason" class="form-label">{{ __('index.leave_reason') }}</label>
                                <textarea class="form-control" name="reasons" id="attendanceQuickTimeLeaveReason" rows="3" minlength="10" required placeholder="Required note"></textarea>
                            </div>

                            <div class="text-start">
                                <button type="submit" class="btn btn-primary btn-sm" id="attendanceQuickTimeLeaveSubmit">
                                    Save as Approved Time Leave
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- note for checkin and checkout -->
        <div id="noteModal" class="modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Attendance Notes</h5>
                    </div>
                    <div class="modal-body">
                        <p><strong>Check-in Note:</strong> <span id="checkinNote"></span></p>
                        <p><strong>Check-out Note:</strong> <span id="checkoutNote"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade attendance-chat-modal" id="attendanceChatModal" tabindex="-1" aria-labelledby="attendanceChatModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content attendance-chat-shell">
                    <div class="attendance-chat-header">
                        <div class="attendance-chat-person">
                            <div class="attendance-chat-avatar-wrap">
                                <img id="attendanceChatAvatar" src="{{ asset('assets/images/img.png') }}" alt="Employee avatar" class="attendance-chat-avatar">
                                <span id="attendanceChatStatus" class="attendance-chat-status"></span>
                            </div>
                            <div class="min-w-0">
                                <h5 id="attendanceChatModalLabel">Employee Chat</h5>
                                <p id="attendanceChatSubtitle">Open a conversation from attendance.</p>
                            </div>
                        </div>
                        <div class="attendance-chat-actions">
                            <span><i data-feather="phone"></i></span>
                            <span><i data-feather="video"></i></span>
                            <button type="button" data-bs-dismiss="modal" aria-label="Close">
                                <i data-feather="x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="attendance-chat-body">
                        <div id="attendanceChatThread"
                             class="attendance-chat-thread"
                             data-base-url="{{ route('admin.employee-chat.messages') }}">
                            <div class="chat-empty">Select an employee to start chatting.</div>
                        </div>
                    </div>
                    <div class="attendance-chat-footer">
                        @can('send_employee_chat')
                            <div class="attendance-chat-preview" id="attendanceChatPreview">
                                <img id="attendanceChatPreviewImage" src="" alt="Attachment preview">
                                <button type="button" class="attendance-chat-preview-remove" id="attendanceChatPreviewRemove" aria-label="Remove attachment">
                                    <i data-feather="x"></i>
                                </button>
                            </div>
                            <form id="attendanceChatForm" class="attendance-chat-form" action="{{ route('admin.employee-chat.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="employee_id" id="attendanceChatEmployeeId">
                                <label class="attendance-chat-attach">
                                    <i data-feather="paperclip"></i>
                                    <input type="file" name="attachment" id="attendanceChatAttachment">
                                </label>
                                <input type="text" class="attendance-chat-input" name="message" id="attendanceChatMessage" placeholder="Type your message">
                                <button type="submit" class="attendance-chat-send">Send</button>
                            </form>
                            <div class="attendance-chat-status-text" id="attendanceChatStatusText">You can send text, image, or voice files here. You can also paste a screenshot.</div>
                        @else
                            <div class="attendance-chat-status-text" id="attendanceChatStatusText">You have view access only. Chat sending is disabled for your role.</div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="attendance-scroll-shortcuts" id="attendanceScrollShortcuts">
            <button type="button"
                    class="attendance-scroll-shortcut"
                    id="attendanceScrollTop"
                    title="Go to top"
                    aria-label="Go to top">
                <i data-feather="arrow-up"></i>
            </button>
            <button type="button"
                    class="attendance-scroll-shortcut"
                    id="attendanceScrollBottom"
                    title="Go to bottom"
                    aria-label="Go to bottom">
                <i data-feather="arrow-down"></i>
            </button>
        </div>
    </section>

@endsection

@section('scripts')
    @include('admin.attendance.common.scripts')
    <script>
        $(document).ready(function () {
            const loadDepartments = async () => {

                const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
                const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
                const selectedBranchId = isAdmin ? $('#branch_id option:selected').val() : defaultBranchId;


                let departmentId = "{{  $filterParameter['department_id'] ?? '' }}";
                console.log(departmentId);
                $('#department_id').empty();
                if (selectedBranchId) {
                    $.ajax({
                        type: 'GET',
                        url: "{{ url('admin/departments/get-All-Departments') }}" + '/' + selectedBranchId,
                    }).done(function (response) {
                        if (!departmentId) {
                            $('#department_id').append('<option disabled  selected >{{ __('index.select_department') }}</option>');
                        }
                        response.data.forEach(function (data) {
                            $('#department_id').append('<option ' + ((data.id == departmentId) ? "selected" : '') + ' value="' + data.id + '" >' + data.dept_name + '</option>');
                        });
                    });
                }
            };

            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            if (isAdmin) {
                $('#branch_id').on('change', loadDepartments);
                $('#branch_id').trigger('change');
            } else {
                loadDepartments(); // Load directly for regular users
            }

        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
                new bootstrap.Tooltip(element);
            });

            const attendancePageLoader = document.getElementById('attendancePageLoader');
            const attendancePageLoaderText = document.getElementById('attendancePageLoaderText');
            let skipAttendanceUnloadLoader = false;
            const showAttendancePageLoader = (message = 'Please wait...') => {
                if (!attendancePageLoader) {
                    return;
                }

                if (attendancePageLoaderText) {
                    attendancePageLoaderText.textContent = message;
                }
                attendancePageLoader.classList.add('is-visible');
                attendancePageLoader.setAttribute('aria-hidden', 'false');
                document.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
                    button.disabled = true;
                });
            };

            const hideAttendancePageLoader = () => {
                if (!attendancePageLoader) {
                    return;
                }

                attendancePageLoader.classList.remove('is-visible');
                attendancePageLoader.setAttribute('aria-hidden', 'true');
                document.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
                    button.disabled = false;
                });
            };

            window.addEventListener('pageshow', hideAttendancePageLoader);
            window.addEventListener('beforeunload', () => {
                if (skipAttendanceUnloadLoader) {
                    return;
                }

                showAttendancePageLoader('Refreshing attendance...');
            });

            document.querySelectorAll('form').forEach((form) => {
                form.addEventListener('submit', () => {
                    if (form.id === 'attendanceChatForm') {
                        return;
                    }

                    showAttendancePageLoader('Saving changes...');
                });
            });

            document.querySelectorAll('a[href]').forEach((link) => {
                link.addEventListener('click', () => {
                    const href = link.getAttribute('href') || '';

                    if (
                        href === '#'
                        || href.startsWith('javascript:')
                        || link.id === 'checkIn'
                        || link.id === 'checkOut'
                        || link.target === '_blank'
                        || link.hasAttribute('download')
                        || link.classList.contains('showProfilePhoto')
                        || link.classList.contains('attendanceLeaveRequestUpdate')
                        || link.classList.contains('attendanceTimeLeaveRequestUpdate')
                        || link.classList.contains('quickApproveLeaveTrigger')
                        || link.classList.contains('quickApproveTimeLeaveTrigger')
                        || link.classList.contains('openAttendanceChat')
                        || link.classList.contains('checkLocation')
                        || link.classList.contains('editAttendance')
                        || link.classList.contains('editNightAttendance')
                        || link.classList.contains('addEmployeeAttendance')
                        || link.closest('#attendanceResultsBlock .pagination')
                    ) {
                        return;
                    }

                    showAttendancePageLoader('Loading...');
                });
            });

            const noteModal = new bootstrap.Modal(document.getElementById('noteModal'));
            const attendanceDaySearch = document.getElementById('attendanceDaySearch');
            const attendanceEntries = document.getElementById('attendanceEntries');
            const attendanceDayTable = document.getElementById('dataTableExample');
            const attendanceDayRows = attendanceDayTable
                ? Array.from(attendanceDayTable.querySelectorAll('tbody .attendance-day-row'))
                : [];
            const attendanceEmptyRow = attendanceDayTable
                ? attendanceDayTable.querySelector('tbody tr td[colspan]')
                : null;
            const attendanceScrollShortcuts = document.getElementById('attendanceScrollShortcuts');
            const attendanceScrollTopButton = document.getElementById('attendanceScrollTop');
            const attendanceScrollBottomButton = document.getElementById('attendanceScrollBottom');
            const attendanceSummaryItems = Array.from(document.querySelectorAll('.attendance-summary-item[data-summary-filter]'));
            const attendanceLeaveDetailModalElement = document.getElementById('attendanceLeaveDetailModal');
            const attendanceLeaveDetailModal = attendanceLeaveDetailModalElement ? new bootstrap.Modal(attendanceLeaveDetailModalElement) : null;
            const attendanceLeaveDetailModalLabel = document.getElementById('attendanceLeaveDetailModalLabel');
            const attendanceLeaveDetailModalSubtitle = document.getElementById('attendanceLeaveDetailModalSubtitle');
            const attendanceLeaveDetailModalBody = document.getElementById('attendanceLeaveDetailModalBody');
            let activeAttendanceSummaryFilter = null;
            let attendanceResultsLoading = false;
            let attendanceSearchTimer = null;
            let attendanceReloadProgressTimer = null;

            const escapeAttendanceHtml = (value) => {
                const div = document.createElement('div');
                div.textContent = value ?? '';
                return div.innerHTML;
            };

            const renderAttendanceLeaveDetails = (records) => {
                if (!attendanceLeaveDetailModalBody) {
                    return;
                }

                if (!records.length) {
                    attendanceLeaveDetailModalBody.innerHTML = '<p class="attendance-leave-detail-empty">No leave detail found.</p>';
                    return;
                }

                attendanceLeaveDetailModalBody.innerHTML = records.map((record) => `
                    <div class="attendance-leave-detail-card">
                        <div class="attendance-leave-detail-head">
                            <div>
                                <h6 class="attendance-leave-detail-title">${escapeAttendanceHtml(record.title)}</h6>
                                <p class="attendance-leave-detail-date">${escapeAttendanceHtml(record.date)}</p>
                            </div>
                            <span class="attendance-leave-detail-status">${escapeAttendanceHtml(record.status)}</span>
                        </div>
                        <div class="attendance-leave-detail-meta">
                            <div>
                                <span>{{ __('index.requested_by') }}</span>
                                <p>${escapeAttendanceHtml(record.requested_by)}</p>
                            </div>
                            <div>
                                <span>{{ __('index.employee_code') }}</span>
                                <p>${escapeAttendanceHtml(record.employee_code)}</p>
                            </div>
                            <div>
                                <span>{{ __('index.branch_name') }}</span>
                                <p>${escapeAttendanceHtml(record.branch)}</p>
                            </div>
                            <div>
                                <span>{{ __('index.department') }}</span>
                                <p>${escapeAttendanceHtml(record.department)}</p>
                            </div>
                            <div>
                                <span>{{ __('index.from') }}</span>
                                <p>${escapeAttendanceHtml(record.leave_from)}</p>
                            </div>
                            <div>
                                <span>{{ __('index.to') }}</span>
                                <p>${escapeAttendanceHtml(record.leave_to)}</p>
                            </div>
                            <div>
                                <span>{{ __('index.requested_date') }}</span>
                                <p>${escapeAttendanceHtml(record.requested_date)}</p>
                            </div>
                            <div>
                                <span>{{ __('index.duration') }}</span>
                                <p>${escapeAttendanceHtml(record.duration)}</p>
                            </div>
                            <div>
                                <span>{{ __('index.referred_by') }}</span>
                                <p>${escapeAttendanceHtml(record.referred_by)}</p>
                            </div>
                            <div>
                                <span>{{ __('index.leave_reason') }}</span>
                                <p>${escapeAttendanceHtml(record.reason)}</p>
                            </div>
                            <div>
                                <span>{{ __('index.admin_remark') }}</span>
                                <p>${escapeAttendanceHtml(record.admin_remark)}</p>
                            </div>
                        </div>
                        ${record.can_update ? `
                            <div class="attendance-leave-detail-actions">
                                <button type="button"
                                        class="btn btn-success btn-xs ${record.type === 'time_leave' ? 'attendanceTimeLeaveRequestUpdate' : 'attendanceLeaveRequestUpdate'}"
                                        data-href="${escapeAttendanceHtml(record.update_url)}"
                                        data-status="{{ \App\Enum\LeaveStatusEnum::approved->value }}"
                                        data-remark="${escapeAttendanceHtml(record.raw_admin_remark)}"
                                        data-reason="${escapeAttendanceHtml(record.reason)}"
                                        data-id="${escapeAttendanceHtml(record.id)}"
                                        data-label="${escapeAttendanceHtml(record.title)}">
                                    {{ __('index.approve') }}
                                </button>
                                <button type="button"
                                        class="btn btn-danger btn-xs ${record.type === 'time_leave' ? 'attendanceTimeLeaveRequestUpdate' : 'attendanceLeaveRequestUpdate'}"
                                        data-href="${escapeAttendanceHtml(record.update_url)}"
                                        data-status="{{ \App\Enum\LeaveStatusEnum::rejected->value }}"
                                        data-remark="${escapeAttendanceHtml(record.raw_admin_remark)}"
                                        data-reason="${escapeAttendanceHtml(record.reason)}"
                                        data-id="${escapeAttendanceHtml(record.id)}"
                                        data-label="${escapeAttendanceHtml(record.title)}">
                                    {{ __('index.reject') }}
                                </button>
                            </div>
                        ` : ''}
                    </div>
                `).join('');
            };

            document.addEventListener('click', function(e) {
                const noteLink = e.target.closest('.noteLink');
                if (!noteLink) {
                    return;
                }

                e.preventDefault();

                const checkinNote = noteLink.getAttribute('data-checkin_note');
                const checkoutNote = noteLink.getAttribute('data-checkout_note');

                document.getElementById('checkinNote').textContent = checkinNote || '';
                document.getElementById('checkoutNote').textContent = checkoutNote || '';

                noteModal.show();
            });

            const attachGeoRedirect = (anchor) => {
                anchor.addEventListener('click', function (e) {
                    const href = anchor.getAttribute('href');
                    if (!href || href === '#') return;

                    if (!navigator.geolocation) {
                        skipAttendanceUnloadLoader = true;
                        return; // fallback: normal navigation without coords
                    }

                    e.preventDefault();
                    anchor.classList.add('disabled');
                    anchor.setAttribute('aria-disabled', 'true');

                    navigator.geolocation.getCurrentPosition(
                        function (pos) {
                            const lat = pos.coords.latitude;
                            const long = pos.coords.longitude;
                            const url = new URL(href, window.location.origin);
                            url.searchParams.set('lat', String(lat));
                            url.searchParams.set('long', String(long));
                            skipAttendanceUnloadLoader = true;
                            window.location.href = url.toString();
                        },
                        function () {
                            skipAttendanceUnloadLoader = true;
                            window.location.href = href; // fallback if denied/error
                        },
                        { enableHighAccuracy: false, timeout: 3000, maximumAge: 60000 }
                    );
                });
            };

            document.querySelectorAll('a#checkIn, a#checkOut').forEach(attachGeoRedirect);

            document.addEventListener('click', function (event) {
                const element = event.target.closest('.showProfilePhoto');
                if (!element) {
                    return;
                }

                event.preventDefault();

                document.getElementById('profilePhotoPreview').setAttribute('src', element.getAttribute('data-src'));
                document.getElementById('profilePhotoModalTitle').innerText = element.getAttribute('data-name') || '';

                const modal = new bootstrap.Modal(document.getElementById('profilePhotoModal'));
                modal.show();
            });

            document.addEventListener('click', function (event) {
                const element = event.target.closest('.attendanceLeaveRequestUpdate');
                if (!element) {
                    return;
                }

                event.preventDefault();

                const url = element.getAttribute('data-href');
                const status = element.getAttribute('data-status');
                const remark = element.getAttribute('data-remark');
                const reason = element.getAttribute('data-reason');
                const leaveRequestId = element.getAttribute('data-id');
                const label = element.getAttribute('data-label') || '{{ __('index.leave_request_section') }}';

                document.getElementById('attendanceLeaveStatusUpdateTitle').textContent = label;
                document.getElementById('attendanceUpdateLeaveStatus').setAttribute('action', url);
                document.getElementById('attendanceLeaveStatus').value = status;
                document.getElementById('attendanceLeaveRemark').value = remark || '';
                document.getElementById('attendanceLeaveStatusReason').textContent = reason || 'N/A';
                document.getElementById('attendancePreviousApprovers').innerHTML = '';

                fetch(`/admin/leave-request/get-approvers/${leaveRequestId}`)
                    .then(response => response.json())
                    .then(response => {
                        if (!response.success) {
                            return;
                        }

                        let approversData = '';
                        response.data.approval_data.forEach(function (approver) {
                            approversData += `
                                    <div class="approver-details">
                                        <p><b>Approver:</b> ${approver.approved_by_name}</p>
                                        <p><b>Status:</b> ${approver.status}</p>
                                        <p><b>Remark:</b> ${approver.reason}</p>
                                    </div>
                                    <hr>`;
                        });

                        if (response.data.admin_data.status !== 'pending' && response.data.admin_data.remark !== '') {
                            approversData += `
                                    <div class="approver-details">
                                        <p><b>Status:</b> ${response.data.admin_data.status}</p>
                                        <p><b>Admin Remark:</b> ${response.data.admin_data.remark}</p>
                                    </div>`;
                        }

                        document.getElementById('attendancePreviousApprovers').innerHTML = approversData;
                    })
                    .catch(error => console.error('Error:', error));

                const modalElement = document.getElementById('attendanceLeaveStatusUpdate');
                const modal = new bootstrap.Modal(modalElement);
                attendanceLeaveDetailModal?.hide();
                modal.show();
            });

            document.addEventListener('click', function (event) {
                const element = event.target.closest('.attendanceTimeLeaveRequestUpdate');
                if (!element) {
                    return;
                }

                event.preventDefault();

                const url = element.getAttribute('data-href');
                const status = element.getAttribute('data-status');
                const remark = element.getAttribute('data-remark');
                const reason = element.getAttribute('data-reason');
                const label = element.getAttribute('data-label') || '{{ __('index.time_leave_request') }}';

                document.getElementById('attendanceLeaveStatusUpdateTitle').textContent = label;
                document.getElementById('attendanceUpdateLeaveStatus').setAttribute('action', url);
                document.getElementById('attendanceLeaveStatus').value = status;
                document.getElementById('attendanceLeaveRemark').value = remark || '';
                document.getElementById('attendanceLeaveStatusReason').textContent = reason || 'N/A';
                document.getElementById('attendancePreviousApprovers').innerHTML = '';

                const modalElement = document.getElementById('attendanceLeaveStatusUpdate');
                const modal = new bootstrap.Modal(modalElement);
                attendanceLeaveDetailModal?.hide();
                modal.show();
            });

            const applyAttendanceTableFilters = () => {
                const currentAttendanceDayTable = document.getElementById('dataTableExample');
                const currentAttendanceDayRows = currentAttendanceDayTable
                    ? Array.from(currentAttendanceDayTable.querySelectorAll('tbody .attendance-day-row'))
                    : [];
                const currentAttendanceEmptyRow = currentAttendanceDayTable
                    ? currentAttendanceDayTable.querySelector('tbody tr td[colspan]')
                    : null;

                if (!currentAttendanceDayTable) {
                    return;
                }

                currentAttendanceDayRows.forEach((row) => {
                    row.style.display = '';
                });

                if (currentAttendanceEmptyRow) {
                    currentAttendanceEmptyRow.parentElement.style.display = currentAttendanceDayRows.length === 0 ? '' : 'none';
                }
            };

            const setAttendanceReloadProgress = (block, percent) => {
                const safePercent = Math.max(0, Math.min(100, Math.round(percent)));
                const bar = block.querySelector('.attendance-reload-bar');
                const label = block.querySelector('.attendance-reload-percent');

                if (bar) {
                    bar.style.width = `${safePercent}%`;
                }

                if (label) {
                    label.textContent = `${safePercent}%`;
                }
            };

            const startAttendanceReloadProgress = (block) => {
                let progress = 0;
                block.classList.add('is-refreshing');
                block.querySelector('.attendance-results-reload')?.setAttribute('aria-hidden', 'false');
                setAttendanceReloadProgress(block, progress);
                clearInterval(attendanceReloadProgressTimer);
                attendanceReloadProgressTimer = setInterval(() => {
                    const remaining = 95 - progress;
                    progress += Math.max(1, Math.ceil(remaining * 0.16));
                    setAttendanceReloadProgress(block, Math.min(progress, 95));
                }, 220);
            };

            const stopAttendanceReloadProgress = (block) => {
                clearInterval(attendanceReloadProgressTimer);
                attendanceReloadProgressTimer = null;
                setAttendanceReloadProgress(block, 100);
            };

            const refreshAttendanceResultsBlock = async (url, pushState = true) => {
                const currentBlock = document.getElementById('attendanceResultsBlock');

                if (!currentBlock || attendanceResultsLoading) {
                    return;
                }

                attendanceResultsLoading = true;
                startAttendanceReloadProgress(currentBlock);

                try {
                    const response = await fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Unable to refresh attendance results.');
                    }

                    const html = await response.text();
                    const parsed = new DOMParser().parseFromString(html, 'text/html');
                    const nextBlock = parsed.getElementById('attendanceResultsBlock');

                    if (!nextBlock) {
                        window.location.href = url.toString();
                        return;
                    }

                    stopAttendanceReloadProgress(currentBlock);
                    await new Promise((resolve) => setTimeout(resolve, 160));
                    currentBlock.replaceWith(nextBlock);

                    if (pushState) {
                        window.history.pushState({}, '', url.toString());
                    }

                    activeAttendanceSummaryFilter = null;
                    applyAttendanceTableFilters();
                    updateAttendanceScrollShortcuts();

                    if (window.feather) {
                        feather.replace();
                    }

                    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
                        bootstrap.Tooltip.getOrCreateInstance(element);
                    });
                } catch (error) {
                    console.error(error);
                    window.location.href = url.toString();
                } finally {
                    attendanceResultsLoading = false;
                    const refreshedBlock = document.getElementById('attendanceResultsBlock');
                    if (refreshedBlock) {
                        refreshedBlock.classList.remove('is-refreshing');
                        refreshedBlock.querySelector('.attendance-results-reload')?.setAttribute('aria-hidden', 'true');
                    }
                }
            };

            document.addEventListener('input', (event) => {
                if (event.target && event.target.id === 'attendanceDaySearch') {
                    clearTimeout(attendanceSearchTimer);
                    attendanceSearchTimer = setTimeout(() => {
                        const url = new URL(window.location.href);
                        const searchValue = event.target.value.trim();

                        if (searchValue) {
                            url.searchParams.set('search', searchValue);
                        } else {
                            url.searchParams.delete('search');
                        }

                        url.searchParams.delete('page');
                        refreshAttendanceResultsBlock(url);
                    }, 350);
                }
            });

            document.addEventListener('change', (event) => {
                if (!event.target || event.target.id !== 'attendanceEntries') {
                    return;
                }

                const url = new URL(window.location.href);
                url.searchParams.set('per_page', event.target.value);
                url.searchParams.delete('page');
                refreshAttendanceResultsBlock(url);
            });

            document.addEventListener('click', (event) => {
                const resetButton = event.target.closest('#attendanceResetFilters');

                if (!resetButton) {
                    return;
                }

                event.preventDefault();
                clearTimeout(attendanceSearchTimer);

                const searchInput = document.getElementById('attendanceDaySearch');
                if (searchInput) {
                    searchInput.value = '';
                }

                const url = new URL(window.location.href);
                url.searchParams.delete('search');
                url.searchParams.delete('status_filter');
                url.searchParams.delete('per_page');
                url.searchParams.delete('page');

                refreshAttendanceResultsBlock(url);
            });

            document.addEventListener('click', (event) => {
                const paginationLink = event.target.closest('#attendanceResultsBlock .pagination a');

                if (!paginationLink) {
                    return;
                }

                event.preventDefault();
                refreshAttendanceResultsBlock(new URL(paginationLink.href, window.location.origin));
            });

            window.addEventListener('popstate', () => {
                refreshAttendanceResultsBlock(new URL(window.location.href), false);
            });

            document.addEventListener('click', async (event) => {
                const trigger = event.target.closest('.attendance-leave-detail-trigger');

                if (!trigger) {
                    return;
                }

                event.preventDefault();

                if (!attendanceLeaveDetailModal || !attendanceLeaveDetailModalBody) {
                    return;
                }

                const url = new URL(trigger.dataset.url, window.location.origin);
                url.searchParams.set('user_id', trigger.dataset.userId || '');
                url.searchParams.set('year', trigger.dataset.year || '');
                url.searchParams.set('month', trigger.dataset.month || '');
                url.searchParams.set('category', trigger.dataset.category || '');
                url.searchParams.set('date_in_bs', trigger.dataset.dateInBs || '0');

                if (attendanceLeaveDetailModalLabel) {
                    attendanceLeaveDetailModalLabel.textContent = trigger.dataset.label || '{{ __('index.leave_request_section') }}';
                }
                if (attendanceLeaveDetailModalSubtitle) {
                    attendanceLeaveDetailModalSubtitle.textContent = trigger.dataset.employeeName || '';
                }
                attendanceLeaveDetailModalBody.innerHTML = '<p class="attendance-leave-detail-empty">Loading detail...</p>';
                attendanceLeaveDetailModal.show();

                try {
                    const response = await fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });
                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to load leave detail.');
                    }

                    if (attendanceLeaveDetailModalSubtitle) {
                        attendanceLeaveDetailModalSubtitle.textContent = data.employee || trigger.dataset.employeeName || '';
                    }
                    renderAttendanceLeaveDetails(data.records || []);
                } catch (error) {
                    attendanceLeaveDetailModalBody.innerHTML = `<p class="attendance-leave-detail-empty">${escapeAttendanceHtml(error.message || 'Unable to load leave detail.')}</p>`;
                }
            });

            document.addEventListener('click', (event) => {
                const item = event.target.closest('.attendance-summary-item[data-summary-filter]');

                if (!item) {
                    return;
                }

                event.preventDefault();
                const nextFilter = item.dataset.summaryFilter || null;
                const url = new URL(window.location.href);

                if (!nextFilter || nextFilter === 'total_employee') {
                    url.searchParams.delete('status_filter');
                } else {
                    url.searchParams.set('status_filter', nextFilter);
                }

                url.searchParams.delete('page');
                refreshAttendanceResultsBlock(url);
            });

            document.addEventListener('keydown', (event) => {
                const item = event.target.closest('.attendance-summary-item[data-summary-filter]');

                if (!item || (event.key !== 'Enter' && event.key !== ' ')) {
                    return;
                }

                event.preventDefault();
                item.click();
            });

            const updateAttendanceScrollShortcuts = () => {
                if (!attendanceScrollShortcuts) {
                    return;
                }

                const scrollable = document.documentElement.scrollHeight > (window.innerHeight + 120);
                attendanceScrollShortcuts.classList.toggle('is-hidden', !scrollable);
            };

            if (attendanceScrollTopButton) {
                attendanceScrollTopButton.addEventListener('click', () => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            if (attendanceScrollBottomButton) {
                attendanceScrollBottomButton.addEventListener('click', () => {
                    window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' });
                });
            }

            window.addEventListener('resize', updateAttendanceScrollShortcuts);

            applyAttendanceTableFilters();
            updateAttendanceScrollShortcuts();

            const attendanceChatModalElement = document.getElementById('attendanceChatModal');
            const attendanceChatModal = attendanceChatModalElement ? new bootstrap.Modal(attendanceChatModalElement) : null;
            const attendanceChatThread = document.getElementById('attendanceChatThread');
            const attendanceChatForm = document.getElementById('attendanceChatForm');
            const attendanceChatEmployeeId = document.getElementById('attendanceChatEmployeeId');
            const attendanceChatAttachment = document.getElementById('attendanceChatAttachment');
            const attendanceChatPreview = document.getElementById('attendanceChatPreview');
            const attendanceChatPreviewImage = document.getElementById('attendanceChatPreviewImage');
            const attendanceChatPreviewRemove = document.getElementById('attendanceChatPreviewRemove');
            const attendanceChatAvatar = document.getElementById('attendanceChatAvatar');
            const attendanceChatTitle = document.getElementById('attendanceChatModalLabel');
            const attendanceChatSubtitle = document.getElementById('attendanceChatSubtitle');
            const attendanceChatStatus = document.getElementById('attendanceChatStatus');
            const attendanceChatStatusText = document.getElementById('attendanceChatStatusText');
            const attendanceQuickLeaveModalElement = document.getElementById('attendanceQuickLeaveModal');
            const attendanceQuickLeaveModal = attendanceQuickLeaveModalElement ? new bootstrap.Modal(attendanceQuickLeaveModalElement) : null;
            const attendanceQuickLeaveUserId = document.getElementById('attendanceQuickLeaveUserId');
            const attendanceQuickLeaveDate = document.getElementById('attendanceQuickLeaveDate');
            const attendanceQuickLeaveType = document.getElementById('attendanceQuickLeaveType');
            const attendanceQuickLeaveReason = document.getElementById('attendanceQuickLeaveReason');
            const attendanceQuickLeaveSubmit = document.getElementById('attendanceQuickLeaveSubmit');
            const attendanceQuickLeaveLabel = document.getElementById('attendanceQuickLeaveModalLabel');
            const attendanceQuickLeaveHelpText = document.getElementById('attendanceQuickLeaveHelpText');
            const attendanceQuickTimeLeaveModalElement = document.getElementById('attendanceQuickTimeLeaveModal');
            const attendanceQuickTimeLeaveModal = attendanceQuickTimeLeaveModalElement ? new bootstrap.Modal(attendanceQuickTimeLeaveModalElement) : null;
            const attendanceQuickTimeLeaveUserId = document.getElementById('attendanceQuickTimeLeaveUserId');
            const attendanceQuickTimeLeaveDate = document.getElementById('attendanceQuickTimeLeaveDate');
            const attendanceQuickTimeLeaveFrom = document.getElementById('attendanceQuickTimeLeaveFrom');
            const attendanceQuickTimeLeaveTo = document.getElementById('attendanceQuickTimeLeaveTo');
            const attendanceQuickTimeLeaveReason = document.getElementById('attendanceQuickTimeLeaveReason');
            const attendanceQuickTimeLeaveLabel = document.getElementById('attendanceQuickTimeLeaveModalLabel');
            const attendanceQuickTimeLeaveHelpText = document.getElementById('attendanceQuickTimeLeaveHelpText');
            let attendanceChatPoller = null;
            let activeAttendanceChatEmployeeId = null;

            const resetQuickLeaveOptions = (message = 'Loading leave types...') => {
                if (!attendanceQuickLeaveType) {
                    return;
                }

                attendanceQuickLeaveType.innerHTML = `<option value="">${message}</option>`;
                attendanceQuickLeaveType.disabled = true;
                if (attendanceQuickLeaveSubmit) {
                    attendanceQuickLeaveSubmit.disabled = true;
                }
            };

            document.addEventListener('click', function (event) {
                const element = event.target.closest('.quickApproveLeaveTrigger');
                if (!element) {
                    return;
                }

                    event.preventDefault();

                    if (!attendanceQuickLeaveModal) {
                        return;
                    }

                    const userId = element.getAttribute('data-user-id');
                    const userName = element.getAttribute('data-user-name');
                    const attendanceDate = element.getAttribute('data-attendance-date');
                    const displayDate = element.getAttribute('data-display-date');
                    const fetchUrl = element.getAttribute('data-fetch-url');

                    attendanceQuickLeaveUserId.value = userId;
                    attendanceQuickLeaveDate.value = attendanceDate;
                    attendanceQuickLeaveReason.value = '';
                    attendanceQuickLeaveLabel.textContent = `Quick Leave: ${userName}`;
                    attendanceQuickLeaveHelpText.textContent = `Create an already approved leave for ${displayDate}.`;

                    resetQuickLeaveOptions();
                    attendanceQuickLeaveModal.show();

                    fetch(fetchUrl)
                        .then(response => response.json())
                        .then(data => {
                            const leaveTypes = data.leaveTypes || data.leveTypes || [];

                            if (!leaveTypes.length) {
                                resetQuickLeaveOptions('No leave types available');
                                attendanceQuickLeaveHelpText.textContent = 'No leave types are available for this employee.';
                                return;
                            }

                            attendanceQuickLeaveType.disabled = false;
                            attendanceQuickLeaveType.innerHTML = '<option value="">Select leave type</option>';

                            leaveTypes.forEach((leaveType) => {
                                const option = document.createElement('option');
                                option.value = leaveType.id;
                                option.textContent = leaveType.name;
                                attendanceQuickLeaveType.appendChild(option);
                            });

                            const preferredType = leaveTypes.find((leaveType) => {
                                const typeName = String(leaveType.name || '').toLowerCase();
                                return typeName.includes('day off') || typeName.includes('ច្បាប់') || typeName.includes('leave');
                            });

                            attendanceQuickLeaveType.value = String(preferredType?.id || leaveTypes[0].id);
                            if (attendanceQuickLeaveSubmit) {
                                attendanceQuickLeaveSubmit.disabled = false;
                            }
                        })
                        .catch(() => {
                            resetQuickLeaveOptions('Unable to load leave types');
                            attendanceQuickLeaveHelpText.textContent = 'Unable to load leave types right now. Please try again.';
                        });
            });

            document.addEventListener('click', function (event) {
                const element = event.target.closest('.quickApproveTimeLeaveTrigger');
                if (!element) {
                    return;
                }

                    event.preventDefault();

                    if (!attendanceQuickTimeLeaveModal) {
                        return;
                    }

                    const userId = element.getAttribute('data-user-id');
                    const userName = element.getAttribute('data-user-name');
                    const attendanceDate = element.getAttribute('data-attendance-date');
                    const displayDate = element.getAttribute('data-display-date');

                    attendanceQuickTimeLeaveUserId.value = userId;
                    attendanceQuickTimeLeaveDate.value = attendanceDate;
                    attendanceQuickTimeLeaveFrom.value = '';
                    attendanceQuickTimeLeaveTo.value = '';
                    attendanceQuickTimeLeaveReason.value = '';
                    attendanceQuickTimeLeaveLabel.textContent = `Quick Time Leave: ${userName}`;
                    attendanceQuickTimeLeaveHelpText.textContent = `Create an already approved time leave for ${displayDate}.`;

                    attendanceQuickTimeLeaveModal.show();
            });

            const attendanceChatScrollToBottom = () => {
                if (attendanceChatThread) {
                    attendanceChatThread.scrollTop = attendanceChatThread.scrollHeight;
                }
            };

            const attendanceChatMessagesUrl = (employeeId) => {
                const url = new URL(attendanceChatThread.dataset.baseUrl, window.location.origin);
                url.searchParams.set('employee_id', employeeId);
                return url.toString();
            };

            const markAttendanceChatUnreadAsRead = (employeeId) => {
                document.querySelectorAll(`.attendance-profile-chat-badge[data-employee-id="${employeeId}"] .attendance-chat-unread-count`).forEach((badge) => {
                    badge.textContent = '0';
                    badge.classList.add('is-empty');
                });
            };

            const renderAttendanceChatMessages = async (employeeId, keepStatus = true) => {
                if (!attendanceChatThread || !employeeId) {
                    return;
                }

                try {
                    const response = await fetch(attendanceChatMessagesUrl(employeeId), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to load chat messages.');
                    }

                    attendanceChatThread.innerHTML = data.html;
                    markAttendanceChatUnreadAsRead(employeeId);
                    attendanceChatScrollToBottom();
                    if (!keepStatus && attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = 'Conversation loaded.';
                    }
                    if (window.feather) {
                        feather.replace();
                    }
                } catch (error) {
                    if (attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = error.message || 'Unable to load chat messages.';
                    }
                }
            };

            const stopAttendanceChatPolling = () => {
                if (attendanceChatPoller) {
                    clearInterval(attendanceChatPoller);
                    attendanceChatPoller = null;
                }
            };

            const startAttendanceChatPolling = (employeeId) => {
                stopAttendanceChatPolling();
                attendanceChatPoller = setInterval(() => {
                    if (activeAttendanceChatEmployeeId === employeeId) {
                        renderAttendanceChatMessages(employeeId);
                    }
                }, 5000);
            };

            const bindClipboardImagePaste = (target, fileInput, setStatus) => {
                if (!target || !fileInput) {
                    return;
                }

                target.addEventListener('paste', function (event) {
                    const items = event.clipboardData?.items || [];

                    for (const item of items) {
                        if (!item.type || !item.type.startsWith('image/')) {
                            continue;
                        }

                        const blob = item.getAsFile();
                        if (!blob) {
                            continue;
                        }

                        const extension = (blob.type.split('/')[1] || 'png').replace('jpeg', 'jpg');
                        const file = new File([blob], `pasted-screenshot-${Date.now()}.${extension}`, { type: blob.type });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        fileInput.files = dataTransfer.files;

                        if (typeof setStatus === 'function') {
                            setStatus(`Screenshot pasted: ${file.name}`);
                        }
                        event.preventDefault();
                        break;
                    }
                });
            };

            const showAttendanceChatPreview = (file) => {
                if (!attendanceChatPreview || !attendanceChatPreviewImage || !file || !file.type.startsWith('image/')) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    attendanceChatPreviewImage.src = event.target?.result || '';
                    attendanceChatPreview.classList.add('is-visible');
                    if (window.feather) {
                        feather.replace();
                    }
                };
                reader.readAsDataURL(file);
            };

            const clearAttendanceChatPreview = () => {
                if (attendanceChatAttachment) {
                    attendanceChatAttachment.value = '';
                }
                if (attendanceChatPreviewImage) {
                    attendanceChatPreviewImage.src = '';
                }
                if (attendanceChatPreview) {
                    attendanceChatPreview.classList.remove('is-visible');
                }
            };

            document.addEventListener('click', function (event) {
                const element = event.target.closest('.openAttendanceChat');
                if (!element) {
                    return;
                }

                    event.preventDefault();

                    const employeeId = element.getAttribute('data-employee-id');
                    if (!employeeId || !attendanceChatModal) {
                        return;
                    }

                    activeAttendanceChatEmployeeId = employeeId;
                    if (attendanceChatEmployeeId) {
                        attendanceChatEmployeeId.value = employeeId;
                    }
                    if (attendanceChatAvatar) {
                        attendanceChatAvatar.setAttribute('src', element.getAttribute('data-employee-avatar') || '{{ asset('assets/images/img.png') }}');
                    }
                    if (attendanceChatTitle) {
                        attendanceChatTitle.textContent = element.getAttribute('data-employee-name') || 'Employee Chat';
                    }
                    if (attendanceChatSubtitle) {
                        attendanceChatSubtitle.textContent = element.getAttribute('data-employee-subtitle') || 'Employee';
                    }
                    if (attendanceChatStatus) {
                        attendanceChatStatus.classList.toggle('online', element.getAttribute('data-employee-online') === '1');
                    }
                    if (attendanceChatThread) {
                        attendanceChatThread.innerHTML = '<div class="chat-empty">Loading conversation...</div>';
                    }
                    if (attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = 'Loading messages...';
                    }

                    attendanceChatModal.show();
                    renderAttendanceChatMessages(employeeId, false);
                    startAttendanceChatPolling(employeeId);
            });

            if (attendanceChatForm) {
                bindClipboardImagePaste(attendanceChatForm, attendanceChatAttachment, (message) => {
                    if (attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = message;
                    }
                    const file = attendanceChatAttachment.files?.[0];
                    if (file) {
                        showAttendanceChatPreview(file);
                    }
                });

                attendanceChatAttachment?.addEventListener('change', function () {
                    const file = this.files?.[0];
                    if (file && file.type.startsWith('image/')) {
                        showAttendanceChatPreview(file);
                        if (attendanceChatStatusText) {
                            attendanceChatStatusText.textContent = `Image ready: ${file.name}`;
                        }
                    } else {
                        clearAttendanceChatPreview();
                    }
                });

                attendanceChatPreviewRemove?.addEventListener('click', function () {
                    clearAttendanceChatPreview();
                    if (attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = 'Attachment removed.';
                    }
                });

                attendanceChatForm.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    if (!activeAttendanceChatEmployeeId) {
                        return;
                    }

                    if (attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = 'Sending message...';
                    }

                    try {
                        const response = await fetch(attendanceChatForm.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: new FormData(attendanceChatForm)
                        });
                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Unable to send message.');
                        }

                        attendanceChatThread.innerHTML = data.html;
                        attendanceChatForm.reset();
                        clearAttendanceChatPreview();
                        attendanceChatScrollToBottom();
                        if (attendanceChatStatusText) {
                            attendanceChatStatusText.textContent = 'Message sent successfully.';
                        }
                        if (window.feather) {
                            feather.replace();
                        }
                    } catch (error) {
                        if (attendanceChatStatusText) {
                            attendanceChatStatusText.textContent = error.message || 'Unable to send message right now.';
                        }
                    }
                });
            }

            if (attendanceChatModalElement) {
                attendanceChatModalElement.addEventListener('hidden.bs.modal', function () {
                    stopAttendanceChatPolling();
                    activeAttendanceChatEmployeeId = null;
                    if (attendanceChatThread) {
                        attendanceChatThread.innerHTML = '<div class="chat-empty">Select an employee to start chatting.</div>';
                    }
                    if (attendanceChatForm) {
                        attendanceChatForm.reset();
                    }
                    clearAttendanceChatPreview();
                    if (attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = @can('send_employee_chat')
                            'You can send text, image, or voice files here. You can also paste a screenshot.'
                        @else
                            'You have view access only. Chat sending is disabled for your role.'
                        @endcan;
                    }
                });
            }
        });
    </script>
@endsection
