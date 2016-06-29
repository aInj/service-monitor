# service-monitor
A ping-based service monitor &amp; standard web client, initially created at March 2015 for my organisation.

# Setting up
The server folder contains the actual Python script (`server.py`) that conducts the routine checks. It runs as a background web server under port 1337 and retrives info about target servers to ping from `servers.cfg`. The configuration file is formatted in following syntax: "IP/hostname | server_name | server_description" (each server aline)

The server script is executable with no arguments: `python server.py`

# Usage &amp; Sample Client
The Python-based web server outputs a JSON file accessible on `http://SERVER_URL:1337`. Each listed server in the configuration file is represented by an array containing all relevant details which can be parsed later by a client:
{"status": bool, "desc": string, "name": string, "address": string}

The client folder contains a sample web client mainly written in PHP that parses the output and displays it nicely to users. As a bonus, the sample web client also contains a basic MySQL-based incident listing. Note that the web client is merely an example and could have been written more efficiently.