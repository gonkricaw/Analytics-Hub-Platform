<!-- resources/js/views/ContentView.vue -->
<template>
  <div class="content-display-container">
    <!-- Loading indicator -->
    <v-skeleton-loader
      v-if="isLoading"
      class="mx-auto content-skeleton"
      type="article"
    ></v-skeleton-loader>

    <!-- Error display -->
    <v-alert
      v-else-if="error"
      type="error"
      class="content-error"
    >
      {{ errorMessage }}
      <template v-slot:append>
        <v-btn
          density="comfortable"
          icon="mdi-refresh"
          variant="text"
          @click="reloadContent"
        ></v-btn>
      </template>
    </v-alert>

    <!-- Content display based on content type -->
    <div v-else-if="content" class="content-wrapper" ref="contentWrapper">
      <!-- Content header -->
      <div class="content-header mb-6">
        <h1 class="text-h3 font-weight-bold">{{ content.title }}</h1>
        <div class="content-meta d-flex align-center mt-2">
          <v-icon icon="mdi-calendar" size="small" class="mr-1"></v-icon>
          <span class="text-caption text-grey">{{ formatDate(content.published_at) }}</span>
          <v-chip
            v-if="content.type"
            size="x-small"
            color="primary"
            class="ml-4 text-uppercase"
          >
            {{ content.type }}
          </v-chip>
        </div>
      </div>

      <!-- Content body based on type -->
      <div class="content-body">
        <!-- HTML content -->
        <div
          v-if="content.type === 'html'"
          class="content-html"
          v-html="sanitizeHtml(content.content)"
        ></div>

        <!-- Text content -->
        <div v-else-if="content.type === 'text'" class="content-text">
          <p>{{ content.content }}</p>
        </div>

        <!-- Image content -->
        <div v-else-if="content.type === 'image'" class="content-image text-center">
          <v-img
            :src="content.content"
            :alt="content.title"
            class="rounded-lg mx-auto"
            max-height="600"
            cover
          >
            <template v-slot:placeholder>
              <v-row class="fill-height ma-0" align="center" justify="center">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
              </v-row>
            </template>
          </v-img>
          <div v-if="content.additional_data?.caption" class="text-caption mt-2">
            {{ content.additional_data.caption }}
          </div>
        </div>

        <!-- YouTube video content -->
        <div v-else-if="content.type === 'video'" class="content-video">
          <div class="video-container">
            <iframe
              :src="getYoutubeEmbedUrl(content.content)"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
              class="video-iframe"
            ></iframe>
          </div>
          <div v-if="content.additional_data?.description" class="text-body-1 mt-4">
            {{ content.additional_data.description }}
          </div>
        </div>

        <!-- Document content -->
        <div v-else-if="content.type === 'document'" class="content-document">
          <v-card class="pa-4 bg-grey-darken-3">
            <div class="d-flex align-center">
              <v-icon icon="mdi-file-document-outline" size="x-large" class="mr-4 text-primary"></v-icon>
              <div>
                <div class="text-h6">{{ getDocumentName(content.content) }}</div>
                <div class="text-caption text-grey">
                  {{ getDocumentExtension(content.content).toUpperCase() }} Document
                </div>
              </div>
              <v-spacer></v-spacer>
              <v-btn
                color="primary"
                :href="content.content"
                target="_blank"
                prepend-icon="mdi-download"
              >
                Download
              </v-btn>
            </div>
          </v-card>
          <div v-if="isPDF(content.content)" class="mt-6 pdf-preview">
            <v-card>
              <div class="pdf-container">
                <iframe :src="content.content" class="pdf-iframe"></iframe>
              </div>
            </v-card>
          </div>
        </div>

        <!-- Fallback for other content types -->
        <div v-else class="content-fallback">
          <p>{{ content.content }}</p>
        </div>
      </div>
    </div>

    <!-- No content available -->
    <v-card v-else class="content-empty pa-6 text-center">
      <v-icon icon="mdi-alert-circle-outline" size="48" class="mb-4 text-grey"></v-icon>
      <h3 class="text-h5 mb-2">Content Not Available</h3>
      <p class="text-body-1 text-grey">The requested content could not be found or is not available.</p>
      <v-btn
        color="primary"
        class="mt-4"
        :to="{ name: 'home' }"
      >
        Return to Home
      </v-btn>
    </v-card>
  </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { useContentStore } from '@/stores/contentStore';
import { useRoute } from 'vue-router';
import gsap from 'gsap';
import DOMPurify from 'dompurify';

export default {
  name: 'ContentView',

  data() {
    return {
      errorMessage: 'Failed to load content. Please try again.'
    };
  },

  computed: {
    ...mapState(useContentStore, ['content', 'isLoading', 'error']),

    route() {
      return useRoute();
    }
  },

  async mounted() {
    await this.loadContent();
    if (this.content) {
      this.setupContentAnimations();
    }
  },

  methods: {
    ...mapActions(useContentStore, ['fetchContentBySlug', 'fetchContentById', 'clearContent']),

    async loadContent() {
      // Clear previous content
      this.clearContent();

      const { slug, id } = this.route.params;

      if (slug) {
        await this.fetchContentBySlug(slug);
      } else if (id) {
        await this.fetchContentById(id);
      }
    },

    reloadContent() {
      this.loadContent();
    },

    formatDate(dateString) {
      if (!dateString) return '';

      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-US', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      }).format(date);
    },

    sanitizeHtml(html) {
      return DOMPurify.sanitize(html);
    },

    getYoutubeEmbedUrl(url) {
      // Extract YouTube video ID and return embed URL
      const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
      const match = url.match(regex);

      if (match && match[1]) {
        return `https://www.youtube.com/embed/${match[1]}`;
      }

      return url; // Return original URL if not a valid YouTube URL
    },

    getDocumentName(url) {
      // Extract filename from URL
      const urlParts = url.split('/');
      const filename = urlParts[urlParts.length - 1];

      // Remove query parameters if present
      const cleanFilename = filename.split('?')[0];

      // Decode URI component to handle special characters
      return decodeURIComponent(cleanFilename);
    },

    getDocumentExtension(url) {
      const filename = this.getDocumentName(url);
      const parts = filename.split('.');

      if (parts.length > 1) {
        return parts[parts.length - 1].toLowerCase();
      }

      return '';
    },

    isPDF(url) {
      return this.getDocumentExtension(url) === 'pdf';
    },

    setupContentAnimations() {
      this.$nextTick(() => {
        if (!this.$refs.contentWrapper) return;

        const contentHeader = this.$refs.contentWrapper.querySelector('.content-header');
        const contentElements = this.$refs.contentWrapper.querySelectorAll('.content-body > div');

        // Create a timeline for smoother animations
        const tl = gsap.timeline();

        // Animate header
        tl.from(contentHeader, {
          opacity: 0,
          y: -30,
          duration: 0.6,
          ease: 'power2.out'
        });

        // Animate content body
        tl.from(contentElements, {
          opacity: 0,
          y: 30,
          duration: 0.6,
          stagger: 0.1,
          ease: 'power2.out'
        }, '-=0.3');
      });
    }
  },

  watch: {
    // Watch for route changes to update content
    'route.params': {
      handler() {
        this.loadContent();
      },
      deep: true
    }
  }
};
</script>

<style lang="scss" scoped>
.content-display-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px;
}

.content-skeleton {
  min-height: 400px;
  border-radius: 8px;
}

.content-error {
  margin-bottom: 24px;
}

.content-wrapper {
  animation-duration: 0.5s;
  animation-fill-mode: both;
}

.content-header {
  margin-bottom: 32px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.content-body {
  margin-bottom: 48px;
}

:deep(.content-html) {
  line-height: 1.7;

  h1, h2, h3, h4, h5, h6 {
    margin-top: 1.5em;
    margin-bottom: 0.75em;
  }

  p {
    margin-bottom: 1em;
  }

  img {
    max-width: 100%;
    height: auto;
    margin: 1em 0;
    border-radius: 8px;
  }

  a {
    color: var(--v-primary-base);
    text-decoration: none;

    &:hover {
      text-decoration: underline;
    }
  }

  ul, ol {
    margin: 1em 0;
    padding-left: 2em;
  }

  blockquote {
    margin: 1em 0;
    padding: 0.5em 1em;
    border-left: 4px solid var(--v-primary-base);
    background-color: rgba(var(--v-theme-primary), 0.1);
  }
}

.video-container {
  position: relative;
  padding-bottom: 56.25%; /* 16:9 aspect ratio */
  height: 0;
  overflow: hidden;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  margin-bottom: 16px;

  .video-iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
  }
}

.pdf-container {
  position: relative;
  padding-bottom: 100%; /* Square aspect ratio */
  height: 0;
  overflow: hidden;

  .pdf-iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    min-height: 600px;
  }
}
</style>
