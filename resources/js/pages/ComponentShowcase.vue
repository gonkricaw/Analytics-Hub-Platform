<!-- resources/js/pages/ComponentShowcase.vue -->
<template>
  <MainLayout>
    <v-container>
      <!-- Header -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6" v-gsap-fade-in>
            <v-card-title class="text-h5 bg-primary text-white">
              Component Showcase
            </v-card-title>
            <v-card-text class="pa-4">
              <p>This page showcases the various components available in the Indonet Analytics Hub Platform based on the MCP get-library-docs standards.</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Color Palette Section -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6" v-gsap-fade-in>
            <v-card-title class="bg-grey-darken-3">Color Palette</v-card-title>
            <v-card-text class="pa-4">
              <v-row>
                <v-col v-for="(color, name) in themeColors" :key="name" cols="6" md="3" lg="2">
                  <div
                    class="d-flex flex-column align-center pa-4 rounded"
                    style="height: 100px;"
                    :style="{ backgroundColor: color }"
                  >
                    <span class="text-caption" :class="getTextColorClass(color)">{{ name }}</span>
                    <span class="text-caption" :class="getTextColorClass(color)">{{ color }}</span>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Typography Section -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6" v-gsap-fade-in>
            <v-card-title class="bg-grey-darken-3">Typography</v-card-title>
            <v-card-text class="pa-4">
              <div class="text-h1">Heading 1</div>
              <div class="text-h2">Heading 2</div>
              <div class="text-h3">Heading 3</div>
              <div class="text-h4">Heading 4</div>
              <div class="text-h5">Heading 5</div>
              <div class="text-h6">Heading 6</div>
              <div class="text-subtitle-1">Subtitle 1</div>
              <div class="text-subtitle-2">Subtitle 2</div>
              <div class="text-body-1">Body 1: This is some sample text for the body-1 style.</div>
              <div class="text-body-2">Body 2: This is some sample text for the body-2 style.</div>
              <div class="text-caption">Caption Text</div>
              <div class="text-overline">OVERLINE TEXT</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Buttons Section -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6" v-gsap-fade-in>
            <v-card-title class="bg-grey-darken-3">Button Variants</v-card-title>
            <v-card-text class="pa-4">
              <v-row>
                <v-col cols="12" class="mb-4">
                  <div class="text-h6 mb-2">Colors</div>
                  <v-btn class="mr-2 mb-2" color="primary">Primary</v-btn>
                  <v-btn class="mr-2 mb-2" color="secondary">Secondary</v-btn>
                  <v-btn class="mr-2 mb-2" color="accent">Accent</v-btn>
                  <v-btn class="mr-2 mb-2" color="error">Error</v-btn>
                  <v-btn class="mr-2 mb-2" color="info">Info</v-btn>
                  <v-btn class="mr-2 mb-2" color="success">Success</v-btn>
                  <v-btn class="mr-2 mb-2" color="warning">Warning</v-btn>
                </v-col>

                <v-col cols="12" class="mb-4">
                  <div class="text-h6 mb-2">Variants</div>
                  <v-btn class="mr-2 mb-2" color="primary">Default</v-btn>
                  <v-btn class="mr-2 mb-2" color="primary" variant="flat">Flat</v-btn>
                  <v-btn class="mr-2 mb-2" color="primary" variant="tonal">Tonal</v-btn>
                  <v-btn class="mr-2 mb-2" color="primary" variant="outlined">Outlined</v-btn>
                  <v-btn class="mr-2 mb-2" color="primary" variant="text">Text</v-btn>
                  <v-btn class="mr-2 mb-2" color="primary" variant="plain">Plain</v-btn>
                </v-col>

                <v-col cols="12" class="mb-4">
                  <div class="text-h6 mb-2">Sizes</div>
                  <v-btn class="mr-2 mb-2" color="primary" size="x-small">X-Small</v-btn>
                  <v-btn class="mr-2 mb-2" color="primary" size="small">Small</v-btn>
                  <v-btn class="mr-2 mb-2" color="primary">Default</v-btn>
                  <v-btn class="mr-2 mb-2" color="primary" size="large">Large</v-btn>
                  <v-btn class="mr-2 mb-2" color="primary" size="x-large">X-Large</v-btn>
                </v-col>

                <v-col cols="12" class="mb-4">
                  <div class="text-h6 mb-2">With Icons</div>
                  <v-btn class="mr-2 mb-2" color="primary" prepend-icon="mdi-check">Save</v-btn>
                  <v-btn class="mr-2 mb-2" color="error" prepend-icon="mdi-delete">Delete</v-btn>
                  <v-btn class="mr-2 mb-2" color="info" append-icon="mdi-arrow-right">Next</v-btn>
                  <v-btn class="mr-2 mb-2" icon="mdi-pencil" color="warning"></v-btn>
                  <v-btn class="mr-2 mb-2" icon="mdi-content-save" color="success"></v-btn>
                </v-col>

                <v-col cols="12">
                  <div class="text-h6 mb-2">GSAP Animation on Hover</div>
                  <v-btn
                    class="mr-2 mb-2"
                    color="primary"
                    v-gsap-hover="{ scale: 1.1, duration: 0.2 }"
                  >
                    Hover Me
                  </v-btn>
                  <v-btn
                    class="mr-2 mb-2"
                    color="secondary"
                    v-gsap-hover="{ scale: 1.1, rotation: 5, duration: 0.2 }"
                  >
                    Rotate on Hover
                  </v-btn>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Cards Section -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6" v-gsap-fade-in>
            <v-card-title class="bg-grey-darken-3">Card Variants</v-card-title>
            <v-card-text class="pa-4">
              <v-row>
                <v-col cols="12" md="4">
                  <v-card class="mb-4" v-gsap-hover="{ scale: 1.03, duration: 0.2 }">
                    <v-img
                      src="https://picsum.photos/seed/card1/600/200"
                      height="200px"
                      cover
                    ></v-img>
                    <v-card-title>Basic Card</v-card-title>
                    <v-card-text>
                      This is a basic card with an image, title, text, and actions.
                    </v-card-text>
                    <v-card-actions>
                      <v-btn color="primary">Action</v-btn>
                      <v-spacer></v-spacer>
                      <v-btn icon="mdi-heart" variant="text"></v-btn>
                      <v-btn icon="mdi-share-variant" variant="text"></v-btn>
                    </v-card-actions>
                  </v-card>
                </v-col>

                <v-col cols="12" md="4">
                  <v-card class="mb-4" variant="outlined" v-gsap-hover="{ scale: 1.03, duration: 0.2 }">
                    <v-card-title>Outlined Card</v-card-title>
                    <v-card-subtitle>Subtitle text</v-card-subtitle>
                    <v-card-text>
                      This is an outlined card variant with title, subtitle, and actions.
                    </v-card-text>
                    <v-card-actions>
                      <v-btn color="primary" variant="text">Learn More</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-col>

                <v-col cols="12" md="4">
                  <v-card class="mb-4" elevation="12" v-gsap-hover="{ scale: 1.03, duration: 0.2 }">
                    <v-card-title class="bg-primary text-white">Elevated Card</v-card-title>
                    <v-card-text>
                      This card has custom elevation and a colored title background.
                    </v-card-text>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="primary" variant="text">OK</v-btn>
                      <v-btn color="error" variant="text">Cancel</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Form Inputs Section -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6" v-gsap-fade-in>
            <v-card-title class="bg-grey-darken-3">Form Inputs</v-card-title>
            <v-card-text class="pa-4">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    label="Text Field"
                    placeholder="Enter text"
                    hint="This is a hint text"
                    persistent-hint
                  ></v-text-field>

                  <v-text-field
                    label="With Prepend/Append"
                    placeholder="Enter amount"
                    prepend-inner-icon="mdi-currency-usd"
                    append-inner-icon="mdi-eye"
                    class="mt-4"
                  ></v-text-field>

                  <v-textarea
                    label="Text Area"
                    placeholder="Enter multiple lines of text"
                    rows="3"
                    auto-grow
                    class="mt-4"
                  ></v-textarea>

                  <v-file-input
                    label="File Input"
                    placeholder="Select file"
                    prepend-icon="mdi-file"
                    class="mt-4"
                  ></v-file-input>
                </v-col>

                <v-col cols="12" md="6">
                  <v-select
                    label="Select"
                    :items="['Option 1', 'Option 2', 'Option 3']"
                  ></v-select>

                  <v-autocomplete
                    label="Autocomplete"
                    :items="['United States', 'Canada', 'Mexico', 'Brazil']"
                    class="mt-4"
                  ></v-autocomplete>

                  <v-switch
                    label="Switch"
                    color="primary"
                    class="mt-4"
                  ></v-switch>

                  <v-checkbox
                    label="Checkbox"
                    color="primary"
                    class="mt-4"
                  ></v-checkbox>

                  <v-radio-group label="Radio Group" class="mt-4">
                    <v-radio label="Option 1" value="1" color="primary"></v-radio>
                    <v-radio label="Option 2" value="2" color="primary"></v-radio>
                  </v-radio-group>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Jumbotron Component -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6" v-gsap-fade-in="{ delay: 0.1 }">
            <v-card-title class="bg-grey-darken-3">Jumbotron Carousel</v-card-title>
            <v-card-text class="pa-0">
              <JumbotronCarousel />
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Digital Clock Component -->
      <v-row>
        <v-col cols="12" md="6">
          <v-card class="mb-6" v-gsap-fade-in="{ delay: 0.2 }">
            <v-card-title class="bg-grey-darken-3">Digital Clock</v-card-title>
            <v-card-text class="pa-4">
              <DigitalClock />
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Rich Text Editor Component -->
        <v-col cols="12" md="6">
          <v-card class="mb-6" v-gsap-fade-in="{ delay: 0.3 }">
            <v-card-title class="bg-grey-darken-3">Rich Text Editor</v-card-title>
            <v-card-text class="pa-4">
              <QuillEditor theme="snow" placeholder="Write something..." />
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Chart Component -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6" v-gsap-fade-in="{ delay: 0.4 }">
            <v-card-title class="bg-grey-darken-3">ApexCharts Line Chart</v-card-title>
            <v-card-text class="pa-4">
              <apexchart
                type="line"
                height="350"
                :options="chartOptions"
                :series="series"
              ></apexchart>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Animation Showcase -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6" v-gsap-fade-in="{ delay: 0.5 }">
            <v-card-title class="bg-grey-darken-3">GSAP Animation Examples</v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="4" class="d-flex justify-center">
                  <v-btn color="primary" size="large" v-gsap-hover>
                    Hover Me
                  </v-btn>
                </v-col>
                <v-col cols="12" md="4" class="d-flex justify-center">
                  <v-icon size="36" color="error" v-gsap-pulse>
                    mdi-bell
                  </v-icon>
                </v-col>
                <v-col cols="12" md="4" class="d-flex justify-center">
                  <v-chip color="success" v-gsap-fade-in="{ duration: 2, delay: 1 }">
                    Fade In Effect
                  </v-chip>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </MainLayout>
</template>

<script>
import { useVuetify } from 'vuetify';
import MainLayout from '@layouts/MainLayout.vue';
import JumbotronCarousel from '@components/JumbotronCarousel.vue';
import DigitalClock from '@components/DigitalClock.vue';
import { QuillEditor } from '@vueup/vue-quill';

export default {
  name: 'ComponentShowcase',
  components: {
    MainLayout,
    JumbotronCarousel,
    DigitalClock,
    QuillEditor
  },

  data() {
    return {
      chartOptions: {
        chart: {
          id: 'login-chart',
          toolbar: {
            show: false
          },
          zoom: {
            enabled: false
          },
          foreColor: '#ccc'
        },
        stroke: {
          curve: 'smooth',
          width: 3
        },
        colors: ['#8C3EFF'],
        xaxis: {
          categories: Array.from({ length: 15 }, (_, i) => {
            const date = new Date();
            date.setDate(date.getDate() - (14 - i));
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
          })
        },
        yaxis: {
          title: {
            text: 'Login Count'
          }
        },
        tooltip: {
          theme: 'dark'
        },
        grid: {
          borderColor: '#404040'
        }
      },
      series: [{
        name: 'Logins',
        data: [30, 25, 36, 30, 45, 35, 40, 60, 35, 25, 30, 45, 32, 40, 55]
      }]
    };
  },

  computed: {
    themeColors() {
      const vuetify = useVuetify();
      const theme = vuetify.theme.global.current.value;

      return {
        'Primary': theme.colors.primary,
        'Secondary': theme.colors.secondary,
        'Accent': theme.colors.accent,
        'Error': theme.colors.error,
        'Info': theme.colors.info,
        'Success': theme.colors.success,
        'Warning': theme.colors.warning,
      };
    }
  },

  methods: {
    getTextColorClass(backgroundColor) {
      // Simple algorithm to determine if text should be light or dark based on background
      const rgb = this.hexToRgb(backgroundColor);
      if (!rgb) return 'text-white';

      // Calculate brightness (0-255)
      const brightness = Math.round((rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000);

      // Return white text on dark backgrounds, black text on light backgrounds
      return brightness > 128 ? 'text-black' : 'text-white';
    },

    hexToRgb(hex) {
      const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
      return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
      } : null;
    }
  }
};
</script>

<style lang="scss" scoped>
// Component showcase specific styles
</style>
