<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\SubscriptionLimitService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureAuthorization();
        $this->configureDefaults();
        $this->configureEventListeners();
        $this->configureMailTemplates();
        $this->configureRateLimiting();

    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    /**
     * Configure the super-admin authorization bypass.
     */
    private function configureAuthorization(): void
    {
        Gate::before(function (?User $user, $ability) {
            if (! $user) {
                return null;
            }

            return $user->hasRole('super_admin') ? true : null;
        });
    }

    /**
     * Configure event listeners for the application.
     */
    private function configureEventListeners(): void
    {
        Event::listen(
            \Laravel\Cashier\Events\WebhookHandled::class,
            \App\Listeners\HandleSubscriptionUpdated::class,
        );
    }

    /**
     * Configure custom mail templates for authentication notifications.
     */
    private function configureMailTemplates(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject(__('Verify Email Address'))
                ->view('mail.verify-email', [
                    'url' => $url,
                    'user' => $notifiable,
                ]);
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $url = url('/reset-password?'.http_build_query([
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]));

            return (new MailMessage)
                ->subject(__('Reset Password Notification'))
                ->view('mail.reset-password', [
                    'url' => $url,
                    'user' => $notifiable,
                    'expireMinutes' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
                ]);
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $throttleKey = Str::transliterate(
                Str::lower($request->string('email')).'|'.$request->ip(),
            );

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request): Limit {
            $loginId = $request->session()->get('login.id') ?? '';
            $throttleKey = $loginId.'|'.$request->ip();

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
