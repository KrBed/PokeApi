<template>
  <div id="login" class="offset-4 mt-5">
    <div class="card col-4">
      <div class="card-body">
        <h5 class="card-title">Login to your account</h5>
        <form @submit.prevent="login">
          <div class="form-group">
            <label for="email">Email address</label>
            <input
              type="email"
              class="form-control"
              id="email"
              aria-describedby="emailHelp"
              v-model="input.email"
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
        email: "",
        password: ""
      }
    };
  },
  methods: {
    login() {
      if (this.input.email !== "" && this.input.password !== "") {
        axios({
          method: "POST",
          url: "http://localhost/pokemon_api/system.php/login",
          data: this.input
        })
          .then(function(response) {
            console.log(response.data);
            if (response.data.status === "OK") {
              router.push({path:'/'})
            } else {
              alert("User does not exist");
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
}
</style>
