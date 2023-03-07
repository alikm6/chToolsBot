# Telegram Bot to Help Channel Administrators

## Introduction
This project is the source code of a Telegram bot with various features such as sending all kinds of messages to the channel without quoting, creating a variety of inline buttons, attaching files, building hyperlinks, changing message information, changing messages caption and anonymize, etc. The bot supports two languages, English and Persian (فارسی).

A live demo of the bot is available at https://t.me/chToolsBot.

## Requirements
To use this Telegram bot, you need to have the following requirements:

- PHP 7.4 or higher
- Composer
- MySQL
- MultiCurl and exec must be enabled in PHP
- A domain with HTTPS authority must be connected to the web service

## Installation

### Create a Telegram Bot
Before getting started, you will need to create a Telegram bot by following these steps:
1. Open the Telegram app and search for the @BotFather bot.
2. Start a chat with @BotFather and send the message /newbot.
3. Follow the on-screen instructions to choose a name and username for your bot.
4. Once you have successfully created your bot, you will receive an API token. Save this token as you will need it later.
5. Also, make sure that the `Inline mode` for the bot is enabled in the BotFather settings and make sure `Inline feedback` is set to 100%.

### Clone Repository
To clone the repository, run the following command in your terminal:
```console
git clone https://github.com/alikm6/chToolsBot.git
```

### Import SQL File
After cloning the repository, import the provided `database.sql` file into your MySQL database.

### Install Dependencies
To install dependencies, navigate to the root directory of the project in your terminal and run the following command:
```consol
composer install
```

### Configuring the Bot
The `config.php` file must be set according to the instructions provided in the file itself. Please make sure to set the necessary variables, including your database connection information.

In addition to setting the config.php file, there is one more important step to take. An account must be set as the **robot admin**. To do this, you must manually add the admin account information to the `tbl_admins` table in the database. The `user_id` column in this table should be set with the numeric ID of the admin account. This will allow the admin account to have access to certain commands and features not available to regular users.

### Set Up Webhook
To receive updates from Telegram, you need to set up a webhook for your bot. This can be done using the following steps:
1. First, make sure your server has a valid SSL certificate and is accessible over HTTPS.
2. Next, obtain your bot's token from the BotFather on Telegram.
3. Determine the URL where your bot will be hosted. This should be the full URL to your `webhook.php` file, including the domain and path.

   The URL should also include the GET request parameter `token=<bot_token>` to pass the token for your bot.
   
   For example, https://example.com/mybot/webhook.php?token=1234567890:abcdefghijklmnopqrstuvwxyz.
4. Set up the Telegram bot webhook manually by sending a request to the Telegram Bot API with the following parameters:
   ```consol
    https://api.telegram.org/bot<bot_token>/setWebhook?url=<webhook_url>&max_connections=100
    ```
    - `<bot_token>` is the token for your bot. 
    - `<webhook_url>` is the URL of your webhook.php file.
    - Set the `max_connections` parameter to 100 to ensure that the bot can handle multiple requests.
5. Once you've set up the webhook, Telegram will send all updates to your bot to the URL you provided. You can test your webhook by sending a message to your bot on Telegram.

### /forward Command
To enable the `/forward` command, you should add the following line to the `crontab`:
```
*/5 * * * * curl https://example.com/mybot/cron/forward.php >/dev/null 2>&1
```
Make sure to replace https://example.com/mybot with the actual location of the root of your bot.

## Usage
You can use the Telegram bot to perform various tasks such as sending messages, creating inline buttons, attaching files, and more. The bot supports two languages, English and Persian. To switch between the languages, you can use the /lang command.

## Admin Commands
The following commands are available for the bot admin:

- `/forward` - Send a message to all users of the bot. To use this command, reply to the target message with /forward.
- `/stats` - View statistics about the bot usage.

Only users who are registered as admins in the tbl_admins table in the database can use these commands.

## Support
If you encounter any issues while using the Telegram bot, please open an issue on the GitHub repository. We will try our best to resolve the issue as soon as possible.
