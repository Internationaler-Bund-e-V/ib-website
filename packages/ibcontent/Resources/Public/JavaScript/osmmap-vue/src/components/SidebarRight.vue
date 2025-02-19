<template>
  <div
    class="osmMapSidebarRight"
    :class="colorThemeClass"
    v-if="locationSelected && Location.id > 0"
  >
    <div class="osmMapSidebarRightContainer">
      <div class="locationImage">
        <img
          v-if="
            typeof locationData.Images.Headerslides !== 'undefined' &&
            locationData.Images.Headerslides.length > 0
          "
          :src="
            'https://redaktion.internationaler-bund.de/img/' +
            locationData.Images.Headerslides[0]
          "
        />
      </div>
      <div class="locationName">
        <h2>
          {{ locationData.Location.name }}
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
          {{ locationData.Location.street }}
        </div>
        <div class="locationZipStreet">
          {{ locationData.Location.plz }} {{ locationData.Location.city }}
        </div>
        <div class="locationPhone">Tel.: {{ locationData.Location.phone_int }}</div>

        <div v-if="locationData.Location.fax" class="locationPhone">
          Fax: {{ locationData.Location.fax }}
        </div>
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
</template>
<script>
import { ref, inject, watch } from "vue";
import { generateTagDots } from "../composable/generateTagDots";
import { getTagFilterHeadline } from "../composable/getTagFilterHeadline";
export default {
  name: "SidebarRight",

  setup() {
    let Location = ref(inject("Location"));
    let baseInterfaceURL = inject("baseInterfaceURL");
    let proxyURL = inject("proxyURL");
    let locationSelected = ref(false);
    let locationData = ref([]);
    let TypoSettings = inject("TypoSettings");
    let colorThemeClass = ref("");

    if (TypoSettings.borderButtonColor == "#f18700") {
      colorThemeClass.value = "orange";
    }
    if (TypoSettings.borderButtonColor == "#009ddf") {
      colorThemeClass.value = "lightblue";
    }

    const getLocation = async (locationID) => {
      //https://redaktionstool.ddev.site/interfaces/requestLocation/id:210676
      fetch(proxyURL + "?baseurl=" + baseInterfaceURL + "&locationid=" + locationID)
        .then((response) => response.json())
        .then((data) => (locationData.value = data))
        .then(() => (locationSelected.value = true));
    };

    watch(Location, () => {
      if (Location.value.id !== undefined) {
        getLocation(Location.value.id);
      }
    });

    return {
      Location,
      locationData,
      getLocation,
      locationSelected,
      colorThemeClass,
      generateTagDots,
      getTagFilterHeadline,
      TypoSettings,
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

  &.orange {
    border: 1px solid #f18700;
  }

  &.lightblue {
    border: 1px solid #009ddf;
  }
}

.osmMapSidebarRightContainer {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  height: 100%;

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
</style>
