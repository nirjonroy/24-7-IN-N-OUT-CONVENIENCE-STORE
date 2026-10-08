<!doctype html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h1 style="font-size: 20px;">New website contact submission</h1>
    <p><strong>Name:</strong> {{ $contact->name }}</p>
    <p><strong>Email:</strong> {{ $contact->email }}</p>
    <p><strong>Phone:</strong> {{ $contact->phone ?: '-' }}</p>
    <p><strong>Topic:</strong> {{ $contact->topic ?: '-' }}</p>
    <p><strong>Subject:</strong> {{ $contact->subject ?: '-' }}</p>
    <p><strong>Submitted:</strong> {{ $contact->submitted_at?->format('M d, Y h:i A') ?: $contact->created_at->format('M d, Y h:i A') }}</p>
    <p><strong>Source page:</strong> {{ $contact->page_url ?: '-' }}</p>
    <hr>
    <p style="white-space: pre-wrap;">{{ $contact->message }}</p>
</body>
</html>
