// resources/js/plugins/vuetify.js
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { md3 } from 'vuetify/blueprints';
import '@mdi/font/css/materialdesignicons.css';

export default createVuetify({
    blueprint: md3,
    theme: {
        defaultTheme: 'dark',
        themes: {
            dark: {
                dark: true,
                colors: {
                    primary: '#8C3EFF',
                    secondary: '#424242',
                    accent: '#FF4081',
                    error: '#FF5252',
                    info: '#2196F3',
                    success: '#4CAF50',
                    warning: '#FFC107',
                }
            }
        }
    },
    defaults: {
        VBtn: {
            variant: 'elevated',
            ripple: true,
        },
        VCard: {
            variant: 'elevated',
        }
    },
    icons: {
        defaultSet: 'mdi',
    },
});
