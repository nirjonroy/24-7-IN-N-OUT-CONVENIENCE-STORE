<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    public const TOPICS = [
        'general' => 'General Question',
        'store' => 'Convenience Store',
        'phone-repair' => 'Phone Repair',
        'smoothies' => 'Smoothies',
        'adult-retail' => 'Adult Retail',
        'other' => 'Other',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->trimNullable('name'),
            'email' => $this->trimNullable('email'),
            'phone' => $this->trimNullable('phone'),
            'topic' => $this->trimNullable('topic'),
            'subject' => $this->trimNullable('subject'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'topic' => ['nullable', 'string', 'max:100', Rule::in(array_keys(self::TOPICS))],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'contact_company' => ['nullable', 'string', 'max:255'],
            'contact_started_at' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'topic.in' => 'Please choose one of the available topics.',
            'message.min' => 'Please include at least 10 characters so we can understand your request.',
        ];
    }

    public function topicLabel(): ?string
    {
        return self::TOPICS[$this->validated('topic')] ?? null;
    }

    public function honeypotFilled(): bool
    {
        return filled($this->input('contact_company'));
    }

    public function submittedTooFast(): bool
    {
        $startedAt = (int) $this->input('contact_started_at');

        return $startedAt > 0 && now()->timestamp - $startedAt < 1;
    }

    private function trimNullable(string $key): ?string
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
