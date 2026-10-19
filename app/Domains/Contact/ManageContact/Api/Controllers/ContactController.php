<?php

namespace App\Domains\Contact\ManageContact\Api\Controllers;

use App\Http\Controllers\ApiController;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ContactController extends ApiController
{
    public function __construct()
    {
        $this->middleware('abilities:read')->only([
            'favorites',
            'show',
        ]);

        $this->middleware('abilities:write')->only([
            'markFavorite',
            'removeFavorite',
            'toggleFavorite',
            'updateNote',
        ]);
        parent::__construct();
    }

    public function index(Request $request)
    {
        $contacts = $this->contactQuery($request);
        $this->applyFilters($contacts, $request);
        $contacts = $contacts->paginate($this->getLimitPerPage());

        return ContactResource::collection($contacts);
    }

    public function favorites(Request $request)
    {
        $contacts = $this->contactQuery($request)
            ->where('is_favorite', true);
        $this->applyFilters($contacts, $request);
        $contacts = $contacts->paginate($this->getLimitPerPage());

        return ContactResource::collection($contacts);
    }

    public function show(Request $request, string $contactId)
    {
        $contact = $this->findContact($request, $contactId);

        return new ContactResource($contact);
    }

    public function markFavorite(Request $request, string $contactId)
    {
        $contact = $this->findContact($request, $contactId);

        $contact->is_favorite = true;
        $contact->last_updated_at = now();
        $contact->save();

        return new ContactResource($contact);
    }

    public function removeFavorite(Request $request, string $contactId)
    {
        $contact = $this->findContact($request, $contactId);

        $contact->is_favorite = false;
        $contact->last_updated_at = now();
        $contact->save();

        return new ContactResource($contact);
    }

    public function toggleFavorite(Request $request, string $contactId)
    {
        $contact = $this->findContact($request, $contactId);

        $contact->is_favorite = ! $contact->is_favorite;
        $contact->last_updated_at = now();
        $contact->save();

        return new ContactResource($contact);
    }

    public function updateNote(Request $request, string $contactId)
    {
        $request->validate([
            'personal_note' => ['nullable', 'string'],
        ]);

        $contact = $this->findContact($request, $contactId);

        $contact->personal_note = $request->input('personal_note');
        $contact->last_updated_at = now();
        $contact->save();

        return new ContactResource($contact);
    }

    public function stats(Request $request)
    {
        $contacts = $this->contactQuery($request);
        $stats = [
            'total_contacts' => (clone $contacts)->count(),

            'favorite_contacts' => (clone $contacts)
                ->where('is_favorite', true)
                ->count(),

            'contacts_with_notes' => (clone $contacts)
                ->whereNotNull('personal_note')
                ->where('personal_note', '!=', '')
                ->count(),
        ];

        return response()->json($stats);
    }

    private function contactQuery(Request $request): Builder
    {
        return Contact::query()
            ->whereIn(
                'vault_id',
                $request->user()->account->vaults()->select('id')
            );
    }

    private function findContact(Request $request, string $contactId): Contact
    {
        return $this->contactQuery($request)
            ->findOrFail($contactId);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->has('favorite')) {
            $query->where('is_favorite', $request->boolean('favorite'));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function (Builder $query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('nickname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
    }
}
