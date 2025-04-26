#include <Wire.h>
#include <Keypad.h>            //معرفی کتابخانه کی پد//
#include <EEPROM.h>
#include <SoftwareSerial.h>

SoftwareSerial bluetooth(4, 5 ); // RX | TX
const byte ROWS = 4;    //مشخص کردن تعداد سطر کی پد//
const byte COLS = 3;   //مشخص کردن تعداد ستون کی پد//
//در زیر ما حروف و اعداد کی پد را به صورت ماتریسی داخل یک ارایه قرار داده ایم. نحوه تعریف ارایه دقیقا مشابه کی پد است//
char keys[ROWS][COLS] = {
  {'1', '2', '3'},
  {'4', '5', '6'},
  {'7', '8', '9'},
  {'*', '0', '#'}
};
int keyLightRoomState=1;
//با تعریف دو ارایه به صورت زیر نحوه اتصال سطرها و ستون های کی پد به پین های اردوینو را مشخص می کنیم//
byte rowPins[ROWS] = {6, 7, 8, 13};
byte colPins[COLS] = {10, 11, 12};

int i,pass_change_count=0,pass_lenght;

char mode='m';

int wrong_count=0;

char password[10],pass_change[10],pass_last[10],password_enter[10];
char password2[10];
//تابع زیر نقشه کی پد را با توجه به مقادیر و ارایه هایی که در بالا تعریف کردیم برای اردوینو مشخص می کنند//
Keypad kpd = Keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);
void(*Reset) (void) = 0;
 
void setup() {

 
  bluetooth.begin(9600);
  
  
  
  //lcd.println(password);
  Serial.begin(9600);
  

  //بوق
  pinMode(A5,OUTPUT);
  digitalWrite(A5,LOW); 

  //لامپ اتاق
  pinMode(A0,OUTPUT);
  digitalWrite(A0,HIGH);

  //به برد ماشین کنترلی وصله و برای دکمه ی پایین و در یکه
  pinMode(2,INPUT);
  digitalWrite(2,HIGH);

  // به برد ماشین کنترلی وصله و برای کلید سمت چپه و برای چراغه
  pinMode(3,INPUT);
  digitalWrite(3,HIGH);


  //میره تو کلید ثابت
  pinMode(A1,OUTPUT);
  digitalWrite(A1,LOW);

  //میره تو کلید متغییر
  pinMode(A2,INPUT);
  digitalWrite(A2,HIGH);
  //در یک
  pinMode(A4,OUTPUT);
  digitalWrite(A4,HIGH);

  //در دو
  pinMode(A3,OUTPUT);
  digitalWrite(A3,HIGH);

  //delete
  Serial.println("loading");
  delay(1000);


 //لود کردن طول پسورد
  pass_lenght=EEPROM.read(20);

  load_pass();

  Serial.println("theDeviseIsReady");
}
void load_pass()
{
  int e=0;
  for(e=0;e<=pass_lenght;e++)
  {
    beep();
    password[e]=EEPROM.read(e);
    Serial.println(password[e]);
  }
}
void save_pass(char pass[])
{
  for(int e=0;e<=pass_lenght;e++)
  {
    EEPROM.write(e,password[e]);
  }
  EEPROM.write(20,pass_lenght);
  beep(2,1);
  Serial.println("passwordChanged:"+String(password));
}

int check_pass(char entered_password[])
{
    
    if(i-1!=pass_lenght)
      return 0;
    for(int index=0;index<=pass_lenght;index++)
    {
      if(entered_password[index]!=password[index])
        return 0;
    }
    return 1;
}
int check_pass2(char entered_password[])
{

  if(i-2!=pass_lenght)
      return 0;
    for(int index=0;index<=pass_lenght;index++)
    {
      if(entered_password[index]!=password[index])
        return 0;
    }
    if(entered_password[pass_lenght+1]=='2')
      return 1;
    else
      return 0;
}
void passwordChange()
{
    Serial.println("Password Change");
    int ture=0,index=0;
    String myString = "Hello Arduino!";
    
    //رمز قبلی را وارد میکند و در صورت درست بودن رمز جدید را وارد میکند
    while(1)
    {      
      char key = kpd.getKey();
      if(key&&key!='*'&&key!='#') 
      {
          pass_last[index]=key;
          index++;
          beepKeyPad();
      }
      i=pass_lenght+1;
      if(key=='#'&&check_pass(pass_last))
      {
       
        //از بین بردن آریه رمز قبلی
        pass_last[0]='#';
        ture=1;
        beepKeyPad();
        break;
      }
      if(key=='*')
      {
        beepKeyPad();
        break;
      }
                  
    }
    index=0; 

    //رمز قبلی را درست وارد کرده و رمز جدید را وارد میکند.
    if(ture==1)
    {
      index=0;     
      
      while(1)
      {                         
           char key = kpd.getKey();
           if(key&&key!='*'&&key!='#'&&index!=10) 
           {
              pass_change[index]=key;
              index++;
              beepKeyPad();
           }    
           if(key=='*')
           {
              beepKeyPad();
              break; 
           }
           if(key=='#')
           {
            beepKeyPad();
            pass_lenght=index-1;
            
            for(int r=0;r<=index;r++)
            {
              password[r]=pass_change[r];
            }
            save_pass(password);
            
            break;
           }
      }
    }
    /////////////////

    pass_change_count=0;

}
void beep(double secondOn,int intervalBeep){
  int interval=0;
  for (;interval<intervalBeep;interval++)
  {
    digitalWrite(A5,HIGH);
    delay(secondOn*1000);
    digitalWrite(A5,LOW);
    delay(secondOn*1000);
  }
  
}
void beep(){
  if(mode=='m'){
    digitalWrite(A5,HIGH);
    delay(500);
    digitalWrite(A5,LOW);
    delay(500);
  }
}
void beepKeyPad(){
  if(mode=='m'){
    digitalWrite(A5,HIGH);
    delay(100);
    digitalWrite(A5,LOW);
    delay(100);
  }
}
void changeLightState(){
  if(digitalRead(A0)==HIGH)
  {
      Serial.println("turnOnLight");
      digitalWrite(A0,LOW); 
  }
  
  else if(digitalRead(A0)==LOW)
  {
    Serial.println("turnOffLight");
    digitalWrite(A0,HIGH);
  }
    
  beep(0.05,1);
}
void openDoor(int whichDoor){
  int waitForOpenDoor=100;
  //open door 1
  if(whichDoor==1){
    digitalWrite(A4,LOW);
    delay(waitForOpenDoor);
    digitalWrite(A4,HIGH);
  }
  //open door 2
  else if(whichDoor==2){
    digitalWrite(A3,LOW);
    delay(waitForOpenDoor);
    digitalWrite(A3,HIGH);
  }

  //sendAlaramToPythonProgram
  Serial.println("openedDoor");
}













void loop() {

  //controled light By switch
  if(keyLightRoomState==0&&digitalRead(A2)==HIGH)
  {
    keyLightRoomState=1;
    changeLightState();
  }
  if(keyLightRoomState==1&&digitalRead(A2)==LOW)
  {
    keyLightRoomState=0;
    changeLightState();
  }


  char key = kpd.getKey();
  if(wrong_count==5)
  {
    beep(0.3,50);
    wrong_count=0;
  }
  if(key=='*')
  {
      i=0;
      //چک کردن برای تغییر رمز
      pass_change_count++;
      beepKeyPad();
  }
  if(key&&key!='*'&&key!='#')  
  {
    pass_change_count=0;
    password_enter[i]=key;
    i++;
    beepKeyPad();
  }
  
  if(key=='#')
  {
      beepKeyPad();
      
      if(check_pass(password_enter))
      {
        wrong_count=0;
        openDoor(1);
        //sendAlaram
      }
      else if(check_pass2(password_enter))
      {
        wrong_count=0;
        openDoor(2);
      }
      //پسوورد اشتباه وارد شده است
      else{
        wrong_count++;
        Serial.println("wrongPassword:"+String(password_enter));
      }
      
      pass_change_count=0;
      i=0;
  }
  if(pass_change_count==5)
  {
    delay(1000);
    digitalWrite(A5,LOW);
    passwordChange();
    i=0;
  }



  //ارتباط پورت سریال با کامپیوتر جهت دریافت دستورات وب
  if (Serial.available() > 0) {
    Serial.println("yes yes yes");
    String command = Serial.readStringUntil('\n');
    if (command == "open") {
        openDoor(1);
    }
  }


    
  if (bluetooth.available())
  {
    int q=0;
    char data[100];
    
    while(bluetooth.available())
    {
      
       data[q] = bluetooth.read();
       Serial.write(data[q]);
       q++;
    }
    q--;
    if(mode=='m')
      digitalWrite(A5,HIGH);
    if (data[q] == 'o')
    {
      digitalWrite(A4, LOW);
    }
    if (data[q] == 'd')
    {
      digitalWrite(A3, LOW);
    }
    if (data[q] == 'p')
    {
     for(int x=0;x<=pass_lenght;x++)
      {
        bluetooth.write(password[x]);
      }
    }
    if(data[q]=='z')
    {
     
      for(int x=0;x<q;x++)
      {
        password[x]=data[x];
      }
      pass_lenght=q-1;
      save_pass(password);
    }
    if(data[q]=='$')
    {

      bluetooth.write(mode);
      if(digitalRead(A0)==HIGH)
        bluetooth.write("0");
      else if(digitalRead(A0)==LOW)
        bluetooth.write("1");
      
    }
    if(data[q]=='m')
    {
      mode='m';
      
    }
    if(data[q]=='s')
    {
      mode='s';
      
    }
    if(data[q]=='l')
    {
      if(digitalRead(A0)==HIGH)
        digitalWrite(A0,LOW);
      else if(digitalRead(A0)==LOW)
        digitalWrite(A0,HIGH);

    }
    delay(100);
    digitalWrite(A5,LOW);
  }
  //delay(1000);
  if(digitalRead(2)==LOW)
  {
    while(digitalRead(2)==LOW);
    if(password_enter[i-1])
    {
      digitalWrite(A3, LOW);  
    }
    else
    {
      digitalWrite(A4, LOW);  
    }
    wrong_count=0;
    pass_change_count=0;
    i=0;

  }
  else if(digitalRead(3)==LOW)
  {
    while(digitalRead(3)==LOW);
    changeLightState();
  }
 
  delay(100);
  digitalWrite(A4,HIGH);
  digitalWrite(A3,HIGH);
  digitalWrite(A5,LOW);
        
}
