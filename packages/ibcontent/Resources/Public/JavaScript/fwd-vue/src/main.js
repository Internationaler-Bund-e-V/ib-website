import { createApp } from 'vue';
import SearchFWDApp from './App.vue'

const customEnv = process.env.NODE_ENV;
let baseURL = "https://redaktion.internationaler-bund.de/";


if (location.href.indexOf('.rmsdev.de') > 0) {
  baseURL = "https://ib:ib@ib-redaktion-staging.rmsdev.de/";
}

if (location.href.indexOf('.ddev.site') > 0) {
  // baseURL = "https://ib-redaktionstool.ddev.site/";
}

const searchFwdApp = createApp(SearchFWDApp);
searchFwdApp.provide('baseURL', baseURL);
searchFwdApp.mount('#searchFWDApp');
