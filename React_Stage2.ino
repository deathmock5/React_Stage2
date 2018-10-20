/**
 * @author = Steven Venham
 * @email = deathmock@gmail.com
 * @update = 3/7/2018
 * 
 * REACT - ESP8266 firmware.
 * 
 * Querys REACT arduino side and passes information off to Web API
 * Handles TCPIP Netstack.
 * Handles responces from server. Updateing variables as nessisary.
 */



#define SD_BUFFER_SIZE 64
#define PING_RATE 3600000 //1 Hour
#define CHECK_RATE 1000 //Once a second.

const char* CMD_PEEK = "PEEK";  //Peek at contents of the message
const char* CMD_SSID = "SSID";  //SSID of wifi
const char* CMD_PASS = "PASS";  //Password of WIFI
const char* CMD_APIK = "APIK";  //API Key
const char* CMD_MACH = "MACH";  //Seacret key
const char* CMD_ERRO = "ERROR"; //Echo Errors

#include <ESP8266WiFi.h>  
#include "CString.h"
#include <Arduino.h>
#include <Hash.h>
#include <Regexp.h>
#include <TimeLib.h>
//#include "sha256.h"

const char* seac = "";//
const char* host = "khe.dzwxgames.com";
const int httpPort = 80;

//Serial stuff
bool incomeingmessage = false;
char serialBuffer[SD_BUFFER_SIZE];
uint8_t serialBuffer_index = 0;
const char BEGIN_CHAR = '#';
const char RETURN_CHAR = '!';
unsigned long curtime = 0;

const String MONTHS[12] = {"Jan" , "Feb" , "Mar" , "Apr" , "May" , "Jun" , "Jul" , "Aug" , "Sep", "Oct" , "Nov" , "Dec"};

void setup() {
  Serial.begin(115200);
  WiFi.mode(WIFI_STA); //Get rid of anoyying wifi networks.
  WiFi.onEvent(WiFiEvent);
}

// typedef enum {
//    WIFI_EVENT_STAMODE_CONNECTED = 0,
//    WIFI_EVENT_STAMODE_DISCONNECTED = 1,
//    WIFI_EVENT_STAMODE_AUTHMODE_CHANGE = 2,
//    WIFI_EVENT_STAMODE_GOT_IP = 3,
//    WIFI_EVENT_STAMODE_DHCP_TIMEOUT = 4,
//    WIFI_EVENT_SOFTAPMODE_STACONNECTED = 5,
//    WIFI_EVENT_SOFTAPMODE_STADISCONNECTED = 6,
//    WIFI_EVENT_SOFTAPMODE_PROBEREQRECVED = 7,
//    WIFI_EVENT_MAX
//} WiFiEvent_t;

//Web information;
int value = 0;
uint8_t numMessages = 0;
char* ssid = NULL;
char* pass = NULL;
char* macn = NULL;
char* apik = NULL;
char* datecode = NULL;
bool connected = false;

static void dumpAsHEX(const char* input_buffer,const uint8_t len){
  Serial.print(F("\""));
  for(uint8_t i = 0; i < len; i++) //strlen(input_buffer)
  {
    uint8_t c = (uint8_t)input_buffer[i];
    if(c < 16)
    {
      Serial.print(F("0"));
    }
    Serial.print(c,HEX);
  }
  Serial.print(F("\" -> "));
  for(uint8_t i = 0; i < len; i++) //strlen(input_buffer)
  {
    char c = (uint8_t)input_buffer[i];
    switch(c)
    {
      case '\r':
        Serial.print(F("\\r"));
        break;
      case '\n':
        Serial.print(F("\\n"));
        break;
      case '\0':
        Serial.print(F("\\0"));
        break;
      default:
        Serial.print(c);
        break;
    }
  }
  Serial.println();
}

void WiFiEvent(WiFiEvent_t event) {
    switch(event) {
        case WIFI_EVENT_STAMODE_GOT_IP:
            connected = true;
            break;
        case WIFI_EVENT_STAMODE_DISCONNECTED:
            Serial.println("WiFi lost connection");
            connected = false;
            break;
        case WIFI_EVENT_SOFTAPMODE_PROBEREQRECVED:
            //Are you still there?
            break;
        default:
            Serial.printf("[WiFi-event] event: %d\n", event);
            break;
    }
}

void processServerMessage(const char* cmd,const char* arg){
  if(strcmp(cmd,"Server") == 0)
  {
    
  }
  else if(strcmp(cmd,"Content-Length") == 0)//4
  {
    
  }
  else if(strcmp(cmd,"Connection") == 0)//close
  {
    
  }
  else if(strcmp(cmd,"Content-Type") == 0)
  {
    
  }
  else if(strcmp(cmd,"Date") == 0)//Fri, 07 Apr 2017 17:03:36 GMT
  {
    MatchState ms;
    ms.Target((char*)arg);
    char result = ms.Match ("(%a+), (%d+) (%a+) (%d+) (%d+):(%d+):(%d+) (%a+)", 0);
    //Year month day hour minute second
    if (result == REGEXP_MATCHED)
     {
      
      String year = ms.GetCapture ((char*)arg, 3); //Year
      String month = ms.GetCapture ((char*)arg, 2); //Month
      for(int i = 0 ; i < 12; i++)
      {
        bool found = true;
        for(int j = 0; j < 3; j++)
        {
          if(MONTHS[i][j] != month[j])
          {
            found = false;
            break;
          }
        }
        if(found)
        {
          month = String(i + 1);
        }
      }
      String day = ms.GetCapture ((char*)arg, 1); //Day
      String hour = ms.GetCapture ((char*)arg, 4); //Hour
      String minute = ms.GetCapture ((char*)arg, 5); //Minute
      String second = ms.GetCapture ((char*)arg, 6); //Second
      String dateinfo = "!TIME ";
      setTime(hour.toInt(), minute.toInt(), second.toInt(), day.toInt(), month.toInt(), year.toInt());
      dateinfo += String(now());
      Serial.println(dateinfo);
     }
     else if (result == REGEXP_NOMATCH)
     {
       Serial.println("NOT FOUND");
       Serial.println(arg);
       dumpAsHEX(arg,strlen(arg));
     }
     else
     {
       Serial.println("ERROR");
     }
  }
  else
  {
    Serial.print("'");
    for(int i =0; i < strlen(cmd);i++)
    {
      Serial.print(cmd[i]);
    }
    Serial.println("'");
  }
  //Date:Fri, 07 Apr 2017 17:03:36 GMT
  //Server:Apache/2.4.18 (Ubuntu)
  //Content-Length:4
  //Connection:close
  //Content-Type:text/html; charset=UTF-8
  //
  //OK/r/n
}

void processCommand(char* cmd,char* arg){
  char* temp = NULL;
  if(CString::strcmp(CMD_SSID,cmd))      //SSID of wifi
  {
    ssid = new char[strlen(arg) + 1];
    strcpy(ssid,arg);
  }
  else if(CString::strcmp(CMD_PASS,cmd))      //Password of WIFI
  {
    pass = new char[strlen(arg) + 1];
    strcpy(pass,arg);
  }
  else if(CString::strcmp(CMD_APIK,cmd))      //API Key
  {
    apik = new char[strlen(arg) + 1];
    strcpy(apik,arg);
  }
  else if(CString::strcmp(CMD_MACH,cmd)){  //Seacret key for encryption
    macn = new char[strlen(arg) + 1];
    strcpy(macn,arg);
  }
  else
  {
    Serial.print(F("#ERROR "));
    Serial.print(cmd);
    Serial.print(F("("));
    if(arg)
    {
      Serial.print(arg);
    }
    Serial.println(F(");"));
    return;
  }
  
  if(temp)
  {
    Serial.print(RETURN_CHAR);
    Serial.print(cmd);
    Serial.print(F(" "));
    Serial.println(temp);
    delete[] temp;
  }
}

void updateSerial(){
  while(Serial.available())
  {
    char c = Serial.read();
    if(!incomeingmessage)
    {
      if(c == BEGIN_CHAR) //27
      {
        incomeingmessage = true;
        serialBuffer_index = 0;
        for(uint8_t i = 0 ; i <SD_BUFFER_SIZE ;i++)
        {
          serialBuffer[i] = 0;
        }
      }
    }
    else
    {
      if(c == '\n')
      {
        //Finished the message
        serialBuffer[serialBuffer_index++] = 0;
        char* args = NULL;
        uint8_t pos = CString::indexOf(serialBuffer,' ');
        if(pos == 0)
        {
          processCommand(serialBuffer,args);
        }
        else
        {
          char* cmd = CString::substring(serialBuffer,0,pos);
          args = CString::substring(serialBuffer,pos + 1);
          processCommand(cmd, args);
          delete[] cmd;
          delete[] args;
        }
        incomeingmessage = false;
      }
      else if(c == '\r')
      {
        //Ignore me
      }
      else
      {
        if(serialBuffer_index < SD_BUFFER_SIZE -1)
          serialBuffer[serialBuffer_index++] = c;
      }
    }
  }
}

boolean updateData(){
  if(numMessages != 0)
  {
    if(value == 0)
    {
      Serial.println("!PEEK");
      return false;
    }
    if(!datecode)
    {
      Serial.println("!NAME");
      return false;
    }
    if(!ssid)
    {
      Serial.println("!SSID");
      return false;
    }
    if(!pass)
    {
      Serial.println("!PASS");
      return false;
    }
    if(!apik)
    {
      Serial.println("!APIK");
      return false;
    }
    if(!macn)
    {
      Serial.println("!MACH");
      return false;
    }
  }
  else
  {
    return false;
  }
  return true;
}

boolean testConnection(){
  if(!connected)
    {
      Serial.print("Connecting to ");
      WiFi.begin(ssid, pass);
      int trys = 0;
      while(!connected)
      {
        if(trys++ > 30)
        {
          Serial.setDebugOutput(true);
          Serial.println("Failed.");
          Serial.print("Debug:");
          Serial.print(ssid);
          Serial.print(" ");
          Serial.println(pass);
          WiFi.disconnect(true);
          connected = false;
          return false;
        }
          delay(500);
          Serial.print(".");
      }
      Serial.println("Connected!");
      Serial.print("IP address: ");
      Serial.println(WiFi.localIP());
    }
    return true;
}

bool sendData(){
  numMessages = 0;
  String url = "/api.php?action=PUSH";
      url += "&val=";
      url += value;
      url += "&date=";
      url += datecode;
      url += "&key=";
      url += apik;
      url += "&macn=";
      url += macn;
   String hashVal = url + String(seac);

   hashVal = sha1(hashVal);
      url += "&chk=";
      url += hashVal;

      value = 0;
      delete[] datecode;
      datecode = NULL;
        
  Serial.print("Requesting URL: ");
  Serial.println(url);

  // Use WiFiClient class to create TCP connections
  WiFiClient client;

  if (!client.connect(host, httpPort)) {
    Serial.println("!ERROR connection failed");
    return false;
  }
        
  client.print(String("GET ") + url + " HTTP/1.1\r\n" +
     "Host: " + host + "\r\n" + 
     "Connection: close\r\n\r\n");

  int timeout = millis() + 5000;
  while (client.available() == 0) {
    if (timeout - millis() < 0) {
      Serial.println(">>> Client Timeout !");
      client.stop();
      return false;
    }
  }
  // Read all the lines of the reply from server and print them to Serial
  String cmd = "";
  String argument = "";
  bool gotargs = false;
  while(client.available()){
    char c = client.read();
    if(c == '\r')
    {
      
    }
    else if(c == '\n')
    {
      if(gotargs)
      {
        processServerMessage(cmd.c_str(),argument.c_str() + 1);
      }
      else
      {
        if(strcmp(cmd.c_str(),"HTTP/1.1 200 OK") == 0)
        {
          
        }
        else if(strcmp(cmd.c_str(),"ok") == 0)
        {
          Serial.println("!POP");
        }
      }
      cmd = "";
      argument = "";
      gotargs = false;
    }
    else if(c == ':' && !gotargs)
    {
      gotargs = true;
    }
    else
    {
      if(gotargs)
      {
        argument += c;
      }
      else
      {
        cmd += c;
      }
    }
  }
  return true;
}

bool sendPing(){
  String url = "/api.php?action=PUSH";
      url += "&val=";
      url += value;
      url += "&date=";
      url += datecode;
      url += "&key=";
      url += apik;
      url += "&macn=";
      url += macn;
   String hashVal = url + String(seac);

   hashVal = sha1(hashVal);
      url += "&chk=";
      url += hashVal;

      value = 0;
      delete[] datecode;
      datecode = NULL;
        
  Serial.print("Requesting URL: ");
  Serial.println(url);

  // Use WiFiClient class to create TCP connections
  WiFiClient client;

  if (!client.connect(host, httpPort)) {
    Serial.println("!ERROR connection failed");
    return false;
  }
        
  client.print(String("GET ") + url + " HTTP/1.1\r\n" +
     "Host: " + host + "\r\n" + 
     "Connection: close\r\n\r\n");

  int timeout = millis() + 5000;
  while (client.available() == 0) {
    if (timeout - millis() < 0) {
      Serial.println(">>> Client Timeout !");
      client.stop();
      return false;
    }
  }
  // Read all the lines of the reply from server and print them to Serial
  String cmd = "";
  String argument = "";
  bool gotargs = false;
  while(client.available()){
    char c = client.read();
    if(c == '\r')
    {
      
    }
    else if(c == '\n')
    {
      if(gotargs)
      {
        processServerMessage(cmd.c_str(),argument.c_str() + 1);
      }
      else
      {
        if(strcmp(cmd.c_str(),"HTTP/1.1 200 OK") == 0)
        {
          
        }
        else if(strcmp(cmd.c_str(),"ok") == 0)
        {
          //Serial.println("!POP");
        }
      }
      cmd = "";
      argument = "";
      gotargs = false;
    }
    else if(c == ':' && !gotargs)
    {
      gotargs = true;
    }
    else
    {
      if(gotargs)
      {
        argument += c;
      }
      else
      {
        cmd += c;
      }
    }
  }
  return true;
}

void loop() {
  curtime += 1;
  updateSerial();

  if(curtime % PING_RATE == 0)
  {
    Serial.println("Once AN HOUR");
    if(sendPing())
    {
      curtime = 0;
    }
  }

  if(curtime % CHECK_RATE == 0)
  {
    if(updateData())
    {
      if(testConnection())
      {
        sendData();
      }
    }
    else
    {
      if(numMessages == 0)
      {
        Serial.println("!COUN");
      }
    }
  }
  delay(1);
}

