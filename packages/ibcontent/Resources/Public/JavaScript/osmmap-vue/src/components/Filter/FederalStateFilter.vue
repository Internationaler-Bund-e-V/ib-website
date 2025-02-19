<template>
    <div class="osmMapFederalStateFilter">
        <label>Bundesland:</label>
        <select v-model="federalState" @change="updateFederalState">
            <option>Alle</option>
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
import { ref, inject, onMounted } from "vue";

export default {
    name: "FederalStateFilter",

    setup() {
        let TypoSettings = inject("TypoSettings");
        let federalState = ref(String("Alle"));
        let Locations = inject("Locations");
        let tmpLocations = ref(inject("tmpLocations"));
        let Location = inject("Location");

        const updateFederalState = () => {
            Location.value = Object;

            if (federalState.value.toLocaleLowerCase() != 'alle') {
                tmpLocations.value = Locations.value.filter((location) => {
                    return (
                        location.Location.federal_state
                            .toLowerCase()
                            .indexOf(federalState.value.toLowerCase()) != -1
                    );
                });
            }
            else {
                tmpLocations.value = Locations.value;
            }

        }


        return {
            TypoSettings,
            federalState,
            updateFederalState
        };
    },
};
</script>
<style lang="scss">
.osmMapFederalStateFilter {
    position: absolute;
    background: white;
    top: 10px;
    margin: 0 auto;
    left: 22%;

    width: 20%;
    border: 1px solid #f18700;
    border-radius: 5px;
    box-shadow: 0 5px 10px rgb(2 2 2 / 20%);
    padding: 0.5rem;
}
</style>
  