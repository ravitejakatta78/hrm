CREATE TABLE `users` ( 
	`id` INT NOT NULL AUTO_INCREMENT ,
	`name` VARCHAR(100) NOT NULL ,
	`email` VARCHAR(100) NOT NULL ,
	`mobile` VARCHAR(30) NOT NULL ,
	`password` VARCHAR(255) NOT NULL ,
	`status` INT(3) NOT NULL ,
	`created_on` DATETIME NOT NULL ,
	`created_by` VARCHAR(100) NOT NULL ,
	`updated_on` DATETIME NULL DEFAULT NULL ,
	`updated_by` VARCHAR(100) NULL DEFAULT NULL ,
	PRIMARY KEY (`id`)
	) ENGINE = InnoDB; 
 
 
INSERT INTO `users` (`id`, `name`, `email`, `mobile`, `password`, `status`, `created_on`, `created_by`, `updated_on`, `updated_by`) VALUES (NULL, 'admin', 'admin@hrm.com', '9999999999', '21232f297a57a5a743894a0e4a801fc3', '1', '2021-05-23 15:00:24.000000', 'admin', '2021-05-23 15:00:24.000000', NULL); 