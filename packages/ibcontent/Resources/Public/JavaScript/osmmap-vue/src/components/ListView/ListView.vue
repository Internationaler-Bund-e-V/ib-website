<template>
  <div
    class="row ib-content-module ib-tiles ib-background-white locationViewSearchContainer"
    :class="searchBarClassName"
  >
    <div class="filterRow">
      <CategoryMobileFilter
        v-if="TypoSettings.showCategoryFilter == 1 && usePreFilterCategories == 0"
      />
      <TagsMobileFilter
        v-if="TypoSettings.showTagFilter == 1 && usePreFilterCategories == 0"
      />
      <PerimeterFilter v-if="Pin.isSet" :selectedDistance="selectedDistance" />
    </div>

    <div class="listViewInputContainer">
      <input
        class="listViewInput"
        placeholder="Stadt/PLZ/Bundesland..."
        v-model="searchLocationTerm"
        @keyup.enter="searchLocation()"
      />
      <i
        class="fas fa-search searchLocations"
        title="Suche ausführen"
        @click="searchLocation()"
      ></i>
      <i
        class="fas fa-undo resetLocations"
        title="Suche zurücksetzen"
        @click="resetLocations"
      ></i>
    </div>

    <div class="listViewResultContainer">
      <div>Ergebnisse: {{ tmpLocations.length }}</div>
    </div>
    <div class="searchTextContainer">
      <input
        class="searchTextInput"
        placeholder="Name der Einrichtung..."
        v-model="searchTextTerm"
        @keyup="searchText()"
      />
    </div>
  </div>

  <Navbar :mobile="true" />
  <div class="row ib-content-module ib-tiles ib-background-white listViewContainer">
    <div class="sliderLoading" v-if="Loading"></div>
    <div
      v-if="!Loading"
      v-for="(location, index) in tmpLocations"
      :key="index"
      class="columns small-12 medium-4 large-3 listViewItem"
    >
      <ListViewItem :location="location" :showDistance="showDistance" />
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
import useFetchLocations, { Status } from "../../composable/fetch";
import ListViewItem from "./ListViewItem.vue";
import CategoryMobileFilter from "../Filter/CategoryMobileFilter.vue";
import TagsMobileFilter from "../Filter/TagsMobileFilter.vue";
import CategoryFilter from "../Filter/CategoryFilter.vue";
import PerimeterFilter from "../Filter/perimeterFilter.vue";
import SelectGeoLocation from "../Partials/SelectGeoLocation.vue";
import Navbar from "../Navbar.vue";
import { generateTags } from "../../composable/getTags";
import { generateCategories } from "../../composable/getCategories";

export const MapStatus = {
  INTITAL: "INITIAL",
  RUNNING: "RUNNING",
};

export default {
  name: "ListMap",
  components: {
    ListViewItem,
    CategoryMobileFilter,
    TagsMobileFilter,
    CategoryFilter,
    PerimeterFilter,
    SelectGeoLocation,
    Navbar,
  },
  props: {
    showCategoryFilter: Boolean,
    navigation: Number,
  },
  setup(props) {
    let mapStatus = ref(MapStatus.INTITAL);
    let locations = inject("Locations");
    let TypoSettings = inject("TypoSettings");
    let Tags = inject("Tags");
    let Categories = inject("Categories");
    let Pin = inject("Pin");
    let requestURL = ref("");
    let Navigation = ref(props.navigation);
    let tmpLocations = inject("tmpLocations");
    let searchBarClassName = ref("");
    let proxyURL = inject("proxyURL");
    let baseInterfaceURL = inject("baseInterfaceURL");
    let geoData = ref([]);
    let selectedDistance = inject("selectedDistance");
    let selectedCategories = inject("selectedCategories");
    let selectedTag = ref(inject("selectedTag"));
    let showDistance = ref(false);
    let resultInfo = ref(false);
    let pristineLocations = ref([]);
    let Loading = inject("Loading");
    let newGeoSearch = ref(false);
    let geoLocations = ref([]);
    let selectedGeoLocationIndex = ref(-1);

    const usePreFilterCategories = TypoSettings.usePreFilterCategory;
    const preFilterCategories = TypoSettings.preFilterCategoryIDs.split(",");

    const searchLocationTerm = ref("");
    const searchTextTerm = ref("");

    /**
     * check for preFilter Settings
     */
    if (usePreFilterCategories == 1) {
      selectedCategories.value = preFilterCategories;
    }

    /**
     * check for transparent background and adjust styles
     */

    if (TypoSettings.searchBarBackgroundColor == "transparent") {
      searchBarClassName.value = "transparent";
    }
    if (TypoSettings.searchBarBackgroundColor == "#f18700") {
      searchBarClassName.value = "orange";
    }
    if (TypoSettings.searchBarBackgroundColor == "#005590") {
      searchBarClassName.value = "blue";
    }

    //see https://nominatim.openstreetmap.org, https://nominatim.org/release-docs/latest/api/Search/
    const searchAPI = proxyURL + "?baseurl=" + baseInterfaceURL + "&geocode=";

    const getLocations = async (navid) => {
      //resetLocations();
      // /interfaces/getLocationsForMapsByNavigation/nav_id:5
      // /interfaces/requestLocationsByRadius/radius:1000/lat:49.9928084/long:8.4875016/navID:5
      //reset search text
      searchTextTerm.value = "";

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

    const searchLocation = async () => {
      tmpLocations.value = [];
      searchTextTerm.value = "";
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
     * search text
     */
    const searchText = () => {
      tmpLocations.value = locations.value.filter((location) => {
        return (
          location.Location.city
            .toLowerCase()
            .indexOf(searchTextTerm.value.toLowerCase()) != -1 ||
          location.Location.name
            .toLowerCase()
            .indexOf(searchTextTerm.value.toLowerCase()) != -1
        );
      });
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
     * get and set distinct tags only if tags actviated
     */
    const getCategories = () => {
      if (TypoSettings.showCategoryFilter == 1 && mapStatus.value == MapStatus.INTITAL) {
        generateCategories(Categories, locations, Navigation.value);
      }
    };

    /**
     * set geoLocation after receiving event/geo data
     */
    const setGeoLocation = (geLocationIndex) => {
      selectedGeoLocationIndex.value = geLocationIndex;
    };

    const checkPristineCopy = (data) => {
      if (pristineLocations.value.length == 0) {
        pristineLocations.value = data;
      }
    };

    const resetLocations = () => {
      mapStatus.value = MapStatus.INTITAL;
      showDistance.value = false;
      Loading.value = true;
      geoLocations.value = [];
      searchLocationTerm.value = "";
      searchTextTerm.value = "";
      selectedDistance.value = 50;
      Pin.value.resetCenter();
      if (usePreFilterCategories == 1) {
        selectedCategories.value = preFilterCategories;
      } else {
        selectedCategories.value = [];
      }

      selectedTag.value = "Alle";
      getLocations(Navigation.value);
    };

    const updateTag = () => {
      tmpLocations.value = [];
      setTimeout(() => {
        if (selectedTag.value.toLowerCase() != "alle") {
          tmpLocations.value = locations.value.filter((location) => {
            return (
              location.Location.tags
                .toLowerCase()
                .indexOf("-" + selectedTag.value.toLowerCase() + "-") != -1
            );
          });
        } else {
          tmpLocations.value = locations.value;
        }
      }, 50);
    };

    watch(selectedCategories, () => {
      if (mapStatus.value != MapStatus.INTITAL) {
        Loading.value = true;
        getLocations(Navigation.value);
      }
    });

    watch(selectedDistance, () => {
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

    onMounted(() => {
      getLocations(Navigation.value);
    });

    return {
      searchBarClassName,
      tmpLocations,
      searchLocationTerm,
      searchText,
      searchTextTerm,
      setGeoLocation,
      showDistance,
      selectedDistance,
      resetLocations,
      selectedGeoLocationIndex,
      searchLocation,
      Loading,
      resultInfo,
      Pin,
      geoLocations,
      Status,
      TypoSettings,
      usePreFilterCategories,
    };
  },
};
</script>

<style scoped lang="scss">
.osmMap {
  position: relative;
}

#osmMapContainer {
  border: none !important;
  border-radius: 5px;
  display: flex;
  overflow: hidden;

  .osmMapMain {
    display: flex;
    flex: 1;
  }
}

.locationViewSearchContainer {
  background: #009fe3;
  border-radius: 5px;
  display: flex;
  flex-wrap: wrap;
  left: 0;
  right: 0;
  margin: 0 auto;
  color: white;
  align-items: center;
  padding-left: 15px;
  padding-right: 15px;

  &.orange {
    background: #f18700;
  }

  &.blue {
    background: #005590;

    .resetLocations,
    .searchLocations {
      &:hover {
        color: #fff;
      }
    }
  }

  &.transparent {
    background: transparent;
    color: #666;

    label {
      color: #666;
    }

    .listViewInputContainer {
      input {
        border: 1px solid #666;
      }

      .resetLocations,
      .searchLocations {
        color: #666;

        &:hover {
          color: #005590;
        }
      }
    }
  }

  .listViewResultContainer {
    width: 100%;
    padding-top: 0.5rem;
  }

  .listViewInputContainer {
    display: flex;
    width: 100%;
    flex-wrap: wrap;
    align-items: center;
  }

  .searchTextContainer {
    width: 100%;
    margin-top: 1rem;
    input {
      border: 1px solid #666;
      width: 75%;
      font-size: 16px;
      border: white;
      border-radius: 5px;
      color: black;
      padding: 10px;
      max-width: 480px;
    }
  }

  label {
    color: white;
    padding-right: 1rem;
    width: 100%;
  }

  .listViewFederalStateFilter {
    color: white;
    display: flex;
    flex-direction: column;
    padding-bottom: 0.5rem;

    select {
      margin: 0;
    }
  }

  .listViewInput {
    width: 75%;
    font-size: 16px;
    border: white;
    border-radius: 5px;
    color: black;
    padding: 10px;
    max-width: 480px;
  }

  .resetLocations,
  .searchLocations {
    cursor: pointer;
    margin-left: 1rem;
    font-size: 1.5rem;
    transition: transform 0.2s;
    color: white;
    transform: scale(1);

    &:hover {
      color: #005590;
      transform: scale(1.1);
    }

    &:focus,
    &:visited {
      color: white;
      transform: scale(1);
    }
  }

  .filterRow,
  .searchRow {
    display: flex;
    flex-direction: column;
    width: 100%;
    align-items: center;
  }
  .filterRow {
    padding-bottom: 1rem;
  }
  .listViewResultRow {
    display: flex;
    flex-wrap: wrap;
    left: 0;
    right: 0;
    margin: 0 auto;
  }
}

.listViewContainer {
  display: flex;
  flex-wrap: wrap;
  left: 0;
  right: 0;
  margin: 0 auto;

  &:before {
    display: none;
  }

  .listViewItem {
    margin-bottom: 1rem;
    box-sizing: border-box;
  }
}

@media (min-width: 1024px) {
  .locationViewSearchContainer {
    justify-content: flex-start;
    align-items: baseline;
    .filterRow {
      flex-direction: row;
      .osmMapCategoriesMobileFilter,
      .osmMapTagsMobileFilter {
        width: 33%;
        height: 90px;
      }
    }

    .listViewResultContainer {
      width: unset;
      padding: unset;
    }

    .listViewInputContainer {
      display: flex;
      width: 50%;
      flex-wrap: nowrap;
      padding-right: 1rem;
    }

    label {
      color: white;
      padding-right: 1rem;
      width: unset;
    }

    .listViewFederalStateFilter {
      color: white;
      display: flex;
      flex-direction: row;
      align-items: center;
      padding-right: 1rem;

      select {
        margin: 0;
      }
    }

    .listViewInput {
      width: 100%;
      font-size: 18px;
      border: white;
      border-radius: 5px;
      color: black;
    }

    .resetLocations,
    .searchLocations {
      cursor: pointer;
      margin-left: 1rem;
      font-size: 1.5rem;
      transition: transform 0.2s;

      &:hover {
        color: #005590;
        transform: scale(1.1);
      }
    }
  }
}
</style>
<!--
<style lang="scss">
.ib-osmmap {
  border: none !important;
}
</style>
-->
