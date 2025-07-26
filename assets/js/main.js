// Main JavaScript file for the Test Management System

// Initialize Lucide icons when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

// Utility functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${
        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
        type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' :
        type === 'warning' ? 'bg-yellow-100 border border-yellow-400 text-yellow-700' :
        'bg-blue-100 border border-blue-400 text-blue-700'
    }`;
    
    notification.innerHTML = `
        <div class="flex items-center">
            <div class="flex-1">${message}</div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Re-initialize icons for the new notification
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Confirm dialog utility
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Format time utility
function formatTime(seconds) {
    if (seconds < 60) {
        return seconds + 's';
    } else if (seconds < 3600) {
        return Math.floor(seconds / 60) + 'm ' + (seconds % 60) + 's';
    } else {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return hours + 'h ' + minutes + 'm ' + secs + 's';
    }
}

// Auto-save functionality for forms
function enableAutoSave(formId, interval = 30000) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    let autoSaveTimer;
    const inputs = form.querySelectorAll('input, textarea, select');
    
    function saveFormData() {
        const formData = new FormData(form);
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        localStorage.setItem('autosave_' + formId, JSON.stringify(data));
        showNotification('Form auto-saved', 'info');
    }
    
    function loadFormData() {
        const saved = localStorage.getItem('autosave_' + formId);
        if (saved) {
            const data = JSON.parse(saved);
            for (let key in data) {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) {
                    input.value = data[key];
                }
            }
        }
    }
    
    // Load saved data on page load
    loadFormData();
    
    // Set up auto-save on input changes
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(saveFormData, interval);
        });
    });
    
    // Clear auto-save data on successful form submission
    form.addEventListener('submit', () => {
        localStorage.removeItem('autosave_' + formId);
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S to save (prevent default browser save)
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.disabled) {
            submitBtn.click();
        }
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.fixed.inset-0:not(.hidden)');
        modals.forEach(modal => {
            const closeBtn = modal.querySelector('[onclick*="close"], [onclick*="Close"]');
            if (closeBtn) {
                closeBtn.click();
            }
        });
    }
});

// Smooth scrolling for anchor links
document.addEventListener('click', function(e) {
    if (e.target.matches('a[href^="#"]')) {
        e.preventDefault();
        const target = document.querySelector(e.target.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
});

// Loading state management
function setLoadingState(element, loading = true) {
    if (loading) {
        element.disabled = true;
        element.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i>Loading...';
    } else {
        element.disabled = false;
        // Restore original content (you might want to store this beforehand)
    }
    
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Form validation helpers
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validateRequired(value) {
    return value && value.trim().length > 0;
}

// Add form validation to all forms
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!validateRequired(field.value)) {
                    isValid = false;
                    field.classList.add('border-red-500');
                    field.classList.remove('border-gray-300');
                } else {
                    field.classList.remove('border-red-500');
                    field.classList.add('border-gray-300');
                }
                
                // Email validation
                if (field.type === 'email' && field.value && !validateEmail(field.value)) {
                    isValid = false;
                    field.classList.add('border-red-500');
                    field.classList.remove('border-gray-300');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields correctly.', 'error');
            }
        });
    });
});

// Mobile menu toggle (if needed)
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenu) {
        mobileMenu.classList.toggle('hidden');
    }
}

// Print functionality
function printPage() {
    window.print();
}

// Export functionality (basic CSV export)
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = Array.from(cols).map(col => {
            return '"' + col.textContent.replace(/"/g, '""') + '"';
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    
    window.URL.revokeObjectURL(url);
}

// Search functionality
function initializeSearch(inputId, targetSelector) {
    const searchInput = document.getElementById(inputId);
    if (!searchInput) return;
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const targets = document.querySelectorAll(targetSelector);
        
        targets.forEach(target => {
            const text = target.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                target.style.display = '';
            } else {
                target.style.display = 'none';
            }
        });
    });
}

// Initialize tooltips (if using a tooltip library)
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute z-50 px-2 py-1 text-sm text-white bg-gray-900 rounded shadow-lg';
            tooltip.textContent = this.getAttribute('data-tooltip');
            tooltip.style.top = this.offsetTop - 30 + 'px';
            tooltip.style.left = this.offsetLeft + 'px';
            document.body.appendChild(tooltip);
            
            this.addEventListener('mouseleave', function() {
                tooltip.remove();
            }, { once: true });
        });
    });
}

// Initialize all functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    
    // Show success/error messages from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('created')) {
        showNotification('Test created successfully!', 'success');
    }
    if (urlParams.get('updated')) {
        showNotification('Test updated successfully!', 'success');
    }
    if (urlParams.get('deleted')) {
        showNotification('Test deleted successfully!', 'success');
    }
});