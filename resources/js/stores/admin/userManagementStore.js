// resources/js/stores/admin/userManagementStore.js
import { defineStore } from 'pinia';
import userManagementService from '@/services/admin/userManagementService';
import { useLayoutStore } from '../layoutStore';

export const useUserManagementStore = defineStore('userManagement', {
  state: () => ({
    users: [],
    roles: [],
    selectedUser: null,
    pagination: {
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,
      sortBy: 'created_at',
      sortDesc: true
    },
    filters: {
      search: '',
      role: null,
      isActive: null
    },
    isLoading: false,
    error: null,
    invitationDialogOpen: false,
    invitationStatus: null,
    blockedIps: []
  }),

  getters: {
    /**
     * Get formatted users for data table display
     */
    formattedUsers: (state) => {
      return state.users.map(user => ({
        ...user,
        roleNames: user.roles ? user.roles.map(role => role.display_name).join(', ') : '',
        statusText: user.is_active ? 'Active' : 'Inactive',
        statusColor: user.is_active ? 'success' : 'error',
        lastLoginDate: user.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'Never'
      }));
    },

    /**
     * Get role options for select components
     */
    roleOptions: (state) => {
      return state.roles.map(role => ({
        value: role.id,
        title: role.display_name || role.name
      }));
    },

    /**
     * Status filter options
     */
    statusOptions: () => [
      { value: true, title: 'Active' },
      { value: false, title: 'Inactive' }
    ]
  },

  actions: {
    /**
     * Fetch users with pagination and filters
     */
    async fetchUsers() {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await userManagementService.getUsers({
          page: this.pagination.page,
          perPage: this.pagination.itemsPerPage,
          sortBy: this.pagination.sortBy,
          sortDesc: this.pagination.sortDesc ? 'desc' : 'asc',
          search: this.filters.search,
          role: this.filters.role,
          isActive: this.filters.isActive
        });

        this.users = response.data.users;
        this.pagination.totalItems = response.data.total;

        return this.users;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to fetch users');
        return [];
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Fetch roles for user assignment
     */
    async fetchRoles() {
      const layoutStore = useLayoutStore();

      try {
        const response = await userManagementService.getRoles();
        this.roles = response.data.roles;
        return this.roles;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to fetch roles');
        return [];
      }
    },

    /**
     * Fetch a single user by ID
     */
    async fetchUser(userId) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await userManagementService.getUser(userId);
        this.selectedUser = response.data.user;
        return this.selectedUser;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to fetch user details');
        return null;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Create a new user
     */
    async createUser(userData) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await userManagementService.createUser(userData);
        await this.fetchUsers(); // Refresh user list

        layoutStore.showSuccess('User created successfully');
        return response.data.user;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to create user');
        throw error;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Update an existing user
     */
    async updateUser(userId, userData) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await userManagementService.updateUser(userId, userData);

        // Update user in the list if exists
        const userIndex = this.users.findIndex(user => user.id === userId);
        if (userIndex !== -1) {
          this.users[userIndex] = response.data.user;
        }

        // Update selected user if it matches
        if (this.selectedUser && this.selectedUser.id === userId) {
          this.selectedUser = response.data.user;
        }

        layoutStore.showSuccess('User updated successfully');
        return response.data.user;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to update user');
        throw error;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Delete a user
     */
    async deleteUser(userId) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        await userManagementService.deleteUser(userId);

        // Remove user from the list
        this.users = this.users.filter(user => user.id !== userId);

        // Clear selected user if it matches
        if (this.selectedUser && this.selectedUser.id === userId) {
          this.selectedUser = null;
        }

        layoutStore.showSuccess('User deleted successfully');
        return true;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to delete user');
        return false;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Invite a new user
     */
    async inviteUser(invitationData) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();
        this.invitationStatus = 'sending';

        const response = await userManagementService.inviteUser(invitationData);

        this.invitationStatus = 'success';
        await this.fetchUsers(); // Refresh user list

        layoutStore.showSuccess('User invitation sent successfully');
        return response.data;
      } catch (error) {
        this.error = error;
        this.invitationStatus = 'error';
        layoutStore.handleError(error, 'Failed to send user invitation');
        throw error;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Toggle user active status
     */
    async toggleUserStatus(userId, isActive) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const userData = { is_active: isActive };
        const response = await userManagementService.updateUser(userId, userData);

        // Update user in the list if exists
        const userIndex = this.users.findIndex(user => user.id === userId);
        if (userIndex !== -1) {
          this.users[userIndex].is_active = isActive;
        }

        // Update selected user if it matches
        if (this.selectedUser && this.selectedUser.id === userId) {
          this.selectedUser.is_active = isActive;
        }

        const statusText = isActive ? 'activated' : 'deactivated';
        layoutStore.showSuccess(`User ${statusText} successfully`);
        return response.data.user;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to update user status');
        throw error;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Fetch blocked IPs
     */
    async fetchBlockedIps() {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await userManagementService.getBlockedIps();
        this.blockedIps = response.data.blocked_ips;
        return this.blockedIps;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to fetch blocked IPs');
        return [];
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Unblock an IP address
     */
    async unblockIp(ipAddress) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        await userManagementService.unblockIp(ipAddress);

        // Remove IP from the list
        this.blockedIps = this.blockedIps.filter(ip => ip.ip_address !== ipAddress);

        layoutStore.showSuccess('IP address unblocked successfully');
        return true;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to unblock IP address');
        return false;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Set invitation dialog state
     */
    setInvitationDialog(isOpen) {
      this.invitationDialogOpen = isOpen;
      if (!isOpen) {
        this.invitationStatus = null;
      }
    },

    /**
     * Update pagination
     */
    updatePagination(pagination) {
      this.pagination = {...this.pagination, ...pagination};
      this.fetchUsers();
    },

    /**
     * Update filters
     */
    updateFilters(filters) {
      this.filters = {...this.filters, ...filters};
      this.pagination.page = 1; // Reset to first page when filtering
      this.fetchUsers();
    },

    /**
     * Clear all filters
     */
    clearFilters() {
      this.filters = {
        search: '',
        role: null,
        isActive: null
      };
      this.fetchUsers();
    }
  }
});
