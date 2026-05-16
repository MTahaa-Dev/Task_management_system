/**
 * Main JavaScript File
 * Handles layout interactions, toasts, and UI logic
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Hide Preloader on Window Load
    window.addEventListener('load', function() {
        const preloader = document.getElementById('global-preloader');
        if(preloader) {
            preloader.style.opacity = '0';
            setTimeout(() => preloader.style.display = 'none', 500);
        }
    });

    // Sidebar Toggle Logic
    const sidebar = document.getElementById('sidebar');
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    
    if(sidebarCollapse && sidebar) {
        sidebarCollapse.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            
            // Adjust icon based on state
            const icon = this.querySelector('i');
            if(sidebar.classList.contains('collapsed')) {
                icon.classList.replace('bx-menu-alt-left', 'bx-menu');
            } else {
                icon.classList.replace('bx-menu', 'bx-menu-alt-left');
            }
        });
    }

    // Mobile Responsiveness for Sidebar
    function checkWidth() {
        if (window.innerWidth <= 768 && sidebar) {
            sidebar.classList.add('collapsed');
        } else if (sidebar && !sidebar.classList.contains('manual-collapsed')) {
            sidebar.classList.remove('collapsed');
        }
    }
    
    window.addEventListener('resize', checkWidth);
    checkWidth();

    // Dark Mode Toggle Logic
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        // Set initial icon based on current theme
        updateThemeIcon(document.documentElement.getAttribute('data-theme'));

        themeToggle.addEventListener('click', function() {
            let currentTheme = document.documentElement.getAttribute('data-theme');
            let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            updateThemeIcon(newTheme);
        });
    }

    function updateThemeIcon(theme) {
        if(!themeToggle) return;
        const icon = themeToggle.querySelector('i');
        if (theme === 'dark') {
            icon.classList.replace('bx-moon', 'bx-sun');
        } else {
            icon.classList.replace('bx-sun', 'bx-moon');
        }
    }
    
    // Initialize Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });

});

/**
 * Toast Notification Wrapper
 */
function showToast(message, type = 'success') {
    let bgColor = type === 'success' ? "linear-gradient(to right, #00b09b, #96c93d)" : "linear-gradient(to right, #ff5f6d, #ffc371)";
    if(type === 'info') bgColor = "linear-gradient(to right, #2193b0, #6dd5ed)";
    
    Toastify({
        text: message,
        duration: 3000,
        close: true,
        gravity: "top", // `top` or `bottom`
        position: "right", // `left`, `center` or `right`
        stopOnFocus: true, // Prevents dismissing of toast on hover
        style: {
            background: bgColor,
            borderRadius: "8px",
            boxShadow: "0 4px 6px -1px rgba(0, 0, 0, 0.1)",
            fontFamily: "'Inter', sans-serif"
        }
    }).showToast();
}

/**
 * Confirm Action using SweetAlert2
 */
function confirmAction(title, text, confirmUrl) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4F46E5',
        cancelButtonColor: '#EF4444',
        confirmButtonText: 'Yes, proceed!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = confirmUrl;
        }
    });
}
