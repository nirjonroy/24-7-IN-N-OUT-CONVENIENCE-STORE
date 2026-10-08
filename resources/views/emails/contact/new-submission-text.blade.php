New website contact submission

Name: {{ $contact->name }}
Email: {{ $contact->email }}
Phone: {{ $contact->phone ?: '-' }}
Topic: {{ $contact->topic ?: '-' }}
Subject: {{ $contact->subject ?: '-' }}
Submitted: {{ $contact->submitted_at?->format('M d, Y h:i A') ?: $contact->created_at->format('M d, Y h:i A') }}
Source page: {{ $contact->page_url ?: '-' }}

Message:
{{ $contact->message }}
