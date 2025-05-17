// resources/js/stores/authStore.js
import { defineStore } from 'pinia';
import authService from '../services/authService';
import { useLayoutStore } from './layoutStore';

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
    isAuthenticated: (state) => state.isAuthenticated && state.token,

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
      const layoutStore = useLayoutStore();

      try {
        const response = await authService.login(credentials);

        if (response.data.token) {
          this.token = response.data.token;
          this.user = response.data.user;
          this.isAuthenticated = true;
          this.requiresTermsAgreement = response.data.requiresTermsAgreement || false;

          localStorage.setItem('token', this.token);

          // Display success message using layoutStore
          layoutStore.showSuccessSnackbar('Login successful! Welcome back.');

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
      const layoutStore = useLayoutStore();
      try {
        if (this.token) {
          await authService.logout();
        }
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.token = null;
        this.user = null;
        this.isAuthenticated = false;
        localStorage.removeItem('token');
        layoutStore.showInfoSnackbar('You have been logged out.');
      }
    },

    // Fetch current user data
    async fetchUser() {
      if (!this.token) return;

      this.isLoading = true;

      try {
        const response = await authService.getCurrentUser();
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
      const layoutStore = useLayoutStore();

      try {
        const response = await authService.forgotPassword(email);
        layoutStore.showSuccessSnackbar('Password reset email sent. Please check your inbox.');
        return response.data;
      } catch (error) {
        layoutStore.showErrorSnackbar(error.response?.data?.message || 'Failed to process password reset.');
        throw error.response?.data?.message || 'Failed to process password reset.';
      } finally {
        this.isLoading = false;
      }
    },

    // Handle password reset
    async handleResetPassword(formData) {
      this.isLoading = true;
      const layoutStore = useLayoutStore();

      try {
        const response = await authService.resetPassword(formData);
        layoutStore.showSuccessSnackbar('Password has been reset successfully. You can now log in with your new password.');
        return response.data;
      } catch (error) {
        layoutStore.showErrorSnackbar(error.response?.data?.message || 'Failed to reset password.');
        throw error.response?.data?.message || 'Failed to reset password.';
      } finally {
        this.isLoading = false;
      }
    },

    // Agree to terms and conditions
    async agreeToTerms(termId) {
      this.isLoading = true;
      const layoutStore = useLayoutStore();

      try {
        await authService.agreeToTerms(termId);
        this.requiresTermsAgreement = false;
        layoutStore.showSuccessSnackbar('Terms and conditions accepted.');
        return true;
      } catch (error) {
        layoutStore.showErrorSnackbar('Error accepting terms and conditions.');
        console.error('Error agreeing to terms:', error);
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    // Change user password
    async changePassword(formData) {
      this.isLoading = true;
      const layoutStore = useLayoutStore();

      try {
        const response = await authService.changePassword(formData);
        layoutStore.showSuccessSnackbar('Password changed successfully.');
        return response.data;
      } catch (error) {
        layoutStore.showErrorSnackbar(error.response?.data?.message || 'Failed to change password.');
        throw error.response?.data?.message || 'Failed to change password.';
      } finally {
        this.isLoading = false;
      }
    },

    // Update user avatar
    async updateAvatar(formData) {
      this.isLoading = true;
      const layoutStore = useLayoutStore();

      try {
        const response = await authService.updateAvatar(formData);

        if (response.data.user) {
          this.user = response.data.user;
        }

        layoutStore.showSuccessSnackbar('Profile picture updated successfully.');
        return response.data;
      } catch (error) {
        layoutStore.showErrorSnackbar(error.response?.data?.message || 'Failed to update profile picture.');
        throw error.response?.data?.message || 'Failed to update avatar.';
      } finally {
        this.isLoading = false;
      }
    },

    // Update user profile
    async updateProfile(profileData) {
      this.isLoading = true;
      const layoutStore = useLayoutStore();

      try {
        const response = await authService.updateProfile(profileData);

        if (response.data.user) {
          this.user = response.data.user;
        }

        layoutStore.showSuccessSnackbar('Profile updated successfully.');
        return response.data;
      } catch (error) {
        layoutStore.showErrorSnackbar(error.response?.data?.message || 'Failed to update profile.');
        throw error.response?.data?.message || 'Failed to update profile.';
      } finally {
        this.isLoading = false;
      }
    },

    // Initialize auth store with session token
    init() {
      const token = localStorage.getItem('token');

      if (token) {
        this.token = token;
        this.isAuthenticated = true;
        this.fetchUser();

        // Setup axios interceptors
        authService.setupInterceptors(() => this.logout());
      }
    }
  }
});
