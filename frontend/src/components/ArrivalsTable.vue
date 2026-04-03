<template>
  <v-data-table
    ref="arrivalsTable"
    v-model="arrivalsSortBy"
    fixed-header
    :headers="arrivalsHeaders"
    :items="upcomingArrivals"
    :items-per-page="15"
  />
</template>

<script setup lang="ts">
  import { ref } from 'vue';
  import axios from 'axios';

  const upcomingArrivals = ref([]);

  const arrivalsSortBy = ref([{ key: 'line', order: 'asc' }])
  const arrivalsHeaders = [
    {
      'title': 'Arrival Time',
      'key': 'arrivalTime',
      'width': '500px',
    },
    {
      'title': 'Line',
      'key': 'line',
      'width': '500px',
    },
    {
      'title': 'Destination',
      'key': 'destination',
      'width': '500px',
    },
    {
      'title': 'Cars on Train',
      'key': 'cars',
      'width': '500px',
    },
  ]
  const fetchStationArrivals = async (station: string): Promise<void> => {
    try {
      const stationCodesResponse = await axios.get(`http://localhost:8000/stations/station-codes/${station}`)
      const stationCodesString = stationCodesResponse.data;

      const response = await axios.get(`http://localhost:8000/arrivals/next-arrivals/${stationCodesString}`)
      upcomingArrivals.value = response.data;
    } catch (e) {
      console.error('Error fetching station details', e);
    }
  }

  defineExpose({ fetchStationArrivals })
</script>
