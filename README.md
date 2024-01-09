# sporto-registras-public

[![OpenSSF Scorecard](https://api.securityscorecards.dev/projects/github.com/Nacionaline-sporto-agentura/sporto-registras-public/badge)](https://securityscorecards.dev/viewer/?platform=github.com&org={Nacionaline-sporto-agentura}&repo={sporto-registras-public})
[![License](https://img.shields.io/github/license/Nacionaline-sporto-agentura/sporto-registras-public)](https://github.com/Nacionaline-sporto-agentura/sporto-registras-public/blob/main/LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/Nacionaline-sporto-agentura/sporto-registras-public)](https://github.com/Nacionaline-sporto-agentura/sporto-registras-public/issues)
[![GitHub stars](https://img.shields.io/github/stars/Nacionaline-sporto-agentura/sporto-registras-public)](https://github.com/Nacionaline-sporto-agentura/sporto-registras-public/stargazers)

This repository contains the source code and documentation for the sporto-registras-public, developed for the National Sport Agency.

## Table of Contents

- [About the Project](#about-the-project)
- [Getting Started](#getting-started)
    - [Installation](#installation)
    - [Usage](#usage)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

## About the Project

Lorem ipsum

This is a client application that utilizes

Key features of the WEB include:

- Lorem ipsum

## Getting Started

To get started with the sporto-registras-public, follow the instructions below.

### Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/Nacionaline-sporto-agentura/sporto-registras-public.git
   ```

2. Install the required dependencies:

   ```bash
   cd sporto-registras-public
   composer install
   ```
3. Rename docker-compose.env.example to docker-composer.env and copy Env Format salts generated at `https://roots.io/salts.html`

### Usage

1. Rename .env.example to .env and copy Env Format salts generated at `https://roots.io/salts.html`

2. Start the WEB server:

   ```bash
   npm run dc:up
   ```

The WEB will be available at `http://localhost`.

3. Install Wordpress

4. Login to MariaDB database via Adminer available at `http://localhost:8888`

    host: mariadb
    dbname: sporto_registras
    dbuser: mariadb
    password: mariadb

5. Login to MinIO dashboard available at `http://localhost:9931`

    user: minioadmin
    pass: minioadmin

    Bucket access permission: Public

## Deployment

### Production

To deploy the application to the production environment, create a new GitHub release:

1. Go to the repository's main page on GitHub.
2. Click on the "Releases" tab.
3. Click on the "Create a new release" button.
4. Provide a version number, such as `1.2.3`, and other relevant information.
5. Click on the "Publish release" button.

### Staging

The `main` branch of the repository is automatically deployed to the staging environment. Any changes pushed to the main
branch will trigger a new deployment.

### Development

To deploy any branch to the development environment use the `Deploy to Development` GitHub action.

## Contributing

Contributions are welcome! If you find any issues or have suggestions for improvements, please open an issue or submit a
pull request. For more information, see the [contribution guidelines](./CONTRIBUTING.md).

## License

This project is licensed under the [MIT License](./LICENSE).