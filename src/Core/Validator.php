<?php

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Validate data against rules
     * Example: Validator::make($data, ['email' => 'required|email'])
     */
    public static function make(array $data, array $rules): self
    {
        $instance = new self($data);
        $instance->validate($rules);
        return $instance;
    }

    /**
     * Run validation
     */
    public function validate(array $rules): void
    {
        foreach ($rules as $field => $ruleString) {
            $rulesArray = explode('|', $ruleString);
            
            foreach ($rulesArray as $rule) {
                $params = [];
                
                // Parse rule with parameters e.g. max:255
                if (strpos($rule, ':') !== false) {
                    list($rule, $paramStr) = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                
                $method = 'validate' . ucfirst($rule);
                
                if (method_exists($this, $method)) {
                    if (!$this->$method($field, $params)) {
                        break; // Stop at first error for this field
                    }
                }
            }
        }
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get all errors
     */
    public function errors(): array
    {
        return $this->errors;
    }
    
    /**
     * Get first error
     */
    public function firstError(): ?string 
    {
        $first = reset($this->errors);
        return $first ? $first[0] : null;
    }

    // --- Validation Rules ---

    private function validateRequired($field, $params): bool
    {
        $value = $this->data[$field] ?? null;
        if ($value === null || trim($value) === '') {
            $this->addError($field, 'กรุณากรอกข้อมูลนี้');
            return false;
        }
        return true;
    }

    private function validateEmail($field, $params): bool
    {
        $value = $this->data[$field] ?? '';
        if (empty($value)) return true; // Skip if optional (handled by required)
        
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'รูปแบบอีเมลไม่ถูกต้อง');
            return false;
        }
        return true;
    }

    private function validateNumeric($field, $params): bool
    {
        $value = $this->data[$field] ?? '';
        if (empty($value)) return true;
        
        if (!is_numeric($value)) {
            $this->addError($field, 'ต้องเป็นตัวเลขเท่านั้น');
            return false;
        }
        return true;
    }

    private function validateDate($field, $params): bool
    {
        $value = $this->data[$field] ?? '';
        if (empty($value)) return true;

        $date = date_parse($value);
        if ($date['error_count'] > 0 || $date['warning_count'] > 0) {
            $this->addError($field, 'รูปแบบวันที่ไม่ถูกต้อง');
            return false;
        }
        return true;
    }

    private function validateMax($field, $params): bool
    {
        $value = $this->data[$field] ?? '';
        $max = (int) $params[0];
        
        if (strlen($value) > $max) {
            $this->addError($field, "ต้องมีความยาวไม่เกิน {$max} ตัวอักษร");
            return false;
        }
        return true;
    }
    
    private function validateMin($field, $params): bool
    {
        $value = $this->data[$field] ?? '';
        $min = (int) $params[0];
        
        if (strlen($value) < $min) {
            $this->addError($field, "ต้องมีความยาวอย่างน้อย {$min} ตัวอักษร");
            return false;
        }
        return true;
    }
    
    private function validateIn($field, $params): bool
    {
        $value = $this->data[$field] ?? '';
        if (empty($value)) return true;
        
        if (!in_array($value, $params)) {
             $this->addError($field, 'ข้อมูลที่เลือกไม่ถูกต้อง');
             return false;
        }
        return true;
    }

    private function addError($field, $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
}
