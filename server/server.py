# ringo at inj / service-monitor / https://github.com/aInj/service-monitor/

from BaseHTTPServer import BaseHTTPRequestHandler, HTTPServer

import thread, time, json

PORT_NUMBER = 1337

servers = []
servers_status = ''

def import_cfg(config_file):
	with open(config_file, "r") as cfg_file:
		file_lines = cfg_file.readlines()
		
		global servers
		servers = []
		for line in file_lines:
			if line:
				line = line.replace('\n', '').replace('\r', '')
				server_config = line.split(' | ')
				servers.append(server_config)

def ping(host):
	import subprocess, platform, os
	ping_flag = "-n" if platform.system().lower() == "windows" else "-c"
	devnull = open(os.devnull, 'wb')
	return subprocess.call(['ping', ping_flag, '1', host], stdout=devnull, stderr=subprocess.STDOUT) == 0

class myHandler(BaseHTTPRequestHandler):
	def do_GET(self):
		self.send_response(200)
		self.send_header('Content-type','text/html')
		self.end_headers()
		
		if not servers_status:
			self.wfile.write("No status yet")
		else:
			self.wfile.write(servers_status)
		
		return

def ping_all():
	global servers_status
	
	statuses = []
	
	import_cfg("servers.cfg")
	
	for server in servers:
		ping_result = ping(server[0])
		statuses.append({'address': server[0], 'name': server[1], 'desc': server[2], 'status': ping_result})

	servers_status = json.dumps(statuses)

	print "Updated status"

def pinging_thread_main():
	while True:
		ping_all()
		time.sleep(5)
	
def main():
	thread.start_new_thread(pinging_thread_main, ())
	
	try:
		server = HTTPServer(('', PORT_NUMBER), myHandler)
		print 'Started service monitor on port ' , PORT_NUMBER

		server.serve_forever()

	except KeyboardInterrupt:
		print '^C received, shutting down service monitor'
		server.socket.close()

if __name__ == '__main__':
	main()
