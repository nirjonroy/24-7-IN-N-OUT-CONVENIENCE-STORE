<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $contacts = Contact::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.contacts.index', [
            'contacts' => $contacts,
            'statuses' => Contact::STATUSES,
        ]);
    }

    public function show(Contact $contact): View
    {
        if ($contact->status === 'new') {
            $contact->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
        }

        return view('admin.contacts.show', [
            'contact' => $contact->fresh(),
            'statuses' => Contact::STATUSES,
        ]);
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Contact::STATUSES)],
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'read' && ! $contact->read_at) {
            $updates['read_at'] = now();
        }

        if ($validated['status'] === 'replied' && ! $contact->replied_at) {
            $updates['replied_at'] = now();
            $updates['read_at'] = $contact->read_at ?: now();
        }

        $contact->update($updates);

        return back()->with('success', 'Contact status updated successfully.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()
            ->route('admin.contacts.index')
            ->with('success', 'Contact deleted successfully.');
    }
}
