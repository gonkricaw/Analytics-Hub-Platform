// resources/js/services/authService.js
import axios from 'axios';

/**
 * Authentication Service
 *
 * Handles all API calls related to authentication and user management
 */
class AuthService {
  /**
   * Login user with email and password
   * @param {Object} credentials - User credentials (email, password)
   * @returns {Promise} - Response from API
   */
  login(credentials) {
    return axios.post('/api/auth/login', credentials);
  }

  /**
   * Logout the current user
   * @returns {Promise} - Response from API
   */
  logout() {
    return axios.post('/api/auth/logout');
  }

  /**
   * Get the current authenticated user
   * @returns {Promise} - Response from API
   */
  getCurrentUser() {
    return axios.get('/api/auth/me');
  }

  /**
   * Send forgot password request
   * @param {String} email - User email
   * @returns {Promise} - Response from API
   */
  forgotPassword(email) {
    return axios.post('/api/auth/forgot-password', { email });
  }

  /**
   * Reset user password with token
   * @param {Object} data - Reset password data (token, email, password, password_confirmation)
   * @returns {Promise} - Response from API
   */
  resetPassword(data) {
    return axios.post('/api/auth/reset-password', data);
  }

  /**
   * Change user password
   * @param {Object} data - Password data (current_password, new_password, new_password_confirmation)
   * @returns {Promise} - Response from API
   */
  changePassword(data) {
    return axios.post('/api/user/change-password', data);
  }

  /**
   * Update user profile avatar
   * @param {FormData} formData - Form data with avatar file
   * @returns {Promise} - Response from API
   */
  updateAvatar(formData) {
    return axios.post('/api/user/update-avatar', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }

  /**
   * Update user profile information
   * @param {Object} data - Profile data to update
   * @returns {Promise} - Response from API
   */
  updateProfile(data) {
    return axios.put('/api/user/profile', data);
  }

  /**
   * Agree to terms and conditions
   * @param {Number} termId - Terms and conditions ID
   * @returns {Promise} - Response from API
   */
  agreeToTerms(termId) {
    return axios.post('/api/terms/agree', { term_id: termId });
  }

  /**
   * Get the latest terms and conditions
   * @returns {Promise} - Response from API
   */
  getLatestTerms() {
    return axios.get('/api/terms-and-conditions/latest');
  }

  /**
   * Setup axios interceptors for authentication
   * @param {Function} onLogout - Function to call when token is invalid
   */
  setupInterceptors(onLogout) {
    axios.interceptors.request.use(
      config => {
        const token = localStorage.getItem('token');
        if (token) {
          config.headers['Authorization'] = `Bearer ${token}`;
        }
        return config;
      },
      error => Promise.reject(error)
    );

    axios.interceptors.response.use(
      response => response,
      error => {
        // Handle 401 Unauthorized errors
        if (error.response && error.response.status === 401 && onLogout) {
          onLogout();
        }
        return Promise.reject(error);
      }
    );
  }
}

export default new AuthService();
