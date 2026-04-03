<template>
  <StationDropdown
    @select-station="fetchStationArrivals"
  />
  <ArrivalsTable ref="arrivalsTable" />
</template>

<script setup lang="ts">
  //
  import { ref } from 'vue';

  const arrivalsTable = ref();
  /**
   * Method for getting selected station data from StationDropdown
   * to the ArrivalsTable.
   * @param station
   */
  const fetchStationArrivals = async (station: string): Promise<void> => {
    await arrivalsTable.value.fetchStationArrivals(station);
  }
</script>
