USE railway;

DROP TABLE IF EXISTS products;

CREATE TABLE products (
  id INT NOT NULL AUTO_INCREMENT,
  data LONGTEXT NOT NULL,
  PRIMARY KEY (id)
);

INSERT INTO products (data) VALUES ('Computer Table');
