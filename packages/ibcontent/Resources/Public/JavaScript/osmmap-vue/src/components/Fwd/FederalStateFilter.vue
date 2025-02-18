<template>
  <div>
    <label>Bundesland wählen<span class="fwdMandatory">*</span></label>
    <select v-model="federalState">
      <option disabled value="">Bundesland wählen...</option>
      <option>Baden-Württemberg</option>
      <option>Bayern</option>
      <option>Berlin</option>
      <option>Brandenburg</option>
      <option>Bremen</option>
      <option>Hamburg</option>
      <option>Hessen</option>
      <option>Mecklenburg-Vorpommern</option>
      <option>Niedersachsen</option>
      <option>Nordrhein-Westfalen</option>
      <option>Rheinland-Pfalz</option>
      <option>Saarland</option>
      <option>Sachsen</option>
      <option>Sachsen-Anhalt</option>
      <option>Schleswig-Holstein</option>
      <option>Thüringen</option>
    </select>
  </div>
</template>

<script>
import { ref } from "vue";

export default {
  name: "FederalStateFilter",
  components: {},
  setup() {
    let federalState = ref(String());

    return {
      federalState,
    };
  },
};
</script>

<style lang="scss">
</style>