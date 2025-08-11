<?php
namespace App\Http\Controllers;

use App\Models\Giveaway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GiveawayController extends Controller
{
    // GET /api/giveaways?active=1|0 (по умолчанию активные)
    public function index(Request $request)
    {
        $query = Giveaway::query();
        if ($request->has('active'))
            $query->where('active', (bool) $request->boolean('active'));
        return $query->latest()->paginate(20);
    }

    // POST /api/giveaways (admin) multipart/form-data
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'network' => 'required|in:EVM,SOL,BTC',
            'description' => 'nullable|string',
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('giveaways', 'public');

        $g = Giveaway::create([
            'name' => $validated['name'],
            'network' => $validated['network'],
            'description' => $validated['description'] ?? null,
            'image' => $path,
        ]);

        return response()->json($g, 201);
    }

    // PATCH /api/giveaways/{id} (admin)
    public function update(Request $request, $id)
    {
        $g = Giveaway::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'network' => 'sometimes|in:EVM,SOL,BTC',
            'description' => 'sometimes|nullable|string',
            'image' => 'sometimes|image|max:2048',
            'active' => 'sometimes|boolean',
            'winner_mode' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('image')) {
            // удалить старую (опционально)
            if ($g->image && Storage::disk('public')->exists($g->image)) {
                Storage::disk('public')->delete($g->image);
            }
            $validated['image'] = $request->file('image')->store('giveaways', 'public');
        }

        $g->update($validated);

        return $g;
    }

    // PATCH /api/giveaways/{id}/end (admin)
    public function end($id)
    {
        $g = Giveaway::findOrFail($id);

        $g->active = !$g->active;
        $g->save();

        return response()->json([
            'status' => 'ok',
            'active' => $g->active
        ]);
    }


    // PATCH /api/giveaways/{id}/winner-toggle (admin)
    public function toggleWinner($id)
    {
        $g = Giveaway::findOrFail($id);
        $g->winner_mode = !$g->winner_mode;
        $g->save();
        return response()->json(['winner_mode' => $g->winner_mode]);
    }

    // DELETE /api/giveaways/{id} (admin)
    public function destroy($id)
    {
        $g = Giveaway::findOrFail($id);
        $g->delete();
        return response()->json(['deleted' => true]);
    }
}
