<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Flights</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
        @font-face {
            font-family: 'CustomFont';
            src: url('/assets/Beautiful_Police_Officer.otf') format('truetype');
            font-weight: 200;
            font-style: normal;
        }

        .font-custom {
            font-family: 'CustomFont', sans-serif;
        }
    </style>

</head>

<body class="bg-black font-custom">
    <div class="container mx-auto mt-8 p-6 bg-black rounded-lg shadow">
        <h1 class="text-2xl font-semibold text-green-600 mb-4 shadow-2xl text-shadow">Today's Flights</h1>

        <div id="current-time" class="text-lg mb-4 font-semibold text-green-600"></div>

        <div id="flight-list" class="space-y-4 text-red-800 list-none"></div>

        <div id="error-message" class="text-red-500 mb-4 hidden text-red-800"></div>

        <div id="no-flights-message" class="text-red-800 hidden">
            <p>No flights available for today.</p>
        </div>
    </div>



    <script>
        function formatDate(date) {
            const day = date.getDate();
            const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const weekdayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

            // Get the suffix for the day
            const suffixes = ["th", "st", "nd", "rd"];
            const suffix = (day % 10 > 3 || (day % 100 >= 11 && day % 100 <= 13)) ? suffixes[0] : suffixes[day % 10];

            return `${day}${suffix} ${weekdayNames[date.getDay()]}, ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
        }

        function updateCurrentTime() {
            const currentTimeElement = document.getElementById('current-time');
            const currentTime = new Date();
            currentTimeElement.textContent = `Current Time: ${currentTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
        }

        function formatTime(timeString) {
            // Convert time string like '1000' to '10:00 AM'
            const hours = parseInt(timeString.substring(0, 2), 10);
            const minutes = timeString.substring(2, 4);
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const formattedHours = hours % 12 || 12; // Convert to 12-hour format
            return `${formattedHours}:${minutes} ${ampm}`;
        }

        function fetchFlights() {
            fetch('radar.php?ajax=true')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("API Response:", data); // Log the entire API response
                    const flightList = document.getElementById('flight-list');
                    const errorMessage = document.getElementById('error-message');
                    const noFlightsMessage = document.getElementById('no-flights-message');

                    flightList.innerHTML = '';
                    errorMessage.classList.add('hidden');
                    noFlightsMessage.classList.add('hidden');

                    if (data.error) {
                        errorMessage.textContent = data.error;
                        errorMessage.classList.remove('hidden');
                    } else if (data.flights.length > 0) {
                        // Get today's date in 'dS D, M Y' format
                        const todayDate = formatDate(new Date());
                        console.log("Today's Formatted Date:", todayDate); // Log today's formatted date

                        // Filter flights for today using the same logic as PHP
                        const todayFlights = data.flights.filter(flight => flight.flightDate === todayDate);

                        console.log("Filtered Flights:", todayFlights); // Log filtered flights for debugging

                        if (todayFlights.length > 0) {
                            todayFlights.forEach(flight => {
                                const flightItem = document.createElement('li');
                                flightItem.className = 'p-4 border rounded-lg shadow-sm bg-black';

                                // Calculate time differences
                                const current_time = Math.floor(Date.now() / 1000); // Current time in seconds
                                const depTimeStr = formatTime(flight.depTime);
                                const desTimeStr = formatTime(flight.desTime);

                                // Parse times to timestamps
                                const depTime = new Date(`${flight.flightDate} ${depTimeStr}`).getTime() / 1000; // Convert to timestamp
                                const desTime = new Date(`${flight.flightDate} ${desTimeStr}`).getTime() / 1000; // Convert to timestamp

                                let depTimeDifference, desTimeDifference;
                                let isBlinking = false;

                                // Calculate departure time difference

                                flightItem.innerHTML = `
                                             <h2 class="text-lg  font-semibold text-green-600 shadow-2xl text-shadow">Flight Date : ${flight.flightDate}</h2>
                                             <p class="font-semibold text-green-600	 shadow-2xl text-shadow ">Departure Time :</strong> ${depTimeStr} ,
	                                          Destination Time :</strong> ${desTimeStr} ,
                                              Flight No :</strong> ${flight.flightNo || 'N/A'}</p>`;


                                if (isBlinking) {
                                    flightItem.classList.add('blinking');
                                }

                                flightList.appendChild(flightItem);
                            });
                        } else {
                            noFlightsMessage.classList.remove('hidden');
                        }
                    } else {
                        noFlightsMessage.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error fetching flights:', error);
                    const errorMessageElement = document.getElementById('error-message');
                    errorMessageElement.textContent = 'Error fetching flights: ' + error.message;
                    errorMessageElement.classList.remove('hidden');
                });
        }

        // Fetch flights on page load
        fetchFlights();

        // Update current time every second
        setInterval(updateCurrentTime, 1000);

        // Fetch flights every minute
        setInterval(fetchFlights, 60000);

        // Initial call to display current time immediately
        updateCurrentTime();
    </script>
</body>

</html>