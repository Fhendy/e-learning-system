// ===== DOM READY =====
$(document).ready(function() {
    
    // ===== SIDEBAR TOGGLE =====
    $('#sidebarToggle').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#sidebar').toggleClass('active');
        $('.sidebar-overlay').toggleClass('active');
        
        // Prevent body scroll when sidebar open on mobile
        if ($(window).width() < 992) {
            if ($('#sidebar').hasClass('active')) {
                $('body').css('overflow', 'hidden');
            } else {
                $('body').css('overflow', '');
            }
        }
    });
    
    // Close sidebar when clicking overlay
    $('.sidebar-overlay').click(function() {
        $('#sidebar').removeClass('active');
        $(this).removeClass('active');
        $('body').css('overflow', '');
    });
    
    // Close sidebar with close button
    $('.sidebar-close').click(function() {
        $('#sidebar').removeClass('active');
        $('.sidebar-overlay').removeClass('active');
        $('body').css('overflow', '');
    });
    
    // Close sidebar on escape key
    $(document).keydown(function(e) {
        if (e.key === 'Escape') {
            $('#sidebar').removeClass('active');
            $('.sidebar-overlay').removeClass('active');
            $('body').css('overflow', '');
        }
    });
    
    // ===== DROPDOWN FIX - REINITIALIZE BOOTSTRAP DROPDOWNS =====
    // Initialize all dropdowns with proper settings
    const dropdownElements = document.querySelectorAll('.dropdown-toggle');
    dropdownElements.forEach(element => {
        // Destroy existing instance if any
        const existingInstance = bootstrap.Dropdown.getInstance(element);
        if (existingInstance) {
            existingInstance.dispose();
        }
        // Create new instance
        new bootstrap.Dropdown(element, {
            autoClose: true,
            boundary: 'viewport'
        });
    });
    
    // Close dropdowns when clicking outside (additional safety)
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').removeClass('show');
            $('.dropdown-toggle').attr('aria-expanded', 'false');
        }
    });
    
    // Prevent dropdown from closing when clicking inside
    $('.dropdown-menu').on('click', function(e) {
        e.stopPropagation();
    });
    
    // Fix dropdown on mobile - reposition if needed
    $(window).on('resize', function() {
        $('.dropdown-menu.show').each(function() {
            const toggle = $(this).siblings('.dropdown-toggle')[0];
            if (toggle) {
                const instance = bootstrap.Dropdown.getInstance(toggle);
                if (instance) instance.update();
            }
        });
    });
    
    // ===== AUTO-HIDE ALERTS =====
    setTimeout(function() {
        $('.alert').each(function() {
            const alert = $(this);
            setTimeout(function() {
                alert.fadeTo(500, 0, function() {
                    alert.remove();
                });
            }, 5000);
        });
    }, 1000);
    
    // Close alert manually
    $('.alert .btn-close').on('click', function() {
        $(this).closest('.alert').fadeOut(300, function() {
            $(this).remove();
        });
    });
    
    // ===== LOADING SPINNER =====
    // Create loading spinner if not exists
    if ($('#loadingSpinner').length === 0) {
        $('body').append(`
            <div id="loadingSpinner" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2 text-primary">Memproses...</div>
            </div>
        `);
    }
    
    // Show spinner on AJAX start
    $(document).ajaxStart(function() {
        $('#loadingSpinner').fadeIn(200);
    });
    
    $(document).ajaxStop(function() {
        $('#loadingSpinner').fadeOut(200);
    });
    
    // ===== FORM SUBMISSION LOADING =====
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        if (submitBtn.length && !submitBtn.data('loading')) {
            submitBtn.data('loading', true);
            const originalText = submitBtn.html();
            submitBtn.data('original-text', originalText);
            submitBtn.prop('disabled', true);
            submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...');
            
            // Re-enable after 30 seconds (fallback)
            setTimeout(function() {
                if (submitBtn.data('loading')) {
                    submitBtn.prop('disabled', false);
                    submitBtn.html(submitBtn.data('original-text'));
                    submitBtn.data('loading', false);
                }
            }, 30000);
        }
    });
    
    // ===== MOBILE DETECTION =====
    function checkMobile() {
        if ($(window).width() < 992) {
            $('body').addClass('mobile-view');
            // Close sidebar on mobile by default
            $('#sidebar').removeClass('active');
            $('.sidebar-overlay').removeClass('active');
        } else {
            $('body').removeClass('mobile-view');
            // Keep sidebar open on desktop
            $('#sidebar').addClass('active');
            $('.sidebar-overlay').removeClass('active');
            $('body').css('overflow', '');
        }
    }
    
    // Initial check and on resize
    checkMobile();
    $(window).resize(function() {
        checkMobile();
        // Close dropdowns on resize to avoid position issues
        $('.dropdown-menu').removeClass('show');
        $('.dropdown-toggle').attr('aria-expanded', 'false');
    });
    
    // ===== ACTIVE SIDEBAR ITEM HIGHLIGHTING =====
    function highlightActiveMenu() {
        const currentPath = window.location.pathname;
        
        $('.sidebar-link').each(function() {
            const linkPath = $(this).attr('href');
            if (linkPath && linkPath !== '/') {
                if (currentPath === linkPath || (linkPath !== '/' && currentPath.startsWith(linkPath))) {
                    $(this).addClass('active');
                    // Expand parent if exists
                    $(this).closest('.sidebar-item').addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            }
        });
    }
    
    highlightActiveMenu();
    
    // ===== TOOLTIP INITIALIZATION =====
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // ===== POPOVER INITIALIZATION =====
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.forEach(function(popoverTriggerEl) {
        new bootstrap.Popover(popoverTriggerEl);
    });
    
    // ===== TABLE RESPONSIVE FIX =====
    $('.table-responsive').each(function() {
        if ($(this).find('table').width() > $(this).width()) {
            $(this).css('overflow-x', 'auto');
        }
    });
    
    // ===== CONFIRM DELETE =====
    $('.delete-confirm').on('click', function(e) {
        if (!confirm('Apakah Anda yakin ingin menghapus data ini?\n\nTindakan ini tidak dapat dibatalkan!')) {
            e.preventDefault();
            return false;
        }
    });
    
    // ===== PASSWORD TOGGLE =====
    $('.toggle-password').on('click', function() {
        const input = $($(this).attr('data-target'));
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).find('i').toggleClass('bi-eye bi-eye-slash');
    });
    
    // ===== DATE PICKER FALLBACK =====
    $('input[type="date"]').each(function() {
        if (!$(this).val()) {
            const today = new Date().toISOString().split('T')[0];
            $(this).attr('placeholder', 'YYYY-MM-DD');
        }
    });
    
    // ===== AUTO REFRESH FOR ACTIVE QR CODES =====
    let refreshInterval;
    function startAutoRefresh() {
        if ($('.status-badge.active').length > 0 && !refreshInterval) {
            refreshInterval = setInterval(function() {
                if (!document.hidden) {
                    location.reload();
                }
            }, 30000);
        } else if ($('.status-badge.active').length === 0 && refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }
    
    // Start auto refresh if there are active items
    startAutoRefresh();
    
    // Stop refresh when page hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden && refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        } else if (!document.hidden) {
            startAutoRefresh();
        }
    });
    
    // ===== LOGOUT CONFIRMATION =====
    $('.logout-btn, .sidebar-link.text-danger').on('click', function(e) {
        if (!confirm('Apakah Anda yakin ingin keluar?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // ===== FIX FOR MODAL BACKDROP =====
    $(document).on('show.bs.modal', '.modal', function() {
        $('.modal-backdrop').css('z-index', '1040');
        $(this).css('z-index', '1050');
    });
    
    // ===== PRINT FUNCTION =====
    $('.btn-print').on('click', function() {
        window.print();
    });
    
    // ===== COPY TO CLIPBOARD =====
    $('.copy-to-clipboard').on('click', function() {
        const text = $(this).data('copy-text') || $(this).text();
        navigator.clipboard.writeText(text).then(function() {
            alert('Berhasil disalin!');
        });
    });
    
    console.log('App.js loaded successfully');
});

// ===== WINDOW LOAD =====
$(window).on('load', function() {
    // Fix any layout issues
    $('body').removeClass('preload');
    
    // Ensure all images are loaded
    $('img').each(function() {
        if (!this.complete) {
            $(this).on('load', function() {
                $(this).fadeIn(200);
            });
        }
    });
});

// ===== CUSTOM FUNCTIONS =====
// Show toast notification
window.showToast = function(type, message, duration = 3000) {
    const toast = $(`
        <div class="toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3" role="alert" style="z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);
    
    $('body').append(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    setTimeout(function() {
        toast.remove();
    }, duration);
};

// Show confirmation dialog
window.showConfirm = function(message, callback) {
    if (confirm(message)) {
        callback();
    }
};

// Format date
window.formatDate = function(date, format = 'd/m/Y') {
    const d = new Date(date);
    const day = d.getDate().toString().padStart(2, '0');
    const month = (d.getMonth() + 1).toString().padStart(2, '0');
    const year = d.getFullYear();
    const hours = d.getHours().toString().padStart(2, '0');
    const minutes = d.getMinutes().toString().padStart(2, '0');
    
    if (format === 'd/m/Y H:i') {
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    }
    return `${day}/${month}/${year}`;
};