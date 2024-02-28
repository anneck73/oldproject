CREATE TABLE m_m_profile_image (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, mimeType VARCHAR(255) NOT NULL, image_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE mm_user (id INT AUTO_INCREMENT NOT NULL, profile_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)', facebook_id VARCHAR(255) DEFAULT NULL, facebook_access_token VARCHAR(255) DEFAULT NULL, google_id VARCHAR(255) DEFAULT NULL, google_access_token VARCHAR(255) DEFAULT NULL, terms TINYINT(1) NOT NULL, over18 TINYINT(1) NOT NULL, hash VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_D5F3BF9092FC23A8 (username_canonical), UNIQUE INDEX UNIQ_D5F3BF90A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_D5F3BF90C05FB297 (confirmation_token), UNIQUE INDEX UNIQ_D5F3BF90D1B862B8 (hash), INDEX IDX_D5F3BF90CCFA12B8 (profile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE guests_meals (mmuser_id INT NOT NULL, meal_id INT NOT NULL, INDEX IDX_7AC373743AC9CBDC (mmuser_id), INDEX IDX_7AC37374639666D6 (meal_id), PRIMARY KEY(mmuser_id, meal_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE base_meal_guests (mmuser_id INT NOT NULL, base_meal_id INT NOT NULL, INDEX IDX_2B3EF7433AC9CBDC (mmuser_id), INDEX IDX_2B3EF7435790DFB2 (base_meal_id), PRIMARY KEY(mmuser_id, base_meal_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE m_m_user_profile (id INT AUTO_INCREMENT NOT NULL, payPalEmail VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, selfDescription LONGTEXT DEFAULT NULL, imageName VARCHAR(255) DEFAULT NULL, firstName VARCHAR(255) DEFAULT NULL, lastName VARCHAR(255) DEFAULT NULL, addressLine1 VARCHAR(255) DEFAULT NULL, addressLine2 VARCHAR(255) DEFAULT NULL, areaCode VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, age INT DEFAULT NULL, gender VARCHAR(255) DEFAULT NULL, hobbies VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE join_request (id INT AUTO_INCREMENT NOT NULL, meal_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, hash VARCHAR(255) NOT NULL, messageToHost VARCHAR(255) NOT NULL, messageToGuest VARCHAR(255) DEFAULT NULL, status VARCHAR(25) NOT NULL, accepted TINYINT(1) NOT NULL, denied TINYINT(1) NOT NULL, payed TINYINT(1) NOT NULL, sort INT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_E932E4FFD1B862B8 (hash), INDEX IDX_E932E4FF639666D6 (meal_id), INDEX IDX_E932E4FFB03A8386 (created_by_id), INDEX IDX_E932E4FF896DBBDE (updated_by_id), INDEX IDX_E932E4FFC76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE thread_metadata (id INT AUTO_INCREMENT NOT NULL, thread_id INT DEFAULT NULL, participant_id INT DEFAULT NULL, is_deleted TINYINT(1) NOT NULL, last_participant_message_date DATETIME DEFAULT NULL, last_message_date DATETIME DEFAULT NULL, INDEX IDX_40A577C8E2904019 (thread_id), INDEX IDX_40A577C89D1C3019 (participant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, thread_id INT DEFAULT NULL, sender_id INT DEFAULT NULL, body LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_B6BD307FE2904019 (thread_id), INDEX IDX_B6BD307FF624B39D (sender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE thread (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, is_spam TINYINT(1) NOT NULL, INDEX IDX_31204C83B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE message_metadata (id INT AUTO_INCREMENT NOT NULL, message_id INT DEFAULT NULL, participant_id INT DEFAULT NULL, is_read TINYINT(1) NOT NULL, INDEX IDX_4632F005537A1329 (message_id), INDEX IDX_4632F0059D1C3019 (participant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE email (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, status VARCHAR(25) NOT NULL, environment VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE meal (id INT AUTO_INCREMENT NOT NULL, host_id INT DEFAULT NULL, hash VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, startDateTime DATETIME DEFAULT NULL, maxGuest INT NOT NULL, starter VARCHAR(255) DEFAULT NULL, main VARCHAR(255) NOT NULL, desert VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, sharedCost DOUBLE PRECISION NOT NULL, sharedCostCurrency VARCHAR(255) NOT NULL, locationAddress VARCHAR(255) NOT NULL, locationLat DOUBLE PRECISION DEFAULT NULL, locationLong DOUBLE PRECISION DEFAULT NULL, status VARCHAR(12) NOT NULL, sort INT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_9EF68E9C1FB8D185 (host_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE meal_to_category (meal_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_C6EF81E7639666D6 (meal_id), INDEX IDX_C6EF81E712469DE2 (category_id), PRIMARY KEY(meal_id, category_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE invites (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, email_used VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_37E6A6CE671439D (email_used), INDEX IDX_37E6A6CB03A8386 (created_by_id), INDEX IDX_37E6A6C896DBBDE (updated_by_id), INDEX IDX_37E6A6CC76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE meal_ticket (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, hash VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, sharedCosts DOUBLE PRECISION NOT NULL, mmFee DOUBLE PRECISION NOT NULL, titel VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, number VARCHAR(255) NOT NULL, location POINT DEFAULT NULL, sort INT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, Ticket_Host_id INT DEFAULT NULL, Ticket_Guest_id INT DEFAULT NULL, Ticket_Meal_id INT DEFAULT NULL, INDEX IDX_F8AA10AA11ADC993 (Ticket_Host_id), INDEX IDX_F8AA10AA6E900611 (Ticket_Guest_id), INDEX IDX_F8AA10AA6D837EC0 (Ticket_Meal_id), INDEX IDX_F8AA10AAB03A8386 (created_by_id), INDEX IDX_F8AA10AA896DBBDE (updated_by_id), INDEX IDX_F8AA10AAC76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE meal_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, meal_id INT DEFAULT NULL, location_address VARCHAR(255) NOT NULL, country VARCHAR(255) DEFAULT NULL, countryCode VARCHAR(5) DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, postalCode VARCHAR(255) DEFAULT NULL, streetName VARCHAR(255) DEFAULT NULL, streetNumber VARCHAR(255) DEFAULT NULL, extraLine1 VARCHAR(255) DEFAULT NULL, exraLine2 VARCHAR(255) DEFAULT NULL, locality VARCHAR(255) DEFAULT NULL, sublocality VARCHAR(255) DEFAULT NULL, Description VARCHAR(255) DEFAULT NULL, location POINT DEFAULT NULL, sort INT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_D4E6F81639666D6 (meal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE same_address (id INT AUTO_INCREMENT NOT NULL, combined_location_string VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_9792C62DD1B862B8 (hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE same_address_to_meal_address (meal_address_id INT NOT NULL, same_address_id INT NOT NULL, INDEX IDX_8F16D0E62DB19FF (meal_address_id), UNIQUE INDEX UNIQ_8F16D0ED57592B4 (same_address_id), PRIMARY KEY(meal_address_id, same_address_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE base_meal (id INT AUTO_INCREMENT NOT NULL, host_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, startDateTime DATETIME NOT NULL, maxGuest INT NOT NULL, description LONGTEXT DEFAULT NULL, sharedCost DOUBLE PRECISION NOT NULL, sharedCostCurrency VARCHAR(3) NOT NULL, status VARCHAR(12) NOT NULL, hash VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, meal_type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_895406CD1B862B8 (hash), INDEX IDX_895406C1FB8D185 (host_id), INDEX IDX_895406CB03A8386 (created_by_id), INDEX IDX_895406C896DBBDE (updated_by_id), INDEX IDX_895406CC76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE base_meal_to_category (base_meal_id INT NOT NULL, base_meal_category_id INT NOT NULL, INDEX IDX_F2E2EC425790DFB2 (base_meal_id), INDEX IDX_F2E2EC422BB1F6CF (base_meal_category_id), PRIMARY KEY(base_meal_id, base_meal_category_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE base_meal_to_meal_address (base_meal_id INT NOT NULL, meal_address_id INT NOT NULL, INDEX IDX_B24AD2145790DFB2 (base_meal_id), UNIQUE INDEX UNIQ_B24AD21462DB19FF (meal_address_id), PRIMARY KEY(base_meal_id, meal_address_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE private_meal (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE base_meal_category (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_EAE5370A5E237E06 (name), INDEX IDX_EAE5370AB03A8386 (created_by_id), INDEX IDX_EAE5370A896DBBDE (updated_by_id), INDEX IDX_EAE5370AC76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE business_meal (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE meal_address (id INT AUTO_INCREMENT NOT NULL, location_string VARCHAR(255) NOT NULL, country VARCHAR(255) DEFAULT NULL, countryCode VARCHAR(5) DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, postalCode VARCHAR(255) DEFAULT NULL, streetName VARCHAR(255) DEFAULT NULL, streetNumber VARCHAR(255) DEFAULT NULL, extraLine1 VARCHAR(255) DEFAULT NULL, exraLine2 VARCHAR(255) DEFAULT NULL, locality VARCHAR(255) DEFAULT NULL, sublocality VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, bounds LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)', hash VARCHAR(255) NOT NULL, location POINT DEFAULT NULL, sort INT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_74FDDD98D1B862B8 (hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE mm_game_score (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, name VARCHAR(64) NOT NULL, type VARCHAR(64) NOT NULL, value INT NOT NULL, location POINT DEFAULT NULL, sort INT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_F8309E0EB03A8386 (created_by_id), INDEX IDX_F8309E0E896DBBDE (updated_by_id), INDEX IDX_F8309E0EC76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE mm_game_currency (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(3) NOT NULL, name VARCHAR(32) NOT NULL, base_value DOUBLE PRECISION NOT NULL, hash VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_DE79FD94D1B862B8 (hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE paypal_payment_token (id INT AUTO_INCREMENT NOT NULL, meal_ticket_id INT DEFAULT NULL, tokenReq LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', tokenResp LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', tokenError LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', tokenHash VARCHAR(255) NOT NULL, tokenKey VARCHAR(255) NOT NULL, tokenStatus VARCHAR(255) NOT NULL, INDEX IDX_CFD1C676CE6F45D9 (meal_ticket_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE mm_user ADD CONSTRAINT FK_D5F3BF90CCFA12B8 FOREIGN KEY (profile_id) REFERENCES m_m_user_profile (id);
ALTER TABLE guests_meals ADD CONSTRAINT FK_7AC373743AC9CBDC FOREIGN KEY (mmuser_id) REFERENCES mm_user (id) ON DELETE CASCADE;
ALTER TABLE guests_meals ADD CONSTRAINT FK_7AC37374639666D6 FOREIGN KEY (meal_id) REFERENCES meal (id) ON DELETE CASCADE;
ALTER TABLE base_meal_guests ADD CONSTRAINT FK_2B3EF7433AC9CBDC FOREIGN KEY (mmuser_id) REFERENCES mm_user (id) ON DELETE CASCADE;
ALTER TABLE base_meal_guests ADD CONSTRAINT FK_2B3EF7435790DFB2 FOREIGN KEY (base_meal_id) REFERENCES base_meal (id) ON DELETE CASCADE;
ALTER TABLE join_request ADD CONSTRAINT FK_E932E4FF639666D6 FOREIGN KEY (meal_id) REFERENCES meal (id);
ALTER TABLE join_request ADD CONSTRAINT FK_E932E4FFB03A8386 FOREIGN KEY (created_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE join_request ADD CONSTRAINT FK_E932E4FF896DBBDE FOREIGN KEY (updated_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE join_request ADD CONSTRAINT FK_E932E4FFC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE thread_metadata ADD CONSTRAINT FK_40A577C8E2904019 FOREIGN KEY (thread_id) REFERENCES thread (id);
ALTER TABLE thread_metadata ADD CONSTRAINT FK_40A577C89D1C3019 FOREIGN KEY (participant_id) REFERENCES mm_user (id);
ALTER TABLE message ADD CONSTRAINT FK_B6BD307FE2904019 FOREIGN KEY (thread_id) REFERENCES thread (id);
ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES mm_user (id);
ALTER TABLE thread ADD CONSTRAINT FK_31204C83B03A8386 FOREIGN KEY (created_by_id) REFERENCES mm_user (id);
ALTER TABLE message_metadata ADD CONSTRAINT FK_4632F005537A1329 FOREIGN KEY (message_id) REFERENCES message (id);
ALTER TABLE message_metadata ADD CONSTRAINT FK_4632F0059D1C3019 FOREIGN KEY (participant_id) REFERENCES mm_user (id);
ALTER TABLE meal ADD CONSTRAINT FK_9EF68E9C1FB8D185 FOREIGN KEY (host_id) REFERENCES mm_user (id);
ALTER TABLE meal_to_category ADD CONSTRAINT FK_C6EF81E7639666D6 FOREIGN KEY (meal_id) REFERENCES meal (id);
ALTER TABLE meal_to_category ADD CONSTRAINT FK_C6EF81E712469DE2 FOREIGN KEY (category_id) REFERENCES meal_categories (id);
ALTER TABLE invites ADD CONSTRAINT FK_37E6A6CB03A8386 FOREIGN KEY (created_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE invites ADD CONSTRAINT FK_37E6A6C896DBBDE FOREIGN KEY (updated_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE invites ADD CONSTRAINT FK_37E6A6CC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE meal_ticket ADD CONSTRAINT FK_F8AA10AA11ADC993 FOREIGN KEY (Ticket_Host_id) REFERENCES mm_user (id);
ALTER TABLE meal_ticket ADD CONSTRAINT FK_F8AA10AA6E900611 FOREIGN KEY (Ticket_Guest_id) REFERENCES mm_user (id);
ALTER TABLE meal_ticket ADD CONSTRAINT FK_F8AA10AA6D837EC0 FOREIGN KEY (Ticket_Meal_id) REFERENCES meal (id);
ALTER TABLE meal_ticket ADD CONSTRAINT FK_F8AA10AAB03A8386 FOREIGN KEY (created_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE meal_ticket ADD CONSTRAINT FK_F8AA10AA896DBBDE FOREIGN KEY (updated_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE meal_ticket ADD CONSTRAINT FK_F8AA10AAC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE address ADD CONSTRAINT FK_D4E6F81639666D6 FOREIGN KEY (meal_id) REFERENCES meal (id);
ALTER TABLE same_address_to_meal_address ADD CONSTRAINT FK_8F16D0E62DB19FF FOREIGN KEY (meal_address_id) REFERENCES same_address (id);
ALTER TABLE same_address_to_meal_address ADD CONSTRAINT FK_8F16D0ED57592B4 FOREIGN KEY (same_address_id) REFERENCES meal_address (id);
ALTER TABLE base_meal ADD CONSTRAINT FK_895406C1FB8D185 FOREIGN KEY (host_id) REFERENCES mm_user (id);
ALTER TABLE base_meal ADD CONSTRAINT FK_895406CB03A8386 FOREIGN KEY (created_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE base_meal ADD CONSTRAINT FK_895406C896DBBDE FOREIGN KEY (updated_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE base_meal ADD CONSTRAINT FK_895406CC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE base_meal_to_category ADD CONSTRAINT FK_F2E2EC425790DFB2 FOREIGN KEY (base_meal_id) REFERENCES base_meal (id) ON DELETE CASCADE;
ALTER TABLE base_meal_to_category ADD CONSTRAINT FK_F2E2EC422BB1F6CF FOREIGN KEY (base_meal_category_id) REFERENCES base_meal_category (id) ON DELETE CASCADE;
ALTER TABLE base_meal_to_meal_address ADD CONSTRAINT FK_B24AD2145790DFB2 FOREIGN KEY (base_meal_id) REFERENCES base_meal (id);
ALTER TABLE base_meal_to_meal_address ADD CONSTRAINT FK_B24AD21462DB19FF FOREIGN KEY (meal_address_id) REFERENCES meal_address (id);
ALTER TABLE private_meal ADD CONSTRAINT FK_E34FA22BF396750 FOREIGN KEY (id) REFERENCES base_meal (id) ON DELETE CASCADE;
ALTER TABLE base_meal_category ADD CONSTRAINT FK_EAE5370AB03A8386 FOREIGN KEY (created_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE base_meal_category ADD CONSTRAINT FK_EAE5370A896DBBDE FOREIGN KEY (updated_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE base_meal_category ADD CONSTRAINT FK_EAE5370AC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE business_meal ADD CONSTRAINT FK_CE6D8C91BF396750 FOREIGN KEY (id) REFERENCES base_meal (id) ON DELETE CASCADE;
ALTER TABLE mm_game_score ADD CONSTRAINT FK_F8309E0EB03A8386 FOREIGN KEY (created_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE mm_game_score ADD CONSTRAINT FK_F8309E0E896DBBDE FOREIGN KEY (updated_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE mm_game_score ADD CONSTRAINT FK_F8309E0EC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE paypal_payment_token ADD CONSTRAINT FK_CFD1C676CE6F45D9 FOREIGN KEY (meal_ticket_id) REFERENCES meal_ticket (id);
