create table if not exists `[prefix]products` (
  `id` int(10) unsigned not null auto_increment,
  `name` varchar(255) not null,
  `item_number` varchar(50) not null,
  `price` decimal(8,2) not null,
  `options_1` text not null,
  `options_2` text not null,
  `custom` varchar(50) not null default 'none',
  `custom_desc` varchar(255) not null default '',
  `taxable` tinyint(1) unsigned not null,
  `shipped` tinyint(1) unsigned not null,
  `weight` decimal(8,2) unsigned not null default 0,
  `download_path` text,
  `download_limit` tinyint default 0,
  `recurring_interval` tinyint default 0,
  `recurring_unit` varchar(10) default 'months',
  `recurring_occurrences` tinyint unsigned not null default 0,
  `allow_cancel` tinyint default 1,
  `free_trial` varchar(20),
  `max_quantity` int(10) unsigned not null default 0,
  `gravity_form_id` int(10) unsigned not null default 0,
  `gravity_form_qty_id` int(10) unsigned not null default 0,
  primary key(`id`)
);

create table if not exists `[prefix]downloads` (
  `id` int(10) unsigned not null auto_increment,
  `duid` varchar(100),
  `downloaded_on` datetime null,
  `ip` varchar(50) not null,
  primary key(`id`)
);

create table if not exists `[prefix]promotions` (
  `id` int(10) unsigned not null auto_increment,
  `code` varchar(50) not null,
  `type` enum('dollar','percentage') not null default 'dollar',
  `amount` decimal(8,2),
  `min_order` decimal(8,2),
  primary key(`id`)
);

create table if not exists `[prefix]shipping_methods` (
  `id` int(10) unsigned not null auto_increment,
  `name` varchar(100) not null,
  `default_rate` decimal(8,2) not null,
  `default_bundle_rate` decimal(8,2) not null,
  `carrier` varchar(100) not null,
  `code` varchar(50) not null,
  primary key(`id`)
);

create table if not exists `[prefix]shipping_rates` (
  `id` int(10) unsigned not null auto_increment,
  `product_id` int(10) unsigned not null,
  `shipping_method_id` int(10) unsigned not null,
  `shipping_rate` decimal(8,2) not null,
  `shipping_bundle_rate` decimal(8,2) not null,
  primary key(`id`)
);

create table if not exists `[prefix]shipping_rules` (
  `id` int(10) unsigned not null auto_increment,
  `min_amount` decimal(8,2),
  `shipping_method_id` int(10) unsigned not null,
  `shipping_cost` decimal(8,2),
  primary key(`id`)
);

create table if not exists `[prefix]tax_rates` (
  `id` int(10) unsigned not null auto_increment,
  `state` varchar(20) not null,
  `zip_low` mediumint unsigned not null default 0,
  `zip_high` mediumint unsigned not null default 0,
  `rate` decimal(8,2) not null,
  `tax_shipping` tinyint(1) not null default 0,
  primary key(`id`)
);

create table if not exists `[prefix]cart_settings` (
  `key` varchar(50) not null,
  `value` text not null,
  primary key(`key`)
);

create table if not exists `[prefix]orders` (
  `id` int(10) unsigned not null auto_increment,
  `bill_first_name` varchar(50) not null,
  `bill_last_name` varchar(50) not null,
  `bill_address` varchar(150) not null,
  `bill_address2` varchar(150) not null,
  `bill_city` varchar(150) not null,
  `bill_state` varchar(50) not null,
  `bill_country` varchar(50) not null default '',
  `bill_zip` varchar(150) not null,
  `ship_first_name` varchar(50) not null,
  `ship_last_name` varchar(50) not null,
  `ship_address` varchar(150) not null,
  `ship_address2` varchar(150) not null,
  `ship_city` varchar(150) not null,
  `ship_state` varchar(50) not null,
  `ship_country` varchar(50) not null default '',
  `ship_zip` varchar(150) not null,
  `phone` varchar(15) not null,
  `email` varchar(100) not null,
  `coupon` varchar(50) null,
  `discount_amount` decimal(8,2) not null,
  `trans_id` varchar(25) not null,
  `shipping` decimal(8,2) not null,
  `subtotal` decimal(8,2) not null,
  `tax` decimal(8,2) not null,
  `total` decimal(8,2) not null,
  `ordered_on` datetime,
  `status` varchar(50) not null,
  `ip` varchar(50) not null,
  `ouid` varchar(100) not null,
  `shipping_method` varchar(50),
  `account_id` int(10) unsigned not null default 0,
  primary key(`id`)
);

create table if not exists `[prefix]order_items` (
  `id` int(10) unsigned not null auto_increment,
  `order_id` int(10) unsigned not null,
  `product_id` int(10) unsigned not null,
  `item_number` varchar(50) not null,
  `product_price` decimal(8,2) not null,
  `description` varchar(250) not null,
  `quantity` int(10) unsigned not null,
  `duid` varchar(100) null,
  `form_entry_ids` varchar(100) not null,
  primary key(`id`)
);

create table if not exists `[prefix]inventory` (
  `ikey` varchar(250) not null,
  `product_id` int(10) unsigned not null,
  `track` tinyint(1) unsigned not null default 0,
  `quantity` int(10) unsigned not null,
  primary key(`ikey`)
);

-- ===================================================
-- Added for PHPurchase Professional
-- ===================================================
create table if not exists `[prefix]accounts` (
  `id` int(10) unsigned not null auto_increment,
  `email` varchar(50) not null,
  `password` varchar(50) not null,
  `customer_id` varchar(50) not null,
  `payment_profile_id` varchar(50) null,
  `shipping_profile_id` varchar(50) null,
  `expire_date` date null,
  `created_at` datetime not null,
  primary key(`id`)
);

create table if not exists `[prefix]recurring_items` (
  `id` int(10) unsigned not null auto_increment,
  `account_id` int(10) unsigned not null,
  `order_item_id` int(10) unsigned not null,
  `amount` decimal(8,2) not null,
  `recurring_interval` tinyint not null,
  `recurring_unit` varchar(10) not null,
  `recurring_occurrences` tinyint unsigned not null default 0,
  `start_date` date not null,
  `status` enum('active', 'canceled', 'suspended', 'overdue', 'complete') not null default 'active',
  `payment_type` enum('cc', 'manual') not null default 'cc',
  `created_at` datetime not null,
  `updated_at` datetime not null,
  primary key(`id`)
);

create table if not exists `[prefix]recurring_transactions` (
  `id` int(10) unsigned not null auto_increment,
  `account_id` int(10) unsigned not null,
  `order_item_id` int(10) unsigned not null,
  `recurring_item_id` int(10) unsigned not null,
  `amount` decimal(8,2) not null,
  `payment_due_on` date not null,
  `created_at` datetime not null,
  `transaction_id` varchar(50) not null,
  primary key(`id`)
);

alter table [prefix]accounts add column `payment_profile_id` varchar(50) null;
alter table [prefix]accounts add column `shipping_profile_id` varchar(50) null;
alter table [prefix]accounts add column `expire_date` date null;

alter table [prefix]orders add column `account_id` int(10) unsigned not null default 0;

alter table [prefix]order_items add column `form_entry_ids` varchar(100) not null;

alter table [prefix]products add column `recurring_unit` varchar(10) default 'months';
alter table [prefix]products add column `recurring_occurrences` tinyint not null default 0;
alter table [prefix]products add column `recurring_interval` tinyint not null default 0;
alter table [prefix]products add column `allow_cancel` tinyint not null default 1;
alter table [prefix]products add column `free_trial` varchar(20);
alter table [prefix]products add column `max_quantity` int(10) unsigned not null default 0;
alter table [prefix]products add column `gravity_form_id` int(10) unsigned not null default 0;
alter table [prefix]products add column `gravity_form_qty_id` int(10) unsigned not null default 0;
alter table [prefix]products add column `weight` decimal(8,2) unsigned not null default 0;


alter table [prefix]shipping_methods add column `carrier` varchar(100) not null;
alter table [prefix]shipping_methods add column `code` varchar(50) not null;

alter table [prefix]tax_rates CHANGE state state varchar(20) not null;
