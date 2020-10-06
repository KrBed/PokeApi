<template>
  <div id="login" class="offset-4 mt-5">
    <div class="card col-4">
      <div class="card-body">
        <h5 class="card-title">Create new Account</h5>
        <form @submit.prevent="register">
          <div class="form-group">
            <label for="email">Email address</label>
            <input
              type="email"
              class="form-control"
              name="email"
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
          <div class="form-group form-check">
            <input
              type="checkbox"
              class="form-check-input"
              id="exampleCheck1"
            />
            <label class="form-check-label" for="exampleCheck1"
              >Check me out</label
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

export default {
  name: "Register",
  data() {
    return {
      input: {
        email: "",
        password: ""
      }
    };
  },
  methods: {
    register() {
      if (this.input.email !== "" && this.input.password !== "") {
        axios({
          method: "POST",
          url: "http://localhost/pokemon_api/system.php/register",
          data: this.input,

        })
          .then(function(response) {
            console.log(response.data);
            if (response.data[0].status === 1) {
              alert("Login Successfully");
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
