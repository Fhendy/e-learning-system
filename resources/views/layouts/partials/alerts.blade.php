<div class="alerts-wrapper container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="alert-content">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="alert-content">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="alert-content">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('warning') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <div class="alert-content">
                <i class="bi bi-info-circle-fill me-2"></i>
                {{ session('info') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="alert-content">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
</div>

<style>
.alerts-wrapper {
    margin-bottom: 1.5rem;
}

.alert {
    border: none;
    border-radius: var(--border-radius);
    padding: 1rem 1.5rem;
    margin-bottom: 0.5rem;
    box-shadow: var(--shadow);
}

.alert-content {
    display: flex;
    align-items: flex-start;
}

.alert-success {
    background: linear-gradient(135deg, #d1e7dd, #a3cfbb);
    color: #0f5132;
    border-left: 4px solid var(--success-color);
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f1aeb5);
    color: #721c24;
    border-left: 4px solid var(--danger-color);
}

.alert-warning {
    background: linear-gradient(135deg, #fff3cd, #ffe69c);
    color: #856404;
    border-left: 4px solid var(--warning-color);
}

.alert-info {
    background: linear-gradient(135deg, #cfe2ff, #9ec5fe);
    color: #084298;
    border-left: 4px solid var(--info-color);
}

.btn-close {
    opacity: 0.7;
    transition: opacity var(--transition-speed);
}

.btn-close:hover {
    opacity: 1;
}
</style>