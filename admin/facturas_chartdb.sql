Table "provedores" {
  "id" int [pk, not null, increment]
  "nombre" varchar(255) [not null]
  "irs" int [not null, default: 0]
  "eliminado" int [not null, default: 0]
}

Table "empleados" {
  "id" int [pk, not null, increment]
  "nombres" varchar(255) [not null]
  "apellidos" varchar(255) [not null]
  "usuario" varchar(20) [unique, not null]
  "password" varchar(255) [not null]
  "eliminado" int [not null, default: 0]
  "puesto" int [not null, default: 1]
}

Table "empresa" {
  "id" int [pk, not null, increment]
  "nombre" varchar(255) [unique, not null]
  "eliminado" int [not null, default: 0]
}

Table "autobus" {
  "id" int [pk, not null, increment]
  "unidad" varchar(500) [unique, not null]
  "id_empresa" int [not null, ref: - "empresa"."id"]
  "eliminado" int [not null, default: 0]
}

Table "factura" {
  "id" int [pk, not null, increment]
  "id_empresa" int [not null, ref: - "empresa"."id"]
  "id_provedor" int [not null, ref: - "provedores"."id"]
  "folio" varchar(20) [not null]
  "fecha" date [not null]
  "total" decimal [not null]
  "estado" int [not null, default: 0]
  "fecha_registro" date [not null]
  "fecha_entregado" date [not null]
}

Table "detalle_factura" {
  "id" int [pk, not null]
  "concepto" varchar(255) [not null]
  "subtotal" decimal [not null]
  "id_autobus" int [ref: - "autobus"."id"]
  "id_factura" int [not null, ref: - "factura"."id"]
}
