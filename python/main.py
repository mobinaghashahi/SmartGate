import serial
import requests
import datetime


def sendSMS(number, password):
    # URL API
    url = "https://api2.ippanel.com/api/v1/sms/pattern/normal/send"

    # داده‌های JSON که باید ارسال شوند
    payload = {
        "code": "yourPatternCode",
        "sender": "+983000505",
        "recipient": number,
        "variable": {
            "password": password
        }
    }

    headers = {
        "Accept": "*/*",
        "apikey": "yourSmsApiCode",
        "Content-Type": "application/json"
    }
    # ارسال درخواست POST
    response = requests.post(url, json=payload, headers=headers)
    if response.status_code == 200:
        print("SMS sent to " + number)
    print(response.status_code)

def checkOpenDoorInHost():
    print("checkDoorInHost")
    url="yourUrl"
    response = requests.post(url, data={'key': 'yourKey'})
    print(response.status_code)
    print(response.text)
    if response.text=="1":
        ser.write(b"open\n")
def checkDoorStatus():

    print("enter to checkDoorStatus")
    url = "yourUrl"
    response = requests.post(url, data={'key': 'yourKey','action':'readStatus'})
    data=response.json()
    if data['doorStatus'] == '1':
        print("Door status is open")
        response = requests.post(url, data={'key': 'yourKey', 'action': 'sendACK'})
        if response.status_code == 200:
            print("yes")
            ser.write(b"open\n")




url = "yourUrlWebSite"

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
    while True:
        if ser.in_waiting > 0:

            serialString = ser.readline()

            serialString = serialString.decode('utf-8').strip()

            print(serialString)

            if serialString.startswith('passwordChanged:'):
                print("yes")
                password = serialString.split('passwordChanged:')[1]
                response = requests.post(url, data={'key': 'yourKey', 'action': 'passwordChanged','password': password})
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
                response = requests.post(url, data={'key': 'yourKey', 'action': 'openedDoor'})

                ser.read_all()
                print("Opened the door at:" + str(datetime.datetime.today()))

            if serialString.startswith('wrongPassword:'):
                password = serialString.split('wrongPassword:')[1]
                response = requests.post(url, data={'key': 'yourKey', 'action': 'wrongPassword','password': password})

                print("Wrong Password at:" + str(datetime.datetime.today()))
                ser.read_all()

            if serialString.startswith('turnOnLight'):
                response = requests.post(url, data={'key': 'yourKey', 'action': 'turnOnLight'})

                print("Turned on light at:" + str(datetime.datetime.today()))

                ser.read_all()

            if serialString.startswith('turnOffLight'):
                response = requests.post(url, data={'key': 'yourKey', 'action': 'turnOffLight'})

                print("Turned off light at:" + str(datetime.datetime.today()))
                ser.read_all()
            
            if serialString.startswith('theDeviseIsReady'):
                response = requests.post(url, data={'key': 'yourKey', 'action': 'theDeviseIsReady'})

                print("the Devise Is Ready:" + str(datetime.datetime.today()))
                ser.read_all()

            #checkDoorStatus()
            #ser.write(b"open\n")
finally:
    ser.close()
    print("Serial port closed")

