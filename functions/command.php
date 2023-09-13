<?php

function all_command_list(): array
{
    return [
        'help',
        'help_sendto',
        'help_addadmin',
        'help_getid',
        'help_inlinekey',
        'help_inlinecode',
        'help_watermark',
        'help_attach',
        'help_hyper',
        'help_formatting',
        'help_none',
        'help_contact',
        'sendto',
        'channels',
        'inlinekey',
        'inlinekey_list',
        'inlinekey_add',
        'inlinekey_edit',
        'inlinekey_delete',
        'attach',
        'hyper',
        'hyper_markdown',
        'hyper_markdownv2',
        'hyper_html',
        'decodehyper',
        'rename',
        'contact',
        'source',
        'cancel',
    ];
}

function command_list(): array
{
    return [
        [
            'command' => 'help',
            'description' => __('Help')
        ],
        [
            'command' => 'sendto',
            'description' => __('Send without quotes')
        ],
        [
            'command' => 'channels',
            'description' => __('Manage channels')
        ],
        [
            'command' => 'inlinekey',
            'description' => __('Manage inline buttons')
        ],
        [
            'command' => 'attach',
            'description' => __('Attach file')
        ],
        [
            'command' => 'hyper',
            'description' => __('Create hyper link')
        ],
        [
            'command' => 'decodehyper',
            'description' => __('Convert hyper text to its original text')
        ],
        [
            'command' => 'rename',
            'description' => __('Change Contact and Location information')
        ],
        [
            'command' => 'contact',
            'description' => __('Contact us')
        ],
        [
            'command' => 'source',
            'description' => __('Bot source')
        ],
        [
            'command' => 'cancel',
            'description' => __('Cancel the current operation')
        ],
    ];
}