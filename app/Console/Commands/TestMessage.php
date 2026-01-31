<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Message;

class TestMessage extends Command
{
    protected $signature = 'test:message';
    protected $description = 'Test message creation';

    public function handle()
    {
        $users = User::take(2)->get();

        if ($users->count() < 2) {
            $this->error('No hay suficientes usuarios en la BD');
            return 1;
        }

        $this->info("Usuario 1: {$users[0]->name} (ID: {$users[0]->id})");
        $this->info("Usuario 2: {$users[1]->name} (ID: {$users[1]->id})");

        $countBefore = Message::count();
        $this->info("\nMensajes antes: $countBefore");

        try {
            $message = Message::create([
                'from_user_id' => $users[0]->id,
                'to_user_id' => $users[1]->id,
                'message' => 'Test message from CLI ' . now(),
            ]);

            $this->info("✓ Mensaje creado con ID: {$message->id}");

            $countAfter = Message::count();
            $this->info("Mensajes después: $countAfter");

            if ($countAfter > $countBefore) {
                $this->info("✓ El mensaje se guardó correctamente en BD");
            } else {
                $this->error("✗ El mensaje NO se guardó en BD");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("✗ Error creando mensaje: " . $e->getMessage());
            return 1;
        }
    }
}
