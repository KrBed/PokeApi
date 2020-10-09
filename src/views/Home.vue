<template>
  <div class="">
    <div class="container-fluid pt-4 h-100">
      <div class="row actions-list">
        <div class="col-12 col-md-5 d-flex flex-column align-items-center" style="">
          <div>
            <h2 class="text-center">Enter creature name to search</h2>
            <form @submit.prevent="search">
              <div class="form-row">
                <div class="offset-1 col-8">
                  <input
                    v-model="input.search"
                    type="text"
                    class="form-control"
                    id="search"
                    placeholder="Search"
                  />
                </div>
                <button
                  type="submit"
                  class="btn btn-primary mb-2"
                  :disabled="input.search === ''"
                >
                  Search
                </button>
              </div>
            </form>
          </div>

          <div class="pt-5">
            <div v-if="searchResult" class="card" style="width: 18rem;">
              <img
                class="card-img-top"
                :src="creature.picture"
                :alt="'Picture of ' + creature.name"
              />
              <div class="card-body">
                <h5 class="card-title">Name : {{ creature.name }}</h5>
                <h5>Moves : {{ creature.moves }}</h5>
              </div>
            </div>
            <div v-if = "!searchResult"  class="pt-5">
              <h2>{{searchMessage}}</h2>
            </div>
          </div>
        </div>

        <div class="col ">
          <div class="card  overflow-auto" style="height:90%">

            <div class="card-body" style="overflow-y:auto;">
              <table class="table table-striped overflow-auto">
                <thead>
                <tr>
                  <th scope="col">Login</th>
                  <th scope="col">Searched</th>
                  <th scope="col">Search result</th>
                  <th scope="col">Date</th>
                  <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody style="overflow-y:auto;">
                <tr v-for="item in actions" :key="item.id">
                  <td>{{ item.login }}</td>
                  <td>{{ item.searched }}</td>
                  <td>{{ item.search_result }}</td>
                  <td>{{ item.date_time }}</td>
                  <td class="text-danger">
                    <a
                      v-if="item.user_id === user_id"
                      @click="deleteAction(item.id)"
                    ><i class="fa fa-trash"
                    /></a>
                  </td>
                </tr>
                </tbody>
              </table>

            </div>
          </div>



<!--          <table class="table table-striped overflow-auto">-->
<!--            <thead>-->
<!--              <tr>-->
<!--                <th scope="col">Login</th>-->
<!--                <th scope="col">Searched</th>-->
<!--                <th scope="col">Search result</th>-->
<!--                <th scope="col">Date</th>-->
<!--                <th scope="col">Action</th>-->
<!--              </tr>-->
<!--            </thead>-->
<!--            <tbody style="overflow-y:auto;">-->
<!--              <tr v-for="item in actions" :key="item.id">-->
<!--                <td>{{ item.login }}</td>-->
<!--                <td>{{ item.searched }}</td>-->
<!--                <td>{{ item.search_result }}</td>-->
<!--                <td>{{ item.date_time }}</td>-->
<!--                <td class="text-danger">-->
<!--                  <a-->
<!--                    v-if="item.user_id === user_id"-->
<!--                    @click="deleteAction(item.id)"-->
<!--                    ><i class="fa fa-trash"-->
<!--                  /></a>-->
<!--                </td>-->
<!--              </tr>-->
<!--            </tbody>-->
<!--          </table>-->
        </div>
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
      },
      creature: null,
      actions: [],
      searchMessage: "",
      searchResult: false
    };
  },
  computed: {
    user_id() {
      return this.$parent.user_id;
    }
  },
  mounted() {
    this.getActions();
  },
  methods: {
    search() {
      axios({
        withCredentials: true,
        method: "POST",
        data: this.input,
        url: "http://localhost/pokemon_api/system.php/search"
      })
        .then(response => {
          console.log(response.data);
          if(response.data.creature !== null){
            this.creature = response.data;
            this.searchResult = true;
          }else{
            this.searchResult = false;
            this.creature = null;
            this.searchMessage = response.data.message;
          }
          this.getActions();
        })
        .catch(function(error) {
          console.log(error);
        });
    },
    getActions() {
      axios({
        withCredentials: true,
        method: "POST",
        data: this.input,
        url: "http://localhost/pokemon_api/system.php/actions"
      })
        .then(response => {
          this.actions = response.data;
        })
        .catch(function(error) {
          console.log(error);
        });
    },
    deleteAction(actionId) {
      axios({
        withCredentials: true,
        method: "POST",
        data: actionId,
        url: "http://localhost/pokemon_api/system.php/delete"
      })
        .then(() => {
          this.getActions();
        })
        .catch(function(error) {
          console.log(error);
        });
    }
  }
};
</script>
<style scoped>

</style>
