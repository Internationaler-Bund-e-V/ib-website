<template>
  <div class="osmMap">
    <SidebarLeft />
    <div id="osmMapContainer" :class="TypoSettings.borderButtonColorClass">
      <div class="osmMapMain">
        <OsmMap />
      </div>
    </div>
    <Navbar />
    <CategoryFilter v-if="TypoSettings.showCategoryFilter == 1 && TypoSettings.usePreFilterCategory != 1" />
    <Tagfilter v-if="TypoSettings.showTagFilter == 1 && TypoSettings.usePreFilterCategory != 1" />
    <FederalStateFilter v-if="TypoSettings.showFederalStateFilter == 1" />
    <SidebarRight />
    <TagLegend v-if="TypoSettings.showTagFilter == 1 && TypoSettings.usePreFilterCategory != 1" />
  </div>
</template>

<script>
import SidebarLeft from "./components/SidebarLeft.vue";
//import SidebarRight from "./components/SidebarRight.vue";
import SidebarRight from "./components/Partials/SidebarRight.vue";
import Navbar from "./components/Navbar.vue";
import Tagfilter from "./components/Filter/TagsFilter.vue";
import FederalStateFilter from "./components/Filter/FederalStateFilter.vue";
import CategoryFilter from "./components/Filter/CategoryFilter.vue";
import TagLegend from "./components/Partials/TagLegend.vue";

import OsmMap from "./components/Osmmap.vue";
import { inject } from "vue";
export default {
  name: "App",
  components: {
    SidebarLeft,
    OsmMap,
    SidebarRight,
    Navbar,
    Tagfilter,
    FederalStateFilter,
    CategoryFilter,
    TagLegend,
  },
  setup() {
    let TypoSettings = inject("TypoSettings");

    return {
      TypoSettings,
    };
  },
};
</script>

<style lang="scss">
.osmMap {
  position: relative;
}

#osmMapContainer {
  border: 1px solid #005590;
  border-radius: 5px;
  display: flex;
  overflow: hidden;

  &.orange {
    border: 1px solid #f18700;
  }

  &.lightblue {
    border: 1px solid #009ddf;
  }

  .osmMapMain {
    display: flex;
    flex: 1;
  }
}
</style>
