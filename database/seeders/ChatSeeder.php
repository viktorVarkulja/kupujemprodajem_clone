<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have a pool of users
        if (User::count() < 10) {
            User::factory()->count(10)->create();
        }

        $users = User::inRandomOrder()->get(['id','name']);
        if ($users->count() < 2) {
            $this->command?->warn('Not enough users to seed conversations. Skipping.');
            return;
        }

        $ads = Ad::query()->inRandomOrder()->get(['id','user_id','title']);

        // Create up to 10 ad-linked conversations if ads exist
        $adConversationsToCreate = min(10, $ads->count());
        for ($i = 0; $i < $adConversationsToCreate; $i++) {
            $ad = $ads[$i];
            $sellerId = $ad->user_id;
            $buyer = $users->where('id', '!=', $sellerId)->random();

            $conv = Conversation::create(['ad_id' => $ad->id]);
            ConversationParticipant::create(['conversation_id' => $conv->id, 'user_id' => $sellerId]);
            ConversationParticipant::create(['conversation_id' => $conv->id, 'user_id' => $buyer->id]);

            $this->seedMessages($conv->id, [$sellerId, $buyer->id]);

            // Randomly set one participant as having read
            if (rand(0,1) === 1) {
                ConversationParticipant::where('conversation_id', $conv->id)
                    ->inRandomOrder()->limit(1)
                    ->update(['last_read_at' => Carbon::now()->subMinutes(rand(0, 120))]);
            }
        }

        // Create 5 general conversations (no ad)
        for ($i = 0; $i < 5; $i++) {
            [$u1, $u2] = $this->pickTwoDistinct($users->pluck('id')->all());

            $conv = Conversation::create(['ad_id' => null]);
            ConversationParticipant::create(['conversation_id' => $conv->id, 'user_id' => $u1]);
            ConversationParticipant::create(['conversation_id' => $conv->id, 'user_id' => $u2]);

            $this->seedMessages($conv->id, [$u1, $u2]);
        }
    }

    private function seedMessages(int $conversationId, array $participantIds): void
    {
        $count = rand(8, 20);
        $now = Carbon::now()->subDays(rand(0, 7))->subHours(rand(0, 8));
        $turn = 0;
        for ($m = 0; $m < $count; $m++) {
            $senderId = $participantIds[$turn % 2];
            $msg = Message::create([
                'conversation_id' => $conversationId,
                'user_id' => $senderId,
                'body' => fake()->realText(rand(40, 140)),
            ]);
            // Adjust timestamps progressively for realism
            $msg->created_at = $now->copy();
            $msg->updated_at = $msg->created_at;
            $msg->save();

            // Randomly soft-delete a rare message
            if (rand(0, 40) === 1) {
                $msg->delete();
            }

            $now->addMinutes(rand(2, 30));
            $turn++;
        }
    }

    private function pickTwoDistinct(array $ids): array
    {
        shuffle($ids);
        return array_slice($ids, 0, 2);
    }
}

