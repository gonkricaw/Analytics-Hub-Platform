// resources/js/stores/admin/contentManagementStore.js
import { defineStore } from 'pinia';
import contentManagementService from '@/services/admin/contentManagementService';
import { useLayoutStore } from '../layoutStore';

export const useContentManagementStore = defineStore('contentManagement', {
  state: () => ({
    contents: [],
    selectedContent: null,
    pagination: {
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,
      sortBy: 'created_at',
      sortDesc: true
    },
    filters: {
      search: '',
      type: null,
      isPublished: null
    },
    isLoading: false,
    error: null,
    contentTypes: [
      { value: 'page', title: 'Page' },
      { value: 'news', title: 'News' },
      { value: 'faq', title: 'FAQ' },
      { value: 'document', title: 'Document' },
      { value: 'video', title: 'Video' },
      { value: 'image', title: 'Image' },
    ],
    editorContent: ''
  }),

  getters: {
    /**
     * Get formatted contents for data table display
     */
    formattedContents: (state) => {
      return state.contents.map(content => ({
        ...content,
        statusText: content.is_published ? 'Published' : 'Draft',
        statusColor: content.is_published ? 'success' : 'warning',
        typeBadge: state.contentTypes.find(t => t.value === content.type)?.title || content.type
      }));
    },

    /**
     * Get content type options for select
     */
    contentTypeOptions: (state) => {
      return state.contentTypes;
    },

    /**
     * Get status filter options
     */
    statusOptions: () => [
      { value: true, title: 'Published' },
      { value: false, title: 'Draft' }
    ]
  },

  actions: {
    /**
     * Fetch contents with pagination and filters
     */
    async fetchContents() {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await contentManagementService.getContents({
          page: this.pagination.page,
          perPage: this.pagination.itemsPerPage,
          sortBy: this.pagination.sortBy,
          sortDesc: this.pagination.sortDesc ? 'desc' : 'asc',
          search: this.filters.search,
          type: this.filters.type,
          is_published: this.filters.isPublished
        });

        this.contents = response.data.contents;
        this.pagination.totalItems = response.data.total;

        return this.contents;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to fetch contents');
        return [];
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Fetch a single content by ID
     */
    async fetchContent(contentId) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await contentManagementService.getContent(contentId);
        this.selectedContent = response.data.content;

        // Set editor content for rich text editor
        if (this.selectedContent.body) {
          this.editorContent = this.selectedContent.body;
        }

        return this.selectedContent;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to fetch content details');
        return null;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Create new content
     */
    async createContent(contentData) {
      const layoutStore = useLayoutStore();

      // Create FormData for file upload support
      const formData = new FormData();

      // Append all content data
      Object.keys(contentData).forEach(key => {
        if (key === 'file' && contentData[key]) {
          formData.append('file', contentData[key]);
        } else if (contentData[key] !== null && contentData[key] !== undefined) {
          formData.append(key, contentData[key]);
        }
      });

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await contentManagementService.createContent(formData);
        await this.fetchContents(); // Refresh content list

        layoutStore.showSuccess('Content created successfully');
        return response.data.content;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to create content');
        throw error;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Update existing content
     */
    async updateContent(contentId, contentData) {
      const layoutStore = useLayoutStore();

      // Create FormData for file upload support
      const formData = new FormData();
      formData.append('_method', 'PUT'); // Laravel requires _method for PUT with FormData

      // Append all content data
      Object.keys(contentData).forEach(key => {
        if (key === 'file' && contentData[key]) {
          formData.append('file', contentData[key]);
        } else if (contentData[key] !== null && contentData[key] !== undefined) {
          formData.append(key, contentData[key]);
        }
      });

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await contentManagementService.updateContent(contentId, formData);

        // Update content in the list if exists
        const contentIndex = this.contents.findIndex(content => content.id === contentId);
        if (contentIndex !== -1) {
          this.contents[contentIndex] = response.data.content;
        }

        // Update selected content if it matches
        if (this.selectedContent && this.selectedContent.id === contentId) {
          this.selectedContent = response.data.content;
        }

        layoutStore.showSuccess('Content updated successfully');
        return response.data.content;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to update content');
        throw error;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Delete content
     */
    async deleteContent(contentId) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        await contentManagementService.deleteContent(contentId);

        // Remove content from the list
        this.contents = this.contents.filter(content => content.id !== contentId);

        // Clear selected content if it matches
        if (this.selectedContent && this.selectedContent.id === contentId) {
          this.selectedContent = null;
        }

        layoutStore.showSuccess('Content deleted successfully');
        return true;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to delete content');
        return false;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Toggle content publish status
     */
    async toggleContentStatus(contentId, isPublished) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await contentManagementService.updateContent(contentId, {
          is_published: isPublished
        });

        // Update content in the list if exists
        const contentIndex = this.contents.findIndex(content => content.id === contentId);
        if (contentIndex !== -1) {
          this.contents[contentIndex].is_published = isPublished;
        }

        // Update selected content if it matches
        if (this.selectedContent && this.selectedContent.id === contentId) {
          this.selectedContent.is_published = isPublished;
        }

        const statusText = isPublished ? 'published' : 'unpublished';
        layoutStore.showSuccess(`Content ${statusText} successfully`);

        return response.data.content;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to update content status');
        throw error;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Set editor content
     */
    setEditorContent(content) {
      this.editorContent = content;
    },

    /**
     * Update pagination
     */
    updatePagination(pagination) {
      this.pagination = {...this.pagination, ...pagination};
      this.fetchContents();
    },

    /**
     * Update filters
     */
    updateFilters(filters) {
      this.filters = {...this.filters, ...filters};
      this.pagination.page = 1; // Reset to first page when filtering
      this.fetchContents();
    },

    /**
     * Clear all filters
     */
    clearFilters() {
      this.filters = {
        search: '',
        type: null,
        isPublished: null
      };
      this.fetchContents();
    }
  }
});
