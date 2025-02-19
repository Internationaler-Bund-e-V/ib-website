<template>
  <div class="osmMapNavigationFilter">
    <div class="selectNavigation" v-if="TypoSettings.navigations.length >= 1 || TypoSettings.mapLayout == 'Standard'">
      <select v-model="selectedNavigation" @change="updateNavigation">
        <option v-for="navigation in navigations" :value="navigation.value" :key="navigation">
          {{ navigation.name }} <span v-if="navigation.value == selectedNavigation"> - ({{ locations.length }})</span>
        </option>
      </select>
    </div>

  </div>
</template>
<script>
import { ref, inject } from "vue";

export default {
  name: "NavigationFilter",

  setup() {
    let locations = inject("Locations");
    let Navigation = inject("Navigation");
    let TypoSettings = inject("TypoSettings");

    const navigations = ref([
      { name: "Alle Standorte", value: 5 },
      { name: "Kitas", value: 3 },
      { name: "Freiwilligendienste", value: 4 },
    ]);


    const selectedNavigation = ref(TypoSettings.mainNavigation);

    const updateNavigation = () => {
      Navigation.value = selectedNavigation.value;
    };

    return {
      navigations,
      selectedNavigation,
      locations,
      updateNavigation,
      TypoSettings
    };
  },
};
</script>
<style lang="scss">
.osmMapNavigationFilter {
  display: flex;
  align-items: center;

  .selectNavigation {
    flex: 1;

    select {
      margin: 0;
    }
  }

  .locationsCount {
    padding: 0 0.5rem;
  }
}
</style>
