-- MySQL database export
START TRANSACTION;

CREATE TABLE IF NOT EXISTS `provedores` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(255) NOT NULL,
    `irs` INT NOT NULL DEFAULT 0,
    `eliminado` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
);



CREATE TABLE IF NOT EXISTS `empleados` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nombres` VARCHAR(255) NOT NULL,
    `apellidos` VARCHAR(255) NOT NULL,
    `usuario` VARCHAR(20) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `eliminado` INT NOT NULL DEFAULT 0,
    `puesto` INT NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
);



CREATE TABLE IF NOT EXISTS `empresa` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(255) NOT NULL UNIQUE,
    `eliminado` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
);



CREATE TABLE IF NOT EXISTS `autobus` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `unidad` VARCHAR(255) NOT NULL UNIQUE,
    `id_empresa` INT NOT NULL,
    `eliminado` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
);



CREATE TABLE IF NOT EXISTS `factura` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_empresa` INT NOT NULL,
    `id_provedor` INT NOT NULL,
    `folio` VARCHAR(20) NOT NULL,
    `fecha` DATE NOT NULL,
    `total` DECIMAL NOT NULL,
    `estado` INT NOT NULL DEFAULT 0,
    `fecha_registro` DATE NOT NULL,
    `fecha_entregado` DATE NOT NULL,
    PRIMARY KEY (`id`)
);



CREATE TABLE IF NOT EXISTS `detalle_factura` (
    `id` INT NOT NULL,
    `concepto` VARCHAR(255) NOT NULL,
    `subtotal` DECIMAL NOT NULL,
    `id_autobus` INT,
    `id_factura` INT NOT NULL,
    PRIMARY KEY (`id`)
);


-- Foreign key constraints
ALTER TABLE `detalle_factura` ADD CONSTRAINT `fk_detalle_factura_id_autobus` FOREIGN KEY(`id_autobus`) REFERENCES `autobus`(`id`);
ALTER TABLE `factura` ADD CONSTRAINT `fk_factura_id_empresa` FOREIGN KEY(`id_empresa`) REFERENCES `empresa`(`id`);
ALTER TABLE `autobus` ADD CONSTRAINT `fk_autobus_id_empresa` FOREIGN KEY(`id_empresa`) REFERENCES `empresa`(`id`);
ALTER TABLE `detalle_factura` ADD CONSTRAINT `fk_detalle_factura_id_factura` FOREIGN KEY(`id_factura`) REFERENCES `factura`(`id`);
ALTER TABLE `factura` ADD CONSTRAINT `fk_factura_id_provedor` FOREIGN KEY(`id_provedor`) REFERENCES `provedores`(`id`);

COMMIT;
