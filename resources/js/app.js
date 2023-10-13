import './bootstrap';

import { createApp } from 'vue';

import App from './components/App.vue';
import VueAxios from 'vue-axios';

const app = createApp(App);

app.mount('#app');
app.use(axios,VueAxios);








