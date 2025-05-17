// resources/js/stores/admin/auditLogStore.js
import { defineStore } from 'pinia';
import auditLogService from '@/services/admin/auditLogService';
import { useLayoutStore } from '../layoutStore';

export const useAuditLogStore = defineStore('auditLog', {
  state: () => ({
    logs: [],
    pagination: {
      page: 1,
      itemsPerPage: 20,
      totalItems: 0,
      sortBy: 'created_at',
      sortDesc: true
    },
    filters: {
      search: '',
      user: null,
      action: null,
      modelType: null,
      dateRange: null,
      startDate: null,
      endDate: null
    },
    isLoading: false,
    error: null,
    detailDialogOpen: false,
    selectedLog: null,
    availableUsers: [],
    availableActions: [],
    availableModelTypes: []
  }),

  getters: {
    /**
     * Get formatted logs for data table display
     */
    formattedLogs: (state) => {
      return state.logs.map(log => ({
        ...log,
        actionDisplay: log.action.toUpperCase(),
        actionColor: getActionColor(log.action),
        userDisplay: log.user ? `${log.user.name} (${log.user.email})` : 'System',
        datetime: new Date(log.created_at).toLocaleString(),
        modelDisplay: formatModelType(log.model_type),
        hasOldValues: log.old_values && Object.keys(log.old_values).length > 0,
        hasNewValues: log.new_values && Object.keys(log.new_values).length > 0
      }));
    },

    /**
     * Get user options for filtering
     */
    userOptions: (state) => {
      return state.availableUsers.map(user => ({
        value: user.id,
        title: `${user.name} (${user.email})`
      }));
    },

    /**
     * Get action options for filtering
     */
    actionOptions: (state) => {
      return state.availableActions.map(action => ({
        value: action,
        title: action.toUpperCase()
      }));
    },

    /**
     * Get model type options for filtering
     */
    modelTypeOptions: (state) => {
      return state.availableModelTypes.map(modelType => ({
        value: modelType,
        title: formatModelType(modelType)
      }));
    }
  },

  actions: {
    /**
     * Fetch audit logs with optional filters
     */
    async fetchLogs() {
      this.isLoading = true;
      this.error = null;

      try {
        const params = {
          page: this.pagination.page,
          per_page: this.pagination.itemsPerPage,
          sort_by: this.pagination.sortBy,
          sort_desc: this.pagination.sortDesc ? 'true' : 'false',
          search: this.filters.search || undefined,
          user_id: this.filters.user || undefined,
          action: this.filters.action || undefined,
          model_type: this.filters.modelType || undefined,
          start_date: this.filters.startDate || undefined,
          end_date: this.filters.endDate || undefined
        };

        const response = await auditLogService.getLogs(params);
        this.logs = response.data.logs;
        this.pagination.totalItems = response.data.total || this.logs.length;
      } catch (error) {
        console.error('Error fetching audit logs:', error);
        this.error = error.response?.data?.message || 'Failed to fetch audit logs';
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
     * Fetch filter options
     */
    async fetchFilterOptions() {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await auditLogService.getFilterOptions();
        this.availableUsers = response.data.users || [];
        this.availableActions = response.data.actions || [];
        this.availableModelTypes = response.data.model_types || [];
      } catch (error) {
        console.error('Error fetching filter options:', error);
        this.error = error.response?.data?.message || 'Failed to fetch filter options';
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Get audit log details by ID
     */
    async getLogDetails(id) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await auditLogService.getLog(id);
        this.selectedLog = response.data.log;
        return this.selectedLog;
      } catch (error) {
        console.error(`Error fetching audit log details with ID ${id}:`, error);
        this.error = error.response?.data?.message || 'Failed to fetch audit log details';
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
     * Export audit logs to CSV
     */
    async exportLogs(format = 'csv') {
      this.isLoading = true;
      this.error = null;

      try {
        const params = {
          format,
          search: this.filters.search || undefined,
          user_id: this.filters.user || undefined,
          action: this.filters.action || undefined,
          model_type: this.filters.modelType || undefined,
          start_date: this.filters.startDate || undefined,
          end_date: this.filters.endDate || undefined
        };

        const response = await auditLogService.exportLogs(params);

        // Create a download link
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `audit-logs-${new Date().toISOString().split('T')[0]}.${format}`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: 'Audit logs exported successfully',
          color: 'success'
        });
      } catch (error) {
        console.error('Error exporting audit logs:', error);
        this.error = error.response?.data?.message || 'Failed to export audit logs';
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
     * Set pagination
     */
    setPagination(pagination) {
      this.pagination = { ...this.pagination, ...pagination };
      this.fetchLogs();
    },

    /**
     * Set filters
     */
    setFilters(filters) {
      this.filters = { ...this.filters, ...filters };
      this.pagination.page = 1; // Reset to first page when filters change
      this.fetchLogs();
    },

    /**
     * Reset filters
     */
    resetFilters() {
      this.filters = {
        search: '',
        user: null,
        action: null,
        modelType: null,
        dateRange: null,
        startDate: null,
        endDate: null
      };
      this.pagination.page = 1;
      this.fetchLogs();
    }
  }
});

/**
 * Get color for action type
 */
function getActionColor(action) {
  const colors = {
    'created': 'success',
    'updated': 'info',
    'deleted': 'error',
    'restored': 'warning',
    'login': 'purple',
    'logout': 'grey'
  };
  return colors[action] || 'primary';
}

/**
 * Format model type name for display
 */
function formatModelType(modelType) {
  if (!modelType) return '';

  // Extract class name from namespace
  const parts = modelType.split('\\');
  const className = parts[parts.length - 1];

  // Convert camel case to words with spaces
  return className
    .replace(/([A-Z])/g, ' $1')
    .replace(/^./, str => str.toUpperCase())
    .trim();
}
