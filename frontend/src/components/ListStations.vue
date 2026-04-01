<template>
  <v-container class="fill-height" max-width="900">
    <div>
      <v-row>
        <v-col cols="12">
          <v-select
            v-model="selectedStation"
            :items="stationList"
            label="Choose a station"
            variant="solo-filled"
            @update:model-value="fetchStationDetails"
          />
        </v-col>
      </v-row>
    </div>
  </v-container>
</template>

<script setup lang="ts">
  import axios from 'axios';
  import { ref } from 'vue';

  const selectedStation = ref('Select A Station');
  const stationList = ref(['']);

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
    console.log(`Fetching data for station: ${selectedStation.value}`)
    const response = await axios.get(`http://localhost:8000/next-arrivals/${selectedStation.value}`)
    console.log(response);
  }
</script>
