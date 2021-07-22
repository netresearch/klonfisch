-- Use until v1.1.1

ALTER TABLE commits
CHANGE c_branch c_highest_branch varchar(128) NULL;

DELETE t1
FROM commits t1
JOIN (
SELECT c_hash, c_repository_name, Max(c_date) AS maxDate, COUNT(*)
FROM commits
GROUP BY c_hash, c_repository_name
HAVING count(*) > 1 ) t2
ON t1.c_hash = t2.c_hash
AND t1.c_repository_name = t2.c_repository_name
AND t1.c_date != t2.maxDate
WHERE t1.c_highest_branch
NOT IN ('develop', 'showroom', 'staging', 'master');

DELETE t1
FROM commits t1
JOIN (
SELECT c_hash, c_repository_name, COUNT(*)
FROM commits
GROUP BY c_hash, c_repository_name
HAVING count(*) > 1 ) t2
ON t1.c_hash = t2.c_hash
AND t1.c_repository_name = t2.c_repository_name
WHERE t1.c_highest_branch
NOT IN ('develop', 'showroom', 'staging', 'master');

DELETE t1
FROM commits t1
JOIN (
SELECT c_hash, c_repository_name, COUNT(*)
FROM commits
GROUP BY c_hash, c_repository_name
HAVING count(*) > 1 ) t2
ON t1.c_hash = t2.c_hash
AND t1.c_repository_name = t2.c_repository_name
WHERE t1.c_hash IN (
SELECT c_hash FROM (
SELECT c_hash FROM commits WHERE c_highest_branch = 'master') t3)
AND t1.c_highest_branch != 'master';

DELETE t1
FROM commits t1
JOIN (
SELECT c_hash, c_repository_name, COUNT(*)
FROM commits
GROUP BY c_hash, c_repository_name
HAVING count(*) > 1 ) t2
ON t1.c_hash = t2.c_hash
AND t1.c_repository_name = t2.c_repository_name
WHERE t1.c_hash IN (
SELECT c_hash FROM (
SELECT c_hash FROM commits WHERE c_highest_branch = 'staging') t3)
AND t1.c_highest_branch != 'staging';

DELETE t1
FROM commits t1
JOIN (
SELECT c_hash, c_repository_name, COUNT(*)
FROM commits
GROUP BY c_hash, c_repository_name
HAVING count(*) > 1 ) t2
ON t1.c_hash = t2.c_hash
AND t1.c_repository_name = t2.c_repository_name
WHERE t1.c_hash IN (
SELECT c_hash FROM (
SELECT c_hash FROM commits WHERE c_highest_branch = 'showroom') t3)
AND t1.c_highest_branch != 'showroom';

DELETE t1
FROM commits t1
JOIN (
SELECT c_hash, c_repository_name, COUNT(*)
FROM commits
GROUP BY c_hash, c_repository_name
HAVING count(*) > 1 ) t2
ON t1.c_hash = t2.c_hash
AND t1.c_repository_name = t2.c_repository_name
WHERE t1.c_hash IN (
SELECT c_hash FROM (
SELECT c_hash FROM commits WHERE c_highest_branch = 'develop') t3)
AND t1.c_highest_branch != 'develop';

DELETE keywords_commits FROM keywords_commits
WHERE keywords_commits.c_id NOT IN ( SELECT c_id FROM commits);
