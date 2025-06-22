# Todo List API (Symfony)

A comprehensive API for managing tasks and subtasks, built with Symfony and containerized with Docker.

## Tech Stack

- **Backend**: PHP 8.2, Symfony
- **Database**: MySQL 8.0
- **Web Server**: Nginx
- **Containerization**: Docker, Docker Compose

## Setup and Running

### Prerequisites

- Docker
- Docker Compose

### Installation Steps

1. **Build and Run the Project**

   This command builds and starts all Docker containers and installs the required PHP dependencies:

   ```bash
   make up
   ```

2. **Generate JWT Keys**

   This step is required for the initial authentication setup:

   ```bash
   make jwt-keys
   ```

   You will be prompted to enter and confirm a passphrase. Update the `JWT_PASSPHRASE` variable in your `symfony/.env.local` file with the same passphrase.


3. **Create Database Tables**

   Apply all existing migrations to create the database schema:

   ```bash
   make migrate
   ```

### You're All Set!

The application is now available at: [http://localhost:8080](http://localhost:8080)

The API documentation is available at: [http://localhost:8080/api/doc](http://localhost:8080/api/doc)

### Using the API Documentation (Swagger UI)
To test protected endpoints (like creating tasks) in the documentation, you need to authorize your session.

**Navigate to the documentation**: Open http://localhost:8080/api/doc.

**Register a User**: Use the POST `/api/register` endpoint to create a new user.

**Get JWT Token**: Use the POST `/api/login_check` endpoint with the credentials you just registered. Copy the token value from the response body.

### Authorize:

Click the green "Authorize" button at the top right of the page.

In the popup window, paste your token into the "Value" field. Important: You must prefix the token with Bearer  (e.g., Bearer eyJ0eXAiOi...).

Click "Authorize" in the popup, then "Close".

Now you can use all the locked endpoints in the documentation.
### (Optional) Seed the Database

To populate the database with test data (users and tasks), run the following command. **Warning**: This will completely purge all tables before adding new data.

```bash
make seed
```

## Useful Makefile Commands

- `make up`: Starts all services and installs dependencies.
- `make down`: Stops and removes all services.
- `make ssh`: Connects to the PHP container's command line.
- `make jwt-keys`: Generates new JWT keys (a one-time setup step).
- `make migrate`: Applies database migrations.
- `make seed`: Populates the database with test data.