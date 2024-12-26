<?php
date_default_timezone_set('UTC');

// Database configuration variables
const DB_HOST = "localhost";    // MySQL host server
const DB_USER = "myuser";       // MySQL database username
const DB_PASSWORD = "mypassword";   // MySQL database password
const DB_NAME = "mydatabase";   // MySQL database name

const DB_TABLE_PREFIX = "tbl_";    // Do not change
const DB_CHARSET = 'utf8mb4_general_ci';    // Do not change

// Define the main link of the website
// This link should point to where the bot files are located.
// For example, if your bot files are located in a directory called "mybot" on your server, you would set the main link to:
// https://www.example.com/mybot
const MAIN_LINK = "https://example.com/mybot";

// The Telegram bot token
// To obtain the bot token, you need to create a new bot using the BotFather in Telegram.
// Search for the BotFather, start a chat with him, and follow the instructions to create a new bot.
// Once you've created the bot, the BotFather will provide you with a token that you can use to authenticate your bot with the Telegram API.
const TOKEN = '1234567890:abcdefghijklmnopqrstuvwxyz';

// IMPORTANT
// Enable inline mode for the bot
// To enable inline mode for your bot, you need to go to the BotFather chat and send the command /setinline.
// Follow the instructions to enable inline mode for your bot.
// Once you've enabled inline mode, your bot will be able to receive inline queries from users and return results.
// Also make sure `Inline feedback` is set to 100%.

// The Telegram bot username without @
const BOT_USERNAME = "MyBot";

// The Telegram bot name
const BOT_NAME = "My Bot Name";

// Define the ATTACH_CHANNEL variable
// This variable should contain the username of the public channel.
// To set up the channel, you need to create a public channel in Telegram and add your bot as an administrator of the channel.
// Once the bot is added as an administrator, you can set the ATTACH_CHANNEL variable to the username of the channel, without the "@" symbol.
const ATTACH_CHANNEL = 'attach_channel';

// Define whether the bot should use the IMGBB API to upload images.
// Set ATTACH_IMGBB_STATE to true to enable image uploads via IMGBB API.
// Set it to false if you do not want to use IMGBB.
const ATTACH_IMGBB_STATE = false;

// Define the IMGBB API Key.
// If ATTACH_IMGBB_STATE is true, you must provide your IMGBB API key here.
// You can obtain an API key by signing up at https://api.imgbb.com and creating an API key.
const ATTACH_IMGBB_API_KEY = '';

// Define the TG_ERROR_REPORTING_CHAT_ID variable
// This variable should contain the numeric ID of the Telegram account where you want to log errors.
// To find your Telegram account ID, you can send a message to the "get_id_bot" bot on Telegram and it will reply with your account ID.
// If you don't want to log errors in your Telegram account, you can set this variable to null.
const TG_ERROR_REPORTING_CHAT_ID = null;

// Define the SPONSOR_CHANNEL_ENABLE and SPONSOR_CHANNELS variables
// If you want to force the bot's users to join sponsor channels, set the SPONSOR_CHANNEL_ENABLE variable to true and set the SPONSOR_CHANNELS variable to an array of sponsor channels.
// To set up the sponsor channels, you need to create channels in Telegram and add your bot as an administrator of the channel.
// Once the bot is added as an administrator, you can add the channel information to the SPONSOR_CHANNELS array.
// If you don't want to force the bot's users to join sponsor channels, set the SPONSOR_CHANNEL_ENABLE variable to false and leave the SPONSOR_CHANNELS variable empty.
const SPONSOR_CHANNEL_ENABLE = false;
const SPONSOR_CHANNELS = [
    [
        'link' => "https://t.me/sponser_channel_1",
        'username' => "@sponser_channel_1"
    ],
    [
        'link' => "https://t.me/sponser_channel_2",
        'username' => "@sponser_channel_2"
    ]
];

// Define the DEFAULT_LANGUAGE and LANGUAGES variables
// We recommend not changing these settings unless you want to add a new language to the bot.
// To add a new language, you can use the "poedit" program and save the desired files to the "languages" folder.
const DEFAULT_LANGUAGE = 'en_US';
const LANGUAGES = [
    'en_US' => [
        'code' => 'en_US',
        'name' => "🇬🇧 English",
        'timezone' => 'UTC',
    ],
    'fa_IR' => [
        'code' => 'fa_IR',
        'name' => "🇮🇷 فارسی",
        'timezone' => 'Asia/Tehran',
    ],
];

// Define the BANNER_LINK variable
// This variable should be an array where the keys are language codes and the values are banner links that point to the banners of the bot in each language.
// To add banners, you need to upload the images to a hosting service and get the direct links to the images.
// Once you have the links, you can add them to the BANNER_LINK array with the language code as the key.
const BANNER_LINK = [
    'en_US' => MAIN_LINK . "/img/banner-en.jpg",
    'fa_IR' => MAIN_LINK . "/img/banner-fa.jpg",
];

// Do not change
const TEMP_FILES_DIR_PATH_PREFIX = 'files/temp';
const TEMP_FILES_DIR_FULL_PATH = __DIR__ . '/' . TEMP_FILES_DIR_PATH_PREFIX;
const TEMP_FILES_DIR_URL = MAIN_LINK . '/' . TEMP_FILES_DIR_PATH_PREFIX;
