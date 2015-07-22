DROP TABLE IF EXISTS accumulator;
CREATE TABLE accumulator LIKE accumulator_bak;
INSERT accumulator SELECT * FROM accumulator_bak;
