import { createApp } from 'vue';
import SearchFWDApp from './App.vue'

const customEnv = process.env.NODE_ENV;

const appContainer = document.getElementById('searchFWDApp');

const searchFwdApp = createApp(SearchFWDApp);
searchFwdApp.provide('proxyURL', '/proxy/fwd.php');
searchFwdApp.mount('#searchFWDApp');
