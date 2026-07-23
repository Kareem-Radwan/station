<?php

namespace App\Http\Controllers;

use App\Models\MixRecipe;
use App\Models\MaterialDensity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MixRecipeController extends Controller
{
    public function index()
    {
        $recipes = MixRecipe::orderBy('cement_per_m3')->get();
        $densities = MaterialDensity::orderBy('material_name')->get();
        
        return view('mix-recipes.index', compact('recipes', 'densities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cement_per_m3' => 'required|integer|unique:mix_recipes,cement_per_m3',
            'sand_kg' => 'required|numeric|min:0',
            'gravel1_kg' => 'required|numeric|min:0',
            'gravel2_kg' => 'required|numeric|min:0',
            'cement_kg' => 'required|numeric|min:0',
            'water_m3' => 'required|numeric|min:0',
            'additives_liter' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        MixRecipe::create($validated);

        return redirect()->route('mix-recipes.index')
            ->with('success', 'تم إضافة الوصفة بنجاح');
    }

    public function update(Request $request, MixRecipe $mixRecipe)
    {
        $validated = $request->validate([
            'sand_kg' => 'required|numeric|min:0',
            'gravel1_kg' => 'required|numeric|min:0',
            'gravel2_kg' => 'required|numeric|min:0',
            'cement_kg' => 'required|numeric|min:0',
            'water_m3' => 'required|numeric|min:0',
            'additives_liter' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $mixRecipe->update($validated);

        return redirect()->route('mix-recipes.index')
            ->with('success', 'تم تحديث الوصفة بنجاح');
    }

    public function destroy(MixRecipe $mixRecipe)
    {
        $mixRecipe->delete();

        return redirect()->route('mix-recipes.index')
            ->with('success', 'تم حذف الوصفة بنجاح');
    }

    public function updateDensities(Request $request)
    {
        $validated = $request->validate([
            'densities' => 'required|array',
            'densities.*.id' => 'required|exists:material_densities,id',
            'densities.*.density_kg_per_m3' => 'required|numeric|min:0.001',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['densities'] as $densityData) {
                MaterialDensity::where('id', $densityData['id'])
                    ->update(['density_kg_per_m3' => $densityData['density_kg_per_m3']]);
            }
        });

        return redirect()->route('mix-recipes.index')
            ->with('success', 'تم تحديث الكثافات بنجاح');
    }
}
