/*==============================================================*/
/* DBMS name:      Sybase SQL Anywhere 12                       */
/* Created on:     23/01/2024 14:02:25                          */
/*==============================================================*/

DROP DATABASE IF EXISTS example;

CREATE DATABASE example;

USE example;

/*==============================================================*/
/* Table: PERSONAS                                              */
/*==============================================================*/
create table PERSONAS 
(
   CEDULA               char(10)                       not null,
   PERNOMBRES           varchar(100)                   not null,
   PERAPELLIDOS         varchar(100)                   not null,
   PERFECHANACIMIENTO   date                           not null,
   PERESTADO            smallint                       not null,
   PERAREATRABAJO       char(25)                       not null,
   constraint PK_PERSONAS primary key (CEDULA)
);

INSERT INTO PERSONAS (CEDULA, PERNOMBRES, PERAPELLIDOS, PERFECHANACIMIENTO, PERESTADO, PERAREATRABAJO)
VALUES ('1728563592', 'Jean', 'Cedeno', '1990-01-15', 1, 'Tics');


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
   ID_USER              integer            AUTO_INCREMENT            not null,
   CEDULA               char(10)                       null,
   USER               char(10)                       not null,
   PASSWORD             varchar(255)                   not null,
   ROL                  integer                        not null,
   REGISTRO             DATETIME                      not null,
   constraint PK_USUARIOS primary key (ID_USER)
);

INSERT INTO USUARIOS (ID_USER, CEDULA, USER, PASSWORD, ROL, REGISTRO)
VALUES (1, '1728563592', 'jeanC', '$2y$10$jeTbyOelKGtqXlEktSx7cei0UvlLj9uvjOQzJA3DV66AeOdfKLkxS', 1, CURRENT_TIMESTAMP);


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
   IDACTIVIDAD          integer             AUTO_INCREMENT           not null,
   IDREGISTRO           integer                        null,
   ACTDETALLE           varchar(255)                   not null,
   USER_ID INT NOT NULL,
   Foreign Key (USER_ID) REFERENCES USUARIOS(ID_USER),
   constraint PK_ACTIVIDADES primary key (IDACTIVIDAD)
);

/*==============================================================*/
/* Index: ACTIVIDADES_PK                                        */
/*==============================================================*/
create unique index ACTIVIDADES_PK on ACTIVIDADES (
IDACTIVIDAD ASC
);

/*==============================================================*/
/* Index: RELATIONSHIP_6_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_6_FK on ACTIVIDADES (
IDREGISTRO ASC
);

/*==============================================================*/
/* Table: KARDEX                                                */
/*==============================================================*/
create table KARDEX 
(
   IDKARDEX             integer           AUTO_INCREMENT             not null,
   ID_USERKARDEX        integer                        not null,
   KARACCION            integer                        not null,
   KARTABLA             char(10)                       not null,
   KARIDROW             integer                        not null,
   constraint PK_KARDEX primary key (IDKARDEX)
);

/*==============================================================*/
/* Index: KARDEX_PK                                             */
/*==============================================================*/
create unique index KARDEX_PK on KARDEX (
IDKARDEX ASC
);

/*==============================================================*/
/* Table: LUGARPRODUCCION                                       */
/*==============================================================*/

create table LUGARPRODUCCION 
(
   IDLUGAR              integer         AUTO_INCREMENT               not null,
   CIUDAD               char(16)                       not null,
   USER_ID INT NOT NULL,
   Foreign Key (USER_ID) REFERENCES USUARIOS(ID_USER),
   constraint PK_LUGARPRODUCCION primary key (IDLUGAR)
);

/*==============================================================*/
/* Index: LUGARPRODUCCION_PK                                    */
/*==============================================================*/
create unique index LUGARPRODUCCION_PK on LUGARPRODUCCION (
IDLUGAR ASC
);

/*==============================================================*/
/* Table: OP                                                    */
/*==============================================================*/
create table OP 
(
   IDOP                 integer                        not null,
   CEDULA               char(10)                       null,
   IDLUGAR              integer                        null,
   OPCLIENTE            char(50)                       not null,
   OPCIUDAD             varchar(255)                   not null,
   OPDETALLE            varchar(255)                   not null,
   OPREGISTRO           DATETIME                      not null,
   OPNOTIFICACIONCORREO DATETIME                      not null,
   OPVENDEDOR           char(10)                       not null,
   OPDISEADOR           char(10)                       not null,
   OPDIRECCIONLOCAL     varchar(255)                   not null,
   OPPERSONACONTACTO    varchar(100)                   null,
   OPTELEFONO           char(15)                       null,
   OPOBSERVACIONES      varchar(255)                   null,
   OPESTADO             char(25)                       null,
   USER_ID INT NOT NULL,
   Foreign Key (USER_ID) REFERENCES USUARIOS(ID_USER),
   constraint PK_OP primary key (IDOP)
);

/*==============================================================*/
/* Index: OP_PK                                                 */
/*==============================================================*/
create unique index OP_PK on OP (
IDOP ASC
);

/*==============================================================*/
/* Index: RELATIONSHIP_2_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_2_FK on OP (
CEDULA ASC
);

/*==============================================================*/
/* Index: RELATIONSHIP_7_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_7_FK on OP (
IDLUGAR ASC
);



/*==============================================================*/
/* Table: PLANOS                                                */
/*==============================================================*/
create table PLANOS 
(
   IDPLANO              integer            AUTO_INCREMENT            not null,
   IDOP                 integer                        null,
   PLANUMERO            integer                        not null,
   USER_ID INT NOT NULL,
   Foreign Key (USER_ID) REFERENCES USUARIOS(ID_USER),
   constraint PK_PLANOS primary key (IDPLANO)
);

/*==============================================================*/
/* Index: PLANOS_PK                                             */
/*==============================================================*/
create unique index PLANOS_PK on PLANOS (
IDPLANO ASC
);

/*==============================================================*/
/* Index: RELATIONSHIP_3_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_3_FK on PLANOS (
IDOP ASC
);

/*==============================================================*/
/* Table: PRODUCCION                                            */
/*==============================================================*/
create table PRODUCCION 
(
   IDPRODUCCION         integer             AUTO_INCREMENT           not null,
   IDPLANO              integer                        null,
   PROOBSERVACIONES     varchar(255)                   not null,
   ATTRIBUTE_32         integer                        not null,
   PROPORCENTAJE        integer                        not null,
   USER_ID INT NOT NULL,
   Foreign Key (USER_ID) REFERENCES USUARIOS(ID_USER),
   constraint PK_PRODUCCION primary key (IDPRODUCCION)
);

/*==============================================================*/
/* Index: PRODUCCION_PK                                         */
/*==============================================================*/
create unique index PRODUCCION_PK on PRODUCCION (
IDPRODUCCION ASC
);

/*==============================================================*/
/* Index: RELATIONSHIP_4_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_4_FK on PRODUCCION (
IDPLANO ASC
);

/*==============================================================*/
/* Table: REGISTROS                                             */
/*==============================================================*/
create table REGISTROS 
(
   IDREGISTRO           integer             AUTO_INCREMENT           not null,
   IDPRODUCCION         integer                        null,
   REGHORAINICIA        DATETIME                      not null,
   REGHORAFINAL         DATETIME                      null,
   REGAVANCE            integer                        null,
   REGOBSERVACION       varchar(255)                   null,
   USER_ID INT NOT NULL,
   Foreign Key (USER_ID) REFERENCES USUARIOS(ID_USER),
   constraint PK_REGISTROS primary key (IDREGISTRO)
);

/*==============================================================*/
/* Index: REGISTROS_PK                                          */
/*==============================================================*/
create unique index REGISTROS_PK on REGISTROS (
IDREGISTRO ASC
);

/*==============================================================*/
/* Index: RELATIONSHIP_5_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_5_FK on REGISTROS (
IDPRODUCCION ASC
);



alter table ACTIVIDADES
   add constraint FK_ACTIVIDA_RELATIONS_REGISTRO foreign key (IDREGISTRO)
      references REGISTROS (IDREGISTRO)
      on update restrict
      on delete restrict;

alter table OP
   add constraint FK_OP_RELATIONS_PERSONAS foreign key (CEDULA)
      references PERSONAS (CEDULA)
      on update restrict
      on delete restrict;

alter table OP
   add constraint FK_OP_RELATIONS_LUGARPRO foreign key (IDLUGAR)
      references LUGARPRODUCCION (IDLUGAR)
      on update restrict
      on delete restrict;

alter table PLANOS
   add constraint FK_PLANOS_RELATIONS_OP foreign key (IDOP)
      references OP (IDOP)
      on update restrict
      on delete restrict;

alter table PRODUCCION
   add constraint FK_PRODUCCI_RELATIONS_PLANOS foreign key (IDPLANO)
      references PLANOS (IDPLANO)
      on update restrict
      on delete restrict;

alter table REGISTROS
   add constraint FK_REGISTRO_RELATIONS_PRODUCCI foreign key (IDPRODUCCION)
      references PRODUCCION (IDPRODUCCION)
      on update restrict
      on delete restrict;

alter table USUARIOS
   add constraint FK_USUARIOS_RELATIONS_PERSONAS foreign key (CEDULA)
      references PERSONAS (CEDULA)
      on update restrict
      on delete restrict;

