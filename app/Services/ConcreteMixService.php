<?php

namespace App\Services;

use App\Models\ConcreteMix;
use Illuminate\Support\Collection;

class ConcreteMixService
{
    /**
     * Auto-generate the description string exactly as stored in the database:
     * "خرسانة {strength} - {cement_per_m3} كغ/م³"
     */
    public function buildDescription(int $strength, float $cementPerM3): string
    {
        return "خرسانة {$strength} - " . number_format($cementPerM3, 0) . ' كغ/م³';
    }

    public function getAll(): Collection
    {
        return ConcreteMix::orderBy('strength')->get();
    }

    public function create(array $data): ConcreteMix
    {
        return ConcreteMix::create([
            'strength'      => $data['strength'],
            'cement_per_m3' => $data['cement_per_m3'],
            'description'   => $this->buildDescription((int)$data['strength'], (float)$data['cement_per_m3']),
            'is_active'     => $data['is_active'] ?? true,
        ]);
    }

    public function toggleActive(ConcreteMix $mix): ConcreteMix
    {
        $mix->update(['is_active' => !$mix->is_active]);
        return $mix;
    }
}
