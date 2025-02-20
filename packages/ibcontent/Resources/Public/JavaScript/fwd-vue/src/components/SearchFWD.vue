<template>
  <div>
    <div class="row">
      <div class="columns small-12 medium-6">
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
    </div>
    <div class="row">
      <div class="columns small-12 medium-6">
        <label>Dienste:</label>
        <div class="fwdServices">
          <div v-for="service in services" :key="service.Jobservice.id">
            <label class="fwdLabel">
              <input
                class="services"
                type="checkbox"
                :id="service.Jobservice.id"
                :value="service.Jobservice.id"
                v-model="selectedServices"
              />
              {{ serviceNames[service.Jobservice.name.toLowerCase()] }}
            </label>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="columns small-12">
        <label>Tätigkeitsbereiche:</label>
        <div
          v-for="category in categories"
          v-bind:key="category.Jobtag.id"
          class="columns medium-6 small-12"
        >
          <label class="fwdLabel">
            <input
              class="category"
              type="checkbox"
              :id="category.Jobtag.id"
              :value="category.Jobtag.id"
              v-model="selectedCategories"
            />{{ category.Jobtag.name }}
          </label>
        </div>
      </div>
    </div>
    <div class="row" v-if="federalState">
      <div class="columns small-12">
        <button class="ibCustomButton lightblue" @click="search()">Suchen</button>
        <!-- <div>Bundesland: {{ federalState }}</div>
        <div>Ausgewählte Kategorien: {{ selectedCategories }}</div> -->
        <div v-if="loading" class="sliderLoading"></div>
        <div v-if="!loading && resultFetched" class="resultContainer">
          <div v-if="resultCount > 0" class="resultHint">
            Es wurden Ergebnisse in den folgenden Städten gefunden:
          </div>
          <div v-if="resultCount == 0" class="resultHint">
            Es wurden keine Ergebnisse gefunden
          </div>
          <div v-for="city in result.Locations" v-bind:key="city.lid">
            <CityComponent :City="city" :Categories="selectedCategories" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, ref, inject } from "vue";
import CityComponent from "./City.vue";
export default {
  components: {
    CityComponent,
  },
  setup() {
    let federalState = ref(String());
    let services = ref([]);
    let selectedServices = ref([]);
    let selectedCategories = ref([]);
    let categories = ref([]);
    let result = ref([]);
    let resultFetched = ref(false);
    let loading = ref(false);
    let resultCount = ref(Number(0));
    let proxyURL = inject('proxyURL');
    let serviceNames = {
      fsj: "FSJ - Freiwilliges Soziales Jahr",
      föj: "FÖJ - Freiwilliges Ökologisches Jahr",
      bfd: "BFD - Bundesfreiwilligendienst",
    };

    const getCategories = async () => {
      fetch(proxyURL + "?jobtags=true)
        .then((response) => response.json())
        .then((data) => (categories.value = data));
      fetch(proxyURL + "?jobservices=true)
        .then((response) => response.json())
        .then((data) => (services.value = data));
    };

    const search = async () => {
      loading.value = true;
      fetch(
        proxyURL +
          "?federalState=" +
          federalState.value +
          "&categories=" +
          selectedCategories.value.toString() +
          "&services=" +
          selectedServices.value.toString() +
          "&baseurl=" +
          baseURL
      )
        .then((response) => response.json())
        .then((data) => (result.value = data))
        .then((data) => (resultCount.value = data.CountJobs))
        .then(() => (loading.value = false))
        .then(() => (resultFetched.value = true));
    };

    onMounted(getCategories);

    return {
      federalState,
      categories,
      services,
      selectedCategories,
      selectedServices,
      result,
      loading,
      resultFetched,
      resultCount,
      search,
      serviceNames,
    };
  },
};
</script>
<style scoped>
.fwdServices {
  display: flex;
  justify-content: space-between;
  flex-direction: column;
}
.fwdLabel {
  cursor: pointer;
  font-weight: normal;
}
.fwdMandatory {
  color: red;
}
.resultHint {
  padding: 1rem 0;
}
</style>
