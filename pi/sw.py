#!/usr/bin/python
# -*- coding: utf-8 -*-

import pigpio
import codecs
import sys
import time
import warnings

import urllib.request
import urllib.parse

SW_PIN = 4

pi = pigpio.pi()
pi.set_mode(SW_PIN, pigpio.INPUT)
pi.set_pull_up_down(SW_PIN, pigpio.PUD_UP)

# GPSデフォルトは奈良高専情報工学科玄関
longitude =  '34.649070'
latitude  = '135.758757'

def alert_error():
    with codecs.open( '/home/pi/gps.log', 'r', 'utf-8' ) as f:
        gps = ''
        for line in f:
            gps = line.strip().replace(' ','')
        csv = gps.split(',')
        if( len(csv) >= 3 ):
            longitude = csv[1]
            latitude  = csv[2]
            print(gps)

            data = urllib.parse.urlencode({'msg_type':'error', 'longitude':longitude, 'latitude':latitude}).encode('utf-8')
            request = urllib.request.Request('https://alerterror.herokuapp.com/index.php', data)
            response = urllib.request.urlopen(request)
            print(response.getcode())
            
            data = urllib.parse.urlencode({"value1":gps,"value2":"http://www.info.nara-k.ac.jp/img/security.png"}).encode('utf-8')
            request = urllib.request.Request('https://maker.ifttt.com/trigger/AlertError/with/key/3dYLoos6TmUlBbn4ez8HP6GZGv4LqraF8nRCtDjXZt', data)
            response = urllib.request.urlopen(request)
            print(response.getcode())
#          html = response.read()
#          print(html.decode('utf-8'))

try:
#    cb = pi.callback(SW_PIN, pigpio.FALLING_EDGE, cb_interrupt)
    while True:
        if( pi.read(SW_PIN) == 0 ):
            alert_error()
        time.sleep(1)
except KeyboardInterrupt:
    print("W: interrupt received, stopping…")
finally:
#    cb.cancel()
    pi.stop()

sys.exit(0)

