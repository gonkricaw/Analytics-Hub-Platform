// resources/js/stores/admin/roleManagementStore.js
import { defineStore } from 'pinia';
import roleManagementService from '@/services/admin/roleManagementService';
import { useLayoutStore } from '../layoutStore';

export const useRoleManagementStore = defineStore('roleManagement', {
  state: () => ({
    roles: [],
    permissions: [],
    selectedRole: null,
    pagination: {
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,
      sortBy: 'name',
      sortDesc: false
    },
    filters: {
      search: '',
    },
    isLoading: false,
    error: null,
    permissionModules: []
  }),

  getters: {
    /**
     * Get formatted roles for data table display
     */
    formattedRoles: (state) => {
      return state.roles.map(role => ({
        ...role,
        permissionCount: role.permissions ? role.permissions.length : 0,
        isSystemRole: ['admin', 'manager', 'user'].includes(role.name.toLowerCase())
      }));
    },

    /**
     * Get permission modules for grouped display
     */
    groupedPermissions: (state) => {
      const grouped = {};

      state.permissions.forEach(permission => {
        const module = permission.module || 'other';
        if (!grouped[module]) {
          grouped[module] = [];
        }
        grouped[module].push(permission);
      });

      return grouped;
    }
  },

  actions: {
    /**
     * Fetch roles with pagination and filters
     */
    async fetchRoles() {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await roleManagementService.getRoles({
          page: this.pagination.page,
          perPage: this.pagination.itemsPerPage,
          sortBy: this.pagination.sortBy,
          sortDesc: this.pagination.sortDesc ? 'desc' : 'asc',
          search: this.filters.search
        });

        this.roles = response.data.roles;
        if (response.data.total) {
          this.pagination.totalItems = response.data.total;
        }

        return this.roles;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to fetch roles');
        return [];
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Fetch a single role by ID
     */
    async fetchRole(roleId) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await roleManagementService.getRole(roleId);
        this.selectedRole = response.data.role;
        return this.selectedRole;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to fetch role details');
        return null;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Fetch all permissions
     */
    async fetchPermissions() {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await roleManagementService.getPermissions();
        this.permissions = response.data.permissions;

        // Extract unique modules
        this.permissionModules = [...new Set(this.permissions.map(p => p.module || 'other'))].sort();

        return this.permissions;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to fetch permissions');
        return [];
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Create a new role
     */
    async createRole(roleData) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await roleManagementService.createRole(roleData);
        await this.fetchRoles(); // Refresh roles list

        layoutStore.showSuccess('Role created successfully');
        return response.data.role;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to create role');
        throw error;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Update an existing role
     */
    async updateRole(roleId, roleData) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await roleManagementService.updateRole(roleId, roleData);

        // Update role in the list if exists
        const roleIndex = this.roles.findIndex(role => role.id === roleId);
        if (roleIndex !== -1) {
          this.roles[roleIndex] = response.data.role;
        }

        // Update selected role if it matches
        if (this.selectedRole && this.selectedRole.id === roleId) {
          this.selectedRole = response.data.role;
        }

        layoutStore.showSuccess('Role updated successfully');
        return response.data.role;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to update role');
        throw error;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Assign permissions to a role
     */
    async assignPermissions(roleId, permissionIds) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await roleManagementService.assignPermissions(roleId, permissionIds);

        // Update role in the list if exists
        const roleIndex = this.roles.findIndex(role => role.id === roleId);
        if (roleIndex !== -1) {
          this.roles[roleIndex] = response.data.role;
        }

        // Update selected role if it matches
        if (this.selectedRole && this.selectedRole.id === roleId) {
          this.selectedRole = response.data.role;
        }

        layoutStore.showSuccess('Permissions assigned successfully');
        return response.data.role;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to assign permissions');
        throw error;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Delete a role
     */
    async deleteRole(roleId) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        await roleManagementService.deleteRole(roleId);

        // Remove role from the list
        this.roles = this.roles.filter(role => role.id !== roleId);

        // Clear selected role if it matches
        if (this.selectedRole && this.selectedRole.id === roleId) {
          this.selectedRole = null;
        }

        layoutStore.showSuccess('Role deleted successfully');
        return true;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to delete role');
        return false;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Update pagination
     */
    updatePagination(pagination) {
      this.pagination = {...this.pagination, ...pagination};
      this.fetchRoles();
    },

    /**
     * Update filters
     */
    updateFilters(filters) {
      this.filters = {...this.filters, ...filters};
      this.pagination.page = 1; // Reset to first page when filtering
      this.fetchRoles();
    },

    /**
     * Clear all filters
     */
    clearFilters() {
      this.filters = {
        search: '',
      };
      this.fetchRoles();
    }
  }
});
