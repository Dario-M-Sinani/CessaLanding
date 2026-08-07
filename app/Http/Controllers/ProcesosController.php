<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProcesosController extends Controller
{
    public function index(Request $request): Response
    {
        $groups = Publication::getGroups();
        $activeGroup = $request->string('group')->toString();
        if (!isset($groups[$activeGroup])) {
            $activeGroup = '';
        }

        $publications = Publication::where('published', 'S')
            ->when($activeGroup !== '', fn ($query) => $query->whereIn('type', $groups[$activeGroup]))
            ->with('documents')
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Procesos/Index', [
            'publications' => $publications,
            'typeLabels' => Publication::getTypes(),
            'groupLabels' => Publication::getGroupLabels(),
            'groupCounts' => Publication::countsByGroup(),
            'activeGroup' => $activeGroup,
        ]);
    }
}
