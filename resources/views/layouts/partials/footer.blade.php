<footer class="footer">
    <div class="container-fluid">
        <div class="footer-content">
            <div class="footer-left">
                <span class="copyright">&copy; {{ date('Y') }} E-Learning System. by Fhendy</span>
            </div>
            <div class="footer-right">
                <span class="version">v1.0.0</span>
            </div>
        </div>
    </div>
</footer>

<style>
.footer {
    background: white;
    border-top: 1px solid #f1f5f9;
    padding: 1rem 0;
    margin-top: auto;
    padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0));
}

.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.footer-left .copyright {
    color: var(--secondary-color);
    font-size: 0.875rem;
    line-height: 1.4;
}

.footer-right .version {
    color: var(--secondary-color);
    font-size: 0.875rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .footer {
        padding: 0.75rem 0;
        padding-bottom: calc(0.75rem + env(safe-area-inset-bottom, 0));
    }
    
    .footer-content {
        flex-direction: column;
        gap: 0.25rem;
        text-align: center;
        padding: 0 0.75rem;
    }
    
    .footer-left .copyright,
    .footer-right .version {
        font-size: 0.8125rem;
    }
}

/* Small Phones */
@media (max-width: 375px) {
    .footer-content {
        padding: 0 0.5rem;
    }
    
    .footer-left .copyright,
    .footer-right .version {
        font-size: 0.75rem;
    }
}

/* Phone Landscape */
@media (max-width: 767px) and (orientation: landscape) {
    .footer {
        padding: 0.5rem 0;
    }
    
    .footer-content {
        flex-direction: row;
        justify-content: space-between;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .footer {
        background: #1e293b;
        border-top-color: #334155;
    }
    
    .footer-left .copyright,
    .footer-right .version {
        color: #94a3b8;
    }
}

/* Print styles */
@media print {
    .footer {
        display: none;
    }
}
</style>