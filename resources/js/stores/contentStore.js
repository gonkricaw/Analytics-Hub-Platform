// resources/js/stores/contentStore.js
import { defineStore } from 'pinia';
import contentService from '../services/contentService';
import { useLayoutStore } from './layoutStore';

export const useContentStore = defineStore('content', {
  state: () => ({
    currentContent: null,
    contentList: [],
    pagination: {
      page: 1,
      perPage: 10,
      total: 0
    },
    embedUrl: null,
    isLoading: false,
    error: null
  }),

  getters: {
    /**
     * Get current content
     */
    content: (state) => state.currentContent,

    /**
     * Check if content is loaded
     */
    hasContent: (state) => !!state.currentContent,

    /**
     * Get content type
     */
    contentType: (state) => state.currentContent?.type || 'text',

    /**
     * Get content list
     */
    contentItems: (state) => state.contentList,

    /**
     * Get embed URL
     */
    targetEmbedUrl: (state) => state.embedUrl?.target_url || null
  },

  actions: {
    /**
     * Fetch content by slug
     * @param {String} slug - Content slug
     */
    async fetchContentBySlug(slug) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await contentService.getContentBySlug(slug);

        if (response.data && response.data.data) {
          this.currentContent = response.data.data;
          return this.currentContent;
        }

        throw new Error('Invalid content data received');
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to load content');
        return null;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Fetch content by ID
     * @param {Number} id - Content ID
     */
    async fetchContentById(id) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await contentService.getContentById(id);

        if (response.data && response.data.data) {
          this.currentContent = response.data.data;
          return this.currentContent;
        }

        throw new Error('Invalid content data received');
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to load content');
        return null;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Fetch content by type
     * @param {String} type - Content type
     * @param {Number} page - Page number
     * @param {Number} perPage - Items per page
     */
    async fetchContentByType(type, page = 1, perPage = 10) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        this.pagination.page = page;
        this.pagination.perPage = perPage;

        const response = await contentService.getContentByType(type, page, perPage);

        if (response.data && response.data.data) {
          this.contentList = response.data.data.data || [];
          this.pagination.total = response.data.data.total || 0;
          return this.contentList;
        }

        throw new Error('Invalid content list data received');
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to load content list');
        return [];
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Fetch embed URL by UUID
     * @param {String} uuid - Embed UUID
     */
    async fetchEmbedUrl(uuid) {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await contentService.getEmbedUrl(uuid);

        if (response.data && response.data.data) {
          this.embedUrl = response.data.data;
          return this.embedUrl;
        }

        throw new Error('Invalid embed URL data received');
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to load embed URL');
        return null;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Clear content data
     */
    clearContent() {
      this.currentContent = null;
      this.error = null;
    }
  }
});
