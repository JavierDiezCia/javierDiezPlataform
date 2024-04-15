/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     31/01/2024 9:39:31                           */
/*==============================================================*/
DROP DATABASE IF EXISTS example;

CREATE DATABASE example;

USE example;
/*==============================================================*/
/* Table: PERSONAS                                              */
/*==============================================================*/
create table PERSONAS
(
   CEDULA               char(10) not null,
   PERNOMBRES           varchar(100) not null,
   PERAPELLIDOS         varchar(100) not null,
   PERFECHANACIMIENTO    date not null,
   PERESTADO            bool not null,
   PERAREATRABAJO       char(25) not null,
   PERCORREO            varchar(150) not null,
   PERFECHAREGISTRO     DATETIME DEFAULT CURRENT_TIMESTAMP,
   constraint PK_PERSONAS primary key (CEDULA)
);
INSERT INTO PERSONAS (CEDULA, PERNOMBRES, PERAPELLIDOS, PERFECHANACIMIENTO, PERESTADO, PERAREATRABAJO,PERCORREO)
VALUES ('1728563592', 'Jean', 'Cedeno', '1990-01-15', 1, 'Tics','example@example.com'),
('1750541730', 'lenin jeerson', 'puetate obando', '1998-02-17', '1', 'Diseño Grafico','puetateobando@gamil.com'),
('1721403945','RAUL ALEJANDRO','BAUTISTA GARCIA','1990-01-01',1,'Diseño Grafico',''),
('1723351076','CESAR ADRIAN','CORDOVA ESPINOSA','1990-01-01',1,'Diseño Grafico',''),
('1725302002','BYRON DANIEL','OÑA SANCHEZ','1990-01-01',1,'Diseño Grafico',''),
('2100196472','ROBERT FABRICIO','ROSILLO ROSILLO','1990-01-01',1,'Diseño Grafico',''),
('1718432352','HENRY DAVID','SALAZAR ABAD','1990-01-01',1,'Diseño Grafico',''),
('1724990807','MILTON MARCELO','HERMOZA ENRIQUEZ','1990-01-01',1,'PRODUCCIÓN',''),
('1715923254','ANA CRISTINA','RAMON CONFORME','1990-01-01',1,'PRODUCCIÓN',''),
('1717766826'	,'STALIN ADOLFO','SANCHEZ SANCHEZ','1990-01-01',1,'PRODUCCIÓN',''),
('1716812951','LUIS EDUARDO','FREIRE LIQUIN','1990-01-01',1,'TEC',''),
('0951881408','ALEJANDRO XAVIER','JORDAN ALVAREZ','1990-01-01',1,'Diseño Grafico',''),
('0953292034','GEOVANNY EMMANUEL','HUMANANTE ALVAREZ','1990-01-01',1,'Diseño Grafico',''),
('1720871142','ANGEL EDUARDO','AYALA ENDARA','1990-01-01',1,'Diseño Grafico','');

/*==============================================================*/
/* Index: PERSONAS_PK                                           */
/*==============================================================*/
create unique index PERSONAS_PK on PERSONAS (
CEDULA ASC
);
/*==============================================================*/
/* Table: USUARIOS                                              */
/*==============================================================*/
create table USUARIOS
(
   ID_USER              int AUTO_INCREMENT  not null,
   CEDULA               char(10),
   USER                 char(10) not null,
   PASSWORD             varchar(255) not null,
   ROL                  int not null,
   REGISTRO             datetime not null,
   constraint PK_USUARIOS primary key (ID_USER)
);

INSERT INTO USUARIOS (CEDULA, USER, PASSWORD, ROL, REGISTRO)
SELECT 
    CEDULA,
    CEDULA AS USER,
    '$2y$10$Bq5e9XjZgytTIE/FTB7c6OM/34OzJthoAF5clGdX8emFZuJh5ArHe' AS PASSWORD,
    1 AS ROL,
    CURRENT_TIMESTAMP AS REGISTRO
FROM 
    PERSONAS
WHERE 
    CEDULA NOT IN (
        SELECT CEDULA FROM USUARIOS
    );

/*==============================================================*/
/* Index: USUARIOS_PK                                           */
/*==============================================================*/
create unique index USUARIOS_PK on USUARIOS (
ID_USER ASC
);
/*==============================================================*/
/* Index: RELATIONSHIP_1_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_1_FK on USUARIOS (
CEDULA ASC
);

/*==============================================================*/
/* Table: ACTIVIDADES                                           */
/*==============================================================*/
create table ACTIVIDADES
(
   IDACTIVIDADES        int AUTO_INCREMENT not null,
   IDREGISTRO           int,
   ACTDETALLE           varchar(255) not null,
   primary key (IDACTIVIDADES)
);



/*==============================================================*/
/* Table: KARDEX                                                */
/*==============================================================*/
create table KARDEX
(
   IDKARDEX             int AUTO_INCREMENT not null,
   ID_USERKARDEX        int not null,
   KARUSER              VARCHAR(50),
   KARACCION            VARCHAR(20) not null,  -- 1 = CREO ; 2 = EDITO ; 3 ELIMINO ; 4 = RESTAURO
   KARTABLA             VARCHAR(50) not null,
   KARROW               VARCHAR(255) not null,
   KARFECHA DATETIME DEFAULT CURRENT_TIMESTAMP,
   primary key (IDKARDEX)
);

/*==============================================================*/
/* Table: LUGARPRODUCCION                                       */
/*==============================================================*/
create table LUGARPRODUCCION
(
   IDLUGAR              int AUTO_INCREMENT not null,
   CIUDAD               char(16) not null,
   primary key (IDLUGAR)
);
INSERT INTO lugarproduccion (`IDLUGAR`,`CIUDAD`)VALUES (NULL,"QUITO"),
(NULL, "GUAYAQUIL");
/*==============================================================*/
/* Table: OP                                                    */
/*==============================================================*/
create table OP
(
   IDOP                 int AUTO_INCREMENT not null,
   CEDULA               char(10),
   IDLUGAR              int,
   OPCLIENTE            char(50) not null,
   OPCIUDAD             varchar(255) not null,
   OPDETALLE            varchar(255) not null,
   OPREGISTRO           DATETIME DEFAULT CURRENT_TIMESTAMP,
   OPNOTIFICACIONCORREO datetime,
   OPVENDEDOR           char(20) not null,
   OPDIRECCIONLOCAL     varchar(255) not null,
   OPPERESONACONTACTO   varchar(100),
   TELEFONO             char(10),
   OPOBSERAVACIONES     varchar(255),
    OPESTADO             int, -- 1= OP CREADA  2= OP EN PRODUCCION  3= OP PAUSADA  4= OP ANULADA  5= OP FINALIZADA
   OPREPROSESO          bool,
   OPFECHAFINAL         datetime,
   primary key (IDOP)
)AUTO_INCREMENT = 11353;

INSERT INTO OP (`OPCLIENTE`,`OPCIUDAD`,`OPDETALLE`,`OPREGISTRO`,`OPNOTIFICACIONCORREO`,`OPVENDEDOR`,`CEDULA`,`OPDIRECCIONLOCAL`,`OPPERESONACONTACTO`,`TELEFONO`,`OPOBSERAVACIONES`,`IDLUGAR`,`OPESTADO`,`OPREPROSESO`)
VALUES
('PRODUBANCO', 'GYE', 'PUNTO i ROTULO', '2024-01-03 00:00:00', '2024-01-03 08:57:00', '1750541730', '0953292034', 'AGENCIA CENTRO', 'BELEN CORAL', '0984520220', '', 1, 'EN PRODUCCION',0),
('PRODUBANCO', 'QUITO', 'SEÑALETICA ASCENSOR', '2024-01-03 00:00:00', '2024-01-03 08:57:00', '1750541730', '0953292034', 'AGENCIA QUICENTRO NORTE', 'BELEN CORAL', '0984520220', '', 1, 'EN PRODUCCION',0),
('PRODUBANCO', 'QUITO', 'SEÑALETICA ASCENSOR', '2024-01-03 00:00:00', '2024-01-03 08:57:00', '1750541730', '0953292034', 'AGENCIA NORTE', 'BELEN CORAL', '0984520220', '', 1, 'EN PRODUCCION',0),
('PRODUBANCO', 'OTAVALO', 'BOTONES DECORATIVOS', '2024-01-03 00:00:00', '2024-01-03 10:14:00', '1750541730', '0953292034', 'AGENCIA PRINCIPAL OTAVALO', 'BELEN CORAL', '0984520220', '', 1, 'EN PRODUCCION',0),
('JCDECAUX', 'GYE', 'PE-PARADERO KFC', '2024-01-03 00:00:00', '2024-01-03 10:26:00', '1750541730', '0951881408', 'JC-GYE', 'ALISON TORRES', '0987254764', '', 1, 'EN PRODUCCION',0),
('PRODUBANCO', 'MILAGRO', 'LONA TRANSLUCIDA CAJA DE LUZ', '2024-01-03 00:00:00', '2024-01-03 10:51:00', '1750541730', '0953292034', 'AGENCIA MILAGRO', 'BELEN CORAL', '0984520220', '', 1, 'EN PRODUCCION',0),
('PRODUBANCO', 'BAÑOS', 'ORIGAMI ACRILICO', '2024-01-03 00:00:00', '2024-01-03 10:56:00', '1750541730', '0951881408', 'TOMAS HALFLANTS N6-40 ENTRE AMBATO Y ORIENTE', 'BELEN CORAL', '0984520220', '', 1, 'EN PRODUCCION',0);



/*==============================================================*/
/* Table: PLANOS                                                */
/*==============================================================*/
create table PLANOS
(
   IDPLANO              int AUTO_INCREMENT not null,
   IDOP                 int,
   PLANNUMERO           int not null,
   PLAESTADO            int not null,  /*1 = activo  2 = pausado 3 = anulado 4 = concluido*/
   PLANOTIFICACION      bool not null, /* 0 no hay notificacion, 1 si hay notificacion */
   PLAFECHANOTI            datetime,
   PLAREPROSESO         bool,
   primary key (IDPLANO)
);

/*==============================================================*/
/* Table: PRODUCCION                                            */
/*==============================================================*/
create table PRODUCCION
(
  IDPRODUCION           int  AUTO_INCREMENT not null,
   IDPLANO              int,
   PROOBSERVACIONES     varchar(255) not null,
   PROFECHA             datetime not null,
   PROPORCENTAJE        int,
   primary key (IDPRODUCION)
);
/*==============================================================*/
/* Table: AREAS                                                 */
/*==============================================================*/
create table AREAS
(
   IDAREA               int AUTO_INCREMENT not null,
   IDPRODUCION          int,
   AREDETALLE           int not null,
   AREAFECHAINICIO      DATETIME DEFAULT CURRENT_TIMESTAMP,
   AREFECHAFINAL        datetime,
   primary key (IDAREA)
);

/*==============================================================*/
/* Table: REGISTRO                                              */
/*==============================================================*/
create table REGISTRO
(
   IDREGISTRO           int AUTO_INCREMENT not null,
   IDAREA               int,
   REGHORAINICIO        datetime not null,
   REGHORAFINAL         datetime,
   REGAVANCE            int,
   REGOBSERVACION       varchar(255),
   REGAYUDA             bool not null,
   REGCEDULA            char(10) not null,
   primary key (IDREGISTRO)
);

/*==============================================================*/
/* Table: REGISTROPRODUCCION                                    */
/*==============================================================*/
create table REGISTROPRODUCCION
(
   IDREPR               int AUTO_INCREMENT not null,
   IDAREA               int,
   REPRFECHAHORA        datetime not null,
   REPRPORCENTAJE       int not null,
   REPROBSERVACIONES    varchar(255),
   primary key (IDREPR)
);

/*==============================================================*/
/* Table: LOGISTICA                                             */
/*==============================================================*/
create table LOGISTICA
(
   IDLOGISTICA          int AUTO_INCREMENT not null,
   IDPLANO              int,
   LOGAREATRABAJO       int not null,
   LOGHORAINCIO         DATETIME DEFAULT CURRENT_TIMESTAMP,
   LOGHORAFINAL         datetime,
   LOGOBSERVACIONES     varchar(255),
   LOGCEDULA            char(10) not null,
    LOGESTADO            int,
   primary key (IDLOGISTICA)
);

alter table ACTIVIDADES add constraint FK_RELATIONSHIP_7 foreign key (IDREGISTRO)
      references REGISTRO (IDREGISTRO) on delete restrict on update restrict;

alter table AREAS add constraint FK_RELATIONSHIP_8 foreign key (IDPRODUCION)
      references PRODUCCION (IDPRODUCION) on delete restrict on update restrict;

alter table LOGISTICA add constraint FK_RELATIONSHIP_11 foreign key (IDPLANO)
      references PLANOS (IDPLANO) on delete restrict on update restrict;

alter table OP add constraint FK_RELATIONSHIP_2 foreign key (CEDULA)
      references PERSONAS (CEDULA) on delete restrict on update restrict;

alter table OP add constraint FK_RELATIONSHIP_3 foreign key (IDLUGAR)
      references LUGARPRODUCCION (IDLUGAR) on delete restrict on update restrict;

alter table PLANOS add constraint FK_RELATIONSHIP_4 foreign key (IDOP)
      references OP (IDOP) on delete restrict on update restrict;

alter table PRODUCCION add constraint FK_RELATIONSHIP_5 foreign key (IDPLANO)
      references PLANOS (IDPLANO) on delete restrict on update restrict;

alter table REGISTRO add constraint FK_RELATIONSHIP_10 foreign key (IDAREA)
      references AREAS (IDAREA) on delete restrict on update restrict;

alter table REGISTROPRODUCCION add constraint FK_RELATIONSHIP_9 foreign key (IDAREA)
      references AREAS (IDAREA) on delete restrict on update restrict;

alter table USUARIOS add constraint FK_RELATIONSHIP_1 foreign key (CEDULA)
      references PERSONAS (CEDULA) on delete restrict on update restrict;