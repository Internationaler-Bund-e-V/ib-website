<template>
  <div
    class="osmMapLocationListItem"
    :class="[{ selectedLocation: Location.id === selectedLocation.id }, colorThemeClass]"
    @click="$emit('locationSelected', Location)"
  >
    <div class="locationName">{{ Location.name }}</div>
    <div class="locationCity">
      {{ Location.city }} <span v-if="showDistance">({{ Location.distance }} Km)</span>
    </div>
    <div
      v-if="TypoSettings.showTagFilter == '1'"
      v-html="generateTagDots(Location.tags)"
    ></div>
  </div>
</template>
<script>
import { ref, inject } from "vue";
import { generateTagDots } from "../composable/generateTagDots";

export default {
  name: "LocationListItem",
  props: {
    Location: Object,
    showDistance: Boolean,
  },
  setup() {
    let selectedLocation = inject("Location");
    let Center = inject("Center");
    let TypoSettings = inject("TypoSettings");
    let colorThemeClass = ref("");

    if (TypoSettings.borderButtonColor == "#f18700") {
      colorThemeClass.value = "orange";
    }
    if (TypoSettings.borderButtonColor == "#009ddf") {
      colorThemeClass.value = "lightblue";
    }

    return {
      selectedLocation,
      colorThemeClass,
      TypoSettings,
      generateTagDots,
    };
  },
};
</script>
<style lang="scss">
.osmMapLocationListItem {
  padding: 0.5rem;
  border-bottom: 1px solid lightgray;
  cursor: pointer;
  overflow: hidden;
  word-wrap: break-word;

  &:hover {
    background: lightgray;
  }

  &.selectedLocation {
    background: #005590;
    color: white;

    &.orange {
      background: #f18700;
    }

    &.lightblue {
      background: #009ddf;
    }
  }

  .locationName {
    font-weight: bold;
  }
  .tagItemIcon {
    width: 10px;
    height: 10px;
    margin: 1px;
    border-radius: 50%;
  }
}
</style>
