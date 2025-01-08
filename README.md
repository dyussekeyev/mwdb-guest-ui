# MWDB Guest UI

## Description
MWDB Guest UI is designed to provide unauthenticated users with access to basic MWDB functionalities. For more information, see the [mwdb-core project by CERT Polska](https://github.com/CERT-Polska/mwdb-core).

## Installation
To install and run the project as a Docker container, follow these steps:

1. Clone the repository:
    ```bash
    git clone https://github.com/dyussekeyev/mwdb-guest-ui.git
    ```
2. Navigate to the project directory:
    ```bash
    cd mwdb-guest-ui
    ```
3. Build the Docker image:
    ```bash
    docker build -t mwdb-guest-ui .
    ```
4. Run the Docker container:
    ```bash
    docker run -d -p 8000:80 mwdb-guest-ui
    ```
   This will start the container and map port 8000 on your host to port 80 in the container.

## Usage
...

## Contributing
If you wish to contribute to the project, please follow these steps:

1. Fork the repository.
2. Create a new branch (`git checkout -b feature/your-feature`).
3. Make your changes and commit them (`git commit -am 'Add your feature'`).
4. Push the changes to the remote repository (`git push origin feature/your-feature`).
5. Create a new Pull Request.

## License
This project is licensed under the MIT License. See the LICENSE file for details.
