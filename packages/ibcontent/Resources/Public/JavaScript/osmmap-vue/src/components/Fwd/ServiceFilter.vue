<template>
  <div>
    <div class="fwdServices">
      <label>Dienste:</label>
      <div v-for="service in services" :key="service.Jobservice.id">
        <label class="fwdLabel">
          <input
            class="service"
            type="checkbox"
            :id="service.Jobservice.id"
            :value="service.Jobservice.id"
            v-model="selectedServices"
          />{{ service.Jobservice.name }}
        </label>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, inject, onMounted } from "vue";
export default {
  name: "ServiceFilter",
  components: {},
  setup() {
    const proxyURL = inject("proxyURL");

    const services = ref([]);
    const selectedServices = ref([]);

    const getServices = async () => {
      fetch(proxyURL + '?jobservices=true')
        .then((response) => response.json())
        .then((data) => (services.value = data));
    };

    onMounted(getServices);

    return {
      services,
      selectedServices,
    };
  },
};
</script>

<style src="@vueform/multiselect/themes/default.css"></style>
<style scoped lang="scss">
.fwdServices {
  padding: 0.5rem;
  .fwdLabel {
    cursor: pointer;
    font-size: 10px;
    display: flex;
    align-items: center;
    .service {
      padding: 0;
      margin: 0;
      margin-right: 0.5rem;
    }
  }
}
</style>
