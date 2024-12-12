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
TEMP_ALARM_TRIGGERED = False
HUMIDITY_ALARM_TRIGGERED = False

def get_thresholds():
    global TEMP_LOW_THRESHOLD, TEMP_HIGH_THRESHOLD, HUMIDITY_LOW_THRESHOLD, HUMIDITY_HIGH_THRESHOLD
    try:
        cursor.execute("SELECT temp_low, temp_high, hum_low, hum_high FROM threshold LIMIT 1")
        result = cursor.fetchone()
        if result:
            # Convert the fetched values to float to ensure proper comparisons
            TEMP_LOW_THRESHOLD = float(result[0])
            TEMP_HIGH_THRESHOLD = float(result[1])
            HUMIDITY_LOW_THRESHOLD = float(result[2])
            HUMIDITY_HIGH_THRESHOLD = float(result[3])
            print(f"temp low: {TEMP_LOW_THRESHOLD}")
            print(f"temp high: {TEMP_HIGH_THRESHOLD}")
            print(f"hum low: {HUMIDITY_LOW_THRESHOLD}")
            print(f"hum high: {HUMIDITY_HIGH_THRESHOLD}")

            print(f"Thresholds updated from database: Temp Low: {TEMP_LOW_THRESHOLD}, Temp High: {TEMP_HIGH_THRESHOLD}, "
                  f"Humidity Low: {HUMIDITY_LOW_THRESHOLD}, Humidity High: {HUMIDITY_HIGH_THRESHOLD}")
        else:
            print("No threshold data found in the database.")
    except Exception as e:
        print(f"Error fetching thresholds: {e}")

get_thresholds()

# Define callback functions
def on_connect(client, userdata, flags, rc):
    print(f"Connected with result code {rc}")
    client.subscribe("FARM/WATER/#")  # Subscribe to all topics under "RHT/#"
    client.subscribe("change_threshold") 
    print("Subscribed to topic FARM/WATER/#")

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
        ph_level = float(json_msg_main["PH"])
        tds = float(json_msg_main["TDS"])  # Convert to float and adjust
        water_temp = float(json_msg_main["WT"])
        water_level = float(json_msg_main["WL"])

        insert_data(topic, ph_level, tds, water_temp, water_level)

    except json.JSONDecodeError as e:
        print(f"Failed to decode JSON: {e}")
    except KeyError as e:
        print(f"Missing key in JSON data: {e}")
    except TypeError as e:
        print(f"Unexpected data type: {e}")

def insert_data(topic, ph_level, tds, water_temp, water_level):
    gmt8_time = datetime.datetime.now(pytz.timezone('Asia/Singapore'))
    timestamp = gmt8_time.strftime('%Y-%m-%d %H:%M:%S')
    # print("Current time in GMT+8:", timestamp)

    print(
        f"PH: {ph_level}, TDS: {tds}, WT: {water_temp}, WL: {water_level},"
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
            f"(ph_level, tds, water_temp, water_level, timestamp) "
            f"VALUES (%s, %s, %s, %s, %s)"
        )
        cursor.execute(
            insert_query, 
            (
                ph_level, tds, water_temp, water_level, timestamp
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
