<!-- resources/js/pages/Login.vue -->
<template>
  <v-container fluid class="fill-height login-container pa-0">
    <v-row class="fill-height ma-0">
      <v-col cols="12" md="6" class="login-image d-none d-md-flex pa-0" v-gsap-fade-in="{ duration: 0.8 }">
        <!-- Left side image with overlay -->
        <div class="image-overlay">
          <div class="content-wrapper">
            <div class="logo-container mb-8" v-gsap-fade-in="{ duration: 1, delay: 0.5 }">
              <img src="https://via.placeholder.com/150x60?text=Logo" alt="Company Logo" />
            </div>
            <h1 class="text-h3 font-weight-bold mb-4" v-gsap-fade-in="{ duration: 1, delay: 0.7 }">
              Analytics Hub Platform
            </h1>
            <p class="text-subtitle-1 mb-8" v-gsap-fade-in="{ duration: 1, delay: 0.9 }">
              Unlock insights, drive decisions, and transform your business with advanced analytics.
            </p>
          </div>
        </div>
      </v-col>
      <v-col cols="12" md="6" class="login-form-container">
        <v-card class="login-form mx-auto" max-width="450px" elevation="0" v-gsap-fade-in="{ duration: 0.8, delay: 0.3 }">
          <v-card-title class="text-h4 font-weight-bold pt-8 pb-2">
            Welcome Back
          </v-card-title>
          <v-card-subtitle class="pb-4">
            Sign in to your account
          </v-card-subtitle>
          <v-card-text>
            <v-form ref="form" @submit.prevent="handleLogin" v-gsap-fade-in="{ duration: 0.8, delay: 0.5 }">
              <v-text-field
                v-model="email"
                label="Email"
                type="email"
                required
                prepend-inner-icon="mdi-email"
                variant="outlined"
                class="mb-2"
                hint="Enter your company email"
                persistent-hint
              ></v-text-field>

              <v-text-field
                v-model="password"
                label="Password"
                type="password"
                required
                prepend-inner-icon="mdi-lock"
                variant="outlined"
                class="mb-2"
                hint="Enter your password"
                persistent-hint
              ></v-text-field>

              <div class="d-flex justify-space-between align-center mb-6">
                <v-checkbox
                  v-model="rememberMe"
                  label="Remember me"
                  hide-details
                  color="primary"
                  density="compact"
                ></v-checkbox>
                <a @click.prevent="redirectToForgotPassword" href="#" class="text-primary text-decoration-none">Forgot Password?</a>
              </div>

              <v-btn
                type="submit"
                color="primary"
                block
                size="large"
                :loading="isLoading"
                elevation="1"
                class="py-6 text-subtitle-1"
                v-gsap-hover="{ scale: 1.03 }"
              >
                Sign In
              </v-btn>

              <!-- Flash message for login errors -->
              <v-alert
                v-if="loginError"
                type="error"
                variant="tonal"
                closable
                class="mt-4"
                v-gsap-fade-in
              >
                {{ loginError }}
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

    <!-- Terms & Conditions Dialog -->
    <TermsAndConditionsDialog
      v-if="showTermsDialog"
      :show="showTermsDialog"
      @accept="acceptTerms"
      @decline="declineTerms"
    />
  </v-container>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { useAuthStore } from '@/stores/authStore';
import TermsAndConditionsDialog from '@/components/TermsAndConditionsDialog.vue';

export default {
  name: 'LoginPage',
  components: {
    TermsAndConditionsDialog
  },
  data() {
    return {
      email: '',
      password: '',
      rememberMe: false,
      showTermsDialog: false,
      termId: null,
      termsContent: ''
    };
  },
  computed: {
    ...mapState(useAuthStore, ['isLoading', 'loginError', 'requiresTermsAgreement'])
  },
  methods: {
    ...mapActions(useAuthStore, ['login', 'logout', 'agreeToTerms']),

    async handleLogin() {
      const credentials = {
        email: this.email,
        password: this.password,
        remember_me: this.rememberMe
      };

      const success = await this.login(credentials);

      if (success) {
        if (this.requiresTermsAgreement) {
          // Show terms and conditions dialog
          this.showTermsDialog = true;
        } else {
          // Navigate to home page
          this.$router.push({ name: 'home' });
        }
      }
    },

    async acceptTerms(termId) {
      const success = await this.agreeToTerms(termId);

      if (success) {
        this.showTermsDialog = false;
        // Navigate to home page after accepting terms
        this.$router.push({ name: 'home' });
      }
    },

    declineTerms() {
      this.showTermsDialog = false;
      // Log the user out
      this.logout();
      // Reset form
      this.email = '';
      this.password = '';
    },

    redirectToForgotPassword() {
      this.$router.push({ name: 'forgot-password' });
    }
  }
}
</script>

<style lang="scss" scoped>
.login-container {
  background-color: var(--v-background-base);
}

.login-image {
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
    background-image: url('https://picsum.photos/1200/1600?random=10');
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

.login-form-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 2rem;
}
</style>
