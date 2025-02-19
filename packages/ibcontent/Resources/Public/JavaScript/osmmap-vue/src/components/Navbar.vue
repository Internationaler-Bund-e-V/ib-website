<template>
  <div class="osmMapNavbar" :class="{ mobile: mobile, colorThemeClass }" v-if="Pin.isSet">
    <span class="pinContainer">
      <img :src="pinIcon" />
    </span>
    {{ Pin.displayName }}
  </div>
</template>
<script>
import { ref, inject } from "vue";
import pinIcon from "../assets/map-pin-icon.png";
export default {
  name: "Navbar",
  components: {},
  props: {
    mobile: Boolean,
  },
  setup() {
    let Pin = inject("Pin");
    let TypoSettings = inject("TypoSettings");
    let colorThemeClass = ref("");

    if (TypoSettings.borderButtonColor == "#f18700") {
      colorThemeClass.value = "orange";
    }
    if (TypoSettings.borderButtonColor == "#009ddf") {
      colorThemeClass.value = "lightblue";
    }

    return {
      Pin,
      pinIcon,
      colorThemeClass,
    };
  },
};
</script>
<style lang="scss">
.osmMapNavbar {
  position: absolute;
  background: white;
  bottom: 65px;
  left: 22%;
  border: 1px solid #005590;
  border-radius: 5px;
  box-shadow: 0 5px 10px rgb(2 2 2 / 20%);
  padding: 0.5rem;

  &.orange {
    border: 1px solid #f18700;
  }

  &.lightblue {
    border: 1px solid #009ddf;
  }

  .pinContainer {
    display: inline-block;
    width: 10px;
  }

  &.mobile {
    position: relative;
    width: 100%;
    margin: 0 auto;
    margin-top: 1rem;
    max-width: 74.25rem;
    left: 0;
    bottom: 10px;
  }
}
</style>
