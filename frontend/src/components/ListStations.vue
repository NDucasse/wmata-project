<template>
  <v-container class="fill-height" max-width="900">
    <div>
      <v-row>
        <v-col cols="12">
          <v-card
            class="py-4"
            color="surface-variant"
            image="https://cdn.vuetifyjs.com/docs/images/one/create/feature.png"
            rounded="lg"
            variant="tonal"
          >
            <template #title>
              <label for="station-select">Choose a station:</label>
              <br>
              <select id="station-select" v-model="selectedStation" @change="fetchStationDetails">
                <option disabled value="">Please select one</option>jh
                <option v-for="station in stationList" :key="station" :value="station">
                  {{ station }}
                </option>
              </select>
            </template>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </v-container>
</template>

<script setup lang="ts">
  import axios from 'axios';
  import { ref } from 'vue';

  const selectedStation = ref('');
  const stationList = ref([]);

  const fetchStationsList = async () => {
    try {
      const response = await axios.get('http://localhost:8000/station-list');
      if (response.status !== 200) {
        return;
      }
      stationList.value = response.data;
    } catch (e) {
      console.error('Error fetching station list', e);
    }
  };
  fetchStationsList();

  const fetchStationDetails = async () => {
    console.log(`Fetching data for country code: ${selectedStation.value}`)
  }
</script>
