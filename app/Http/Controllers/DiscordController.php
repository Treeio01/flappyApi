<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
class DiscordController extends Controller
{
    // /auth/discord/redirect
    public function redirect()
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver('discord')
            ->stateless()
            ->scopes(['identify', 'email']);

        return response()->json([
            'url' => $driver->redirect()->getTargetUrl()
        ]);
    }


    // /auth/discord/callback
    public function callback(Request $request)
    {
        // Временно в callback() перед обменом кодом:
        \Log::info('discord_config', [
            'client_id' => config('services.discord.client_id'),
            'redirect' => config('services.discord.redirect'),
            // secret в логи не пиши в проде, здесь только для диагностики
            'has_secret' => !!config('services.discord.client_secret'),
        ]);

        // Для SPA обычно удобнее stateless()
        $discordUser = Socialite::driver('discord')
            ->stateless()
            ->user();

        $user = User::updateOrCreate(
            ['discord_id' => $discordUser->getId()],
            [
                'name' => $discordUser->getName() ?? $discordUser->getNickname(),
                'email' => $discordUser->getEmail(),
                'discord_name' => $discordUser->getNickname() ?? $discordUser->getName(),
                'discord_avatar' => $discordUser->getAvatar(),
            ]
        );

        Auth::login($user);

        // Если используешь Sanctum/JWT — выдай токен:
        $token = $user->createToken('auth')->plainTextToken;

        // редиректни на фронт с токеном или ставь HttpOnly cookie
        $frontend = config('services.discord.frontend_url', 'http://localhost:3000');
        return redirect()->away($frontend . "/auth/callback?token=$token");
    }
}
