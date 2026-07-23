<?php

namespace App\Http\Controllers;

use App\Models\ConcreteMix;
use App\Services\ConcreteMixService;
use Illuminate\Http\Request;

class ConcreteMixController extends Controller
{
    public function __construct(private ConcreteMixService $mixService) {}

    public function index()
    {
        $mixes = $this->mixService->getAll();
        return view('concrete-mixes.index', compact('mixes'));
    }

    public function create()
    {
        return view('concrete-mixes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'strength'      => 'required|integer|min:1',
            'cement_per_m3' => 'required|numeric|min:1',
            'is_active'     => 'nullable|boolean',
        ]);

        $this->mixService->create($request->all());

        return redirect()->route('concrete-mixes.index')
            ->with('success', 'تم إضافة الخلطة بنجاح');
    }

    public function toggleActive(ConcreteMix $concreteMix)
    {
        $this->mixService->toggleActive($concreteMix);
        $label = $concreteMix->fresh()->is_active ? 'تفعيل' : 'تعطيل';
        return back()->with('success', "تم {$label} الخلطة بنجاح");
    }
}
