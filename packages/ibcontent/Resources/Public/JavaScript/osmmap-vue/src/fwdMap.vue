<template>
  <div class="osmMap">
    <FwdSidebarLeft />
    <div id="osmMapContainer">
      <div class="osmMapMain">
        <OsmMap />
      </div>
    </div>
    <Navbar />
    <SidebarRight />
  </div>
</template>

<script>
import FwdSidebarLeft from "./components/Fwd/FwdSidebarLeft.vue";
import SidebarRight from "./components/SidebarRight.vue";
import Navbar from "./components/Navbar.vue";

import OsmMap from "./components/Osmmap.vue";
export default {
  name: "fwdMap",
  components: { FwdSidebarLeft, OsmMap, SidebarRight, Navbar },
  setup() {},
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

  .osmMapMain {
    display: flex;
    flex: 1;
  }
}
</style>