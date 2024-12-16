<template>
  <div
    class="osmMapTagsMobileFilter"
    :class="[{ showCategory: showCategoryClass }, TypoSettings.borderButtonColorClass]"
  >
    <label>{{ getTagFilterHeadline() }}</label>

    <VueSelect
      v-model="selectedTag"
      :options="Tags"
      label="title"
      :reduce="(tag) => tag.id"
      :clearable="false"
      :searchable="false"
    >
      <!-- template selected -->
      <template #selected-option="tag">
        <div style="display: flex; align-items: center">
          <strong>{{ tag.title }}</strong>
          <span class="tagItemIcon">
            <i class="fas fa-bookmark" :style="{ color: tag.color }"></i>
          </span>
        </div>
      </template>
      <!-- template option -->
      <template #option="option">
        <span>{{ option.title }}</span>
        <span class="tagItemIcon">
          <i class="fas fa-bookmark" :style="{ color: option.color }"></i>
        </span>
      </template>
    </VueSelect>
  </div>
</template>
<script>
import { ref, inject } from "vue";
import VueSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import { getTagFilterHeadline } from "../../composable/getTagFilterHeadline";

export default {
  name: "TagsMobileFilter",
  components: {
    VueSelect,
  },
  setup() {
    let TypoSettings = inject("TypoSettings");
    let Tags = inject("Tags");
    let selectedTag = ref(inject("selectedTag"));
    let showCategoryClass = false;

    if (TypoSettings.showCategoryFilter == "1") {
      showCategoryClass = true;
    }

    return {
      Tags,
      TypoSettings,
      selectedTag,
      showCategoryClass,
      getTagFilterHeadline,
    };
  },
};
</script>
<style lang="scss">
.osmMapTagsMobileFilter {
  background: white;
  width: 100%;
  border: 1px solid #005590;
  border-radius: 5px;
  box-shadow: 0 5px 10px rgb(2 2 2 / 20%);
  padding: 0.5rem;

  &.showCategory {
    left: 43%;
  }

  &.orange {
    border: 1px solid #f18700;
  }

  &.lightblue {
    border: 1px solid #009ddf;
  }
  .colorCircle {
    background: red;
  }

  .tagItemIcon {
    color: transparent;
    width: 15px;
    height: 15px;
    display: inline-block;
    margin: 5px;
    border-radius: 50%;
  }

  .vs__search {
    display: none;
  }
  .vs__dropdown-option {
    display: flex;
    align-items: center;
    color: #666;
  }
}
</style>
