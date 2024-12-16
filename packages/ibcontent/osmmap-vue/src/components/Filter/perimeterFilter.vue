<template>
  <div class="perimeterContainer darkblue">
    <div class="perimeterLabel"><label></label></div>
    <label>Entfernung/Umkreis:</label>
    <Select2
      v-model="selectedDistance"
      :settings="{ multiple: false, language: 'de', minimumResultsForSearch: -1 }"
      :options="distanceSettings"
    />
  </div>
</template>

<script>
import { ref, inject } from "vue";
import Select2 from "vue3-select2-component";

export default {
  name: "PerimeterMobileFIleter",
  setup() {
    //let distanceSettings = ref(["5 Km", "10 Km", "25 Km", "50 Km", "100 Km"]);
    let distanceSettings = ref([5, 10, 25, 50, 100, 150, 200]);
    let selectedDistance = inject("selectedDistance");
    return {
      distanceSettings,
      selectedDistance,
    };
  },
  components: { Select2 },
};
</script>

<style scoped>
.perimeterContainer {
  background: white;
  width: 100%;
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
}
@media (min-width: 1024px) {
  .perimeterContainer {
    width: 33%;
    height: 90px;
  }
}
</style>
