<template>
  <div class="overlay-content" :class="colorThemeClass">
    {{ Location.name }}
  </div>
</template>

<script>
import { ref, inject } from "vue";
export default {
  name: "Mapoverlay",
  props: {
    Location: Object,
  },

  setup() {
    let TypoSettings = inject("TypoSettings");
    let colorThemeClass = ref("");

    if (TypoSettings.borderButtonColor == '#f18700') {
      colorThemeClass.value = 'orange';
    }
    if (TypoSettings.borderButtonColor == '#009ddf') {
      colorThemeClass.value = 'lightblue';
    }
    return {
      colorThemeClass
    }
  },
};
</script>
<style lang="scss">
.overlay-content {
  background: white;
  border: 1px solid #005590;
  border-radius: 5px;
  box-shadow: 0 5px 10px rgb(2 2 2 / 20%);
  padding: 10px 20px;
  font-size: 10px;
  width: 200px;
  opacity: .9;

  &.orange {
    border: 1px solid #f18700;
  }

  &.lightblue {
    border: 1px solid #009ddf;
  }
}
</style>