// resources/js/stores/authStore.js
import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    // User data
    user: null,
    token: localStorage.getItem('token') || null,
    isAuthenticated: false,
    isLoading: false,
    loginError: null,
    requiresTermsAgreement: false
  }),

  getters: {
    // Check if user is authenticated
    isUserAuthenticated: (state) => state.isAuthenticated && state.token,

    // Get current user
    currentUser: (state) => state.user,

    // Check if user is admin
    isAdmin: (state) => state.user && state.user.roles && state.user.roles.some(role => role.name === 'Admin')
  },

  actions: {
    // Login action
    async login(credentials) {
      this.isLoading = true;
      this.loginError = null;

      try {
        const response = await axios.post('/api/auth/login', credentials);

        if (response.data.token) {
          this.token = response.data.token;
          this.user = response.data.user;
          this.isAuthenticated = true;
          this.requiresTermsAgreement = response.data.requiresTermsAgreement || false;

          localStorage.setItem('token', this.token);

          return true;
        }
      } catch (error) {
        this.loginError = error.response?.data?.message || 'Login failed. Please check your credentials.';
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    // Logout action
    async logout() {
      try {
        if (this.token) {
          await axios.post('/api/auth/logout');
        }
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.token = null;
        this.user = null;
        this.isAuthenticated = false;
        localStorage.removeItem('token');
      }
    },

    // Fetch current user data
    async fetchUser() {
      if (!this.token) return;

      this.isLoading = true;

      try {
        const response = await axios.get('/api/auth/me');
        this.user = response.data;
        this.isAuthenticated = true;
      } catch (error) {
        console.error('Error fetching user data:', error);
        this.logout();
      } finally {
        this.isLoading = false;
      }
    },

    // Handle forgot password request
    async handleForgotPassword(email) {
      this.isLoading = true;

      try {
        const response = await axios.post('/api/auth/forgot-password', { email });
        return response.data;
      } catch (error) {
        throw error.response?.data?.message || 'Failed to process password reset.';
      } finally {
        this.isLoading = false;
      }
    },

    // Handle password reset
    async handleResetPassword(formData) {
      this.isLoading = true;

      try {
        const response = await axios.post('/api/auth/reset-password', formData);
        return response.data;
      } catch (error) {
        throw error.response?.data?.message || 'Failed to reset password.';
      } finally {
        this.isLoading = false;
      }
    },

    // Agree to terms and conditions
    async agreeToTerms(termId) {
      this.isLoading = true;

      try {
        await axios.post('/api/terms/agree', { term_id: termId });
        this.requiresTermsAgreement = false;
        return true;
      } catch (error) {
        console.error('Error agreeing to terms:', error);
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    // Change user password
    async changePassword(formData) {
      this.isLoading = true;

      try {
        const response = await axios.post('/api/user/change-password', formData);
        return response.data;
      } catch (error) {
        throw error.response?.data?.message || 'Failed to change password.';
      } finally {
        this.isLoading = false;
      }
    },

    // Update user avatar
    async updateAvatar(formData) {
      this.isLoading = true;

      try {
        const response = await axios.post('/api/user/update-avatar', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        if (response.data.user) {
          this.user = response.data.user;
        }

        return response.data;
      } catch (error) {
        throw error.response?.data?.message || 'Failed to update avatar.';
      } finally {
        this.isLoading = false;
      }
    }
  }
});
