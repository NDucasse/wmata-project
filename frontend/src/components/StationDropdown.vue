<template>
  <v-container max-width="900">
    <div>
      <v-row>
        <v-col cols="12">
          <v-autocomplete
            v-model="selectedStation"
            v-model:menu="menu"
            :items="stationList"
            label="Choose a station"
            variant="solo-filled"
            @update:model-value="$emit('select-station', selectedStation)"
          >
            <template #item="{ props }">
              <v-list-item v-bind="props" />
            </template>
          </v-autocomplete>
        </v-col>
      </v-row>
    </div>
  </v-container>
</template>

<script setup lang="ts">
  defineEmits(['select-station']);

  import axios from 'axios';
  import { ref, shallowRef } from 'vue';

  const selectedStation = ref('');
  const stationList = ref(['']);

  const menu = shallowRef(false)

  /**
   * Gets alphabetized list of stations from backend and displays them.
   */
  const fetchStationsList = async () => {
    try {
      const response = await axios.get('http://localhost:8000/stations/station-list');
      if (response.status !== 200) {
        return;
      }
      stationList.value = response.data;
    } catch (e) {
      console.error('Error fetching station list', e);
    }
  };

  fetchStationsList();
</script>
