// resources/js/plugins/apexcharts.js
import VueApexCharts from 'vue3-apexcharts';

export default {
  install: (app) => {
    app.component('apexchart', VueApexCharts);
  }
};
