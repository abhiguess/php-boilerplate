<?php

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Validate data against rules
     *
     * Usage:
     *   $v = Validator::make($data, [
     *       'name'  => 'required|min:2|max:100',
     *       'email' => 'required|email',
     *       'age'   => 'numeric|min_value:18',
     *   ]);
     *   if ($v->fails()) { $errors = $v->errors(); }
     */
    public static function make(array $data, array $rules): self
    {
        $v = new self($data);

        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $data[$field] ?? null;
            $label = ucfirst(str_replace('_', ' ', $field));

            foreach ($fieldRules as $rule) {
                $param = null;

                if (str_contains($rule, ':')) {
                    [$rule, $param] = explode(':', $rule, 2);
                }

                match ($rule) {
                    'required' => $v->checkRequired($field, $value, $label),
                    'email'    => $v->checkEmail($field, $value, $label),
                    'numeric'  => $v->checkNumeric($field, $value, $label),
                    'integer'  => $v->checkInteger($field, $value, $label),
                    'min'      => $v->checkMin($field, $value, (int) $param, $label),
                    'max'      => $v->checkMax($field, $value, (int) $param, $label),
                    'min_value'=> $v->checkMinValue($field, $value, (float) $param, $label),
                    'max_value'=> $v->checkMaxValue($field, $value, (float) $param, $label),
                    'in'       => $v->checkIn($field, $value, $param, $label),
                    'unique'   => $v->checkUnique($field, $value, $param, $label),
                    'confirmed'=> $v->checkConfirmed($field, $value, $label),
                    'url'      => $v->checkUrl($field, $value, $label),
                    'date'     => $v->checkDate($field, $value, $label),
                    'regex'    => $v->checkRegex($field, $value, $param, $label),
                    default    => null,
                };
            }
        }

        return $v;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    private function checkRequired(string $field, mixed $value, string $label): void
    {
        if ($value === null || $value === '' || $value === []) {
            $this->addError($field, "$label is required.");
        }
    }

    private function checkEmail(string $field, mixed $value, string $label): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "$label must be a valid email.");
        }
    }

    private function checkNumeric(string $field, mixed $value, string $label): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, "$label must be a number.");
        }
    }

    private function checkInteger(string $field, mixed $value, string $label): void
    {
        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, "$label must be an integer.");
        }
    }

    private function checkMin(string $field, mixed $value, int $min, string $label): void
    {
        if ($value && strlen((string) $value) < $min) {
            $this->addError($field, "$label must be at least $min characters.");
        }
    }

    private function checkMax(string $field, mixed $value, int $max, string $label): void
    {
        if ($value && strlen((string) $value) > $max) {
            $this->addError($field, "$label must not exceed $max characters.");
        }
    }

    private function checkMinValue(string $field, mixed $value, float $min, string $label): void
    {
        if ($value !== null && $value !== '' && (float) $value < $min) {
            $this->addError($field, "$label must be at least $min.");
        }
    }

    private function checkMaxValue(string $field, mixed $value, float $max, string $label): void
    {
        if ($value !== null && $value !== '' && (float) $value > $max) {
            $this->addError($field, "$label must not exceed $max.");
        }
    }

    private function checkIn(string $field, mixed $value, string $options, string $label): void
    {
        $allowed = explode(',', $options);
        if ($value && !in_array($value, $allowed)) {
            $this->addError($field, "$label must be one of: $options.");
        }
    }

    /**
     * Check uniqueness in DB. Format: "table" or "table,column" or "table,column,except_id"
     */
    private function checkUnique(string $field, mixed $value, string $param, string $label): void
    {
        if (!$value) return;

        $parts = explode(',', $param);
        $table = $parts[0];
        $column = $parts[1] ?? $field;
        $exceptId = $parts[2] ?? null;

        $sql = "SELECT COUNT(*) as count FROM $table WHERE $column = ?";
        $params = [$value];

        if ($exceptId) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }

        $result = Database::queryOne($sql, $params);
        if (($result['count'] ?? 0) > 0) {
            $this->addError($field, "$label already exists.");
        }
    }

    private function checkConfirmed(string $field, mixed $value, string $label): void
    {
        $confirmation = $this->data[$field . '_confirmation'] ?? null;
        if ($value !== $confirmation) {
            $this->addError($field, "$label confirmation does not match.");
        }
    }

    private function checkUrl(string $field, mixed $value, string $label): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "$label must be a valid URL.");
        }
    }

    private function checkDate(string $field, mixed $value, string $label): void
    {
        if ($value && strtotime($value) === false) {
            $this->addError($field, "$label must be a valid date.");
        }
    }

    private function checkRegex(string $field, mixed $value, string $pattern, string $label): void
    {
        if ($value && !preg_match("/$pattern/", $value)) {
            $this->addError($field, "$label format is invalid.");
        }
    }
}
