<template>
  <div class="osmmap">
    <div class="sliderLoading" v-if="Loading"></div>
    <div class="mapContainer" v-if="!Loading">
      <ol-map
        @click="checkClick"
        :loadTilesWhileAnimating="true"
        :loadTilesWhileInteracting="true"
        style="height: 600px"
        ref="map"
      >
        <ol-view
          ref="view"
          :center="center"
          :rotation="rotation"
          :zoom="zoom"
          :projection="projection"
          :constrainOnlyCenter="true"
        />

        <ol-tile-layer>
          <ol-source-osm />
        </ol-tile-layer>

        <!-- show borders start -->
        <ol-vector-layer>
          <ol-source-vector :url="borderURL" :format="geoJson">
            <ol-style>
              <ol-style-stroke
                :color="TypoSettings.borderColor"
                width="2"
              ></ol-style-stroke>
              <ol-style-fill :color="TypoSettings.mapColor"></ol-style-fill>
            </ol-style>
          </ol-source-vector>
        </ol-vector-layer>
        <!-- show borders end -->

        <!-- clusters -->

        <!-- cluster click select-->
        <ol-interaction-select
          @select="clusterSelected"
          :filter="selectInteractionFilter"
        >
          <ol-style>
            <ol-style-circle :radius="radiusActive" :zindex="10000">
              <ol-style-fill :color="fillColorActive"></ol-style-fill>
              <ol-style-stroke :color="strokeColorActive"></ol-style-stroke>
            </ol-style-circle>
          </ol-style>
        </ol-interaction-select>

        <!-- cluster hover-->
        <ol-interaction-select
          @select="highlightCluster"
          :filter="selectInteractionFilter"
          :condition="selectCondition"
        >
          <ol-style>
            <ol-style-circle :radius="radiusActive" :zindex="10000">
              <ol-style-fill :color="fillColorActive"></ol-style-fill>
              <ol-style-stroke :color="strokeColorActive"></ol-style-stroke>
            </ol-style-circle>
          </ol-style>
        </ol-interaction-select>

        <!-- cluster layer -->
        <ol-vector-layer
          ref="clusterLayer"
          :updateWhileAnimating="true"
          :updateWhileInteracting="true"
        >
          <ol-source-vector ref="clusterVector" v-if="locationsLoaded">
            <ol-feature
              v-for="(cluster, index) in clusters"
              :key="cluster.Locations[0].id"
              :properties="{ Cluster: cluster }"
            >
              <ol-geom-point
                style="cursor: pointer"
                :coordinates="fromLonLat([cluster.Geo.Lon, cluster.Geo.Lat])"
              >
              </ol-geom-point>

              <ol-style v-if="cluster.LocationIds.includes(Location.id)">
                <ol-style-circle :radius="radiusActive" :zindex="10000">
                  <ol-style-fill :color="fillColorActive"></ol-style-fill>
                  <ol-style-stroke :color="strokeColorActive"></ol-style-stroke>
                </ol-style-circle>
              </ol-style>
              <ol-style v-else>
                <ol-style-circle :radius="radius">
                  <ol-style-fill :color="fillColor"></ol-style-fill>
                  <ol-style-stroke :color="strokeColor"></ol-style-stroke>
                </ol-style-circle>
              </ol-style>
            </ol-feature>
            <!-- pin/geocoding -->
            <ol-feature v-if="Pin.isSet">
              <ol-geom-point :coordinates="pinCoordinates" />
              <ol-style>
                <ol-style-icon :src="centerIcon" :scale="0.05"> </ol-style-icon>
              </ol-style>
            </ol-feature>
            <!-- radius if pin/geocoding -->
            <ol-feature v-if="Pin.isSet">
              <ol-geom-circle
                :center="pinCoordinates"
                :radius="selectedDistance * 1600"
              ></ol-geom-circle>
              <ol-style>
                <ol-style-stroke color="lightgray" :width="3"></ol-style-stroke>
                <ol-style-fill color="rgba(255,255,255,0.6)"></ol-style-fill>
              </ol-style>
            </ol-feature>
          </ol-source-vector>
        </ol-vector-layer>
        <!-- cluster overlay start -->
        <ol-overlay
          v-if="hoverActive"
          :position="fromLonLat([hoverCluster.Geo.Lon, hoverCluster.Geo.Lat])"
          :positioning="'bottom-center'"
          :offset="[0, -25]"
        >
          <template v-slot="">
            <Clusteroverlay :Cluster="hoverCluster" />
          </template>
        </ol-overlay>
        <!-- cluster overlay end -->

        <ol-zoom-control />
      </ol-map>
    </div>
  </div>
</template>
*
<script>
import { ref, inject, watch } from "vue";
import { fromLonLat } from "ol/proj";
import Mapoverlay from "./Mapoverlay.vue";
import Clusteroverlay from "./Partials/Clusteroverlay.vue";
import centerIcon from "../assets/map-pin-icon.png";

export default {
  name: "Osmmap",
  components: {
    Mapoverlay,
    Clusteroverlay,
  },
  setup() {
    let locations = inject("tmpLocations");
    let clusters = inject("Clusters");
    let Loading = inject("Loading");
    let TypoSettings = inject("TypoSettings");
    TypoSettings.mapColor = hexToRgba(TypoSettings.mapColor);
    let locationsLoaded = ref(false);
    let Location = inject("Location");
    let Cluster = inject("Cluster");
    let hoverLocation = ref(Object);
    let hoverCluster = ref(Object);
    let hoverActive = ref(false);
    let Center = inject("Center");
    let Pin = inject("Pin");
    let locationSelected = ref(false);
    let selectedDistance = inject("selectedDistance"); //default 5km
    const selectConditions = inject("ol-selectconditions");
    const selectCondition = selectConditions.pointerMove;

    // pin styles
    const radius = ref(5);
    const strokeColor = ref("white");
    const fillColor = ref(TypoSettings.pinColor);
    const radiusActive = ref(10);
    const strokeColorActive = ref("#fff");
    const fillColorActive = ref("#009fe3");
    const view = ref(null);
    const pinCoordinates = ref(fromLonLat([Pin.value.longitude, Pin.value.latitude]));

    /////////////////

    // border styles
    const borderURL = ref(
      "/typo3conf/ext/ibcontent/Resources/Public/dist/json/borderGermany.json"
    );
    const format = inject("ol-format");
    const geoJson = new format.GeoJSON();
    //////////////////

    const center = ref(fromLonLat([Center.value.longitude, Center.value.latitude]));
    const projection = ref("EPSG:3857");
    const zoom = ref(Center.value.zoomLevel);
    const rotation = ref(0);

    //cluster
    const highlightCluster = (event) => {
      if (event.selected.length == 1) {
        document.body.style.cursor = "pointer";
        hoverCluster.value = event.selected[0].values_.Cluster;
        hoverActive.value = true;
      } else {
        document.body.style.cursor = "default";
        hoverActive.value = false;
      }
    };

    const selectInteractionFilter = (feature) => {
      return feature.values_.Cluster != undefined;
    };

    const clusterSelected = (event) => {
      if (event.selected.length == 1) {
        Cluster.value = event.selected[0].values_.Cluster;
      } else {
        Cluster.value = new Object();
        Location.value = new Object();
      }
    };

    const createClusters = () => {
      clusters.value = new Object();
      Object.values(locations.value).forEach((location) => {
        if (
          clusters.value[
            location.Location.latitude + "-" + location.Location.longitude
          ] == undefined
        ) {
          clusters.value[
            location.Location.latitude + "-" + location.Location.longitude
          ] = {
            Geo: {
              Lat: location.Location.latitude,
              Lon: location.Location.longitude,
            },
            LocationIds: "",
            Locations: [],
          };
        }
        clusters.value[
          location.Location.latitude + "-" + location.Location.longitude
        ].Locations.push(location.Location);
        clusters.value[
          location.Location.latitude + "-" + location.Location.longitude
        ].LocationIds += ",-" + location.Location.id + "-";
      });
    };

    watch(locations, () => {
      locationsLoaded.value = true;
      createClusters();
    });

    watch(Pin.value, () => {
      pinCoordinates.value = fromLonLat([Pin.value.longitude, Pin.value.latitude]);

      view.value.animate({
        center: fromLonLat([Pin.value.longitude, Pin.value.latitude]),
        duration: 750,
      });
    });

    watch(Center.value, () => {
      zoom.value = Center.value.zoomLevel;
      view.value.animate({
        center: fromLonLat([Center.value.longitude, Center.value.latitude]),
        duration: 750,
      });
    });

    const checkClick = (event) => {
      if (Location.value > 1 && Cluster.value.Locations == undefined) {
        Location.value = new Object();
      }
    };

    return {
      TypoSettings,
      center,
      Center,
      Pin,
      pinCoordinates,
      projection,
      checkClick,
      clusters,
      highlightCluster,
      zoom,
      rotation,
      view,
      locations,
      selectedDistance,
      locationsLoaded,
      fromLonLat,
      selectCondition,
      radius,
      strokeColor,
      fillColor,
      radiusActive,
      strokeColorActive,
      fillColorActive,
      Location,
      locationSelected,
      Loading,
      clusterSelected,
      centerIcon,
      borderURL,
      geoJson,
      selectInteractionFilter,
      hoverLocation,
      hoverCluster,
      hoverActive,
    };
  },
};

function hexToRgba(hex) {
  var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result
    ? [parseInt(result[1], 16), parseInt(result[2], 16), parseInt(result[3], 16), 0.5]
    : null;
}
</script>

<style lang="scss">
.osmmap {
  width: 100%;
  height: 600px;

  .sliderLoading {
    top: 50%;
  }

  .ol-zoom {
    right: 0.5em;
    left: unset;
    top: unset;
    bottom: 2rem;

    button {
      width: 2rem;
      height: 2rem;
    }
  }
}
</style>
