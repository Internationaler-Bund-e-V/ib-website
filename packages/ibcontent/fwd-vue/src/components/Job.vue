<template>
  <a class="fwdJobLinkContainer" :href="'/job/' + Job.id" target="_blank">
    <div class="FWDJob">
      <div>
        <div>
          <span class="fwdJobName fwdJobLlink">{{ Job.name }}  <i class="fas fa-external-link-alt"></i></span>
        </div>
      </div>
    </div>
  </a>
</template>

<script>
export default {
  name: "Job",
  props: {
    Job: Object,
  },
  setup() {},
};
</script>
<style scoped>
.fwdJobLinkContainer {
  text-decoration: none !important;
}

.fwdJobLinkContainer:hover {
  color: #666 !important;
}
.fwdJobLinkContainer:hover .fwdJobLlink {
  color: #f18700;
}
.FWDJob {
  position: relative;
  border-radius: 10px;
  padding: 1rem;
  margin-bottom: 0.5rem;
  box-shadow: 1px 1px 5px 1px rgba(240, 229, 229, 0.4);
  -webkit-box-shadow: 1px 1px 5px 1px rgba(240, 229, 229, 0.4);
  -moz-box-shadow: 1px 1px 5px 1px rgba(240, 229, 229, 0.4);
  cursor: pointer;
  left: 0;
  background: #fff;
  transition: all 0.1s linear;
}
.FWDJob:hover {
  left: -15px;
  background: #eee;
}
.fwdJobCategory,
.fwdJobName {
  font-weight: bold;
}
</style>