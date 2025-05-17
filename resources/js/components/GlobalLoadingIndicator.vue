<!-- resources/js/components/GlobalLoadingIndicator.vue -->
<template>
  <div v-if="isLoading" class="loading-overlay" v-gsap-fade-in="{ duration: 0.3 }">
    <v-progress-circular
      indeterminate
      color="primary"
      size="64"
      width="4"
    ></v-progress-circular>
  </div>
</template>

<script>
import { mapState } from 'pinia';
import { useLayoutStore } from '@stores/layoutStore';

export default {
  name: 'GlobalLoadingIndicator',

  computed: {
    ...mapState(useLayoutStore, ['isLoading'])
  }
};
</script>

<style lang="scss" scoped>
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
</style>
