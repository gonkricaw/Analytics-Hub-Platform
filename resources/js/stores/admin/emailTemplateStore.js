// resources/js/stores/admin/emailTemplateStore.js
import { defineStore } from 'pinia';
import emailTemplateService from '@/services/admin/emailTemplateService';
import { useLayoutStore } from '../layoutStore';

export const useEmailTemplateStore = defineStore('emailTemplate', {
  state: () => ({
    templates: [],
    selectedTemplate: null,
    pagination: {
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,
      sortBy: 'name',
      sortDesc: false
    },
    filters: {
      search: '',
      isActive: null
    },
    isLoading: false,
    error: null,
    editDialogOpen: false,
    previewDialogOpen: false
  }),

  getters: {
    /**
     * Get formatted templates for data table display
     */
    formattedTemplates: (state) => {
      return state.templates.map(template => ({
        ...template,
        statusText: template.is_active ? 'Active' : 'Inactive',
        statusColor: template.is_active ? 'success' : 'error',
        placeholderText: template.placeholders ? template.placeholders.join(', ') : '',
        updatedAt: new Date(template.updated_at).toLocaleString()
      }));
    },

    /**
     * Get template by ID
     */
    getTemplateById: (state) => (id) => {
      return state.templates.find(t => t.id === id);
    },

    /**
     * Get template by slug
     */
    getTemplateBySlug: (state) => (slug) => {
      return state.templates.find(t => t.slug === slug);
    }
  },

  actions: {
    /**
     * Fetch email templates with optional filters
     */
    async fetchTemplates() {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await emailTemplateService.getTemplates();
        this.templates = response.data.templates;

        // Apply client-side filtering and pagination
        let filteredTemplates = [...this.templates];

        if (this.filters.search) {
          const searchTerm = this.filters.search.toLowerCase();
          filteredTemplates = filteredTemplates.filter(template =>
            template.name.toLowerCase().includes(searchTerm) ||
            template.subject.toLowerCase().includes(searchTerm) ||
            template.slug.toLowerCase().includes(searchTerm)
          );
        }

        if (this.filters.isActive !== null) {
          filteredTemplates = filteredTemplates.filter(template =>
            template.is_active === this.filters.isActive
          );
        }

        // Sort
        filteredTemplates.sort((a, b) => {
          let valueA = a[this.pagination.sortBy];
          let valueB = b[this.pagination.sortBy];

          if (typeof valueA === 'string') {
            valueA = valueA.toLowerCase();
            valueB = valueB.toLowerCase();
          }

          if (this.pagination.sortDesc) {
            return valueA < valueB ? 1 : -1;
          } else {
            return valueA > valueB ? 1 : -1;
          }
        });

        // Calculate total for pagination
        this.pagination.totalItems = filteredTemplates.length;

        // Apply pagination
        const start = (this.pagination.page - 1) * this.pagination.itemsPerPage;
        const end = start + this.pagination.itemsPerPage;
        this.templates = filteredTemplates.slice(start, end);
      } catch (error) {
        console.error('Error fetching email templates:', error);
        this.error = error.response?.data?.message || 'Failed to fetch email templates';
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
     * Fetch a single template by ID
     */
    async fetchTemplate(id) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await emailTemplateService.getTemplate(id);
        this.selectedTemplate = response.data.template;
        return this.selectedTemplate;
      } catch (error) {
        console.error(`Error fetching email template with ID ${id}:`, error);
        this.error = error.response?.data?.message || 'Failed to fetch email template';
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
     * Create a new email template
     */
    async createTemplate(templateData) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await emailTemplateService.createTemplate(templateData);
        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: 'Email template created successfully',
          color: 'success'
        });

        // Refresh templates
        await this.fetchTemplates();
        return response.data.template;
      } catch (error) {
        console.error('Error creating email template:', error);
        this.error = error.response?.data?.message || 'Failed to create email template';
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
     * Update an existing email template
     */
    async updateTemplate(id, templateData) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await emailTemplateService.updateTemplate(id, templateData);
        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: 'Email template updated successfully',
          color: 'success'
        });

        // Refresh templates
        await this.fetchTemplates();
        return response.data.template;
      } catch (error) {
        console.error(`Error updating email template with ID ${id}:`, error);
        this.error = error.response?.data?.message || 'Failed to update email template';
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
     * Delete an email template
     */
    async deleteTemplate(id) {
      this.isLoading = true;
      this.error = null;

      try {
        await emailTemplateService.deleteTemplate(id);

        const layoutStore = useLayoutStore();
        layoutStore.showSnackbar({
          text: 'Email template deleted successfully',
          color: 'success'
        });

        // Refresh templates
        await this.fetchTemplates();
        return true;
      } catch (error) {
        console.error(`Error deleting email template with ID ${id}:`, error);
        this.error = error.response?.data?.message || 'Failed to delete email template';
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
      this.fetchTemplates();
    },

    /**
     * Set filters
     */
    setFilters(filters) {
      this.filters = { ...this.filters, ...filters };
      this.pagination.page = 1; // Reset to first page when filters change
      this.fetchTemplates();
    },

    /**
     * Reset filters
     */
    resetFilters() {
      this.filters = {
        search: '',
        isActive: null
      };
      this.pagination.page = 1;
      this.fetchTemplates();
    }
  }
});
