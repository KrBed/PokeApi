<template>
  <div id="login-template" class="row mt-5">
    <div class="card col-12 col-sm-6 offset-sm-3 offset-xl-5 col-xl-2">
      <div class="card-body">
        <h5 class="card-title">Login to your account</h5>
        <form @submit.prevent="login">
          <div class="form-group">
            <label for="login">Login</label>
            <input
              type="text"
              class="form-control"
              id="login"
              aria-describedby="loginHelp"
              v-model="input.login"
            />
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input
              type="password"
              class="form-control"
              id="password"
              v-model="input.password"
            />
          </div>
          <div class="row">
            <strong
              ><p class="text-danger">{{ errorMessage }}</p></strong
            >
          </div>
          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import router from "../router";
export default {
  name: "Login",
  data() {
    return {
      input: {
        login: "",
        password: ""
      },
      errorMessage: ""
    };
  },
  methods: {
    login() {
      let self = this;
      if (this.input.login !== "" && this.input.password !== "") {
        axios({
          withCredentials: true,
          method: "POST",
          url: "http://localhost/pokemon_api/system.php/login",
          data: this.input
        })
          .then(function(response) {
            console.log(response);
            if (response.data.status === "OK") {
              router.push({ path: "/" });
            } else if (response.data.status === "ERROR") {
              self.errorMessage = response.data.message;
            }
          })
          .catch(function(error) {
            console.log(error);
          });
      } else {
        alert("Please enter username & password");
      }
    }
  }
};
</script>

<style scoped>
button {
  width: 100%;
  background-color: #726ae6;
}
</style>
