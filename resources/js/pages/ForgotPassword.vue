<!-- resources/js/pages/ForgotPassword.vue -->
<template>
  <v-container fluid class="fill-height forgot-password-container pa-0">
    <v-row class="fill-height ma-0">
      <v-col cols="12" md="6" class="auth-image d-none d-md-flex pa-0" v-gsap-fade-in="{ duration: 0.8 }">
        <!-- Left side image with overlay -->
        <div class="image-overlay">
          <div class="content-wrapper">
            <div class="logo-container mb-8" v-gsap-fade-in="{ duration: 1, delay: 0.5 }">
              <img src="https://via.placeholder.com/150x60?text=Logo" alt="Company Logo" />
            </div>
            <h1 class="text-h3 font-weight-bold mb-4" v-gsap-fade-in="{ duration: 1, delay: 0.7 }">
              Password Recovery
            </h1>
            <p class="text-subtitle-1 mb-8" v-gsap-fade-in="{ duration: 1, delay: 0.9 }">
              We'll send you a link to reset your password. Enter your email address below.
            </p>
          </div>
        </div>
      </v-col>
      <v-col cols="12" md="6" class="form-container">
        <v-card class="forgot-password-form mx-auto" max-width="450px" elevation="0" v-gsap-fade-in="{ duration: 0.8, delay: 0.3 }">
          <v-card-title class="text-h4 font-weight-bold pt-8 pb-2">
            Forgot Password
          </v-card-title>
          <v-card-subtitle class="pb-4">
            Enter your email to receive a password reset link
          </v-card-subtitle>
          <v-card-text>
            <v-form ref="form" @submit.prevent="submitForgotPassword" v-gsap-fade-in="{ duration: 0.8, delay: 0.5 }">
              <v-text-field
                v-model="email"
                label="Email"
                type="email"
                required
                prepend-inner-icon="mdi-email"
                variant="outlined"
                class="mb-6"
                :rules="emailRules"
                hint="Enter the email address associated with your account"
                persistent-hint
              ></v-text-field>

              <v-btn
                type="submit"
                color="primary"
                block
                size="large"
                :loading="isLoading"
                elevation="1"
                class="py-6 text-subtitle-1 mb-4"
                v-gsap-hover="{ scale: 1.03 }"
              >
                Send Reset Link
              </v-btn>

              <div class="d-flex justify-center">
                <v-btn
                  variant="text"
                  color="primary"
                  @click="goBackToLogin"
                  prepend-icon="mdi-arrow-left"
                  class="px-0"
                >
                  Back to Login
                </v-btn>
              </div>

              <!-- Success message -->
              <v-alert
                v-if="successMessage"
                type="success"
                variant="tonal"
                closable
                class="mt-4"
                v-gsap-fade-in
              >
                {{ successMessage }}
              </v-alert>

              <!-- Error message -->
              <v-alert
                v-if="errorMessage"
                type="error"
                variant="tonal"
                closable
                class="mt-4"
                v-gsap-fade-in
              >
                {{ errorMessage }}
              </v-alert>
            </v-form>
          </v-card-text>

          <v-card-actions class="justify-center pb-6">
            <p class="text-caption text-medium-emphasis">
              &copy; {{ new Date().getFullYear() }} Indonet. All rights reserved.
            </p>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { mapActions, mapState } from 'pinia';
import { useAuthStore } from '@/stores/authStore';

export default {
  name: 'ForgotPasswordView',
  data() {
    return {
      email: '',
      successMessage: '',
      errorMessage: '',
      emailRules: [
        v => !!v || 'Email is required',
        v => /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(v) || 'Email must be valid'
      ]
    };
  },
  computed: {
    ...mapState(useAuthStore, ['isLoading'])
  },
  methods: {
    ...mapActions(useAuthStore, ['handleForgotPassword']),

    async submitForgotPassword() {
      this.successMessage = '';
      this.errorMessage = '';

      // Validate form
      const isValid = await this.$refs.form.validate();
      if (!isValid) return;

      try {
        await this.handleForgotPassword(this.email);
        this.successMessage = 'Password reset email sent. Check your inbox for further instructions.';
        this.email = ''; // Clear form
        this.$refs.form.reset();
      } catch (error) {
        this.errorMessage = error || 'Failed to send password reset email. Please try again.';
      }
    },

    goBackToLogin() {
      this.$router.push({ name: 'login' });
    }
  }
};
</script>

<style lang="scss" scoped>
.forgot-password-container {
  background-color: var(--v-background-base);
}

.auth-image {
  background-color: var(--v-primary-base);
  min-height: 100vh;
  position: relative;
  overflow: hidden;

  .image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('https://picsum.photos/1200/1600?random=11');
    background-size: cover;
    background-position: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;

    &::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(140, 62, 255, 0.85); // Primary color with opacity
    }

    .content-wrapper {
      position: relative;
      z-index: 1;
      color: white;
      padding: 2rem;
      max-width: 500px;
      text-align: center;
    }
  }
}

.form-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 2rem;
}
</style>
