create table `cell` (
	`id` int(11) NOT NULL auto_increment,
        `created` timestamp default now(),
        xcord int,
        ycord int,
	PRIMARY KEY  (`id`)
        -- TODO add a unique key xcord, ycord here
) ;