CREATE TABLE pages (
	show_page_title tinyint(3) DEFAULT '0' NOT NULL,
	hide_breadcrumb tinyint(3) DEFAULT '0' NOT NULL,
	page_title varchar(255) DEFAULT NULL,
	page_theme varchar(255) DEFAULT 'ib-theme-portal' NOT NULL,
	menue_layout tinyint(3) DEFAULT '0' NOT NULL,
	contact_person tinyint(3) DEFAULT NULL,
	contact_person_bg varchar(255) DEFAULT 'white' NOT NULL,
	hide_print_icon tinyint(3) DEFAULT '0' NOT NULL,	
	bubble_text varchar(255) DEFAULT NULL,
	bubble_link varchar(255) DEFAULT NULL,
	bubble_title_text varchar(255) DEFAULT NULL
);

