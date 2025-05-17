<!-- resources/js/views/admin/AuditLogView.vue -->
<template>
  <div>
    <v-card>
      <v-card-title class="flex-wrap">
        <div class="w-100 w-md-75 mb-4 mb-md-0">
          <h1 class="text-h4 text-sm-h4 text-md-h4">Audit Log Management</h1>
          <p class="text-subtitle-1">Track and monitor all system activities</p>
        </div>
        <div class="w-100 w-md-25 d-flex justify-start justify-md-end">
          <v-btn color="primary" @click="exportLogs" prepend-icon="mdi-download" class="px-3">
            <span class="d-none d-sm-inline">Export Logs</span>
            <span class="d-inline d-sm-none">Export</span>
          </v-btn>
        </div>
      </v-card-title>

      <!-- Filters section -->
      <v-card-text>
        <v-expansion-panels>
          <v-expansion-panel>
            <v-expansion-panel-title>
              <div class="d-flex align-center">
                <v-icon class="mr-2">mdi-filter</v-icon>
                <span>Filters and Search</span>
              </div>
            </v-expansion-panel-title>
            <v-expansion-panel-text>
              <v-row>
                <!-- Search field -->
                <v-col cols="12" sm="6" md="4">
                  <v-text-field
                    v-model="filters.search"
                    label="Search log entries"
                    prepend-inner-icon="mdi-magnify"
                    hide-details
                    density="compact"
                    variant="outlined"
                    @keyup.enter="applyFilters"
                    class="mb-3 mb-sm-0"
                  ></v-text-field>
                </v-col>

                <!-- User filter -->
                <v-col cols="12" sm="6" md="4">
                  <v-select
                    v-model="filters.user"
                    :items="userOptions"
                    label="Filter by user"
                    clearable
                    hide-details
                    density="compact"
                    variant="outlined"
                    class="mb-3 mb-md-0"
                  ></v-select>
                </v-col>

                <!-- Action filter -->
                <v-col cols="12" sm="6" md="4">
                  <v-select
                    v-model="filters.action"
                    :items="actionOptions"
                    label="Filter by action"
                    clearable
                    hide-details
                    density="compact"
                    variant="outlined"
                    class="mb-3 mb-md-0"
                  ></v-select>
                </v-col>

                <!-- Model type filter -->
                <v-col cols="12" sm="6" md="4">
                  <v-select
                    v-model="filters.modelType"
                    :items="modelTypeOptions"
                    label="Filter by model type"
                    clearable
                    hide-details
                    density="compact"
                    variant="outlined"
                    class="mb-3 mb-md-0"
                  ></v-select>
                </v-col>

                <!-- Date range picker -->
                <v-col cols="12" sm="6" md="8">
                  <v-row>
                    <v-col cols="6">
                      <v-menu
                        v-model="startDateMenu"
                        :close-on-content-click="false"
                        transition="scale-transition"
                        min-width="auto"
                      >
                        <template v-slot:activator="{ props }">
                          <v-text-field
                            v-model="filters.startDate"
                            label="Start date"
                            prepend-inner-icon="mdi-calendar"
                            readonly
                            v-bind="props"
                            hide-details
                            density="compact"
                            variant="outlined"
                          ></v-text-field>
                        </template>
                        <v-date-picker
                          v-model="filters.startDate"
                          @update:model-value="startDateMenu = false"
                        ></v-date-picker>
                      </v-menu>
                    </v-col>
                    <v-col cols="6">
                      <v-menu
                        v-model="endDateMenu"
                        :close-on-content-click="false"
                        transition="scale-transition"
                        min-width="auto"
                      >
                        <template v-slot:activator="{ props }">
                          <v-text-field
                            v-model="filters.endDate"
                            label="End date"
                            prepend-inner-icon="mdi-calendar"
                            readonly
                            v-bind="props"
                            hide-details
                            density="compact"
                            variant="outlined"
                          ></v-text-field>
                        </template>
                        <v-date-picker
                          v-model="filters.endDate"
                          @update:model-value="endDateMenu = false"
                        ></v-date-picker>
                      </v-menu>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>

              <!-- Filter buttons -->
              <div class="d-flex justify-end mt-3">
                <v-btn color="secondary" variant="outlined" @click="resetFilters" class="mr-2">
                  Reset
                </v-btn>
                <v-btn color="primary" variant="outlined" @click="applyFilters">
                  Apply Filters
                </v-btn>
              </div>
            </v-expansion-panel-text>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-card-text>

      <!-- Loading overlay -->
      <v-overlay v-if="isLoading" contained class="align-center justify-center">
        <v-progress-circular indeterminate size="64" color="primary"></v-progress-circular>
      </v-overlay>

      <!-- Data table -->
      <div class="table-responsive">
        <v-data-table-server
          v-model:items-per-page="pagination.itemsPerPage"
          v-model:page="pagination.page"
          :headers="getResponsiveHeaders"
          :items="formattedLogs"
          :loading="isLoading"
          :items-length="pagination.totalItems"
          :items-per-page-options="[10, 20, 50, 100]"
          density="compact"
          class="elevation-1"
          @update:options="handleTableOptions"
        >
        <!-- Action column -->
        <template v-slot:item.actionDisplay="{ item }">
          <v-chip :color="item.actionColor" size="small" variant="outlined" class="text-uppercase">
            {{ item.actionDisplay }}
          </v-chip>
        </template>

        <!-- Model column -->
        <template v-slot:item.modelDisplay="{ item }">
          <div>{{ item.modelDisplay }}</div>
          <div class="text-caption">{{ item.model_id }}</div>
        </template>

        <!-- Changes column -->
        <template v-slot:item.changes="{ item }">
          <v-chip-group>
            <v-chip v-if="item.hasOldValues" color="amber" size="small" variant="outlined">
              Modified {{ Object.keys(item.old_values).length }} fields
            </v-chip>
            <v-chip v-if="item.hasNewValues && !item.hasOldValues" color="success" size="small" variant="outlined">
              New record
            </v-chip>
            <v-chip v-else-if="!item.hasNewValues && item.hasOldValues" color="error" size="small" variant="outlined">
              Deleted
            </v-chip>
          </v-chip-group>
        </template>

        <!-- Actions column -->
        <template v-slot:item.actions="{ item }">
          <v-btn icon size="small" color="info" @click="openDetailDialog(item)">
            <v-icon>mdi-eye</v-icon>
          </v-btn>
        </template>

        <!-- Empty state -->
        <template v-slot:no-data>
          <div class="d-flex flex-column align-center py-8">
            <v-icon size="large" color="grey">mdi-database-off</v-icon>
            <p class="text-subtitle-1 mt-2 text-grey">No audit logs found</p>
          </div>
        </template>
      </v-data-table-server>

      <!-- Export format dialog -->
      <v-dialog v-model="exportDialogOpen" max-width="400px">
        <v-card>
          <v-card-title>
            Export Audit Logs
          </v-card-title>
          <v-card-text>
            <p class="mb-4">Select export format:</p>
            <v-radio-group v-model="exportFormat" hide-details>
              <v-radio value="csv" label="CSV"></v-radio>
              <v-radio value="xlsx" label="Excel (XLSX)"></v-radio>
              <v-radio value="json" label="JSON"></v-radio>
            </v-radio-group>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="secondary" text @click="exportDialogOpen = false">
              Cancel
            </v-btn>
            <v-btn
              color="primary"
              :loading="isExporting"
              @click="downloadExport"
            >
              Export
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Log detail dialog -->
      <v-dialog v-model="detailDialogOpen" max-width="900px">
        <v-card>
          <v-card-title class="d-flex justify-space-between">
            <span class="text-h5">Audit Log Details</span>
            <v-btn icon @click="detailDialogOpen = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-card-title>

          <v-divider></v-divider>

          <v-card-text v-if="selectedLog">
            <v-row>
              <v-col cols="12" sm="6">
                <p><strong>Action:</strong>
                  <v-chip :color="getActionColor(selectedLog.action)" size="small">
                    {{ selectedLog.action.toUpperCase() }}
                  </v-chip>
                </p>
                <p><strong>User:</strong> {{ selectedLog.userDisplay }}</p>
                <p><strong>Date/Time:</strong> {{ formatDateTime(selectedLog.created_at) }}</p>
                <p><strong>IP Address:</strong> {{ selectedLog.ip_address }}</p>
              </v-col>

              <v-col cols="12" sm="6">
                <p><strong>Model:</strong> {{ selectedLog.modelDisplay }}</p>
                <p><strong>Model ID:</strong> {{ selectedLog.model_id }}</p>
                <p><strong>URL:</strong> {{ selectedLog.url || 'N/A' }}</p>
                <p><strong>User Agent:</strong> {{ selectedLog.user_agent || 'N/A' }}</p>
              </v-col>
            </v-row>

            <v-divider class="my-4"></v-divider>

            <v-tabs v-model="detailTab">
              <v-tab value="changes">Changes</v-tab>
              <v-tab value="old">Old Values</v-tab>
              <v-tab value="new">New Values</v-tab>
            </v-tabs>

            <v-window v-model="detailTab">
              <!-- Changes tab -->
              <v-window-item value="changes">
                <v-table v-if="hasChanges" density="compact" class="mt-2">
                  <thead>
                    <tr>
                      <th>Field</th>
                      <th>Old Value</th>
                      <th>New Value</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(change, field) in changes" :key="field">
                      <td>{{ formatFieldName(field) }}</td>
                      <td>
                        <span v-if="change.old !== undefined" :class="{'text-error': change.old !== change.new}">
                          {{ formatValue(change.old) }}
                        </span>
                        <span v-else class="text-grey">N/A</span>
                      </td>
                      <td>
                        <span v-if="change.new !== undefined" :class="{'text-success': change.old !== change.new}">
                          {{ formatValue(change.new) }}
                        </span>
                        <span v-else class="text-grey">N/A</span>
                      </td>
                    </tr>
                  </tbody>
                </v-table>
                <div v-else class="text-center py-4 text-grey">
                  No changes detected or change data not available
                </div>
              </v-window-item>

              <!-- Old values tab -->
              <v-window-item value="old">
                <v-table v-if="selectedLog.hasOldValues" density="compact" class="mt-2">
                  <thead>
                    <tr>
                      <th>Field</th>
                      <th>Value</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(value, field) in selectedLog.old_values" :key="field">
                      <td>{{ formatFieldName(field) }}</td>
                      <td>{{ formatValue(value) }}</td>
                    </tr>
                  </tbody>
                </v-table>
                <div v-else class="text-center py-4 text-grey">
                  No old values available
                </div>
              </v-window-item>

              <!-- New values tab -->
              <v-window-item value="new">
                <v-table v-if="selectedLog.hasNewValues" density="compact" class="mt-2">
                  <thead>
                    <tr>
                      <th>Field</th>
                      <th>Value</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(value, field) in selectedLog.new_values" :key="field">
                      <td>{{ formatFieldName(field) }}</td>
                      <td>{{ formatValue(value) }}</td>
                    </tr>
                  </tbody>
                </v-table>
                <div v-else class="text-center py-4 text-grey">
                  No new values available
                </div>
              </v-window-item>
            </v-window>
          </v-card-text>

          <v-card-text v-else class="text-center py-4">
            <v-progress-circular indeterminate color="primary"></v-progress-circular>
            <p class="mt-2">Loading audit log details...</p>
          </v-card-text>
        </v-card>
      </v-dialog>
    </v-card>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import { useAuditLogStore } from '@/stores/admin/auditLogStore';
import { storeToRefs } from 'pinia';

export default defineComponent({
  name: 'AuditLogView',

  setup() {
    const auditLogStore = useAuditLogStore();
    const {
      logs,
      formattedLogs,
      pagination,
      filters,
      isLoading,
      selectedLog,
      detailDialogOpen,
      availableUsers,
      availableActions,
      availableModelTypes
    } = storeToRefs(auditLogStore);

    return {
      auditLogStore,
      logs,
      formattedLogs,
      pagination,
      filters,
      isLoading,
      selectedLog,
      detailDialogOpen,
      availableUsers,
      availableActions,
      availableModelTypes
    };
  },

  data() {
    return {
      headers: [
        { title: 'User', key: 'userDisplay', align: 'start', sortable: false },
        { title: 'Action', key: 'actionDisplay', align: 'center', sortable: true, width: '120px' },
        { title: 'Model', key: 'modelDisplay', align: 'start', sortable: true },
        { title: 'Changes', key: 'changes', align: 'center', sortable: false, width: '150px' },
        { title: 'Date/Time', key: 'datetime', align: 'start', sortable: true, width: '180px' },
        { title: 'Actions', key: 'actions', align: 'center', sortable: false, width: '80px' }
      ],
      startDateMenu: false,
      endDateMenu: false,
      exportDialogOpen: false,
      exportFormat: 'csv',
      isExporting: false,
      detailTab: 'changes'
    };
  },

  computed: {
    // Responsive headers based on screen size
    getResponsiveHeaders() {
      // Get current display size
      const isMobile = window.innerWidth < 600;
      const isTablet = window.innerWidth >= 600 && window.innerWidth < 960;

      if (isMobile) {
        // On mobile, show minimal columns
        return [
          { title: 'User', key: 'userDisplay', align: 'start', sortable: false },
          { title: 'Action', key: 'actionDisplay', align: 'center', sortable: true },
          { title: 'Actions', key: 'actions', align: 'center', sortable: false, width: '60px' }
        ];
      } else if (isTablet) {
        // On tablet, show more columns but still limited
        return [
          { title: 'User', key: 'userDisplay', align: 'start', sortable: false },
          { title: 'Action', key: 'actionDisplay', align: 'center', sortable: true },
          { title: 'Date/Time', key: 'datetime', align: 'start', sortable: true },
          { title: 'Actions', key: 'actions', align: 'center', sortable: false, width: '60px' }
        ];
      }

      // On desktop, show all columns
      return this.headers;
    },

    userOptions() {
      return this.availableUsers.map(user => ({
        value: user.id,
        title: `${user.name} (${user.email})`
      }));
    },

    actionOptions() {
      return this.availableActions.map(action => ({
        value: action,
        title: action.toUpperCase()
      }));
    },

    modelTypeOptions() {
      return this.availableModelTypes.map(modelType => ({
        value: modelType,
        title: this.formatModelType(modelType)
      }));
    },

    hasChanges() {
      if (!this.selectedLog) return false;

      // Check if we have either old or new values
      return (this.selectedLog.hasOldValues || this.selectedLog.hasNewValues);
    },

    changes() {
      if (!this.selectedLog) return {};

      const changes = {};
      const oldValues = this.selectedLog.old_values || {};
      const newValues = this.selectedLog.new_values || {};

      // Combine all fields from both old and new values
      const allFields = new Set([
        ...Object.keys(oldValues),
        ...Object.keys(newValues)
      ]);

      // Create a diff object
      allFields.forEach(field => {
        changes[field] = {
          old: oldValues[field],
          new: newValues[field]
        };
      });

      return changes;
    }
  },

  mounted() {
    this.fetchData();
    // Add window resize listener for responsive layout updates
    window.addEventListener('resize', this.handleResize);
  },

  beforeUnmount() {
    // Clean up resize listener when component is destroyed
    window.removeEventListener('resize', this.handleResize);
  },

  methods: {
    async fetchData() {
      await this.auditLogStore.fetchFilterOptions();
      await this.auditLogStore.fetchLogs();
    },

    handleTableOptions({ page, itemsPerPage, sortBy }) {
      this.pagination.page = page;
      this.pagination.itemsPerPage = itemsPerPage;

      if (sortBy && sortBy.length > 0) {
        this.pagination.sortBy = sortBy[0].key;
        this.pagination.sortDesc = sortBy[0].order === 'desc';
      }

      this.auditLogStore.setPagination(this.pagination);
    },

    applyFilters() {
      this.auditLogStore.setFilters(this.filters);
    },

    resetFilters() {
      this.auditLogStore.resetFilters();
      this.startDateMenu = false;
      this.endDateMenu = false;
    },

    exportLogs() {
      this.exportDialogOpen = true;
    },

    async downloadExport() {
      this.isExporting = true;

      try {
        await this.auditLogStore.exportLogs(this.exportFormat);
        this.exportDialogOpen = false;
      } finally {
        this.isExporting = false;
      }
    },

    async openDetailDialog(log) {
      // If we already have the full log details, just open the dialog
      if (this.selectedLog && this.selectedLog.id === log.id) {
        this.detailDialogOpen = true;
        return;
      }

      // Otherwise fetch the details first
      await this.auditLogStore.getLogDetails(log.id);
      this.detailDialogOpen = true;
      this.detailTab = 'changes';
    },

    formatDateTime(dateTime) {
      if (!dateTime) return 'N/A';
      const date = new Date(dateTime);
      return date.toLocaleString();
    },

    formatFieldName(field) {
      return field
        .replace(/_/g, ' ')
        .replace(/\b\w/g, char => char.toUpperCase());
    },

    formatModelType(modelType) {
      if (!modelType) return '';

      // Extract class name from namespace
      const parts = modelType.split('\\');
      const className = parts[parts.length - 1];

      // Convert camel case to words with spaces
      return className
        .replace(/([A-Z])/g, ' $1')
        .replace(/^./, str => str.toUpperCase())
        .trim();
    },

    formatValue(value) {
      if (value === null || value === undefined) return 'null';
      if (typeof value === 'object') return JSON.stringify(value);
      if (typeof value === 'boolean') return value ? 'True' : 'False';
      return String(value);
    },

    getActionColor(action) {
      const colors = {
        'created': 'success',
        'updated': 'info',
        'deleted': 'error',
        'restored': 'warning',
        'login': 'purple',
        'logout': 'grey'
      };
      return colors[action] || 'primary';
    },

    // Handle resize events for responsive headers
    handleResize() {
      // Force component to update when screen size changes
      this.$forceUpdate();
    }
  }
});
</script>

<style scoped>
.text-error {
  color: #ff5252;
}

.text-success {
  color: #4caf50;
}

/* Responsive styles */
.table-responsive {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

@media (max-width: 600px) {
  /* Mobile optimizations */
  :deep(.v-data-table) th,
  :deep(.v-data-table) td {
    padding: 6px 8px !important;
  }

  :deep(.v-btn--icon) {
    width: 36px !important;
    height: 36px !important;
    min-width: 0 !important;
    margin: 0 2px !important;
  }

  /* Better touch targets */
  :deep(.v-btn),
  :deep(.v-list-item) {
    min-height: 44px;
  }
}

/* Layout classes */
.w-100 {
  width: 100%;
}

.w-md-75 {
  width: 100%;
}

.w-md-25 {
  width: 100%;
}

@media (min-width: 960px) {
  .w-md-75 {
    width: 75%;
  }

  .w-md-25 {
    width: 25%;
  }
}

/* Dialog responsiveness */
@media (max-width: 600px) {
  :deep(.v-card-title) {
    font-size: 1.25rem !important;
  }

  :deep(.v-dialog) {
    width: 95% !important;
    margin: 0 !important;
  }
}
</style>
