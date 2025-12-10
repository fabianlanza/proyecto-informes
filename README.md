# Sistema de Gestión de Informes de Práctica

Este sistema es una aplicación web desarrollada para optimizar el proceso de seguimiento, revisión y aprobación de informes de práctica profesional. Facilita la interacción entre **Alumnos**, **Docentes** y **Administradores**, permitiendo una gestión digital y centralizada de la documentación académica.

## 🚀 Características Principales

El sistema gestiona tres roles principales con funcionalidades específicas:

### 🎓 Alumno
- **Subida de Informes**: Carga de informes de práctica en formato PDF.
- **Seguimiento de Estado**: Visualización del estado actual de la revisión (En revisión, Aprobado, Observado).
- **Feedback**: Recepción de observaciones y correcciones por parte de los docentes.
- **Información de Terna**: Consulta de los docentes asignados a su comité de evaluación (Terna).

### 👨‍🏫 Docente
- **Revisión de Alumnos**: Listado de alumnos asignados para tutoría o revisión.
- **Visor de Documentos**: Visualización de los informes PDF directamente en el navegador.
- **Evaluación**: Herramientas para realizar observaciones y calificar el desempeño.
- **Historial**: Registro de revisiones realizadas.

### 🛡️ Administrador
- **Gestión de Usuarios**: Creación y administración de cuentas de Alumnos, Docentes y Administrativos.
- **Asignación de Ternas**: Configuración de los comités de evaluación para cada alumno.
- **Gestión Académica**: Administración de Facultades y Campus.
- **Supervisión Global**: Acceso a todos los informes y estados del sistema.

## 🛠️ Tecnologías Utilizadas

Este proyecto está construido con un stack tecnológico moderno y robusto:

- **Backend**: [Laravel 12](https://laravel.com) - Framework de PHP para aplicaciones web seguras y escalables.
- **Frontend**: [Blade](https://laravel.com/docs/blade) (Motor de plantillas) + [Tailwind CSS](https://tailwindcss.com) (Estilos).
- **Base de Datos**: MySQL / MariaDB.
- **Autenticación**: Laravel Breeze.
- **Testing**: [Pest PHP](https://pestphp.com).
- **Manejo de Archivos**: Almacenamiento seguro de PDFs.

## 💻 Instalación y Configuración

Sigue estos pasos para desplegar el proyecto en tu entorno local:

1.  **Clonar el Repositorio**
    ```bash
    git clone https://github.com/tu-usuario/proyecto-informes.git
    cd proyecto-informes
    ```

2.  **Instalar Dependencias de PHP**
    ```bash
    composer install
    ```

3.  **Instalar Dependencias de Frontend**
    ```bash
    npm install
    npm run build
    ```

4.  **Configuración del Entorno**
    Copia el archivo de ejemplo y genera la clave de la aplicación:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Asegúrate de configurar tus credenciales de base de datos (DB_DATABASE, DB_USERNAME, etc.) en el archivo `.env`.*

5.  **Base de Datos**
    Ejecuta las migraciones y los seeders (datos de prueba):
    ```bash
    php artisan migrate --seed
    ```

6.  **Ejecutar el Servidor**
    ```bash
    php artisan serve
    ```
    El sistema estará disponible en `http://localhost:8000`.

## 🔒 Seguridad y Privacidad

Este sistema fue desarrollado con fines académicos y profesionales.
- **Nota sobre Seguridad**: Si planeas desplegar este proyecto en un entorno de producción, asegúrate de realizar una auditoría de seguridad y cambiar todas las credenciales predeterminadas.
- **Datos Sensibles**: El código fuente no contiene datos reales de estudiantes ni contraseñas.
