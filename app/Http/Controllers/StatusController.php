<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class StatusController extends Controller
{
    public function toggleStatus($model, $id)
    {
        $modelClass = "App\\Models\\" . ucfirst($model);

        if (!class_exists($modelClass)) {
            return back()->with('error', 'Model not found');
        }

        $record = $modelClass::find($id);

        if (!$record) {
            return back()->with('error', 'Record not found');
        }

        $record->is_active = !$record->is_active;
        $record->save();

        return back()->with('success', ucfirst($model) . ' status updated successfully!');
    }
}
