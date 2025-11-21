<?php

namespace App\Filament\Auth;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Http\Livewire\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class Login extends BaseLogin
{
    protected function getFormSchema(): array
    {
        return [
            TextInput::make('email')
                ->label('No. KK / Email')
                ->placeholder('Masukkan No. KK atau Email')
                ->required()
                ->rule('string')
                ->autocomplete('username'),
            TextInput::make('password')
                ->label(__('filament::login.fields.password.label'))
                ->placeholder('Masukkan password')
                ->password()
                ->required(),
            Checkbox::make('remember')
                ->label(__('filament::login.fields.remember.label')),
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'email' => __('filament::login.messages.throttled', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        $data = $this->form->getState();

        // Try login with email or family_card_number
        $credentials = [
            'password' => $data['password'],
        ];

        // Check if input is email or No. KK
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $data['email'];
        } else {
            $credentials['family_card_number'] = $data['email'];
        }

        if (!auth()->attempt($credentials, $data['remember'] ?? false)) {
            throw ValidationException::withMessages([
                'email' => __('filament::login.messages.failed'),
            ]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

}
