-- ============================================================================
-- Copyright (C) 2021 Frédéric France
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <https://www.gnu.org/licenses/>.
-- ============================================================================

CREATE TABLE llx_prune_oauth_token (
	rowid	        integer AUTO_INCREMENT PRIMARY KEY,
	service         varchar(64),							-- What king of key or token: 'Google', 'Stripe', 'auth-public-key', ...
	token 	        text,									-- token in serialize() format, of an object
	refreshtoken 	text,									-- refreshtoken
	fk_user         integer,								-- Id of user in llx_user
	email			varchar(255) NULL DEFAULT NULL,			-- email in case no user
	tms		        timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	entity	        integer DEFAULT 1
)ENGINE=innodb;
