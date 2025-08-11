<?php
namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Giveaway;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Auth;
class EntryController extends Controller
{
    // POST /api/entries
    public function store(Request $request, TelegramService $tg)
    {
        $validated = $request->validate([
            'giveaway_id' => ['required', Rule::exists('giveaways', 'id')->where(fn($q) => $q->where('active', 1))],
            'wallet' => 'required|string|max:255',
        ]);
        $user = Auth::user();
        $giveaway = Giveaway::find($validated['giveaway_id']);
        // один раз участвовать (unique constraint тоже есть)
        $entry = Entry::updateOrCreate(
            ['user_id' => $user->id, 'giveaway_id' => $validated['giveaway_id']],
            ['wallet' => $validated['wallet']]
        );

        // TODO: уведомление в телеграм (при желании)
        // best-effort: не блокируем ответ, ошибки просто логируются
        
        $tg->notifyWalletEntry(
            $user->discord_name ?? ($user->name ?? 'unknown'),
            $entry->wallet,
            $giveaway?->name ?? 'Unknown Project'
        );

        return response()->json($entry, 201);
    }

    // GET /api/entries?search=... (admin)
    public function index()
    {
        $user = auth()->user();

        $query = Entry::with(['user', 'giveaway']);

        if (!$user->is_admin) {
            $query->where('user_id', $user->id);
        }

        return $query->get();
    }



    // PATCH /api/entries/{id}/verify (admin)
    public function verify($id)
    {
        $entry = Entry::findOrFail($id);
        $entry->update(['verified' => true]);
        return response()->json(['verified' => true]);
    }
}
