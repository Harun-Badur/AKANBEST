<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    /** @param array<string, string> $rules field => "rule1|rule2:param" */
    public static function make(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            $labels = explode('|', $ruleString);

            $isEmpty = !is_string($value) || trim($value) === '';

            foreach ($labels as $label) {
                [$name, $argument] = array_pad(explode(':', $label, 2), 2, null);

                switch ($name) {
                    case 'required':
                        if ($isEmpty) {
                            $errors[$field][] = 'required';
                        }
                        break;

                    case 'email':
                        if (!$isEmpty && (!is_string($value)
                            || filter_var($value, FILTER_VALIDATE_EMAIL) === false)) {
                            $errors[$field][] = 'email';
                        }
                        break;

                    case 'max':
                        if (!$isEmpty && is_string($value) && mb_strlen($value) > (int) $argument) {
                            $errors[$field][] = 'max';
                        }
                        break;

                    case 'min':
                        if (!$isEmpty && is_string($value) && mb_strlen($value) < (int) $argument) {
                            $errors[$field][] = 'min';
                        }
                        break;

                    case 'phone':
                        // TR-friendly: optional +90/0 prefix, 10 digits, optional separators.
                        if (!$isEmpty && (!is_string($value)
                            || preg_match('/^(\+90|0)?\s?5\d{2}[\s.-]?\d{3}[\s.-]?\d{2}[\s.-]?\d{2}$/D', $value) !== 1)) {
                            $errors[$field][] = 'phone';
                        }
                        break;

                    case 'in':
                        $allowed = array_map('trim', explode(',', (string) $argument));
                        if (!$isEmpty && (!is_string($value) || !in_array($value, $allowed, true))) {
                            $errors[$field][] = 'in';
                        }
                        break;

                    default:
                        $errors[$field][] = 'unknown_rule:' . $name;
                }
            }
        }

        return $errors;
    }

    /** Stores submitted input in flash for re-populating the form. */
    public static function old(array $data): void
    {
        Session::flash('_old', $data);
    }
}
