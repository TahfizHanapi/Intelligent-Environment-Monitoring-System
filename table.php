<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Environment Sensor Data - Table</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        body {
            background-color: #abdbe3;
        }
        .center-content {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .alert-container {
            width: 80%;
            margin-bottom: 20px;
        }
        .table thead th {
            background-color: #343a40;
            color: #fff;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }
        .table tbody tr:nth-child(even) {
            background-color: #ffffff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Dashboard</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="http://localhost/EnvSensor/table.php">Data Table</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="http://localhost/EnvSensor/visualize.php">Visualizations</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5 center-content">
        <h1 class="mb-4 text-center">Environment Sensor Data Table</h1>
        <div class="alert-container" id="alertContainer"></div>
        <table class="table table-bordered table-hover mt-5" style="width: 80%;">
            <thead>
                <tr>
                    <th>Temperature (°C)</th>
                    <th>Humidity (%)</th>
                    <th>Air Quality (PPM)</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody id="dataBody">
            </tbody>
        </table>
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
                    var rows = '';

                    data.forEach(function(row) {
                        var timestamp = new Date(row.timestamp).toLocaleString('en-US', { timeZone: 'Asia/Kuala_Lumpur' });
                        rows += '<tr>';
                        rows += '<td>' + row.temperature + '</td>';
                        rows += '<td>' + row.humidity + '</td>';
                        rows += '<td>' + row.airQuality + '</td>';
                        rows += '<td>' + timestamp + '</td>';
                        rows += '</tr>';

                        checkForAlerts(row, initialLoad);
                    });

                    $('#dataBody').html(rows);
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
        });
    </script>
</body>
</html>
