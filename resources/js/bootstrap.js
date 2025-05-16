import axios from 'axios';
import api from './plugins/axios';

// Set up global axios for backward compatibility
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Export our configured api instance for use throughout the app
window.api = api;
