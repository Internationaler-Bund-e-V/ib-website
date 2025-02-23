import { ref, inject } from "vue";
import { generateTags } from "./getTags";
import { generateCategories } from "./getCategories";
export const Status = {
    IDLE: "IDLE",
    RUNNING: "RUNNING",
    SUCCESS: "SUCCESS",
    ERROR: "ERROR",
};

export default async function useFetchLocations(navid, Tags, Categories) {
    async function fetchLocations() {
        status.value = Status.RUNNING;

        const proxyURL = inject('proxyURL');


        try {
            const res = await fetch(proxyURL + '?navid=' + navid, {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                },
            });
            if (!res.ok) {
                status.value = Status.ERROR;
            }
            const json = await res.json();
            status.value = Status.SUCCESS;
            return json;
        } catch (err) {
            status.value = Status.ERROR;

            throw new Error(err);
        }
    }

    async function refetchLocations() {
        locations.value = await fetchLocations();
    }

    let status = ref(Status.IDLE);
    let locations = ref(await fetchLocations());
    generateTags(Tags, locations);
    generateCategories(Categories, locations, navid);

    return {
        locations,
        status,
        refetchLocations,
    };
}
