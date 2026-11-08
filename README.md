# Óptica Vision

Plataforma web para la gestión de una óptica, desarrollada como Proyecto Final de Ciclo (DAW) por Raúl Almarza Robles.

## Tecnologías
- PHP (PDO)
- MySQL / MariaDB
- HTML, CSS y JavaScript

## Funcionalidades
- **Administrador:** gestión de pacientes, revisiones y citas, panel y calendario.
- **Paciente:** consulta de sus propias revisiones y citas.

## Instalación local (XAMPP)
1. Copia el proyecto en `xampp/htdocs/Optica-web`.
2. Inicia Apache y MySQL desde XAMPP.
3. Importa `sql/optica.sql` en phpMyAdmin (crea la base de datos `optica_db`).
4. Revisa la conexión en `config/database.php`.
5. Abre http://localhost/Optica-web/login.php

## Usuarios de prueba
- Admin: `admin@optica.local` / `password`
- Paciente: `maria.garcia@mail.com` / `password`

## Documentación
La propuesta, las entregas parciales, la memoria final y la presentación están en los PDF de la raíz del repositorio.
