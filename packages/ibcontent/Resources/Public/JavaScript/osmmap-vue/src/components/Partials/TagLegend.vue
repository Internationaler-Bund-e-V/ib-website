<template>
  <div class="osmMapTagLegend" :class="colorThemeClass">
    <div><b>Legende</b></div>
    <span class="legendItem" v-for="tag in getTags" :key="tag">
      {{ tag.title }}
      <span class="tagColor"
        ><i class="fas fa-bookmark" :style="{ color: tag.color }"></i
      ></span>
    </span>
  </div>
</template>
<script>
import { ref, inject, computed } from "vue";
export default {
  name: "TagLegend",

  setup() {
    let Tags = inject("Tags");
    let TypoSettings = inject("TypoSettings");
    let colorThemeClass = ref("");

    if (TypoSettings.borderButtonColor == "#f18700") {
      colorThemeClass.value = "orange";
    }
    if (TypoSettings.borderButtonColor == "#009ddf") {
      colorThemeClass.value = "lightblue";
    }

    const getTags = computed(() => {
      return Tags.value.filter((tag) => {
        return tag.title != "Alle";
      });
    });

    return {
      colorThemeClass,
      getTags,
    };
  },
};
</script>
<style lang="scss">
.osmMapTagLegend {
  position: absolute;
  background: white;
  bottom: 10px;
  margin: 0 auto;
  left: 22%;
  border: 1px solid #005590;
  border-radius: 5px;
  box-shadow: 0 5px 10px rgb(2 2 2 / 20%);
  padding: 0.5rem;
  font-size: 10px;

  &.orange {
    border: 1px solid #f18700;
  }

  &.lightblue {
    border: 1px solid #009ddf;
  }
  .legendItem {
    margin-right: 10px;
  }
  .tagColor {
    width: 5px;
    height: 5px;
    display: inline-block;
    margin: 1px;
    border-radius: 50%;
  }
}
</style>
