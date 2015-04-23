#
# >> mysql -u root -p
# running this script using mysql client:
# :\mysql\bin\mysql --local-infile=1 -u root -p < CreateDatabase.sql
# server start: sudo start mysql
#           or: sudo service mysql start
#

create database cr_dealz CHARACTER SET = utf8;

use cr_dealz;

CREATE TABLE `members` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) DEFAULT NULL,
  `firstName` varchar(45) DEFAULT NULL,
  `lastName` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `hash` varchar(200) DEFAULT NULL,
  `salt` varchar(45) DEFAULT NULL,
  `phone` varchar(45) DEFAULT NULL,
  `carrier` int(11) DEFAULT '0',
  `autolog` int(11) DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `products` (
  `uid` int(11) NOT NULL,
  `asin` varchar(45) NOT NULL,
  `title` varchar(45) DEFAULT NULL,
  `maker` varchar(45) DEFAULT NULL,
  `priority` int(11) DEFAULT '0',
  `category` varchar(45) DEFAULT NULL,
  `reputation` int(11) DEFAULT NULL,
  `price_below` decimal(6,2) DEFAULT NULL,
  `lowest_price` decimal(6,2) DEFAULT NULL,
  PRIMARY KEY (`uid`,`asin`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `carrier` (
  `cid` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `handle` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;