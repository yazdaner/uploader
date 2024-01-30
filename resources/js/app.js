import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle'
import 'bootstrap/dist/css/bootstrap.min.css'


import { createApp } from 'vue';

import App from './components/App.vue';
import VueAxios from 'vue-axios';

const app = createApp(App);

// app.use(VueAxios);

app.mount('#app');






