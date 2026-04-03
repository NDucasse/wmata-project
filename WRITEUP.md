
### Please include a service overview including data types, service interactions, and expected usage.

Regarding data types, I don't know enough PHP to know the best way to create custom
data wrappers for the received WMATA API data. Were this written in JS/TS
I would create custom wrapper data type such as the following for Arrivals:
```
type Arrival = {
    destination: string;
    line: string;
    min: string;
    cars: int;
};
```
I would then expect the WMATA API to return a list that would be typecast to
a list of Arrivals objects which would then be easier to control and manipulate
than a more generic list with no assurances on the structure.

<br>

Regarding service interactions, the frontend calls three endpoints on the backend:

- /stations/station-list/
- /stations/station-codes/{stationNames}
- /arrivals/next-arrivals/{stationCodes}

The backend calls two WMATA API endpoints:

- /Rail.svc/json/jStations/
- /StationPrediction.svc/json/GetPrediction/{stationCodes || 'All'}

There is a flow diagram uploaded to the repo alongside this writeup called Flowchart.png
which shows all the interactions between the frontend and backend, as well as between
the backend and WMATA API.

<br>

The expected usage of this system is single-digit users checking the upcoming train schedule
for their closest station. If it were to be scaled to a larger userbase, a more robust caching
mechanism should be implemented to avoid excess calls to the WMATA API.

The primary flow expects a user to select a station from the dropdown list and view the
upcoming arrivals data presented to them, and possibly select another station or two from the
list to view after the first before leaving the site.

### Please include an outline of how you would think about testing this code.

The backend is broken down in such a way that each function should be independently testable.
Each Service object file would have its own associated unit test file.
The functions which call the WMATA API would need to have the APIService object mocked 
so it would not hit the actual API, and feed the tests some useful mock data to verify the
data transformation logic. As the service scales, more robust testing such as smoke testing
and/or latency testing may be needed, as this would be a user-facing application where slow
load times matter due to the nature of the userbase (riding public transit).

I am not very familiar with how frontends are tested as most of my experience has been in backend
work, but I imagine the base concept is similar in terms of using mock data to simulate the API calls
and verify the correct expected data is being produced to the client.
