<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Auth::viaRequest('api', function ($request) {
            $token = $request->header('Authorization');
            $valid = false;


            if (!$token) {
                return null;
            }


            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
                $valid = true;
            } else {
                return null;
            }
            
            if ($valid) {
                return PersonalAccessToken::where('token', $token)->first();
            } else {
                return null;
            }
        });
    }
}
