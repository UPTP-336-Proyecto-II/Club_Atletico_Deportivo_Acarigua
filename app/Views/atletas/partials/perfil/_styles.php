<style>
    .profile-tabs .tab-btn {
        padding: 16px 24px;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        color: var(--color-text-muted);
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .profile-tabs .tab-btn:hover {
        color: var(--color-primary);
    }

    .profile-tabs .tab-btn.active {
        color: var(--color-primary);
        border-bottom-color: var(--color-primary);
        font-weight: 600;
    }

    @media (max-width: 900px) {
        .show-layout {
            grid-template-columns: 1fr !important;
        }
    }

    @media print {

        .page-header .btn,
        .profile-tabs {
            display: none !important;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #ccc !important;
        }

        .tab-content {
            display: block !important;
            page-break-inside: avoid;
            margin-bottom: 30px;
        }
    }

    /* Calendario Interactivo Asistencias */
    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        position: relative;
    }
    .calendar-day.empty {
        background: transparent;
        cursor: default;
    }
    .calendar-day:not(.empty) {
        background: rgba(0,0,0,0.02);
    }
    .calendar-day:not(.empty):hover {
        background: var(--color-bg);
        border-color: var(--color-border);
        transform: scale(1.05);
        z-index: 10;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .calendar-day.today {
        border-color: var(--color-primary);
        font-weight: 700;
        color: var(--color-primary);
    }
    .calendar-day .day-num {
        z-index: 1;
    }
    .calendar-day .status-dots-container {
        position: absolute;
        bottom: 4px;
        display: flex;
        gap: 2px;
        justify-content: center;
        width: 100%;
    }
    .calendar-day .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }
    .calendar-day .status-dot.presente { background: #10B981; }
    .calendar-day .status-dot.ausente { background: #EF4444; }
    .calendar-day .status-dot.justificado { background: #F59E0B; }
    .calendar-day .status-dot.partido { background: var(--color-primary); width: 8px; height: 8px; transform: translateY(-1px); }
</style>
