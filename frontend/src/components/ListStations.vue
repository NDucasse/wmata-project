<template>
  <v-container max-width="900">
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
  <v-data-table
    v-model="upcomingSortBy"
    fixed-header
    :headers="upcomingTrainsHeaders"
    :items="upcomingTrains"
    :items-per-page="15"
  >
    <template #top>
      <v-number-input
        v-model="upcomingItemsPerPage"
        class="pa-2"
        hide-details
        label="Items per page"
        :max="20"
        :min="-1"
      />
    </template>
  </v-data-table>
</template>

<script setup lang="ts">
  import axios from 'axios';
  import { ref } from 'vue';

  const selectedStation = ref('Select A Station');
  const stationList = ref(['']);

  const upcomingSortBy = ref([{ key: 'line', order: 'asc' }])
  const upcomingItemsPerPage = ref(15);
  const upcomingTrainsHeaders = [
    {
      'title': 'Arrival Time',
      'key': 'arrTime',
      'width': '500px',
    },
    {
      'title': 'Line',
      'key': 'line',
      'width': '500px',
    },
    {
      'title': 'Destination',
      'key': 'dest',
      'width': '500px',
    },
    {
      'title': 'Cars on Train',
      'key': 'cars',
      'width': '500px',
    },
  ]
  const upcomingTrains = ref([{}]);

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

  const fetchStationDetails = async () => {
    const response = await axios.get(`http://localhost:8000/next-arrivals/${selectedStation.value}`)

    const timeShiftedData: Record<string, string>[] = []
    const now = new Date().getTime()

    // TODO: put this processing in backend
    response.data.forEach((arrival: Record<string, string>) => {
      let arrivalTimestamp: string = '';
      const minToArrival = arrival['minToArrival']

      const timestampOptions: Intl.DateTimeFormatOptions = {
        hour: 'numeric',
        minute: 'numeric',
        hour12: true,
      };

      switch (minToArrival) {
        case 'BRD':
          arrivalTimestamp = 'Boarding';
          break;
        case 'ARR':
          arrivalTimestamp = 'Arriving';
          break;
        case '---': // '---' and '' both should result in 'Unknown'
        case '':
          arrivalTimestamp = 'Unknown';
          break;
        default: // Any other expected result will be a numerical (integer) value
          const arrivalTime: Date = new Date(now + parseInt(minToArrival) * 60000);
          arrivalTimestamp = new Intl.DateTimeFormat('en-us', timestampOptions).format(arrivalTime);
      }

      timeShiftedData.push({
        'line': arrival['line'],
        'dest': arrival['destination'],
        'arrTime': arrivalTimestamp,
        'cars': arrival['cars'] || 'Unknown',
      })
    })
    upcomingTrains.value = timeShiftedData
  }

  fetchStationsList();
</script>
