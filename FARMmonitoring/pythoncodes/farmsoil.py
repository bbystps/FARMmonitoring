import pymysql
import paho.mqtt.client as mqtt
import json
import datetime
import pytz
import time

# Connect to the MySQL database
conn = pymysql.connect(
    host='localhost',
    user='root',
    password='',
    database='farm_monitoring'
)

# Create a cursor object
cursor = conn.cursor()

# Global threshold variables
TEMP_LOW_THRESHOLD = 18.0  # Example default low threshold for temperature
TEMP_HIGH_THRESHOLD = 30.0  # Example default high threshold for temperature
HUMIDITY_LOW_THRESHOLD = 30.0  # Example default low threshold for humidity
HUMIDITY_HIGH_THRESHOLD = 70.0  # Example default high threshold for humidity

# Global state variables to prevent spamming
TEMP_ALARM_TRIGGERED = "NORMAL" #PREV VAL(LOW, HIGH, NORMAL)
HUMIDITY_ALARM_TRIGGERED = "NORMAL" #PREV VAL(LOW, HIGH, NORMAL)

def get_thresholds():
    global TEMP_LOW_THRESHOLD, TEMP_HIGH_THRESHOLD, HUMIDITY_LOW_THRESHOLD, HUMIDITY_HIGH_THRESHOLD
    try:
        cursor.execute("SELECT temp_low, temp_high, temp_last_state, hum_low, hum_high, hum_last_state FROM threshold_farm LIMIT 1")
        result = cursor.fetchone()
        if result:
            # Convert the fetched values to float to ensure proper comparisons
            TEMP_LOW_THRESHOLD = float(result[0])
            TEMP_HIGH_THRESHOLD = float(result[1])
            TEMP_LAST_STATE = result[2]
            HUMIDITY_LOW_THRESHOLD = float(result[3])
            HUMIDITY_HIGH_THRESHOLD = float(result[4])
            HUMIDITY_LAST_STATE = result[5]
            print(f"temp low: {TEMP_LOW_THRESHOLD}")
            print(f"temp high: {TEMP_HIGH_THRESHOLD}")
            print(f"hum low: {HUMIDITY_LOW_THRESHOLD}")
            print(f"hum high: {HUMIDITY_HIGH_THRESHOLD}")

            TEMP_ALARM_TRIGGERED = TEMP_LAST_STATE
            HUMIDITY_ALARM_TRIGGERED = HUMIDITY_LAST_STATE

            print(f"Thresholds updated from database: Temp Low: {TEMP_LOW_THRESHOLD}, Temp High: {TEMP_HIGH_THRESHOLD}, "
                  f"Humidity Low: {HUMIDITY_LOW_THRESHOLD}, Humidity High: {HUMIDITY_HIGH_THRESHOLD}, "
                  f"TEMP_LAST_STATE: {TEMP_LAST_STATE}, HUMIDITY_LAST_STATE: {HUMIDITY_LAST_STATE}")
        else:
            print("No threshold data found in the database.")
    except Exception as e:
        print(f"Error fetching thresholds: {e}")

get_thresholds()

# Define callback functions
def on_connect(client, userdata, flags, rc):
    print(f"Connected with result code {rc}")
    client.subscribe("FARM/SOIL/#")  # Subscribe to all topics under "RHT/#"
    client.subscribe("change_threshold") 
    print("Subscribed to topic FARM/SOIL/#")

def on_message(client, userdata, message):
    print(f"Message received on topic: {message.topic}")
    # get_thresholds()
    if message.topic == "change_threshold":
        # Call get_thresholds to update thresholds from the database
        print("Received change_threshold message, updating thresholds...")
        get_thresholds()
    else:
        msg_main = str(message.payload.decode("utf-8"))
        print(f"Received message on topic {message.topic}: {msg_main}")
        process_data(message.topic, msg_main)

def process_data(topic, msg_main):
    global TEMP_ALARM_TRIGGERED, HUMIDITY_ALARM_TRIGGERED
    try:
        json_msg_main = json.loads(msg_main)
        temperature = float(json_msg_main["T"])  # Convert to float and adjust
        humidity = float(json_msg_main["H"])  # Convert to float
        soil_moisture = float(json_msg_main["SM"])
        ph_level = float(json_msg_main["PH"])
        soil_temp = float(json_msg_main["ST"])
        nitrogen = float(json_msg_main["N"])
        phosphorus = float(json_msg_main["P"])
        potassium = float(json_msg_main["K"])

        # Calculate Heat Index (HI)
        heat_index = calculate_heat_index(temperature, humidity)

        # Temperature threshold check
        if soil_temp < TEMP_LOW_THRESHOLD:
            if TEMP_ALARM_TRIGGERED != "LOW":
                TEMP_ALARM_TRIGGERED = "LOW"
                client.publish("FARM/RELAY/1", "1", retain=True)

        elif soil_temp > TEMP_HIGH_THRESHOLD:
            if TEMP_ALARM_TRIGGERED != "HIGH":
                TEMP_ALARM_TRIGGERED = "HIGH"
                client.publish("FARM/RELAY/1", "0", retain=True)

        elif soil_temp < TEMP_HIGH_THRESHOLD and soil_temp > TEMP_LOW_THRESHOLD:
            if TEMP_ALARM_TRIGGERED != "NORMAL":
                TEMP_ALARM_TRIGGERED = "NORMAL"

        # Update the TEMP_LAST_STATE in the database
        update_last_states(TEMP_ALARM_TRIGGERED, HUMIDITY_ALARM_TRIGGERED)
        
        # Humidity threshold check
        if soil_moisture < HUMIDITY_LOW_THRESHOLD:
            if HUMIDITY_ALARM_TRIGGERED != "LOW":
                HUMIDITY_ALARM_TRIGGERED = "LOW"
                client.publish("FARM/RELAY/2", "1", retain=True)
        elif soil_moisture > HUMIDITY_HIGH_THRESHOLD:
            if HUMIDITY_ALARM_TRIGGERED != "HIGH":
                HUMIDITY_ALARM_TRIGGERED = "HIGH"
                client.publish("FARM/RELAY/2", "0", retain=True)

        elif soil_temp < TEMP_HIGH_THRESHOLD and soil_temp > TEMP_LOW_THRESHOLD:
            if HUMIDITY_ALARM_TRIGGERED != "NORMAL":
                HUMIDITY_ALARM_TRIGGERED = "NORMAL"
                print(f"Humidity back to normal: {soil_moisture}%")
                HUMIDITY_ALARM_TRIGGERED = "NORMAL"

        # Update the HUMIDITY_LAST_STATE in the database
        update_last_states(TEMP_ALARM_TRIGGERED, HUMIDITY_ALARM_TRIGGERED)

        insert_data(topic, temperature, humidity, heat_index, soil_moisture, ph_level, soil_temp, nitrogen, phosphorus, potassium)

    except json.JSONDecodeError as e:
        print(f"Failed to decode JSON: {e}")
    except KeyError as e:
        print(f"Missing key in JSON data: {e}")
    except TypeError as e:
        print(f"Unexpected data type: {e}")

def update_last_states(temp_last_state, humidity_last_state):
    try:
        update_query = """
            UPDATE threshold_farm
            SET temp_last_state = %s, hum_last_state = %s
            WHERE id = 1
        """
        cursor.execute(update_query, (temp_last_state, humidity_last_state))
        conn.commit()
        print(f"Updated LAST_STATE: TEMP_LAST_STATE={temp_last_state}, HUMIDITY_LAST_STATE={humidity_last_state}")
    except Exception as e:
        print(f"Error updating LAST_STATE: {e}")
        conn.rollback()

def calculate_heat_index(temperature, humidity):
    """
    Compute the Heat Index (HI) using the formula from the National Weather Service.
    HI is only valid if temperature >= 27°C and humidity >= 40%.
    """
    if temperature < 27 or humidity < 40:
        return temperature  # If conditions are not met, return the actual temperature

    # Simplified formula
    hi = (
        -8.78469475556 +
        1.61139411 * temperature +
        2.33854883889 * humidity +
        -0.14611605 * temperature * humidity +
        -0.012308094 * (temperature ** 2) +
        -0.0164248277778 * (humidity ** 2) +
        0.002211732 * (temperature ** 2) * humidity +
        0.00072546 * temperature * (humidity ** 2) +
        -0.000003582 * (temperature ** 2) * (humidity ** 2)
    )
    return round(hi, 2)

def insert_data(topic, temperature, humidity, heat_index, soil_moisture, ph_level, soil_temp, nitrogen, phosphorus, potassium):
    gmt8_time = datetime.datetime.now(pytz.timezone('Asia/Singapore'))
    timestamp = gmt8_time.strftime('%Y-%m-%d %H:%M:%S')
    # print("Current time in GMT+8:", timestamp)

    print(
        f"TEMP: {temperature}, HUM: {humidity}, HI: {heat_index}, "
        f"SM: {soil_moisture}, PH: {ph_level},  ST: {soil_temp}, "
        f"N: {nitrogen}, P: {phosphorus},  K: {potassium}, "
        f"Timestamp: {timestamp} "
    )

    try:
        sensor_id = topic.split('/')[-1]
        # db_region = sensor_id.split('-')[0].lower()
        # print(f"Saving data of: {sensor_id}, region: {db_region}")

        # insert_query = f"INSERT INTO {sensor_id} (temperature, humidity, heat_index, soil_moisture, ph_level, soil_temp, nitrogen, phosphorus, potassium, timestamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"
        # cursor.execute(insert_query, (temperature, humidity, heat_index, soil_moisture, ph_level, soil_temp, nitrogen, phosphorus, potassium, timestamp))
        # conn.commit()

        insert_query = (
            f"INSERT INTO {sensor_id} "
            f"(temperature, humidity, heat_index, soil_moisture, ph_level, "
            f"soil_temp, nitrogen, phosphorus, potassium, timestamp) "
            f"VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"
        )
        cursor.execute(
            insert_query, 
            (
                temperature, humidity, heat_index, soil_moisture, ph_level, 
                soil_temp, nitrogen, phosphorus, potassium, timestamp
            )
        )
        conn.commit()

        print(f"Insert Success for {sensor_id}")
    except Exception as e:
        print("An error occurred:", e)
        conn.rollback()

# MQTT Client setup
client = mqtt.Client()

# Set callback functions
client.on_connect = on_connect
client.on_message = on_message

# Set username and password
username = "mqtt"
password = "ICPHmqtt!"
client.username_pw_set(username, password)

# Connect to the MQTT broker
client.connect("3.27.210.100", 1883, 60)

# Start the MQTT client loop
client.loop_forever()