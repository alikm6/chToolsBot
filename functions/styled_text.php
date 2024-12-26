<?php

function markdownv2_special_chars_encode(string $text): string
{
    $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    $escapedChars = ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)', '\\~', '\\`', '\\>', '\\#', '\\+', '\\-', '\\=', '\\|', '\\{', '\\}', '\\.', '\\!'];

    $text = str_replace('\\', '\\\\', $text);
    $text = str_replace($specialChars, $escapedChars, $text);

    return $text;
}

function markdown_special_chars_encode(string $text, string $mode = 'default'): string
{
    if ($mode == 'default') {
        $specialChars = ['_', '*', '`', '['];
        $escapedChars = ['\\_', '\\*', '\\`', '\\['];

        $text = str_replace($specialChars, $escapedChars, $text);
    } elseif ($mode == 'text_link') {
        $text = str_replace(']', '', $text);
    } elseif ($mode == 'bold') {
        $text = str_replace('*', '', $text);
    } elseif ($mode == 'italic') {
        $text = str_replace('_', '', $text);
    } elseif ($mode == 'code' || $mode == 'pre') {
        $text = str_replace('`', '', $text);
    }

    return $text;
}

function special_chars_encode(string $text, string $format = 'html'): string
{
    $format = strtolower($format);

    if ($format == 'html') {
        return htmlspecialchars($text);
    } elseif ($format == 'markdown') {
        return markdown_special_chars_encode($text);
    } elseif ($format == 'markdownv2') {
        return markdownv2_special_chars_encode($text);
    } else {
        throw new InvalidArgumentException('Invalid format!');
    }
}

function convert_to_styled_text(string $text, ?array $entities = null, string $format = 'html'): string
{
    $format = strtolower($format);

    if ($format != 'html' && $format != 'markdown' && $format != 'markdownv2') {
        throw new InvalidArgumentException('Invalid format!');
    }

    if (empty($entities)) {
        return special_chars_encode($text, $format);
    }

    foreach ($entities as $key => $entity) {
        if (
            $entity['type'] != 'bold' &&
            $entity['type'] != 'italic' &&
            $entity['type'] != 'code' &&
            $entity['type'] != 'pre' &&
            $entity['type'] != 'text_link' &&
            (
                $format == 'markdown' ||
                (
                    $entity['type'] != 'underline' &&
                    $entity['type'] != 'strikethrough' &&
                    $entity['type'] != 'spoiler'
                )
            ) &&
            (
                $format == 'markdown' ||
                $format == 'markdownv2' ||
                (
                    $entity['type'] != 'blockquote' &&
                    $entity['type'] != 'expandable_blockquote'
                )
            )
        ) {
            unset($entities[$key]);
        }
    }

    if (empty($entities)) {
        return special_chars_encode($text, $format);
    }

    $entities = array_values($entities);

    $offsets = [];
    $lengths = [];
    $keys = [];

    foreach ($entities as $key => $entity) {
        $keys[$key] = $key;
        $offsets[$key] = $entity['offset'];
        $lengths[$key] = $entity['length'];
    }

    array_multisort($offsets, SORT_ASC, $lengths, SORT_DESC, $keys, SORT_ASC, $entities);

    if ($format == 'markdown') {
        $previous_entity_keys = [];

        $previous_entity = $entities[0];

        for ($i = 1; $i < count($entities); $i++) {
            $current_entity = $entities[$i];

            if ($current_entity['offset'] < $previous_entity['offset'] + $previous_entity['length']) {
                $previous_entity_keys[] = $i;
            } else {
                $previous_entity = $current_entity;
            }
        }

        if (!empty($previous_entity_keys)) {
            rsort($previous_entity_keys);

            foreach ($previous_entity_keys as $previous_entity_key) {
                unset($entities[$previous_entity_key]);
            }
        }
    }

    if (empty($entities)) {
        return special_chars_encode($text, $format);
    }

    $entities = array_values($entities);

    $sub_texts_offsets = [];

    foreach ($entities as $entity) {
        $sub_texts_offsets[] = $entity['offset'];
        $sub_texts_offsets[] = $entity['offset'] + $entity['length'];
    }

    $sub_texts_offsets = array_unique($sub_texts_offsets);
    sort($sub_texts_offsets);

    $str = mb_convert_encoding($text, 'UTF-16', 'UTF-8');
    $sub_texts = [];

    for ($i = 0; $i < count($sub_texts_offsets) + 1; $i++) {
        if (isset($sub_texts_offsets[$i]) && $sub_texts_offsets[$i] == 0) {
            continue;
        }

        $start = $sub_texts_offsets[$i - 1] ?? 0;
        $end = $sub_texts_offsets[$i] ?? null;
        $length = $end != null ? ($end - $start) : null;

        $segment = mb_substr($str, $start, $length, 'UCS-2');
        $sub_texts[] = [
            'offset' => $start,
            'length' => $length,
            'text' => mb_convert_encoding(
                $segment,
                'UTF-8',
                'UTF-16',
            ),
        ];
    }

    $reversed_entities = array_reverse($entities);

    $final_text = "";
    $offset_index = 0;

    $opened_tags = [];

    foreach ($sub_texts as $sub_text) {
        foreach ($reversed_entities as $entity) {
            if ($offset_index != $entity['offset'] + $entity['length']) {
                continue;
            }

            unset($opened_tags[array_key_last($opened_tags)]);

            if ($format == 'html') {
                if ($entity['type'] == 'bold') {
                    $final_text .= "</b>";
                } elseif ($entity['type'] == 'italic') {
                    $final_text .= "</i>";
                } elseif ($entity['type'] == 'underline') {
                    $final_text .= "</u>";
                } elseif ($entity['type'] == 'strikethrough') {
                    $final_text .= "</s>";
                } elseif ($entity['type'] == 'spoiler') {
                    $final_text .= "</tg-spoiler>";
                } elseif ($entity['type'] == 'blockquote') {
                    $final_text .= "</blockquote>";
                } elseif ($entity['type'] == 'expandable_blockquote') {
                    $final_text .= "</blockquote>";
                } elseif ($entity['type'] == 'code') {
                    $final_text .= "</code>";
                } elseif ($entity['type'] == 'pre') {
                    $final_text .= "</pre>";
                } elseif ($entity['type'] == 'text_link') {
                    $final_text .= "</a>";
                }
            } else {
                if ($entity['type'] == 'bold') {
                    $final_text .= "*";
                } elseif ($entity['type'] == 'italic') {
                    $final_text .= "_";
                } elseif ($entity['type'] == 'underline') {
                    $final_text .= "__";
                } elseif ($entity['type'] == 'strikethrough') {
                    $final_text .= "~";
                } elseif ($entity['type'] == 'spoiler') {
                    $final_text .= "||";
                } elseif ($entity['type'] == 'code') {
                    $final_text .= "`";
                } elseif ($entity['type'] == 'pre') {
                    $final_text .= "\n```";
                } elseif ($entity['type'] == 'text_link') {
                    if ($format == 'markdown') {
                        $final_text .= "](" . $entity['url'] . ")";
                    } else {
                        $final_text .= "](" . markdownv2_special_chars_encode($entity['url']) . ")";
                    }
                }
            }
        }

        foreach ($entities as $entity) {
            if ($offset_index != $entity['offset']) {
                continue;
            }

            $opened_tags[] = $entity['type'];

            if ($format == 'html') {
                if ($entity['type'] == 'bold') {
                    $final_text .= "<b>";
                } elseif ($entity['type'] == 'italic') {
                    $final_text .= "<i>";
                } elseif ($entity['type'] == 'underline') {
                    $final_text .= "<u>";
                } elseif ($entity['type'] == 'strikethrough') {
                    $final_text .= "<s>";
                } elseif ($entity['type'] == 'spoiler') {
                    $final_text .= "<tg-spoiler>";
                } elseif ($entity['type'] == 'blockquote') {
                    $final_text .= "<blockquote>";
                } elseif ($entity['type'] == 'expandable_blockquote') {
                    $final_text .= "<blockquote expandable>";
                } elseif ($entity['type'] == 'code') {
                    $final_text .= "<code>";
                } elseif ($entity['type'] == 'pre') {
                    $final_text .= "<pre>";
                } elseif ($entity['type'] == 'text_link') {
                    $final_text .= "<a href=\"{$entity['url']}\">";
                }
            } else {
                if ($entity['type'] == 'bold') {
                    $final_text .= "*";
                } elseif ($entity['type'] == 'italic') {
                    $final_text .= "_";
                } elseif ($entity['type'] == 'underline') {
                    $final_text .= "__";
                } elseif ($entity['type'] == 'strikethrough') {
                    $final_text .= "~";
                } elseif ($entity['type'] == 'spoiler') {
                    $final_text .= "||";
                } elseif ($entity['type'] == 'code') {
                    $final_text .= "`";
                } elseif ($entity['type'] == 'pre') {
                    $final_text .= "```\n";
                } elseif ($entity['type'] == 'text_link') {
                    $final_text .= "[";
                }
            }
        }

        if ($format == 'html') {
            $final_text .= htmlspecialchars($sub_text['text']);
        } elseif ($format == 'markdown') {
            if (empty($opened_tags)) {
                $final_text .= markdown_special_chars_encode($sub_text['text']);
            } else {
                $final_text .= markdown_special_chars_encode($sub_text['text'], $opened_tags[array_key_last($opened_tags)]);
            }
        } else {
            $final_text .= markdownv2_special_chars_encode($sub_text['text']);
        }

        $offset_index += $sub_text['length'];
    }

    return $final_text;
}