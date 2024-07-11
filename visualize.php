<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Environment Sensor Data - Visualizations</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>

        body{
            background-color: #abdbe3;
        }

        .navbar{
            background-color: #154c79;
        }
        .chart-container {
            width: 100%;
            height: 400px; /* Adjust the height as needed */
            margin-bottom: 30px;
        }

        .center-content {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .data-analysis {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .data-analysis .card {
            margin-bottom: 20px;
        }

        .data-analysis .card-body {
            text-align: center;
        }

        .alert-container {
            width: 80%;
            margin-bottom: 20px;
        }
    </style>
    <script>
        // Function to refresh the page every 40 seconds
        function autoRefresh() {
            setInterval(function() {
                location.reload();
            }, 40000);
        }

        function categorizeTemperature(value) {
            if (value <= 15) return { label: 'Very Cold', color: '#00f' };
            if (value <= 24) return { label: 'Cold', color: '#0ff' };
            if (value <= 30) return { label: 'Normal', color: '#0f0' };
            if (value <= 35) return { label: 'Warm', color: '#ff0' };
            return { label: 'Hot', color: '#f00' };
        }

        function categorizeHumidity(value) {
            if (value <= 30) return { label: 'Low', color: '#0ff' };
            if (value <= 50) return { label: 'Normal', color: '#0f0' };
            if (value <= 70) return { label: 'High', color: '#ff0' };
            return { label: 'Very High', color: '#f00' };
        }

        function categorizeAirQuality(value) {
            if (value <= 300) return { label: 'Good', color: '#0f0' };
            if (value <= 500) return { label: 'Unhealthy for Sensitive Groups', color: '#ff0' };
            return { label: 'Hazardous', color: '#f00' };
        }
    </script>
</head>
<body onload="autoRefresh()">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Dashboard</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="http://localhost/EnvSensor/table.php">Data Table</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="http://localhost/EnvSensor/visualize.php">Visualizations</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5 center-content">
        <h1 class="mb-4 text-center">Environment Sensor Data Visualizations</h1>
        <div class="alert-container" id="alertContainer"></div>

        <div class="card mb-4" style="width: 80%;">
            <div class="card-body">
                <h3 class="card-title text-center">Temperature (°C)</h3>
                <div class="chart-container">
                    <canvas id="temperatureChart"></canvas>
                </div>
                <div class="data-analysis" id="temperatureAnalysis"></div>
            </div>
        </div>
        <div class="card mb-4" style="width: 80%;">
            <div class="card-body">
                <h3 class="card-title text-center">Humidity (%)</h3>
                <div class="chart-container">
                    <canvas id="humidityChart"></canvas>
                </div>
                <div class="data-analysis" id="humidityAnalysis"></div>
            </div>
        </div>
        <div class="card mb-4" style="width: 80%;">
            <div class="card-body">
                <h3 class="card-title text-center">Air Quality (PPM)</h3>
                <div class="chart-container">
                    <canvas id="airQualityChart"></canvas>
                </div>
                <div class="data-analysis" id="airQualityAnalysis"></div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var highestTemperature = -Infinity;
            var lowestTemperature = Infinity;
            var highestHumidity = -Infinity;
            var lowestHumidity = Infinity;
            var highestAirQuality = -Infinity;
            var lowestAirQuality = Infinity;
            var initialLoad = true;

            fetchData();

            function fetchData() {
                $.get('http://localhost/EnvSensor/data.php', function(data) {
                    console.log('Data fetched:', data); // Debugging: Log fetched data
                    var temperatures = [];
                    var humidities = [];
                    var airQualities = [];
                    var timestamps = [];

                    data.forEach(function(row) {
                        var timestamp = new Date(row.timestamp).toLocaleString('en-US', { timeZone: 'Asia/Kuala_Lumpur' });
                        temperatures.push(parseFloat(row.temperature));
                        humidities.push(parseFloat(row.humidity));
                        airQualities.push(parseFloat(row.airQuality));
                        timestamps.push(timestamp);

                        checkForAlerts(row, initialLoad);
                    });

                    renderChart('temperatureChart', 'Temperature Over Time', timestamps, temperatures, 'Temperature (°C)', 'temperatureAnalysis', categorizeTemperature);
                    renderChart('humidityChart', 'Humidity Over Time', timestamps, humidities, 'Humidity (%)', 'humidityAnalysis', categorizeHumidity);
                    renderChart('airQualityChart', 'Air Quality Over Time', timestamps, airQualities, 'Air Quality (PPM)', 'airQualityAnalysis', categorizeAirQuality);

                    initialLoad = false;
                }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Error fetching data:', textStatus, errorThrown); // Debugging: Log errors
                    console.error('Response text:', jqXHR.responseText); // Debugging: Log response text
                });
            }

            function checkForAlerts(row, isInitialLoad) {
                var alertMessage = '';

                // Check temperature
                if (row.temperature > highestTemperature) {
                    highestTemperature = row.temperature;
                    if (!isInitialLoad) {
                        alertMessage += `<div class="alert alert-danger" role="alert">New highest temperature: ${row.temperature}°C</div>`;
                    }
                }
                if (row.temperature < lowestTemperature) {
                    lowestTemperature = row.temperature;
                    if (!isInitialLoad) {
                        alertMessage += `<div class="alert alert-primary" role="alert">New lowest temperature: ${row.temperature}°C</div>`;
                    }
                }

                // Check humidity
                if (row.humidity > highestHumidity) {
                    highestHumidity = row.humidity;
                    if (!isInitialLoad) {
                        alertMessage += `<div class="alert alert-danger" role="alert">New highest humidity: ${row.humidity}%</div>`;
                    }
                }
                if (row.humidity < lowestHumidity) {
                    lowestHumidity = row.humidity;
                    if (!isInitialLoad) {
                        alertMessage += `<div class="alert alert-primary" role="alert">New lowest humidity: ${row.humidity}%</div>`;
                    }
                }

                // Check air quality
                if (row.airQuality > highestAirQuality) {
                    highestAirQuality = row.airQuality;
                    if (!isInitialLoad) {
                        alertMessage += `<div class="alert alert-danger" role="alert">New highest air quality: ${row.airQuality} PPM</div>`;
                    }
                }
                if (row.airQuality < lowestAirQuality) {
                    lowestAirQuality = row.airQuality;
                    if (!isInitialLoad) {
                        alertMessage += `<div class="alert alert-primary" role="alert">New lowest air quality: ${row.airQuality} PPM</div>`;
                    }
                }

                // Display alerts
                if (alertMessage) {
                    var alertContainer = $('#alertContainer');
                    var alertElement = $(alertMessage);
                    alertContainer.append(alertElement);
                    setTimeout(function() {
                        alertElement.fadeOut(500, function() {
                            $(this).remove();
                        });
                    }, 5000); // Notification will disappear after 5 seconds
                }
            }

            function calculateStats(data) {
                if (data.length === 0) return { avg: 0, min: 0, max: 0 };
                var sum = data.reduce((a, b) => a + b, 0);
                var avg = (sum / data.length) || 0;
                var min = Math.min(...data);
                var max = Math.max(...data);
                return { avg: avg.toFixed(2), min: min, max: max };
            }

            function renderAnalysis(containerId, stats, categorize) {
                var container = $('#' + containerId);
                var category = categorize(stats.avg); // Use the categorization function
                var content = `
                    <div class="card" style="background-color: ${category.color};">
                        <div class="card-body">
                            <p><strong>Average:</strong> ${stats.avg} (${category.label})</p>
                            <p><strong>Minimum:</strong> ${stats.min}</p>
                            <p><strong>Maximum:</strong> ${stats.max}</p>
                        </div>
                    </div>
                `;
                container.html(content);
            }

            function renderChart(canvasId, title, labels, data, yLabel, analysisContainerId, categorize) {
                var ctx = document.getElementById(canvasId).getContext('2d');
                var category = categorize(calculateStats(data).avg);
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: yLabel,
                            data: data,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: category.color, // Area color based on average
                            borderWidth: 1,
                            fill: true
                        }]
                    },
                    options: {
                        scales: {
                            x: {
                                display: false // Hide the x-axis labels
                            },
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: title
                            },
                            tooltip: {
                                callbacks: {
                                    title: function() {
                                        return ''; // Prevent displaying the title (timestamp) in tooltips
                                    }
                                }
                            }
                        }
                    }
                });

                // Calculate and render analysis
                var stats = calculateStats(data);
                renderAnalysis(analysisContainerId, stats, categorize);
            }
        });
    </script>
</body>
</html>
