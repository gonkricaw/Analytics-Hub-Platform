<!-- resources/js/views/admin/SystemConfigurationView.vue -->
<template>
  <div>
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center">
        <div>
          <h1 class="text-h4">System Configuration Management</h1>
          <p class="text-subtitle-1">Manage application system configurations</p>
        </div>
        <v-btn color="primary" @click="openCreateDialog" prepend-icon="mdi-plus">
          Add Configuration
        </v-btn>
      </v-card-title>

      <!-- Filters section -->
      <v-card-text>
        <v-row>
          <v-col cols="12" sm="4">
            <v-text-field
              v-model="filters.search"
              label="Search by key"
              prepend-inner-icon="mdi-magnify"
              hide-details
              dense
              outlined
              @keyup.enter="applyFilters"
            ></v-text-field>
          </v-col>
          <v-col cols="12" sm="4">
            <v-select
              v-model="filters.group"
              :items="groupOptions"
              label="Filter by group"
              clearable
              hide-details
              dense
              outlined
              @update:modelValue="applyFilters"
            ></v-select>
          </v-col>
          <v-col cols="12" sm="4">
            <v-select
              v-model="filters.isPublic"
              :items="visibilityOptions"
              label="Filter by visibility"
              clearable
              hide-details
              dense
              outlined
              @update:modelValue="applyFilters"
            ></v-select>
          </v-col>
        </v-row>
        <div class="d-flex justify-end mt-2">
          <v-btn color="secondary" text @click="resetFilters" variant="outlined" class="mr-2">
            Reset Filters
          </v-btn>
          <v-btn color="primary" @click="applyFilters" variant="outlined">
            Apply Filters
          </v-btn>
        </div>
      </v-card-text>

      <!-- Loading overlay -->
      <v-overlay v-if="isLoading" contained class="align-center justify-center">
        <v-progress-circular indeterminate size="64" color="primary"></v-progress-circular>
      </v-overlay>

      <!-- Data table -->
      <v-data-table-server
        v-model:items-per-page="pagination.itemsPerPage"
        v-model:page="pagination.page"
        :headers="headers"
        :items="configurations"
        :loading="isLoading"
        :items-length="pagination.totalItems"
        :items-per-page-options="[5, 10, 20, 50]"
        class="elevation-1"
        @update:options="handleTableOptions"
      >
        <!-- Custom headers -->
        <template v-slot:header.actions>
          <span class="text-subtitle-1 font-weight-black">Actions</span>
        </template>

        <!-- Type column -->
        <template v-slot:item.type="{ item }">
          <v-chip :color="getTypeColor(item.type)" size="small" class="text-uppercase">
            {{ item.type }}
          </v-chip>
        </template>

        <!-- Value column -->
        <template v-slot:item.value="{ item }">
          <div class="text-truncate" style="max-width: 300px;" :title="item.value">
            {{ formatValue(item) }}
          </div>
        </template>

        <!-- Public status -->
        <template v-slot:item.is_public="{ item }">
          <v-icon :color="item.is_public ? 'success' : 'grey'">
            {{ item.is_public ? 'mdi-eye' : 'mdi-eye-off' }}
          </v-icon>
          <span class="ml-1">{{ item.is_public ? 'Public' : 'Private' }}</span>
        </template>

        <!-- Actions column -->
        <template v-slot:item.actions="{ item }">
          <div class="d-flex justify-start">
            <v-btn icon size="small" color="primary" @click="openEditDialog(item)" class="mr-1">
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon size="small" color="error" @click="confirmDelete(item)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </div>
        </template>

        <!-- Empty state -->
        <template v-slot:no-data>
          <div class="d-flex flex-column align-center py-8">
            <v-icon size="large" color="grey">mdi-database-off</v-icon>
            <p class="text-subtitle-1 mt-2 text-grey">No configurations found</p>
          </div>
        </template>
      </v-data-table-server>
    </v-card>

    <!-- Create/Edit Configuration Dialog -->
    <v-dialog v-model="editDialogOpen" max-width="600px">
      <v-card>
        <v-card-title>
          <span class="text-h5">{{ isEditMode ? 'Edit Configuration' : 'Add New Configuration' }}</span>
        </v-card-title>

        <v-card-text>
          <v-form ref="configForm" v-model="formValid">
            <v-container>
              <v-row>
                <!-- Key field -->
                <v-col cols="12">
                  <v-text-field
                    v-model="formData.key"
                    label="Key"
                    :disabled="isEditMode"
                    :rules="[v => !!v || 'Key is required']"
                    required
                  ></v-text-field>
                </v-col>

                <!-- Value field -->
                <v-col cols="12">
                  <v-textarea
                    v-model="formData.value"
                    label="Value"
                    :rules="[v => !!v || 'Value is required']"
                    required
                    auto-grow
                    rows="3"
                  ></v-textarea>
                </v-col>

                <!-- Type selector -->
                <v-col cols="12" sm="6">
                  <v-select
                    v-model="formData.type"
                    label="Type"
                    :items="typeOptions"
                    :rules="[v => !!v || 'Type is required']"
                    required
                  ></v-select>
                </v-col>

                <!-- Group field -->
                <v-col cols="12" sm="6">
                  <v-autocomplete
                    v-model="formData.group"
                    label="Group"
                    :items="existingGroups"
                    :rules="[v => !!v || 'Group is required']"
                    required
                    clearable
                    autocomplete
                    chips
                    small-chips
                  ></v-autocomplete>
                </v-col>

                <!-- Public visibility -->
                <v-col cols="12">
                  <v-switch
                    v-model="formData.is_public"
                    label="Publicly accessible"
                    hint="If enabled, this configuration will be accessible without authentication"
                    persistent-hint
                    color="primary"
                  ></v-switch>
                </v-col>

                <!-- Display name (optional) -->
                <v-col cols="12">
                  <v-text-field
                    v-model="formData.display_name"
                    label="Display Name (optional)"
                  ></v-text-field>
                </v-col>

                <!-- Description (optional) -->
                <v-col cols="12">
                  <v-textarea
                    v-model="formData.description"
                    label="Description (optional)"
                    auto-grow
                    rows="2"
                  ></v-textarea>
                </v-col>
              </v-row>
            </v-container>
          </v-form>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey-darken-1" text @click="closeDialog">
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            :loading="isSubmitting"
            :disabled="!formValid"
            @click="submitForm"
          >
            {{ isEditMode ? 'Update' : 'Create' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialogOpen" max-width="400">
      <v-card>
        <v-card-title class="text-h5">
          Confirm Delete
        </v-card-title>
        <v-card-text>
          Are you sure you want to delete the configuration with key
          <code>{{ configToDelete?.key }}</code>? This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey-darken-1" text @click="deleteDialogOpen = false">
            Cancel
          </v-btn>
          <v-btn color="error" :loading="isSubmitting" @click="deleteConfiguration">
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import { useSystemConfigurationStore } from '@/stores/admin/systemConfigurationStore';
import { storeToRefs } from 'pinia';

export default defineComponent({
  name: 'SystemConfigurationView',

  setup() {
    const systemConfigStore = useSystemConfigurationStore();
    const {
      configurations,
      configurationGroups,
      pagination,
      filters,
      isLoading
    } = storeToRefs(systemConfigStore);

    return {
      systemConfigStore,
      configurations,
      configurationGroups,
      pagination,
      filters,
      isLoading
    };
  },

  data() {
    return {
      headers: [
        { title: 'Key', key: 'key', align: 'start', sortable: true },
        { title: 'Value', key: 'value', align: 'start', sortable: false },
        { title: 'Type', key: 'type', align: 'start', sortable: true },
        { title: 'Group', key: 'group', align: 'start', sortable: true },
        { title: 'Public', key: 'is_public', align: 'center', sortable: true },
        { title: 'Actions', key: 'actions', align: 'center', sortable: false }
      ],
      editDialogOpen: false,
      deleteDialogOpen: false,
      isEditMode: false,
      isSubmitting: false,
      formValid: false,
      formData: {
        key: '',
        value: '',
        type: 'string',
        group: '',
        is_public: false,
        display_name: '',
        description: ''
      },
      configToDelete: null,
      visibilityOptions: [
        { title: 'Public', value: true },
        { title: 'Private', value: false }
      ],
      typeOptions: [
        { title: 'String', value: 'string' },
        { title: 'Integer', value: 'integer' },
        { title: 'Boolean', value: 'boolean' },
        { title: 'JSON', value: 'json' },
        { title: 'Array', value: 'array' }
      ]
    };
  },

  computed: {
    groupOptions() {
      return this.configurationGroups.map(group => ({
        title: this.formatGroupTitle(group),
        value: group
      }));
    },

    existingGroups() {
      return this.configurationGroups;
    }
  },

  mounted() {
    this.fetchData();
  },

  methods: {
    async fetchData() {
      await this.systemConfigStore.fetchConfigurationGroups();
      await this.systemConfigStore.fetchConfigurations();
    },

    formatGroupTitle(group) {
      if (!group) return '';
      return group.charAt(0).toUpperCase() + group.slice(1).replace(/-/g, ' ');
    },

    formatValue(item) {
      if (item.type === 'boolean') {
        return item.typed_value ? 'True' : 'False';
      } else if (item.type === 'json' || item.type === 'array') {
        return JSON.stringify(item.typed_value).substring(0, 50) + (JSON.stringify(item.typed_value).length > 50 ? '...' : '');
      }
      return String(item.value).substring(0, 50) + (item.value.length > 50 ? '...' : '');
    },

    getTypeColor(type) {
      const colors = {
        string: 'blue',
        integer: 'green',
        boolean: 'purple',
        json: 'orange',
        array: 'pink'
      };
      return colors[type] || 'grey';
    },

    handleTableOptions({ page, itemsPerPage, sortBy }) {
      this.pagination.page = page;
      this.pagination.itemsPerPage = itemsPerPage;

      if (sortBy && sortBy.length > 0) {
        this.pagination.sortBy = sortBy[0].key;
        this.pagination.sortDesc = sortBy[0].order === 'desc';
      }

      this.systemConfigStore.setPagination(this.pagination);
    },

    applyFilters() {
      this.systemConfigStore.setFilters(this.filters);
    },

    resetFilters() {
      this.systemConfigStore.resetFilters();
    },

    openCreateDialog() {
      this.isEditMode = false;
      this.formData = {
        key: '',
        value: '',
        type: 'string',
        group: '',
        is_public: false,
        display_name: '',
        description: ''
      };
      this.editDialogOpen = true;

      // Reset form validation
      if (this.$refs.configForm) {
        this.$refs.configForm.resetValidation();
      }
    },

    openEditDialog(config) {
      this.isEditMode = true;
      this.formData = {
        key: config.key,
        value: config.value,
        type: config.type,
        group: config.group,
        is_public: config.is_public,
        display_name: config.display_name || '',
        description: config.description || ''
      };
      this.editDialogOpen = true;

      // Reset form validation
      if (this.$refs.configForm) {
        this.$refs.configForm.resetValidation();
      }
    },

    closeDialog() {
      this.editDialogOpen = false;
    },

    async submitForm() {
      if (!this.formValid) return;

      this.isSubmitting = true;

      try {
        if (this.isEditMode) {
          await this.systemConfigStore.updateConfiguration(this.formData.key, this.formData);
        } else {
          await this.systemConfigStore.createConfiguration(this.formData);
        }
        this.closeDialog();
      } catch (error) {
        console.error('Error submitting form:', error);
      } finally {
        this.isSubmitting = false;
      }
    },

    confirmDelete(config) {
      this.configToDelete = config;
      this.deleteDialogOpen = true;
    },

    async deleteConfiguration() {
      if (!this.configToDelete) return;

      this.isSubmitting = true;

      try {
        await this.systemConfigStore.deleteConfiguration(this.configToDelete.key);
        this.deleteDialogOpen = false;
        this.configToDelete = null;
      } catch (error) {
        console.error('Error deleting configuration:', error);
      } finally {
        this.isSubmitting = false;
      }
    }
  }
});
</script>

<style scoped>
.text-truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
