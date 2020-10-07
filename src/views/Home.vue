<template>
  <div class="home">
    <div class="container-fluid">
      <div class="row">
        <div class="col">
          <h2>Enter creature name to search</h2>
          <form class="form-inline" @submit.prevent="action">
            <div class="form-group mx-sm-3 mb-2">
              <label for="search" class="sr-only">Search</label>
              <input
                v-model="input.search"
                type="text"
                class="form-control"
                id="search"
                placeholder="Password"
              />
            </div>
            <button type="submit" class="btn btn-primary mb-2">
              Search
            </button>
          </form>
        </div>
        <div class="col"></div>
      </div>
    </div>
  </div>
</template>

<script>
// @ is an alias to /src

import axios from "axios";

export default {
  name: "Home",
  data() {
    return {
      input: {
        search: ""
      }
    };
  },
  methods: {
    action: function() {
      axios({
        // withCredentials:true,
        method: "POST",
        data: this.input,
        url: "http://localhost/pokemon_api/system.php/action"
      })
        .then(function(response) {
          console.log(response);
        })
        .catch(function(error) {
          console.log(error);
        });
    }
  }
};
</script>
