# packetloss

A HTML/PHP/Python application to help track Packetloss by pinging a server

### Notes

This program works for any version of Linux OS. 
I will use Raspbian Lite version September 2017 for this guide but will add other OS examples after testing. The guide will be different depending on your OS. 

### Prerequisites

We need a few programs before we start.

###### SQL Server
###### Web Server
###### PHP and a PHP module for your SQL server
###### Python2 (should be installed)

Install any SQL server and a Webserver you comfy with:
```
sudo apt-get update
sudo apt-get install mysql-server apache2
```

Install PHP and modules. Preferably PHP7
```
sudo apt-get install php php-myql
```

Install PHP and modules. Preferably PHP7
```
sudo apt-get install php php-myql
```

Install Python2. This may not be necessary because most Linux distributions come with Python2 preinstalled. Make sure you are using Python 2. You can Check by using the following command:
```
python --version

Python 2.7.13
```

### Installing

Now we dive into installing the Script. [Download](https://github.com/Juzzed/packetloss/archive/master.zip) the Latest version. 

```
wget https://github.com/Juzzed/packetloss/archive/master.zip
unzip master.zip
cd packetloss-master
```

Move the Script and config somewhere they can be executed by Cronjob later.
```
mkdir /opt/packetloss/
mv script/* /opt/packetloss/
```
Change the config file if needed.

Move php script to the corresponding HTML directory. I will access the page like this http://xxx.xxx.xxx.xxx/packetloss/ . Thats why a subdirectory is needed.
```
mkdir /var/www/html/packetloss
mv html/index.php /var/www/html/packetloss/
```

You need to change the directory for the config in the php and python script if you have a different location for the scripts than /opt/packetloss/[scripts]

Database. Create Tables as following or use dump script below.
```
tblStatus:
+--------------+-------------+------+-----+---------+-------+
| Field        | Type        | Null | Key | Default | Extra |
+--------------+-------------+------+-----+---------+-------+
| idStatus     | int(11)     | NO   | PRI | NULL    |       |-|-------------------------------------------
| beschreibung | varchar(50) | YES  |     | NULL    |       |                                            |
+--------------+-------------+------+-----+---------+-------+                                            |
                                                                                                         |
 tblPackets:                                                                                     1:n     |
+--------------+-----------+------+-----+-------------------+-----------------------------+              |
| Field        | Type      | Null | Key | Default           | Extra                       |              |
+--------------+-----------+------+-----+-------------------+-----------------------------+              |
| id           | int(11)   | NO   | PRI | NULL              | auto_increment              |              |
| status       | int(11)   | YES  | MUL | NULL              |                             |>|-------------
| timestamp    | timestamp | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |
| packet_count | int(11)   | NO   |     | NULL              |                             |
+--------------+-----------+------+-----+-------------------+-----------------------------+
```
or
```
mysql -u root 
create database packetloss;
exit
mysql -u root packetloss < database.sql
```

Create a user:

```
CREATE USER 'user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON packetloss . * TO 'user'@'localhost';
```

Create cronjob for the Script
```
crontab -e
```

Add this line at the End.
```
* * * * * python /opt/packetloss/packetloss.py >/dev/null 2>&1
```
Done!
# ENJOY

## Running the tests


To test whether the script works or not you can unplug the Internet Connection. 

You can start the Python script manually and look if you run into any problems:
```
python /opt/packetloss/packetloss.py
```

## Authors

* **Mantautas Jurksa** - *Initial work* - [Juzzed](https://github.com/Juzzed)

See also the list of [contributors](https://github.com/Juzzed/packetloss/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
