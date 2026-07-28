<style>
    .hold-history-panel {
        --hold-accent: #6d28d9;
        --hold-accent-light: #ede9fe;
        --hold-accent-muted: #a78bfa;
    }

    .hold-history-summary {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        padding: 0.85rem 1rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
        border: 1px solid #ddd6fe;
        border-radius: 8px;
    }

    .hold-history-summary-item {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .hold-history-summary-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--hold-accent);
    }

    .hold-history-summary-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-top: 2px;
    }

    .hold-history-summary-divider {
        width: 1px;
        height: 2rem;
        background: #c4b5fd;
        align-self: center;
    }

    .hold-history-summary-active {
        flex-direction: row;
        align-items: center;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--hold-accent);
    }

    .hold-history-timeline {
        position: relative;
    }

    .hold-history-entry {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 0;
        padding-bottom: 1.25rem;
    }

    .hold-history-entry:last-child {
        padding-bottom: 0;
    }

    .hold-history-marker {
        position: relative;
        flex-shrink: 0;
        width: 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 0.35rem;
    }

    .hold-history-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid var(--hold-accent-muted);
        z-index: 1;
    }

    .hold-history-entry--active .hold-history-dot {
        border-color: var(--hold-accent);
        box-shadow: 0 0 0 4px rgba(109, 40, 217, 0.2);
    }

    .hold-history-line {
        flex: 1;
        width: 2px;
        min-height: 1rem;
        margin-top: 4px;
        background: linear-gradient(180deg, #c4b5fd, #e9e5ff);
        border-radius: 1px;
    }

    .hold-history-content {
        flex: 1;
        min-width: 0;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.85rem 1rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .hold-history-entry--active .hold-history-content {
        border-color: #c4b5fd;
        background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%);
        box-shadow: 0 2px 8px rgba(109, 40, 217, 0.08);
    }

    .hold-history-content-header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.65rem;
    }

    .hold-history-dates {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        font-size: 0.8rem;
    }

    .hold-history-date-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        background: #f3f4f6;
        color: #374151;
        white-space: nowrap;
    }

    .hold-history-date-chip--active {
        background: var(--hold-accent-light);
        color: var(--hold-accent);
        font-weight: 600;
    }

    .hold-history-arrow {
        font-size: 0.7rem;
    }

    .hold-history-duration {
        background: #f3f4f6 !important;
        color: #4b5563 !important;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }

    .hold-history-entry--active .hold-history-duration {
        background: var(--hold-accent) !important;
        color: #fff !important;
    }

    .hold-history-reason {
        font-size: 0.875rem;
        color: #374151;
        margin-bottom: 0.5rem;
        padding-left: 0.65rem;
        border-left: 3px solid #e5e7eb;
    }

    .hold-history-reason--hold {
        border-left-color: var(--hold-accent-muted);
    }

    .hold-history-reason--release {
        border-left-color: #38bdf8;
    }

    .hold-history-reason-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #9ca3af;
        margin-bottom: 0.15rem;
    }

    .hold-history-actors {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem 0.5rem;
        font-size: 0.8rem;
        color: #6b7280;
        margin-top: 0.35rem;
        padding-top: 0.5rem;
        border-top: 1px dashed #e5e7eb;
    }

    .hold-history-actor {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .hold-history-actor-sep {
        color: #d1d5db;
    }

    .hold-history-active-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.5rem;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--hold-accent);
        background: var(--hold-accent-light);
        padding: 0.25rem 0.5rem;
        border-radius: 999px;
    }

    .fptk-card.hold-history-card .card-head {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: #fff;
    }

    .fptk-card.hold-history-card .card-head h2 {
        color: #fff;
    }

    .fptk-card.hold-history-card .card-head h2 i {
        opacity: 0.95;
    }

    .card.hold-history-card-outline {
        border-top: 3px solid #6d28d9;
        box-shadow: 0 2px 12px rgba(109, 40, 217, 0.08);
    }

    .card.hold-history-card-outline > .card-header {
        background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);
        border-bottom: 1px solid #ede9fe;
    }

    .card.hold-history-card-outline .card-title {
        color: #5b21b6;
        font-weight: 600;
    }

    .badge-hold-status {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: #fff;
    }
</style>
