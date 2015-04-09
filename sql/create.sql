#
# >> mysql -u root -p
# running this script using mysql client:
# :\mysql\bin\mysql --local-infile=1 -u root -p < CreateDatabase.sql
# server start: sudo start mysql
#           or: sudo service mysql start
#

create database taskdb CHARACTER SET = utf8;

use taskdb;

CREATE TABLE  `cr_demodb`.`members` (
`uid` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`username` VARCHAR( 45 ) NOT NULL ,
`firstName` VARCHAR( 45 ) NOT NULL ,
`lastName` VARCHAR( 45 ) NOT NULL ,
`email` VARCHAR( 45 ) NOT NULL ,
`hash` VARCHAR( 200 ) NOT NULL ,
`salt` VARCHAR( 45 ) NOT NULL ,
`phone` VARCHAR( 45 ) NOT NULL ,
`carrier` VARCHAR( 45 ) NOT NULL ,
PRIMARY KEY (  `uid` )
) ENGINE = MYISAM ;