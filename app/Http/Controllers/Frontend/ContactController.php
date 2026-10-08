<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Mail\NewContactSubmission;
use App\Models\Contact;
use App\Services\FrontendContactService;
use App\Services\FrontendPageService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    public function index(Request $request, FrontendPageService $pages, FrontendContactService $contacts): View
    {
        $data = $pages->forSlug('contact', $request);
        $data['contactContent'] = $contacts->data($data);

        return view('frontend.contact', $data);
    }

    public function store(StoreContactRequest $request, FrontendPageService $pages, FrontendContactService $contacts): RedirectResponse
    {
        if ($request->honeypotFilled()) {
            return redirect()
                ->route('frontend.contact')
                ->with('success', 'Thanks for contacting us. Your message has been received.');
        }

        $validated = $request->validated();

        $contact = Contact::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'topic' => $request->topicLabel(),
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => str($request->userAgent())->limit(1000, '')->toString(),
            'referrer' => str((string) $request->headers->get('referer'))->limit(1000, '')->toString() ?: null,
            'page_url' => $request->url(),
            'submitted_at' => now(),
        ]);

        $pageData = $pages->forSlug('contact', $request);
        $recipient = $contacts->data($pageData)['recipient_email'] ?? null;

        if ($recipient) {
            try {
                Mail::to($recipient)->send(new NewContactSubmission($contact));
            } catch (Throwable $exception) {
                Log::warning('Contact notification email failed.', [
                    'contact_id' => $contact->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        return redirect()
            ->route('frontend.contact')
            ->with('success', 'Thanks for contacting us. Your message has been received.');
    }
}
