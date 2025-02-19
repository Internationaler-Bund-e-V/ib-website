<template>
  <div id="osmMapSelectGeoLocation">
    <h4 class="osmMapSelectionGeoLocationHeadline">
      Es wurden {{ GeoLocations.length }} Positionen gefunden, bitte auswählen für weitere
      Lokalisierung:
    </h4>
    <div>
      <div
        class="osmMapSelectGeoLocationItem"
        v-for="(geoLocation, index) in GeoLocations"
        v-bind:key="geoLocation"
        @click="$emit('geoLocationSelected', index)"
      >
        {{ geoLocation.properties.display_name }}
        <span
          v-if="
            geoLocation.properties.addresstype == 'state' ||
            geoLocation.properties.addresstype == 'land_area'
          "
        >
          (Bundesland)</span
        >
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "SelectGeoLocation",
  props: { GeoLocations: Object },
  setup() {
    return {};
  },
};
</script>

<style scoped lang="scss">
#osmMapSelectGeoLocation {
  background: white;
  position: absolute;
  top: 75px;
  bottom: 0px;
  left: 0;
  overflow: scroll;
  border: 1px solid #005590;
  border-radius: 5px;

  .osmMapSelectionGeoLocationHeadline {
    padding: 0.5rem;
  }

  .osmMapSelectGeoLocationItem {
    border-top: 1px solid gray;
    padding: 0.5rem;
    font-size: 0.75rem;
    &:hover {
      background: lightgray;
      cursor: pointer;
    }
  }
}

#osmListApp {
  #osmMapSelectGeoLocation {
    position: initial;
  }
}
</style>
