<template>
  <div
    class="osmMapSidebarRight"
    :class="colorThemeClass"
    v-if="Location.id > 0 || Cluster.Locations != undefined"
  >
    <Transition name="slide-fade">
      <div v-if="singleView && Location.id > 0">
        <div class="osmMapSidebarRightContainer">
          <div
            v-if="Cluster.Locations.length > 1"
            class="multiLocationsBack"
            @click="switchBack"
          >
            <i class="fas fa-chevron-left"></i> Zurück
          </div>
          <div class="locationImage">
            <img v-if="Location.image" :src="imageBaseURL + Location.image" />
          </div>
          <div class="locationName">
            <h2>
              {{ Location.name }}
            </h2>
            <div
              v-if="TypoSettings.showTagFilter == '1'"
              v-html="getTagFilterHeadline()"
            ></div>
            <div
              v-if="TypoSettings.showTagFilter == '1'"
              v-html="generateTagDots(Location.tags)"
            ></div>
          </div>
          <div class="infoContainer">
            <div>
              <label>Kontakt:</label>
            </div>
            <div class="locationStreet">
              {{ Location.street }}
            </div>
            <div class="locationZipStreet">{{ Location.plz }} {{ Location.city }}</div>
            <div class="locationPhone">Tel.: {{ Location.phone_int }}</div>

            <div v-if="Location.fax" class="locationPhone">Fax: {{ Location.fax }}</div>
          </div>
          <div class="locationLink">
            <a
              class="ibCustomButton darkblue"
              :class="colorThemeClass"
              :href="'/standort/' + Location.id"
              target="_blank"
              >Weitere Standortinformationen hier aufrufen!</a
            >
          </div>
        </div>
      </div>
    </Transition>
    <!-- List View -->
    <Transition name="slide-fade">
      <div v-if="listView">
        <div class="listViewHeader">
          <h3>Bitte wählen Sie eine Einrichtung:</h3>
        </div>
        <div v-for="location in Cluster.Locations">
          <SidebarRightListItem :Location="location" @locationSelected="setLocation" />
        </div>
      </div>
    </Transition>
  </div>
</template>
<script>
import { ref, inject, watch } from "vue";
import { generateTagDots } from "../../composable/generateTagDots";
import { getTagFilterHeadline } from "../../composable/getTagFilterHeadline";
import SidebarRightListItem from "../Partials/SidebarRightListItem.vue";
export default {
  name: "SidebarRight",
  components: {
    SidebarRightListItem,
  },

  setup() {
    let Cluster = ref(inject("Cluster"));
    //let Location = ref(Object);
    let Location = inject("Location");
    let selectedLocation = inject("Location");
    let imageBaseURL = inject("imageBaseURL");
    let singleView = ref(false);
    let listView = ref(false);
    let TypoSettings = inject("TypoSettings");
    let colorThemeClass = ref("");
    let multiLocations = ref(false);

    if (TypoSettings.borderButtonColor == "#f18700") {
      colorThemeClass.value = "orange";
    }
    if (TypoSettings.borderButtonColor == "#009ddf") {
      colorThemeClass.value = "lightblue";
    }

    const setLocation = (location) => {
      singleView.value = true;
      listView.value = false;
      multiLocations.value = true;
      Location.value = location;
    };

    const switchBack = () => {
      singleView.value = false;
      listView.value = true;
    };

    watch(selectedLocation, () => {
      setLocation(selectedLocation.value);
    });

    watch(Cluster, () => {
      if (Cluster.value.Locations != undefined) {
        singleView.value = false;
        listView.value = false;
        multiLocations.value = false;
        if (Cluster.value.Locations.length == 1) {
          Location.value = Cluster.value.Locations[0];
          singleView.value = true;
        }
        if (Cluster.value.Locations.length > 1) {
          listView.value = true;
        }
      }
    });

    return {
      Location,
      Cluster,
      singleView,
      listView,
      imageBaseURL,
      colorThemeClass,
      setLocation,
      selectedLocation,
      generateTagDots,
      getTagFilterHeadline,
      TypoSettings,
      switchBack,
      multiLocations,
    };
  },
};
</script>
<style scoped lang="scss">
.osmMapSidebarRight {
  position: absolute;
  top: 0;
  right: 10px;
  top: 10px;
  background: white;
  width: 250px;
  border: 1px solid #005590;
  border-radius: 5px;
  box-shadow: 0 5px 10px rgb(2 2 2 / 20%);
  overflow: hidden;
  max-height: 550px;
  overflow-y: scroll;

  &.orange {
    border: 1px solid #f18700;
  }

  &.lightblue {
    border: 1px solid #009ddf;
  }
}

.listViewHeader {
  padding: 15px 15px 0 15px;
  border-bottom: 1px solid lightgray;
}

.osmMapSidebarRightContainer {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  height: 100%;

  .multiLocationsBack {
    padding: 1rem;
    border: 1px solid lightgray;
    color: #005590;
    &:hover {
      background: lightgray;
      cursor: pointer;
    }
  }

  .tagItemIcon {
    width: 10px;
    height: 10px;
    display: inline-block;
    margin: 1px;
    border-radius: 50%;
    margin-right: 5px;
  }

  .locationName {
    h2 {
      font-size: 1rem;
      padding: 0.5rem 0;
    }
  }

  .ibCustomButton {
    &.orange {
      background: #f18700;
    }

    &.lightblue {
      background: #009ddf;
    }
  }

  .locationLink {
    display: flex;
    align-self: center;
    margin-top: auto;
    text-align: center;

    a {
      transition: all 0.3s;
      font-weight: bold;
    }

    a:hover {
      box-shadow: 0 10px 20px -10px rgb(88 49 0 / 50%);
    }
  }

  .infoContainer {
    margin-bottom: 1rem;
  }

  .infoContainerDesription {
    overflow: scroll;
  }
}

.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateX(20px);
  opacity: 0;
}
</style>
