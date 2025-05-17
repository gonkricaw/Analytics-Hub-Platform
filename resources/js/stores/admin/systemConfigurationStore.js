// resources/js/stores/admin/systemConfigurationStore.js
import { defineStore } from 'pinia';
import systemConfigurationService from '@/services/admin/systemConfigurationService';
import { useLayoutStore } from '../layoutStore';

export const useSystemConfigurationStore = defineStore('systemConfiguration', {
  state: () => ({
    configurations: [],
    configurationGroups: [],
    selectedConfig: null,
    pagination: {
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,
      sortBy: 'key',
      sortDesc: false
    },
    filters: {
      search: '',
      group: null,
      isPublic: null
    },
    isLoading: false,
    error: null,
    editDialogOpen: false
  }),

  getters: {
    /**
     * Get configurations grouped by their group property
     */
    groupedConfigurations: (state) => {
      const grouped = {};
      state.configurations.forEach(config => {
        if (!grouped[config.group]) {
          grouped[config.group] = [];
        }
        grouped[config.group].push(config);
      });
      return grouped;
    },

    /**
     * Get configuration group options
     */
    groupOptions: (state) => {
      return state.configurationGroups.map(group => ({
        value: group,
        title: group.charAt(0).toUpperCase() + group.slice(1)
      }));
    },

    /**
     * Get a configuration value by key
     */
    getConfigByKey: (state) => (key) => {
      const config = state.configurations.find(c => c.key === key);
      return config ? config.typed_value : null;
    }
  },

  actions: {
    /**
     * Fetch all system configurations with optional filters
     */
    async fetchConfigurations() {
      this.isLoading = true;
      this.error = null;

      try {
        const params = {
          page: this.pagination.page,
          per_page: this.pagination.itemsPerPage,
          sort_by: this.pagination.sortBy,
          sort_desc: this.pagination.sortDesc,
          key: this.filters.search || undefined,
          group: this.filters.group || undefined,
          is_public: this.filters.isPublic !== null ? this.filters.isPublic : undefined
        };

        const response = await systemConfigurationService.getConfigurations(params);
        this.configurations = response.data.configurations;
        this.pagination.totalItems = response.data.total || this.configurations.length;
      } catch (error) {
        console.error('Error fetching system configurations:', error);
        this.error = error.response?.data?.message || 'Failed to fetch system configurations';
        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: this.error,
          color: 'error'
        });
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Fetch configuration groups for filtering
     */
    async fetchConfigurationGroups() {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await systemConfigurationService.getConfigurationGroups();
        this.configurationGroups = response.data.groups;
      } catch (error) {
        console.error('Error fetching configuration groups:', error);
        this.error = error.response?.data?.message || 'Failed to fetch configuration groups';
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Get a single system configuration by key
     */
    async fetchConfiguration(key) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await systemConfigurationService.getConfiguration(key);
        this.selectedConfig = response.data.configuration;
        return this.selectedConfig;
      } catch (error) {
        console.error(`Error fetching configuration with key ${key}:`, error);
        this.error = error.response?.data?.message || 'Failed to fetch configuration';
        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: this.error,
          color: 'error'
        });
        return null;
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Create a new system configuration
     */
    async createConfiguration(configData) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await systemConfigurationService.createConfiguration(configData);
        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: 'System configuration created successfully',
          color: 'success'
        });

        // Refresh configurations
        await this.fetchConfigurations();
        return response.data.configuration;
      } catch (error) {
        console.error('Error creating system configuration:', error);
        this.error = error.response?.data?.message || 'Failed to create system configuration';
        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: this.error,
          color: 'error'
        });
        return null;
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Update an existing system configuration
     */
    async updateConfiguration(key, configData) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await systemConfigurationService.updateConfiguration(key, configData);
        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: 'System configuration updated successfully',
          color: 'success'
        });

        // Refresh configurations
        await this.fetchConfigurations();
        return response.data.configuration;
      } catch (error) {
        console.error(`Error updating configuration with key ${key}:`, error);
        this.error = error.response?.data?.message || 'Failed to update configuration';
        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: this.error,
          color: 'error'
        });
        return null;
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Delete a system configuration
     */
    async deleteConfiguration(key) {
      this.isLoading = true;
      this.error = null;

      try {
        await systemConfigurationService.deleteConfiguration(key);

        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: 'System configuration deleted successfully',
          color: 'success'
        });

        // Refresh configurations
        await this.fetchConfigurations();
        return true;
      } catch (error) {
        console.error(`Error deleting configuration with key ${key}:`, error);
        this.error = error.response?.data?.message || 'Failed to delete configuration';
        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: this.error,
          color: 'error'
        });
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Bulk update system configurations
     */
    async bulkUpdateConfigurations(configDataArray) {
      this.isLoading = true;
      this.error = null;

      try {
        await systemConfigurationService.bulkUpdateConfigurations({ configurations: configDataArray });

        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: 'System configurations updated successfully',
          color: 'success'
        });

        // Refresh configurations
        await this.fetchConfigurations();
        return true;
      } catch (error) {
        console.error('Error updating system configurations:', error);
        this.error = error.response?.data?.message || 'Failed to update configurations';
        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: this.error,
          color: 'error'
        });
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Set pagination
     */
    setPagination(pagination) {
      this.pagination = { ...this.pagination, ...pagination };
      this.fetchConfigurations();
    },

    /**
     * Set filters
     */
    setFilters(filters) {
      this.filters = { ...this.filters, ...filters };
      this.pagination.page = 1; // Reset to first page when filters change
      this.fetchConfigurations();
    },

    /**
     * Reset filters
     */
    resetFilters() {
      this.filters = {
        search: '',
        group: null,
        isPublic: null
      };
      this.pagination.page = 1;
      this.fetchConfigurations();
    }
  }
});
