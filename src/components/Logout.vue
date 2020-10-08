<template><div></div></template>

<script>
import axios from "axios";
import router from "../router";

export default {
  name: "Logout",

  mounted() {
    axios({
      withCredentials: true,
      method: "GET",
      url: "http://localhost/pokemon_api/system.php/logout"
    })
      .then(function(response) {
        if (response.data.status === "OK") {
          console.log(response);
          router.push({
            path: "/login"
          });
        }
      })
      .catch(function(error) {
        console.log(error);
      });
  }
};
</script>
