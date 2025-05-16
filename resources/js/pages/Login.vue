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
            <v-form ref="form" @submit.prevent="login" v-gsap-fade-in="{ duration: 0.8, delay: 0.5 }">
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
                <a href="#" class="text-primary text-decoration-none">Forgot Password?</a>
              </div>

              <v-btn
                type="submit"
                color="primary"
                block
                size="large"
                :loading="loading"
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

    <!-- Terms & Conditions Dialog (will be implemented in Phase 2) -->
    <v-dialog v-model="showTermsDialog" persistent max-width="700">
      <v-card>
        <v-card-title class="text-h5 bg-primary text-white">
          Terms and Conditions
        </v-card-title>
        <v-card-text class="pa-4 mt-4">
          <p>This is a placeholder for the Terms & Conditions content.</p>
          <p>The actual implementation will be done in Phase 2.</p>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" variant="text" @click="declineTerms">
            Decline
          </v-btn>
          <v-btn color="primary" variant="elevated" @click="acceptTerms">
            Accept
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
export default {
  name: 'LoginPage',
  data() {
    return {
      email: '',
      password: '',
      rememberMe: false,
      loading: false,
      loginError: '',
      showTermsDialog: false
    };
  },
  methods: {
    login() {
      this.loading = true;
      this.loginError = '';

      // Login logic will be implemented in future phases
      setTimeout(() => {
        this.loading = false;

        // Simulate successful login for demo purposes
        if (this.email && this.password) {
          // Check if this would be a first-time login (would be handled by API in Phase 2)
          const isFirstLogin = this.email.includes('new');

          if (isFirstLogin) {
            // For demo - simulate redirecting to change password page
            alert('First-time login detected. Redirecting to change password page...');
          } else {
            // Simulating showing terms and conditions for demo purposes
            // In real implementation, this would be shown only when T&C has been updated
            this.showTermsDialog = true;

            // In production, we would navigate to home on successful login after T&C is accepted
            // this.$router.push({ name: 'home' });
          }
        } else {
          // Show error message
          this.loginError = 'Invalid email or password. Please try again.';
        }
        // Comment out for demo purposes so dialog is shown
        // this.$router.push({ name: 'home' });
      }, 1000);
    },

    acceptTerms() {
      this.showTermsDialog = false;
      // Navigate to home page after accepting terms
      this.$router.push({ name: 'home' });
    },

    declineTerms() {
      this.showTermsDialog = false;
      alert('Terms & Conditions must be accepted to use the application. You will be logged out.');
      // In real implementation, this would log the user out
      this.email = '';
      this.password = '';
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
