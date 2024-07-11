#include "DHT.h"
#include <ESP8266WiFi.h>
#include <WiFiClient.h>
#include <ESP8266WebServer.h>
#include <ESP8266mDNS.h>
#include <TimeLib.h>
#include <NTPClient.h>
#include <WiFiUdp.h>

// DHT22 Sensor
#define DHTPIN D4
#define DHTTYPE DHT22

// MQ135 Sensor
#define MQ135PIN A0

// Red LED and Buzzer
#define LED_PIN D3
#define BUZZER_PIN D2

DHT dht(DHTPIN, DHTTYPE);

float humidity;
float temperature;
int airQuality;

const char* ssid = "The Internet"; // WiFi SSID
const char* password = "alif1234"; // WiFi password
char server[] = "192.168.43.186"; // Server IP address

WiFiClient client; // Initialize WiFi client

WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", 8 * 3600, 60000); // NTP client for Malaysia (UTC+8)

void setup() {
  Serial.begin(115200);
  dht.begin();

  pinMode(MQ135PIN, INPUT);
  pinMode(LED_PIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);

  digitalWrite(LED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);

  // Connect to WiFi network
  Serial.println();
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);

  WiFi.begin(ssid, password);

  // Wait until connected to WiFi
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.println("WiFi connected");

  // Initialize NTP client
  timeClient.begin();
  while (!timeClient.update()) {
    timeClient.forceUpdate();
    delay(500);
  }
  Serial.println("Time synchronized");

  Serial.println("Server started");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP()); // Print local IP address
  delay(1000);
}

void loop() {
  humidity = dht.readHumidity();
  temperature = dht.readTemperature();
  airQuality = analogRead(MQ135PIN);

  controlOutputs();
  Sending_To_phpmyadmindatabase(); // Send data to the server

  delay(30000); // Update every 30 seconds
}

void controlOutputs() {
  // Define threshold values for air quality, temperature, and humidity
  int airQualityThreshold = 500; // Example threshold value for air quality
  float temperatureHighThreshold = 27.0; // Example threshold value for high temperature (in °C)
  float temperatureLowThreshold = 25.0; // Example threshold value for low temperature (in °C)
  float humidityHighThreshold = 70.0; // Example threshold value for high humidity (in %)
  float humidityLowThreshold = 20.0; // Example threshold value for low humidity (in %)

  if (airQuality > airQualityThreshold || 
      temperature > temperatureHighThreshold || 
      temperature < temperatureLowThreshold || 
      humidity > humidityHighThreshold || 
      humidity < humidityLowThreshold) {
    digitalWrite(LED_PIN, HIGH);
    digitalWrite(BUZZER_PIN, HIGH);
  } else {
    digitalWrite(LED_PIN, LOW);
    digitalWrite(BUZZER_PIN, LOW);
  }

  Serial.print("Temperature: ");
  Serial.print(temperature);
  Serial.print(" °C, Humidity: ");
  Serial.print(humidity);
  Serial.print(" %, Air Quality: ");
  Serial.println(airQuality);
}

void Sending_To_phpmyadmindatabase() {
  timeClient.update();
  unsigned long now = timeClient.getEpochTime(); // Get current time in seconds since 1970-01-01 00:00:00 UTC

  if (client.connect(server, 80)) {
    Serial.println("Connected to server");

    // Formulate the HTTP GET request:
    String url = "/EnvSensor/EnvSensor.php";
    url += "?humidity=";
    url += humidity;
    url += "&temperature=";
    url += temperature;
    url += "&airQuality=";
    url += airQuality;
    url += "&timestamp=";
    url += now; // Append current Unix timestamp

    Serial.print("Requesting URL: ");
    Serial.println(url);

    // Send HTTP GET request to the server:
    client.print(String("GET ") + url + " HTTP/1.1\r\n" +
                 "Host: " + server + "\r\n" +
                 "Connection: close\r\n\r\n");

  } else {
    Serial.println("Connection failed");
  }
}
