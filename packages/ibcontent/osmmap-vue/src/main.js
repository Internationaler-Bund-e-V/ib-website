import { createApp, reactive, ref } from 'vue'
import OsmMapApp from './App.vue'
import OsmFwdMapApp from './fwdMap.vue'
import OsmListApp from './ListApp.vue';
import OpenLayersMap from 'vue3-openlayers'


const customEnv = import.meta.env.VITE_MODE;

//let baseInterfaceURL = 'https://redaktion.internationaler-bund.de/'
//let baseInterfaceURL = 'https://redaktionstool.ddev.site/'
//let imageBaseURL = 'https://redaktionstool.ddev.site/'
let baseInterfaceURL = 'https://redaktion-relaunch.ddev.site/'
let imageBaseURL = 'https://redaktion-relaunch.ddev.site/'
let proxyURL = "/typo3conf/ext/ibcontent/Resources/Public/dist/osmProxy.php";

if (customEnv === 'staging') {
  baseInterfaceURL = 'https://ib:ib@ib-redaktion-staging.rmsdev.de/';
  imageBaseURL = 'https://ib-redaktion-staging.rmsdev.de/';
}
if (customEnv === 'production') {
  baseInterfaceURL = 'https://redaktion.internationaler-bund.de/'
  imageBaseURL = 'https://redaktion.internationaler-bund.de/'
}

let startZoomLevel = 6;
let startLongitude = 10.451526;
let startLatitude = 51.165691;



// due to single instace openlayers "bug"
const appContainer = document.getElementById('osmMapContainer');
const typoSettings = {
  map: appContainer.dataset.map,
  borderColor: appContainer.dataset.bordercolor,
  mapColor: appContainer.dataset.mapcolor,
  pinColor: appContainer.dataset.pincolor,
  navigations: appContainer.dataset.navigations.split(","),
  mapLayout: appContainer.dataset.appid,
  mainNavigation: appContainer.dataset.navigations[0],
  listViewNavigation: appContainer.dataset.navigations,
  showTagFilter: appContainer.dataset.showtagfilter,
  tagFilterHeadline: appContainer.dataset.tagfilterheadlline,
  showCategoryFilter: appContainer.dataset.showcategoryfilter,
  showFederalStateFilter: appContainer.dataset.showfederalstatefilter,
  emailLabel: appContainer.dataset.emaillabel,
  showLinkButton: appContainer.dataset.showlinkbutton,
  linkButtonColor: appContainer.dataset.linkbuttoncolor,
  tileBorderColor: appContainer.dataset.tilebordercolor,
  borderButtonColor: appContainer.dataset.borderbuttoncolor,
  borderButtonColorClass: 'darkblue',
  searchBarBackgroundColor: appContainer.dataset.searchbarbackgroundcolor,
  useCustomCenter: appContainer.dataset.usecustomcenter,
  customLongitude: appContainer.dataset.customlongitude,
  customLatitude: appContainer.dataset.customlatitude,
  customZoomLevel: appContainer.dataset.customzoomlevel,
  usePreFilterCategory: appContainer.dataset.useprefiltercategory,
  preFilterCategoryIDs: appContainer.dataset.prefiltercategoryids


};



const osmMapApp = createApp(OsmMapApp)
osmMapApp.use(OpenLayersMap, { inject: false })

osmMapApp.provide('Locations', ref([]));
osmMapApp.provide('Clusters', ref([]));
osmMapApp.provide('Tags', ref([]));
osmMapApp.provide('Categories', ref([]));
osmMapApp.provide('selectedCategories', ref([]));
osmMapApp.provide('selectedDistance', ref(50));
osmMapApp.provide('selectedTag', ref(String('Alle')));
osmMapApp.provide('Location', ref(Object));
osmMapApp.provide('Cluster', ref(Object));
if (typoSettings.mapLayout == 'Navigation') {
  osmMapApp.provide('Navigation', ref(typoSettings.navigations));
}
else {
  osmMapApp.provide('Navigation', ref(appContainer.dataset.navigations));
  typoSettings.mainNavigation = appContainer.dataset.navigations;
}

//check for custom geo coordinates
if (typoSettings.useCustomCenter == 1) {
  startLatitude = typoSettings.customLatitude;
  startLongitude = typoSettings.customLongitude;
}
if (typoSettings.customZoomLevel != '') {
  startZoomLevel = typoSettings.customZoomLevel;
}
//set border color class
if (typoSettings.borderButtonColor == '#f18700') {
  typoSettings.borderButtonColorClass = 'orange';
}
if (typoSettings.borderButtonColor == '#009ddf') {
  typoSettings.borderButtonColorClass = 'lightblue';
}


osmMapApp.provide('Loading', ref(true));
osmMapApp.provide('tmpLocations', ref([]));
osmMapApp.provide('baseInterfaceURL', baseInterfaceURL);
osmMapApp.provide('imageBaseURL', imageBaseURL);
osmMapApp.provide('proxyURL', proxyURL);
osmMapApp.provide('TypoSettings', typoSettings);
osmMapApp.provide('Center', ref({
  isSet: false,
  displayName: '',
  longitude: startLongitude,
  latitude: startLatitude,
  zoomLevel: startZoomLevel,
  resetCenter: function () {
    this.longitude = startLongitude;
    this.latitude = startLatitude;
    this.displayName = '';
    this.isSet = true;
    this.zoomLevel = startZoomLevel;
  }
}))

osmMapApp.provide('Pin', ref({
  isSet: false,
  displayName: '',
  longitude: 10.451526,
  latitude: 51.165691,
  resetCenter: function () {
    this.longitude = 10.451526;
    this.latitude = 51.165691;
    this.displayName = '';
    this.isSet = false;
  }
}));
osmMapApp.mount('#osmMapApp')

/*
if (appContainer.dataset.appid == 'FWD') {
  const osmFwdMapApp = createApp(OsmFwdMapApp)
  osmFwdMapApp.use(OpenLayersMap)
  osmFwdMapApp.provide('Locations', ref([]));
  osmFwdMapApp.provide('Location', ref(Object));
  osmFwdMapApp.provide('Navigation', ref(5));
  osmFwdMapApp.provide('Loading', ref(true));
  osmFwdMapApp.provide('tmpLocations', ref([]));
  osmFwdMapApp.provide('baseInterfaceURL', baseInterfaceURL)
  osmFwdMapApp.provide('proxyURL', proxyURL)
  osmFwdMapApp.mount('#osmFwdMapApp')
}
*/


/**
 * list app
 */
const osmListApp = createApp(OsmListApp);
osmListApp.provide('baseInterfaceURL', baseInterfaceURL)
osmListApp.provide('imageBaseURL', imageBaseURL)
osmListApp.provide('proxyURL', proxyURL)
osmListApp.provide('Locations', ref([]));
osmListApp.provide('tmpLocations', ref([]));
osmListApp.provide('Tags', ref([]));
osmListApp.provide('Categories', ref([]));
osmListApp.provide('Loading', ref(true));
osmListApp.provide('Navigation',)
osmListApp.provide('selectedCategories', ref([]));
osmListApp.provide('selectedDistance', ref(5));
osmListApp.provide('TypoSettings', typoSettings);
osmListApp.provide('selectedTag', ref(String('Alle')));
osmListApp.provide('Pin', ref({
  isSet: false,
  displayName: '',
  longitude: 10.451526,
  latitude: 51.165691,
  resetCenter: function () {
    this.longitude = 10.451526;
    this.latitude = 51.165691;
    this.displayName = '';
    this.isSet = false;
  }
}));
osmListApp.mount('#osmListApp');