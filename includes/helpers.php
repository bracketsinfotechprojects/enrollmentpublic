<?php
// includes/helpers.php

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function sanitize(mixed $value): mixed {
    if (is_array($value)) return array_map('sanitize', $value);
    return trim(strip_tags((string)$value));
}

function generateSlug(string $str): string {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9\-]/', '-', $str);
    return preg_replace('/-+/', '-', $str);
}

function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $time);
}

function formatBytes(int $bytes): string {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}

function getFormFieldTypes(): array {
    return [
        'Basic Fields' => [
            'text'          => ['label' => 'Text',          'icon' => 'fa-font'],
            'email'         => ['label' => 'Email',         'icon' => 'fa-envelope'],
            'number'        => ['label' => 'Number',        'icon' => 'fa-hashtag'],
            'tel'           => ['label' => 'Phone',         'icon' => 'fa-phone'],
            'url'           => ['label' => 'URL',           'icon' => 'fa-link'],
            'password'      => ['label' => 'Password',      'icon' => 'fa-lock'],
            'textarea'      => ['label' => 'Textarea',      'icon' => 'fa-align-left'],
        ],
        'Choice Fields' => [
            'select'        => ['label' => 'Dropdown',      'icon' => 'fa-chevron-down'],
            'radio'         => ['label' => 'Radio',         'icon' => 'fa-dot-circle'],
            'checkbox'      => ['label' => 'Checkbox',      'icon' => 'fa-check-square'],
        ],
        'Date & Time' => [
            'date'          => ['label' => 'Date',          'icon' => 'fa-calendar'],
            'time'          => ['label' => 'Time',          'icon' => 'fa-clock'],
            'datetime-local'=> ['label' => 'Date & Time',   'icon' => 'fa-calendar-alt'],
        ],
        'Advanced' => [
            'file'          => ['label' => 'File Upload',   'icon' => 'fa-upload'],
            'signature'     => ['label' => 'Signature',     'icon' => 'fa-pen-nib'],
            'hidden'        => ['label' => 'Hidden',        'icon' => 'fa-eye-slash'],
        ],
        'Layout' => [
            'heading'       => ['label' => 'Heading',       'icon' => 'fa-heading'],
            'paragraph'     => ['label' => 'Paragraph',     'icon' => 'fa-paragraph'],
            'divider'       => ['label' => 'Divider',       'icon' => 'fa-minus'],
            'spacer'        => ['label' => 'Spacer',        'icon' => 'fa-arrows-alt-v'],
        ],
    ];
}

function isLayoutField(string $type): bool {
    return in_array($type, ['heading', 'paragraph', 'divider', 'spacer']);
}
