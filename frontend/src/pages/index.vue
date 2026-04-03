<template>
  <StationDropdown
    @select-station="fetchArrivalsForStation"
  />
  <ArrivalsTable ref="arrivalsTable" />
</template>

<script setup lang="ts">
  //
  import { ref } from 'vue';

  const arrivalsTable = ref();
  const fetchArrivalsForStation = async (station: string): Promise<void> => {
    await arrivalsTable.value.fetchStationArrivals(station);
  }
</script>
