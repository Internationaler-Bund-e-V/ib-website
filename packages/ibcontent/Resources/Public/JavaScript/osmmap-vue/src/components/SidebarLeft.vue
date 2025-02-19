<template>
  <div class="osmMapSidebarLeft">
    <div class="osmMapSidebarLeftContainer">
      <div class="sidebarLeftTop" :class="TypoSettings.borderButtonColorClass">
        <!-- navigation filter is disabled in initial release-->
        <NavigationFilter v-if="false" />
        <div class="featureBar">
          <i
            class="fas fa-ellipsis-v searchButton"
            title="Suchefeld/Ergebnisse ein/ausblenden"
            :class="{ activeSearch: toggle }"
            @click="toggle = !toggle"
          ></i>
          <i
            class="fas fa-undo resetMap"
            title="Suche zurücksetzen"
            @click="resetLocations"
          ></i>
        </div>
      </div>

      <transition>
        <div
          class="sidebarLeftMain"
          :class="TypoSettings.borderButtonColorClass"
          v-if="toggle"
        >
          <div
            class="osmSearchInputContainer"
            :class="TypoSettings.borderButtonColorClass"
          >
            <div class="osmInputWrap">
              <input
                class="osmSearchInput"
                placeholder="Stadt/PLZ/Bundesland..."
                v-model="searchLocationTerm"
                @keyup.enter="
                  resultInfo = false;
                  searchLocation();
                "
              />
            </div>

            <div class="perimeterContainer" v-if="Pin.isSet">
              <div class="perimeterLabel"><label>Entfernung/Umkreis:</label></div>
              <div class="perimeterSelect">
                <select v-model="selectedDistance">
                  <option value="5">5 Km</option>
                  <option value="10">10 Km</option>
                  <option value="25">25 Km</option>
                  <option value="50">50 Km</option>
                  <option value="100">100 Km</option>
                  <option value="150">150 Km</option>
                  <option value="200">200 Km</option>
                </select>
              </div>
            </div>
            <div
              @click="
                resultInfo = false;
                searchLocation();
              "
              class="ibCustomButton darkblue"
              :class="TypoSettings.borderButtonColorClass"
            >
              Suchen
            </div>
            <div class="resultText" :class="{ info: resultInfo }">
              Es wurden {{ tmpLocations.length }} Ergebnisse gefunden:
            </div>
          </div>
          <div class="osmResultContainer">
            <div v-for="location in tmpLocations" v-bind:key="location.Location.id">
              <LocationListItem
                :Location="location.Location"
                :showDistance="showDistance"
                @locationSelected="setLocation(location)"
              />
            </div>
          </div>
        </div>
      </transition>
    </div>

    <SelectGeoLocation
      v-if="geoLocations.length > 1 && selectedGeoLocationIndex == -1"
      :GeoLocations="geoLocations"
      @geoLocationSelected="setGeoLocation"
    />
  </div>
</template>
<script>
import { onMounted, ref, inject, watch } from "vue";
import LocationListItem from "./LocationListItem.vue";
import NavigationFilter from "./Filter/NavigationFilter.vue";
import SelectGeoLocation from "./Partials/SelectGeoLocation.vue";
import { generateTags } from "../composable/getTags";
import { generateCategories } from "../composable/getCategories";

export const MapStatus = {
  INTITAL: "INITIAL",
  RUNNING: "RUNNING",
};

export default {
  name: "SidebarLeft",
  components: {
    LocationListItem,
    NavigationFilter,
    SelectGeoLocation,
  },

  setup() {
    let mapStatus = ref(MapStatus.INTITAL);
    let locations = inject("Locations");
    let Clusters = inject("Clusters");
    let Cluster = inject("Cluster");
    let Tags = inject("Tags");
    let Categories = inject("Categories");
    let Location = inject("Location");
    let Center = inject("Center");
    let Pin = inject("Pin");
    let tmpLocations = inject("tmpLocations");
    let pristineLocations = ref([]);
    let Navigation = inject("Navigation");
    let Loading = inject("Loading");
    let selectedCategories = inject("selectedCategories");
    let selectedTag = ref(inject("selectedTag"));
    let TypoSettings = inject("TypoSettings");
    let resultInfo = ref(false);
    let appState = ref("");
    let baseInterfaceURL = inject("baseInterfaceURL");
    let requestURL = ref("");
    let proxyURL = inject("proxyURL");
    let toggle = ref(true);
    let geoData = ref([]);
    let selectedDistance = inject("selectedDistance");
    let showDistance = ref(false);
    let newGeoSearch = ref(false);
    let geoLocations = ref([]);
    let selectedGeoLocationIndex = ref(-1);
    const searchLocationTerm = ref("");

    const usePreFilterCategories = TypoSettings.usePreFilterCategory;
    const preFilterCategories = TypoSettings.preFilterCategoryIDs.split(",");

    /**
     * check for preFilter Settings
     */
    if (usePreFilterCategories == 1) {
      selectedCategories.value = preFilterCategories;
    }

    //see https://nominatim.openstreetmap.org, https://nominatim.org/release-docs/latest/api/Search/
    const searchAPI = proxyURL + "?baseurl=" + baseInterfaceURL + "&geocode=";

    //"https://nominatim.openstreetmap.org/search?countrycodes=de&format=geojson&q=";
    //see https://photon.komoot.io/
    //const searchAPI = "https://photon.komoot.io/api/?q=";

    const getLocations = async (navid) => {
      //resetLocations();
      //https://redaktionstool.ddev.site/interfaces/getLocationsForMapsByNavigation/nav_id:5
      //https://redaktionstool.ddev.site/interfaces/requestLocationsByRadius/radius:1000/lat:49.9928084/long:8.4875016/navID:5

      if (selectedDistance.value !== 0 && Pin.value.isSet) {
        requestURL.value =
          proxyURL +
          "?baseurl=" +
          baseInterfaceURL +
          "&navid=" +
          navid +
          "&radius=" +
          selectedDistance.value +
          "&lat=" +
          Pin.value.latitude +
          "&long=" +
          Pin.value.longitude +
          "&categories=" +
          (selectedCategories.value == null ? "" : selectedCategories.value.toString());
      } else {
        //selectedDistance.value = 0;
        requestURL.value =
          proxyURL +
          "?baseurl=" +
          baseInterfaceURL +
          "&navid=" +
          navid +
          "&categories=" +
          (selectedCategories.value == null ? "" : selectedCategories.value.toString());
      }

      fetch(requestURL.value)
        .then((response) => response.json())
        .then((data) => (locations.value = data))
        .then((data) => (tmpLocations.value = data))
        .then((data) => checkPristineCopy(data))
        .then(() => (Loading.value = false))
        .then(() => getTags())
        .then(() => getCategories())
        .then(() => updateTag())
        .then(() => (mapStatus.value = MapStatus.RUNNING));
    };

    const checkPristineCopy = (data) => {
      if (pristineLocations.value.length == 0) {
        pristineLocations.value = data;
      }
    };

    const searchLocation = async () => {
      tmpLocations.value = [];
      getGeoData(searchLocationTerm);
    };

    const getGeoData = async (address) => {
      selectedGeoLocationIndex.value = -1;
      fetch(searchAPI + address.value)
        .then((response) => response.json())
        .then((data) => (geoData.value = data))
        .then(() => {
          geoLocations.value = geoData.value.features;
          //implement check/select for multiple results
          if (geoLocations.value.length == 1) {
            selectedGeoLocationIndex.value = 0;
          }
        });
    };


    /**
     * set location after receiving event/location from list
     */
    const setLocation = (location) => {
      Location.value = location.Location;
      Center.value.longitude = Location.value.longitude;
      Center.value.latitude = Location.value.latitude;
      Object.values(Clusters.value).forEach((cluster) => {
        cluster.Locations.forEach((location) => {
          if (location.id == Location.value.id) {
            Cluster.value = cluster;
          }
        });
      });
    };

    /**
     * set geoLocation after receiving event/geo data
     */
    const setGeoLocation = (geLocationIndex) => {
      selectedGeoLocationIndex.value = geLocationIndex;
    };

    /**
     * get and set distinct tags only if tags actviated
     */
    const getTags = () => {
      if (TypoSettings.showTagFilter == 1) {
        generateTags(Tags, locations);
      }
    };

    /**
     * get and set distinct categories only if categories actviated
     */
    const getCategories = () => {
      if (TypoSettings.showCategoryFilter == 1 && mapStatus.value == MapStatus.INTITAL) {
        generateCategories(Categories, locations, Navigation.value);
        mapStatus.value = MapStatus.RUNNING;
      }
    };

    /**
     * run tag filter
     */
    const updateTag = () => {
      tmpLocations.value = [];
      setTimeout(() => {
        if (typeof selectedTag.value !== 'string') {
          tmpLocations.value = locations.value.filter((location) => {
            return (
              location.Location.tags
                .toLowerCase()
                .indexOf("-" + selectedTag.value + "-") != -1
            );
          });
        } else {
          tmpLocations.value = locations.value;
        }
      }, 50);
    };

    const resetLocations = () => {
      mapStatus.value = MapStatus.INTITAL;
      showDistance.value = false;
      Loading.value = true;
      geoLocations.value = [];
      searchLocationTerm.value = "";
      selectedDistance.value = 50;
      Location.value = new Object();
      Cluster.value = new Object();
      Clusters.value = new Object();
      Center.value.resetCenter();
      Pin.value.resetCenter();
      if (usePreFilterCategories == 1) {
        selectedCategories.value = preFilterCategories;
      } else {
        selectedCategories.value = [];
      }
      selectedTag.value = "Alle";
      getLocations(Navigation.value);
    };

    watch(Navigation, () => {
      resetLocations();
    });

    watch(selectedGeoLocationIndex, () => {
      if (selectedGeoLocationIndex.value != -1) {
        var tempGeoLocation = geoLocations.value[selectedGeoLocationIndex.value];

        if (
          tempGeoLocation.properties.type == "postal_code" ||
          tempGeoLocation.properties.type == "postcode" ||
          tempGeoLocation.properties.type == "administrative" ||
          tempGeoLocation.properties.type == "land_area" ||
          tempGeoLocation.properties.type == "unesco world heritage" ||
          tempGeoLocation.properties.type == "stop" ||
          tempGeoLocation.properties.type == "halt" ||
          tempGeoLocation.properties.type == "bus_stop" ||
          tempGeoLocation.properties.type == "village"
        ) {
          //check state and adjust distance
          newGeoSearch.value = true;
          if (
            tempGeoLocation.properties.addresstype == "state" ||
            tempGeoLocation.properties.addresstype == "land_area"
          ) {
            selectedDistance.value = 150;
          } else {
            selectedDistance.value = 50;
          }
          Pin.value.longitude = tempGeoLocation.geometry.coordinates[0];
          Pin.value.latitude = tempGeoLocation.geometry.coordinates[1];
          Pin.value.isSet = true;
          Pin.value.displayName = tempGeoLocation.properties.display_name;
          if (selectedDistance.value !== 0 && Pin.value.isSet) {
            getLocations(Navigation.value);
            showDistance.value = true;
          }
        } else {
          Pin.value.isSet = false;
          tmpLocations.value = [];
          showDistance.value = false;
        }
        resultInfo.value = true;
      }
    });

    watch(selectedCategories, () => {
      if (mapStatus.value != MapStatus.INTITAL) {
        getLocations(Navigation.value);
      }
    });

    watch(selectedDistance, () => {
      if (mapStatus.value != MapStatus.INTITAL) {
        getLocations(Navigation.value);
      }
      if (newGeoSearch.value == false) {
        getLocations(Navigation.value);
      }
      newGeoSearch.value = false;
    });

    watch(selectedTag, () => {
      if (mapStatus.value != MapStatus.INTITAL) {
        updateTag();
      }
    });

    onMounted(() => {
      getLocations(Navigation.value);
    });

    return {
      resultInfo,
      locations,
      tmpLocations,
      searchLocation,
      searchLocationTerm,
      toggle,
      selectedDistance,
      showDistance,
      selectedGeoLocationIndex,
      Center,
      Pin,
      setLocation,
      resetLocations,
      setGeoLocation,
      geoLocations,
      TypoSettings,
      usePreFilterCategories,
    };
  },
};
</script>

<style scoped lang="scss">
.resultText.info {
  -webkit-animation: color_change 1s 1 alternate;
  -moz-animation: color_change 1s 1 alternate;
  -ms-animation: color_change 1s 1 alternate;
  -o-animation: color_change 1s 1 alternate;
  animation: color_change 1s 1 alternate;
}

@keyframes color_change {
  from {
    // background-color: rgb(255, 255, 255);

    font-weight: inherit;
  }

  to {
    font-weight: bold;
    // background-color: rgb(144, 138, 138);
  }
}

.ibCustomButton {
  cursor: pointer;
  width: 100%;
  text-align: center;

  &.orange {
    background: #f18700;
  }

  &.lightblue {
    background: #009ddf;
  }
}

.osmMapSidebarLeft {
  overflow: hidden;
  display: flex;
  flex-direction: column;
  position: absolute;
  top: 10px;
  left: 10px;
  bottom: 10px;
  z-index: 1;
  width: 20%;
}

.osmMapSidebarLeftContainer {
  overflow: hidden;
  display: flex;
  flex-direction: column;

  .osmSearchInputContainer {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #005590;
    padding: 0.5rem;
    flex-direction: column;

    &.orange {
      border-bottom: 1px solid #f18700;
    }

    &.lightblue {
      border-bottom: 1px solid #009ddf;
    }

    .osmInputWrap {
      width: 100%;

      input {
        width: 100%;
      }
    }

    .perimeterContainer {
      display: flex;
      width: 100%;
      align-items: center;
      flex-direction: column;
      align-items: center;

      .perimeterLabel {
        width: 100%;
      }

      .perimeterSelect {
        width: 100%;

        select {
          width: 100%;
        }
      }

      select {
        margin: 0;
      }
    }
  }

  .searchButton {
    font-size: 18px;
    padding: 0 0.5rem;
    cursor: pointer;

    &.activeSearch {
      color: #005590;
    }
  }

  .setCenter {
    font-size: 18px;
    padding: 0 0.5rem;

    &.activeCenter {
      color: red;
    }
  }

  .resetMap {
    cursor: pointer;
    font-size: 18px;
    padding: 0 0.5rem;

    &:hover {
      color: #005590;
    }
  }

  .sidebarLeftTop {
    background: white;
    justify-content: space-between;
    display: flex;
    flex-direction: column;
    border: 1px solid #005590;
    border-radius: 5px;
    box-shadow: 0 5px 10px rgb(2 2 2 / 20%);

    &.orange {
      border: 1px solid #f18700;

      .activeSearch {
        color: #f18700;
      }

      .resetMap:hover {
        color: #f18700;
      }
    }

    &.lightblue {
      border: 1px solid #009ddf;

      .activeSearch {
        color: #009ddf;
      }

      .resetMap:hover {
        color: #009ddf;
      }
    }

    .featureBar {
      display: flex;
      padding: 0.5rem;
    }
  }

  .sidebarLeftMain {
    background: white;
    border: 1px solid #005590;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-radius: 5px;
    box-shadow: 0 5px 10px rgb(2 2 2 / 20%);

    &.orange {
      border: 1px solid #f18700;
    }

    &.lightblue {
      border: 1px solid #009ddf;
    }
  }

  .osmResultContainer {
    overflow: scroll;
    background: white;
  }
}

.v-enter-active,
.v-leave-active {
  transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}
</style>
