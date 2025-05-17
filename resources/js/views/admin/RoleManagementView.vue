<!-- resources/js/views/admin/RoleManagementView.vue -->
<template>
  <div class="role-management">
    <v-container fluid>
      <!-- Page Header -->
      <v-row class="mb-4">
        <v-col cols="12" sm="6">
          <h1 class="text-h4">Role Management</h1>
          <p class="text-subtitle-1 text-medium-emphasis">Manage system roles and their permissions</p>
        </v-col>
        <v-col cols="12" sm="6" class="d-flex justify-end align-center">
          <v-btn
            color="primary"
            prepend-icon="mdi-plus"
            @click="openCreateRoleDialog"
            v-gsap-hover="{ scale: 1.05 }"
          >
            Add Role
          </v-btn>
        </v-col>
      </v-row>

      <!-- Filter and Search Section -->
      <v-card class="mb-4">
        <v-card-title>Search</v-card-title>
        <v-card-text>
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="filters.search"
                label="Search roles"
                prepend-inner-icon="mdi-magnify"
                clearable
                hide-details
                variant="outlined"
                density="compact"
                @update:model-value="debouncedSearch"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Roles Data Table -->
      <v-card>
        <v-card-title>Roles</v-card-title>
        <v-card-text>
          <v-data-table-server
            v-model:items-per-page="pagination.itemsPerPage"
            v-model:page="pagination.page"
            :headers="headers"
            :items="formattedRoles"
            :items-length="pagination.totalItems"
            :loading="isLoading"
            item-value="id"
            class="elevation-1 mt-2"
            @update:options="handleTableUpdate"
          >
            <!-- Role name with system role badge -->
            <template v-slot:item.name="{ item }">
              <div class="d-flex align-center">
                <div>
                  <div class="font-weight-medium">{{ item.display_name }}</div>
                  <div class="text-caption text-medium-emphasis">{{ item.name }}</div>
                </div>
                <v-chip
                  v-if="item.isSystemRole"
                  size="x-small"
                  color="primary"
                  class="ms-2"
                >System</v-chip>
              </div>
            </template>

            <!-- Permission count -->
            <template v-slot:item.permissionCount="{ item }">
              <v-chip
                :color="item.permissionCount > 0 ? 'success' : 'warning'"
                size="small"
                text-color="white"
              >
                {{ item.permissionCount }}
              </v-chip>
            </template>

            <!-- Actions column -->
            <template v-slot:item.actions="{ item }">
              <div class="d-flex">
                <v-tooltip text="Edit Role">
                  <template v-slot:activator="{ props }">
                    <v-icon
                      class="me-2"
                      icon="mdi-pencil"
                      color="primary"
                      size="small"
                      v-bind="props"
                      @click="editRole(item)"
                      v-gsap-hover="{ scale: 1.2 }"
                    ></v-icon>
                  </template>
                </v-tooltip>

                <v-tooltip text="Manage Permissions">
                  <template v-slot:activator="{ props }">
                    <v-icon
                      class="me-2"
                      icon="mdi-shield-account"
                      color="info"
                      size="small"
                      v-bind="props"
                      @click="openPermissionsDialog(item)"
                      v-gsap-hover="{ scale: 1.2 }"
                    ></v-icon>
                  </template>
                </v-tooltip>

                <v-tooltip
                  v-if="!item.isSystemRole"
                  text="Delete Role"
                >
                  <template v-slot:activator="{ props }">
                    <v-icon
                      icon="mdi-delete"
                      color="error"
                      size="small"
                      v-bind="props"
                      @click="confirmDelete(item)"
                      v-gsap-hover="{ scale: 1.2 }"
                    ></v-icon>
                  </template>
                </v-tooltip>
              </div>
            </template>

            <!-- No data display -->
            <template v-slot:no-data>
              <div class="text-center py-4">
                <v-icon size="large" color="primary" class="mb-2">mdi-shield-alert</v-icon>
                <p class="mb-0">No roles found. Try clearing filters or adding a new role.</p>
              </div>
            </template>
          </v-data-table-server>
        </v-card-text>
      </v-card>

      <!-- Role Form Dialog -->
      <v-dialog
        v-model="roleDialogOpen"
        max-width="600"
      >
        <v-card>
          <v-card-title>
            {{ isEditMode ? 'Edit Role' : 'Create Role' }}
          </v-card-title>
          <v-card-text>
            <v-form ref="roleForm" @submit.prevent="saveRole">
              <v-text-field
                v-model="roleForm.name"
                label="Role Name"
                :rules="[v => !!v || 'Name is required', v => !isEditMode || /^[a-zA-Z0-9_-]+$/.test(v) || 'Name should contain only letters, numbers, underscores or hyphens']"
                hint="System identifier (e.g. admin, content-manager)"
                persistent-hint
                :disabled="isEditMode && selectedRole.isSystemRole"
              ></v-text-field>

              <v-text-field
                v-model="roleForm.display_name"
                label="Display Name"
                :rules="[v => !!v || 'Display name is required']"
                class="mt-4"
                hint="User-friendly name (e.g. Administrator, Content Manager)"
                persistent-hint
              ></v-text-field>

              <v-textarea
                v-model="roleForm.description"
                label="Description"
                class="mt-4"
                rows="3"
                hint="Brief description of this role's purpose"
                persistent-hint
              ></v-textarea>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="grey-darken-1"
              variant="text"
              @click="roleDialogOpen = false"
            >
              Cancel
            </v-btn>
            <v-btn
              color="primary"
              @click="saveRole"
              :loading="isLoading"
            >
              Save
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Permissions Dialog -->
      <v-dialog
        v-model="permissionsDialogOpen"
        max-width="900"
      >
        <v-card>
          <v-card-title class="d-flex align-center">
            <span>Manage Permissions for {{ selectedRole?.display_name || 'Role' }}</span>
            <v-spacer></v-spacer>
            <v-switch
              v-model="selectAllPermissions"
              label="Select All"
              density="compact"
              hide-details
              @change="toggleAllPermissions"
            ></v-switch>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="permissions-container">
            <v-progress-circular
              v-if="isLoading"
              indeterminate
              color="primary"
              class="mt-4 mx-auto d-block"
            ></v-progress-circular>

            <template v-else>
              <v-expansion-panels v-model="openPanels" multiple variant="accordion">
                <v-expansion-panel
                  v-for="(permissions, module) in groupedPermissions"
                  :key="module"
                  :title="formatModuleName(module)"
                >
                  <template v-slot:text>
                    <v-row dense>
                      <v-col
                        v-for="permission in permissions"
                        :key="permission.id"
                        cols="12"
                        sm="6"
                        md="4"
                      >
                        <v-checkbox
                          v-model="selectedPermissions"
                          :label="permission.display_name"
                          :hint="permission.description"
                          persistent-hint
                          :value="permission.id"
                          density="comfortable"
                        ></v-checkbox>
                      </v-col>
                    </v-row>
                  </template>
                </v-expansion-panel>
              </v-expansion-panels>
            </template>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="grey-darken-1"
              variant="text"
              @click="permissionsDialogOpen = false"
            >
              Cancel
            </v-btn>
            <v-btn
              color="primary"
              @click="savePermissions"
              :loading="isLoading"
              :disabled="!selectedRole"
            >
              Save Permissions
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Delete Confirmation Dialog -->
      <v-dialog
        v-model="deleteDialogOpen"
        max-width="500"
      >
        <v-card>
          <v-card-title class="headline">Confirm Delete</v-card-title>
          <v-card-text>
            Are you sure you want to delete the role "{{ roleToDelete?.display_name }}"?
            This action cannot be undone and may affect users assigned to this role.
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="primary"
              variant="text"
              @click="deleteDialogOpen = false"
            >Cancel</v-btn>
            <v-btn
              color="error"
              @click="deleteRole"
            >Delete</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'pinia';
import { useRoleManagementStore } from '@/stores/admin/roleManagementStore';
import { ref, computed, onMounted, watch } from 'vue';
import { debounce } from 'lodash';

export default {
  name: 'RoleManagementView',

  setup() {
    const roleManagementStore = useRoleManagementStore();
    const {
      fetchRoles,
      fetchPermissions,
      createRole: storeCreateRole,
      updateRole: storeUpdateRole,
      deleteRole: storeDeleteRole,
      assignPermissions: storeAssignPermissions,
      updateFilters: storeUpdateFilters,
      updatePagination: storeUpdatePagination
    } = roleManagementStore;

    // Local state
    const roleDialogOpen = ref(false);
    const permissionsDialogOpen = ref(false);
    const deleteDialogOpen = ref(false);
    const roleToDelete = ref(null);
    const isEditMode = ref(false);
    const selectedRole = ref(null);
    const roleForm = ref({
      name: '',
      display_name: '',
      description: ''
    });
    const selectedPermissions = ref([]);
    const selectAllPermissions = ref(false);
    const openPanels = ref([]);
    const roleForm = ref(null);

    // Table headers
    const headers = [
      { title: 'Role', key: 'name', align: 'start', sortable: true },
      { title: 'Description', key: 'description', align: 'start', sortable: false },
      { title: 'Permissions', key: 'permissionCount', align: 'center', sortable: false, width: '120px' },
      { title: 'Actions', key: 'actions', align: 'center', sortable: false, width: '150px' }
    ];

    // Create a debounced search function
    const debouncedSearch = debounce(() => {
      storeUpdateFilters({ search: roleManagementStore.filters.search });
    }, 500);

    const handleTableUpdate = (options) => {
      storeUpdatePagination({
        page: options.page,
        itemsPerPage: options.itemsPerPage,
        sortBy: options.sortBy[0]?.key || 'name',
        sortDesc: options.sortBy[0]?.order === 'desc'
      });
    };

    // Format module name for display
    const formatModuleName = (module) => {
      return module.charAt(0).toUpperCase() + module.slice(1);
    };

    // Toggle all permissions
    const toggleAllPermissions = () => {
      if (selectAllPermissions.value) {
        selectedPermissions.value = roleManagementStore.permissions.map(p => p.id);
      } else {
        selectedPermissions.value = [];
      }
    };

    // Open the role creation dialog
    const openCreateRoleDialog = () => {
      roleForm.value = {
        name: '',
        display_name: '',
        description: ''
      };
      isEditMode.value = false;
      roleDialogOpen.value = true;
    };

    // Open the role edit dialog
    const editRole = (role) => {
      roleForm.value = {
        name: role.name,
        display_name: role.display_name,
        description: role.description
      };
      selectedRole.value = role;
      isEditMode.value = true;
      roleDialogOpen.value = true;
    };

    // Open permissions dialog
    const openPermissionsDialog = async (role) => {
      selectedRole.value = role;

      // Load all permissions if not already loaded
      if (roleManagementStore.permissions.length === 0) {
        await fetchPermissions();
      }

      // Set selected permissions based on the role
      selectedPermissions.value = role.permissions ? role.permissions.map(p => p.id) : [];

      // Check if all permissions are selected
      selectAllPermissions.value = selectedPermissions.value.length === roleManagementStore.permissions.length;

      // Open all panels by default
      openPanels.value = [...Array(Object.keys(roleManagementStore.groupedPermissions).length).keys()];

      permissionsDialogOpen.value = true;
    };

    // Save role (create or update)
    const saveRole = async () => {
      try {
        if (!roleFormRef.value.validate()) {
          return;
        }

        if (isEditMode.value) {
          await storeUpdateRole(selectedRole.value.id, roleForm.value);
        } else {
          await storeCreateRole(roleForm.value);
        }
        roleDialogOpen.value = false;
      } catch (error) {
        console.error('Error saving role:', error);
      }
    };

    // Save permissions
    const savePermissions = async () => {
      if (!selectedRole.value) return;

      try {
        await storeAssignPermissions(selectedRole.value.id, selectedPermissions.value);
        permissionsDialogOpen.value = false;
      } catch (error) {
        console.error('Error saving permissions:', error);
      }
    };

    // Confirm role deletion
    const confirmDelete = (role) => {
      roleToDelete.value = role;
      deleteDialogOpen.value = true;
    };

    // Delete role after confirmation
    const deleteRole = async () => {
      if (roleToDelete.value) {
        await storeDeleteRole(roleToDelete.value.id);
        deleteDialogOpen.value = false;
        roleToDelete.value = null;
      }
    };

    // Initial data fetch
    onMounted(async () => {
      await fetchRoles();
      // We'll load permissions on demand when opening the permissions dialog
    });

    return {
      headers,
      roleDialogOpen,
      permissionsDialogOpen,
      deleteDialogOpen,
      roleToDelete,
      roleForm,
      selectedRole,
      isEditMode,
      selectedPermissions,
      selectAllPermissions,
      openPanels,
      debouncedSearch,
      handleTableUpdate,
      formatModuleName,
      toggleAllPermissions,
      openCreateRoleDialog,
      editRole,
      openPermissionsDialog,
      saveRole,
      savePermissions,
      confirmDelete,
      deleteRole
    };
  },

  computed: {
    ...mapState(useRoleManagementStore, [
      'roles',
      'permissions',
      'isLoading',
      'pagination',
      'filters'
    ]),
    ...mapGetters(useRoleManagementStore, [
      'formattedRoles',
      'groupedPermissions'
    ])
  }
};
</script>

<style scoped>
.role-management {
  animation: fadeIn 0.5s ease-out;
}

.permissions-container {
  max-height: 60vh;
  overflow-y: auto;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
