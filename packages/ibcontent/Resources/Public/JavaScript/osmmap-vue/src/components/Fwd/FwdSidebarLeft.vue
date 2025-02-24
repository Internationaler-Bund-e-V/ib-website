<template>
  <div class="osmMapSidebarLeft">
    <div class="osmMapSidebarLeftContainer">
      <div class="tabs">
        <div :class="{ activeTab: tab == 'search' }" @click="tab = 'search'">
          Suche
        </div>
        <div :class="{ activeTab: tab == 'list' }" @click="tab = 'list'">
          Liste
        </div>
      </div>
      <div class="tabContent">
        <div class="tabFilter" v-if="tab == 'search'">
          <FederalStateFilter />
          <ServiceFilter />
          <JobtagFilter />
        </div>
        <div class="tabList" v-if="tab == 'list'">
          <transition>
            <div class="sidebarLeftMain">
              <div class="osmSearchInputContainer">
                <input
                  class="osmSearchInput"
                  placeholder="Stadt/PLZ..."
                  v-model="searchLocationTerm"
                  @input="searchLocation()"
                />
              </div>
              <div class="osmResultContainer">
                <div
                  v-for="location in tmpLocations"
                  v-bind:key="location.Location.id"
                >
                  <LocationListItem :Location="location.Location" />
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { onMounted, ref, inject } from "vue";
import debounce from "lodash/debounce";
import LocationListItem from "../LocationListItem.vue";
import FederalStateFilter from "./FederalStateFilter.vue";
import ServiceFilter from "../Fwd/ServiceFilter.vue";
import JobtagFilter from "../Fwd/JobtagFilter.vue";

export default {
  name: "SidebarLeft",
  components: {
    LocationListItem,
    FederalStateFilter,
    ServiceFilter,
    JobtagFilter,
  },

  setup() {
    let locations = inject("Locations");
    let Location = inject("Location");
    let tmpLocations = ref(inject("tmpLocations"));
    let Loading = inject("Loading");
    let locationsLoaded = ref(false);
    let proxyURL = inject("proxyURL");
    let tab = ref("search");
    const searchLocationTerm = ref("");

    const getLocations = async () => {
      resetLocations();
      //https://redaktionstool.ddev.site/interfaces/getLocationsForMapsByNavigation/nav_id:5
      fetch(proxyURL + '?fwd=true')
        .then((response) => response.json())
        .then((data) => (locations.value = data))
        .then((data) => (tmpLocations.value = data))
        .then(() => (Loading.value = false))
        .then(() => (locationsLoaded.value = true));
    };

    const searchLocation = debounce(() => {
      tmpLocations.value = locations.value.filter((location) => {
        return (
          location.Location.city
            .toLowerCase()
            .indexOf(searchLocationTerm.value.toLowerCase()) != -1
        );
      });
    }, 1150);

    const resetLocations = () => {
      Loading.value = true;
      searchLocationTerm.value = "";
      Location.value.id = 0;
    };

    onMounted(getLocations());

    return {
      locations,
      tmpLocations,
      searchLocation,
      searchLocationTerm,
      tab,
    };
  },
};
</script>
<style scoped lang="scss">
.tabs {
  display: flex;
  padding: 0;
  justify-content: space-between;
  align-items: center;
  > div {
    padding: 0.5rem 0;
    flex: 1;
    display: flex;
    justify-content: center;
    cursor: pointer;
  }
  .activeTab {
    background: #005590;
    color: white;
  }
}
.tabContent {
  overflow: scroll;
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
  background: white;
  height: 100%;
  .osmSearchInputContainer {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #005590;
    padding: 0.5rem;
  }
  .searchButton {
    font-size: 24px;
    padding: 0 0.5rem;
    cursor: pointer;
  }
  .sidebarLeftTop {
    background: white;
    justify-content: space-between;
    display: flex;
    align-items: center;
    border: 1px solid #005590;
    border-radius: 5px;
    box-shadow: 0 5px 10px rgb(2 2 2 / 20%);
  }
  .sidebarLeftMain {
    background: white;
    border: 1px solid #005590;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-radius: 5px;
    box-shadow: 0 5px 10px rgb(2 2 2 / 20%);
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
