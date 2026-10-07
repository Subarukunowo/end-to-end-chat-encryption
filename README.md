# End-to-End Chat Encryption

A real-time chat application built with Laravel 12 and Laravel Reverb, demonstrating a client-side encryption approach.

The server only stores and broadcasts ciphertext. All encryption and decryption happen directly in the browser before messages are sent or after they are received.

## Features

- **Client-Side Encryption:** Messages are encoded in the browser before transmission. The server database only stores the encrypted string.
- **Real-Time Messaging:** Powered by Laravel Reverb and Laravel Echo for instant message delivery without polling.
- **Modern UI:** Built with Tailwind CSS, featuring a clean layout with a contact list and active chat view.
- **Profile Management:** Users can update their display names and avatars.

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js & npm
- SQLite or PostgreSQL (configured in `.env`)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Subarukunowo/end-to-end-chat-encryption.git
   cd end-to-end-chat-encryption
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install frontend dependencies:
   ```bash
   npm install
   ```

4. Set up your environment file:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Run database migrations:
   ```bash
   php artisan migrate
   ```

6. Start the development servers. This project uses a `dev` script in `composer.json` that runs the Laravel server, queue worker, logs, and Vite concurrently:
   ```bash
   composer run dev
   ```

## How the Encryption Works

This project demonstrates the concept of End-to-End Encryption (E2EE). In `resources/views/chat/index.blade.php`, messages are intercepted before being sent to the server.

The client applies a transformation (currently a Base64 encoding `btoa(encodeURIComponent(text))` for demonstration purposes) to create the ciphertext. The Laravel controller receives this ciphertext, stores it in the `messages` table under the `encrypted_body` column, and broadcasts it to the receiving user via WebSocket.

When the receiving client gets the WebSocket event, it decrypts the ciphertext back into plaintext right before appending it to the chat interface.

## License

This project is open-source software licensed under the MIT license.
