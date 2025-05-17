<!-- resources/js/components/TermsAndConditionsDialog.vue -->
<template>
  <v-dialog v-model="dialogVisible" persistent max-width="700" v-gsap-fade-in>
    <v-card elevation="5">
      <v-card-title class="text-h5 bg-primary text-white">
        <v-icon start color="white" class="mr-2">mdi-file-document-outline</v-icon>
        Terms and Conditions
      </v-card-title>

      <v-card-text class="pa-4 mt-4">
        <div v-if="isLoading" class="d-flex justify-center align-center my-8">
          <v-progress-circular indeterminate color="primary"></v-progress-circular>
        </div>

        <div v-else>
          <div v-if="termsContent" v-html="termsContent" class="terms-content"></div>
          <div v-else class="text-center my-6">
            <v-alert type="warning" variant="tonal">
              Could not load Terms and Conditions. Please try again later.
            </v-alert>
          </div>
        </div>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions class="pa-4">
        <div class="text-caption text-medium-emphasis">
          Version {{ termsVersion }} - Last updated: {{ formattedUpdatedDate }}
        </div>
        <v-spacer></v-spacer>
        <v-btn
          color="error"
          variant="text"
          @click="handleDecline"
          :disabled="isLoading"
          v-gsap-hover="{ scale: 1.05 }"
        >
          Decline
        </v-btn>
        <v-btn
          color="primary"
          variant="elevated"
          @click="handleAccept"
          :loading="isAccepting"
          :disabled="isLoading || isAccepting"
          v-gsap-hover="{ scale: 1.05 }"
        >
          Accept
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapActions } from 'pinia';
import { useAuthStore } from '@/stores/authStore';
import authService from '@/services/authService';

export default {
  name: 'TermsAndConditionsDialog',
  props: {
    show: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isLoading: true,
      isAccepting: false,
      termsId: null,
      termsContent: '',
      termsVersion: '1.0',
      updatedDate: null
    };
  },
  computed: {
    dialogVisible: {
      get() {
        return this.show;
      },
      set(value) {
        if (!value) {
          this.$emit('update:show', false);
        }
      }
    },
    formattedUpdatedDate() {
      if (!this.updatedDate) return 'N/A';

      return new Date(this.updatedDate).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    }
  },
  methods: {
    ...mapActions(useAuthStore, ['agreeToTerms']),

    async fetchTermsAndConditions() {
      this.isLoading = true;

      try {
        const response = await authService.getLatestTerms();
        const terms = response.data;

        this.termsId = terms.id;
        this.termsContent = terms.content;
        this.termsVersion = terms.version || '1.0';
        this.updatedDate = terms.updated_at || terms.created_at;
      } catch (error) {
        console.error('Error fetching terms and conditions:', error);
        this.termsContent = '<p>Unable to load Terms and Conditions. Please try again later.</p>';
      } finally {
        this.isLoading = false;
      }
    },

    async handleAccept() {
      if (!this.termsId) return;

      this.isAccepting = true;

      try {
        await this.$emit('accept', this.termsId);
      } catch (error) {
        console.error('Error accepting terms:', error);
      } finally {
        this.isAccepting = false;
      }
    },

    handleDecline() {
      this.$emit('decline');
    }
  },
  watch: {
    show(newValue) {
      if (newValue) {
        this.fetchTermsAndConditions();
      }
    }
  },
  mounted() {
    if (this.show) {
      this.fetchTermsAndConditions();
    }
  }
};
</script>

<style lang="scss" scoped>
.terms-content {
  max-height: 400px;
  overflow-y: auto;
  padding: 0 8px;

  &::-webkit-scrollbar {
    width: 8px;
  }

  &::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
  }

  &::-webkit-scrollbar-thumb {
    background: var(--v-primary-lighten1);
    border-radius: 4px;
  }

  &::-webkit-scrollbar-thumb:hover {
    background: var(--v-primary-base);
  }
}
</style>
