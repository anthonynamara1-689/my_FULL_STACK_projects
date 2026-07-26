<?php

declare(strict_types=1);

class ValidationService
{
    public static function validateRequired(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                throw new InvalidArgumentException(ucwords(str_replace('_', ' ', $field)) . ' is required.');
            }
        }
    }
}
