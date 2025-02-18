import { createApp } from 'vue';
import SearchFWDApp from './App.vue'

const customEnv = process.env.NODE_ENV;
let baseURL = "https://ib-redaktionstool.ddev.site/";


if (customEnv == 'staging') {
  baseURL = "https://ib:ib@ib-redaktion-staging.rmsdev.de/";
}
if (customEnv == 'production') {
  baseURL = "https://redaktion.internationaler-bund.de/";
}


const searchFwdApp = createApp(SearchFWDApp);
searchFwdApp.provide('baseURL', baseURL);
searchFwdApp.mount('#searchFWDApp');
