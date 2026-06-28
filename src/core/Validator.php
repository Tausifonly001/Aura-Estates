<?php
class Validator {
    private $errors = [];
    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function required($field, $label = null) {
        $label = $label ?? $field;
        $value = $this->get($field);
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->errors[$field][] = "$label is required.";
        }
        return $this;
    }

    public function email($field, $label = null) {
        $value = $this->get($field);
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = ($label ?? $field) . ' must be a valid email.';
        }
        return $this;
    }

    public function minLength($field, $min, $label = null) {
        $value = $this->get($field);
        if ($value !== null && strlen($value) < $min) {
            $this->errors[$field][] = ($label ?? $field) . " must be at least $min characters.";
        }
        return $this;
    }

    public function maxLength($field, $max, $label = null) {
        $value = $this->get($field);
        if ($value !== null && strlen($value) > $max) {
            $this->errors[$field][] = ($label ?? $field) . " must not exceed $max characters.";
        }
        return $this;
    }

    public function numeric($field, $label = null) {
        $value = $this->get($field);
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->errors[$field][] = ($label ?? $field) . ' must be numeric.';
        }
        return $this;
    }

    public function inArray($field, array $allowed, $label = null) {
        $value = $this->get($field);
        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field][] = ($label ?? $field) . ' must be one of: ' . implode(', ', $allowed) . '.';
        }
        return $this;
    }

    public function integer($field, $label = null) {
        $value = $this->get($field);
        if ($value !== null && $value !== '' && !ctype_digit((string)$value)) {
            $this->errors[$field][] = ($label ?? $field) . ' must be an integer.';
        }
        return $this;
    }

    public function date($field, $format = 'Y-m-d', $label = null) {
        $value = $this->get($field);
        if ($value !== null && $value !== '') {
            $d = DateTime::createFromFormat($format, $value);
            if (!$d || $d->format($format) !== $value) {
                $this->errors[$field][] = ($label ?? $field) . " must be a valid date ($format).";
            }
        }
        return $this;
    }

    public function passes() {
        return empty($this->errors);
    }

    public function errors() {
        return $this->errors;
    }

    public function firstError() {
        foreach ($this->errors as $field => $msgs) {
            return $msgs[0];
        }
        return null;
    }

    public function validated(): array {
        return $this->errors ? [] : (array)$this->data;
    }

    private function get($field) {
        if (is_object($this->data)) {
            return $this->data->$field ?? null;
        }
        return $this->data[$field] ?? null;
    }

    public static function sanitize($value) {
        if (is_string($value)) {
            return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }

    public static function sanitizeAll(array $data): array {
        $clean = [];
        foreach ($data as $key => $value) {
            $clean[$key] = self::sanitize($value);
        }
        return $clean;
    }
}
