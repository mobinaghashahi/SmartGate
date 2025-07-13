import serial
import requests
import datetime
import time
import os
from dotenv import load_dotenv
import threading
import sqlite3
import json

# ???????? ???????? ????? ?? ???? .env
load_dotenv()
checkDoorStatusPermission=False

def sendSMS(number, password):
    # URL API
    urlSMS = os.getenv("URL_SMS_PANEL")


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
        "apikey": os.getenv("API_KEY_SMS_BOT"),  # ??????? ?? API Key ?????
        "Content-Type": "application/json"
    }

    response = requests.post(urlSMS, json=payload, headers=headers)

    if response.status_code == 200:
        print("SMS sent to " + number)
    print(response.status_code)
    print(response.text)


def checkDoorStatus():
    try:
        print("enter to checkDoorStatus")
        url = os.getenv("URL_CHECK_DOOR_STATUS")
        response = requests.post(url, data={'key': os.getenv("API_KEY_WEBSITE")},timeout=6)
        print(response.status_code)
        if response.status_code == 200:
            print("Response OK!!")
            data = response.json()
            if data['doorStatus'] == '1':

                data = {
                    "message": "openDoor1",
                    "whichUser": data['whichUser']
                }

                json_data = json.dumps(data)

                ser.write((json_data + "\n").encode())
                print("Door status is open")
            elif data['lightStatus'] == '1':

                data = {
                    "message": "changeStateLight",
                    "whichUser": data['whichUser']
                }

                json_data = json.dumps(data)

                ser.write((json_data + "\n").encode())
                print("changeStateLight")
    except:
        print("Something went wrong in checkDoorStatus.")
        return 0
def sendMessageToTelegram(action,whichUser):
    response = requests.post(url, data={'key': os.getenv("API_KEY_WEBSITE"), 'action': action,'whichUser': whichUser},timeout=6)
    print(response.status_code)

def saveLogsEvent(action,date):

    try:
        conn = sqlite3.connect('mydb.db')
        cursor = conn.cursor()
        cursor.execute('INSERT INTO events (action, date) VALUES (?, ?)', (action, date))
        conn.commit()
    except:
        print("Something went wrong in DataBase")


url = os.getenv("URL_SEND_NOTIFICATION")

# ??????? ???? ????? (???? ????? ?? ??????? ?????? ????? ??? ????)
ser = serial.Serial(
    port='/dev/serial/by-id/usb-1a86_USB2.0-Serial-if00-port0',
    baudrate=9600,  # ??? ?????? ???????
    bytesize=8,
    timeout=2,
    stopbits=serial.STOPBITS_ONE
)

# ????? ??? ???? ???? ?????
if ser.is_open:
    print(f"Connected to {ser.portstr}")

try:
    timeToCheck = time.time()
    while True:

        if ser.in_waiting > 0:
            line = ser.readline().decode().strip()

            now = datetime.datetime.now()
            date_str = now.strftime('%Y-%m-%d %H:%M')

            ##conn = sqlite3.connect('mydb.db')
            ##cursor = conn.cursor()

            if line:
                try:
                    data = json.loads(line)
                    print("Data is: ", data)

                    if isinstance(data, dict):  # ??? ??? ???? ?? ??????? ???
                        if data.get("message") == "passwordChanged":
                            password=data.get("password")
                            response = requests.post(url, data={'key': os.getenv("API_KEY_WEBSITE"), 'action': 'passwordChanged',
                                                                'password': password},timeout=6)
                            sendSMS("09139638917", password)
                            sendSMS("09132611899", password)
                            sendSMS("09132616941", password)
                            sendSMS("09132618277", password)
                            sendSMS("09103264482", password)
                            sendSMS("09139794285", password)
                            sendSMS("09134611217", password)
                            sendSMS("09371126018", password)
                        elif data.get("message") == "openedDoor":
                            sendMessageToTelegram('openedDoor',data.get("whichUser"))
                            saveLogsEvent('openedDoor',date_str)
                            #cursor.execute('INSERT INTO events (action, date) VALUES (?, ?)', ('openedDoor', date_str))
                            print("Opened the door at:" + str(datetime.datetime.today()))
                        elif data.get("message") == "wrongPassword":
                            password = data.get("wrongPassword")
                            response = requests.post(url, data={'key': os.getenv("API_KEY_WEBSITE"), 'action': 'wrongPassword',
                                                                'password': password},timeout=6)
                            saveLogsEvent('wrongPassword', date_str)
                            #cursor.execute('INSERT INTO events (action, date) VALUES (?, ?)', ('wrongPassword', date_str))

                            print("Wrong Password at:" + str(datetime.datetime.today()))
                        elif data.get("message")=='theDeviseIsReady':
                            sendMessageToTelegram('theDeviseIsReady',"")

                            saveLogsEvent('theDeviseIsReady', date_str)
                            #cursor.execute('INSERT INTO events (action, date) VALUES (?, ?)', ('theDeviseIsReady', date_str))
                            checkDoorStatusPermission=True
                            timeToCheck = time.time()

                            print("the Devise Is Ready:" + str(datetime.datetime.today()))

                        elif data.get("message") == "turnOnLight":
                            sendMessageToTelegram('turnOnLight',data.get("whichUser"))
                            saveLogsEvent('trunOnLight', date_str)
                            #cursor.execute('INSERT INTO events (action, date) VALUES (?, ?)', ('trunOnLight', date_str))
                            print("Turned on light at:" + str(datetime.datetime.today()))

                        elif data.get("message") == "turnOffLight":
                            sendMessageToTelegram('turnOffLight',data.get("whichUser"))
                            saveLogsEvent('trunOffLight', date_str)
                            #cursor.execute('INSERT INTO events (action, date) VALUES (?, ?)',('trunOffLight', date_str))
                            print("Turned off light at:" + str(datetime.datetime.today()))
                        #conn.commit()
                    else:
                        print("?? ???? JSON ??? ??? ??????? ????:", data)

                except json.JSONDecodeError:
                    print("? JSON ???????:", repr(line))
            else:
                print("? ?????? ?? ????")




        if checkDoorStatusPermission==True:
            timeToCheck = time.time()
            checkDoorStatus()

finally:
    ser.close()
    print("Serial port closed")

