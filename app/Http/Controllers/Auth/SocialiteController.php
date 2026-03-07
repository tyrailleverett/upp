<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\HandleSocialiteCallbackAction;
use App\Enums\SocialiteProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

final class SocialiteController extends Controller
{
    public function redirect(SocialiteProvider $provider): SymfonyRedirectResponse
    {
        if (Auth::check()) {
            $previous = url()->previous();

            if (str_starts_with($previous, config('app.url'))) {
                session()->put('url.intended', $previous);
            }
        }

        return Socialite::driver($provider->value)->redirect();
    }

    public function callback(SocialiteProvider $provider, HandleSocialiteCallbackAction $action): RedirectResponse
    {
        try {
            $socialiteUser = Socialite::driver($provider->value)->user();
            $user = $action->execute($provider, $socialiteUser);
        } catch (InvalidStateException) {
            return redirect()->route('login')->withErrors([
                'email' => __('The authentication request was invalid. Please try again.'),
            ]);
        } catch (RuntimeException) {
            return redirect()->route('login')->withErrors([
                'email' => __('Your :provider account does not have an email address. Please add one and try again.', ['provider' => $provider->value]),
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}
