<template>
  <div class="listViewItemContainer" :style="styleTileBorderColor">
    <div class="locationImage">
      <v-lazy-image
        v-if="location.Location.image != ''"
        :src="imageBaseURL + location.Location.image"
      />
      <v-lazy-image v-else :src="dummyLocationImage" />
    </div>
    <div class="locationName">
      <h2>
        <a
          class="listViewItemHeadline"
          :href="'/standort/' + location.Location.id"
          target="_blank"
          >{{ location.Location.name }}</a
        >
      </h2>
    </div>
    <div class="infoContainer">
      <div v-if="showDistance">
        <label>Entfernung: {{ location.Location.distance }} Km</label>
      </div>
      <div>
        <label>Kontakt:</label>
      </div>
      <div class="locationStreet">
        {{ location.Location.street }}
      </div>
      <div class="locationZipStreet">
        {{ location.Location.plz }} {{ location.Location.city }}
      </div>
      <div class="locationPhone" v-if="location.Location.phone_int.length > 5">
        Tel.:
        <a :href="'tel:' + location.Location.phone_int">{{
          location.Location.phone_int
        }}</a>
      </div>
      <div class="locationPhone" v-if="location.Location.fax.length > 5">
        Fax: {{ location.Location.fax }}
      </div>
      <div class="locationEmail" v-if="location.Location.email.length > 5">
        <a class="" @click="setMailAddress(encryptMailto(location.Location.email))">{{
          TypoSettings.emailLabel
        }}</a>
      </div>
    </div>
    <div class="locationLink" v-if="TypoSettings.showLinkButton == '0'">
      <a
        :style="styleLinkButton"
        class="ibCustomButton orange"
        :href="'/standort/' + location.Location.id"
        target="_blank"
        >Weitere Standortinformationen hier aufrufen!</a
      >
    </div>
  </div>
</template>

<script>
import { ref, inject, onMounted, reactive } from "vue";
import dummyLocationImage from "../../assets/locationFallback.jpg";
import VLazyImage from "v-lazy-image";

export default {
  name: "ListViewItem",
  props: {
    location: Object,
    emaillabel: String,
    showDistance: Boolean,
  },
  components: {
    "v-lazy-image": VLazyImage,
  },
  setup() {
    let imageBaseURL = inject("imageBaseURL");
    let TypoSettings = inject("TypoSettings");

    var emDialog = document.getElementById("eMailDialog");
    var emdCloseButton = document.getElementById("emdCloseButton");
    var showEmailAddress = document.getElementById("showEmailAddress");
    var btnOpenMailClient = document.getElementById("btnOpenMailClient");
    var btnShwoMail = document.getElementById("btnShwoMail");

    const styleLinkButton = reactive({
      background: TypoSettings.linkButtonColor,
    });
    const styleTileBorderColor = reactive({
      border: "1px solid " + TypoSettings.tileBorderColor,
    });

    const encryptMailto = (mailToEncrypt) => {
      var n = 0;
      var r = "";
      var o = getRandomInt(1, 10);
      for (var i = 0; i < mailToEncrypt.length; i++) {
        n = mailToEncrypt.charCodeAt(i);
        var code = n + o;
        if (code > 127) {
          code = code - 127;
        }
        r += String.fromCharCode(code);
      }
      return r + "#i3B1*" + o;
    };

    const decryptMailto = (s) => {
      var o = s.split("#i3B1*")[1];
      s = s.split("#i3B1*")[0];
      var n = 0;
      var r = "";

      for (var i = 0; i < s.length; i++) {
        n = s.charCodeAt(i);
        var code = n - o;

        if (code < 0) {
          code = code + 127;
        }

        r += String.fromCharCode(code);
      }
      return r;
    };

    const getRandomInt = (min, max) => {
      min = Math.ceil(min);
      max = Math.floor(max);
      return Math.floor(Math.random() * (max - min) + min); // The maximum is exclusive and the minimum is inclusive
    };

    const setMailAddress = (mail) => {
      emDialog.setAttribute("data-encryptedmail", mail);
      emDialog.style.display = "block";
    };

    const provideEmail = () => {
      emdCloseButton.addEventListener(
        "click",
        function (event) {
          emDialog.style.display = "none";
          showEmailAddress.innerHTML = "";
        },
        false
      );

      btnOpenMailClient.addEventListener(
        "click",
        function (event) {
          var mail = emDialog.getAttribute("data-encryptedmail");
          btnOpenMailClient.setAttribute("href", "mailto:" + decryptMailto(mail));
        },
        false
      );

      btnShwoMail.addEventListener(
        "click",
        function (event) {
          var mail = emDialog.getAttribute("data-encryptedmail");
          var decryptedMail = decryptMailto(mail);
          showEmailAddress.innerHTML = decryptedMail;
          //console.log("matomo event...");
          //window._paq.push(['trackEvent', 'Kontakt', 'Klick - Listenansicht', decryptedMail]);
        },
        false
      );
    };

    onMounted(() => {
      provideEmail();
    });

    return {
      setMailAddress,
      styleTileBorderColor,
      styleLinkButton,
      TypoSettings,
      encryptMailto,
      dummyLocationImage,
      imageBaseURL,
    };
  },
};
</script>

<style scoped lang="scss">
.listViewItemContainer {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  height: 100%;
  border: 1px solid #f18700;
  border-radius: 5px;
  box-shadow: 0 5px 10px rgb(2 2 2 / 20%);

  .locationName {
    h2 {
      font-size: 1rem;
      padding: 0.5rem 0;

      .listViewItemHeadline {
        text-decoration: none !important;
        color: #005590 !important;

        &:hover {
          color: #f18700 !important;
        }
      }
    }
  }

  .locationLink {
    display: flex;
    align-self: center;
    margin-top: auto;
    text-align: center;

    a {
      transition: all 0.3s;
      font-weight: bold;
    }

    a:hover {
      box-shadow: 0 10px 20px -10px rgb(88 49 0 / 50%);
    }
  }

  .infoContainer {
    margin-bottom: 1rem;
  }

  .infoContainerDesription {
    overflow: scroll;
  }
}
</style>
