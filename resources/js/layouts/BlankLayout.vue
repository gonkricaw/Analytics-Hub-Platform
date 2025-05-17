<!-- resources/js/layouts/BlankLayout.vue -->
<template>
  <v-app>
    <!-- No app-bar, navigation, or footer for blank layout -->
    <v-main>
      <!-- Router view -->
      <router-view></router-view>
    </v-main>
  </v-app>
</template>

<script>
export default {
  name: 'BlankLayout'
};
</script>

<style lang="scss" scoped>
// Ensuring full height and width
:deep(html, body, .v-application, .v-application__wrap, .v-main, .v-main__wrap) {
  height: 100%;
  width: 100%;
  overflow: hidden;

  display: flex;
  flex-direction: column;
}

// Remove any default padding that might affect the full-width display
:deep(.v-main__wrap) {
  padding: 0 !important;
}
</style>
