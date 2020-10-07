<template>
  <div id="register-template" class="offset-4 mt-5">
    <div class="card col-4">
      <div class="card-body">
        <h5 class="card-title">Create new Account</h5>
        <form @submit.prevent="register">
          <div class="form-group">
            <label for="login">Login</label>
            <input
              type="text"
              class="form-control"
              name="login"
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
          <button type="submit" class="btn btn-primary">
            Submit
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import router from "../router";

export default {
  name: "Register",
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
    register() {
      let self = this;
      if (this.input.login !== "" && this.input.password !== "") {
        axios({
          // withCredentials:true,
          method: "POST",
          url: "http://localhost/pokemon_api/system.php/register",
          data: this.input
        })
          .then(function(response) {
            if (response.data.status === "ERROR") {
              self.errorMessage = response.data.message;
            } else {
              router.push({ path: "/" });
            }
          })
          .catch(function(error) {
            console.log(error);
          });
      } else {
        self.errorMessage = "Please enter login and password";
      }
    }
  }
};
</script>

<style scoped>
button {
  width: 100%;
}
</style>
