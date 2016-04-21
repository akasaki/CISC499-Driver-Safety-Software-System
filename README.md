# CISC499 Driver-Safety-Software-System: DriveSafe
Undergraduate project 2016

<b>Setup LAMP-like server</b>

We chose to use AMS webserver.  

Follow the tutorial to setup the server step by step:
http://docs.aws.amazon.com/quickstarts/latest/vmlaunch/welcome.html?icmpid=docs_console_new

Or you can choose other webservers as your preference.

<b>Setup Database with Torque</b>

Setup a database to store data collected by Torque, follow the tutorial created by Torque:
https://github.com/econpy/torque

<b>Code Files</b>

In <i>scripts</i>:
<p>Run scripts to create other supported tables in database.</p>

In <i>source-code</i>:
<p>Copy all source code files to your main path (for example, the path in AWS is /var/www/html/). </br> 
The main page of webportal is session.php</br>
OS_level.php, accel_level.php, brake_level.php, angle_level.php compute the score for each measurement and return a level and a score (used in APP). </br>
overall.php computes the total score combining all measurements.
</p>

In <i>data</i>:
<p>Combining all data we collected this semester. </br>
.csv file shows the data directly. </br>
Run .sql file to insert everything to your database.</br>
torque.sql includes everything we need for analysis (raw data, driver scores for each trip, surrounding and road conditions). </br>
raw_data.sql includes the data collected from OBD ONLY. </p>

<b>Application</b>
Download your APP code from https://github.com/rayrwen/DriveSafe and complie.
