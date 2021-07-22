ALTER TABLE commits CHANGE c_highest_branch c_branch varchar(128) NULL;

TRUNCATE commits;
INSERT INTO `commits` (`c_id`, `c_hash`, `c_author`, `c_date`, `c_message`, `c_url`, `c_project_name`, `c_repository_name`, `c_repository_url`, `c_branch`) VALUES
(1,	'normal',	'goal:FOO-45',	'2021-07-02 18:49:29',	'',	'',	'',	'e19d',	'',	'FOO-45'),
(2,	'normal',	'goal:FOO-45',	'2021-07-02 10:32:09',	'',	'',	'',	'e19d',	'',	'FOO-1'),

(3,	'normal',	'goal:FOO-400',	'2021-07-02 16:32:09',	'',	'',	'',	'e20d',	'',	'FOO-400'),

(4,	'normalM',	'goal:master',	'2021-07-02 14:32:42',	'',	'',	'',	'e21d',	'',	'ATest2'),
(5,	'normalM',	'goal:master',	'2021-07-02 14:32:10',	'',	'',	'',	'e21d',	'',	'master'),
(6,	'normalM',	'goal:master',	'2021-07-02 14:32:09',	'',	'',	'',	'e21d',	'',	'ATest1'),

(7,	'normalSD',	'goal:staging',	'2021-07-05 14:32:42',	'',	'',	'',	'e22d',	'',	'staging'),
(8,	'normalSD',	'goal:staging',	'2021-07-05 14:32:10',	'',	'',	'',	'e22d',	'',	'develop'),
(9,	'normalSD',	'goal:staging',	'2021-07-05 14:32:09',	'',	'',	'',	'e22d',	'',	'FOO-123'),

(10,	'SDM',	        'goal:master',	'2021-07-05 14:32:42',	'',	'',	'',	'e23d',	'',	'staging'),
(11,	'SDM',	        'goal:master',	'2021-07-05 14:32:10',	'',	'',	'',	'e23d',	'',	'develop'),
(12,	'SDM',	        'goal:master',	'2021-07-05 14:32:09',	'',	'',	'',	'e23d',	'',	'master'),

(13,	'normal',	'goal:FOO-453',	'2021-07-05 14:32:42',	'',	'',	'',	'e24d',	'',	'FOO-453'),
(14,	'normal',	'goal:FOO-453',	'2021-07-05 14:32:10',	'',	'',	'',	'e24d',	'',	'FOO-234'),
(15,	'normal',	'goal:FOO-453',	'2021-07-05 14:32:09',	'',	'',	'',	'e24d',	'',	'FOO-453'),

(16,	'MDB',	        'goal:master',	'2021-07-05 14:32:42',	'',	'',	'',	'e24d',	'',	'master'),
(17,	'MDB',	        'goal:master',	'2021-07-05 14:32:10',	'',	'',	'',	'e24d',	'',	'develop'),
(19,	'MDB',	        'goal:master',	'2021-07-05 14:32:09',	'',	'',	'',	'e24d',	'',	'FOO-123'),

(20,	'DBB',	        'goal:develop',	'2021-07-05 14:32:42',	'',	'',	'',	'e24d',	'',	'FOO-234'),
(21,	'DBB',	        'goal:develop',	'2021-07-05 14:32:10',	'',	'',	'',	'e24d',	'',	'FOO-382'),
(22,	'DBB',	        'goal:develop',	'2021-07-05 14:32:09',	'',	'',	'',	'e24d',	'',	'develop'),

(23,	'DB',	        'goal:develop',	'2021-07-05 14:32:42',	'',	'',	'',	'e24d',	'',	'develop'),
(24,	'DB',	        'goal:develop',	'2021-07-05 14:32:10',	'',	'',	'',	'e24d',	'',	'FOO-382')

;
