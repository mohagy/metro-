// UI Management Functions

let currentOrderId = null;

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
    setupEventListeners();
});

// Check authentication status
async function checkAuth() {
    const token = getAuthToken();
    if (!token) {
        showLoginScreen();
        return;
    }
    
    try {
        const result = await AuthAPI.verifySession();
        if (result.success) {
            showAdminPanel(result.admin);
        } else {
            showLoginScreen();
        }
    } catch (error) {
        showLoginScreen();
    }
}

// Show login screen
function showLoginScreen() {
    document.getElementById('loginScreen').style.display = 'flex';
    document.getElementById('adminPanel').style.display = 'none';
}

// Show admin panel
function showAdminPanel(admin) {
    document.getElementById('loginScreen').style.display = 'none';
    document.getElementById('adminPanel').style.display = 'block';
    document.getElementById('adminUsername').textContent = admin.username;
    
    // Load dashboard
    if (document.querySelector('.nav-tab.active').dataset.tab === 'dashboard') {
        loadDashboard();
    }
}

// Setup event listeners
function setupEventListeners() {
    // Login form
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    
    // Logout button
    document.getElementById('logoutBtn').addEventListener('click', handleLogout);
    
    // Tab navigation
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', () => switchTab(tab.dataset.tab));
    });
    
    // Orders
    document.getElementById('searchOrdersBtn').addEventListener('click', searchOrders);
    document.getElementById('refreshOrdersBtn').addEventListener('click', loadOrders);
    document.getElementById('updateOrderStatusBtn').addEventListener('click', updateOrderStatus);
    
    // Users
    document.getElementById('createUserBtn').addEventListener('click', () => openUserModal());
    document.getElementById('refreshUsersBtn').addEventListener('click', loadUsers);
    document.getElementById('userForm').addEventListener('submit', saveUser);
    document.getElementById('deleteUserBtn').addEventListener('click', deleteUser);
    
    // Modal close buttons
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', closeModals);
    });
    
    // Close modal on outside click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModals();
            }
        });
    });
}

// Handle login
async function handleLogin(e) {
    e.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const errorDiv = document.getElementById('loginError');
    
    errorDiv.textContent = '';
    
    try {
        const result = await AuthAPI.login(username, password);
        if (result.success) {
            showAdminPanel(result.admin);
        }
    } catch (error) {
        errorDiv.textContent = error.message;
    }
}

// Handle logout
async function handleLogout() {
    await AuthAPI.logout();
    showLoginScreen();
    document.getElementById('loginForm').reset();
}

// Switch tabs
function switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.classList.remove('active');
        if (tab.dataset.tab === tabName) {
            tab.classList.add('active');
        }
    });
    
    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(`${tabName}Tab`).classList.add('active');
    
    // Load tab data
    switch(tabName) {
        case 'dashboard':
            loadDashboard();
            break;
        case 'orders':
            loadOrders();
            break;
        case 'users':
            loadUsers();
            break;
        case 'pricing':
            loadPricing();
            break;
    }
}

// Load dashboard
async function loadDashboard() {
    try {
        const result = await StatsAPI.getStats();
        if (result.success) {
            const stats = result.stats;
            
            document.getElementById('statTotalOrders').textContent = stats.total_orders;
            document.getElementById('statTotalRevenue').textContent = formatCurrency(stats.total_revenue);
            document.getElementById('statTotalUsers').textContent = stats.total_users;
            document.getElementById('statTodayOrders').textContent = stats.today_orders;
            document.getElementById('statTodayRevenue').textContent = formatCurrency(stats.today_revenue);
            document.getElementById('statAvgOrder').textContent = formatCurrency(stats.average_order_value);
            
            // Status breakdown
            const statusBreakdown = document.getElementById('statusBreakdown');
            statusBreakdown.innerHTML = '';
            for (const [status, count] of Object.entries(stats.orders_by_status)) {
                const statusItem = document.createElement('div');
                statusItem.className = 'status-item';
                statusItem.innerHTML = `<span class="status-label">${status}:</span> <span class="status-count">${count}</span>`;
                statusBreakdown.appendChild(statusItem);
            }
        }
    } catch (error) {
        showNotification('Failed to load dashboard: ' + error.message, 'error');
    }
}

// Load orders
async function loadOrders() {
    const tbody = document.getElementById('ordersTableBody');
    tbody.innerHTML = '<tr><td colspan="6">Loading...</td></tr>';
    
    try {
        const result = await OrdersAPI.getOrders();
        if (result.success) {
            displayOrders(result.orders);
        }
    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="6" class="error">Error: ${error.message}</td></tr>`;
    }
}

// Display orders
function displayOrders(orders) {
    const tbody = document.getElementById('ordersTableBody');
    
    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6">No orders found</td></tr>';
        return;
    }
    
    tbody.innerHTML = orders.map(order => `
        <tr>
            <td>${order.order_id}</td>
            <td>${order.user_name || order.user_email || order.user_id}</td>
            <td><span class="status-badge status-${order.status.toLowerCase()}">${order.status}</span></td>
            <td>${formatCurrency(order.total_cost)}</td>
            <td>${formatDate(order.created_at)}</td>
            <td><button class="btn btn-sm btn-primary" onclick="viewOrder('${order.order_id}')">View</button></td>
        </tr>
    `).join('');
}

// View order
async function viewOrder(orderId) {
    currentOrderId = orderId;
    const modal = document.getElementById('orderModal');
    const details = document.getElementById('orderDetails');
    
    details.innerHTML = 'Loading...';
    modal.style.display = 'block';
    
    try {
        const result = await OrdersAPI.getOrder(orderId);
        if (result.success) {
            const order = result.order;
            details.innerHTML = `
                <div class="order-detail-section">
                    <h3>Order Information</h3>
                    <p><strong>Order ID:</strong> ${order.order_id}</p>
                    <p><strong>User:</strong> ${order.user_name || order.user_email}</p>
                    <p><strong>Status:</strong> ${order.status}</p>
                    <p><strong>Total Cost:</strong> ${formatCurrency(order.total_cost)}</p>
                    <p><strong>Delivery Option:</strong> ${order.delivery_option}</p>
                    <p><strong>Created:</strong> ${formatDate(order.created_at)}</p>
                </div>
                ${order.print_options ? `
                <div class="order-detail-section">
                    <h3>Print Options</h3>
                    <p><strong>Paper Size:</strong> ${order.print_options.paper_size}</p>
                    <p><strong>Color:</strong> ${order.print_options.color}</p>
                    <p><strong>Quantity:</strong> ${order.print_options.quantity}</p>
                    <p><strong>Sides:</strong> ${order.print_options.sides}</p>
                    <p><strong>Orientation:</strong> ${order.print_options.orientation}</p>
                    <p><strong>Binding:</strong> ${order.print_options.binding}</p>
                </div>
                ` : ''}
                ${order.files && order.files.length > 0 ? `
                <div class="order-detail-section">
                    <h3>Files (${order.files.length})</h3>
                    <ul>
                        ${order.files.map(file => `<li>${file.original_name} (${formatFileSize(file.size_bytes)})</li>`).join('')}
                    </ul>
                </div>
                ` : ''}
            `;
            document.getElementById('orderStatusSelect').value = order.status;
        }
    } catch (error) {
        details.innerHTML = `<p class="error">Error: ${error.message}</p>`;
    }
}

// Update order status
async function updateOrderStatus() {
    const status = document.getElementById('orderStatusSelect').value;
    
    if (!currentOrderId) {
        showNotification('No order selected', 'error');
        return;
    }
    
    try {
        const result = await OrdersAPI.updateOrderStatus(currentOrderId, status);
        if (result.success) {
            showNotification('Order status updated successfully', 'success');
            closeModals();
            loadOrders();
        }
    } catch (error) {
        showNotification('Failed to update status: ' + error.message, 'error');
    }
}

// Search orders
async function searchOrders() {
    const orderId = document.getElementById('orderSearch').value;
    const status = document.getElementById('statusFilter').value;
    
    const tbody = document.getElementById('ordersTableBody');
    tbody.innerHTML = '<tr><td colspan="6">Searching...</td></tr>';
    
    try {
        const filters = {};
        if (orderId) filters.order_id = orderId;
        if (status) filters.status = status;
        
        const result = await OrdersAPI.searchOrders(filters);
        if (result.success) {
            displayOrders(result.orders);
        }
    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="6" class="error">Error: ${error.message}</td></tr>`;
    }
}

// Load users
async function loadUsers() {
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = '<tr><td colspan="7">Loading...</td></tr>';
    
    try {
        const result = await UsersAPI.getUsers();
        if (result.success) {
            displayUsers(result.users);
        }
    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="7" class="error">Error: ${error.message}</td></tr>`;
    }
}

// Display users
function displayUsers(users) {
    const tbody = document.getElementById('usersTableBody');
    
    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7">No users found</td></tr>';
        return;
    }
    
    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${user.id.substring(0, 12)}...</td>
            <td>${user.email}</td>
            <td>${user.name || '-'}</td>
            <td>${user.phone || '-'}</td>
            <td>${user.order_count || 0}</td>
            <td>${formatDate(user.created_at)}</td>
            <td><button class="btn btn-sm btn-primary" onclick="viewUser('${user.id}')">View</button></td>
        </tr>
    `).join('');
}

// View user
async function viewUser(userId) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const title = document.getElementById('userModalTitle');
    const deleteBtn = document.getElementById('deleteUserBtn');
    const passwordGroup = document.getElementById('userPasswordGroup');
    
    title.textContent = 'Edit User';
    deleteBtn.style.display = 'block';
    passwordGroup.style.display = 'none';
    form.dataset.userId = userId;
    modal.style.display = 'block';
    
    try {
        const result = await UsersAPI.getUser(userId);
        if (result.success) {
            const user = result.user;
            document.getElementById('userId').value = user.id;
            document.getElementById('userEmail').value = user.email || '';
            document.getElementById('userName').value = user.name || '';
            document.getElementById('userPhone').value = user.phone || '';
        }
    } catch (error) {
        showNotification('Failed to load user: ' + error.message, 'error');
    }
}

// Open user modal for creation
function openUserModal() {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const title = document.getElementById('userModalTitle');
    const deleteBtn = document.getElementById('deleteUserBtn');
    const passwordGroup = document.getElementById('userPasswordGroup');
    
    title.textContent = 'Create User';
    deleteBtn.style.display = 'none';
    passwordGroup.style.display = 'block';
    form.reset();
    form.dataset.userId = '';
    modal.style.display = 'block';
}

// Save user
async function saveUser(e) {
    e.preventDefault();
    const form = e.target;
    const userId = form.dataset.userId;
    
    const userData = {
        email: document.getElementById('userEmail').value,
        name: document.getElementById('userName').value,
        phone: document.getElementById('userPhone').value
    };
    
    if (!userId) {
        // Create new user
        const password = document.getElementById('userPassword').value;
        if (password) {
            userData.password = password;
        }
        
        try {
            const result = await UsersAPI.createUser(userData);
            if (result.success) {
                showNotification('User created successfully', 'success');
                closeModals();
                loadUsers();
            }
        } catch (error) {
            showNotification('Failed to create user: ' + error.message, 'error');
        }
    } else {
        // Update existing user
        try {
            const result = await UsersAPI.updateUser(userId, userData);
            if (result.success) {
                showNotification('User updated successfully', 'success');
                closeModals();
                loadUsers();
            }
        } catch (error) {
            showNotification('Failed to update user: ' + error.message, 'error');
        }
    }
}

// Delete user
async function deleteUser() {
    const userId = document.getElementById('userId').value;
    
    if (!userId || !confirm('Are you sure you want to delete this user?')) {
        return;
    }
    
    try {
        const result = await UsersAPI.deleteUser(userId);
        if (result.success) {
            showNotification('User deleted successfully', 'success');
            closeModals();
            loadUsers();
        }
    } catch (error) {
        showNotification('Failed to delete user: ' + error.message, 'error');
    }
}

// Load pricing
async function loadPricing() {
    try {
        // Load base pricing
        const pricingResult = await PricingAPI.getPricing();
        if (pricingResult.success) {
            displayPricing(pricingResult.pricing);
        }
        
        // Load paper multipliers
        const paperResult = await PricingAPI.getPaperMultipliers();
        if (paperResult.success) {
            displayPaperMultipliers(paperResult.multipliers);
        }
        
        // Load binding costs
        const bindingResult = await PricingAPI.getBindingCosts();
        if (bindingResult.success) {
            displayBindingCosts(bindingResult.bindings);
        }
    } catch (error) {
        showNotification('Failed to load pricing: ' + error.message, 'error');
    }
}

// Display pricing
function displayPricing(pricing) {
    const container = document.getElementById('pricingConfig');
    container.innerHTML = pricing.map(item => `
        <div class="pricing-item">
            <label>${item.config_key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</label>
            <div class="pricing-input-group">
                <span class="currency">$</span>
                <input type="number" step="0.01" value="${item.config_value}" 
                       onchange="updatePricing('${item.config_key}', this.value, '${item.description || ''}')"
                       class="pricing-input">
            </div>
            ${item.description ? `<small>${item.description}</small>` : ''}
        </div>
    `).join('');
}

// Display paper multipliers
function displayPaperMultipliers(multipliers) {
    const container = document.getElementById('paperMultipliers');
    container.innerHTML = multipliers.map(item => `
        <div class="pricing-item">
            <label>${item.paper_size}</label>
            <div class="pricing-input-group">
                <input type="number" step="0.01" value="${item.multiplier}" 
                       onchange="updatePaperMultiplier('${item.paper_size}', this.value)"
                       class="pricing-input">
                <span class="multiplier-label">x</span>
            </div>
        </div>
    `).join('');
}

// Display binding costs
function displayBindingCosts(bindings) {
    const container = document.getElementById('bindingCosts');
    container.innerHTML = bindings.map(item => `
        <div class="pricing-item">
            <label>${item.binding_type}</label>
            <div class="pricing-input-group">
                <span class="currency">$</span>
                <input type="number" step="0.01" value="${item.cost}" 
                       onchange="updateBindingCost('${item.binding_type}', this.value)"
                       class="pricing-input">
            </div>
        </div>
    `).join('');
}

// Update pricing
async function updatePricing(configKey, configValue, description) {
    try {
        const result = await PricingAPI.updatePricing(configKey, parseFloat(configValue), description);
        if (result.success) {
            showNotification('Pricing updated successfully', 'success');
        }
    } catch (error) {
        showNotification('Failed to update pricing: ' + error.message, 'error');
    }
}

// Update paper multiplier
async function updatePaperMultiplier(paperSize, multiplier) {
    try {
        const result = await PricingAPI.updatePaperMultiplier(paperSize, parseFloat(multiplier));
        if (result.success) {
            showNotification('Paper multiplier updated successfully', 'success');
        }
    } catch (error) {
        showNotification('Failed to update multiplier: ' + error.message, 'error');
    }
}

// Update binding cost
async function updateBindingCost(bindingType, cost) {
    try {
        const result = await PricingAPI.updateBindingCost(bindingType, parseFloat(cost));
        if (result.success) {
            showNotification('Binding cost updated successfully', 'success');
        }
    } catch (error) {
        showNotification('Failed to update binding cost: ' + error.message, 'error');
    }
}

// Close modals
function closeModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.style.display = 'none';
    });
    currentOrderId = null;
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = `notification notification-${type}`;
    notification.style.display = 'block';
    
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

// Format file size
function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

