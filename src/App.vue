<template>
  <div id="app">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark static-top">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">Start Bootstrap</a>
        <button
          class="navbar-toggler"
          type="button"
          data-toggle="collapse"
          data-target="#navbarResponsive"
          aria-controls="navbarResponsive"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
          <ul class="navbar-nav ml-auto">
            <li v-if="user_id === null" class="nav-item active">
              <router-link class="nav-link" to="/login">Login</router-link>
            </li>
            <li v-if="user_id === null" class="nav-item">
              <router-link class="nav-link" to="/register"
                >Register
              </router-link>
            </li>
            <li v-if="user_id !== null" class="nav-item active">
              <router-link class="nav-link" to="/logout">Logout</router-link>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <router-view />
  </div>
</template>

<script>
// @ is an alias to /src

import axios from "axios";
import router from "./router";

export default {
  name: "App",
  data() {
    return {
      user_id: null
    };
  },
  methods: {
    checkUserId() {
      axios({
        withCredentials: true,
        method: "GET",
        url: "http://localhost/pokemon_api/system.php/me"
      })
        .then(response => {
          if (response.data) {
            if (response.data.status === "OK") {
              this.user_id = response.data.user_id;
              if (this.$route.path !== "/") {
                router.push({ path: "/" });
              }
            } else {
              this.user_id = null;
              if (this.$route.path === "/") {
                router.push({ path: "/login" });
              }
            }
          }
        })

        .catch(function(error) {
          console.log(error);
        });
    }
  },
  watch: {
    $route() {
      this.checkUserId();
    }
  },
  created() {
    this.checkUserId();
  }
};
</script>

<style>
#app {
}

#nav {
  padding: 30px;
}

#nav a {
  font-weight: bold;
  color: #2c3e50;
}

#nav a.router-link-exact-active {
  color: #42b983;
}
</style>
