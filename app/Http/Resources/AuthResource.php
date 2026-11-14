<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Disable the default "data" wrapper.
     *
     * @var string|null
     */
    public static $wrap = null;

    /**
     * Create a new AuthResource instance.
     */
    public function __construct($resource, public ?string $message = null)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = data_get($this->resource, 'user');

        if (data_get($this->resource, 'twoFactorRequired', false)) {
            return [
                'message' => $this->message,
                'verification_token' => data_get($this->resource, 'verificationToken'),
                'expires_in' => (int) data_get($this->resource, 'expiresIn'),
                'two_factor_required' => true,
            ];
        }

        return [
            'message' => $this->message,
            'user' => UserResource::make($user),
            'access_token' => data_get($this->resource, 'accessToken'),
            'refresh_token' => data_get($this->resource, 'refreshToken'),
            'expires_in' => (int) (data_get($this->resource, 'expiresIn')),
            'two_factor_required' => false,
        ];
    }
}
