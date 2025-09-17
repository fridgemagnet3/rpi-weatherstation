#!/usr/bin/python2
import socket
import time

# create socket to listen for the broadcast packets
# sent periodically by the temperature sensor rpi
temp_sfd = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
temp_sfd.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
# make this non-blocking
temp_sfd.setblocking(0)
listen_address = ('0.0.0.0',52003)
temp_sfd.bind(listen_address)

# read latest temperature from rpi4 broadcast packet
def read_temp(sfd):
    pending = True
    temp_available = False
    # keep reading whilst there are datagrams so we
    # get the latest
    while pending:
        try:
            temperature, address = sfd.recvfrom(256)
            temp_available = True
        except socket.error:
            pending = False
            continue
    # return latest temperature if available
    if temp_available:
        return temperature
    else:
        return None

while True:
    current_temp = read_temp(temp_sfd)
    if current_temp != None:
        print current_temp
    time.sleep(0.5)
