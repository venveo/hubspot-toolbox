<template>
  <div class="flex" v-if="stores.value">
    <div class="select">
      <select :name="name" v-if="stores.value.length" v-model="internalValue">
        <option value="" disabled selected>Select a store</option>
        <option v-for="store in stores.value" :value="store.id">{{ store.label }}</option>
      </select>
    </div>
    <button class="btn" @click.prevent="this.showingStoreSetup = !this.showingStoreSetup">New Store</button>
  </div>
  <div v-if="showingStoreSetup">
    <div class="pane">
      <div class="field">
        <div class="heading">
          <label for="newStoreId">Store ID</label>
        </div>
        <input class="text fullwidth" v-model="newStore.id" type="text" id="newStoreId" :disabled="loading"/>
      </div>
      <div class="field">
        <div class="heading">
          <label for="newStoreLabel">Store Label</label>
        </div>
        <input class="text fullwidth" v-model="newStore.label" type="text" id="newStoreLabel" :disabled="loading"/>
      </div>
      <div class="field">
        <div class="heading">
          <label for="newStoreUrl">Store Admin URL</label>
        </div>
          <input class="text fullwidth" v-model="newStore.adminUri" type="text" id="newStoreUrl" :disabled="loading"/>
      </div>
      <div v-if="error">
        <p>{{error}}</p>
      </div>
      <button class="btn submit" type="submit" @click.prevent="saveStore" :disabled="loading">Save</button>
    </div>
  </div>
</template>

<script>
import ecommerce from "../api/ecommerce";
export default {
  name: 'StoreSelector',
  props: {
    stores: Array,
    value: String,
    name: String
  },
  data() {
    return {
      internalValue: this.value,
      loading: false,
      showingStoreSetup: false,
      newStore: {
        id: '',
        label: '',
        adminUri: Craft.baseCpUrl
      },
      error: null
    }
  },
  methods: {
    saveStore() {
      this.loading = true
      ecommerce.saveStore(this.newStore.id, this.newStore.label, this.newStore.adminUri).then((res) => {
        this.newStore = {
          id: '',
          label: '',
          adminUri: Craft.baseCpUrl
        }
        this.stores.value.push(res.data)
        this.showingStoreSetup = false

      }).catch((e) => {
        this.error = 'Oops! Something went wrong.'
      }).finally(() => {
        this.loading = false
      });
    }
  }
}
</script>
