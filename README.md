# Invest - Integrated Asset and Investment Manager

A web-based asset and investment management application built with PHP and MySQL. Easily track, manage, and report on your investments.

---
## Demo Link
https://invest-diq7.onrender.com
---
---

## Features
- User authentication (login/signup)
- Asset and investment management
- Audit and reporting tools
- Responsive web interface
- MySQL database integration

---

## Project Structure
```
├── assets.php, investments.php, audits.php, ...   # PHP backend files
├── *.js, *.css                                  # Frontend assets
├── config.php                                   # Database configuration
├── Dockerfile                                   # Docker build file
├── docker-compose.yml                           # Docker Compose setup (for local dev)
├── invest.sql                                   # Database schema
└── README.md                                    # Project documentation
```

---

## Local Development

### Prerequisites
- Docker & Docker Compose installed
- (Optional) MySQL client for database access

### Setup
1. Clone the repository:
   ```sh
   git clone https://github.com/Vishnups08/invest.git
   cd invest
   ```
2. Start the app and MySQL (for local dev):
   ```sh
   docker-compose up --build
   ```
3. Access the app at [http://localhost:8080](http://localhost:8080)

---

## Deployment on Render.com
1. Push your code to GitHub/GitLab.
2. Create a **Web Service** on Render.com:
   - Environment: Docker
   - Use the default Dockerfile
   - Add these environment variables:
     - `DB_HOST` (e.g., sql12.freesqldatabase.com)
     - `DB_USER`, `DB_PASSWORD`, `DB_NAME`

---

## CI/CD with Jenkins
- Example Jenkins pipeline:
  - Clone repo
  - Build Docker image
  - Trigger Render.com deploy via Deploy Hook

See `Jenkinsfile` for a sample pipeline.

---

## Database
- The app uses MySQL. For production, you can use [FreeSQLDatabase](https://www.freesqldatabase.com/) or any managed MySQL provider.
- Import the schema from `invest.sql`.

---

## Security Notes
- Never commit real credentials to the repository.
- Use environment variables for all secrets and database credentials.

