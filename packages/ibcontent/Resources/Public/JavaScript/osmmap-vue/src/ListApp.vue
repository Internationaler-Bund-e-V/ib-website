<template>
  <Suspense>
    <ListView
      :showFederalStateFilter="TypoSettings.showFederalStateFilter"
      :navigation="TypoSettings.listViewNavigation"
      :showCategoryFilter="TypoSettings.showCategoryFilter"
    />
    <template #fallback>
      <div class="loadingImage">
        <img :src="loadingImage" />
      </div>
    </template>
  </Suspense>
</template>

<script>
import { inject } from "vue";
import ListView from "./components/ListView/ListView.vue";
import loadingImage from "@/assets/loading.svg";

export default {
  name: "ListApp",
  components: { ListView },
  option: {
    suspensible: false,
  },
  setup() {
    let TypoSettings = inject("TypoSettings");

    return {
      loadingImage,
      TypoSettings,
    };
  },
};
</script>
<style scoped lang="scss">
.loadingImage {
  width: 100%;
  text-align: center;
}
</style>
