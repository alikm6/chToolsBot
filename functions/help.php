<?php

function get_help_config(): array
{
    return [
        [
            'key' => 'sendto',
            'name' => __("Send without quote to channel"),
            'messages' => [
                [
                    'type' => 'text',
                    'text' =>
                        __("When a robot creates a message and sends it to you, if you forward it to your channel, the name of our robot will remain at the top of the post and will not have a good view, and if you forward it using unofficial telegram programs, the hyper link, attachment and inline buttons are gone. To solve this problem, you can use sending without quotes.") . "\n" .
                        __("You can even create hyper text with any robot you like and use our robot to post it into your channel without quotes.") . "\n\n" .
                        __("There are two ways to send without a quote:") . "\n" .
                        "1- " . __("Send step by step") . "\n" .
                        "2- " . __("Send with only one command") . "\n\n" .
                        __("⚠️ Use /sendto_setting command to change the settings for this feature.") . "\n\n" .
                        __("⚠️ Use /channels command to add channels to the list of predefined channels.") . "\n\n",
                ],
                [
                    'type' => 'text',
                    'text' =>
                        "<b>" . __("Send step by step") . "</b>" . "\n\n" .
                        __("For this purpose, send /sendto command alone and then proceed step by step according to the robot training."),
                    'parse_mode' => 'html',
                ],
                [
                    'type' => 'text',
                    'text' =>
                        "<b>" . __("Send with only one command") . "</b>" . "\n\n" .
                        __("To do this, you must do the following:") . "\n\n" .
                        sprintf(__("1️⃣ Make %s bot the desired channel admin."), '@' . BOT_USERNAME) . "\n\n" .
                        __("⚠️ The second step is different for messages containing the inline button and other messages:") . "\n\n" .
                        __("For messages containing a inline button:") . "\n" .
                        __("2️⃣ Send this command to the following format for the robot:") . "\n" .
                        "/sendto {channel id} {inline code}" . "\n\n" .
                        __("❕For example:") . "\n" .
                        "/sendto @FarsBots HGwBUUFsfq" . "\n" .
                        "/sendto -1001031102294 HGwBUUFsfq" . "\n\n" .
                        __("For other messages:") . "\n" .
                        __("2️⃣ Reply this command in the following format to the post: (note that you must reply)") . "\n" .
                        "/sendto {channel id}" . "\n\n" .
                        __("❕For example:") . "\n" .
                        "/sendto @FarsBots" . "\n" .
                        "/sendto -1001031102294" . "\n\n",
                    'parse_mode' => 'html',
                ],
                [
                    'type' => 'animation',
                    "animation" => MAIN_LINK . __("/img/sendto_inlinekey_help_en.mp4") . "?v1",
                    "caption" => __("📍 Tutorial for sending messages without quotes containing a inline button to the channel with just one command"),
                ],
                [
                    'type' => 'animation',
                    "animation" => MAIN_LINK . __("/img/sendto_other_help_en.mp4") . "?v1",
                    "caption" => __("📍 Tutorial for send other messages to the channel without quoting with just one command"),
                ],
            ],
        ],
        [
            'key' => 'addadmin',
            'name' => __("How to administer a robot in a channel"),
            'messages' => [
                [
                    'type' => 'text',
                    'text' =>
                        __("It's like adding your friend as admin.") . "\n" .
                        sprintf(
                            __("With the difference that when you want to admin your friend, when you type the first one or two letters of the ID or even their name, the search result will appear, but this is not the case with the robot and the robot must have a Latin ID (not a name). Type in <b>full</b> (I emphasize: complete and not just the first few letters) to appear in the search list of the add admin section, so in the search section of the add admin section type: %s"),
                            "<code>" . BOT_USERNAME . "</code>"
                        ),
                    'parse_mode' => 'html',
                ],
            ],
        ],
        [
            'key' => 'getid',
            'name' => __("Get private channel ID"),
            'messages' => [
                [
                    'type' => 'text',
                    'text' => __("If you want to post content on a private channel without a quote, since private channels do not have an ID, you must first extract the channel ID and then send without a quote.") . "\n\n" .
                        __("You can use /getid command to extract the ID of private channels."),
                ],
            ],
        ],
        [
            'key' => 'inlinekey',
            'name' => __("inline buttons"),
            'messages' => [
                [
                    'type' => 'text',
                    'text' =>
                        __("With this robot, you can add any type of inline button (link, counter, etc.) to any type of message (text, photo, video, voice, etc.) and edit them even after publishing.") . "\n\n" .
                        __("You can use /inlinekey command to manage inline buttons.") . "\n\n" .
                        __("You have two ways to make a inline button:") . "\n" .
                        __("1 - Send /inlinekey first, then select Add.") . "\n" .
                        __("2 - First send us the message you want to add to the inline button and then send /inlinekey command in reply.") . "\n\n" .
                        __("After creating the message, you can view the statistics related to the message and also edit it. (By sending /inlinekey)"),
                ],
            ],
        ],
        [
            'key' => 'inlinecode',
            'name' => __("What is an inline code?"),
            'messages' => [
                [
                    'type' => 'text',
                    'text' =>
                        __("When you create a inline button, the robot sends you a 30-character English text that you need to use to use the message containing the glass button, view its statistics, edit it and delete it."),
                ],
            ],
        ],
        [
            'key' => 'watermark',
            'name' => __("Watermark"),
            'messages' => [
                [
                    'type' => 'text',
                    'text' =>
                        __("Use the @editgram_bot bot to watermark."),
                ],
            ],
        ],
        [
            'key' => 'attach',
            'name' => __("Attach file"),
            'messages' => [
                [
                    'type' => 'text',
                    'text' =>
                        __("With this robot, you can attach any type of file (photo, video, voice, sticker, etc.) to your post.") . "\n" .
                        __("The file you want will be displayed at the bottom of your text.") . "\n" .
                        __("All you have to do is use /attach command."),
                ],
            ],
        ],
        [
            'key' => 'hyper',
            'name' => __("Create hyper"),
            'messages' => [
                [
                    'type' => 'text',
                    'text' =>
                        __("With this bot you can create hyper messages in Markdown, MarkdownV2 and HTML formats.") . "\n" .
                        __("You can also attach a file to your hyper message.") . "\n" .
                        __("All you have to do is use /hyper command."),
                ],
            ],
        ],
        [
            'key' => 'formatting',
            'name' => __("Text Formatting"),
            'messages' => [
                [
                    'type' => 'text',
                    'text' =>
                        __("Text Formatting Guide:") . "\n" .
                        __("https://telegra.ph/chToolsBot-Guide-Text-Formatting-EN-09-10")
                    ,
                    'disable_web_page_preview' => false,
                ],
            ],
        ],
        [
            'key' => 'none',
            'name' => __("Anonymous post"),
            'messages' => [
                [
                    'type' => 'text',
                    'text' =>
                        __("Sometimes you get a message from someone you want to share but you don't want that person's name to be at the top of your message.") . "\n" .
                        __("For this purpose, when you send the word \"none\" in reply to the desired post, that post will be anonymous."),
                ],
            ],
        ],
        [
            'key' => 'contact',
            'name' => __("Contact us"),
            'messages' => [
                [
                    'type' => 'text',
                    'text' => __("To contact us, just send the command /contact and then send your suggestion, criticism or problem.") . "\n" .
                        __("You can also attach a file to your message."),
                ],
            ],
        ],
    ];
}

function get_help_menu()
{
    global $tg;
    $keyboard = [
        [
            __("Send without quote to channel"),
        ],
        [
            __("How to administer a robot in a channel"),
        ],
        [
            __("Get private channel ID"),
        ],
        [
            __("inline buttons"),
            __("What is an inline code?"),
        ],
        [
            __("Watermark"),
        ],
        [
            __("Attach file"),
        ],
        [
            __("Create hyper"),
        ],
        [
            __("Text Formatting"),
        ],
        [
            __("Anonymous post"),
        ],
        [
            __("Contact us"),
        ],
        [
            __("↩️ Cancel"),
        ],
    ];

    return $tg->replyKeyboardMarkup([
        'keyboard' => apply_rtl_to_keyboard($keyboard),
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
}