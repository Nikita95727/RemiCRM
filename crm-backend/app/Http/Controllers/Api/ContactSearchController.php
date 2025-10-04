<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Contact\Contracts\ContactRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactSearchController extends Controller
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository
    ) {}

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen(trim($query)) < 2) {
            return response()->json([]);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json([], 401);
        }

        try {
            $contacts = $this->contactRepository->search($user, trim($query))
                ->take(8);

            $results = [];
            foreach ($contacts as $contact) {
                $primarySource = $contact->primarySource;
                $results[] = [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'email' => $contact->email,
                    'phone' => $contact->phone,
                    'sources' => $contact->sourceObjects,
                    'primary_source' => $primarySource ? $primarySource->getLabel() : 'No source',
                    'primary_source_color' => $primarySource ? $primarySource->getCssClass() : 'bg-slate-100 text-slate-600',
                    'tags' => $contact->tags ?? [],
                    'initials' => $contact->initials,
                    'updated_at' => $contact->updated_at?->format('M j, Y'),
                ];
            }

            return response()->json($results);
        } catch (\Exception $e) {
            \Log::error('ContactSearchController error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}