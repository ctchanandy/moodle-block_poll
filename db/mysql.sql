CREATE TABLE mdl_block_poll (
    id BIGINT(10) unsigned NOT NULL auto_increment,
    name VARCHAR(64) NOT NULL,
    courseid BIGINT(10) unsigned NOT NULL,
    questiontext LONGTEXT NOT NULL,
    eligible VARCHAR(10) NOT NULL DEFAULT 'all',
    created BIGINT(10) NOT NULL,
CONSTRAINT  PRIMARY KEY (id)
) COMMENT='Contains response info for each poll in the poll block';


CREATE TABLE mdl_block_poll_option (
    id BIGINT(10) unsigned NOT NULL auto_increment,
    pollid BIGINT(10) unsigned NOT NULL,
    optiontext LONGTEXT NOT NULL,
CONSTRAINT  PRIMARY KEY (id)
) COMMENT='Contains options for each poll in the poll block';

ALTER TABLE mdl_block_poll_option COMMENT='Contains options for each poll in the poll block';

CREATE TABLE mdl_block_poll_response (
    id BIGINT(10) unsigned NOT NULL auto_increment,
    pollid BIGINT(10) unsigned NOT NULL,
    optionid BIGINT(10) unsigned NOT NULL,
    userid BIGINT(10) unsigned NOT NULL,
    submitted BIGINT(10) unsigned NOT NULL,
CONSTRAINT  PRIMARY KEY (id)
) COMMENT='Contains response info for each poll in the poll block';