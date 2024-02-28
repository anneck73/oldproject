CREATE TABLE base_meal_guests (mmuser_id INT NOT NULL, base_meal_id INT NOT NULL, INDEX IDX_2B3EF7433AC9CBDC (mmuser_id), INDEX IDX_2B3EF7435790DFB2 (base_meal_id), PRIMARY KEY(mmuser_id, base_meal_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE base_meal (id INT AUTO_INCREMENT NOT NULL, host_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, startDateTime DATETIME NOT NULL, maxGuest INT NOT NULL, description LONGTEXT DEFAULT NULL, sharedCost DOUBLE PRECISION NOT NULL, sharedCostCurrency VARCHAR(3) NOT NULL, status VARCHAR(12) NOT NULL, hash VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, meal_type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_895406CD1B862B8 (hash), INDEX IDX_895406C1FB8D185 (host_id), INDEX IDX_895406CB03A8386 (created_by_id), INDEX IDX_895406C896DBBDE (updated_by_id), INDEX IDX_895406CC76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE base_meal_to_category (base_meal_id INT NOT NULL, base_meal_category_id INT NOT NULL, INDEX IDX_F2E2EC425790DFB2 (base_meal_id), INDEX IDX_F2E2EC422BB1F6CF (base_meal_category_id), PRIMARY KEY(base_meal_id, base_meal_category_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE base_meal_to_meal_address (base_meal_id INT NOT NULL, meal_address_id INT NOT NULL, INDEX IDX_B24AD2145790DFB2 (base_meal_id), UNIQUE INDEX UNIQ_B24AD21462DB19FF (meal_address_id), PRIMARY KEY(base_meal_id, meal_address_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE same_address (id INT AUTO_INCREMENT NOT NULL, combined_location_string VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_9792C62DD1B862B8 (hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE same_address_to_meal_address (meal_address_id INT NOT NULL, same_address_id INT NOT NULL, INDEX IDX_8F16D0E62DB19FF (meal_address_id), UNIQUE INDEX UNIQ_8F16D0ED57592B4 (same_address_id), PRIMARY KEY(meal_address_id, same_address_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE private_meal (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE base_meal_category (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_EAE5370A5E237E06 (name), INDEX IDX_EAE5370AB03A8386 (created_by_id), INDEX IDX_EAE5370A896DBBDE (updated_by_id), INDEX IDX_EAE5370AC76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE business_meal (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE meal_address (id INT AUTO_INCREMENT NOT NULL, location_string VARCHAR(255) NOT NULL, country VARCHAR(255) DEFAULT NULL, countryCode VARCHAR(5) DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, postalCode VARCHAR(255) DEFAULT NULL, streetName VARCHAR(255) DEFAULT NULL, streetNumber VARCHAR(255) DEFAULT NULL, extraLine1 VARCHAR(255) DEFAULT NULL, exraLine2 VARCHAR(255) DEFAULT NULL, locality VARCHAR(255) DEFAULT NULL, sublocality VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, bounds LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)', hash VARCHAR(255) NOT NULL, location POINT DEFAULT NULL, sort INT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_74FDDD98D1B862B8 (hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE base_meal_guests ADD CONSTRAINT FK_2B3EF7433AC9CBDC FOREIGN KEY (mmuser_id) REFERENCES mm_user (id) ON DELETE CASCADE;
ALTER TABLE base_meal_guests ADD CONSTRAINT FK_2B3EF7435790DFB2 FOREIGN KEY (base_meal_id) REFERENCES base_meal (id) ON DELETE CASCADE;
ALTER TABLE base_meal ADD CONSTRAINT FK_895406C1FB8D185 FOREIGN KEY (host_id) REFERENCES mm_user (id);
ALTER TABLE base_meal ADD CONSTRAINT FK_895406CB03A8386 FOREIGN KEY (created_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE base_meal ADD CONSTRAINT FK_895406C896DBBDE FOREIGN KEY (updated_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE base_meal ADD CONSTRAINT FK_895406CC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE base_meal_to_category ADD CONSTRAINT FK_F2E2EC425790DFB2 FOREIGN KEY (base_meal_id) REFERENCES base_meal (id) ON DELETE CASCADE;
ALTER TABLE base_meal_to_category ADD CONSTRAINT FK_F2E2EC422BB1F6CF FOREIGN KEY (base_meal_category_id) REFERENCES base_meal_category (id) ON DELETE CASCADE;
ALTER TABLE base_meal_to_meal_address ADD CONSTRAINT FK_B24AD2145790DFB2 FOREIGN KEY (base_meal_id) REFERENCES base_meal (id);
ALTER TABLE base_meal_to_meal_address ADD CONSTRAINT FK_B24AD21462DB19FF FOREIGN KEY (meal_address_id) REFERENCES meal_address (id);
ALTER TABLE same_address_to_meal_address ADD CONSTRAINT FK_8F16D0E62DB19FF FOREIGN KEY (meal_address_id) REFERENCES same_address (id);
ALTER TABLE same_address_to_meal_address ADD CONSTRAINT FK_8F16D0ED57592B4 FOREIGN KEY (same_address_id) REFERENCES meal_address (id);
ALTER TABLE private_meal ADD CONSTRAINT FK_E34FA22BF396750 FOREIGN KEY (id) REFERENCES base_meal (id) ON DELETE CASCADE;
ALTER TABLE base_meal_category ADD CONSTRAINT FK_EAE5370AB03A8386 FOREIGN KEY (created_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE base_meal_category ADD CONSTRAINT FK_EAE5370A896DBBDE FOREIGN KEY (updated_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE base_meal_category ADD CONSTRAINT FK_EAE5370AC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
ALTER TABLE business_meal ADD CONSTRAINT FK_CE6D8C91BF396750 FOREIGN KEY (id) REFERENCES base_meal (id) ON DELETE CASCADE;
