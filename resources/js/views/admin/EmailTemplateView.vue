<!-- resources/js/views/admin/EmailTemplateView.vue -->
<template>
  <div>
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center">
        <div>
          <h1 class="text-h4">Email Template Management</h1>
          <p class="text-subtitle-1">Manage email templates for system notifications</p>
        </div>
        <v-btn color="primary" @click="openCreateDialog" prepend-icon="mdi-plus">
          Add Template
        </v-btn>
      </v-card-title>

      <!-- Filters section -->
      <v-card-text>
        <v-row>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="filters.search"
              label="Search by name or subject"
              prepend-inner-icon="mdi-magnify"
              hide-details
              dense
              outlined
              @keyup.enter="applyFilters"
            ></v-text-field>
          </v-col>
          <v-col cols="12" sm="6">
            <v-select
              v-model="filters.isActive"
              :items="statusOptions"
              label="Filter by status"
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
      <v-data-table
        :headers="headers"
        :items="formattedTemplates"
        :loading="isLoading"
        class="elevation-1"
      >
        <!-- Custom headers -->
        <template v-slot:header.actions>
          <span class="text-subtitle-1 font-weight-black">Actions</span>
        </template>

        <!-- Status column -->
        <template v-slot:item.statusText="{ item }">
          <v-chip :color="item.statusColor" size="small">
            {{ item.statusText }}
          </v-chip>
        </template>

        <!-- Actions column -->
        <template v-slot:item.actions="{ item }">
          <div class="d-flex justify-start">
            <v-btn icon size="small" color="info" @click="previewTemplate(item)" class="mr-1">
              <v-icon>mdi-eye</v-icon>
            </v-btn>
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
            <v-icon size="large" color="grey">mdi-email-off</v-icon>
            <p class="text-subtitle-1 mt-2 text-grey">No email templates found</p>
          </div>
        </template>
      </v-data-table>

      <!-- Pagination -->
      <div class="d-flex justify-center py-3">
        <v-pagination
          v-model="pagination.page"
          :length="Math.ceil(pagination.totalItems / pagination.itemsPerPage)"
          @update:modelValue="fetchTemplates"
          total-visible="7"
        ></v-pagination>
      </div>
    </v-card>

    <!-- Create/Edit Template Dialog -->
    <v-dialog v-model="editDialogOpen" max-width="1000px" scrollable>
      <v-card>
        <v-card-title>
          <span class="text-h5">{{ isEditMode ? 'Edit Email Template' : 'Add Email Template' }}</span>
        </v-card-title>

        <v-card-text class="pt-4">
          <v-form ref="templateForm" v-model="formValid">
            <v-container>
              <v-tabs v-model="activeTab" grow>
                <v-tab value="basic">Basic Information</v-tab>
                <v-tab value="content">Email Content</v-tab>
                <v-tab value="placeholders">Placeholders</v-tab>
              </v-tabs>

              <v-window v-model="activeTab">
                <!-- Basic Information Tab -->
                <v-window-item value="basic">
                  <v-row>
                    <!-- Name field -->
                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model="formData.name"
                        label="Name"
                        :rules="[v => !!v || 'Name is required']"
                        required
                      ></v-text-field>
                    </v-col>

                    <!-- Subject field -->
                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model="formData.subject"
                        label="Subject"
                        :rules="[v => !!v || 'Subject is required']"
                        required
                      ></v-text-field>
                    </v-col>

                    <!-- Active status -->
                    <v-col cols="12" sm="6">
                      <v-switch
                        v-model="formData.is_active"
                        label="Active"
                        color="success"
                      ></v-switch>
                    </v-col>

                    <!-- Sender name (optional) -->
                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model="formData.sender_name"
                        label="Sender Name (optional)"
                        hint="Leave blank to use system default sender name"
                        persistent-hint
                      ></v-text-field>
                    </v-col>

                    <!-- Sender email (optional) -->
                    <v-col cols="12">
                      <v-text-field
                        v-model="formData.sender_email"
                        label="Sender Email (optional)"
                        hint="Leave blank to use system default sender email"
                        persistent-hint
                        :rules="[v => !v || v.includes('@') || 'Email should be valid']"
                      ></v-text-field>
                    </v-col>
                  </v-row>
                </v-window-item>

                <!-- Email Content Tab -->
                <v-window-item value="content">
                  <v-row>
                    <!-- HTML Body -->
                    <v-col cols="12">
                      <p class="text-subtitle-1 font-weight-bold mb-2">HTML Content</p>
                      <v-textarea
                        v-model="formData.body"
                        label="HTML Body"
                        :rules="[v => !!v || 'HTML body is required']"
                        required
                        auto-grow
                        rows="15"
                        class="code-editor"
                        outlined
                        hint="Enter HTML content. You can use placeholders in the format {{placeholder_name}}"
                        persistent-hint
                      ></v-textarea>
                    </v-col>

                    <!-- Plain Text (optional) -->
                    <v-col cols="12">
                      <p class="text-subtitle-1 font-weight-bold mb-2">Plain Text Version (optional)</p>
                      <v-textarea
                        v-model="formData.plain_text"
                        label="Plain Text Version"
                        auto-grow
                        rows="8"
                        class="code-editor"
                        outlined
                        hint="Plain text fallback version. Leave blank to auto-generate from HTML version. You can use placeholders in the format {{placeholder_name}}"
                        persistent-hint
                      ></v-textarea>
                    </v-col>
                  </v-row>
                </v-window-item>

                <!-- Placeholders Tab -->
                <v-window-item value="placeholders">
                  <v-row>
                    <v-col cols="12">
                      <p class="text-subtitle-1 mb-3">Define available placeholders for this template</p>

                      <v-list>
                        <div class="d-flex mb-2">
                          <v-text-field
                            v-model="newPlaceholder"
                            label="New placeholder"
                            hint="Add a new placeholder"
                            persistent-hint
                            class="mr-2"
                          ></v-text-field>
                          <v-btn
                            color="primary"
                            :disabled="!newPlaceholder"
                            @click="addPlaceholder"
                            size="large"
                            class="mt-2"
                          >
                            Add
                          </v-btn>
                        </div>

                        <v-list-item
                          v-for="(placeholder, index) in formData.placeholders"
                          :key="index"
                          :title="placeholder"
                          :subtitle="`Use as: {{${placeholder}}}`"
                        >
                          <template v-slot:append>
                            <v-btn icon color="error" size="small" @click="removePlaceholder(index)">
                              <v-icon>mdi-delete</v-icon>
                            </v-btn>
                          </template>
                        </v-list-item>

                        <v-list-item v-if="formData.placeholders.length === 0">
                          <div class="text-center py-4 text-grey">
                            No placeholders defined. Add some using the field above.
                          </div>
                        </v-list-item>
                      </v-list>

                      <v-alert
                        type="info"
                        variant="tonal"
                        class="mt-4"
                      >
                        <p class="text-subtitle-1 font-weight-bold">Common placeholders</p>
                        <v-list density="compact">
                          <v-list-item title="name" subtitle="User's name"></v-list-item>
                          <v-list-item title="email" subtitle="User's email address"></v-list-item>
                          <v-list-item title="login_url" subtitle="URL to the login page"></v-list-item>
                          <v-list-item title="reset_url" subtitle="URL for password reset"></v-list-item>
                          <v-list-item title="year" subtitle="Current year (automatically added)"></v-list-item>
                        </v-list>
                      </v-alert>
                    </v-col>
                  </v-row>
                </v-window-item>
              </v-window>
            </v-container>
          </v-form>
        </v-card-text>

        <v-divider></v-divider>

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
            {{ isEditMode ? 'Update Template' : 'Create Template' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Template Preview Dialog -->
    <v-dialog v-model="previewDialogOpen" max-width="900px">
      <v-card>
        <v-card-title class="d-flex justify-space-between">
          <span class="text-h5">Template Preview: {{ selectedTemplate?.name }}</span>
          <v-btn icon @click="previewDialogOpen = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <v-card-text>
          <v-tabs v-model="previewTab">
            <v-tab value="rendered">Rendered View</v-tab>
            <v-tab value="html">HTML Source</v-tab>
            <v-tab v-if="selectedTemplate?.plain_text" value="text">Plain Text</v-tab>
          </v-tabs>

          <v-window v-model="previewTab">
            <v-window-item value="rendered">
              <div class="pa-4">
                <p class="text-subtitle-1 mb-2">
                  <strong>Subject:</strong> {{ selectedTemplate?.subject }}
                </p>
                <div class="email-preview">
                  <iframe
                    :srcdoc="selectedTemplate?.body"
                    style="width: 100%; height: 600px; border: 1px solid #e0e0e0;"
                  ></iframe>
                </div>
              </div>
            </v-window-item>

            <v-window-item value="html">
              <div class="pa-4">
                <v-textarea
                  v-model="selectedTemplate?.body"
                  readonly
                  auto-grow
                  rows="20"
                  class="code-editor"
                  outlined
                ></v-textarea>
              </div>
            </v-window-item>

            <v-window-item value="text" v-if="selectedTemplate?.plain_text">
              <div class="pa-4">
                <v-textarea
                  v-model="selectedTemplate?.plain_text"
                  readonly
                  auto-grow
                  rows="20"
                  class="code-editor"
                  outlined
                ></v-textarea>
              </div>
            </v-window-item>
          </v-window>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialogOpen" max-width="400">
      <v-card>
        <v-card-title class="text-h5">
          Confirm Delete
        </v-card-title>
        <v-card-text>
          Are you sure you want to delete the email template
          <strong>"{{ templateToDelete?.name }}"</strong>? This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey-darken-1" text @click="deleteDialogOpen = false">
            Cancel
          </v-btn>
          <v-btn color="error" :loading="isSubmitting" @click="deleteTemplate">
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import { useEmailTemplateStore } from '@/stores/admin/emailTemplateStore';
import { storeToRefs } from 'pinia';

export default defineComponent({
  name: 'EmailTemplateView',

  setup() {
    const emailTemplateStore = useEmailTemplateStore();
    const {
      templates,
      selectedTemplate,
      pagination,
      filters,
      isLoading,
      formattedTemplates
    } = storeToRefs(emailTemplateStore);

    return {
      emailTemplateStore,
      templates,
      selectedTemplate,
      pagination,
      filters,
      isLoading,
      formattedTemplates
    };
  },

  data() {
    return {
      headers: [
        { title: 'Name', key: 'name', align: 'start', sortable: true },
        { title: 'Subject', key: 'subject', align: 'start', sortable: true },
        { title: 'Status', key: 'statusText', align: 'center', sortable: false },
        { title: 'Placeholders', key: 'placeholderText', align: 'start', sortable: false },
        { title: 'Last Updated', key: 'updatedAt', align: 'center', sortable: true },
        { title: 'Actions', key: 'actions', align: 'center', sortable: false }
      ],
      editDialogOpen: false,
      previewDialogOpen: false,
      deleteDialogOpen: false,
      activeTab: 'basic',
      previewTab: 'rendered',
      isEditMode: false,
      isSubmitting: false,
      formValid: false,
      formData: this.getDefaultFormData(),
      templateToDelete: null,
      newPlaceholder: '',
      statusOptions: [
        { title: 'Active', value: true },
        { title: 'Inactive', value: false }
      ]
    };
  },

  mounted() {
    this.fetchTemplates();
  },

  methods: {
    getDefaultFormData() {
      return {
        name: '',
        subject: '',
        body: '',
        plain_text: '',
        is_active: true,
        sender_name: '',
        sender_email: '',
        placeholders: []
      };
    },

    async fetchTemplates() {
      await this.emailTemplateStore.fetchTemplates();
    },

    applyFilters() {
      this.emailTemplateStore.setFilters(this.filters);
    },

    resetFilters() {
      this.emailTemplateStore.resetFilters();
    },

    previewTemplate(template) {
      this.selectedTemplate = template;
      this.previewDialogOpen = true;
      this.previewTab = 'rendered';
    },

    openCreateDialog() {
      this.isEditMode = false;
      this.formData = this.getDefaultFormData();
      this.activeTab = 'basic';
      this.editDialogOpen = true;

      // Reset form validation
      if (this.$refs.templateForm) {
        this.$refs.templateForm.resetValidation();
      }
    },

    openEditDialog(template) {
      this.isEditMode = true;
      this.formData = {
        id: template.id,
        name: template.name,
        subject: template.subject,
        body: template.body,
        plain_text: template.plain_text || '',
        is_active: template.is_active,
        sender_name: template.sender_name || '',
        sender_email: template.sender_email || '',
        placeholders: template.placeholders ? [...template.placeholders] : []
      };
      this.activeTab = 'basic';
      this.editDialogOpen = true;

      // Reset form validation
      if (this.$refs.templateForm) {
        this.$refs.templateForm.resetValidation();
      }
    },

    closeDialog() {
      this.editDialogOpen = false;
      this.formData = this.getDefaultFormData();
    },

    addPlaceholder() {
      if (this.newPlaceholder && !this.formData.placeholders.includes(this.newPlaceholder)) {
        this.formData.placeholders.push(this.newPlaceholder);
      }
      this.newPlaceholder = '';
    },

    removePlaceholder(index) {
      this.formData.placeholders.splice(index, 1);
    },

    async submitForm() {
      if (!this.formValid) return;

      this.isSubmitting = true;

      try {
        if (this.isEditMode) {
          await this.emailTemplateStore.updateTemplate(this.formData.id, this.formData);
        } else {
          await this.emailTemplateStore.createTemplate(this.formData);
        }
        this.closeDialog();
      } catch (error) {
        console.error('Error submitting form:', error);
      } finally {
        this.isSubmitting = false;
      }
    },

    confirmDelete(template) {
      this.templateToDelete = template;
      this.deleteDialogOpen = true;
    },

    async deleteTemplate() {
      if (!this.templateToDelete) return;

      this.isSubmitting = true;

      try {
        await this.emailTemplateStore.deleteTemplate(this.templateToDelete.id);
        this.deleteDialogOpen = false;
        this.templateToDelete = null;
      } catch (error) {
        console.error('Error deleting template:', error);
      } finally {
        this.isSubmitting = false;
      }
    }
  }
});
</script>

<style scoped>
.code-editor {
  font-family: 'Courier New', Courier, monospace;
  font-size: 14px;
}

.email-preview {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  background-color: white;
}
</style>
