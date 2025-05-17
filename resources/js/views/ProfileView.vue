<!-- resources/js/views/ProfileView.vue -->
<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4 text-sm-h4 text-md-h4 mb-4 mb-sm-6">My Profile</h1>
      </v-col>
    </v-row>

    <v-row>
      <!-- Profile information card - full width on mobile, half width on medium screens and up -->
      <v-col cols="12" md="6">
        <v-card class="mb-4">
          <v-card-title class="primary lighten-1 white--text d-flex align-center">
            <v-icon left color="white" class="me-2">mdi-account-edit</v-icon>
            <span>Profile Information</span>
          </v-card-title>

          <v-card-text class="pa-3 pa-sm-4">
            <v-form ref="profileForm" v-model="isProfileFormValid" @submit.prevent="updateProfile">
              <v-row>
                <v-col cols="12">
                  <v-text-field
                    v-model="profileData.name"
                    label="Name"
                    :rules="nameRules"
                    required
                    variant="outlined"
                    density="comfortable"
                  ></v-text-field>
                </v-col>

                <v-col cols="12">
                  <v-text-field
                    v-model="profileData.email"
                    label="Email"
                    :rules="emailRules"
                    disabled
                    variant="outlined"
                    density="comfortable"
                  ></v-text-field>
                </v-col>

                <v-col cols="12">
                  <v-text-field
                    v-model="profileData.phone"
                    label="Phone Number"
                    variant="outlined"
                    density="comfortable"
                  ></v-text-field>
                </v-col>
              </v-row>

              <div class="d-flex justify-end">
                <v-btn
                  color="primary"
                  type="submit"
                  :loading="isUpdatingProfile"
                  :disabled="!isProfileFormValid || isUpdatingProfile"
                  size="large"
                  class="px-6 py-2"
                >
                  Save Changes
                </v-btn>
              </div>
            </v-form>
          </v-card-text>
        </v-card>

        <!-- Change Password Card - full width on mobile, half width on medium screens and up -->
        <v-card>
          <v-card-title class="primary lighten-1 white--text d-flex align-center">
            <v-icon left color="white" class="me-2">mdi-lock</v-icon>
            <span>Change Password</span>
          </v-card-title>

          <v-card-text class="pa-3 pa-sm-4">
            <v-form ref="passwordForm" v-model="isPasswordFormValid" @submit.prevent="changePassword">
              <v-row>
                <v-col cols="12">
                  <v-text-field
                    v-model="passwordData.current_password"
                    label="Current Password"
                    :rules="currentPasswordRules"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    :append-inner-icon="showCurrentPassword ? 'mdi-eye' : 'mdi-eye-off'"
                    required
                    variant="outlined"
                    density="comfortable"
                    @click:append-inner="showCurrentPassword = !showCurrentPassword"
                  ></v-text-field>
                </v-col>

                <v-col cols="12">
                  <v-text-field
                    v-model="passwordData.new_password"
                    label="New Password"
                    :rules="passwordRules"
                    :type="showNewPassword ? 'text' : 'password'"
                    :append-inner-icon="showNewPassword ? 'mdi-eye' : 'mdi-eye-off'"
                    required
                    variant="outlined"
                    density="comfortable"
                    @click:append-inner="showNewPassword = !showNewPassword"
                  ></v-text-field>
                </v-col>

                <v-col cols="12">
                  <v-text-field
                    v-model="passwordData.new_password_confirmation"
                    label="Confirm New Password"
                    :rules="[...passwordRules, passwordMatchRule]"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    :append-inner-icon="showConfirmPassword ? 'mdi-eye' : 'mdi-eye-off'"
                    required
                    variant="outlined"
                    density="comfortable"
                    @click:append-inner="showConfirmPassword = !showConfirmPassword"
                  ></v-text-field>
                </v-col>
              </v-row>

              <div class="d-flex justify-end">
                <v-btn
                  color="primary"
                  type="submit"
                  :loading="isChangingPassword"
                  :disabled="!isPasswordFormValid || isChangingPassword"
                  size="large"
                  class="px-6 py-2"
                >
                  Update Password
                </v-btn>
              </div>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card class="mb-4">
          <v-card-title class="primary lighten-1 white--text d-flex align-center">
            <v-icon left color="white" class="me-2">mdi-account-circle</v-icon>
            <span>Profile Picture</span>
          </v-card-title>

          <v-card-text class="pa-3 pa-sm-4">
            <avatar-upload />
          </v-card-text>
        </v-card>

        <!-- Account Settings Card -->
        <v-card>
          <v-card-title class="primary lighten-1 white--text d-flex align-center">
            <v-icon left color="white" class="me-2">mdi-cog</v-icon>
            <span>Account Settings</span>
          </v-card-title>

          <v-card-text class="pa-3 pa-sm-4">
            <v-list>
              <v-list-item>
                <v-switch
                  v-model="accountSettings.emailNotifications"
                  color="primary"
                  density="comfortable"
                  hide-details
                >
                  <template v-slot:label>
                    <div>
                      <div class="text-subtitle-1">Email Notifications</div>
                      <div class="text-caption text-grey">Receive email notifications for important updates</div>
                    </div>
                  </template>
                </v-switch>
              </v-list-item>

              <v-divider></v-divider>

              <v-list-item>
                <v-switch
                  v-model="accountSettings.twoFactorAuth"
                  color="primary"
                  density="comfortable"
                  hide-details
                >
                  <template v-slot:label>
                    <div>
                      <div class="text-subtitle-1">Two Factor Authentication</div>
                      <div class="text-caption text-grey">Add an extra layer of security to your account</div>
                    </div>
                  </template>
                </v-switch>
              </v-list-item>
            </v-list>

            <div class="d-flex justify-end mt-3">
              <v-btn
                color="primary"
                @click="saveAccountSettings"
                :loading="isSavingSettings"
                size="large"
                class="px-6 py-2"
              >
                Save Settings
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-snackbar v-model="showSnackbar" :color="snackbarColor" timeout="3000" class="text-center">
      {{ snackbarText }}
      <template v-slot:actions>
        <v-btn variant="text" @click="showSnackbar = false">Close</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/authStore';
import AvatarUpload from '../components/AvatarUpload.vue';
import axios from 'axios';

export default {
  name: 'ProfileView',
  components: {
    AvatarUpload
  },
  setup() {
    const authStore = useAuthStore();
    const user = computed(() => authStore.user);

    // Profile form data and validation
    const profileForm = ref(null);
    const isProfileFormValid = ref(false);
    const isUpdatingProfile = ref(false);
    const profileData = ref({
      name: '',
      email: '',
      phone: ''
    });

    // Password form data and validation
    const passwordForm = ref(null);
    const isPasswordFormValid = ref(false);
    const isChangingPassword = ref(false);
    const passwordData = ref({
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    });

    // Password visibility toggles
    const showCurrentPassword = ref(false);
    const showNewPassword = ref(false);
    const showConfirmPassword = ref(false);

    // Account settings
    const accountSettings = ref({
      emailNotifications: true,
      twoFactorAuth: false
    });
    const isSavingSettings = ref(false);

    // Snackbar
    const showSnackbar = ref(false);
    const snackbarText = ref('');
    const snackbarColor = ref('success');

    // Validation rules
    const nameRules = [
      v => !!v || 'Name is required',
      v => (v && v.length >= 3) || 'Name must be at least 3 characters'
    ];

    const emailRules = [
      v => !!v || 'Email is required',
      v => /.+@.+\..+/.test(v) || 'Email must be valid'
    ];

    const currentPasswordRules = [
      v => !!v || 'Current password is required'
    ];

    const passwordRules = [
      v => !!v || 'Password is required',
      v => (v && v.length >= 8) || 'Password must be at least 8 characters',
      v => /[A-Z]/.test(v) || 'Password must contain at least one uppercase letter',
      v => /[a-z]/.test(v) || 'Password must contain at least one lowercase letter',
      v => /[0-9]/.test(v) || 'Password must contain at least one number'
    ];

    const passwordMatchRule = v => v === passwordData.value.new_password || 'Password confirmation must match new password';

    // Initialize form data
    onMounted(() => {
      if (user.value) {
        profileData.value = {
          name: user.value.name || '',
          email: user.value.email || '',
          phone: user.value.phone || ''
        };
      }
    });

    // Update profile information
    const updateProfile = async () => {
      if (!isProfileFormValid.value) return;

      isUpdatingProfile.value = true;

      try {
        const response = await axios.put('/api/user/profile', profileData.value);

        // Update auth store user data
        if (authStore.user) {
          authStore.user = response.data.user;
        }

        // Show success message
        snackbarText.value = 'Profile updated successfully';
        snackbarColor.value = 'success';
        showSnackbar.value = true;
      } catch (error) {
        console.error('Error updating profile:', error);

        // Show error message
        snackbarText.value = error.response?.data?.message || 'Failed to update profile';
        snackbarColor.value = 'error';
        showSnackbar.value = true;
      } finally {
        isUpdatingProfile.value = false;
      }
    };

    // Change password
    // Save account settings
    const saveAccountSettings = async () => {
      isSavingSettings.value = true;

      try {
        // Here we would call the API to save the settings
        // For now just simulate a delay
        await new Promise(resolve => setTimeout(resolve, 500));

        // Show success message
        snackbarText.value = 'Account settings saved successfully';
        snackbarColor.value = 'success';
        showSnackbar.value = true;
      } catch (error) {
        console.error('Error saving account settings:', error);
        snackbarText.value = 'Failed to save account settings';
        snackbarColor.value = 'error';
        showSnackbar.value = true;
      } finally {
        isSavingSettings.value = false;
      }
    };

    const changePassword = async () => {
      if (!isPasswordFormValid.value) return;

      isChangingPassword.value = true;

      try {
        await authStore.changePassword(passwordData.value);

        // Reset form
        passwordData.value = {
          current_password: '',
          new_password: '',
          new_password_confirmation: ''
        };
        passwordForm.value.reset();

        // Show success message
        snackbarText.value = 'Password changed successfully';
        snackbarColor.value = 'success';
        showSnackbar.value = true;
      } catch (error) {
        console.error('Error changing password:', error);

        // Show error message
        snackbarText.value = error.message || 'Failed to change password';
        snackbarColor.value = 'error';
        showSnackbar.value = true;
      } finally {
        isChangingPassword.value = false;
      }
    };

    return {
      user,
      profileForm,
      isProfileFormValid,
      isUpdatingProfile,
      profileData,
      passwordForm,
      isPasswordFormValid,
      isChangingPassword,
      passwordData,
      showCurrentPassword,
      showNewPassword,
      showConfirmPassword,
      showSnackbar,
      snackbarText,
      snackbarColor,
      nameRules,
      emailRules,
      currentPasswordRules,
      passwordRules,
      accountSettings,
      isSavingSettings,
      passwordMatchRule,
      updateProfile,
      changePassword,
      saveAccountSettings
    };
  }
};
</script>
