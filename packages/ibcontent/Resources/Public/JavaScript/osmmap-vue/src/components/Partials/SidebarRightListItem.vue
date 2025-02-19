<template>
  <div @click="$emit('locationSelected', Location)" class="SidebarRightListItem">
    {{ Location.name }} <i class="fas fa-chevron-right"></i>
  </div>
</template>
<script>
export default {
  name: "SidebarRightListItem",
  props: { Location },
  setup() {
    return {};
  },
};
</script>
<style scoped lang="scss">
.SidebarRightListItem {
  padding: 1rem;
  border-bottom: 1px solid lightgray;
  color: #005590;
  display: flex;
  align-items: center;
  justify-content: space-between;
  &:hover {
    background: lightgray;
    cursor: pointer;
  }
}
</style>
