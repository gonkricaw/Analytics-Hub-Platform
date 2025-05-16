<!-- resources/js/components/AvatarUpload.vue -->
<template>
  <div class="avatar-upload">
    <v-sheet class="mb-4 pa-4" rounded elevation="1">
      <div class="d-flex align-center justify-center flex-column">
        <v-avatar size="150" class="mb-3" style="border: 2px solid #8C3EFF">
          <v-img v-if="avatarUrl" :src="avatarUrl" :alt="user?.name || 'User avatar'"></v-img>
          <v-icon v-else size="80" color="grey lighten-1">mdi-account</v-icon>
        </v-avatar>

        <v-file-input
          v-model="avatarFile"
          accept="image/png, image/jpeg, image/jpg, image/gif"
          :loading="isUploading"
          :disabled="isUploading"
          :error-messages="errorMessage"
          label="Change profile picture"
          variant="outlined"
          density="comfortable"
          prepend-icon="mdi-camera"
          show-size
          @update:model-value="handleFileChange"
        ></v-file-input>

        <div class="d-flex mt-3">
          <v-btn
            color="primary"
            :loading="isUploading"
            :disabled="!avatarFile || isUploading"
            @click="uploadAvatar"
          >
            Upload
          </v-btn>
          <v-btn
            variant="outlined"
            class="ml-2"
            :disabled="isUploading || !avatarUrl || avatarUrl.includes('default-avatar')"
            @click="removeAvatar"
          >
            Remove
          </v-btn>
        </div>
      </div>
    </v-sheet>

    <v-snackbar v-model="showSnackbar" :color="snackbarColor" timeout="3000">
      {{ snackbarText }}
      <template v-slot:actions>
        <v-btn variant="text" @click="showSnackbar = false">Close</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/authStore';
import axios from 'axios';

export default {
  name: 'AvatarUpload',
  props: {
    userId: {
      type: [Number, String],
      default: null
    }
  },
  setup(props) {
    const authStore = useAuthStore();
    const user = computed(() => authStore.user);

    const avatarUrl = ref('');
    const avatarFile = ref(null);
    const isUploading = ref(false);
    const errorMessage = ref('');

    const showSnackbar = ref(false);
    const snackbarText = ref('');
    const snackbarColor = ref('success');

    // Fetch the current avatar on component mount
    onMounted(async () => {
      try {
        const response = await axios.get('/api/user/avatar');
        avatarUrl.value = response.data.avatar_url;
      } catch (error) {
        console.error('Error fetching avatar:', error);
        avatarUrl.value = '/images/default-avatar.png';
      }
    });

    // Handle file validation when user selects a new file
    const handleFileChange = (file) => {
      if (!file) {
        errorMessage.value = '';
        return;
      }

      // Reset error
      errorMessage.value = '';

      // Validate file type
      const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
      if (!validTypes.includes(file.type)) {
        errorMessage.value = 'Please select a valid image file (JPEG, PNG, GIF)';
        avatarFile.value = null;
        return;
      }

      // Validate file size (2MB max)
      if (file.size > 2 * 1024 * 1024) {
        errorMessage.value = 'Image size must not exceed 2MB';
        avatarFile.value = null;
        return;
      }
    };

    // Upload the avatar
    const uploadAvatar = async () => {
      if (!avatarFile.value) return;

      isUploading.value = true;
      errorMessage.value = '';

      try {
        const formData = new FormData();
        formData.append('avatar', avatarFile.value);

        const response = await axios.post('/api/user/avatar', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        // Update the avatar URL with the new one
        avatarUrl.value = response.data.avatar_url;

        // Show success message
        snackbarText.value = 'Profile picture updated successfully';
        snackbarColor.value = 'success';
        showSnackbar.value = true;

        // Reset file input
        avatarFile.value = null;

        // Update auth store if needed
        if (authStore.user) {
          authStore.user.avatar = response.data.user.avatar;
        }

      } catch (error) {
        console.error('Error uploading avatar:', error);
        errorMessage.value = error.response?.data?.message || 'Failed to upload profile picture';

        // Show error message
        snackbarText.value = errorMessage.value;
        snackbarColor.value = 'error';
        showSnackbar.value = true;
      } finally {
        isUploading.value = false;
      }
    };

    // Remove avatar (revert to default)
    const removeAvatar = async () => {
      if (isUploading.value) return;

      isUploading.value = true;

      try {
        // Here we'd typically call an API endpoint to remove the avatar
        // For now, we'll just implement a placeholder
        await axios.post('/api/user/avatar/remove');

        // Update to default avatar
        avatarUrl.value = '/images/default-avatar.png';

        // Show success message
        snackbarText.value = 'Profile picture removed';
        snackbarColor.value = 'info';
        showSnackbar.value = true;

        // Update auth store if needed
        if (authStore.user) {
          authStore.user.avatar = null;
        }

      } catch (error) {
        console.error('Error removing avatar:', error);

        // Show error message
        snackbarText.value = error.response?.data?.message || 'Failed to remove profile picture';
        snackbarColor.value = 'error';
        showSnackbar.value = true;
      } finally {
        isUploading.value = false;
      }
    };

    return {
      user,
      avatarUrl,
      avatarFile,
      isUploading,
      errorMessage,
      showSnackbar,
      snackbarText,
      snackbarColor,
      handleFileChange,
      uploadAvatar,
      removeAvatar
    };
  }
};
</script>

<style scoped>
.avatar-upload {
  max-width: 450px;
  margin: 0 auto;
}
</style>
