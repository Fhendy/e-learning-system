// DOM Ready
$(document).ready(function() {
    // Sidebar Toggle
    $('#sidebarToggle').click(function() {
        $('#sidebar').toggleClass('active');
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeTo(500, 0).slideUp(500, function() {
            $(this).remove();
        });
    }, 5000);

    // Close alert manually
    $('.alert .btn-close').click(function() {
        $(this).closest('.alert').fadeOut();
    });

    // Loading spinner for AJAX requests
    $(document).ajaxStart(function() {
        $('#loadingSpinner').fadeIn();
    });

    $(document).ajaxStop(function() {
        $('#loadingSpinner').fadeOut();
    });

    // Form submissions loading
    $('form').submit(function() {
        const submitBtn = $(this).find('button[type="submit"]');
        if (submitBtn.length) {
            submitBtn.prop('disabled', true);
            submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');
        }
    });

    // Mobile detection and adjustments
    function checkMobile() {
        if ($(window).width() < 992) {
            $('body').addClass('mobile-view');
            $('#sidebar').removeClass('active');
        } else {
            $('body').removeClass('mobile-view');
            $('#sidebar').addClass('active');
        }
    }

    // Initial check and on resize
    checkMobile();
    $(window).resize(checkMobile);

    // Active sidebar item highlighting
    function highlightActiveMenu() {
        const currentPath = window.location.pathname;
        
        $('.sidebar-link').each(function() {
            const linkPath = $(this).attr('href');
            if (linkPath && currentPath.startsWith(linkPath) && linkPath !== '/') {
                $(this).addClass('active');
            }
        });
    }

    highlightActiveMenu();

    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Esc to close sidebar on mobile
        if (e.key === 'Escape') {
            if ($(window).width() < 992 && $('#sidebar').hasClass('active')) {
                $('#sidebar').removeClass('active');
            }
        }
    });
});