

create table `user` (
	`id` int(11) NOT NULL auto_increment,
	`created` timestamp default now(),
	`username` varchar(200) default NULL,
	`password` varchar(250) default NULL,
	`email` varchar(250) default NULL,
	`type` enum('user','admin','mod','fluffy') default 'user',
	`auth_code` varchar(20) default NULL,
	PRIMARY KEY  (`id`)
) ;

/* add the administrator account */
INSERT INTO user (username, password, email, type ) VALUES ("admin", "pasword", "admin@yournextapplication.com", "admin");
