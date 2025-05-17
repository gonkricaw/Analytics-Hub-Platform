<!-- resources/js/views/admin/UserManagementView.vue -->
<template>
  <div class="user-management">
    <v-container fluid>
      <!-- Page Header -->
      <v-row class="mb-4">
        <v-col cols="12" sm="6">
          <h1 class="text-h4">User Management</h1>
          <p class="text-subtitle-1 text-medium-emphasis">Manage system users, roles and permissions</p>
        </v-col>
        <v-col cols="12" sm="6" class="d-flex justify-end align-center">
          <v-btn
            color="primary"
            prepend-icon="mdi-email-plus"
            class="me-2"
            @click="openInvitationDialog"
            v-gsap-hover="{ scale: 1.05 }"
          >
            Invite User
          </v-btn>
          <v-btn
            color="primary"
            prepend-icon="mdi-plus"
            @click="openCreateUserDialog"
            v-gsap-hover="{ scale: 1.05 }"
          >
            Add User
          </v-btn>
        </v-col>
      </v-row>

      <!-- Filter and Search Section -->
      <v-card class="mb-4">
        <v-card-title>Filters & Search</v-card-title>
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="4">
              <v-text-field
                v-model="filters.search"
                label="Search"
                prepend-inner-icon="mdi-magnify"
                clearable
                hide-details
                variant="outlined"
                density="compact"
                @update:model-value="debouncedSearch"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="4">
              <v-select
                v-model="filters.role"
                label="Filter by Role"
                :items="roleOptions"
                item-title="title"
                item-value="value"
                clearable
                hide-details
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-shield-account"
                @update:model-value="applyFilters"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="4">
              <v-select
                v-model="filters.isActive"
                label="Status"
                :items="statusOptions"
                item-title="title"
                item-value="value"
                clearable
                hide-details
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-account-check"
                @update:model-value="applyFilters"
              ></v-select>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="px-4 py-2">
          <v-spacer></v-spacer>
          <v-btn
            variant="text"
            color="primary"
            @click="clearFilters"
          >
            Clear Filters
          </v-btn>
        </v-card-actions>
      </v-card>

      <!-- Users Data Table -->
      <v-card>
        <v-card-title>User Listing</v-card-title>
        <v-card-text>
          <v-data-table-server
            v-model:items-per-page="pagination.itemsPerPage"
            v-model:page="pagination.page"
            :headers="headers"
            :items="formattedUsers"
            :items-length="pagination.totalItems"
            :loading="isLoading"
            class="elevation-1 mt-2"
            item-value="id"
            @update:options="handleTableUpdate"
          >
            <!-- Avatar and name column -->
            <template v-slot:item.user="{ item }">
              <div class="d-flex align-center">
                <v-avatar size="40" class="me-3">
                  <v-img v-if="item.avatar" :src="item.avatar" alt="User Avatar"></v-img>
                  <v-icon v-else>mdi-account-circle</v-icon>
                </v-avatar>
                <div>
                  <div class="font-weight-medium">{{ item.name }}</div>
                  <div class="text-caption text-medium-emphasis">{{ item.email }}</div>
                </div>
              </div>
            </template>

            <!-- Status column -->
            <template v-slot:item.is_active="{ item }">
              <v-chip
                :color="item.is_active ? 'success' : 'error'"
                size="small"
                text-color="white"
              >
                {{ item.is_active ? 'Active' : 'Inactive' }}
              </v-chip>
            </template>

            <!-- Roles column -->
            <template v-slot:item.roleNames="{ item }">
              <div v-if="item.roles && item.roles.length">
                <v-chip
                  v-for="role in item.roles"
                  :key="role.id"
                  class="me-1 mb-1"
                  size="small"
                  color="primary"
                  variant="outlined"
                >
                  {{ role.display_name || role.name }}
                </v-chip>
              </div>
              <span v-else class="text-medium-emphasis text-caption">No roles assigned</span>
            </template>

            <!-- Actions column -->
            <template v-slot:item.actions="{ item }">
              <div class="d-flex">
                <v-icon
                  class="me-2"
                  icon="mdi-pencil"
                  color="primary"
                  size="small"
                  @click="editUser(item)"
                  v-gsap-hover="{ scale: 1.2 }"
                ></v-icon>
                <v-icon
                  class="me-2"
                  :icon="item.is_active ? 'mdi-account-off' : 'mdi-account-check'"
                  :color="item.is_active ? 'error' : 'success'"
                  size="small"
                  @click="toggleUserStatus(item)"
                  v-gsap-hover="{ scale: 1.2 }"
                ></v-icon>
                <v-icon
                  icon="mdi-delete"
                  color="error"
                  size="small"
                  @click="confirmDelete(item)"
                  v-gsap-hover="{ scale: 1.2 }"
                ></v-icon>
              </div>
            </template>

            <!-- No data display -->
            <template v-slot:no-data>
              <div class="text-center py-4">
                <v-icon size="large" color="primary" class="mb-2">mdi-account-off</v-icon>
                <p class="mb-0">No users found. Try clearing filters or adding a new user.</p>
              </div>
            </template>
          </v-data-table-server>
        </v-card-text>
      </v-card>

      <!-- User Form Dialog -->
      <UserFormDialog
        v-model="userDialogOpen"
        :user="selectedUser"
        :roles="roles"
        @save="saveUser"
        @close="userDialogOpen = false"
      />

      <!-- User Invitation Dialog -->
      <UserInvitationDialog
        v-model="invitationDialogOpen"
        :status="invitationStatus"
        :roles="roles"
        @send-invitation="inviteUser"
        @close="closeInvitationDialog"
      />

      <!-- Delete Confirmation Dialog -->
      <v-dialog
        v-model="deleteDialogOpen"
        max-width="500"
      >
        <v-card>
          <v-card-title class="headline">Confirm Delete</v-card-title>
          <v-card-text>
            Are you sure you want to delete {{ userToDelete?.name }}? This action cannot be undone.
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
              @click="deleteUser"
            >Delete</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'pinia';
import { useUserManagementStore } from '@stores/admin/userManagementStore';
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import UserFormDialog from '@/components/admin/UserFormDialog.vue';
import UserInvitationDialog from '@/components/admin/UserInvitationDialog.vue';
import { debounce } from 'lodash';

export default {
  name: 'UserManagementView',
  components: {
    UserFormDialog,
    UserInvitationDialog
  },
  setup() {
    const userManagementStore = useUserManagementStore();
    const {
      fetchUsers,
      fetchRoles,
      updateUser,
      createUser,
      deleteUser: storeDeleteUser,
      toggleUserStatus: storeToggleUserStatus,
      inviteUser: storeInviteUser,
      setInvitationDialog,
      updateFilters: storeUpdateFilters,
      clearFilters: storeClearFilters,
      updatePagination: storeUpdatePagination
    } = userManagementStore;

    const userDialogOpen = ref(false);
    const deleteDialogOpen = ref(false);
    const userToDelete = ref(null);
    const isEditMode = ref(false);
    const selectedUser = ref(null);

    const headers = [
      { title: 'User', key: 'user', align: 'start', sortable: false },
      { title: 'Status', key: 'is_active', align: 'center', sortable: false, width: '100px' },
      { title: 'Roles', key: 'roleNames', align: 'start', sortable: false },
      { title: 'Last Login', key: 'last_login_at', align: 'center', sortable: false, width: '180px' },
      { title: 'Created', key: 'created_at', align: 'center', sortable: false, width: '180px' },
      { title: 'Actions', key: 'actions', align: 'center', sortable: false, width: '120px' }
    ];

    // Create a debounced search function
    const debouncedSearch = debounce(() => {
      storeUpdateFilters({ search: userManagementStore.filters.search });
    }, 500);

    const applyFilters = () => {
      storeUpdateFilters({
        role: userManagementStore.filters.role,
        isActive: userManagementStore.filters.isActive
      });
    };

    const handleTableUpdate = (options) => {
      storeUpdatePagination({
        page: options.page,
        itemsPerPage: options.itemsPerPage
      });
    };

    // Open the user creation dialog
    const openCreateUserDialog = () => {
      selectedUser.value = null;
      isEditMode.value = false;
      userDialogOpen.value = true;
    };

    // Open the user edit dialog
    const editUser = (user) => {
      selectedUser.value = { ...user };
      isEditMode.value = true;
      userDialogOpen.value = true;
    };

    // Save user (create or update)
    const saveUser = async (userData) => {
      try {
        if (isEditMode.value) {
          await updateUser(userData.id, userData);
        } else {
          await createUser(userData);
        }
        userDialogOpen.value = false;
      } catch (error) {
        console.error('Error saving user:', error);
      }
    };

    // Confirm user deletion
    const confirmDelete = (user) => {
      userToDelete.value = user;
      deleteDialogOpen.value = true;
    };

    // Delete user after confirmation
    const deleteUser = async () => {
      if (userToDelete.value) {
        await storeDeleteUser(userToDelete.value.id);
        deleteDialogOpen.value = false;
        userToDelete.value = null;
      }
    };

    // Toggle user active status
    const toggleUserStatus = async (user) => {
      await storeToggleUserStatus(user.id, !user.is_active);
    };

    // Open invitation dialog
    const openInvitationDialog = () => {
      setInvitationDialog(true);
    };

    // Close invitation dialog
    const closeInvitationDialog = () => {
      setInvitationDialog(false);
    };

    // Invite user
    const inviteUser = async (invitationData) => {
      await storeInviteUser(invitationData);
    };

    // Initial data fetch
    onMounted(async () => {
      await fetchRoles();
      await fetchUsers();
    });

    return {
      headers,
      userDialogOpen,
      deleteDialogOpen,
      userToDelete,
      selectedUser,
      isEditMode,
      debouncedSearch,
      applyFilters,
      handleTableUpdate,
      openCreateUserDialog,
      editUser,
      saveUser,
      confirmDelete,
      deleteUser,
      toggleUserStatus,
      openInvitationDialog,
      closeInvitationDialog,
      inviteUser
    };
  },
  computed: {
    ...mapState(useUserManagementStore, [
      'users',
      'roles',
      'isLoading',
      'pagination',
      'filters',
      'invitationDialogOpen',
      'invitationStatus'
    ]),
    ...mapGetters(useUserManagementStore, [
      'formattedUsers',
      'roleOptions',
      'statusOptions'
    ])
  },
  methods: {
    ...mapActions(useUserManagementStore, [
      'clearFilters',
      'updateFilters'
    ])
  }
};
</script>

<style scoped>
.user-management {
  animation: fadeIn 0.5s ease-out;
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
