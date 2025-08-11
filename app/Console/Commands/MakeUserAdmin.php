<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class MakeUserAdmin extends Command
{
    /**
     * Имя и сигнатура команды.
     *
     * php artisan make:user-admin {id=1}
     */
    protected $signature = 'make:user-admin {id=1}';

    /**
     * Описание команды.
     */
    protected $description = 'Назначить пользователю роль admin по ID (по умолчанию id=1)';

    /**
     * Логика команды.
     */
    public function handle()
    {
        $id = $this->argument('id');
        $user = User::find($id);

        if (!$user) {
            $this->error("Пользователь с ID {$id} не найден.");
            return Command::FAILURE;
        }

        $user->is_admin = true;
        $user->save();

        $this->info("Пользователь {$user->name} (ID {$user->id}) теперь admin!");
        return Command::SUCCESS;
    }
}
