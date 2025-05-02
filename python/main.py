import serial
import requests
import datetime
import time
import os
from dotenv import load_dotenv

# بارگذاری متغیرهای محیطی از فایل .env
load_dotenv()

def sendSMS(number, password):
    # URL API
    urlSMS = os.getenv("URL_SMS_PANEL")

    # داده‌های JSON که باید ارسال شوند
    payload = {
        "code": os.getenv("API_KEY_SMS_PANEL"),
        "sender": "+983000505",
        "recipient": number,
        "variable": {
            "password": password
        }
    }

    headers = {
        "Accept": "*/*",
        "apikey": os.getenv("API_KEY_TELEGRAM_BOT"),  # جایگزین با API Key واقعی
        "Content-Type": "application/json"
    }
    # ارسال درخواست POST
    response = requests.post(url, json=payload, headers=headers)
    if response.status_code == 200:
        print("SMS sent to " + number)
    print(response.status_code)

def checkDoorStatus():
    try:
        print("enter to checkDoorStatus")
        url = os.getenv("URL_CHECK_DOOR_STATUS")
        response = requests.post(url, data={'key': os.getenv("API_KEY_WEBSITE")})
        if response.status_code == 200:
            print("Response OK!!")
            print(response.text)
            data=response.json()
            if data['doorStatus'] == '1':
                print("Door status is open")
                ser.write(b"openDoor1\n")
            elif data['lightStatus'] == '1':
                print("changeStateLight")
                ser.write(b"changeStateLight\n")
        time.sleep(0.5)
    except:
        print("Something went wrong")



url = os.getenv("URL_SEND_NOTIFICATION")

# تنظیمات پورت سریال (باید مطابق با تنظیمات دستگاه سریال شما باشد)
ser = serial.Serial(
    port='/dev/serial/by-id/usb-1a86_USB2.0-Serial-if00-port0',
    baudrate=9600,  # نرخ انتقال داده‌ها
    bytesize=8,
    timeout=2,
    stopbits=serial.STOPBITS_ONE
)

# بررسی باز بودن پورت سریال
if ser.is_open:
    print(f"Connected to {ser.portstr}")

try:
    timeToCheck=time.time()
    while True:
        if ser.in_waiting > 0:

            serialString = ser.readline()

            serialString = serialString.decode('utf-8').strip()

            print(serialString)

            if serialString.startswith('passwordChanged:'):
                print("yes")
                password = serialString.split('passwordChanged:')[1]
                response = requests.post(url, data={'key': os.getenv("API_KEY_WEBSITE"), 'action': 'passwordChanged','password': password})
                sendSMS("09139638917", password)

                sendSMS("09132611899", password)
                sendSMS("09132616941", password)
                sendSMS("09132618277", password)
                sendSMS("09103264482", password)

                sendSMS("09139794285", password)
                sendSMS("09134611217", password)
                sendSMS("09371126018", password)

                ser.read_all()

            if serialString.startswith('openedDoor'):
                response = requests.post(url, data={'key': os.getenv("API_KEY_WEBSITE"), 'action': 'openedDoor'})

                ser.read_all()
                print("Opened the door at:" + str(datetime.datetime.today()))

            if serialString.startswith('wrongPassword:'):
                password = serialString.split('wrongPassword:')[1]
                response = requests.post(url, data={'key': os.getenv("API_KEY_WEBSITE"), 'action': 'wrongPassword','password': password})

                print("Wrong Password at:" + str(datetime.datetime.today()))
                ser.read_all()

            if serialString.startswith('turnOnLight'):
                response = requests.post(url, data={'key': os.getenv("API_KEY_WEBSITE"), 'action': 'turnOnLight'})

                print("Turned on light at:" + str(datetime.datetime.today()))

                ser.read_all()

            if serialString.startswith('turnOffLight'):
                response = requests.post(url, data={'key': os.getenv("API_KEY_WEBSITE"), 'action': 'turnOffLight'})

                print("Turned off light at:" + str(datetime.datetime.today()))
                ser.read_all()
            
            if serialString.startswith('theDeviseIsReady'):
                response = requests.post(url, data={'key': os.getenv("API_KEY_WEBSITE"), 'action': 'theDeviseIsReady'})

                print("the Devise Is Ready:" + str(datetime.datetime.today()))
                ser.read_all()
        else:
            time.sleep(0.1)
        if time.time()-timeToCheck>4:
            timeToCheck=time.time()
            checkDoorStatus()
            #ser.write(b"open\n")
finally:
    ser.close()
    print("Serial port closed")

