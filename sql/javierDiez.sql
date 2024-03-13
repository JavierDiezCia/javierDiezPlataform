DROP DATABASE IF EXISTS javierDiez;
CREATE DATABASE javierDiez;
USE javierDiez;

CREATE TABLE personas (
    cedula VARCHAR(255) PRIMARY KEY,
    per_nombres VARCHAR(255) NOT NULL,
    per_apellidos VARCHAR(255) NOT NULL,
    per_fechaNacimiento DATE NOT NULL,
    per_estado BOOLEAN NOT NULL, -- 1 TRABAJANDO  0 NO TRABAJA EN LA EMPRESA
    per_areaTrabajo VARCHAR(255) NOT NULL,
    per_correo VARCHAR(255) NULL
);
INSERT INTO personas (cedula, per_nombres, per_apellidos, per_fechaNacimiento, per_estado, per_areaTrabajo,per_correo)
VALUES ('1728563592', 'Jean', 'Cedeno', '1990-01-15', 1, 'Tics','example@example.com'),
('1750541730', 'lenin jeerson', 'puetate obando', '1998-02-17', '1', 'Diseño Grafico','puetateobando@gamil.com'),
('1721403945','RAUL ALEJANDRO','BAUTISTA GARCIA','1990-01-01',1,'Diseño Grafico', 'NULL'),
('1723351076','CESAR ADRIAN','CORDOVA ESPINOSA','1990-01-01',1,'Diseño Grafico', 'NULL'),
('1725302002','BYRON DANIEL','OÑA SANCHEZ','1990-01-01',1,'Diseño Grafico', 'NULL'),
('2100196472','ROBERT FABRICIO','ROSILLO ROSILLO','1990-01-01',1,'Diseño Grafico', 'NULL'),
('1718432352','HENRY DAVID','SALAZAR ABAD','1990-01-01',1,'Diseño Grafico', 'NULL'),
('1724990807','MILTON MARCELO','HERMOZA ENRIQUEZ','1990-01-01',1,'PRODUCCIÓN', 'NULL'),
('1715923254','ANA CRISTINA','RAMON CONFORME','1990-01-01',1,'PRODUCCIÓN', 'NULL'),
('1717766826'	,'STALIN ADOLFO','SANCHEZ SANCHEZ','1990-01-01',1,'PRODUCCIÓN', 'NULL'),
('1716812951','LUIS EDUARDO','FREIRE LIQUIN','1990-01-01',1,'TEC', 'NULL'),
('0951881408','ALEJANDRO XAVIER','JORDAN ALVAREZ','1990-01-01',1,'Diseño Grafico', 'NULL'),
('0953292034','GEOVANNY EMMANUEL','HUMANANTE ALVAREZ','1990-01-01',1,'Diseño Grafico', 'NULL'),
('1720871142','ANGEL EDUARDO','AYALA ENDARA','1990-01-01',1,'Diseño Grafico', 'NULL');

CREATE TABLE usuarios (
    id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usu_user VARCHAR(255) NOT NULL,
    usu_password VARCHAR(255) NOT NULL,
    usu_rol INT NOT NULL,
    usu_registro DATETIME NOT NULL,
    cedula VARCHAR(255) NOT NULL,
    Foreign Key (cedula) REFERENCES personas(cedula)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

INSERT INTO usuarios (cedula, usu_user, usu_password, usu_rol, usu_registro)
SELECT 
    cedula,
    cedula AS USER,
    '$2y$10$Bq5e9XjZgytTIE/FTB7c6OM/34OzJthoAF5clGdX8emFZuJh5ArHe' AS PASSWORD,
    1 AS ROL,
    CURRENT_TIMESTAMP AS registro
FROM 
    personas
WHERE 
    cedula NOT IN (
        SELECT cedula FROM usuarios
    );

CREATE TABLE orden_disenio(
    od_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    od_responsable VARCHAR(255) NOT NULL,  /*cedula*/
    od_comercial VARCHAR(255) NOT NULL,  /*cedula del vendedor o comercial*/
    od_detalle VARCHAR(255) NOT NULL,
    od_cliente VARCHAR(255) NOT NULL,
    od_fechaRegistro DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    od_estado ENUM("PROPUESTA", "DESAPROBADA", "MATERIALIDAD", "OP" , "OP CREADA") NOT NULL, 
    Foreign Key (od_responsable) REFERENCES personas(cedula)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);
CREATE TABLE od_actividades ( 
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    od_id INT UNSIGNED NOT NULL,
    odAct_detalle VARCHAR(255) NOT NULL,
    odAct_estado BOOLEAN DEFAULT 0,
    odAct_fechaEntrega DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    Foreign Key (od_id) REFERENCES orden_disenio(od_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

CREATE TABLE registros_disenio(
    rd_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    od_id INT UNSIGNED NOT NULL,
    rd_diseniador VARCHAR(255) NOT NULL, /*cedula*/
    rd_detalle VARCHAR(255) NOT NULL,
    rd_hora_ini DATETIME NOT NULL,
    rd_hora_fin DATETIME NULL,
    rd_observaciones VARCHAR(255) NULL,
    Foreign Key (od_id) REFERENCES orden_disenio(od_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

CREATE TABLE ciudad_produccion (
    lu_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    lu_ciudad VARCHAR(255) NOT NULL
);
INSERT INTO `ciudad_produccion`(`lu_ciudad`) VALUES('QUITO'),('GUAYAQUIL');

CREATE TABLE op (
    op_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    od_id INT UNSIGNED NOT NULL,
    lu_id INT UNSIGNED NOT NULL,
    op_ciudad VARCHAR(255) NOT NULL,
    op_registro DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    op_direccionLocal VARCHAR(255) NOT NULL,
    op_personaContacto VARCHAR(255) NOT NULL,
    op_telefono VARCHAR(255) NOT NULL,
    op_estado ENUM("OP CREADA", "EN PRODUCCION", "OP PAUSADA", "OP FINALIZADA", "OP ANULADA", "EN COBRANZA", "PRODUCCION GUAYAQUIL") NOT NULL,
    op_reproceso BOOLEAN NOT NULL, /* 0 nada 1 reproceso */
    op_porcentaje DECIMAL(5, 2) UNSIGNED NULL,
    op_notiProFecha TIMESTAMP NULL,
    op_fechaFinalizacion DATETIME NULL,
    Foreign Key (od_id) REFERENCES orden_disenio(od_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    Foreign Key (lu_id) REFERENCES ciudad_produccion(lu_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    CHECK (op_porcentaje <= 100)
);/*AUTO_INCREMENT = 11353   antes del ;*/

    /*op_observaciones*/
CREATE TABLE op_observaciones (
    op_id INT UNSIGNED NOT NULL,
    opOb_estado VARCHAR(255) NOT NULL,
    opOb_obsevacion VARCHAR(255) NOT NULL,
    opOb_fecha DATETIME NOT NULL,
    Foreign Key (op_id) REFERENCES op(op_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

CREATE TABLE planos (
    pla_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    op_id INT UNSIGNED NOT NULL,
    pla_numero INT UNSIGNED NOT NULL,
    pla_estado ENUM("ACTIVO", "PAUSADO", "ANULADO", "CONCLUIDO") NOT NULL,
    pla_reproceso BOOLEAN NOT NULL, /* 0 no es reproceso, 1 si es reproceso */
    pla_porcentaje DECIMAL(5, 2) UNSIGNED NOT NULL,
    Foreign Key (op_id) REFERENCES op(op_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    CHECK (pla_porcentaje <= 100)
);

    /* observaciones */
CREATE TABLE pla_observaciones (
    pla_id INT UNSIGNED NOT NULL,
    plaOb_estado VARCHAR(255) NOT NULL,
    plaOb_obsevacion VARCHAR(255) NOT NULL,
    plaOb_fecha DATETIME NOT NULL,
    Foreign Key (pla_id) REFERENCES planos(pla_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

CREATE TABLE produccion (
    pro_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    pla_id INT UNSIGNED NOT NULL,
    pro_fecha DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    Foreign Key (pla_id) REFERENCES planos(pla_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

    /* areaaaaaaaas */
CREATE TABLE pro_areas (
    pro_id INT UNSIGNED NOT NULL,
    proAre_detalle VARCHAR(255) NOT NULL,
    proAre_fechaIni VARCHAR(255) NOT NULL,
    proAre_fechaFin DATETIME NOT NULL,
    proAre_porcentaje DECIMAL(5, 2) UNSIGNED NOT NULL,
    Foreign Key (pro_id) REFERENCES produccion(pro_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    CHECK (proAre_porcentaje <= 100)
);

CREATE TABLE registro (
    reg_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    pro_id INT UNSIGNED NOT NULL,
    reg_fecha DATETIME NOT NULL,
    reg_cedula VARCHAR(255) NOT NULL,
    reg_observacion VARCHAR(255) NULL,
    op_id INT UNSIGNED NOT NULL,
    pla_id INT UNSIGNED NOT NULL,
    Foreign Key (pro_id) REFERENCES produccion(pro_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

/* sub tablas de registro */
CREATE TABLE registro_produccion (
    reg_id INT UNSIGNED NOT NULL,
    reg_porcentaje DECIMAL(5, 2) UNSIGNED NOT NULL,
    proAre_detalle VARCHAR(255) NOT NULL,
    Foreign Key (reg_id) REFERENCES registro(reg_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    CHECK (reg_porcentaje <= 100)
);
CREATE TABLE registro_reproceso (
    reg_id INT UNSIGNED NOT NULL,
    reg_reproceso BOOLEAN NOT NULL,
    Foreign Key (reg_id) REFERENCES registro(reg_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);
CREATE TABLE registro_empleado (
    reg_id INT UNSIGNED NOT NULL,
    reg_fechaFin DATETIME NULL,
    reg_logistica BOOLEAN NOT NULL,
    reg_areaTrabajo VARCHAR(255) NOT NULL,
    Foreign Key (reg_id) REFERENCES registro(reg_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);
/* atributo compuesto de registro_empleado */
CREATE TABLE registro_empleado_actividades (
    reg_id INT UNSIGNED NOT NULL,
    reg_detalle VARCHAR(255) NOT NULL,
    Foreign Key (reg_id) REFERENCES registro(reg_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

CREATE TABLE notificaciones (
    noti_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    noti_cedula VARCHAR(255) NOT NULL, /* cedula */
    noti_fecha DATETIME NOT NULL,
    noti_detalle VARCHAR(255) NOT NULL,
    noti_destinatario INT NOT NULL, /* usando los roles como destinatarios */
    Foreign Key (noti_cedula) REFERENCES personas(cedula)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);
CREATE TABLE notificaciones_accionales (
    noti_id INT UNSIGNED NOT NULL,
    notiAc_estado BOOLEAN NOT NULL,  /* 0 sin notificacion, 1 con notificacion */
    notiAc_referencia VARCHAR(255) NOT NULL,
    Foreign Key (noti_id) REFERENCES notificaciones(noti_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

/*sub tabla notificaciones accionales*/

CREATE TABLE kardex (
    kar_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    kar_cedula VARCHAR(255) NOT NULL,
    kar_accion VARCHAR(255) NOT NULL,
    kar_tabla VARCHAR(255) NOT NULL,
    kar_idRow VARCHAR(255) NOT NULL,
    kar_fecha DATETIME NOT NULL,
    Foreign Key (kar_cedula) REFERENCES personas(cedula)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);
