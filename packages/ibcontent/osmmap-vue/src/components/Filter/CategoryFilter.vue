<template>
  <div class="osmMapCategoriesFilter darkblue" :class="colorThemeClass">
    <label>Kategorien</label>
    <Select2
      v-model="selectedCategories"
      :settings="{ multiple: true, placeholder: 'Alle', language: 'de' }"
      :options="Categories"
      @select="updateCategory($event)"
    />
  </div>
</template>
<script>
import { ref, inject } from "vue";
import Select2 from "vue3-select2-component";

export default {
  name: "CategoryFilter",
  components: {
    Select2,
  },
  setup() {
    let TypoSettings = inject("TypoSettings");
    let Categories = inject("Categories");
    let selectedCategory = ref(String("Alle"));
    let selectedCategories = inject("selectedCategories");
    let selectedTag = ref(inject("selectedTag"));
    let colorThemeClass = ref("");
    colorThemeClass.value = TypoSettings.borderButtonColorClass;

    const updateCategory = () => {
      if (TypoSettings.showTagFilter) {
        selectedTag.value = "Alle";
      }
    };

    return {
      Categories,
      TypoSettings,
      selectedCategory,
      updateCategory,
      selectedCategories,
      colorThemeClass,
    };
  },
};
</script>
<style lang="scss">
.osmMapCategoriesFilter {
  position: absolute; 
  background: white;
  top: 10px;
  margin: 0 auto;
  left: 22%;
  width: 20%;
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

  .select2-search__field {
    margin: 0;
    padding: 0;
    font-size: 12px;
  }

  .select2-selection__choice {
    font-size: 11px;
    font-weight: bold;
    display: flex;
    align-items: center;

    .select2-selection__choice__remove {
      font-size: 20px;
      line-height: 1;
    }
  }
}
</style>
