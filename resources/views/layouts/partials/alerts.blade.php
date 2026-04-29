@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <div class="alert-content">
            <div class="alert-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="alert-message">
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="alert-content">
            <div class="alert-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="alert-message">
                {{ session('error') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <div class="alert-content">
            <div class="alert-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="alert-message">
                {{ session('warning') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <div class="alert-content">
            <div class="alert-icon">
                <i class="bi bi-info-circle-fill"></i>
            </div>
            <div class="alert-message">
                {{ session('info') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="alert-content">
            <div class="alert-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="alert-message">
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

<style>
/* Alerts Styles - Warna putih konsisten */
.alert {
    border: none;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    animation: slideInRight 0.3s ease-out;
    position: relative;
}

.alert-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-icon {
    flex-shrink: 0;
}

.alert-icon i {
    font-size: 1.25rem;
}

.alert-message {
    flex: 1;
    font-size: 0.875rem;
    line-height: 1.5;
}

.alert-message ul {
    padding-left: 1.25rem;
    margin-top: 0.25rem;
}

.alert-message li {
    margin-bottom: 0.25rem;
}

/* Success Alert */
.alert-success {
    background: #f0fdf4;
    border-left: 4px solid #22c55e;
    color: #166534;
}

.alert-success .alert-icon i {
    color: #22c55e;
}

/* Danger Alert */
.alert-danger {
    background: #fef2f2;
    border-left: 4px solid #ef4444;
    color: #991b1b;
}

.alert-danger .alert-icon i {
    color: #ef4444;
}

/* Warning Alert */
.alert-warning {
    background: #fffbeb;
    border-left: 4px solid #f59e0b;
    color: #92400e;
}

.alert-warning .alert-icon i {
    color: #f59e0b;
}

/* Info Alert */
.alert-info {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    color: #1e40af;
}

.alert-info .alert-icon i {
    color: #3b82f6;
}

/* Close Button */
.alert .btn-close {
    width: 0.75rem;
    height: 0.75rem;
    padding: 0.5rem;
    margin: -0.5rem -0.5rem -0.5rem auto;
    background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%236b7280'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e") center/0.75rem auto no-repeat;
    opacity: 0.5;
    transition: opacity 0.2s ease;
}

.alert .btn-close:hover {
    opacity: 1;
}

/* Animations */
@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.alert.fade {
    transition: all 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
    .alert {
        padding: 0.875rem 1rem;
    }
    
    .alert-content {
        gap: 0.625rem;
    }
    
    .alert-icon i {
        font-size: 1.125rem;
    }
    
    .alert-message {
        font-size: 0.813rem;
    }
}

@media (max-width: 576px) {
    .alert {
        padding: 0.75rem 0.875rem;
    }
    
    .alert-content {
        gap: 0.5rem;
    }
    
    .alert-icon i {
        font-size: 1rem;
    }
    
    .alert-message {
        font-size: 0.75rem;
    }
}

/* Print */
@media print {
    .alert {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(function(alert) {
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            if (alert && alert.parentNode) {
                alert.style.transition = 'opacity 0.5s, transform 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(30px)';
                setTimeout(function() {
                    if (alert.parentNode) alert.remove();
                }, 500);
            }
        }, 5000);
        
        // Close button handler
        const closeBtn = alert.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                alert.style.transition = 'opacity 0.3s, transform 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(30px)';
                setTimeout(function() {
                    if (alert.parentNode) alert.remove();
                }, 300);
            });
        }
    });
});
</script>