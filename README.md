# A Raspberry Pi weather station

This is the repo for my [Raspberry Pi based weather station](https://www.oasw.co.uk/weather/about.html) Based around the original Raspberry Pi Model A, I first set this up in 2017 to simply provide a live external temperature reading. At the back end of 2018, I added firstly a wind annemometer, then rain guague and started logging the data. 

There are 3 components: the station itself, the logging software and a set of web pages which display the data.

## Station

### Hardware

The weather station itself comprises the Raspberry Pi, connected via it's GPIO to a DS18b20 temperature sensor and the (tipping bucket) rain gauge. The annemometer interfaces to an [AdaFruit ADS1115 ADC board](https://www.adafruit.com/product/1085), which in turn connects to the Pi via I2C. An additional DC/DC converter is used to provide the necessary voltage to the annemometer.

![schematic](station/schematic.png)

The annemometer is also [available from AdaFruit](https://www.adafruit.com/product/1733). There's not a lot of documentation on this, included in the repo is a [badly formatted PDF of the datasheet](station/C2192+datasheet_en.pdf) after pushing it through Google translate.

The rain gauge is intended for a Misol weather station, I picked this up on ebay.

It's also worth having a read through my write up in the link at the top of the page, this has a lot more detail including some info on the behaviour of the annemometer and spurious readings from the rain gauge.

## Software

The software running on the station consists of a [single Python 2.7 script](station/temp_broadcast.py) which is responsible for interrogating all of the sensors.

## Logger

## Web pages
