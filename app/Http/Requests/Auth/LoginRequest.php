<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['sometimes', 'string'],
            'email' => ['sometimes', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = (string) ($this->input('login') ?: $this->input('email') ?: $this->input('phone'));
        $login = trim($login);
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        // Check if account exists and is inactive
        $user = \App\Models\User::where($field, $login)
            ->orWhere(function ($q) use ($login) {
                // If input starts with 01..., also check without leading 0 or with 88
                if (preg_match('/^01[3-9]\d{8}$/', $login)) {
                    $q->where('phone', $login)
                        ->orWhere('phone', '88' . $login)
                        ->orWhere('phone', '+88' . $login);
                }
            })->first();

        if ($user && $user->status === 'inactive') {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'login' => 'আপনার অ্যাকাউন্টটি বর্তমানে নিষ্ক্রিয় (Inactive) রয়েছে। অনুগ্রহ করে অ্যাডমিন বা সাপোর্টের সাথে যোগাযোগ করুন।',
            ]);
        }

        $credentials = [
            $field => $login,
            'password' => $this->input('password'),
            'status' => 'active',
        ];

        $attemptSuccess = Auth::attempt($credentials, $this->boolean('remember'));

        // If direct attempt failed and it's a phone with BD variations, try matching the actual user's phone field
        if (!$attemptSuccess && $user && $user->status === 'active') {
            $attemptSuccess = Auth::attempt([
                'id' => $user->id,
                'password' => $this->input('password'),
                'status' => 'active',
            ], $this->boolean('remember'));
        }

        if (! $attemptSuccess) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'মোবাইল নম্বর অথবা পাসওয়ার্ডটি সঠিক নয়। (Invalid credentials)',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $login = (string) ($this->input('login') ?: $this->input('email') ?: $this->input('phone'));
        return Str::transliterate(Str::lower(trim($login)) . '|' . $this->ip());
    }
}
