<?php

namespace App\DTOs;

use App\Enums\VerificationCodeIdentifierKey;
use App\Enums\VerificationCodePurpose;
use App\Http\Requests\Api\VerificationCodeRequest;

readonly class VerificationCodeData
{
    /**
     * Create a new VerificationCodeData instance.
     */
    public function __construct(
        public VerificationCodePurpose $purpose,
        public VerificationCodeIdentifierKey $identifier_key,
        public string $identifier_value,
    ) {}

    /**
     * Create a new VerificationCodeData instance from a VerificationCodeRequest.
     */
    public static function fromRequest(VerificationCodeRequest $request): self
    {
        return new self(
            purpose: VerificationCodePurpose::from($request->validated('purpose')),
            identifier_key: VerificationCodeIdentifierKey::from($request->validated('identifier_key')),
            identifier_value: $request->validated('identifier_value'),
        );
    }

    /**
     * Convert the VerificationCodeData instance to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'purpose' => $this->purpose->value,
            'identifier_key' => $this->identifier_key->value,
            'identifier_value' => $this->identifier_value,
        ];
    }
}
