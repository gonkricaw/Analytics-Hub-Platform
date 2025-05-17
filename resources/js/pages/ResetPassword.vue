<!-- resources/js/pages/ResetPassword.vue -->
<template>
  <v-container fluid class="fill-height reset-password-container pa-0">
    <v-row class="fill-height ma-0">
      <v-col cols="12" md="6" class="auth-image d-none d-md-flex pa-0" v-gsap-fade-in="{ duration: 0.8 }">
        <!-- Left side image with overlay -->
        <div class="image-overlay">
          <div class="content-wrapper">
            <div class="logo-container mb-8" v-gsap-fade-in="{ duration: 1, delay: 0.5 }">
              <img src="https://via.placeholder.com/150x60?text=Logo" alt="Company Logo" />
            </div>
            <h1 class="text-h3 font-weight-bold mb-4" v-gsap-fade-in="{ duration: 1, delay: 0.7 }">
              Reset Your Password
            </h1>
            <p class="text-subtitle-1 mb-8" v-gsap-fade-in="{ duration: 1, delay: 0.9 }">
              Create a new secure password for your account.
            </p>
          </div>
        </div>
      </v-col>
      <v-col cols="12" md="6" class="form-container">
        <v-card class="reset-password-form mx-auto" max-width="450px" elevation="0" v-gsap-fade-in="{ duration: 0.8, delay: 0.3 }">
          <v-card-title class="text-h4 font-weight-bold pt-8 pb-2">
            Reset Password
          </v-card-title>
          <v-card-subtitle class="pb-4">
            Enter your new password below
          </v-card-subtitle>
          <v-card-text>
            <v-form ref="form" @submit.prevent="submitResetPassword" v-gsap-fade-in="{ duration: 0.8, delay: 0.5 }">
              <v-text-field
                v-model="email"
                label="Email"
                type="email"
                required
                prepend-inner-icon="mdi-email"
                variant="outlined"
                class="mb-2"
                :rules="emailRules"
                readonly
              ></v-text-field>

              <v-text-field
                v-model="password"
                label="New Password"
                :type="showPassword ? 'text' : 'password'"
                required
                prepend-inner-icon="mdi-lock"
                :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                @click:append-inner="showPassword = !showPassword"
                variant="outlined"
                class="mb-2"
                :rules="passwordRules"
                hint="Password must be at least 8 characters with letters, numbers, and special characters"
                persistent-hint
              ></v-text-field>

              <v-text-field
                v-model="passwordConfirmation"
                label="Confirm New Password"
                :type="showConfirmPassword ? 'text' : 'password'"
                required
                prepend-inner-icon="mdi-lock-check"
                :append-inner-icon="showConfirmPassword ? 'mdi-eye' : 'mdi-eye-off'"
                @click:append-inner="showConfirmPassword = !showConfirmPassword"
                variant="outlined"
                class="mb-6"
                :rules="[...passwordRules, passwordMatchRule]"
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
                :disabled="!isFormValid"
              >
                Reset Password
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

    <!-- Success dialog -->
    <v-dialog v-model="showSuccessDialog" max-width="500" persistent>
      <v-card>
        <v-card-title class="text-h5 bg-success text-white">
          <v-icon start color="white" class="mr-2">mdi-check-circle</v-icon>
          Password Reset Successful
        </v-card-title>
        <v-card-text class="pa-4 mt-4">
          <p>Your password has been successfully reset. You can now log in with your new password.</p>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            variant="elevated"
            @click="goToLoginPage"
            v-gsap-hover="{ scale: 1.05 }"
          >
            Go to Login
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import { mapActions, mapState } from 'pinia';
import { useAuthStore } from '@/stores/authStore';

export default {
  name: 'ResetPasswordView',
  data() {
    return {
      token: '',
      email: '',
      password: '',
      passwordConfirmation: '',
      showPassword: false,
      showConfirmPassword: false,
      errorMessage: '',
      showSuccessDialog: false,
      isFormValid: false,
      emailRules: [
        v => !!v || 'Email is required',
        v => /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(v) || 'Email must be valid'
      ],
      passwordRules: [
        v => !!v || 'Password is required',
        v => v.length >= 8 || 'Password must be at least 8 characters',
        v => /[A-Z]/.test(v) || 'Password must contain at least one uppercase letter',
        v => /[a-z]/.test(v) || 'Password must contain at least one lowercase letter',
        v => /[0-9]/.test(v) || 'Password must contain at least one number',
        v => /[^A-Za-z0-9]/.test(v) || 'Password must contain at least one special character'
      ]
    };
  },
  computed: {
    ...mapState(useAuthStore, ['isLoading']),

    passwordMatchRule() {
      return () => this.password === this.passwordConfirmation || 'Passwords must match';
    }
  },
  methods: {
    ...mapActions(useAuthStore, ['handleResetPassword']),

    async submitResetPassword() {
      this.errorMessage = '';

      // Validate form
      const isValid = await this.$refs.form.validate();
      if (!isValid) return;

      const resetData = {
        token: this.token,
        email: this.email,
        password: this.password,
        password_confirmation: this.passwordConfirmation
      };

      try {
        await this.handleResetPassword(resetData);
        this.showSuccessDialog = true;
      } catch (error) {
        this.errorMessage = error || 'Failed to reset password. Please try again or request a new reset link.';
      }
    },

    goBackToLogin() {
      this.$router.push({ name: 'login' });
    },

    goToLoginPage() {
      this.showSuccessDialog = false;
      this.$router.push({ name: 'login' });
    }
  },
  watch: {
    '$refs.form': {
      handler() {
        if (this.$refs.form) {
          this.$refs.form.validate();
        }
      },
      deep: true
    }
  },
  async created() {
    // Get token and email from URL query parameters
    const { token, email } = this.$route.query;

    if (!token || !email) {
      this.errorMessage = 'Invalid password reset link. Please request a new one.';
      return;
    }

    this.token = token;
    this.email = email;
  }
};
</script>

<style lang="scss" scoped>
.reset-password-container {
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
    background-image: url('https://picsum.photos/1200/1600?random=12');
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
