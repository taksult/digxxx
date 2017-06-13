# coding: UTF-8
import sys
import soundcloud

argvs = sys.argv
argc = len(argvs)
if(argc != 2):
    print 'debug: arg count failure'
    quit()

client = soundcloud.Client(client_id = 'yourClientID')  #dev
track_param = argvs[1];
track = client.get('/resolve', url='http://soundcloud.com/' + track_param)

print track.id
