# packetloss

A HTML/PHP/Python application to help tracking the Packetloss by pinging a server

### Notes

For this Programm to work you need any Linux OS. 
I will use Raspbian Lite version September 2017 for this guide but will add other OS examples later when i tested it. So the guide will vary depending on your OS. 

### Prerequisites

First we need a few programms befor we start. This should be easy to do as this is very basic. 

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

Install Python2. This may not be necessary because most Linux distributions come with Python2 preinstalled. Make sure you are using Python 2. You can Check.
```
python --version

Python 2.7.13
```

### Installing

Now we dive into Installing the Script. [Download](https://github.com/Juzzed/packetloss/archive/master.zip) the Latest version. 
```
wget https://github.com/Juzzed/packetloss/archive/master.zip
unzip master.zip
cd packetloss-master
```

Move the Script somewhere where it can be executed by Cronjob later on
```
mkdir /opt/packetloss/
mv script/packetloss.py /opt/packetloss/
```

Move php script to the corresponding HTML directory.I will access the page like this http://xxx.xxx.xxx.xxx/packetloss/ . Thats why i will create a subdirectory
```
mkdir /var/www/html/packetloss
mv html/index.php /var/www/html/packetloss/
```

Database. Create Tables as following. Or use dump script bellow.
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

or
```
mysql -u root 
create database packetloss;
exit
mysql -u root packetloss < database.sql
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


To test whether the Script works or not you can Unplug the Internet Connection. 

You can start the Python script manualy and look if you run into some kind of problems:
```
python /opt/packetloss/packetloss.py
```

## Authors

* **Mantautas Jurksa** - *Initial work* - [Juzzed](https://github.com/Juzzed)

See also the list of [contributors](https://github.com/Juzzed/packetloss/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
