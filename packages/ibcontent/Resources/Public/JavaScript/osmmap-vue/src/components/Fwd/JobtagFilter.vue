<template>
  <div class="Jobtags">
    <label>Tätigkeitsbereiche:</label>
    <div v-for="jobtag in jobtags" v-bind:key="jobtag.Jobtag.id">
      <label class="fwdLabel">
        <input
          class="jobtag"
          type="checkbox"
          :id="jobtag.Jobtag.id"
          :value="jobtag.Jobtag.id"
          v-model="selectedCategories"
        />{{ jobtag.Jobtag.name }}
      </label>
    </div>
  </div>
</template>

<script>
import { onMounted, ref, inject } from "vue";
export default {
  name: "JobtagFilter",
  components: {},
  setup() {
    const proxyURL = inject("proxyURL");

    const jobtags = ref([]);
    const selectedJobtags = ref([]);

    const getJobtags = async () => {
      //redaktionstool.ddev.site/interfaces/requestJobTags
      fetch(proxyURL + "?jobtags=true")
        .then((response) => response.json())
        .then((data) => (jobtags.value = data));
    };

    onMounted(getJobtags);

    return {
      jobtags,
      selectedJobtags,
    };
  },
};
</script>

<style src="@vueform/multiselect/themes/default.css"></style>
<style scoped lang="scss">
.Jobtags {
  padding: 0.5rem;
  .fwdLabel {
    cursor: pointer;
    font-size: 10px;
    display: flex;
    align-items: center;
    .jobtag {
      padding: 0;
      margin: 0;
      margin-right: 0.5rem;
    }
  }
}
</style>
