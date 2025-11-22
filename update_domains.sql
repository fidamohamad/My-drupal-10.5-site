UPDATE node__body
SET body_value = REPLACE(body_value, 'landportal.info', 'landportal.org')
WHERE body_value LIKE '%landportal.info%';
UPDATE node_revision__body
SET body_value = REPLACE(body_value, 'landportal.info', 'landportal.org')
WHERE body_value LIKE '%landportal.info%';
UPDATE block_content__body
SET body_value = REPLACE(body_value, 'landportal.info', 'landportal.org')
WHERE body_value LIKE '%landportal.info%';
UPDATE block_content_revision__body
SET body_value = REPLACE(body_value, 'landportal.info', 'landportal.org')
WHERE body_value LIKE '%landportal.info%';
UPDATE cache_render SET data = REPLACE(data, 'landportal.info', 'landportal.org') WHERE data LIKE '%landportal.info%';
UPDATE cache_entity SET data = REPLACE(data, 'landportal.info', 'landportal.org') WHERE data LIKE '%landportal.info%';