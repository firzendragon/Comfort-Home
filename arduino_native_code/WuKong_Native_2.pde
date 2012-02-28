#include <SHT1x.h>

#define nodeID "0001"
#define MaxNumStrs 100  // The length for an instruction (ex: 'A read temperature:6:1000 ' has length equal to 26)
#define MaxNumPins 20   // The number for pins   (ex: Mega has 70 pins and Uno has 20 pins)
#define MaxType 20      // The length for a type (ex: 'temperature' has length equal to 11)

/**********************************************************
Designer:  Firzendragon
Data:      10/10/2011
Version:   1.0
***********************************************************
Instruction Set : read, write
Type Set        : temperature, motion, sound, light
***********************************************************
Instruction Format : (Remenber to add one space in the end)
    ID read type:pin:value 
    ID write pin:value 
***********************************************************
Sensor Functions:
    temperatureAndHumidity (digitalPin, digitalPin + 1)
    motionRead (digitalPin)
    soundRead (analogPin)
    lightRea (analogPin)
***********************************************************
Instruction Functions:
    readExecution:
        1) print the successful response
        2) restart the loopCounter
    readInstruction:
        1) get type, pin and value
        2) store type, pin and value
        3) goto readExecution
    writeExecution:
        1) open the digital pin to OUTPUT and write
        2) print the successful response
        3) set the write flag
    writeInstruction:
        1) get pin and value
        2) goto writeExecution
    analyzeInstruction:
        1) Is ID right?
              Is read?  go to readInstruction
              Is write? go to writeInstruction
    getInstruction:
        1) get string (ex: 'A read temperature:6:1000 ')
***********************************************************
Constraints:
    One type, one sensor, for one node ID on one board.
    Wrong instruction is useless.
***********************************************************/

char type[MaxNumPins][MaxType];
int pin[MaxNumPins];
int value[MaxNumPins];
int configureMaxIndex = 0;

int loopCounter = 0;
int writeFlag   = 0;

int temperatureAndHumidityRead(int readFlag, int dataPin, int clockPin) {
    SHT1x sht1x(dataPin, clockPin);

    // Read values from the sensor
    float temperature = sht1x.readTemperatureC();
    float humidity    = sht1x.readHumidity();
    
    // print sensor node ID
    if(readFlag == 0) {
        readFlag = 1;
        Serial.print(nodeID);
    }
    Serial.print(",");
    Serial.print("temperature:");
    Serial.print(temperature, DEC);
    Serial.print(",");
    Serial.print("humidity:");
    Serial.print(humidity, DEC);
    
    return readFlag;
}
int motionRead(int readFlag, int motionPin) {
    // print sensor node ID
    if(readFlag == 0) {
        readFlag = 1;
        Serial.print(nodeID);
    }
    Serial.print(",");
    Serial.print("motion:");
    Serial.print(digitalRead(motionPin));
    
    return readFlag;
}
int soundRead(int readFlag, int soundPin) {
    // print sensor node ID
    if(readFlag == 0) {
        readFlag = 1;
        Serial.print(nodeID);
    }
    Serial.print(",");
    Serial.print("sound:");
    Serial.print(analogRead(soundPin));
    
    return readFlag;
}
int lightRead(int readFlag, int lightPin) {
    // print sensor node ID
    if(readFlag == 0) {
        readFlag = 1;
        Serial.print(nodeID);
    }
    Serial.print(",");
    Serial.print("light:");
    Serial.print(analogRead(lightPin));
    
    return readFlag;
}
void readExecution(int configureMaxIndex) {
    for(int i=0; i<configureMaxIndex; i++)  // Successful response
    {
        Serial.print("Success read ");
        Serial.print(type[i]);
        Serial.print(":");
        Serial.print(pin[i]);
        Serial.print(":");
        Serial.print(value[i]);
        Serial.println(" ");
    }
    loopCounter = 0;  // Restart
}
void readInstruction(char * pch) {
    /***** New a sensor type  *****/
    pch = strtok(NULL, " ");  // get remaining - format : type:pin:value
    pch = strtok(pch, ":");   // get remaining - format : type
                
    // If the type is new
    int configureIndex = configureMaxIndex;
                
    // If the type has been taken
    for(int i=0; i<configureMaxIndex; i++) {
        if(!strcmp(pch, type[i])) {
            configureIndex = i;
            configureMaxIndex--;  // sub new type count
            break;
        }
    }
                
    // Store into type array
    for(int i=0; i<MaxType; i++)
        type[configureIndex][i] = *pch++;
            
    /***** New a sensor pin *****/
    pch = strtok(NULL, ":");   // get remaining - format : pin
    pin[configureIndex] = atoi(pch);
                
    /***** New a sensor value (sample rate) *****/
    pch = strtok(NULL, ":");   // get remaining - format : value
    value[configureIndex] = atoi(pch);
                
    // add new type count
    configureMaxIndex += 1;

    readExecution(configureMaxIndex);
}
void writeExecution(int pin, int value) {
    pinMode(pin, OUTPUT);
    digitalWrite(pin,value);
    //digitalWrite(pin,1);
    //delay(500);
    //digitalWrite(pin,0);
    
    Serial.print(nodeID);
    Serial.print(" success write ");
    Serial.print(pin);
    Serial.print(":");
    Serial.print(value);
    Serial.println(" ");
    
    writeFlag = 1;
}
void writeInstruction(char * pch) {
    pch = strtok(NULL, " ");  // Get remaining - pin:value
    pch = strtok(pch, ":");   // Get remaining - pin
    int pin = atoi(pch);
    pch = strtok(NULL, " ");  // Get remaining - value
    int value = atoi(pch);
                
    writeExecution(pin, value);
}
void analyzeInstruction (char str[]) {
    // If ID is right - format: ID read type:pin:value
    char * pch = strtok(str, " ");
    if(!strcmp(pch, nodeID))
    {
        // Execute the instruction - format: read type:pin:value
        pch = strtok(NULL, " ");
        if(!strcmp(pch, "read"))        readInstruction(pch);
        else if(!strcmp(pch, "write"))  writeInstruction(pch);
    }
}
void getInstruction() {
    char str[MaxNumStrs];
    int stringCounter = 0;
    while(Serial.available()) { 
        char getData = Serial.read();
        str[stringCounter++] = getData;
        delay(10);
    }
    
    if(stringCounter != 0)
        analyzeInstruction(str);
}

void setup() {
    Serial.begin(9600);
    Serial.println("Starting up");
}
void loop() {
    getInstruction();
  
    /***** Read Sensor Value *****/
    if(writeFlag == 0) {
        // Set for printing sensor node ID
        int readFlag = 0;
        
        // For all the sensors
        for(int configureIndex = 0; configureIndex < configureMaxIndex; configureIndex ++)
        {
            // Timer fired
            if((value[configureIndex] != 0) && (loopCounter % value[configureIndex] == 0))
            {
                if(!strcmp(type[configureIndex], "temperature")) { readFlag = temperatureAndHumidityRead(readFlag, pin[configureIndex], pin[configureIndex]+1); }
                else if(!strcmp(type[configureIndex], "motion")) { readFlag = motionRead(readFlag, pin[configureIndex]); }
                else if(!strcmp(type[configureIndex], "sound"))  { readFlag = soundRead(readFlag, pin[configureIndex]);  }
                else if(!strcmp(type[configureIndex], "light"))  { readFlag = lightRead(readFlag, pin[configureIndex]);  }
            }
        }
        
        // Next line
        if(readFlag == 1)
            Serial.println(" ");
    }
    else {
        writeFlag = 0;
    }
    
    /***** Delay Counter *****/
    loopCounter = (loopCounter + 1) % 60000;
    delay(1);
}
