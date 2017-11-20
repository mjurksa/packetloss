import sys
import ConfigParser

Config = ConfigParser.SafeConfigParser()
Config.read(sys.argv[1])

#Config.add_section(sys.argv[2])
Config.set(sys.argv[2], sys.argv[3] , sys.argv[4])

with open(sys.argv[1], 'w') as configfile:
    Config.write(configfile)
