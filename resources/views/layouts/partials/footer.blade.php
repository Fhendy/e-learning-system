<footer class="footer">
    <div class="container-fluid">
        <div class="footer-content">
            <div class="footer-left">
                <span class="copyright">
                    <i class="bi bi-c-circle me-1"></i>
                    {{ date('Y') }} E-Learning System. Developed by Fhendy
                </span>
            </div>
            <div class="footer-right">
                <span class="version">
                    <i class="bi bi-code-square me-1"></i>
                    v1.0.0
                </span>
            </div>
        </div>
    </div>
</footer>

<style>
/* Footer Styles - Warna putih konsisten */
.footer {
    background: #ffffff !important;
    border-top: 1px solid #e5e7eb;
    padding: 1rem 0;
    margin-top: auto;
    width: 100%;
    position: relative;
    bottom: 0;
    left: 0;
    right: 0;
}

.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.footer-left .copyright {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    color: #64748b;
    font-size: 0.813rem;
    line-height: 1.5;
}

.footer-right .version {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    color: #64748b;
    font-size: 0.813rem;
    background: #f8fafc;
    padding: 0.25rem 0.625rem;
    border-radius: 20px;
}

/* Hover Effects */
.footer-left .copyright:hover,
.footer-right .version:hover {
    color: #4f46e5;
    transition: color 0.2s ease;
}

/* Icons */
.footer-left .copyright i,
.footer-right .version i {
    font-size: 0.75rem;
    opacity: 0.7;
}

/* Responsive */
@media (max-width: 768px) {
    .footer {
        padding: 0.875rem 0;
    }
    
    .footer-content {
        flex-direction: column;
        justify-content: center;
        text-align: center;
        gap: 0.5rem;
    }
    
    .footer-left .copyright,
    .footer-right .version {
        font-size: 0.75rem;
    }
    
    .footer-right .version {
        padding: 0.25rem 0.5rem;
    }
}

@media (max-width: 576px) {
    .footer {
        padding: 0.75rem 0;
    }
    
    .footer-left .copyright,
    .footer-right .version {
        font-size: 0.688rem;
    }
    
    .footer-left .copyright i,
    .footer-right .version i {
        font-size: 0.688rem;
    }
}

/* Desktop */
@media (min-width: 992px) {
    .footer {
        padding: 1rem 0;
    }
    
    .footer-left .copyright,
    .footer-right .version {
        font-size: 0.875rem;
    }
}

/* Animation */
.footer {
    animation: fadeInUp 0.4s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Print */
@media print {
    .footer {
        display: none;
    }
}

/* Safe Area Support (Notch/iOS) */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .footer {
        padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update copyright year automatically
    const copyrightElements = document.querySelectorAll('.copyright');
    const currentYear = new Date().getFullYear();
    
    copyrightElements.forEach(function(el) {
        let text = el.innerHTML;
        text = text.replace(/\d{4}/, currentYear);
        el.innerHTML = text;
    });
});
</script>