// Admin Dashboard Functions

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeAdminDashboard();
});

function initializeAdminDashboard() {
    // Set up any event listeners or initial state
    console.log('Admin dashboard initialized');
    
    // Check for any notifications
    const notifications = getNotifications();
    if (notifications.length > 0) {
        showToast('You have ' + notifications.length + ' new notifications', 'info');
    }
}

// Toast notification system
window.showToast = function(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    
    toast.textContent = message;
    toast.className = 'toast show';
    
    // Add type class
    if (type) {
        toast.classList.add(type);
    }
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        toast.className = 'toast';
    }, 3000);
};

// Confirm action dialog
window.confirmAction = function(message, callback) {
    if (confirm(message)) {
        if (typeof callback === 'function') {
            callback();
        }
        return true;
    }
    return false;
};

// Logout function
window.logout = function() {
    if (confirmAction('Are you sure you want to logout?')) {
        // In Laravel, this would be handled by the form submission
        const logoutForm = document.querySelector('form[action*="logout"]');
        if (logoutForm) {
            logoutForm.submit();
        }
    }
};

// Navigation functions
window.changeView = function(view) {
    // In Laravel, this would be handled by route navigation
    // This is for SPA-like behavior if needed
    const viewElements = document.querySelectorAll('.view-content');
    viewElements.forEach(el => el.style.display = 'none');
    
    const targetView = document.getElementById(`view-${view}`);
    if (targetView) {
        targetView.style.display = 'block';
    }
    
    // Update active nav buttons
    const navButtons = document.querySelectorAll('.nav-btn');
    navButtons.forEach(btn => btn.classList.remove('active'));
    
    const activeButton = document.querySelector(`.nav-btn[data-view="${view}"]`);
    if (activeButton) {
        activeButton.classList.add('active');
    }
};

// Get notifications (mock function)
function getNotifications() {
    // This would typically make an API call
    return [];
}

// Initialize any dashboard charts or graphs
function initializeCharts() {
    // Initialize any chart.js graphs if needed
    // Example:
    // const ctx = document.getElementById('statsChart');
    // if (ctx) {
    //     new Chart(ctx, { ... });
    // }
}

// Add to your admin.js file

// Table actions icons styling
document.addEventListener('DOMContentLoaded', function() {
    // Create icon buttons CSS
    const style = document.createElement('style');
    style.textContent = `
        .table-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        
        .icon-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: 1px solid var(--gray-200);
            background: white;
            color: var(--gray-600);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .icon-btn:hover {
            background: var(--gray-50);
            color: var(--gray-900);
            border-color: var(--gray-300);
        }
        
        .text-right {
            text-align: right;
        }
        
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .data-table thead th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--gray-700);
            border-bottom: 2px solid var(--gray-200);
            background: var(--gray-50);
        }
        
        .data-table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--gray-100);
        }
        
        .data-table tbody tr:hover {
            background: var(--gray-50);
        }
    `;
    document.head.appendChild(style);
});