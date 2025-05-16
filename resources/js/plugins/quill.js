// resources/js/plugins/quill.js
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

export default {
  install: (app) => {
    app.component('QuillEditor', QuillEditor);
  }
};
