<template>
  <div class="FWDCity">
    <h2 :class="{ statusOpen: open }" v-on:click="toggleContainer">
      {{ City.citytitle }}
      <span class="jobCounter"> ({{ City.Jobs.length }}) </span>
    </h2>
    <div v-show="open" class="cityJobContainer">
      <div v-for="Job in City.Jobs" v-bind:key="Job.id">
        <JobComponent :Job="Job" />
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from "vue";
import JobComponent from "./Job.vue";
export default {
  name: "City",
  components: { JobComponent },
  props: {
    City: Object,
    Categories: Object,
  },
  setup() {
    const open = ref(false);

    const toggleContainer = () => {
      open.value = !open.value;
    };

    return {
      open,
      toggleContainer,
    };
  },
};
</script>
<style scoped>
.FWDCity h2 {
  padding: 0.5rem 0;
  cursor: pointer;
  border-bottom: 1px solid #005590;
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  display: flex;
  align-items: center;
}
span.jobCounter {
  font-size: 1rem;
}
.FWDCity h2::after {
  font-family: "Font Awesome 5 Free";
  content: "\F054";
  position: absolute;
  right: 20px;
  transform: rotate(90deg);
}
.FWDCity h2.statusOpen::after {
  transform: rotate(-90deg);
}
</style>
