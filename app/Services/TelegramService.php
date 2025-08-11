<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    protected string $chatId;

    public function __construct(?string $token = null, ?string $chatId = null)
    {
        $this->token = $token ?: config('services.telegram.bot_token'); // config() — глобальный хелпер
        $this->chatId = $chatId ?: config('services.telegram.chat_id');
    }

    public function sendMessage(string $text, string $parseMode = 'HTML'): bool
    {
        if (!$this->token || !$this->chatId) {
            Log::warning('Telegram not configured: token or chat_id missing');
            return false;
        }

        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";

        try {
            $res = Http::asForm()
                ->timeout(10)
                ->post($url, [
                    'chat_id' => $this->chatId,
                    'text' => $text,
                    'parse_mode' => $parseMode,
                    'disable_web_page_preview' => true,
                ]);

            if (!$res->ok()) {
                Log::warning('Telegram sendMessage failed', [
                    'status' => $res->status(),
                    'body' => $res->body()
                ]);
            }

            return $res->ok();
        } catch (\Throwable $e) {
            Log::error('Telegram sendMessage exception: ' . $e->getMessage());
            return false;
        }
    }

    public function notifyWalletEntry(string $discordName, string $wallet, string $projectName): bool
    {
        $text = "Уведомление в ветку:\n\n" .
            "Пользователь <b>{$this->escape($discordName)}</b> вписал кошелек:\n" .
            "<code>{$this->escape($wallet)}</code> в гиве с <b>{$this->escape($projectName)}</b>";

        return $this->sendMessage($text);
    }

    protected function escape(string $s): string
    {
        return str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $s);
    }
}
