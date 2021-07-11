<!--
  -  @link      https://www.venveo.com
  -  @copyright Copyright (c) 2020 Venveo
  -->

<template>
  <div class="flex">
    <button class="btn icon add" @click="showingPropertyPicker = !showingPropertyPicker">Add Property</button>
    <button class="btn icon search" :class="{'disabled': loadingNewPreview}" :disabled="loadingNewPreview" @click="updatePreview">New Preview</button>
    <button class="btn submit" @click.prevent="publishChanges">Publish Changes</button>
    <div class="flex-grow"></div>
    <div v-if="sourceTypeName"><label>{{sourceTypeName}}</label></div>
    <div v-if="sourceTypes" class="select">
      <select v-model="sourceTypeId" @change="handleSourceTypeChanged">
        <option :value="null">Default</option>
        <option v-for="(sourceType) in sourceTypes" :value="sourceType.id">{{sourceType.displayName}}</option>
      </select>
    </div>
  </div>
  <div v-if="showingPropertyPicker">
    <div class="pane">
      <div class="flex">
      <div class="select">
        <select v-model="selectedPropertyToAdd">
          <option disabled selected value="">Select Property</option>
          <option v-for="(data, name) in propertiesFromApi" :value="name" :disabled="mappedPropertyNames.includes(name)">{{ data.label }} - ({{ name }})</option>
        </select>
      </div>
      <div>
        <button class="btn icon add" :class="{'disabled': loadingAddProperty, 'loading': loadingAddProperty}" :disabled="loadingAddProperty" @click.prevent="handleAddProperty">Add</button>
      </div>
      </div>
      <div v-if="selectedPropertyToAdd" class="readable">
        <p>{{properties[selectedPropertyToAdd].description}}</p>
      </div>
    </div>
    <hr>
  </div>
  <div class="input ltr">
    <table id="sites" class="editable fullwidth">
      <thead>
      <tr>
        <th scope="col" class="heading-cell thin">HubSpot Property</th>
        <th scope="col" class="singleline-cell textual" style="">Object Template</th>
        <th scope="col" class="singleline-cell textual" style="">Preview</th>
        <th colspan="1"></th>
      </tr>
      </thead>
      <tbody>
      <mapped-property v-for="(mapping, index) in propertyMappings"
                       v-model:template="propertyMappings[index].template"
                       :property="propertiesFromApi[mapping.property.name]"
                       :preview="previewData[mapping.property.name]"
                       v-on:input="(e) => {handleTemplateChange(propertyMappings[index], index)}"
                       v-on:delete="deletePropertyMapping(propertyMappings[index])"
      />
      </tbody>
    </table>
  </div>
</template>

<script>
import api from '../../api/ecommerce.js'
import MappedProperty from "./MappedProperty.vue";
import {debounce, omitBy} from "lodash"

export default {
  name: 'FieldMapper',
  components: {MappedProperty},
  props: {
    mapper: String
  },
  data() {
    return {
      showingPropertyPicker: false,

      propertyMappings: [],

      propertiesFromApi: {},
      previewData: {},

      selectedPropertyToAdd: '',
      previewObjectId: null,

      loadingNewPreview: false,
      loadingAddProperty: false,

      sourceTypeId: null,

      sourceTypes: null,
      sourceTypeName: null
    }
  },
  mounted() {
    this.fetchMappings()
  },
  computed: {
    mappedPropertyNames: function() {
      return this.propertyMappings.map(function(mapping) {
        return mapping.property.name
      }, this.propertyMappings)
    }
  },
  methods: {
    async fetchMappings() {
      const {data} = await api.getMappings(this.mapper, this.sourceTypeId, this.previewObjectId)
      this.propertiesFromApi = omitBy(data.propertiesFromApi, prop => prop.readOnlyValue)
      this.propertyMappings = data.propertyMappings
      if (Object.keys(data).includes('sourceTypes')) {
        this.sourceTypes = data.sourceTypes
        this.sourceTypeName = data.sourceTypeName
      }
      if (Object.keys(data).includes('previewData')) {
        this.previewObjectId = data.previewData.previewObjectId
        this.previewData = data.previewData.preview
      }
    },
    handleSourceTypeChanged: async function() {
      await this.fetchMappings();
    },
    handleAddProperty: async function () {
      this.loadingAddProperty = true
      const mapping = {
        property: this.selectedPropertyToAdd,
      }

      const property = {
        name: this.propertiesFromApi[this.selectedPropertyToAdd].name,
        dataType: this.propertiesFromApi[this.selectedPropertyToAdd].type,
      }
      await api.saveMapping(mapping, this.mapper, this.sourceTypeId, property, this.previewObjectId);
      await this.fetchMappings();
      this.loadingAddProperty = false
      this.selectedPropertyToAdd = '';
    },
    async publishChanges() {
      await api.publishObjectMappings(this.mapper, this.sourceTypeId);
      await this.fetchMappings();
      alert('Published');
    },
    handleTemplateChange: debounce(function(mapping, index) {
      api.saveMapping(mapping, this.mapper, this.sourceTypeId, null, this.previewObjectId).then(v => {
        const mappingData = v.data
        if (Object.keys(mappingData).includes('previewData')) {
          this.previewData[mapping.property.name] = mappingData.previewData.preview
          this.propertyMappings[index].id = mappingData.id
        }
      });
    }, 250),
    async deletePropertyMapping(mapping) {
      await api.deleteMapping(mapping.id);
      await this.fetchMappings();
    },
    async updatePreview() {
      this.loadingNewPreview = true
      this.previewObjectId = null
      await this.fetchMappings()
      this.loadingNewPreview = false
    }
  }
}
</script>
