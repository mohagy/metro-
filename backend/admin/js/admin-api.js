// Admin API Configuration
const API_BASE_URL = '/metro/backend/api/admin';

// Get stored token
function getAuthToken() {
    return localStorage.getItem('admin_token');
}

// Set auth token
function setAuthToken(token) {
    localStorage.setItem('admin_token', token);
}

// Remove auth token
function removeAuthToken() {
    localStorage.removeItem('admin_token');
}

// Make API request
async function apiRequest(endpoint, options = {}) {
    const token = getAuthToken();
    const url = `${API_BASE_URL}/${endpoint}`;
    
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    };
    
    if (token) {
        defaultOptions.headers['Authorization'] = `Bearer ${token}`;
    }
    
    // Convert data to form data if provided
    if (options.data) {
        const formData = new URLSearchParams();
        if (options.data.token) {
            formData.append('token', token);
        }
        for (const key in options.data) {
            if (key !== 'token') {
                formData.append(key, options.data[key]);
            }
        }
        options.body = formData.toString();
        delete options.data;
    }
    
    const config = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...(options.headers || {})
        }
    };
    
    try {
        const response = await fetch(url, config);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Request failed');
        }
        
        return data;
    } catch (error) {
        throw error;
    }
}

// Authentication APIs
const AuthAPI = {
    async login(username, password) {
        const formData = new URLSearchParams();
        formData.append('username', username);
        formData.append('password', password);
        
        const response = await fetch(`${API_BASE_URL}/login.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData.toString()
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Login failed');
        }
        
        if (data.success && data.token) {
            setAuthToken(data.token);
        }
        
        return data;
    },
    
    async logout() {
        try {
            await apiRequest('logout.php', {
                method: 'POST',
                data: { token: getAuthToken() }
            });
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            removeAuthToken();
        }
    },
    
    async verifySession() {
        return await apiRequest('verify_session.php', {
            method: 'POST',
            data: { token: getAuthToken() }
        });
    }
};

// Orders APIs
const OrdersAPI = {
    async getOrders(page = 1, limit = 50) {
        return await apiRequest(`get_orders.php?page=${page}&limit=${limit}`, {
            method: 'GET'
        });
    },
    
    async getOrder(orderId) {
        return await apiRequest(`get_order.php?order_id=${orderId}`, {
            method: 'GET'
        });
    },
    
    async updateOrderStatus(orderId, status) {
        return await apiRequest('update_order_status.php', {
            method: 'POST',
            data: {
                order_id: orderId,
                status: status
            }
        });
    },
    
    async searchOrders(filters) {
        const params = new URLSearchParams();
        for (const key in filters) {
            if (filters[key]) {
                params.append(key, filters[key]);
            }
        }
        return await apiRequest(`search_orders.php?${params.toString()}`, {
            method: 'GET'
        });
    },
    
    async getOrdersByStatus(status) {
        return await apiRequest(`get_orders_by_status.php?status=${status}`, {
            method: 'GET'
        });
    }
};

// Users APIs
const UsersAPI = {
    async getUsers(page = 1, limit = 50) {
        return await apiRequest(`get_users.php?page=${page}&limit=${limit}`, {
            method: 'GET'
        });
    },
    
    async getUser(userId) {
        return await apiRequest(`get_user.php?user_id=${userId}`, {
            method: 'GET'
        });
    },
    
    async createUser(userData) {
        return await apiRequest('create_user.php', {
            method: 'POST',
            data: userData
        });
    },
    
    async updateUser(userId, userData) {
        return await apiRequest('update_user.php', {
            method: 'POST',
            data: {
                user_id: userId,
                ...userData
            }
        });
    },
    
    async deleteUser(userId) {
        return await apiRequest('delete_user.php', {
            method: 'POST',
            data: {
                user_id: userId
            }
        });
    }
};

// Pricing APIs
const PricingAPI = {
    async getPricing() {
        return await apiRequest('get_pricing.php', {
            method: 'GET'
        });
    },
    
    async updatePricing(configKey, configValue, description = null) {
        return await apiRequest('update_pricing.php', {
            method: 'POST',
            data: {
                config_key: configKey,
                config_value: configValue,
                description: description
            }
        });
    },
    
    async getPaperMultipliers() {
        return await apiRequest('get_paper_multipliers.php', {
            method: 'GET'
        });
    },
    
    async updatePaperMultiplier(paperSize, multiplier) {
        return await apiRequest('update_paper_multiplier.php', {
            method: 'POST',
            data: {
                paper_size: paperSize,
                multiplier: multiplier
            }
        });
    },
    
    async getBindingCosts() {
        return await apiRequest('get_binding_costs.php', {
            method: 'GET'
        });
    },
    
    async updateBindingCost(bindingType, cost) {
        return await apiRequest('update_binding_cost.php', {
            method: 'POST',
            data: {
                binding_type: bindingType,
                cost: cost
            }
        });
    }
};

// Stats API
const StatsAPI = {
    async getStats() {
        return await apiRequest('get_stats.php', {
            method: 'GET'
        });
    }
};

